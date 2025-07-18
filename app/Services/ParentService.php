<?php

namespace App\Services;

use App\Models\Parents;
use Illuminate\Support\Facades\Hash;

class ParentService
{
    /**
     * Create a new parent record
     *
     * @param array $data
     * @return Parents
     */
    public function createParent(array $data)
    {
        $parent = new Parents();
        $parent->name = $data['name'];
        $parent->phone = $data['phone'];
        $parent->address = $data['address'];
        $parent->profession = $data['profession'];
        $parent->cnic = $data['cnic'];
        $parent->save();
        
        return $parent;
    }

    /**
     * Find or create a parent by phone or CNIC
     *
     * @param array $data
     * @return Parents
     */
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

    /**
     * Update an existing parent
     *
     * @param int $id
     * @param array $data
     * @return Parents
     */
    public function updateParent($id, array $data)
    {
        $parent = Parents::find($id);
        
        if ($parent) {
            $parent->name = $data['name'];
            $parent->phone = $data['phone'];
            $parent->address = $data['address'];
            $parent->profession = $data['profession'];
            $parent->cnic = $data['cnic'];
            $parent->save();
        }
        
        return $parent;
    }
}
