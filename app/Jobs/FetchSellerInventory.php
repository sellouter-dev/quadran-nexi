<?php

namespace App\Jobs;

use App\Models\CustomerCredential;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Services\InventoryService;

class FetchSellerInventory implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $credential;

    /**
     * Create a new job instance.
     */
    public function __construct(CustomerCredential $credential)
    {
        $this->credential = $credential;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        // Fetch the seller inventory
        $inventoryService = new InventoryService();

        try {
            $inventoryService->fetchInventory($this->credential);
        } catch (\Exception $e) {
            \Log::error($e->getMessage());
        }
    }
}
