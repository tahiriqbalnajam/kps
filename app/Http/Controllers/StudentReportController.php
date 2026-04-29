<?php

namespace App\Http\Controllers;

use App\Laravue\JsonResponse;
use App\Models\ExamResult;
use App\Models\Student;
use App\Models\StudentAttendance;
use App\Models\TestResult;
use Illuminate\Support\Facades\DB;
use Phpml\Classification\KNearestNeighbors;

class StudentReportController extends Controller
{
    const EXCELLENT     = 'Excellent';
    const GOOD          = 'Good';
    const AVERAGE       = 'Average';
    const BELOW_AVERAGE = 'Below Average';
    const AT_RISK       = 'At Risk';

    public function generateReport($id)
    {
        $student = Student::with(['parents', 'stdclasses', 'section', 'class_session'])
            ->findOrFail($id);

        // Attendance
        $totalDays   = StudentAttendance::where('student_id', $id)->count();
        $presentDays = StudentAttendance::where('student_id', $id)->where('status', 'present')->count();
        $absentDays  = StudentAttendance::where('student_id', $id)->where('status', 'absent')->count();
        $attPct      = $totalDays > 0 ? round(($presentDays / $totalDays) * 100, 1) : 0;

        // Test results grouped by subject
        $testRows = TestResult::where('test_results.student_id', $id)
            ->join('tests',    'test_results.test_id',  '=', 'tests.id')
            ->join('subjects', 'tests.subject_id',      '=', 'subjects.id')
            ->select(
                'subjects.title as subject',
                DB::raw('COUNT(test_results.id)                                                          as total_tests'),
                DB::raw('SUM(tests.total_marks)                                                          as total_marks'),
                DB::raw('SUM(test_results.score)                                                         as obtained_marks'),
                DB::raw('SUM(CASE WHEN (test_results.score / tests.total_marks) < 0.33 THEN 1 ELSE 0 END) as failed_tests')
            )
            ->groupBy('subjects.id', 'subjects.title')
            ->get();

        // Exam results grouped by subject
        $examRows = ExamResult::where('exam_results.student_id', $id)
            ->join('exam_subjects', 'exam_results.exam_subject_id',  '=', 'exam_subjects.id')
            ->join('subjects',      'exam_subjects.subject_id',      '=', 'subjects.id')
            ->select(
                'subjects.title as subject',
                DB::raw('COUNT(exam_results.id)                                                                         as total_tests'),
                DB::raw('SUM(exam_subjects.total_marks)                                                                 as total_marks'),
                DB::raw('SUM(exam_results.obtained_marks)                                                               as obtained_marks'),
                DB::raw('SUM(CASE WHEN (exam_results.obtained_marks / exam_subjects.total_marks) < 0.33 THEN 1 ELSE 0 END) as failed_tests')
            )
            ->groupBy('subjects.id', 'subjects.title')
            ->get();

        // Merge test + exam per subject
        $map = [];
        foreach ($testRows as $row) {
            $map[$row->subject] = [
                'subject'        => $row->subject,
                'total_tests'    => (int)   $row->total_tests,
                'total_marks'    => (float) $row->total_marks,
                'obtained_marks' => (float) $row->obtained_marks,
                'failed_tests'   => (int)   $row->failed_tests,
            ];
        }
        foreach ($examRows as $row) {
            if (isset($map[$row->subject])) {
                $map[$row->subject]['total_tests']    += (int)   $row->total_tests;
                $map[$row->subject]['total_marks']    += (float) $row->total_marks;
                $map[$row->subject]['obtained_marks'] += (float) $row->obtained_marks;
                $map[$row->subject]['failed_tests']   += (int)   $row->failed_tests;
            } else {
                $map[$row->subject] = [
                    'subject'        => $row->subject,
                    'total_tests'    => (int)   $row->total_tests,
                    'total_marks'    => (float) $row->total_marks,
                    'obtained_marks' => (float) $row->obtained_marks,
                    'failed_tests'   => (int)   $row->failed_tests,
                ];
            }
        }

        $subjects = [];
        foreach ($map as $s) {
            $pct        = $s['total_marks'] > 0 ? round(($s['obtained_marks'] / $s['total_marks']) * 100, 1) : 0;
            $subjects[] = array_merge($s, ['percentage' => $pct]);
        }

        // Overall stats for ML features
        $overallObtained = array_sum(array_column($subjects, 'obtained_marks'));
        $overallTotal    = array_sum(array_column($subjects, 'total_marks'));
        $overallPct      = $overallTotal > 0 ? round(($overallObtained / $overallTotal) * 100, 1) : 0;
        $totalTests      = array_sum(array_column($subjects, 'total_tests'));
        $failedTests     = array_sum(array_column($subjects, 'failed_tests'));
        $failRate        = $totalTests > 0 ? round(($failedTests / $totalTests) * 100, 1) : 0;
        $subjectCount    = count($subjects);
        $weakCount       = count(array_filter($subjects, fn($s) => $s['percentage'] < 50));

        $features  = [(float)$attPct, (float)$overallPct, (float)$failRate, (float)$subjectCount, (float)$weakCount];
        $mlLabel   = $this->getMLClassification((int)$id, $features);

        return response()->json(new JsonResponse([
            'student' => [
                'id'               => $student->id,
                'name'             => $student->name,
                'admission_number' => $student->adminssion_number,
                'roll_no'          => $student->roll_no,
                'class'            => optional($student->stdclasses)->name   ?? '-',
                'section'          => optional($student->section)->name      ?? '-',
                'gender'           => $student->gender,
                'dob'              => $student->dob,
                'parent_name'      => optional($student->parents)->name     ?? '-',
                'parent_phone'     => optional($student->parents)->phone    ?? '-',
                'session'          => optional($student->class_session)->name ?? '-',
            ],
            'attendance' => [
                'total_working_days' => $totalDays,
                'present'            => $presentDays,
                'absent'             => $absentDays,
                'attendance_percent' => $attPct,
            ],
            'subjects'           => array_values($subjects),
            'overall_percentage' => $overallPct,
            'ml_classification'  => $mlLabel,
            'narrative_en'       => $this->buildNarrative($student, $attPct, $subjects, $overallPct, $mlLabel),
            'narrative_ur'       => $this->buildNarrativeUrdu($student, $attPct, $subjects, $overallPct, $mlLabel),
        ]));
    }

    // ─── ML Classification ─────────────────────────────────────────────────────

    private function getMLClassification(int $excludeId, array $features): string
    {
        $rows = DB::select("
            SELECT
                s.id,
                COALESCE(att.att_pct,  0) AS att_pct,
                COALESCE(tr.avg_pct,   0) AS avg_pct,
                COALESCE(tr.fail_rate, 0) AS fail_rate,
                COALESCE(tr.subj_cnt,  0) AS subj_cnt,
                COALESCE(tr.weak_cnt,  0) AS weak_cnt
            FROM students s
            LEFT JOIN (
                SELECT student_id,
                    (SUM(CASE WHEN status = 'present' THEN 1 ELSE 0 END) / NULLIF(COUNT(*), 0)) * 100 AS att_pct
                FROM student_attendances
                GROUP BY student_id
            ) att ON att.student_id = s.id
            LEFT JOIN (
                SELECT
                    tr.student_id,
                    (SUM(tr.score) / NULLIF(SUM(t.total_marks), 0)) * 100 AS avg_pct,
                    (SUM(CASE WHEN (tr.score / NULLIF(t.total_marks,0)) < 0.33 THEN 1 ELSE 0 END)
                        / NULLIF(COUNT(*), 0)) * 100 AS fail_rate,
                    COUNT(DISTINCT t.subject_id) AS subj_cnt,
                    SUM(CASE WHEN (tr.score / NULLIF(t.total_marks,0)) < 0.50 THEN 1 ELSE 0 END) AS weak_cnt
                FROM test_results tr
                JOIN tests t ON tr.test_id = t.id
                GROUP BY tr.student_id
            ) tr ON tr.student_id = s.id
            WHERE s.id != ? AND s.status = 'enable'
            HAVING avg_pct > 0
        ", [$excludeId]);

        if (count($rows) < 5) {
            return $this->ruleClassify((float)$features[1], (float)$features[0], (float)$features[2]);
        }

        $samples = [];
        $labels  = [];
        foreach ($rows as $row) {
            $samples[] = [(float)$row->att_pct, (float)$row->avg_pct, (float)$row->fail_rate, (float)$row->subj_cnt, (float)$row->weak_cnt];
            $labels[]  = $this->ruleClassify((float)$row->avg_pct, (float)$row->att_pct, (float)$row->fail_rate);
        }

        try {
            $knn = new KNearestNeighbors(7);
            $knn->train($samples, $labels);
            return $knn->predict($features);
        } catch (\Throwable $e) {
            return $this->ruleClassify((float)$features[1], (float)$features[0], (float)$features[2]);
        }
    }

    private function ruleClassify(float $avgPct, float $attPct = 75, float $failRate = 0): string
    {
        if ($avgPct < 33 || ($attPct < 50 && $avgPct < 50) || $failRate > 60) return self::AT_RISK;
        if ($avgPct < 50)  return self::BELOW_AVERAGE;
        if ($avgPct < 65)  return self::AVERAGE;
        if ($avgPct < 80)  return self::GOOD;
        return self::EXCELLENT;
    }

    // ─── Narrative Builder ──────────────────────────────────────────────────────

    private function buildNarrative(Student $student, float $attPct, array $subjects, float $overallPct, string $label): string
    {
        $name     = $student->name;
        $female   = strtolower($student->gender ?? '') === 'female';
        $he       = $female ? 'She'    : 'He';
        $his      = $female ? 'Her'    : 'His';
        $him      = $female ? 'her'    : 'him';
        $himself  = $female ? 'herself': 'himself';

        // ── Opening ──────────────────────────────────────────────────────────────
        $openings = [
            self::EXCELLENT     => [
                "{$name} has delivered an outstanding academic performance this term.",
                "It is a pleasure to report that {$name} has performed exceptionally well across all areas.",
                "{$name} continues to be among the top-performing students with impressive results this session.",
                "This report reflects an exemplary performance by {$name}, who has excelled in both academics and attendance.",
            ],
            self::GOOD          => [
                "{$name} has shown commendable progress and consistent effort this term.",
                "This progress report reflects a solid performance by {$name} across academics and attendance.",
                "{$name} has demonstrated steady improvement and a positive learning attitude throughout this session.",
                "A review of {$name}'s performance reveals encouraging progress and growing confidence in academics.",
            ],
            self::AVERAGE       => [
                "{$name} has performed at an average level this term, with clear room for improvement.",
                "This report highlights {$name}'s current academic standing along with areas that need focused attention.",
                "{$name} has shown adequate performance this term but has significant potential yet to be fully realized.",
                "An overall review of {$name}'s progress shows a mixed picture — some strengths alongside areas needing improvement.",
            ],
            self::BELOW_AVERAGE => [
                "{$name} has faced academic challenges this term that are reflected in the results below.",
                "This report highlights key areas where {$name} needs focused attention and consistent support.",
                "{$name}'s performance this term indicates the need for targeted improvement strategies and greater effort.",
                "A careful review of {$name}'s results reveals concerning patterns that require immediate corrective action.",
            ],
            self::AT_RISK       => [
                "{$name} is currently at academic risk and requires urgent attention from all stakeholders.",
                "This report raises serious concerns about {$name}'s academic standing and overall attendance record.",
                "{$name} requires immediate intervention to prevent further academic decline this session.",
                "The data for {$name} paints a worrying picture that demands prompt action from parents and school management alike.",
            ],
        ];
        $opening = ($openings[$label] ?? $openings[self::AVERAGE])[array_rand($openings[$label] ?? $openings[self::AVERAGE])];

        // ── Attendance ───────────────────────────────────────────────────────────
        if ($attPct >= 90) {
            $attPool = [
                "{$his} attendance is outstanding at {$attPct}%, placing {$him} among the most regular students in school.",
                "With an attendance rate of {$attPct}%, {$name} has demonstrated exceptional commitment and punctuality throughout the term.",
                "{$he} has attended {$attPct}% of all school days — a reflection of strong discipline and a serious attitude toward studies.",
                "{$his} near-perfect attendance of {$attPct}% is commendable and has positively reinforced {$his} learning consistency.",
                "Attendance of {$attPct}% places {$name} in the top bracket for regularity, which directly contributes to {$his} academic strength.",
            ];
        } elseif ($attPct >= 75) {
            $attPool = [
                "{$his} attendance stands at {$attPct}%, which is satisfactory. Maintaining or improving this will further support {$his} academic growth.",
                "With {$attPct}% attendance, {$name} has been reasonably regular this term, though there is room to improve consistency.",
                "{$he} has attended {$attPct}% of school days — a decent record, but striving for above 90% would yield better academic results.",
                "{$his} attendance of {$attPct}% meets the required threshold. Greater regularity will directly benefit {$his} overall performance.",
                "At {$attPct}% attendance, {$name} is present for most school days. Eliminating unnecessary absences could further boost results.",
            ];
        } elseif ($attPct >= 60) {
            $attPool = [
                "{$his} attendance of {$attPct}% is below the desired level and is likely impacting {$his} academic continuity.",
                "A concern is {$name}'s attendance, which stands at only {$attPct}%. Regular schooling is essential for uninterrupted learning.",
                "{$he} has been absent frequently this term, with attendance recorded at {$attPct}%. Parents are strongly advised to ensure regular school presence.",
                "With {$attPct}% attendance, {$name} is missing significant classroom instruction. Immediate improvement in regularity is highly recommended.",
                "{$his} attendance rate of {$attPct}% is a warning sign. Each missed day means lost learning — parental support is key here.",
            ];
        } elseif ($attPct > 0) {
            $attPool = [
                "{$his} attendance is critically low at {$attPct}%, which is a serious concern that demands urgent parental involvement.",
                "{$name} has attended only {$attPct}% of school days — a deeply concerning figure that requires immediate action.",
                "An attendance rate of just {$attPct}% is alarming. {$name} is missing far too much school to maintain meaningful academic progress.",
                "{$his} critically low attendance of {$attPct}% must be addressed urgently by both parents and school management without delay.",
                "At only {$attPct}% attendance, {$name} is at severe risk of falling permanently behind. An emergency plan with parents is essential.",
            ];
        } else {
            $attPool = ["No attendance records have been recorded for {$name} in the current session."];
        }
        $attLine = $attPool[array_rand($attPool)];

        // ── Per-Subject Lines ─────────────────────────────────────────────────────
        $subjectLines    = [];
        $strongSubjects  = [];
        $weakSubjects    = [];
        $criticalSubjects = [];

        foreach ($subjects as $s) {
            $subj     = $s['subject'];
            $pct      = $s['percentage'];
            $obtained = $s['obtained_marks'];
            $total    = $s['total_marks'];
            $tests    = $s['total_tests'];

            if ($pct >= 80) {
                $strongSubjects[] = $subj;
                $pool = [
                    "In {$subj}, {$he} has achieved an excellent score of {$obtained}/{$total} ({$pct}%) across {$tests} assessments — truly impressive.",
                    "{$subj} is clearly a strong subject for {$name}, with {$pct}% marks ({$obtained} out of {$total}) reflecting deep understanding.",
                    "{$he} has excelled in {$subj}, scoring {$obtained} out of {$total} marks ({$pct}%) — one of {$his} finest performances.",
                    "A score of {$pct}% in {$subj} ({$obtained}/{$total}) demonstrates {$name}'s excellent command and consistent preparation.",
                    "In {$subj}, {$name} scored {$obtained} out of {$total} ({$pct}%), showing outstanding mastery and strong academic foundation.",
                ];
            } elseif ($pct >= 65) {
                $pool = [
                    "In {$subj}, {$name} has obtained {$obtained} out of {$total} marks ({$pct}%), which is a satisfactory and encouraging result.",
                    "{$he} has performed well in {$subj} with {$pct}% ({$obtained}/{$total}), showing a solid grasp of the subject matter.",
                    "A score of {$obtained}/{$total} ({$pct}%) in {$subj} reflects consistent effort and a reasonable understanding of core concepts.",
                    "{$name} secured {$pct}% in {$subj} ({$obtained} out of {$total}) — a good result with clear potential to go even higher.",
                    "In {$subj}, {$he} achieved {$pct}% across {$tests} tests ({$obtained}/{$total}), indicating growing confidence in this subject.",
                ];
            } elseif ($pct >= 50) {
                $pool = [
                    "In {$subj}, {$name} obtained {$obtained}/{$total} marks ({$pct}%), which is a passing grade but leaves significant room for improvement.",
                    "{$he} scored {$pct}% in {$subj} ({$obtained} out of {$total}). More focused effort and regular revision could substantially improve this.",
                    "{$name}'s performance in {$subj} is borderline at {$pct}% ({$obtained}/{$total}). Greater attention to this subject is needed.",
                    "A {$pct}% score in {$subj} ({$obtained}/{$total}) suggests {$name} understands the basics but lacks depth and practice.",
                    "In {$subj}, {$he} has managed {$pct}% across {$tests} tests — acceptable but not yet reflecting {$his} true potential.",
                ];
            } elseif ($pct >= 33) {
                $weakSubjects[] = $subj;
                $pool = [
                    "{$subj} is a weak area for {$name}, who scored only {$obtained}/{$total} ({$pct}%). Extra practice and coaching are strongly recommended.",
                    "In {$subj}, {$he} obtained {$pct}% ({$obtained} out of {$total}), which is below average and requires urgent remedial attention.",
                    "{$name} is struggling in {$subj} with a score of {$obtained}/{$total} ({$pct}%). Dedicated support from teachers is strongly advised.",
                    "A score of just {$pct}% in {$subj} ({$obtained}/{$total}) highlights the need for focused revision, extra practice, and targeted coaching.",
                    "In {$subj}, {$he} has scored {$pct}% across {$tests} tests — clearly a difficult subject that needs sustained, focused attention.",
                ];
            } else {
                $criticalSubjects[] = $subj;
                $weakSubjects[]     = $subj;
                $pool = [
                    "{$name} has failed in {$subj} with only {$obtained}/{$total} marks ({$pct}%). Immediate remedial classes are required.",
                    "In {$subj}, {$he} scored critically low — {$pct}% ({$obtained} out of {$total}). This requires urgent and sustained intervention.",
                    "{$subj} is a critical concern: {$name} obtained only {$pct}% ({$obtained}/{$total}), indicating serious difficulty with the subject material.",
                    "A failing score of {$obtained}/{$total} ({$pct}%) in {$subj} must be addressed immediately through dedicated extra support and close monitoring.",
                    "In {$subj}, {$he} failed to reach the passing threshold with {$pct}% ({$obtained}/{$total}). Both school and parents must act quickly.",
                ];
            }
            $subjectLines[] = $pool[array_rand($pool)];
        }

        // ── Strength / Weakness Summary ───────────────────────────────────────────
        $summaryParts = [];
        if (!empty($strongSubjects)) {
            $list = implode(', ', $strongSubjects);
            $pool = [
                "{$he} demonstrates particular strength in {$list}, where {$his} performance stands out as truly noteworthy.",
                "{$list} stand out as {$name}'s strongest subjects, showing real academic talent that should be nurtured further.",
                "{$name} clearly excels in {$list} and should continue building on these strengths to maximize academic potential.",
                "The data shows {$name} thriving in {$list} — a clear indicator of natural aptitude and dedicated preparation.",
            ];
            $summaryParts[] = $pool[array_rand($pool)];
        }
        if (!empty($weakSubjects)) {
            $list = implode(', ', $weakSubjects);
            $pool = [
                "Additional support and regular practice in {$list} are strongly recommended to bring performance up to an acceptable level.",
                "{$list} require focused attention and consistent extra effort from {$name} to achieve the improvement needed.",
                "Parents and teachers should collaborate to help {$name} overcome difficulties in {$list} before it affects overall academic standing.",
                "Dedicated tutoring sessions and structured revision plans for {$list} would make a significant positive difference for {$name}.",
            ];
            $summaryParts[] = $pool[array_rand($pool)];
        }
        if (!empty($criticalSubjects)) {
            $list = implode(', ', $criticalSubjects);
            $summaryParts[] = "The failing grades in {$list} are a serious concern — remedial classes should begin without delay.";
        }

        // ── ML-Driven Closing / Prediction ────────────────────────────────────────
        $closings = [
            self::EXCELLENT     => [
                "Based on an outstanding overall performance of {$overallPct}%, {$name} is on a strong trajectory for exceptional academic success. Keep up this excellent work!",
                "With an overall score of {$overallPct}%, {$name} is performing brilliantly. Continued dedication will ensure {$he} reaches the highest academic levels.",
                "The analysis strongly suggests {$name} will continue to excel. An overall average of {$overallPct}% places {$him} among the school's top performers this session.",
                "At {$overallPct}% overall, {$name} has set a high benchmark for {$himself}. Sustaining this momentum will open excellent academic and career opportunities.",
            ],
            self::GOOD          => [
                "With an overall average of {$overallPct}%, {$name} is progressing well. Sustained effort and focused improvement in weaker areas can push {$him} to the excellent category.",
                "Overall performance at {$overallPct}% is solid and encouraging. With targeted focus on weaker subjects, {$name} has every potential to reach the top tier.",
                "{$name}'s overall {$overallPct}% indicates good progress this term. A little more push and consistency in revision could make a significant difference in the next assessment.",
                "At {$overallPct}% overall, {$name} shows real promise. Addressing the identified weak areas will be the key to unlocking even better results going forward.",
            ],
            self::AVERAGE       => [
                "An overall average of {$overallPct}% shows {$name} is passing, but significantly more consistent effort is needed to progress toward better academic outcomes.",
                "With {$overallPct}% overall, {$name} has room to grow considerably. Regular study habits, teacher guidance, and parental encouragement will be the key drivers.",
                "The current {$overallPct}% average is a foundation to build upon. With discipline, structured study, and targeted support, {$name} can improve substantially.",
                "At {$overallPct}% overall, {$name} is in the middle bracket. Breaking into the good or excellent tier is achievable with consistent effort and the right guidance.",
            ],
            self::BELOW_AVERAGE => [
                "An overall score of {$overallPct}% is a cause for serious concern. Without immediate and sustained improvement, {$name} risks falling even further behind.",
                "{$name}'s overall {$overallPct}% performance requires corrective action right away. Regular tutoring and close parental supervision are strongly advised.",
                "At {$overallPct}% overall, {$name} needs an urgent change in study habits and consistent academic support to get back on a positive track.",
                "A {$overallPct}% overall average signals that {$name} is struggling significantly. An individualized improvement plan developed with teachers and parents is essential.",
            ],
            self::AT_RISK       => [
                "With an overall performance of only {$overallPct}%, {$name} is at serious academic risk. Immediate and sustained intervention from all stakeholders is critical.",
                "The combination of low attendance and a {$overallPct}% academic average places {$name} at high risk. Emergency academic support should begin without further delay.",
                "{$name}'s overall {$overallPct}% performance, paired with attendance concerns, requires an urgent meeting between parents, the student, and school management.",
                "At {$overallPct}% overall, {$name} faces a critical academic situation. Without a comprehensive intervention plan, recovery will become increasingly difficult.",
            ],
        ];

        $closingPool = $closings[$label] ?? $closings[self::AVERAGE];
        $closing     = $closingPool[array_rand($closingPool)];

        // ── Compose ───────────────────────────────────────────────────────────────
        $parts = [$opening, $attLine];
        if (!empty($subjectLines)) {
            $parts[] = implode(' ', $subjectLines);
        }
        if (!empty($summaryParts)) {
            $parts[] = implode(' ', $summaryParts);
        }
        $parts[] = $closing;

        return implode("\n\n", $parts);
    }

    // ─── Urdu Narrative Builder ─────────────────────────────────────────────────

    private function buildNarrativeUrdu(Student $student, float $attPct, array $subjects, float $overallPct, string $label): string
    {
        $name   = $student->name;
        $female = strtolower($student->gender ?? '') === 'female';
        // Urdu gender helpers (verb suffixes)
        $raha   = $female ? 'رہی'    : 'رہا';
        $kiya   = $female ? 'کی'     : 'کیا';
        $karta  = $female ? 'کرتی'   : 'کرتا';
        $sakta  = $female ? 'سکتی'   : 'سکتا';
        $wala   = $female ? 'والی'   : 'والا';
        $his    = 'اس کی';
        $he     = 'وہ';

        // ── Opening ──────────────────────────────────────────────────────────────
        $openings = [
            self::EXCELLENT     => [
                "{$name} نے اس سمسٹر میں ایک شاندار تعلیمی کارکردگی کا مظاہرہ {$kiya} ہے۔",
                "یہ جان کر بے حد خوشی ہوئی کہ {$name} نے تمام شعبوں میں بہترین کارکردگی دکھائی ہے۔",
                "{$name} مسلسل اعلیٰ نتائج کے ساتھ ممتاز طلبہ میں شامل ہے اور اس سمسٹر میں بھی اپنی بہترین صلاحیتوں کا اظہار {$kiya}۔",
                "یہ رپورٹ {$name} کی مثالی کارکردگی کی عکاسی کرتی ہے جو تعلیم اور حاضری دونوں میں نمایاں {$raha}۔",
            ],
            self::GOOD          => [
                "{$name} نے اس سمسٹر میں قابل تعریف ترقی اور مستقل محنت کا مظاہرہ {$kiya} ہے۔",
                "یہ رپورٹ {$name} کی تعلیمی اور حاضری کے حوالے سے ایک مضبوط کارکردگی کی عکاسی کرتی ہے۔",
                "{$name} نے اس سمسٹر میں مسلسل بہتری اور مثبت سیکھنے کا رویہ اپنایا ہے۔",
                "{$name} کی کارکردگی کا جائزہ حوصلہ افزا ترقی اور تعلیمی اعتماد میں اضافے کو ظاہر کرتا ہے۔",
            ],
            self::AVERAGE       => [
                "{$name} نے اس سمسٹر میں اوسط سطح پر کارکردگی دکھائی ہے اور بہتری کی واضح گنجائش موجود ہے۔",
                "یہ رپورٹ {$name} کی موجودہ تعلیمی صورتحال اور توجہ طلب شعبوں کو اجاگر کرتی ہے۔",
                "{$name} نے قابل قبول کارکردگی دکھائی ہے لیکن ابھی ان کی پوری صلاحیت سامنے نہیں آئی۔",
                "{$name} کی کارکردگی ملی جلی تصویر پیش کرتی ہے — کچھ خوبیاں اور کچھ بہتری کے شعبے۔",
            ],
            self::BELOW_AVERAGE => [
                "{$name} کو اس سمسٹر میں تعلیمی چیلنجز کا سامنا {$raha} جو نتائج میں ظاہر ہوتا ہے۔",
                "یہ رپورٹ ان اہم شعبوں کو اجاگر کرتی ہے جہاں {$name} کو توجہ اور مستقل مدد کی ضرورت ہے۔",
                "{$name} کی کارکردگی مخصوص بہتری کی حکمت عملیوں اور زیادہ محنت کی ضرورت ظاہر کرتی ہے۔",
                "{$name} کے نتائج کا جائزہ تشویشناک نمونوں کو ظاہر کرتا ہے جن کے لیے فوری اصلاحی اقدامات ضروری ہیں۔",
            ],
            self::AT_RISK       => [
                "{$name} اس وقت تعلیمی خطرے میں ہے اور تمام متعلقہ افراد کی فوری توجہ ضروری ہے۔",
                "یہ رپورٹ {$name} کی تعلیمی صورتحال اور حاضری کے ریکارڈ کے بارے میں سنگین خدشات اٹھاتی ہے۔",
                "{$name} کو مزید تعلیمی تنزلی روکنے کے لیے فوری مداخلت کی ضرورت ہے۔",
                "{$name} کا ڈیٹا ایک تشویشناک صورتحال پیش کرتا ہے جس کے لیے والدین اور اسکول انتظامیہ کی فوری کارروائی درکار ہے۔",
            ],
        ];
        $opening = ($openings[$label] ?? $openings[self::AVERAGE])[array_rand($openings[$label] ?? $openings[self::AVERAGE])];

        // ── Attendance ────────────────────────────────────────────────────────────
        if ($attPct >= 90) {
            $attPool = [
                "{$his} حاضری {$attPct}% رہی جو شاندار ہے اور اسکول کے سب سے باقاعدہ طلبہ میں شامل {$karta} ہے۔",
                "{$attPct}% کی شرح حاضری کے ساتھ {$name} نے غیر معمولی پابندی اور وقت کی پاسداری ثابت کی ہے۔",
                "{$he} نے {$attPct}% اسکولی دنوں میں حاضری دی — یہ مضبوط نظم و ضبط اور تعلیم کے تئیں سنجیدہ رویے کا ثبوت ہے۔",
                "{$his} قریباً مکمل حاضری {$attPct}% قابل تعریف ہے اور {$his} تعلیمی تسلسل پر مثبت اثر ڈالتی ہے۔",
                "{$attPct}% کی حاضری {$name} کو باقاعدگی کے لحاظ سے سرفہرست رکھتی ہے جو براہ راست {$his} تعلیمی کامیابی میں معاون ہے۔",
            ];
        } elseif ($attPct >= 75) {
            $attPool = [
                "{$his} حاضری {$attPct}% ہے جو قابل قبول ہے۔ اسے برقرار رکھنا {$his} تعلیمی ترقی میں مددگار ہوگا۔",
                "{$attPct}% حاضری کے ساتھ {$name} معقول حد تک باقاعدہ {$raha}، لیکن مزید بہتری کی گنجائش موجود ہے۔",
                "{$he} نے {$attPct}% اسکولی دنوں میں حاضری دی — قابل قبول ریکارڈ، مگر 90% سے اوپر کا ہدف بہتر نتائج دے گا۔",
                "{$his} {$attPct}% حاضری کم از کم حد کو پورا کرتی ہے۔ زیادہ باقاعدگی {$his} مجموعی کارکردگی پر مثبت اثر ڈالے گی۔",
                "{$attPct}% حاضری پر {$name} زیادہ تر اسکول میں موجود {$raha}۔ غیر ضروری غیر حاضریاں ختم کرنے سے نتائج بہتر ہو سکتے ہیں۔",
            ];
        } elseif ($attPct >= 60) {
            $attPool = [
                "{$his} حاضری {$attPct}% مطلوبہ سطح سے کم ہے اور یہ {$his} تعلیمی تسلسل پر اثر انداز ہو رہی ہے۔",
                "ایک تشویشناک بات {$name} کی حاضری ہے جو صرف {$attPct}% ہے۔ مسلسل اسکول آنا بلاتعطل تعلیم کے لیے ضروری ہے۔",
                "{$he} اس سمسٹر میں کثرت سے غیر حاضر {$raha} اور حاضری صرف {$attPct}% درج ہوئی۔ والدین کو فوری توجہ دینی چاہیے۔",
                "{$attPct}% حاضری کے ساتھ {$name} کلاس کی اہم تعلیم سے محروم ہو {$raha} ہے۔ فوری بہتری ضروری ہے۔",
                "{$his} {$attPct}% حاضری ایک تنبیہ ہے۔ ہر غیر حاضری سیکھنے کا ایک موقع ضائع کرتی ہے — والدین کا تعاون ضروری ہے۔",
            ];
        } elseif ($attPct > 0) {
            $attPool = [
                "{$his} حاضری صرف {$attPct}% ہے جو ایک سنگین تشویش ہے اور والدین کی فوری توجہ کی متقاضی ہے۔",
                "{$name} نے صرف {$attPct}% اسکولی دنوں میں حاضری دی — یہ گہری تشویشناک صورتحال فوری عمل کا تقاضا کرتی ہے۔",
                "صرف {$attPct}% حاضری کی شرح انتہائی فکرانگیز ہے۔ {$name} اتنی غیر حاضریوں سے تعلیمی ترقی جاری نہیں رکھ {$sakta}۔",
                "{$attPct}% کی انتہائی کم حاضری والدین اور اسکول انتظامیہ دونوں کو بغیر تاخیر کے حل کرنی چاہیے۔",
                "صرف {$attPct}% حاضری پر {$name} شدید خطرے میں ہے۔ والدین کے ساتھ ایک ہنگامی منصوبہ ناگزیر ہے۔",
            ];
        } else {
            $attPool = ["{$name} کے لیے موجودہ سمسٹر میں کوئی حاضری ریکارڈ دستیاب نہیں ہے۔"];
        }
        $attLine = $attPool[array_rand($attPool)];

        // ── Per-Subject Lines ─────────────────────────────────────────────────────
        $subjectLines     = [];
        $strongSubjects   = [];
        $weakSubjects     = [];
        $criticalSubjects = [];

        foreach ($subjects as $s) {
            $subj     = $s['subject'];
            $pct      = $s['percentage'];
            $obtained = $s['obtained_marks'];
            $total    = $s['total_marks'];
            $tests    = $s['total_tests'];

            if ($pct >= 80) {
                $strongSubjects[] = $subj;
                $pool = [
                    "{$subj} میں {$he} نے {$tests} امتحانات میں {$obtained}/{$total} نمبر ({$pct}%) حاصل {$kiya} — واقعی شاندار نتیجہ۔",
                    "{$subj} واضح طور پر {$name} کا مضبوط مضمون ہے — {$pct}% ({$obtained}/{$total}) جو گہری سمجھ اور تیاری کی عکاسی کرتے ہیں۔",
                    "{$he} نے {$subj} میں {$obtained}/{$total} نمبر ({$pct}%) حاصل کر کے بہترین کارکردگی دکھائی — {$his} اعلیٰ ترین نتائج میں سے ایک۔",
                    "{$subj} میں {$pct}% ({$obtained}/{$total}) {$name} کی مہارت اور مستقل تیاری کا ثبوت ہے۔",
                    "{$name} نے {$subj} میں {$obtained}/{$total} ({$pct}%) حاصل کیے — غیر معمولی تسلط اور مضبوط تعلیمی بنیاد کا اظہار۔",
                ];
            } elseif ($pct >= 65) {
                $pool = [
                    "{$subj} میں {$name} نے {$obtained}/{$total} نمبر ({$pct}%) حاصل کیے جو ایک قابل قبول اور حوصلہ افزا نتیجہ ہے۔",
                    "{$he} نے {$subj} میں {$pct}% ({$obtained}/{$total}) کے ساتھ اچھی کارکردگی دکھائی جو مضمون پر مضبوط گرفت کی نشاندہی کرتی ہے۔",
                    "{$subj} میں {$obtained}/{$total} ({$pct}%) مستقل محنت اور بنیادی تصورات کی معقول سمجھ کی عکاسی کرتا ہے۔",
                    "{$name} نے {$subj} میں {$pct}% ({$obtained}/{$total}) حاصل کیے — اچھا نتیجہ جس میں مزید بلندی کی صلاحیت ہے۔",
                    "{$subj} میں {$he} نے {$pct}% حاصل کیے — {$his} بڑھتے ہوئے اعتماد اور محنت کی نشاندہی کرتا ہے۔",
                ];
            } elseif ($pct >= 50) {
                $pool = [
                    "{$subj} میں {$name} نے {$obtained}/{$total} ({$pct}%) حاصل کیے جو پاس ہے مگر بہتری کی کافی گنجائش ہے۔",
                    "{$he} نے {$subj} میں {$pct}% ({$obtained}/{$total}) اسکور {$kiya}۔ زیادہ توجہ اور مشق سے نمایاں بہتری آ {$sakta} ہے۔",
                    "{$name} کا {$subj} میں {$pct}% ({$obtained}/{$total}) کا اسکور اوسط سطح پر ہے۔ اس مضمون پر خصوصی توجہ ضروری ہے۔",
                    "{$subj} میں {$pct}% ({$obtained}/{$total}) سے پتہ چلتا ہے کہ {$name} بنیادی باتیں سمجھتا ہے مگر گہرائی کی کمی ہے۔",
                    "{$subj} میں {$he} نے {$tests} امتحانات میں {$pct}% حاصل کیے — قابل قبول مگر ابھی اصل صلاحیت سامنے نہیں آئی۔",
                ];
            } elseif ($pct >= 33) {
                $weakSubjects[] = $subj;
                $pool = [
                    "{$subj} {$name} کا کمزور شعبہ ہے — صرف {$obtained}/{$total} ({$pct}%)۔ اضافی مشق اور رہنمائی سخت ضروری ہے۔",
                    "{$subj} میں {$he} نے {$pct}% ({$obtained}/{$total}) حاصل کیے جو اوسط سے کم ہے اور فوری توجہ کی ضرورت ہے۔",
                    "{$name} {$subj} میں مشکل محسوس کر {$raha} ہے — {$obtained}/{$total} ({$pct}%)۔ اساتذہ کی خصوصی مدد لینی چاہیے۔",
                    "{$subj} میں {$pct}% ({$obtained}/{$total}) مخصوص نظرثانی، اضافی مشق اور ہدفی رہنمائی کی ضرورت اجاگر کرتا ہے۔",
                    "{$subj} میں {$he} نے {$pct}% حاصل کیے — یہ ایک مشکل مضمون ہے جس پر مستقل توجہ درکار ہے۔",
                ];
            } else {
                $criticalSubjects[] = $subj;
                $weakSubjects[]     = $subj;
                $pool = [
                    "{$name} {$subj} میں ناکام {$raha} — صرف {$obtained}/{$total} ({$pct}%)۔ فوری تدارکی اقدامات ناگزیر ہیں۔",
                    "{$subj} میں {$he} نے انتہائی کم اسکور کیا — {$pct}% ({$obtained}/{$total})۔ فوری اور مستقل توجہ درکار ہے۔",
                    "{$subj} ایک سنگین تشویش ہے: {$name} نے صرف {$pct}% ({$obtained}/{$total}) حاصل کیے جو شدید مشکل کی نشاندہی کرتا ہے۔",
                    "{$subj} میں {$obtained}/{$total} ({$pct}%) کے ناکام نمبر کو فوری خصوصی سپورٹ اور قریبی نگرانی سے حل کرنا ضروری ہے۔",
                    "{$subj} میں {$he} پاس ہونے کی حد تک نہیں پہنچ {$sakta} — {$pct}% ({$obtained}/{$total})۔ اسکول اور والدین کو فوری عمل کرنا ہوگا۔",
                ];
            }
            $subjectLines[] = $pool[array_rand($pool)];
        }

        // ── Summary ───────────────────────────────────────────────────────────────
        $summaryParts = [];
        if (!empty($strongSubjects)) {
            $list = implode('، ', $strongSubjects);
            $pool = [
                "{$he} خاص طور پر {$list} میں نمایاں قوت دکھاتا/دکھاتی ہے جہاں {$his} کارکردگی قابل ذکر ہے۔",
                "{$list} {$name} کے مضبوط ترین مضامین ہیں جو حقیقی تعلیمی صلاحیت کا اظہار کرتے ہیں۔",
                "{$name} واضح طور پر {$list} میں مہارت رکھتا/رکھتی ہے — ان خوبیوں کو آگے بڑھانا تعلیمی صلاحیت کو زیادہ سے زیادہ کرے گا۔",
                "ڈیٹا سے ظاہر ہے کہ {$name} {$list} میں پھل پھول {$raha} ہے — یہ قدرتی صلاحیت اور لگن دونوں کا اشارہ ہے۔",
            ];
            $summaryParts[] = $pool[array_rand($pool)];
        }
        if (!empty($weakSubjects)) {
            $list = implode('، ', $weakSubjects);
            $pool = [
                "{$list} میں اضافی مدد اور باقاعدہ مشق کی سخت ضرورت ہے تاکہ کارکردگی قابل قبول سطح تک بہتر ہو۔",
                "{$list} پر توجہ اور مستقل اضافی کوشش کی ضرورت ہے تاکہ مطلوبہ بہتری حاصل ہو سکے۔",
                "والدین اور اساتذہ کو مل کر {$name} کو {$list} میں مشکلات پر قابو پانے میں مدد کرنی چاہیے۔",
                "{$list} کے لیے منظم نظرثانی اور ٹیوشن سیشن {$name} کی کارکردگی میں نمایاں مثبت فرق پیدا کریں گے۔",
            ];
            $summaryParts[] = $pool[array_rand($pool)];
        }
        if (!empty($criticalSubjects)) {
            $list = implode('، ', $criticalSubjects);
            $summaryParts[] = "{$list} میں فیل ہونا سنگین تشویش ہے — بغیر تاخیر کے تدارکی کلاسیں شروع ہونی چاہئیں۔";
        }

        // ── Closing ───────────────────────────────────────────────────────────────
        $closings = [
            self::EXCELLENT     => [
                "{$overallPct}% کی شاندار مجموعی کارکردگی کی بنیاد پر {$name} غیر معمولی تعلیمی کامیابی کی راہ پر گامزن ہے۔ یہ شاندار کام جاری رہے!",
                "{$overallPct}% مجموعی اسکور کے ساتھ {$name} بہترین کارکردگی دکھا {$raha} ہے۔ مستقل لگن سے {$he} اعلیٰ ترین تعلیمی سطح تک پہنچے گا۔",
                "ڈیٹا سے واضح ہے کہ {$name} کا تعلیمی سفر مزید بلندیوں کی طرف جائے گا۔ {$overallPct}% مجموعی اوسط اسے ممتاز طلبہ میں رکھتی ہے۔",
                "{$overallPct}% مجموعی پر {$name} نے اپنے لیے اعلیٰ معیار قائم کیا ہے۔ اس رفتار کو برقرار رکھنا شاندار مستقبل کی ضمانت دے گا۔",
            ],
            self::GOOD          => [
                "{$overallPct}% مجموعی اوسط کے ساتھ {$name} اچھی ترقی کر {$raha} ہے۔ کمزور شعبوں پر توجہ سے ممتاز طبقے میں شامل ہو {$sakta} ہے۔",
                "{$overallPct}% کی مجموعی کارکردگی مضبوط اور حوصلہ افزا ہے۔ کمزور مضامین پر توجہ سے {$name} اعلیٰ درجے تک پہنچنے کی صلاحیت رکھتا/رکھتی ہے۔",
                "{$name} کا {$overallPct}% مجموعی نتیجہ اچھی ترقی کو ظاہر کرتا ہے۔ کمزور مضامین میں تھوڑی زیادہ محنت اگلے امتحان میں نمایاں فرق پیدا کرے گی۔",
                "{$overallPct}% مجموعی پر {$name} حقیقی صلاحیت دکھا {$raha} ہے۔ شناخت شدہ کمزور شعبوں کو دور کرنا مزید بہترین نتائج کی کلید ہے۔",
            ],
            self::AVERAGE       => [
                "{$overallPct}% مجموعی اوسط سے پتہ چلتا ہے کہ {$name} پاس ہے لیکن آگے بڑھنے کے لیے زیادہ مستقل کوشش ضروری ہے۔",
                "{$overallPct}% مجموعی کے ساتھ {$name} میں کافی ترقی کی گنجائش ہے۔ باقاعدہ مطالعہ اور رہنمائی اہم کردار ادا کریں گے۔",
                "موجودہ {$overallPct}% اوسط ایک بنیاد ہے جس پر آگے بڑھا جا {$sakta} ہے۔ نظم و ضبط اور توجہ سے {$name} کافی بہتری کر {$sakta} ہے۔",
                "{$overallPct}% مجموعی پر {$name} درمیانے طبقے میں ہے۔ اچھے یا ممتاز درجے میں آنا مسلسل محنت اور صحیح رہنمائی سے ممکن ہے۔",
            ],
            self::BELOW_AVERAGE => [
                "{$overallPct}% کا مجموعی اسکور سنگین تشویش کا باعث ہے۔ فوری بہتری کے بغیر {$name} مزید پیچھے رہ جانے کا خطرہ ہے۔",
                "{$name} کی {$overallPct}% مجموعی کارکردگی کو ابھی اصلاحی اقدامات کی ضرورت ہے۔ ٹیوشن اور والدین کی نگرانی سخت ضروری ہے۔",
                "{$overallPct}% مجموعی پر {$name} کو مطالعہ کی عادات میں فوری تبدیلی اور مستقل مدد کی ضرورت ہے تاکہ مثبت راستے پر آ سکے۔",
                "{$overallPct}% مجموعی اوسط ظاہر کرتی ہے کہ {$name} شدید جدوجہد کر {$raha} ہے۔ اساتذہ اور والدین کے ساتھ مل کر انفرادی بہتری کا منصوبہ ضروری ہے۔",
            ],
            self::AT_RISK       => [
                "صرف {$overallPct}% مجموعی کارکردگی کے ساتھ {$name} تعلیمی خطرے میں ہے۔ تمام متعلقہ افراد کی فوری مداخلت ضروری ہے۔",
                "کم حاضری اور {$overallPct}% تعلیمی اوسط کا مجموعہ {$name} کو اعلیٰ خطرے میں ڈالتا ہے۔ ہنگامی تعلیمی مدد بغیر تاخیر کے شروع ہونی چاہیے۔",
                "{$name} کی {$overallPct}% مجموعی کارکردگی اور حاضری کے خدشات کو دیکھتے ہوئے والدین، طالب علم اور اسکول انتظامیہ کی فوری ملاقات ضروری ہے۔",
                "{$overallPct}% مجموعی پر {$name} نازک تعلیمی صورتحال میں ہے۔ جامع مداخلتی منصوبے کے بغیر بحالی مزید مشکل ہو جائے گی۔",
            ],
        ];
        $closingPool = $closings[$label] ?? $closings[self::AVERAGE];
        $closing     = $closingPool[array_rand($closingPool)];

        // ── Compose ───────────────────────────────────────────────────────────────
        $parts = ["{$name} کی پیش رفت رپورٹ", $opening, $attLine];
        if (!empty($subjectLines)) $parts[] = implode(' ', $subjectLines);
        if (!empty($summaryParts)) $parts[] = implode(' ', $summaryParts);
        $parts[] = $closing;

        return implode("\n\n", $parts);
    }
}
