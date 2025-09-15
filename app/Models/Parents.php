<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class Parents extends Model
{
    protected $table = 'parents';

    protected $fillable = [
        'name',
        'phone',
        'address',
        'profession',
        'cnic',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($parent) {
            // Create user when parent is created
            $email = !empty($parent->email) ? $parent->email : $parent->phone . '@idlschool.pk';
            $user = User::create([
                'name' => $parent->name,
                'email' => $email,
                'password' => Hash::make($parent->password),
                'role' => 'parent',
            ]);
            $parent->user_id = $user->id;
        });

        static::updating(function ($parent) {
            // Update user when parent is updated
            if ($parent->user_id) {
                $user = User::find($parent->user_id);
                if ($user) {
                    $email = !empty($parent->email) ? $parent->email : $parent->phone . '@idlschool.pk';
                    $user->update([
                        'name' => $parent->name,
                        'email' => $email,
                        'password' => Hash::make($parent->password),
                    ]);
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
