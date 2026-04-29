<template>
  <el-dialog
    v-model="visible"
    width="820px"
    top="4vh"
    :close-on-click-modal="false"
    :before-close="handleClose"
    class="report-dialog"
  >
    <template #header>
      <div class="dialog-header">
        <span class="dialog-title">Student Progress Report</span>
        <div style="display:flex;gap:8px;">
          <el-button type="success" size="small" @click="downloadPdf" :loading="pdfLoading" :icon="Download">Download PDF</el-button>
          <el-button type="primary" size="small" @click="printReport" :icon="Printer">Print</el-button>
        </div>
      </div>
    </template>

    <!-- Loading state -->
    <div v-if="loading" class="loading-wrap">
      <el-icon class="spin"><Loading /></el-icon>
      <span style="margin-left:10px; color:#606266;">Generating report, please wait...</span>
    </div>

    <!-- Report Content -->
    <div v-else-if="report" ref="printArea" class="report-body">

      <!-- ── Student Info ──────────────────────────────────────── -->
      <div class="section student-header">
        <div class="avatar-circle">{{ initials }}</div>
        <div class="student-meta">
          <h2 class="student-name">{{ report.student.name }}</h2>
          <div class="meta-grid">
            <span><strong>Adm #:</strong> {{ report.student.admission_number }}</span>
            <span><strong>Roll #:</strong> {{ report.student.roll_no }}</span>
            <span><strong>Class:</strong> {{ report.student.class }}</span>
            <span><strong>Section:</strong> {{ report.student.section }}</span>
            <span><strong>Gender:</strong> {{ report.student.gender }}</span>
            <span><strong>DOB:</strong> {{ formatDate(report.student.dob) }}</span>
            <span><strong>Parent:</strong> {{ report.student.parent_name }}</span>
            <span><strong>Phone:</strong> {{ report.student.parent_phone }}</span>
            <span><strong>Session:</strong> {{ report.student.session }}</span>
          </div>
        </div>
        <div class="ml-badge" :class="badgeClass">
          <div class="badge-label">AI Assessment</div>
          <div class="badge-value">{{ report.ml_classification }}</div>
        </div>
      </div>

      <!-- ── Attendance ───────────────────────────────────────── -->
      <div class="section">
        <div class="section-title">Attendance Overview</div>
        <div class="stat-cards">
          <div class="stat-card total">
            <div class="stat-num">{{ report.attendance.total_working_days }}</div>
            <div class="stat-label">Working Days</div>
          </div>
          <div class="stat-card present">
            <div class="stat-num">{{ report.attendance.present }}</div>
            <div class="stat-label">Present</div>
          </div>
          <div class="stat-card absent">
            <div class="stat-num">{{ report.attendance.absent }}</div>
            <div class="stat-label">Absent</div>
          </div>
          <div class="stat-card pct" :class="attClass">
            <div class="stat-num">{{ report.attendance.attendance_percent }}%</div>
            <div class="stat-label">Attendance Rate</div>
          </div>
        </div>
        <el-progress
          :percentage="report.attendance.attendance_percent"
          :color="attColor"
          :stroke-width="10"
          class="att-progress"
        />
      </div>

      <!-- ── Academic Performance ─────────────────────────────── -->
      <div class="section" v-if="report.subjects.length">
        <div class="section-title">Academic Performance — Subject Wise</div>
        <el-table :data="report.subjects" size="small" stripe border class="subject-table">
          <el-table-column label="Subject" prop="subject" min-width="130" />
          <el-table-column label="Tests" prop="total_tests" width="70" align="center" />
          <el-table-column label="Total Marks" prop="total_marks" width="105" align="center" />
          <el-table-column label="Obtained" prop="obtained_marks" width="95" align="center" />
          <el-table-column label="%" prop="percentage" width="70" align="center">
            <template #default="{ row }">
              <span :class="pctClass(row.percentage)">{{ row.percentage }}%</span>
            </template>
          </el-table-column>
          <el-table-column label="Grade" width="100" align="center">
            <template #default="{ row }">
              <el-tag :type="gradeTagType(row.percentage)" size="small">{{ grade(row.percentage) }}</el-tag>
            </template>
          </el-table-column>
          <el-table-column label="Progress" min-width="130">
            <template #default="{ row }">
              <el-progress :percentage="Math.min(row.percentage, 100)" :color="progressColor(row.percentage)" :show-text="false" :stroke-width="7" />
            </template>
          </el-table-column>
        </el-table>

        <!-- Overall row -->
        <div class="overall-row">
          <span class="overall-label">Overall Performance</span>
          <span class="overall-pct" :class="pctClass(report.overall_percentage)">{{ report.overall_percentage }}%</span>
          <el-tag :type="gradeTagType(report.overall_percentage)" size="small" class="overall-tag">{{ grade(report.overall_percentage) }}</el-tag>
        </div>
      </div>

      <div class="section no-data" v-else>
        <el-empty description="No academic records found for this student." :image-size="80" />
      </div>

      <!-- ── AI Narrative ─────────────────────────────────────── -->
      <!-- ── AI Narrative ─────────────────────────────────────── -->
      <div class="section narrative-section">
        <div class="section-title">
          <span>AI Generated Report</span>
          <el-tag size="small" type="info" class="ai-tag">Rule-based ML</el-tag>
          <div class="lang-toggle">
            <button :class="['lang-btn', { active: lang === 'en' }]" @click="lang = 'en'">English</button>
            <button :class="['lang-btn', { active: lang === 'ur' }]" @click="lang = 'ur'">اردو</button>
          </div>
        </div>
        <div class="narrative-body" :class="{ 'narrative-rtl': lang === 'ur' }">
          <p
            v-for="(para, i) in narrativeParagraphs"
            :key="i"
            class="narrative-para"
            :class="{ 'para-title': i === 0 }"
          >{{ para }}</p>
        </div>
      </div>

      <!-- ── Footer ───────────────────────────────────────────── -->
      <div class="report-footer">
        <span>Generated on {{ generatedOn }}</span>
        <span>IDL School Management System</span>
      </div>
    </div>

    <!-- Error -->
    <div v-else-if="error" class="error-wrap">
      <el-result icon="error" title="Failed to generate report" :sub-title="error">
        <template #extra>
          <el-button type="primary" @click="load">Retry</el-button>
        </template>
      </el-result>
    </div>
  </el-dialog>
</template>

<script>
import { Printer, Loading, Download } from '@element-plus/icons-vue';
import jsPDF from 'jspdf';
import 'jspdf-autotable';
import { getProgressReport } from '@/api/student.js';
import moment from 'moment';

export default {
  name: 'StudentProgressReport',
  props: {
    studentId: { type: [Number, String], required: true },
    modelValue: { type: Boolean, default: false },
  },
  emits: ['update:modelValue'],
  data() {
    return {
      Printer,
      Loading,
      Download,
      loading: false,
      pdfLoading: false,
      report: null,
      error: null,
      generatedOn: '',
      lang: 'en',
    };
  },
  computed: {
    visible: {
      get() { return this.modelValue; },
      set(v) { this.$emit('update:modelValue', v); },
    },
    initials() {
      if (!this.report) return '?';
      return this.report.student.name
        .split(' ')
        .slice(0, 2)
        .map(w => w[0]?.toUpperCase() ?? '')
        .join('');
    },
    narrativeParagraphs() {
      const text = this.lang === 'ur'
        ? (this.report?.narrative_ur ?? '')
        : (this.report?.narrative_en ?? '');
      return text.split('\n\n').filter(p => p.trim());
    },
    englishParagraphs() {
      return (this.report?.narrative_en ?? '').split('\n\n').filter(p => p.trim());
    },
    badgeClass() {
      const map = {
        'Excellent':     'badge-excellent',
        'Good':          'badge-good',
        'Average':       'badge-average',
        'Below Average': 'badge-below',
        'At Risk':       'badge-risk',
      };
      return map[this.report?.ml_classification] ?? 'badge-average';
    },
    attClass() {
      const p = this.report?.attendance.attendance_percent ?? 0;
      if (p >= 90) return 'pct-excellent';
      if (p >= 75) return 'pct-good';
      if (p >= 60) return 'pct-warn';
      return 'pct-bad';
    },
    attColor() {
      const p = this.report?.attendance.attendance_percent ?? 0;
      if (p >= 90) return '#67c23a';
      if (p >= 75) return '#409eff';
      if (p >= 60) return '#e6a23c';
      return '#f56c6c';
    },
  },
  mounted() {
    if (this.modelValue) this.load();
  },
  watch: {
    modelValue(v) { if (v) this.load(); },
  },
  methods: {
    async load() {
      this.loading = true;
      this.report  = null;
      this.error   = null;
      this.lang    = 'en';
      try {
        const { data } = await getProgressReport(this.studentId);
        this.report      = data;
        this.generatedOn = moment().format('DD MMM YYYY, hh:mm A');
      } catch (e) {
        this.error = e?.response?.data?.message ?? 'Something went wrong. Please try again.';
      } finally {
        this.loading = false;
      }
    },
    handleClose() {
      this.visible = false;
    },
    formatDate(d) {
      return d ? moment(d).format('DD MMM, YYYY') : '-';
    },
    grade(pct) {
      if (pct >= 80) return 'A+';
      if (pct >= 65) return 'B';
      if (pct >= 50) return 'C';
      if (pct >= 33) return 'D';
      return 'F';
    },
    gradeTagType(pct) {
      if (pct >= 80) return 'success';
      if (pct >= 65) return '';
      if (pct >= 50) return 'warning';
      return 'danger';
    },
    pctClass(pct) {
      if (pct >= 80) return 'clr-excellent';
      if (pct >= 65) return 'clr-good';
      if (pct >= 50) return 'clr-warn';
      return 'clr-bad';
    },
    progressColor(pct) {
      if (pct >= 80) return '#67c23a';
      if (pct >= 65) return '#409eff';
      if (pct >= 50) return '#e6a23c';
      return '#f56c6c';
    },
    async downloadPdf() {
      if (!this.report) return;
      this.pdfLoading = true;
      await this.$nextTick();

      try {
        const doc = new jsPDF('p', 'mm', 'a4');
        const pageW  = doc.internal.pageSize.getWidth();
        const pageH  = doc.internal.pageSize.getHeight();
        const margin = 14;
        const usable = pageW - margin * 2;
        let y = 0;

        // ── Helpers ─────────────────────────────────────────────────────
        const bold   = (size = 10) => { doc.setFont('helvetica', 'bold');   doc.setFontSize(size); };
        const normal = (size = 9)  => { doc.setFont('helvetica', 'normal'); doc.setFontSize(size); };
        const dark   = () => doc.setTextColor(48, 49, 51);
        const muted  = () => doc.setTextColor(144, 147, 153);
        const blue   = () => doc.setTextColor(64, 158, 255);

        const checkPage = (needed = 20) => {
          if (y + needed > pageH - 16) { doc.addPage(); y = margin; }
        };

        const gradeOf = (pct) => {
          if (pct >= 80) return 'A+';
          if (pct >= 65) return 'B';
          if (pct >= 50) return 'C';
          if (pct >= 33) return 'D';
          return 'F';
        };

        const badgeColorOf = (label) => {
          const map = {
            'Excellent':     [103, 194, 58],
            'Good':          [64,  158, 255],
            'Average':       [230, 162, 60],
            'Below Average': [245, 108, 108],
            'At Risk':       [245, 108, 108],
          };
          return map[label] || [64, 158, 255];
        };

        // ── Header bar ──────────────────────────────────────────────────
        doc.setFillColor(64, 158, 255);
        doc.rect(0, 0, pageW, 20, 'F');

        bold(15);
        doc.setTextColor(255, 255, 255);
        doc.text('Student Progress Report', pageW / 2, 10, { align: 'center' });

        normal(8);
        doc.setTextColor(220, 235, 255);
        doc.text('IDL School Management System', pageW / 2, 16, { align: 'center' });

        y = 26;

        // ── ML Badge (top-right) ─────────────────────────────────────────
        const mlLabel = this.report.ml_classification;
        const [br, bg, bb] = badgeColorOf(mlLabel);
        const badgeText = `AI Assessment: ${mlLabel}`;
        bold(8);
        doc.setTextColor(br, bg, bb);
        doc.setFillColor(br, bg, bb);
        const bw = doc.getTextWidth(badgeText) + 10;
        doc.setFillColor(br + 30 > 255 ? 255 : br + 30, bg + 30 > 255 ? 255 : bg + 30, bb + 30 > 255 ? 255 : bb + 30);
        doc.roundedRect(pageW - margin - bw, y - 5, bw, 7, 2, 2, 'F');
        doc.setTextColor(br, bg, bb);
        doc.text(badgeText, pageW - margin - bw / 2, y, { align: 'center' });

        // ── Student name ─────────────────────────────────────────────────
        bold(14);
        dark();
        doc.text(String(this.report.student.name ?? ''), margin, y);
        y += 8;

        // ── Student info grid (3 columns) ────────────────────────────────
        const s = this.report.student;
        const fields = [
          ['Admission #',   String(s.admission_number ?? '-')],
          ['Roll #',        String(s.roll_no          ?? '-')],
          ['Class',         String(s.class            ?? '-')],
          ['Section',       String(s.section          ?? '-')],
          ['Gender',        String(s.gender           ?? '-')],
          ['Date of Birth', String(this.formatDate(s.dob) ?? '-')],
          ['Parent',        String(s.parent_name      ?? '-')],
          ['Phone',         String(s.parent_phone     ?? '-')],
          ['Session',       String(s.session          ?? '-')],
        ];
        const colW = usable / 3;
        fields.forEach(([label, value], i) => {
          const cx = margin + (i % 3) * colW;
          const cy = y + Math.floor(i / 3) * 6;
          bold(8.5);
          dark();
          doc.text(`${label}: `, cx, cy);
          normal(8.5);
          muted();
          doc.text(String(value ?? '-'), cx + doc.getTextWidth(`${label}: `), cy);
        });
        y += Math.ceil(fields.length / 3) * 6 + 4;

        // Blue divider
        doc.setDrawColor(64, 158, 255);
        doc.setLineWidth(0.4);
        doc.line(margin, y, pageW - margin, y);
        y += 6;

        // ── Attendance ──────────────────────────────────────────────────
        bold(11);
        dark();
        doc.text('Attendance Overview', margin, y);
        y += 5;

        const att = this.report.attendance;
        const attBoxes = [
          { label: 'Working Days',    value: String(att.total_working_days), bg: [244, 244, 245], fg: [48,  49,  51]  },
          { label: 'Present',         value: String(att.present),            bg: [240, 249, 235], fg: [103, 194, 58]  },
          { label: 'Absent',          value: String(att.absent),             bg: [254, 240, 240], fg: [245, 108, 108] },
          { label: 'Attendance Rate', value: `${att.attendance_percent}%`,   bg: [236, 245, 255], fg: [64,  158, 255] },
        ];
        const bxW = (usable - 9) / 4;
        attBoxes.forEach((box, i) => {
          const bx = margin + i * (bxW + 3);
          doc.setFillColor(...box.bg);
          doc.roundedRect(bx, y, bxW, 16, 2, 2, 'F');
          bold(16);
          doc.setTextColor(...box.fg);
          doc.text(box.value, bx + bxW / 2, y + 9, { align: 'center' });
          normal(8);
          muted();
          doc.text(box.label, bx + bxW / 2, y + 14, { align: 'center' });
        });
        y += 20;

        // Attendance progress bar
        const attPct = att.attendance_percent;
        const barH   = 4;
        doc.setFillColor(235, 238, 245);
        doc.roundedRect(margin, y, usable, barH, 1.5, 1.5, 'F');
        const attFill = attPct >= 90 ? [103,194,58] : attPct >= 75 ? [64,158,255] : attPct >= 60 ? [230,162,60] : [245,108,108];
        doc.setFillColor(...attFill);
        doc.roundedRect(margin, y, Math.max((attPct / 100) * usable, 1), barH, 1.5, 1.5, 'F');
        y += barH + 6;

        // ── Subject Table ───────────────────────────────────────────────
        if (this.report.subjects.length > 0) {
          checkPage(40);
          bold(11);
          dark();
          doc.text('Academic Performance — Subject Wise', margin, y);
          y += 4;

          doc.autoTable({
            startY: y,
            margin: { left: margin, right: margin },
            head: [['Subject', 'Tests', 'Total', 'Obtained', '%', 'Grade', 'Progress']],
            body: this.report.subjects.map(s => [
              String(s.subject),
              String(s.total_tests),
              String(s.total_marks),
              String(s.obtained_marks),
              `${s.percentage}%`,
              gradeOf(s.percentage),
              '',
            ]),
            foot: [[
              'Overall', '',
              String(this.report.subjects.reduce((a, s) => a + s.total_marks,    0)),
              String(this.report.subjects.reduce((a, s) => a + s.obtained_marks, 0)),
              `${this.report.overall_percentage}%`,
              gradeOf(this.report.overall_percentage),
              '',
            ]],
            showFoot: 'lastPage',
            headStyles: { fillColor: [64, 158, 255], textColor: 255, fontStyle: 'bold', fontSize: 9 },
            footStyles: { fillColor: [245, 247, 250], textColor: [48, 49, 51], fontStyle: 'bold', fontSize: 9 },
            bodyStyles: { fontSize: 9, textColor: [48, 49, 51] },
            alternateRowStyles: { fillColor: [250, 250, 250] },
            columnStyles: {
              0: { cellWidth: 42 },
              1: { halign: 'center', cellWidth: 14 },
              2: { halign: 'center', cellWidth: 22 },
              3: { halign: 'center', cellWidth: 22 },
              4: { halign: 'center', cellWidth: 18 },
              5: { halign: 'center', cellWidth: 16 },
              6: { cellWidth: 44 },
            },
            didParseCell: (data) => {
              if (data.section !== 'body') return;
              if (data.column.index === 5) {
                const g = data.cell.raw;
                if (g === 'A+') data.cell.styles.textColor = [103, 194, 58];
                else if (g === 'F') data.cell.styles.textColor = [245, 108, 108];
                else if (g === 'D') data.cell.styles.textColor = [230, 162, 60];
              }
              if (data.column.index === 4) {
                const p = parseFloat(data.cell.raw);
                if (p >= 80)      data.cell.styles.textColor = [103, 194, 58];
                else if (p < 33)  data.cell.styles.textColor = [245, 108, 108];
                else if (p < 50)  data.cell.styles.textColor = [230, 162, 60];
              }
            },
            didDrawCell: (data) => {
              if (data.section !== 'body' || data.column.index !== 6) return;
              const subj   = this.report.subjects[data.row.index];
              if (!subj) return;
              const pct    = Math.min(Math.max(subj.percentage, 0), 100);
              const pad    = 3;
              const bx     = data.cell.x + pad;
              const bw     = data.cell.width - pad * 2;
              const bh     = 3;
              const by     = data.cell.y + (data.cell.height - bh) / 2;
              // Track background
              doc.setFillColor(229, 231, 235);
              doc.roundedRect(bx, by, bw, bh, 1, 1, 'F');
              // Colored fill
              const fillColor = pct >= 80 ? [103,194,58] : pct >= 65 ? [64,158,255] : pct >= 50 ? [230,162,60] : [245,108,108];
              doc.setFillColor(...fillColor);
              doc.roundedRect(bx, by, Math.max((pct / 100) * bw, 1), bh, 1, 1, 'F');
            },
          });

          y = doc.lastAutoTable.finalY + 8;
        }

        // ── AI Narrative ─────────────────────────────────────────────────
        checkPage(30);
        bold(11);
        dark();
        doc.text('AI Generated Report', margin, y);
        y += 6;

        const narrativeX    = margin + 6;
        const narrativeUsable = usable - 8;
        const paragraphs    = this.englishParagraphs; // PDF always uses English — jsPDF doesn't render Urdu script
        const barStartY     = y - 2;

        paragraphs.forEach((para, i) => {
          if (i === 0) { bold(10);   dark(); }
          else         { normal(9);  doc.setTextColor(74, 74, 74); }

          const lines = doc.splitTextToSize(para, narrativeUsable);
          checkPage(lines.length * 5 + 5);
          doc.text(lines, narrativeX, y);
          y += lines.length * 5 + (i < paragraphs.length - 1 ? 4 : 0);
        });

        // Draw left blue accent bar
        doc.setFillColor(64, 158, 255);
        doc.rect(margin, barStartY, 2, y - barStartY + 2, 'F');

        // Light bg behind narrative
        doc.setFillColor(249, 250, 251);
        // (drawn before text — so re-order: draw bg, then re-draw text)
        // Instead just add a thin box outline
        doc.setDrawColor(179, 216, 255);
        doc.setLineWidth(0.2);
        doc.roundedRect(margin + 2, barStartY, usable - 2, y - barStartY + 2, 1, 1, 'S');

        y += 6;

        // ── Footer on every page ─────────────────────────────────────────
        const totalPages = doc.internal.getNumberOfPages();
        for (let p = 1; p <= totalPages; p++) {
          doc.setPage(p);
          doc.setDrawColor(235, 238, 245);
          doc.setLineWidth(0.3);
          doc.line(margin, pageH - 12, pageW - margin, pageH - 12);
          normal(7.5);
          muted();
          doc.text(`Generated: ${this.generatedOn}`, margin, pageH - 7);
          doc.text(`Page ${p} of ${totalPages}`, pageW / 2, pageH - 7, { align: 'center' });
          doc.text('IDL School Management System', pageW - margin, pageH - 7, { align: 'right' });
        }

        // ── Save ──────────────────────────────────────────────────────────
        const safeName = this.report.student.name.replace(/\s+/g, '_');
        doc.save(`Progress_Report_${safeName}.pdf`);

      } catch (e) {
        console.error('PDF generation failed:', e);
        this.$message.error('Failed to generate PDF. Please try again.');
      } finally {
        this.pdfLoading = false;
      }
    },
    printReport() {
      const content = this.$refs.printArea?.innerHTML;
      if (!content) return;
      const win = window.open('', '_blank');
      win.document.write(`
        <html><head><title>Progress Report — ${this.report?.student.name}</title>
        <style>
          * { box-sizing: border-box; margin: 0; padding: 0; }
          body { font-family: Arial, sans-serif; font-size: 13px; color: #222; padding: 24px; }
          .student-header { display: flex; gap: 16px; align-items: flex-start; margin-bottom: 20px; border-bottom: 2px solid #409eff; padding-bottom: 16px; }
          .avatar-circle { width: 60px; height: 60px; border-radius: 50%; background: #409eff; color: #fff; font-size: 22px; font-weight: 700; display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
          .student-name { font-size: 20px; font-weight: 700; margin-bottom: 8px; }
          .meta-grid { display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 4px 16px; font-size: 12px; }
          .ml-badge { margin-left: auto; text-align: center; padding: 8px 16px; border-radius: 8px; background: #ecf5ff; border: 1px solid #b3d8ff; }
          .badge-label { font-size: 11px; color: #666; }
          .badge-value { font-size: 15px; font-weight: 700; color: #409eff; }
          .section-title { font-size: 14px; font-weight: 700; color: #303133; border-left: 3px solid #409eff; padding-left: 8px; margin: 16px 0 10px; }
          .stat-cards { display: flex; gap: 12px; margin-bottom: 10px; }
          .stat-card { flex: 1; text-align: center; padding: 10px; border-radius: 8px; border: 1px solid #eee; }
          .stat-num { font-size: 22px; font-weight: 700; }
          .stat-label { font-size: 11px; color: #666; }
          table { width: 100%; border-collapse: collapse; font-size: 12px; margin-top: 8px; }
          th, td { border: 1px solid #ddd; padding: 6px 8px; text-align: center; }
          th { background: #f5f7fa; font-weight: 600; }
          .overall-row { display: flex; align-items: center; gap: 12px; margin-top: 10px; padding: 8px 12px; background: #f5f7fa; border-radius: 6px; font-weight: 600; }
          .overall-pct { font-size: 18px; font-weight: 700; }
          .narrative-body { background: #f9fafb; border-left: 4px solid #409eff; padding: 14px 16px; border-radius: 0 8px 8px 0; }
          .narrative-body.narrative-rtl { border-left:none; border-right:4px solid #409eff; border-radius:8px 0 0 8px; direction:rtl; text-align:right; font-family:'Noto Nastaliq Urdu','Arial Unicode MS',sans-serif; font-size:14px; line-height:2.2; }
          .narrative-para { margin-bottom: 10px; line-height: 1.7; }
          .para-title { font-size: 15px; font-weight: 700; color: #303133; }
          .report-footer { display: flex; justify-content: space-between; font-size: 11px; color: #909399; margin-top: 20px; padding-top: 10px; border-top: 1px solid #eee; }
          .clr-excellent { color: #67c23a; font-weight: 700; }
          .clr-good { color: #409eff; font-weight: 600; }
          .clr-warn { color: #e6a23c; font-weight: 600; }
          .clr-bad { color: #f56c6c; font-weight: 700; }
          .section { margin-bottom: 4px; }
        </style></head>
        <body>${content}</body></html>
      `);
      win.document.close();
      win.focus();
      setTimeout(() => { win.print(); win.close(); }, 400);
    },
  },
};
</script>

<style scoped>
.report-dialog :deep(.el-dialog__header) { padding: 14px 20px 10px; border-bottom: 1px solid #ebeef5; }
.dialog-header { display: flex; justify-content: space-between; align-items: center; }
.dialog-title { font-size: 16px; font-weight: 700; color: #303133; }

.loading-wrap { min-height: 300px; display: flex; align-items: center; justify-content: center; }
.error-wrap { min-height: 280px; display: flex; align-items: center; justify-content: center; }

.report-body { padding: 4px 0; font-size: 13px; }

/* Student header */
.student-header {
  display: flex;
  gap: 16px;
  align-items: flex-start;
  background: linear-gradient(135deg, #ecf5ff 0%, #f0f9ff 100%);
  border-radius: 10px;
  padding: 16px;
  margin-bottom: 16px;
  border: 1px solid #d9ecff;
}
.avatar-circle {
  width: 64px; height: 64px;
  border-radius: 50%;
  background: linear-gradient(135deg, #409eff, #007bff);
  color: #fff;
  font-size: 24px; font-weight: 700;
  display: flex; align-items: center; justify-content: center;
  flex-shrink: 0;
  box-shadow: 0 4px 12px rgba(64,158,255,0.35);
}
.student-meta { flex: 1; }
.student-name { font-size: 20px; font-weight: 700; color: #303133; margin-bottom: 8px; }
.meta-grid {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 4px 20px;
  font-size: 12px;
  color: #606266;
}
.meta-grid strong { color: #303133; }
.ml-badge {
  flex-shrink: 0;
  text-align: center;
  padding: 10px 18px;
  border-radius: 10px;
  min-width: 110px;
}
.badge-label { font-size: 11px; color: #909399; margin-bottom: 4px; }
.badge-value { font-size: 15px; font-weight: 700; }
.badge-excellent { background: #f0f9eb; border: 1px solid #b3e19d; }
.badge-excellent .badge-value { color: #67c23a; }
.badge-good      { background: #ecf5ff; border: 1px solid #b3d8ff; }
.badge-good .badge-value      { color: #409eff; }
.badge-average   { background: #fdf6ec; border: 1px solid #fcd29c; }
.badge-average .badge-value   { color: #e6a23c; }
.badge-below     { background: #fef0f0; border: 1px solid #fbc4c4; }
.badge-below .badge-value     { color: #f56c6c; }
.badge-risk      { background: #fff0f0; border: 2px solid #f56c6c; }
.badge-risk .badge-value      { color: #f56c6c; font-size: 17px; }

/* Sections */
.section { margin-bottom: 16px; }
.section-title {
  font-size: 13px; font-weight: 700;
  color: #303133;
  border-left: 3px solid #409eff;
  padding-left: 8px;
  margin-bottom: 10px;
  display: flex; align-items: center; gap: 8px;
}
.ai-tag { margin-left: 4px; }

/* Stat cards */
.stat-cards { display: flex; gap: 12px; margin-bottom: 12px; }
.stat-card {
  flex: 1; text-align: center;
  padding: 12px 8px;
  border-radius: 8px;
  border: 1px solid #ebeef5;
}
.stat-card.total   { background: #f4f4f5; }
.stat-card.present { background: #f0f9eb; border-color: #c2e7b0; }
.stat-card.absent  { background: #fef0f0; border-color: #fbc4c4; }
.stat-card.pct     { background: #ecf5ff; border-color: #b3d8ff; }
.stat-card.pct-excellent { background: #f0f9eb; border-color: #b3e19d; }
.stat-card.pct-bad       { background: #fef0f0; border-color: #fbc4c4; }
.stat-card.pct-warn      { background: #fdf6ec; border-color: #fcd29c; }
.stat-num { font-size: 26px; font-weight: 700; color: #303133; line-height: 1; }
.stat-card.present .stat-num { color: #67c23a; }
.stat-card.absent  .stat-num { color: #f56c6c; }
.stat-label { font-size: 11px; color: #909399; margin-top: 4px; }
.att-progress { margin-top: 4px; }

/* Table */
.subject-table { margin-bottom: 10px; }
.clr-excellent { color: #67c23a; font-weight: 700; }
.clr-good      { color: #409eff; font-weight: 600; }
.clr-warn      { color: #e6a23c; font-weight: 600; }
.clr-bad       { color: #f56c6c; font-weight: 700; }

.overall-row {
  display: flex; align-items: center; gap: 14px;
  background: #f5f7fa; border-radius: 8px;
  padding: 10px 16px;
  font-weight: 600;
}
.overall-label { flex: 1; font-size: 13px; }
.overall-pct   { font-size: 20px; }
.overall-tag   { font-size: 13px; }

/* Lang toggle */
.lang-toggle {
  margin-left: auto;
  display: flex;
  border: 1px solid #dcdfe6;
  border-radius: 6px;
  overflow: hidden;
}
.lang-btn {
  padding: 3px 12px;
  font-size: 12px;
  border: none;
  background: #fff;
  cursor: pointer;
  color: #606266;
  transition: all 0.2s;
  font-family: inherit;
}
.lang-btn:first-child { border-right: 1px solid #dcdfe6; }
.lang-btn.active {
  background: #409eff;
  color: #fff;
  font-weight: 600;
}
.lang-btn:not(.active):hover { background: #f5f7fa; }

/* Narrative */
.narrative-section { margin-top: 4px; }
.narrative-body {
  background: #f9fafb;
  border-left: 4px solid #409eff;
  border-radius: 0 8px 8px 0;
  padding: 14px 18px;
}
.narrative-body.narrative-rtl {
  border-left: none;
  border-right: 4px solid #409eff;
  border-radius: 8px 0 0 8px;
  direction: rtl;
  text-align: right;
  font-family: 'Noto Nastaliq Urdu', 'Jameel Noori Nastaleeq', 'Arial Unicode MS', sans-serif;
  font-size: 14px;
  line-height: 2.2;
}
.narrative-para {
  margin-bottom: 10px;
  line-height: 1.75;
  color: #4a4a4a;
  font-size: 13px;
}
.narrative-para:last-child { margin-bottom: 0; }
.para-title {
  font-size: 15px;
  font-weight: 700;
  color: #303133;
}

/* Footer */
.report-footer {
  display: flex; justify-content: space-between;
  font-size: 11px; color: #909399;
  margin-top: 16px; padding-top: 10px;
  border-top: 1px solid #ebeef5;
}

.no-data { text-align: center; padding: 20px 0; }
</style>
