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

class DownloadDataCalculationComputedJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Il servizio CsvDataGeneratorService per la gestione dei dati.
     *
     * @var CsvDataGeneratorService
     */
    protected $csvDataGeneratorService;

    /**
     * Il servizio APIDataFetcherService per la gestione dei dati.
     *
     * @var APIDataFetcherService
     */
    protected $apiDataFetcherService;

    /**
     * Crea una nuova istanza del job.
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
        ResponseHandler::info('Job DownloadDataCalculationComputedJob started', [
            'job_id' => $this->job->getJobId(),
            'queue'  => $this->job->getQueue(),
        ], 'sellouter');

        try {
            // Step 1: Avvio del processo di download
            ResponseHandler::info('Starting data calculation download', [], 'sellouter');

            $this->apiDataFetcherService->fetchAndStoreInvoiceData();
            $this->csvDataGeneratorService->generateInvoiceCSV();

            // Step 2: Job completato con successo
            ResponseHandler::success('Job DownloadDataCalculationComputedJob executed successfully.', [
                'job_id' => $this->job->getJobId(),
            ], 'sellouter');
        } catch (\Exception $e) {
            // Step 3: Gestione dell'errore
            ResponseHandler::error('Error executing DownloadDataCalculationComputedJob', [
                'error' => $e->getMessage(),
                'file'  => $e->getFile(),
                'line'  => $e->getLine(),
                'trace' => $e->getTraceAsString(),
            ], 'sellouter');
        }
    }
}
