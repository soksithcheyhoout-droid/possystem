<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\ProductApiController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

// Product API Routes
Route::prefix('products')->group(function () {
    // Get products
    Route::get('/', [ProductApiController::class, 'index']);
    Route::get('/search', [ProductApiController::class, 'search']);
    Route::get('/low-stock', [ProductApiController::class, 'lowStock']);
    Route::get('/stats', [ProductApiController::class, 'stats']);
    Route::get('/category/{categoryId}', [ProductApiController::class, 'getByCategory']);
    Route::get('/{id}', [ProductApiController::class, 'show']);
    
    // Delete products
    Route::delete('/{id}', [ProductApiController::class, 'destroy']);
    Route::delete('/bulk', [ProductApiController::class, 'bulkDestroy']);
    Route::patch('/{id}/deactivate', [ProductApiController::class, 'softDestroy']);
});