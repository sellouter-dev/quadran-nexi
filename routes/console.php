<?php

use Carbon\Carbon;
use App\Jobs\FetchSellerInventory;
use App\Models\CustomerCredential;
use Illuminate\Support\Facades\Schedule;
use App\Jobs\DownloadFlatfileVATInvoiceDataJob;

// CustomerCredential::seller()
//     ->active()
//     ->get()
//     ->each(function (CustomerCredential $credential) {
//         Schedule::job(new FetchSellerInventory($credential))->everyFourHours();
    // });
// Schedule::call(function () {
//     dispatch(new DownloadFlatfileVATInvoiceDataJob());
// })->dailyAt("11:02");