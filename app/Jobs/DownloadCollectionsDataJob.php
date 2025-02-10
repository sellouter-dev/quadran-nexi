<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use App\Services\ResponseHandler;
use App\Services\CsvDataGeneratorService;
use Illuminate\Queue\SerializesModels;
use App\Services\APIDataFetcherService;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class DownloadCollectionsDataJob implements ShouldQueue
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
        ResponseHandler::info('Job DownloadCollectionsDataJob started', [
            'job_id' => $this->job->getJobId(),
            'queue'  => $this->job->getQueue(),
        ], 'sellouter');

        try {
            // Step 1: Avvio del download dei dati
            ResponseHandler::info('Starting data download from API', [], 'sellouter');
            $this->apiDataFetcherService->fetchAndStoreCollectionData();
            $this->csvDataGeneratorService->generateCollectionCSV();

            // Step 2: Completamento del job con successo
            ResponseHandler::success('Job DownloadCollectionsDataJob executed successfully.', [
                'job_id' => $this->job->getJobId(),
            ], 'sellouter');
        } catch (\Exception $e) {
            // Log dell'errore con dettagli
            ResponseHandler::error('Error executing DownloadCollectionsDataJob', [
                'error' => $e->getMessage(),
                'file'  => $e->getFile(),
                'line'  => $e->getLine(),
                'trace' => $e->getTraceAsString(),
            ], 'sellouter');
        }
    }
}
