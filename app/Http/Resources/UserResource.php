<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request $request
     * @return array
     */
    public function toArray($request)
    {
        $data = [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'status' => $this->status,
            'avatar' => $this->avatar,
            //'sex' => $this->sex,
            //'sex_format' => $this->sex_format,
            'age' => $this->age,
            'birthday' => $this->birthday,
            'roles' => array_map(
                function ($role) {
                    return $role['name'];
                },
                $this->roles->toArray()
            ),
            'permissions' => array_map(
                function ($permission) {
                    return $permission['name'];
                },
                $this->getAllPermissions()->toArray()
            )
        ];

        // Add student information if user has student role
        if ($this->hasRole('student')) {
            $data['student'] = $this->when($this->student, [
                'student_id' => $this->student->id ?? null,
                'roll_no' => $this->student->roll_no ?? null,
                'name' => $this->student->name ?? null,
                'grade' => $this->student->grade ?? null,
                'enrollment_date' => $this->student->enrollment_date ?? null,
            ]);
        }

        return $data;
    }
}
