<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use App\Services\ResponseHandler;
use App\Services\APIDataFetcherService;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class FetchFlatfilev2Job implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $apiDataFetcherService;

    public function handle()
    {
        ResponseHandler::info(
            'Job FetchFlatfilev2Job avviato',
            [
                'job_id' => $this->job->getJobId(),
                'queue'  => $this->job->getQueue(),
            ],
            'sellouter'
        );

        try {
            ResponseHandler::info('Download dati dall\'API in corso...', [], 'sellouter');
            $this->apiDataFetcherService = new APIDataFetcherService();
            $this->apiDataFetcherService->fetchAndStoreFlatfilev2();

            ResponseHandler::success(
                'FetchFlatfilev2Job completato con successo.',
                [
                    'job_id' => $this->job->getJobId(),
                ],
                'sellouter'
            );
        } catch (\Exception $e) {
            ResponseHandler::error(
                'Errore nel FetchFlatfilev2Job',
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
