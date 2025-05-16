<?php


use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\RestaurantController;

// Auth Route
Route::post('/signup', [AuthController::class, 'signup']);
Route::post('/signin', [AuthController::class, 'signin']);
Route::post('/signout', [AuthController::class, 'signout'])->middleware('auth:sanctum');

// User Route

Route::get('/user-data', [UserController::class, 'getUser'])->middleware('auth:sanctum');

// Restaurant Route
Route::get('/restaurant-exist', [RestaurantController::class, 'restaurantExist'])->middleware('auth:sanctum');
Route::post('/create-restaurant', [RestaurantController::class, 'createRestaurant'])->middleware('auth:sanctum');
Route::get('/check-slug/{slug}', [RestaurantController::class, 'checkSlug']);
Route::get('/get-slug', [RestaurantController::class, 'getSlugForAuthenticatedUser'])->middleware('auth:sanctum');
Route::get('/restaurant-by-user', [RestaurantController::class, 'getRestaurantByUser'])->middleware('auth:sanctum');
Route::put('/update-restaurant', [RestaurantController::class, 'updateRestaurant'])->middleware('auth:sanctum');
