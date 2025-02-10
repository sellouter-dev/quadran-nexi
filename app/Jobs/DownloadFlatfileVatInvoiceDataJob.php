<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use App\Services\ResponseHandler;
use App\Services\CSVGeneratorService;
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
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        ResponseHandler::info('Job DownloadFlatfileVatInvoiceDataJob started', [
            'job_id' => $this->job->getJobId(),
            'queue'  => $this->job->getQueue(),
        ], 'sellouter');

        try {
            ResponseHandler::info('Starting flatfile VAT invoice data download', [], 'sellouter');

            $this->apiDataFetcherService->fetchAndStoreFlatfileVatData();
            $this->csvDataGeneratorService->generateFlatfileVatCSV();

            ResponseHandler::success('Job DownloadFlatfileVatInvoiceDataJob executed successfully.', [
                'job_id' => $this->job->getJobId(),
            ], 'sellouter');
        } catch (\Exception $e) {
            ResponseHandler::error('Error executing DownloadFlatfileVatInvoiceDataJob', [
                'error' => $e->getMessage(),
                'file'  => $e->getFile(),
                'line'  => $e->getLine(),
                'trace' => $e->getTraceAsString(),
            ], 'sellouter');
        }
    }
}
