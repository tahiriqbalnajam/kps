<?php

namespace App\Services\Contracts;

interface ExamServiceInterface
{
    public function listExams(array $params);
    public function storeExam(array $data);
    public function updateExam(int $id, array $data);
    public function deleteExam(int $id);
    public function getExamById(int $id);
    public function getExamSubjects(int $id);
    public function addExamMarks(array $params);
}
