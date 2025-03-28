<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use App\Services\ResponseHandler;
use App\Services\APIDataFetcherService;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class FetchInvoiceDataVidrJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $apiDataFetcherService;


    public function handle()
    {
        ResponseHandler::info(
            'Job FetchVatInvoiceDataJob avviato',
            [
                'job_id' => $this->job->getJobId(),
                'queue'  => $this->job->getQueue(),
            ],
            'sellouter'
        );

        try {
            ResponseHandler::info('Avvio del download dei dati Flatfile VAT Invoice', [], 'sellouter');
            $this->apiDataFetcherService = new APIDataFetcherService();
            $this->apiDataFetcherService->fetchAndStoreInvoiceDataVidr();

            ResponseHandler::success(
                'FetchVatInvoiceDataJob completato con successo.',
                [
                    'job_id' => $this->job->getJobId(),
                ],
                'sellouter'
            );
        } catch (\Exception $e) {
            ResponseHandler::error(
                'Errore nel FetchVatInvoiceDataJob',
                [
                    'errore' => $e->getMessage(),
                    'file'   => $e->getFile(),
                    'linea'  => $e->getLine(),
                    'trace'  => $e->getTraceAsString(),
                ],
                'sellouter'
            );
        }
    }
}
