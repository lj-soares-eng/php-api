<?php

use App\Http\Controllers\Api\OpenApiController;
use App\Http\Controllers\Api\UserController;
use Illuminate\Support\Facades\Route;

Route::get('/schema', [OpenApiController::class, 'schema']);
Route::get('/docs', [OpenApiController::class, 'docs']);

Route::get('/users', [UserController::class, 'index']);
Route::post('/users', [UserController::class, 'store']);
Route::get('/users/{id}', [UserController::class, 'show'])->whereNumber('id');
Route::put('/users/{id}', [UserController::class, 'update'])->whereNumber('id');
Route::delete('/users/{id}', [UserController::class, 'destroy'])->whereNumber('id');
