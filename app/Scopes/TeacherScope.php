<?php namespace App\Scopes;

use Illuminate\Database\Eloquent\ScopeInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class TeacherScope implements ScopeInterface{

  public function apply(Builder $builder, Model $model)
  {
    $builder->where('type', '=', 'teacher');
  }

  public function remove(Builder $builder, Model $model){}

}
