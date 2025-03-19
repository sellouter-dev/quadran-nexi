<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use App\Services\ResponseHandler;
use Illuminate\Queue\SerializesModels;
use App\Services\APIDataFetcherService;
use Illuminate\Queue\InteractsWithQueue;
use App\Services\CsvDataGeneratorService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class DownloadFlatfileVatInvoiceDataJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Il servizio CsvDataGeneratorService per la gestione dei dati.
     *
     * @var CsvDataGeneratorService
     */
    protected $csvDataGeneratorService;

    /**
     * Il servizio APIDataFetcherService per il salvataggio dei dati.
     *
     * @var APIDataFetcherService
     */
    protected $apiDataFetcherService;

    /**
     * Crea una nuova istanza del comando.
     *
     * @return void
     */
    public function __construct()
    {
        $this->csvDataGeneratorService = new CsvDataGeneratorService();
        $this->apiDataFetcherService = new APIDataFetcherService();
    }

    /**
     * Esegue il job.
     *
     * @return void
     */
    public function handle()
    {
        ResponseHandler::info(
            'Job DownloadFlatfileVatInvoiceDataJob avviato',
            [
                'job_id' => $this->job->getJobId(),
                'queue'  => $this->job->getQueue(),
            ],
            'sellouter'
        );

        try {
            ResponseHandler::info(
                'Avvio del download dei dati Flatfile VAT Invoice',
                [],
                'sellouter'
            );

            $this->apiDataFetcherService->fetchAndStoreInvoiceData();
            $this->csvDataGeneratorService->generateFlatfileVatCSV();

            ResponseHandler::success(
                'Job DownloadFlatfileVatInvoiceDataJob eseguito con successo.',
                [
                    'job_id' => $this->job->getJobId(),
                ],
                'sellouter'
            );
        } catch (\Exception $e) {
            ResponseHandler::error(
                'Errore durante l\'esecuzione del job DownloadFlatfileVatInvoiceDataJob',
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
