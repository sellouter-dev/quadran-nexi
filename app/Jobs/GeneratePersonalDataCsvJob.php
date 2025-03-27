<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use App\Services\ResponseHandler;
use App\Services\CsvDataGeneratorService;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class GeneratePersonalDataCsvJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $csvDataGeneratorService;

    public function __construct()
    {
        $this->csvDataGeneratorService = new CsvDataGeneratorService();
    }

    public function handle()
    {
        ResponseHandler::info(
            'Job GenerateVatInvoiceCSVJob avviato',
            [
                'job_id' => $this->job->getJobId(),
                'queue'  => $this->job->getQueue(),
            ],
            'sellouter'
        );

        try {
            ResponseHandler::info('Generazione CSV per dati VAT Invoice in corso...', [], 'sellouter');
            $this->csvDataGeneratorService->generatePersonalDataCSV();

            ResponseHandler::success(
                'GenerateVatInvoiceCSVJob completato con successo.',
                [
                    'job_id' => $this->job->getJobId(),
                ],
                'sellouter'
            );
        } catch (\Exception $e) {
            ResponseHandler::error(
                'Errore nel GenerateVatInvoiceCSVJob',
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
