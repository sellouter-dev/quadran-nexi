<?php

namespace App\Services;

use Exception;
use Carbon\Carbon;
use Illuminate\Support\Str;
use App\Services\ResponseHandler;
use Illuminate\Support\Facades\DB;
use App\Services\FileEncryptionService;
use App\Models\AmazonSpReportAmazonVatTransaction;
use App\Models\AmazonSpReportFlatfilev2settlement;
use App\Models\AmazonSpReportFlatfilevatinvoicedatavidr;
use CommerceGuys\Addressing\Subdivision\SubdivisionRepository;
use Illuminate\Support\Facades\Log;

/**
 * Class CsvDataGeneratorService
 *
 * Questa classe si occupa di generare file CSV a partire dai dati presenti nei modelli InvoiceTrack,
 * FlatfileVatInvoiceData e DataCollection, successivamente esegue la crittografia del file generato.
 * Durante il processo, vengono utilizzati log consistenti sul channel "csv" per tracciare le operazioni.
 *
 * @package App\Services
 */
class CsvDataGeneratorService
{
    /**
     * @var FileEncryptionService Istanza del servizio di crittografia dei file.
     */
    protected $fileEncryptionService;

    /**
     * CsvDataGeneratorService constructor.
     *
     * Inizializza il servizio di crittografia.
     */
    public function __construct()
    {
        $this->fileEncryptionService = new FileEncryptionService();
    }

    /**
     * TODO:
     * Genera il CSV per i dati delle InvoiceTrack. Transazione(Inviato ogni 4 del mese)
     *
     * Effettua il logging dell'inizio e del completamento dell'operazione, nonché degli eventuali errori.
     *
     * @return mixed Risultato della crittografia del file CSV, oppure una risposta JSON in caso di errore.
     */
    public function generateTransactionCSV()
    {
        ResponseHandler::info('Avvio della generazione del CSV per InvoiceTrack', [], 'csv');

        try {
            $data = [];

            ResponseHandler::info('generateTransactionCSV - inizio elaborazione record', [], 'csv');
            $previousMonth = Carbon::now()->subMonth();
            $previousMonthString = $previousMonth->format('Y') . '-' . Str::upper($previousMonth->format('M'));
            $previousMonthString = "2025-MAR";
            $results = AmazonSpReportAmazonVatTransaction::select('*')
                ->where('amazon_sp_report_amazonvattransactions.activity_period', $previousMonthString)
                ->whereNotIn('amazon_sp_report_amazonvattransactions.transaction_type', ['FC_TRANSFER', 'RETURN'])->get();
            foreach ($results as $row) {
                $tipoRegistrazione = (empty($row->buyer_tax_registration_type))
                    ? 'Corrispettivo'
                    : $row->buyer_tax_registration_type;
                $buyerVatNumber = $row->buyer_vat_number;

                $shipmentDate = Carbon::parse($row->transaction_complete_date)
                    ->timezone('Europe/Rome')
                    ->format('Y-m-d');

                $documentType = match ($row->transaction_type) {
                    'SALE' => 'F',
                    'REFUND' => 'N',
                    default => $row->transaction_type,
                };

                $data[] = [
                    'document_date'               => $shipmentDate,
                    'registration_date'          => '',
                    'document_number'            => $row->vat_inv_number,
                    'document_type'              => $documentType,
                    'currency'                   => $row->transaction_currency_code,
                    'gross_amount'               => $row->total_activity_value_amt_vat_incl,
                    'net_amount'                 => $row->total_activity_value_amt_vat_excl,
                    'vat_amount'                 => $row->total_activity_value_vat_amt,
                    'split_payment'              => '',
                    'vat_code'                   => round($row->price_of_items_vat_rate_percent, 2),
                    'unique_code_rif3'           => $row->transaction_event_id,
                    'buyer_tax_registration_type' => ($tipoRegistrazione == "BusinessReg" ? "VAT" : $tipoRegistrazione),
                    'buyer_vat_number'           => $buyerVatNumber,
                ];
            }

            $filePath = storage_path('/app/temp/Transaction_' . Carbon::today()->format('dmY') . '.csv');
            $result = $this->streamCSV($data, $filePath);

            ResponseHandler::success('CSV per Transaction_ generato con successo', ['file' => 'Transaction.csv'], 'csv');
            return $result;
        } catch (Exception $e) {
            ResponseHandler::error('Errore durante la generazione del CSV per Transaction_', ['errore' => $e->getMessage()], 'csv');
            return response()->json(['errore' => $e->getMessage(), 'codice' => $e->getCode()], 500);
        }
    }

    /**
     * Genera il CSV per i dati delle FlatfileVatInvoiceData. Anagrafiche(generazione ogni 4 del mese)
     *
     * Effettua il logging dell'inizio e del completamento dell'operazione, nonché degli eventuali errori.
     *
     * @return mixed Risultato della crittografia del file CSV, oppure una risposta JSON in caso di errore.
     */
    public function generatePersonalDataCSV()
    {
        ResponseHandler::info('Avvio della generazione del CSV per FlatfileVatInvoiceData', [], 'csv');

        try {
            $previousMonth = Carbon::now()->subMonth();
            $previousMonthString = $previousMonth->format('Y') . '-' . Str::upper($previousMonth->format('M'));
            $results = AmazonSpReportAmazonVatTransaction::select(
                'amazon_sp_report_flatfilevatinvoicedatavidr.*',
                'amazon_sp_report_amazonvattransactions.*',
                'amazon_sp_report_flatfilevatinvoicedatavidr.buyer_vat_number as buyer_vat_number_vidr',
            )
                ->leftJoin(
                    'amazon_sp_report_flatfilevatinvoicedatavidr',
                    'amazon_sp_report_amazonvattransactions.transaction_event_id',
                    '=',
                    'amazon_sp_report_flatfilevatinvoicedatavidr.order_id'
                )

                ->where('amazon_sp_report_amazonvattransactions.activity_period', $previousMonthString)
                ->whereIn('amazon_sp_report_flatfilevatinvoicedatavidr.buyer_tax_registration_type', ['CitizenId', 'VAT'])
                ->get();

            $data = [];
            $subdivisionRepository = new SubdivisionRepository();
            $uniqueCombinations = [];

            ResponseHandler::info('downloadDataOfFlatfilevatinvoicedatavidr - inizio elaborazione record', ["results" => $results], 'csv');
            $vatNumbersAlreadyAdded = [];

            foreach ($results as $row) {
                $uniqueKey = $row->order_id . '|' . $row->shipment_date;
                $vatNumber = $row->buyer_vat_number_vidr;

                if (in_array($vatNumber, $vatNumbersAlreadyAdded)) {
                    continue;
                }

                if (! in_array($uniqueKey, $uniqueCombinations)) {
                    $denominazioneDelCliente = $row->buyer_tax_registration_type === 'VAT'
                        ? (empty($row->buyer_company_name) ? $row->billing_name : $row->buyer_company_name)
                        : $row->billing_name;

                    // Recuperiamo la provincia in modo analogo a prima
                    $provinciaResidenza = $row->bill_state;
                    $subdivision = $subdivisionRepository->getAll(['IT']);
                    foreach ($subdivision as $sub) {
                        if (strtolower($sub->getName()) === strtolower($row->bill_state)) {
                            $provinciaResidenza = $sub->getCode();
                            break;
                        }
                    }

                    $data[] = [
                        'buyer_name'                  => $denominazioneDelCliente,
                        'buyer_address'               => $row->bill_address_1,
                        'buyer_postal_code'           => $row->bill_postal_code,
                        'buyer_city'                  => $row->bill_city,
                        'buyer_country'               => $row->bill_country,
                        'buyer_province_code'         => $provinciaResidenza,
                        'buyer_tax_registration_type' => $row->buyer_tax_registration_type,
                        'buyer_vat_number'            => $vatNumber,
                    ];

                    $uniqueCombinations[] = $uniqueKey;
                    $vatNumbersAlreadyAdded[] = $vatNumber;
                }
            }

            // Salvataggio CSV
            $filePath = storage_path('/app/temp/Personal_Data_' . Carbon::today()->format('dmY') . '.csv');
            $result = $this->streamCSV($data, $filePath);

            ResponseHandler::success(
                'CSV per Personal_Data generato con successo',
                ['file' => 'Personal_Data.csv'],
                'csv'
            );
            return $result;
        } catch (\Exception $e) {
            ResponseHandler::error(
                'Errore durante la generazione del CSV per Personal_Data',
                ['errore' => $e->getMessage()],
                'csv'
            );
            return response()->json(['errore' => $e->getMessage(), 'codice' => $e->getCode()], 500);
        }
    }

    /**
     * Genera il CSV per i dati delle DataCollection. Pagamenti(Ogni 15 giorni con invio su MUVI ogni 40)
     *
     * Effettua il logging dell'inizio e del completamento dell'operazione, nonché degli eventuali errori.
     * @param Carbon|null $filterDepositDate
     *
     * @return mixed Risultato della crittografia del file CSV, oppure una risposta JSON in caso di errore.
     */
    public function generatePaymentCSV(?Carbon $filterDepositDate = null)
    {
        ResponseHandler::info('Avvio della generazione del CSV per DataCollection', [], 'csv');

        try {
            $data = [];

            $query = AmazonSpReportFlatfilev2settlement::query()
                ->whereNotNull('order_id')
                ->orderBy('deposit_date', 'DESC');

            if ($filterDepositDate) {
                $query->whereDate('deposit_date', $filterDepositDate->toDateString());
            }

            $dataElements = $query->get();

            foreach ($dataElements as $row) {
                $orderId = $row->order_id;

                $vatTransaction = AmazonSpReportAmazonVatTransaction::where('transaction_event_id', $orderId)->first();
                $invoiceDivr = AmazonSpReportFlatfilevatinvoicedatavidr::where('order_id', $orderId)->first();

                $shipmentDate = $vatTransaction?->transaction_complete_date
                    ? Carbon::parse($vatTransaction->transaction_complete_date)->timezone('Europe/Rome')->format('Y-m-d')
                    : '';

                $data[] = [
                    'deposit_date'                => $row->deposit_date,
                    'document_date'               => $shipmentDate,
                    'registration_date'           => '',
                    'document_number'             => $vatTransaction->vat_inv_number ?? '',
                    'document_type'               => $vatTransaction->transaction_type ?? '',
                    'transaction_type'            => $row->amount_description,
                    'currency'                    => $row->currency,
                    'amount'                      => round($row->amount, 2),
                    'unique_code_rif3'            => $row->order_id,
                    'buyer_tax_registration_type' => $invoiceDivr->buyer_tax_registration_type ?? '',
                    'buyer_vat_number'            => $invoiceDivr->buyer_vat_number ?? '',
                ];
            }

            $filename = 'Payment_' . ($filterDepositDate?->format('dmY') ?? Carbon::today()->format('dmY'));
            $filePath = storage_path("/app/temp/{$filename}.csv");
            $result = $this->streamCSV($data, $filePath);

            ResponseHandler::success('CSV per Payment generato con successo', ['file' => "{$filename}.csv"], 'csv');
            return $result;
        } catch (Exception $e) {
            ResponseHandler::error('Errore durante la generazione del CSV per Payment', ['errore' => $e->getMessage()], 'csv');
            return response()->json(['errore' => $e->getMessage(), 'codice' => $e->getCode()], 500);
        }
    }

    /**
     * Crea e scrive il file CSV a partire dai dati forniti, successivamente esegue la crittografia del file.
     *
     * Vengono eseguiti controlli sul corretto accesso al file e sulla scrittura delle righe CSV.
     * In caso di errore, viene registrato l'evento e restituita una risposta JSON di errore.
     * Al termine dell'operazione, il file temporaneo viene eliminato.
     *
     * @param array  $data     Array di dati da scrivere nel CSV.
     * @param string $filePath Percorso completo del file CSV temporaneo.
     * @return mixed Risultato della crittografia del file CSV, oppure una risposta JSON in caso di errore.
     */
    private function streamCSV($data, $filePath)
    {
        try {
            ResponseHandler::info("Avvio del salvataggio del CSV sul file: $filePath", [], 'csv');

            $handle = fopen($filePath, 'w+');
            if (!$handle) {
                throw new Exception("Impossibile aprire il file: $filePath per la scrittura");
            }

            if (!empty($data)) {
                if (fputcsv($handle, array_keys($data[0]), ";") === false) {
                    throw new Exception("Errore nella scrittura dell'header CSV sul file: $filePath");
                }
                ResponseHandler::info("Scrittura delle righe CSV sul file: $filePath", [], 'csv');
                foreach ($data as $row) {
                    if (fputcsv($handle, $row, ";") === false) {
                        throw new Exception("Errore nella scrittura di una riga CSV sul file: $filePath");
                    }
                }
            }

            fclose($handle);

            $encryptedFile = $this->fileEncryptionService->saveFile($filePath);
            ResponseHandler::success("File CSV salvato e crittografato con successo", ['filePath' => $filePath], 'csv');
            return $encryptedFile;
        } catch (Exception $e) {
            ResponseHandler::error("Errore durante il salvataggio del CSV sul file: $filePath", ['errore' => $e->getMessage()], 'csv');
            return response()->json(['errore' => $e->getMessage(), 'codice' => $e->getCode()], 500);
        } finally {
            // Rimuove il file temporaneo se esiste
            if (file_exists($filePath)) {
                // unlink($filePath);
            }
        }
    }
}
