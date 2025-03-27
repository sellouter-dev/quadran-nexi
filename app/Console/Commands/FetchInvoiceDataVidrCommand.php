<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\ResponseHandler;
use App\Services\APIDataFetcherService;

class FetchInvoiceDataVidrCommand extends Command
{
    protected $signature = 'app:fetch-invoice-data-vidr';
    protected $description = 'Scarica i dati delle fatture VAT da flatfile tramite API';

    protected $apiDataFetcherService;

    public function __construct()
    {
        parent::__construct();
        $this->apiDataFetcherService = new APIDataFetcherService();
    }

    public function handle()
    {
        ResponseHandler::info('Comando app:fetch-invoice-data-vidr avviato', [], 'info_log');

        try {
            $this->info('Avvio del download dati fatture VAT...');
            $this->apiDataFetcherService->fetchAndStoreInvoiceDataVidr();

            $this->info('Download completato con successo.');
            ResponseHandler::success('Download completato con successo', [], 'success_log');

            return Command::SUCCESS;
        } catch (\Exception $e) {
            $this->error('Errore durante il download.');
            ResponseHandler::error('Errore in app:fetch-invoice-data-vidr', [
                'error' => $e->getMessage(),
                'file'  => $e->getFile(),
                'line'  => $e->getLine(),
                'trace' => $e->getTraceAsString(),
            ], 'error_log');

            return Command::FAILURE;
        }
    }
}
