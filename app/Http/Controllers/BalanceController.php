<?php

namespace App\Http\Controllers;

use App\Models\Balance;
use Illuminate\Support\Arr;
use Illuminate\Http\Request;
use App\Laravue\JsonResponse;

class BalanceController extends Controller
{
    const ITEM_PER_PAGE = 1000;
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $searchParams = $request->all();
        $keyword = $request->keyword;
        $limit = Arr::get($searchParams, 'limit', static::ITEM_PER_PAGE);
        $accounts = Balance::with(['accounts' => function($query) use ($keyword) {
            $query->when($keyword, function ($q) use($keyword) {
                return $q->where('accounts.name', $keyword);
            });
        }])->paginate($limit);
        return response()->json(new JsonResponse(['balances' =>  $accounts]));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
