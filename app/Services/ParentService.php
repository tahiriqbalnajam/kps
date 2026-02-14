<?php

namespace App\Services;

use App\Models\Parents;
use App\Services\Contracts\ParentServiceInterface;
use Illuminate\Validation\ValidationException;

class ParentService implements ParentServiceInterface
{
    /**
     * Normalize CNIC by removing dashes and spaces
     *
     * @param string $cnic
     * @return string
     */
    private function normalizeCnic($cnic)
    {
        return str_replace(['-', ' '], '', $cnic);
    }

    /**
     * Check if CNIC already exists (excluding a specific parent ID)
     *
     * @param string $cnic
     * @param int|null $excludeId
     * @return bool
     */
    public function cnicExists($cnic, $excludeId = null)
    {
        $normalizedCnic = $this->normalizeCnic($cnic);
        
        $query = Parents::whereRaw("REPLACE(REPLACE(cnic, '-', ''), ' ', '') = ?", [$normalizedCnic]);
        
        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }
        
        return $query->exists();
    }

    /**
     * Create a new parent record
     *
     * @param array $data
     * @return Parents
     * @throws ValidationException
     */
    public function createParent(array $data)
    {
        // Check if CNIC already exists
        if (isset($data['cnic']) && $this->cnicExists($data['cnic'])) {
            throw ValidationException::withMessages([
                'cnic' => ['This CNIC is already registered in the system.']
            ]);
        }

        return Parents::create($data);
    }

    /**
     * Update an existing parent
     *
     * @param int $id
     * @param array $data
     * @return Parents
     * @throws ValidationException
     */
    public function updateParent(int $id, array $data)
    {
        $parent = Parents::findOrFail($id);
        
        // Check if CNIC already exists (excluding current parent)

        if (isset($data['cnic']) && $this->normalizeCnic($data['cnic']) !== $this->normalizeCnic($parent->cnic ?? '')) {
            if ($this->cnicExists($data['cnic'], $id)) {
                throw ValidationException::withMessages([
                    'cnic' => ['This CNIC is already registered to another parent.']
                ]);
            }
        }
        
        $parent->update($data);
        return $parent;
    }

    public function findOrCreateParent(array $data)
    {
        // Normalize CNIC for comparison
        $normalizedCnic = isset($data['cnic']) ? $this->normalizeCnic($data['cnic']) : null;
        
        // Try to find existing parent by phone or normalized CNIC
        $parent = Parents::where('phone', $data['phone'])
                         ->orWhereRaw("REPLACE(REPLACE(cnic, '-', ''), ' ', '') = ?", [$normalizedCnic])
                         ->first();

        if (!$parent) {
            $parent = $this->createParent($data);
        }

        return $parent;
    }
}

