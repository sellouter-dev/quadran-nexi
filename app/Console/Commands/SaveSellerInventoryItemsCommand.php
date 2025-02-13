<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\ResponseHandler;
use App\Services\APIDataFetcherService;

class SaveSellerInventoryItemsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:save-seller-inventory-items-command';

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
        parent::__construct();
        $this->apiDataFetcherService = new APIDataFetcherService();
    }

    /**
     * Esegue il comando.
     *
     * @return int
     */
    public function handle()
    {
        ResponseHandler::info('Command data:save-seller-inventory started', [], 'info_log');

        try {
            // Avvio del processo di salvataggio
            $this->info('Starting seller inventory items data save process...');
            ResponseHandler::info('Starting seller inventory items data save process', [], 'info_log');

            $this->apiDataFetcherService->fetchAndSaveDataSellerInventoryItemsApi();

            // Completamento con successo
            $this->info('Seller inventory items data saved successfully.');
            ResponseHandler::success('Seller inventory items data saved successfully', [], 'success_log');

            return Command::SUCCESS;
        } catch (\Exception $e) {
            // Log dell'errore con dettagli
            $this->error('Error occurred while saving seller inventory items data.');
            ResponseHandler::error('Error executing data:save-seller-inventory command', [
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
            ], 'error_log');

            return Command::FAILURE;
        }
    }
}
