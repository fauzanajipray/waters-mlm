<?php

use App\Http\Controllers\Admin\CustomerCrudController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::get('/members', [App\Http\Controllers\Api\MemberController::class, 'index']);
Route::get('/members/not-activated', [App\Http\Controllers\Api\MemberController::class, 'notActivated']);
Route::get('/members/only-actived', [App\Http\Controllers\Api\MemberController::class, 'onlyActive']);