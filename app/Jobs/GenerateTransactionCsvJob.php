<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use App\Services\ResponseHandler;
use Illuminate\Queue\SerializesModels;
use App\Services\APIDataFetcherService;
use Illuminate\Queue\InteractsWithQueue;
use App\Services\CsvDataGeneratorService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class GenerateTransactionCsvJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Il servizio CsvDataGeneratorService per la gestione dei dati.
     *
     * @var CsvDataGeneratorService
     */
    protected $csvDataGeneratorService;

    /**
     * Esegue il job.
     *
     * @return void
     */
    public function handle()
    {
        ResponseHandler::info(
            'Job DownloadDataCalculationComputedJob avviato',
            [
                'job_id' => $this->job->getJobId(),
                'queue'  => $this->job->getQueue(),
            ],
            'sellouter'
        );

        try {
            // Step 1: Avvio del processo di download dei dati per il calcolo
            ResponseHandler::info(
                'Avvio del download dei dati per il calcolo',
                [],
                'sellouter'
            );

            $this->csvDataGeneratorService = new CsvDataGeneratorService();
            $this->csvDataGeneratorService->generateTransactionCSV();

            // Step 2: Completamento del job con successo
            ResponseHandler::success(
                'Job DownloadDataCalculationComputedJob eseguito con successo.',
                [
                    'job_id' => $this->job->getJobId(),
                ],
                'sellouter'
            );
        } catch (\Exception $e) {
            // Step 3: Gestione dell'errore
            ResponseHandler::error(
                'Errore durante l\'esecuzione del job DownloadDataCalculationComputedJob',
                [
                    'errore' => $e->getMessage(),
                    'file'   => $e->getFile(),
                    'linea'  => $e->getLine(),
                    'trace'  => $e->getTraceAsString(),
                ],
                'sellouter'
            );
        }
    }
}
