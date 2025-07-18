<?php

namespace App\Services;

use App\Models\Parents;
use App\Services\Contracts\ParentServiceInterface;

class ParentService implements ParentServiceInterface
{
    /**
     * Create a new parent record
     *
     * @param array $data
     * @return Parents
     */
    public function createParent(array $data)
    {
        return Parents::create($data);
    }

    /**
     * Update an existing parent
     *
     * @param int $id
     * @param array $data
     * @return Parents
     */
    public function updateParent(int $id, array $data)
    {
        $parent = Parents::findOrFail($id);
        $parent->update($data);
        return $parent;
    }

    public function findOrCreateParent(array $data)
    {
        // Try to find existing parent by phone or CNIC
        $parent = Parents::where('phone', $data['phone'])
                         ->orWhere('cnic', $data['cnic'])
                         ->first();

        if (!$parent) {
            $parent = $this->createParent($data);
        }

        return $parent;
    }
}

