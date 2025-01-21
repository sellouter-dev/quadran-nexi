<?php

namespace App\Jobs;

use App\Services\CSVGeneratorService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

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
        try {
            $this->csvGeneratorService->downloadDataOfCollections();
            Log::info('Job DownloadCollectionsDataJob executed successfully.');
        } catch (\Exception $e) {
            Log::error('Error executing DownloadCollectionsDataJob: ' . $e->getMessage());
        }
    }
}
