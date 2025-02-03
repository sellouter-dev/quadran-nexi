<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\ResponseHandler;
use App\Services\CSVGeneratorService;

class DownloadCollectionsDataCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:download-collections-data-command';

    /**
     * Il servizio CSVGeneratorService per la gestione dei dati.
     *
     * @var CSVGeneratorService
     */
    protected $csvGeneratorService;

    /**
     * Crea una nuova istanza del comando.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
        $this->csvGeneratorService = new CSVGeneratorService();
    }

    /**
     * Esegue il comando.
     *
     * @return int
     */
    public function handle()
    {
        ResponseHandler::info('Command collections:download started', [], 'info_log');

        try {
            // Avvio del download dei dati
            $this->info('Starting data download from API...');
            ResponseHandler::info('Starting data download from API', [], 'info_log');

            $this->csvGeneratorService->downloadDataOfCollections();

            // Completamento con successo
            $this->info('Data download completed successfully.');
            ResponseHandler::success('Data download completed successfully', [], 'success_log');

            return Command::SUCCESS;
        } catch (\Exception $e) {
            // Log dell'errore con dettagli
            $this->error('Error occurred during data download.');
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
