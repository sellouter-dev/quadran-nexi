<?php

use Illuminate\Support\Facades\Schedule;
use App\Jobs\DownloadFlatfileVATInvoiceDataJob;

Schedule::call(function () {
    dispatch(new DownloadFlatfileVATInvoiceDataJob());
})->dailyAt("11:02");
