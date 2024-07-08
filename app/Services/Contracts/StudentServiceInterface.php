<?php
namespace App\Services\Contracts;

use App\Models\Student;

interface StudentServiceInterface
{
    public function listStudents(array $searchParams);
    public function storeStudent(array $data);
    public function updateStudent(Student $student, array $data);
    public function addAttendance(array $data);
    public function editClass(array $selectedStudents, $desiredClass);
    public function getSubjectWiseScores(int $studentid);
}