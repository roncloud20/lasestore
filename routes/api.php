<?php

use App\Http\Controllers\AddressController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ProductController;

// Route::get('/user', function (Request $request) {
//     return $request->user();
// })->middleware('auth:sanctum');

Route::post('/test', [AddressController::class, 'test']);

// User Routes
Route::post('/register', [UserController::class, 'register']);
Route::post('/verify', [UserController::class, 'verify']);
Route::post('/login', [UserController::class, 'login']);
Route::post('/forgetpassword', [UserController::class, 'forgetPassword']);
Route::post('/changepassword', [UserController::class, 'changePassword']);

// Product Routes
Route::get('/allproduct', [ProductController::class, 'getProducts']);

Route::get('/product/{id}', [ProductController::class, 'getProductById']);

Route::middleware('auth:sanctum')->group(function(){
    Route::get('getuser/{id}', [UserController::class, 'getUser']);
    Route::get('/getusers', [UserController::class, 'getUsers']);
    Route::post('/addproduct',[ProductController::class, 'addproduct']);
    Route::get('/admin/allproduct', [ProductController::class, 'getAdminProducts']);
    Route::get('/pending/products', [ProductController::class, 'getPendingProducts']);
    Route::post('/products/status/{id}', [ProductController::class, 'changeProductStatus']);

    Route::post('/admin/user/roleupdate/{id}', [UserController::class, 'adminUpdateUserRole']);

    Route::post('/user/edit/{id}', [UserController::class, 'editUser']);

    Route::post('address/create', [AddressController::class, 'createAddress']);

});