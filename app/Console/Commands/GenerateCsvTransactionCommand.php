<?php
// FILE GENERATO
namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\ResponseHandler;
use App\Services\APIDataFetcherService;
use App\Services\CsvDataGeneratorService;

class GenerateCsvTransactionCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:generate-csv-transaction';

    /**
     * La descrizione del comando Artisan.
     *
     * @var string
     */
    protected $description = 'Scarica i dati di calcolo e li salva in un file CSV';

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
    }

    /**
     * Esegue il comando.
     *
     * @return int
     */
    public function handle()
    {
        ResponseHandler::info('Command app:generate-csv-transaction started', [], 'info_log');

        try {
            // Avvio del download dei dati
            $this->info('Starting calculation data download...');
            ResponseHandler::info('Starting data calculation download', [], 'info_log');

            $this->csvDataGeneratorService->generateTransactionCSV();

            // Completamento con successo
            $this->info('Data calculation download completed successfully.');
            ResponseHandler::success('Data calculation download completed successfully', [], 'success_log');

            return Command::SUCCESS;
        } catch (\Exception $e) {
            // Log dell'errore con dettagli
            $this->error('Error occurred during data calculation download.');
            ResponseHandler::error('Error executing app:generate-csv-transaction command', [
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
            ], 'error_log');

            return Command::FAILURE;
        }
    }
}
