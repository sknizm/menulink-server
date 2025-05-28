<?php


use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\RestaurantController;
use App\Http\Controllers\MenuController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\FileUploadController;

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

// Public
Route::get('/restaurant/{slug}', [RestaurantController::class, 'publicRestaurantData']);

// Category
Route::get('/menu/categories', [CategoryController::class, 'index'])->middleware('auth:sanctum');
Route::post('/menu/categories', [CategoryController::class, 'store'])->middleware('auth:sanctum');

// Menu Items

Route::get('/menu/allmenu', [MenuController::class, 'allMenu'])->middleware('auth:sanctum');
Route::post('/menu/items', [MenuController::class, 'store'])->middleware('auth:sanctum');
Route::put('/menu/items/{id}', [MenuController::class, 'update'])->middleware('auth:sanctum');
Route::get('/menu/items/{id}', [MenuController::class, 'show'])->middleware('auth:sanctum');

Route::delete('/menu/categories/{id}', [MenuController::class, 'deleteCategory'])->middleware('auth:sanctum');
Route::delete('/menu/items/{id}', [MenuController::class, 'deleteMenuItem'])->middleware('auth:sanctum');

// Upload routes

Route::post('/upload', [FileUploadController::class, 'upload'])->middleware('auth:sanctum');
Route::delete('/delete-image', [FileUploadController::class, 'delete'])->middleware('auth:sanctum');
