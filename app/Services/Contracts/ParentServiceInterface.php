<?php
namespace App\Services\Contracts;

interface ParentServiceInterface
{
    public function createParent(array $data);
    public function findOrCreateParent(array $data);
    public function updateParent(int $id, array $data);
    public function cnicExists($cnic, $excludeId = null);
}