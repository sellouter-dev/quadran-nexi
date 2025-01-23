<?php

namespace App\Jobs;

use App\Services\CSVGeneratorService;
use App\Services\ResponseHandler;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class DownloadDataCalculationComputedJob implements ShouldQueue
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
        ResponseHandler::info('Job DownloadDataCalculationComputedJob started', [
            'job_id' => $this->job->getJobId(),
            'queue' => $this->job->getQueue(),
        ], 'info_log');

        try {
            // Step 1: Avvio del processo di download
            ResponseHandler::info('Starting data calculation download', [], 'info_log');

            $this->csvGeneratorService->downloadDataCalculationComputed();

            // Step 2: Job completato con successo
            ResponseHandler::success('Job DownloadDataCalculationComputedJob executed successfully.', [
                'job_id' => $this->job->getJobId(),
            ], 'success_log');
        } catch (\Exception $e) {
            // Step 3: Gestione dell'errore
            ResponseHandler::error('Error executing DownloadDataCalculationComputedJob', [
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
            ], 'error_log');
        }
    }
}
