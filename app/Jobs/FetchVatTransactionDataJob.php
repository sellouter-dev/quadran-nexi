<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use App\Services\ResponseHandler;
use Illuminate\Queue\SerializesModels;
use App\Services\APIDataFetcherService;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class FetchVatTransactionDataJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Il servizio APIDataFetcherService per il recupero dei dati.
     *
     * @var APIDataFetcherService
     */
    protected $apiDataFetcherService;

    /**
     * Esegue il job.
     *
     * @return void
     */
    public function handle()
    {
        ResponseHandler::info(
            'Job DownloadFlatfileVatDataJob avviato',
            [],
            'sellouter'
        );

        try {
            // Avvio del download dei dati dall'API
            ResponseHandler::info('Avvio del download dei dati dall\'API', [], 'sellouter');
            $this->apiDataFetcherService = new APIDataFetcherService();
            // Chiamata alla funzione desiderata
            $this->apiDataFetcherService->fetchAndStoreVatTransactionData();

            // Completamento del job con successo
            ResponseHandler::success(
                'Job DownloadFlatfileVatDataJob eseguito con successo.',
                [],
                'sellouter'
            );
        } catch (\Exception $e) {
            // Log dell'errore con dettagli
            ResponseHandler::error(
                'Errore durante l\'esecuzione del job DownloadFlatfileVatDataJob',
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
