<?php

use App\Http\Controllers\LoginController;
use App\Http\Controllers\SellerInventoryItemsController;
use App\Http\Controllers\DownloadCSVController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::post('/login', [LoginController::class, 'login']);

Route::middleware('auth:sanctum')->get('/seller-inventory-items', [SellerInventoryItemsController::class, 'getInventory']);

// Rotte per il download dei dati delle transazioni e dei calcoli IVA
// Route::middleware('auth:sanctum')->get('/download/vat-transactions', [DownloadCSVController::class, 'downloadDataOfVatTransaction']);
// Route::middleware('auth:sanctum')->get('/download/vat-calculations', [DownloadCSVController::class, 'downloadDataOfVatCalculation']);
Route::middleware('auth:sanctum')->get('/download/vat-flat-file-invoice-data', [DownloadCSVController::class, 'downloadDataOfFlatfilevatinvoicedata']);
Route::middleware('auth:sanctum')->get('/download/vat-calculations-computed', [DownloadCSVController::class, 'downloadDataCalculationComputed']);
Route::middleware('auth:sanctum')->get('/download/vat-collections', [DownloadCSVController::class, 'downloadDataOfCollections']);
