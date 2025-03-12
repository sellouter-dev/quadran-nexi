<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use App\Services\ResponseHandler;
use Illuminate\Queue\SerializesModels;
use App\Services\APIDataFetcherService;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class DownloadFlatfileVatDataJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Il servizio APIDataFetcherService per il recupero dei dati.
     *
     * @var APIDataFetcherService
     */
    protected $apiDataFetcherService;

    /**
     * Crea una nuova istanza del job.
     *
     * @return void
     */
    public function __construct(APIDataFetcherService $apiDataFetcherService)
    {
        $this->apiDataFetcherService = $apiDataFetcherService;
    }

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
            'sellouter-info'
        );

        try {
            // Avvio del download dei dati dall'API
            ResponseHandler::info('Avvio del download dei dati dall\'API', [], 'sellouter-info');

            // Chiamata alla funzione desiderata
            $this->apiDataFetcherService->fetchAndStoreFlatfileVatData();

            // Completamento del job con successo
            ResponseHandler::success(
                'Job DownloadFlatfileVatDataJob eseguito con successo.',
                [],
                'sellouter-success'
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
                'sellouter-error'
            );
        }
    }
}
