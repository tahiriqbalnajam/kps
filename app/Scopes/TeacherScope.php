<?php namespace App\Scopes;

use Illuminate\Database\Eloquent\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class TeacherScope implements Scope{

  public function apply(Builder $builder, Model $model)
  {
    // $builder->whereHas('roles', function ($q) {
    //     $q->where('name', 'teacher');
    // });
  }

  public function remove(Builder $builder, Model $model){}

}
