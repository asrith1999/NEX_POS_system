<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\OrderController;
use Illuminate\Http\Request;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/api/csrf', fn () => response()->json(['token' => csrf_token()]));
Route::post('/api/login', [AuthController::class, 'login'])->middleware('throttle:10,1');
Route::middleware('auth')->group(function () {
    Route::get('/api/me', fn (Request $request) => $request->user());
    Route::post('/api/logout', [AuthController::class, 'logout']);
    Route::get('/api/products', [ProductController::class, 'index']);
    Route::post('/api/orders', [OrderController::class, 'store']);
});
