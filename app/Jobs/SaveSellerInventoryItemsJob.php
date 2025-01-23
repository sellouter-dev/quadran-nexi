<?php

namespace App\Jobs;

use App\Services\CSVGeneratorService;
use App\Services\ResponseHandler;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SaveSellerInventoryItemsJob implements ShouldQueue
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
        ResponseHandler::info('Job SaveSellerInventoryItemsJob started', [
            'job_id' => $this->job->getJobId(),
            'queue' => $this->job->getQueue(),
        ], 'info_log');

        try {
            ResponseHandler::info('Starting seller inventory items data save process', [], 'info_log');

            $this->csvGeneratorService->saveDataSellerInventoryItemsApi();

            ResponseHandler::success('Job SaveSellerInventoryItemsJob executed successfully.', [
                'job_id' => $this->job->getJobId(),
            ], 'success_log');
        } catch (\Exception $e) {
            ResponseHandler::error('Error executing SaveSellerInventoryItemsJob', [
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
            ], 'error_log');
        }
    }
}
