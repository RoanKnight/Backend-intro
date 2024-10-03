<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\SupplierController;
use App\Http\Controllers\API\ProductController;

Route::controller(AuthController::class)->group(function(){
    Route::post('register', 'register');
    Route::post('login', 'login');
});

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::middleware('auth:sanctum')->group( function () {
    Route::apiResource('suppliers', SupplierController::class)->missing(function (Request $request) {
        $response = [
            'success' => false,
            'message' => 'Supplier not found.'
        ];
        
        return response()->json($response, 404);
    });
    Route::apiResource('products', ProductController::class)->missing(function (Request $request) {
        $response = [
            'success' => false,
            'message' => 'Product not found.'
        ];
        
        return response()->json($response, 404);
    });
});
