<?php

use Illuminate\Support\Facades\Schedule;
use App\Jobs\SaveSellerInventoryItemsJob;
use App\Services\ResponseHandler;

Schedule::call(function () {
    ResponseHandler::info("Starting SaveSellerInventoryItemsJob at " . now()->toIso8601String());
    dispatch(new SaveSellerInventoryItemsJob())->onConnection("database");
})->dailyAt("16:18");
