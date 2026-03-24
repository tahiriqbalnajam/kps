<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Role;

class Parents extends Model
{
    protected $table = 'parents';

    protected $fillable = [
        'name',
        'phone',
        'password',
        'address',
        'profession',
        'cnic',
        'user_id',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($parent) {
            // Create user when parent is created
            $email = !empty($parent->email) ? $parent->email : $parent->phone . '@idlschool.pk';
            $user = User::create([
                'name'     => $parent->name,
                'email'    => $email,
                'password' => Hash::make($parent->password),
            ]);
            $role = Role::findByName('parent');
            $user->syncRoles($role);
            $parent->user_id = $user->id;
        });

        static::updating(function ($parent) {
            // Update user when parent is updated
            if ($parent->user_id) {
                $user = User::find($parent->user_id);
                if ($user) {
                    $email = !empty($parent->email) ? $parent->email : $parent->phone . '@idlschool.pk';
                    $updateData = [
                        'name' => $parent->name,
                        'email' => $email,
                    ];
                    if (!empty($parent->password)) {
                        $updateData['password'] = Hash::make($parent->password);
                    }
                    $user->update($updateData);
                }
            }
        });
    }

    public function students()
    {
        return $this->hasMany(Student::class, 'parent_id', 'id')
            ->select(['id','name','parent_id','class_id'])
            ->with('stdclasses:id,name');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
