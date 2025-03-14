<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\ResponseHandler;
use App\Services\APIDataFetcherService;
use App\Services\CsvDataGeneratorService;

class DownloadCollectionsDataCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:download-collections-data-command {mode=0 : 0 (fetch+CSV), 1 (CSV only), 2 (fetch only)}';

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

        ResponseHandler::info('Command collections:download started with mode ' . $mode, [], 'info_log');

        try {
            if ($mode === 0 || $mode === 2) {
                $this->info('Starting data download from API...');
                ResponseHandler::info('Starting data download from API', [], 'info_log');
                $this->apiDataFetcherService->fetchAndStoreCollectionData();
            }

            if ($mode === 0 || $mode === 1) {
                $this->info('Generating CSV data...');
                ResponseHandler::info('Generating CSV data', [], 'info_log');
                $this->csvDataGeneratorService->generateCollectionCSV();
            }

            $this->info('Operation completed successfully.');
            ResponseHandler::success('Operation completed successfully', [], 'success_log');

            return Command::SUCCESS;
        } catch (\Exception $e) {
            $this->error('Error occurred during command execution.');
            ResponseHandler::error('Error executing collections:download command', [
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
            ], 'error_log');

            return Command::FAILURE;
        }
    }
}
