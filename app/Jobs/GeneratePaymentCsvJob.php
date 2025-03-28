<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use App\Services\ResponseHandler;
use App\Services\CsvDataGeneratorService;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class GeneratePaymentCsvJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $csvDataGeneratorService;


    public function handle()
    {
        ResponseHandler::info(
            'Job GeneratePaymentCSVJob avviato',
            [
                'job_id' => $this->job->getJobId(),
                'queue'  => $this->job->getQueue(),
            ],
            'sellouter'
        );

        try {
            ResponseHandler::info('Generazione CSV in corso...', [], 'sellouter');
            $this->csvDataGeneratorService = new CsvDataGeneratorService();
            $this->csvDataGeneratorService->generatePaymentCSV();

            ResponseHandler::success(
                'GeneratePaymentCSVJob completato con successo.',
                [
                    'job_id' => $this->job->getJobId(),
                ],
                'sellouter'
            );
        } catch (\Exception $e) {
            ResponseHandler::error(
                'Errore nel GeneratePaymentCSVJob',
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
