<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use App\Services\ResponseHandler;
use Illuminate\Queue\SerializesModels;
use App\Services\APIDataFetcherService;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class SaveSellerInventoryItemsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Esegue il job.
     *
     * @return void
     */
    public function handle()
    {
        ResponseHandler::info(
            'Job SaveSellerInventoryItemsJob avviato',
            [
                'job_id' => $this->job->getJobId(),
                'queue'  => $this->job->getQueue(),
            ],
            'sellouter'
        );

        try {
            ResponseHandler::info(
                'Avvio del processo di salvataggio dei dati degli Seller Inventory Items',
                [],
                'sellouter'
            );

            // Istanzia il servizio direttamente nel metodo handle()
            $apiDataFetcherService = new APIDataFetcherService();
            $apiDataFetcherService->fetchAndSaveDataSellerInventoryItemsApi();

            ResponseHandler::success(
                'Job SaveSellerInventoryItemsJob eseguito con successo.',
                [
                    'job_id' => $this->job->getJobId(),
                ],
                'sellouter'
            );
        } catch (\Exception $e) {
            ResponseHandler::error(
                'Errore durante l\'esecuzione del job SaveSellerInventoryItemsJob',
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
