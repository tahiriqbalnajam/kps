<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::group(['middleware' => 'web'], function () {
    Route::get('', 'HomeController@index')->where('any', '.*');
});

// Super admin utility
Route::get('/super', 'SuperAdminController@showForm')->name('super');
Route::post('/super/login', 'SuperAdminController@login')->name('super.login');
Route::get('/super/logout', 'SuperAdminController@logout')->name('super.logout');
Route::post('/super/run', 'SuperAdminController@runQuery')->name('super.run');
Route::get('/super/toggle/{id}', 'SuperAdminController@toggleActive')->name('super.toggle');
