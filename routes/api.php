<?php

use App\Http\Controllers\SellerInventoryItemsController;
use Illuminate\Support\Facades\Route;

Route::get('/seller-inventory-items', [SellerInventoryItemsController::class, 'getInventory']);
