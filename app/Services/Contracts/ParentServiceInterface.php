<?php
namespace App\Services\Contracts;

use App\Models\Student;

interface StudentServiceInterface
{
    public function createParent(array $data);
    public function findOrCreateParent(array $data);
    public function updateParent(int $id, array $data);
}