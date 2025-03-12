<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\ResponseHandler;
use App\Services\APIDataFetcherService;

class DownloadFlatfileVatDataCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:download-flatfile-vat-data';

    /**
     * Il servizio APIDataFetcherService per il recupero dei dati.
     *
     * @var APIDataFetcherService
     */
    protected $apiDataFetcherService;

    /**
     * Crea una nuova istanza del comando.
     *
     * @return void
     */
    public function __construct(APIDataFetcherService $apiDataFetcherService)
    {
        parent::__construct();
        $this->apiDataFetcherService = $apiDataFetcherService;
    }

    /**
     * Esegue il comando.
     *
     * @return int
     */
    public function handle()
    {
        ResponseHandler::info('Command app:download-flatfile-vat-data started', [], 'info_log');

        try {
            // Avvio del download dei dati
            $this->info('Starting data download from API...');
            ResponseHandler::info('Starting data download from API', [], 'info_log');

            // Chiamata alla funzione desiderata
            $this->apiDataFetcherService->fetchAndStoreFlatfileVatData();

            // Completamento con successo
            $this->info('Data download completed successfully.');
            ResponseHandler::success('Data download completed successfully', [], 'success_log');

            return Command::SUCCESS;
        } catch (\Exception $e) {
            // Log dell'errore con dettagli
            $this->error('Error occurred during data download.');
            ResponseHandler::error('Error executing app:download-flatfile-vat-data command', [
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
            ], 'error_log');

            return Command::FAILURE;
        }
    }
}
