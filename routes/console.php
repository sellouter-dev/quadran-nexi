<?php

use Illuminate\Support\Facades\Schedule;
use App\Jobs\SaveSellerInventoryItemsJob;

Schedule::call(function () {
    dispatch(new SaveSellerInventoryItemsJob())->onConnection("database");
})->dailyAt("00:01");
