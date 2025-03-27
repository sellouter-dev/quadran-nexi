<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\ResponseHandler;
use App\Services\APIDataFetcherService;

class FetchFlatFileV2Command extends Command
{
    protected $signature = 'app:fetch-flatfilev2';
    protected $description = 'Scarica i dati delle collezioni da API (Flatfile v2)';

    protected $apiDataFetcherService;

    public function __construct()
    {
        parent::__construct();
        $this->apiDataFetcherService = new APIDataFetcherService();
    }

    public function handle()
    {
        ResponseHandler::info('Comando app:fetch-flatfilev2 avviato', [], 'info_log');

        try {
            $this->info('Avvio del download dei dati da API...');
            $this->apiDataFetcherService->fetchAndStoreFlatfilev2();

            $this->info('Download completato con successo.');
            ResponseHandler::success('Download completato con successo', [], 'success_log');

            return Command::SUCCESS;
        } catch (\Exception $e) {
            $this->error('Errore durante il download dei dati.');
            ResponseHandler::error('Errore in app:fetch-flatfilev2', [
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
            ], 'error_log');

            return Command::FAILURE;
        }
    }
}