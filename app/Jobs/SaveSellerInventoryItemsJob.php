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
     * Il servizio APIDataFetcherService per il salvataggio dei dati.
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
        $this->apiDataFetcherService = new APIDataFetcherService();
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
            'queue'  => $this->job->getQueue(),
        ], 'inventory');

        try {
            ResponseHandler::info('Starting seller inventory items data save process', [], 'inventory');

            $this->apiDataFetcherService->fetchAndSaveDataSellerInventoryItemsApi();

            ResponseHandler::success('Job SaveSellerInventoryItemsJob executed successfully.', [
                'job_id' => $this->job->getJobId(),
            ], 'inventory');
        } catch (\Exception $e) {
            ResponseHandler::error('Error executing SaveSellerInventoryItemsJob', [
                'error' => $e->getMessage(),
                'file'  => $e->getFile(),
                'line'  => $e->getLine(),
                'trace' => $e->getTraceAsString(),
            ], 'inventory');
        }
    }
}
