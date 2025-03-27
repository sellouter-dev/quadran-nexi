<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\ResponseHandler;
use App\Models\DepositDateHistory;
use App\Services\CsvDataGeneratorService;
use Carbon\Carbon;

class GenerateCsvPaymentCommand extends Command
{
    protected $signature = 'app:generate-csv-payment';
    protected $description = 'Genera il CSV per i dati delle collezioni';

    protected $csvDataGeneratorService;

    public function __construct()
    {
        parent::__construct();
        $this->csvDataGeneratorService = new CsvDataGeneratorService();
    }


    public function handle()
    {
        ResponseHandler::info('Comando app:generate-csv-payment avviato', [], 'info_log');

        try {
            $this->info('Controllo delle deposit_date in corso...');

            $dates = DepositDateHistory::all();
            $today = Carbon::today();

            foreach ($dates as $dateEntry) {
                $depositDate = Carbon::parse($dateEntry->deposit_date);
                if ($depositDate->diffInDays($today) >= 40) {
                    $this->info("Generazione CSV per deposit_date: " . $depositDate->toDateString());

                    // Passa la data al metodo e genera il CSV
                    $this->csvDataGeneratorService->generatePaymentCSV($depositDate);

                    // Rimuove la deposit_date dal database
                    $dateEntry->delete();

                    $this->info("Eliminata deposit_date: " . $depositDate->toDateString());
                }
            }

            ResponseHandler::success('Comando completato con successo', [], 'success_log');
            return Command::SUCCESS;
        } catch (\Exception $e) {
            $this->error('Errore durante la generazione dei CSV.');
            ResponseHandler::error('Errore in app:generate-csv-payment', [
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
            ], 'error_log');

            return Command::FAILURE;
        }
    }
}
