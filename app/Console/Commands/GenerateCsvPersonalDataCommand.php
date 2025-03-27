<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\ResponseHandler;
use App\Services\CsvDataGeneratorService;

class GenerateCsvPersonalDataCommand extends Command
{
    protected $signature = 'app:generate-csv-personaldata';
    protected $description = 'Genera il file CSV con i dati personali dalle fatture VAT (Flatfile)';

    protected $csvDataGeneratorService;

    public function __construct()
    {
        parent::__construct();
        $this->csvDataGeneratorService = new CsvDataGeneratorService();
    }

    public function handle()
    {
        ResponseHandler::info('Comando app:generate-csv-personaldata avviato', [], 'info_log');

        try {
            $this->info('Generazione del CSV in corso...');
            $this->csvDataGeneratorService->generatePersonalDataCSV();

            $this->info('CSV generato con successo.');
            ResponseHandler::success('CSV generato con successo', [], 'success_log');

            return Command::SUCCESS;
        } catch (\Exception $e) {
            $this->error('Errore durante la generazione del CSV.');
            ResponseHandler::error('Errore in app:generate-csv-personaldata', [
                'error' => $e->getMessage(),
                'file'  => $e->getFile(),
                'line'  => $e->getLine(),
                'trace' => $e->getTraceAsString(),
            ], 'error_log');

            return Command::FAILURE;
        }
    }
}
