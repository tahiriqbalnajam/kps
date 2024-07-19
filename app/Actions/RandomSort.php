<?php
namespace App\Actions;
use Illuminate\Database\Eloquent\Builder;
use Spatie\QueryBuilder\Sorts\Sort;
class RandomSort implements Sort
{
    public function __invoke(Builder $query, bool $descending, string $property)
    {

        $query->inRandomOrder();
    }
}