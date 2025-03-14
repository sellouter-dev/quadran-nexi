<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\ResponseHandler;
use App\Services\APIDataFetcherService;
use App\Services\CsvDataGeneratorService;

class DownloadFlatfileVatInvoiceDataCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:download-flatfile-vat-invoice-data-command {mode=0 : 0 (fetch+CSV), 1 (CSV only), 2 (fetch only)}';

    /**
     * La descrizione del comando Artisan.
     *
     * @var string
     */
    protected $description = 'Scarica i dati delle fatture VAT flatfile e li salva in un file CSV';

    /**
     * Il servizio CsvDataGeneratorService per la gestione dei dati.
     *
     * @var CsvDataGeneratorService
     */
    protected $csvDataGeneratorService;

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
        $this->csvDataGeneratorService = new CsvDataGeneratorService();
        $this->apiDataFetcherService = new APIDataFetcherService();
    }

    /**
     * Esegue il comando.
     *
     * @return int
     */
    public function handle()
    {
        $mode = (int)$this->argument('mode');

        ResponseHandler::info('Command data:download-flatfile-vat started with mode ' . $mode, [], 'info_log');

        try {
            if ($mode === 0 || $mode === 2) {
                $this->info('Starting flatfile VAT invoice data download...');
                ResponseHandler::info('Starting flatfile VAT invoice data download', [], 'info_log');
                $this->apiDataFetcherService->fetchAndStoreInvoiceData();
            }

            if ($mode === 0 || $mode === 1) {
                $this->info('Generating flatfile VAT CSV data...');
                ResponseHandler::info('Generating flatfile VAT CSV data', [], 'info_log');
                $this->csvDataGeneratorService->generateFlatfileVatCSV();
            }

            $this->info('Operation completed successfully.');
            ResponseHandler::success('Flatfile VAT invoice data download completed successfully', [], 'success_log');

            return Command::SUCCESS;
        } catch (\Exception $e) {
            $this->error('Error occurred during flatfile VAT invoice data download.');
            ResponseHandler::error('Error executing data:download-flatfile-vat command', [
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
            ], 'error_log');

            return Command::FAILURE;
        }
    }
}
