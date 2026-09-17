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
        'address',
        'profession',
        'cnic',
        'user_id',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($parent) {
            // Capture plain password set directly on the instance, then remove it
            // so it is never written to the parents table
            $plainPassword = $parent->getAttributes()['password'] ?? null;
            unset($parent->password);

            $email = !empty($parent->email) ? $parent->email : $parent->phone . '@idlschool.pk';
            $user  = User::create([
                'name'     => $parent->name,
                'email'    => $email,
                'phone'    => $parent->phone,
                'password' => Hash::make($plainPassword ?? str_pad($parent->phone, 8, '0')),
            ]);
            $role = Role::findByName('parent');
            // assignRole, not syncRoles: never strip a role this account may
            // already hold (a teacher can also be a parent).
            $user->assignRole($role);
            $parent->user_id = $user->id;
        });

        static::updating(function ($parent) {
            if (!$parent->user_id) return;

            $user = User::find($parent->user_id);
            if (!$user) return;

            // An account shared with another role (a teacher who is also a
            // parent) must not have its identity rewritten from the parent
            // record — that would change the teacher's login email.
            $sharedRoles = $user->getRoleNames()->reject(fn ($name) => $name === 'parent');
            if ($sharedRoles->isNotEmpty()) return;

            $email      = !empty($parent->email) ? $parent->email : $parent->phone . '@idlschool.pk';
            $updateData = [
                'name'  => $parent->name,
                'email' => $email,
                'phone' => $parent->phone,
            ];

            // Allow callers to trigger a password reset by setting ->password on the parent
            $plainPassword = $parent->getAttributes()['password'] ?? null;
            if (!empty($plainPassword)) {
                $updateData['password'] = Hash::make($plainPassword);
                unset($parent->password);
            }

            $user->update($updateData);
        });
    }

    public function students()
    {
        return $this->hasMany(Student::class, 'parent_id', 'id')
            ->select(['id','name','parent_id','class_id','status'])
            ->where('status', '!=', 'disable')
            ->with('stdclasses:id,name');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
