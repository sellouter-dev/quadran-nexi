<?php

use App\Http\Controllers\LoginController;
use App\Http\Controllers\SellerInventoryItemsController;
use App\Http\Controllers\DownloadCSVController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
//     return $request->user();
// });

// Route::post('/login', [LoginController::class, 'login']);

Route::get('/seller-inventory-items', [SellerInventoryItemsController::class, 'getInventory']);
