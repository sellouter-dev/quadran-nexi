<?php

use App\Jobs\DownloadCollectionsDataJob;
use Illuminate\Support\Facades\Schedule;
use App\Jobs\SaveSellerInventoryItemsJob;
use App\Jobs\DownloadFlatfileVATInvoiceDataJob;
use App\Jobs\DownloadDataCalculationComputedJob;
use Illuminate\Support\Facades\File;

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

// Task per eliminare i log più vecchi di 90 giorni ogni 90 giorni
Schedule::call(function () {
    $logPath = storage_path('logs');

    if (File::exists($logPath)) {
        $files = File::files($logPath);

        foreach ($files as $file) {
            if (now()->diffInDays($file->getMTime()) >= 90) {
                File::delete($file);
            }
        }
    }
})->dailyAt("10:06");
