<?php
namespace App\Services;

use App\Models\Student;
use App\Models\StudentAttendance;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use App\Services\Contracts\StudentServiceInterface;
use Spatie\QueryBuilder\QueryBuilder;
use Spatie\QueryBuilder\AllowedFilter;
use Illuminate\Support\Arr;

class StudentService implements StudentServiceInterface
{
    const ITEM_PER_PAGE = 1000;

    public function listStudents(array $searchParams)
    {
        $limit = Arr::get($searchParams, 'limit', static::ITEM_PER_PAGE);
        return QueryBuilder::for(Student::class)
            ->with('parents', 'stdclasses', 'class_session')
            ->allowedFilters([
                'id', 'name', 'roll_no', 'adminssion_number', 'is_orphan', 'pef_admission', 'nadra_pending', 'gender',
                AllowedFilter::partial('parent_phone', 'parents.phone'),
                AllowedFilter::partial('parent_name', 'parents.name'),
                AllowedFilter::exact('stdclass', 'stdclasses.id'),
            ])
            ->paginate($limit)
            ->appends(request()->query());
    }

    public function storeStudent(array $data)
    {
        DB::beginTransaction();
        try {
            $user['name'] = $data['name'];
            $user['email'] = $data['name'].rand(10,100).'@idlschool.com';
            $user['password'] = bcrypt($data['password']);
            $user_id = User::create($user)->id;
            $data['user_id'] = $user_id;
            $student = Student::create($data);
            DB::commit();
            return $student;
        } catch (\Exception $e) {
            DB::rollback();
            throw $e;
        }
    }

    public function updateStudent(Student $student, array $data)
    {
        $student->fill($data)->save();
        return $student;
    }

    public function addAttendance(array $data)
    {
        $date = $data['date'];
        $students = $data['students'];
        foreach ($students as $studentData) {
            $attendance[] = [
                'class_id' => $studentData['class_id'],
                'student_id' => $studentData['id'],
                'status' => $studentData['attendance'],
                'created_at' => $date,
            ];
        }
        StudentAttendance::insert($attendance);
    }

    public function editClass(array $selectedStudents, $desiredClass)
    {
        foreach ($selectedStudents as $studentId) {
            Student::where('id', '=', $studentId)
                ->update(['class_id' => $desiredClass]);
        }
    }
}