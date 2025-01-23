<?php

namespace App\Jobs;

use App\Services\CSVGeneratorService;
use App\Services\ResponseHandler;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class DownloadCollectionsDataJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $csvGeneratorService;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->csvGeneratorService = new CSVGeneratorService();
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        ResponseHandler::info('Job DownloadCollectionsDataJob started', [
            'job_id' => $this->job->getJobId(),
            'queue' => $this->job->getQueue(),
        ]);

        try {
            // Step 1: Avvio del download dei dati
            ResponseHandler::info('Starting data download from API', [], 'info_log');
            $this->csvGeneratorService->downloadDataOfCollections();

            // Step 2: Completamento del job con successo
            ResponseHandler::success('Job DownloadCollectionsDataJob executed successfully.', [
                'job_id' => $this->job->getJobId(),
            ], 'success_log');
        } catch (\Exception $e) {
            // Log dell'errore con dettagli
            ResponseHandler::error('Error executing DownloadCollectionsDataJob', [
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
            ], 'error_log');
        }
    }
}
