<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\RecommendationController;
use App\Http\Controllers\Api\AuthController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// Auth routes
Route::post('/login', [AuthController::class, 'login']);
Route::post('/register', [AuthController::class, 'register']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', function (Request $request) {
        return $request->user();
    });
});

Route::apiResource('products', ProductController::class);

// Recommendation Routes
Route::prefix('recommendations')->group(function () {
    Route::get('/', [RecommendationController::class, 'getRecommendations']);
    Route::get('/trending', [RecommendationController::class, 'getTrendingProducts']);
    Route::get('/similar/{product}', [RecommendationController::class, 'getSimilarProducts']);
    Route::get('/search-based', [RecommendationController::class, 'getSearchBasedRecommendations']);
    
    // User preference and feedback routes (require authentication)
    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/preferences', [RecommendationController::class, 'updatePreferences']);
        Route::post('/{product}/feedback', [RecommendationController::class, 'recordFeedback']);
    });
}); 