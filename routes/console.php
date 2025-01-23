<?php

use App\Jobs\DownloadCollectionsDataJob;
use Illuminate\Support\Facades\Schedule;
use App\Jobs\SaveSellerInventoryItemsJob;
use App\Jobs\DownloadFlatfileVATInvoiceDataJob;
use App\Jobs\DownloadDataCalculationComputedJob;

Schedule::call(function () {
    dispatch(new DownloadFlatfileVATInvoiceDataJob());
})->dailyAt("14:48");

Schedule::call(function () {
    dispatch(new DownloadDataCalculationComputedJob());
})->dailyAt("10:03");


Schedule::call(function () {
    dispatch(new DownloadCollectionsDataJob());
})->dailyAt("10:04");


Schedule::call(function () {
    dispatch(new SaveSellerInventoryItemsJob());
})->dailyAt("10:05");
