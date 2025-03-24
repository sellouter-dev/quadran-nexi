<?php

namespace App\Services;

use Exception;
use App\Services\ResponseHandler;
use App\Services\FileEncryptionService;
use App\Models\AmazonSpReportFlatfilev2settlement;
use Carbon\Carbon;
use CommerceGuys\Addressing\Subdivision\SubdivisionRepository;
use Illuminate\Support\Str;
use App\Models\AmazonSpReportAmazonVatTransaction;
// Importa qui anche SubdivisionRepository se non lo hai già
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
    public function generateInvoiceCSV()
    {
        ResponseHandler::info('Avvio della generazione del CSV per InvoiceTrack', [], 'csv');

        try {
            // Filtra i record di oggi
            $data = [];

            // Log info prima del ciclo: inizio elaborazione record
            ResponseHandler::info('downloadDataCalculationComputed - inizio elaborazione record', [], 'csv');
            $previousMonth = Carbon::now()->subMonth();
            $previousMonthString = $previousMonth->format('Y') . '-' . Str::upper($previousMonth->format('M'));
            // Recupera tutti i record del mese corrente, ordinati per requesttime
            $results = AmazonSpReportAmazonVatTransaction::where('amazon_sp_report_amazonvattransactions.activity_period', $previousMonthString)->get();

            foreach ($results as $row) {
                $importoConIva = $row->gift_promo_vat_incl_amount +
                    $row->item_vat_incl_amount +
                    $row->item_promo_vat_incl_amount +
                    $row->shipping_promo_vat_incl_amount +
                    $row->shipping_vat_incl_amount;

                $importoSenzaIva = $row->gift_promo_vat_excl_amount +
                    $row->item_vat_excl_amount +
                    $row->item_promo_vat_excl_amount +
                    $row->shipping_promo_vat_excl_amount +
                    $row->shipping_promo_vat_excl_amount;

                $importoIva = $row->gift_wrap_vat_amount +
                    $row->item_vat_amount +
                    $row->item_promo_vat_amount +
                    $row->shipping_vat_amount +
                    $row->shipping_promo_vat_amount;

                $tipoRegistrazione = (empty($row->buyer_tax_registration_type))
                    ? 'Corrispettivo'
                    : $row->buyer_tax_registration_type;

                $shipmentDate = Carbon::parse($row->tax_calculation_date)
                    ->timezone('Europe/Rome')
                    ->format('Y-m-d');

                $data[] = [
                    'document_date'              => $shipmentDate,
                    'registration_date'          => '',
                    'document_number'            => $row->original_vat_invoice_number,
                    'document_type'              => $row->transaction_type,
                    'currency'                   => $row->currency,
                    'gross_amount'               => $importoConIva,
                    'net_amount'                 => $importoSenzaIva,
                    'vat_amount'                 => $importoIva,
                    'split_payment'              => '',
                    'vat_code'                   => round($row->item_vat_rate, 2),
                    'unique_code_rif3'           => $row->order_id,
                    'buyer_tax_registration_type' => ($tipoRegistrazione == "BusinessReg" ? "VAT" : $tipoRegistrazione),
                    'buyer_vat_number'           => $row->buyer_vat_number,
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
    public function generateFlatfileVatCSV()
    {
        ResponseHandler::info('Avvio della generazione del CSV per FlatfileVatInvoiceData', [], 'csv');

        try {
            $previousMonth = Carbon::now()->subMonth();
            $previousMonthString = $previousMonth->format('Y') . '-' . Str::upper($previousMonth->format('M'));

            $results = AmazonSpReportAmazonVatTransaction::select(
                'amazon_sp_report_amazonvattransactions.*',
                'amazon_sp_report_flatfilevatinvoicedatavidr.*'
            )
                ->leftJoin(
                    'amazon_sp_report_flatfilevatinvoicedatavidr',
                    'amazon_sp_report_amazonvattransactions.transaction_event_id',
                    '=',
                    'amazon_sp_report_flatfilevatinvoicedatavidr.order_id'
                )
                ->where('amazon_sp_report_amazonvattransactions.activity_period', $previousMonthString)
                ->whereNotNull('amazon_sp_report_flatfilevatinvoicedatavidr.buyer_tax_registration_type')
                ->whereIn('amazon_sp_report_flatfilevatinvoicedatavidr.buyer_tax_registration_type', ['CitizenId', 'VAT'])
                ->get();

            $data = [];
            $subdivisionRepository = new SubdivisionRepository();
            $uniqueCombinations = [];

            ResponseHandler::info('downloadDataOfFlatfilevatinvoicedatavidr - inizio elaborazione record', ["results" => $results], 'csv');

            foreach ($results as $row) {
                $uniqueKey = $row->order_id . '|' . $row->shipment_date;

                if (! in_array($uniqueKey, $uniqueCombinations)) {
                    // Se buyer_tax_registration_type è "VAT" e buyer_company_name è vuoto, usiamo buyer_name
                    $denominazioneDelCliente = $row->buyer_tax_registration_type === 'VAT'
                        ? (empty($row->buyer_company_name) ? $row->buyer_name : $row->buyer_company_name)
                        : $row->buyer_name;

                    // Recuperiamo la provincia in modo analogo a prima
                    $provinciaResidenza = $row->bill_state;
                    $subdivision = $subdivisionRepository->getAll(['IT']);
                    foreach ($subdivision as $sub) {
                        if (strtolower($sub->getName()) === strtolower($row->bill_state)) {
                            $provinciaResidenza = $sub->getCode();
                            break;
                        }
                    }

                    // Popoliamo i dati da salvare nel CSV
                    $data[] = [
                        'buyer_name'                  => $denominazioneDelCliente,
                        'buyer_address'               => $row->bill_address_1,
                        'buyer_postal_code'           => $row->bill_postal_code,
                        'buyer_city'                  => $row->bill_city,
                        'buyer_country'               => $row->bill_country,
                        'buyer_province_code'         => $provinciaResidenza,
                        'buyer_tax_registration_type' => $row->buyer_tax_registration_type,
                        'buyer_vat_number'            => $row->buyer_vat_number,
                    ];

                    $uniqueCombinations[] = $uniqueKey;
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
     *
     * @return mixed Risultato della crittografia del file CSV, oppure una risposta JSON in caso di errore.
     */
    public function generateCollectionCSV()
    {
        ResponseHandler::info('Avvio della generazione del CSV per DataCollection', [], 'csv');

        try {
            $data = [];
            // Filtra i record di oggi
            $query = AmazonSpReportFlatfilev2settlement::select(
                'amazon_sp_report_flatfilev2settlement.deposit_date',
                'amazon_sp_report_flatfilev2settlement.transaction_type',
                'amazon_sp_report_flatfilev2settlement.amount_description',
                'amazon_sp_report_flatfilev2settlement.currency',
                'amazon_sp_report_flatfilev2settlement.amount',
                'amazon_sp_report_flatfilev2settlement.order_id',
                'amazon_sp_report_amazonvattransactions.transaction_complete_date',
                'amazon_sp_report_amazonvattransactions.vat_inv_number',
                'amazon_sp_report_amazonvattransactions.buyer_tax_registration_type',
                'amazon_sp_report_amazonvattransactions.buyer_vat_number'
            )
                ->whereNotNull('amazon_sp_report_flatfilev2settlement.order_id')
                ->orderBy('amazon_sp_report_flatfilev2settlement.deposit_date', 'DESC')
                ->rightJoin(
                    'amazon_sp_report_amazonvattransactions',
                    'amazon_sp_report_amazonvattransactions.transaction_event_id',
                    '=',
                    'amazon_sp_report_flatfilev2settlement.order_id'
                );

            $dataElement = $query->get();

            foreach ($dataElement as $row) {
                $shipmentDate = Carbon::parse($row->transaction_arrival_date ?? '')
                    ->timezone('Europe/Rome')
                    ->format('Y-m-d');

                $data[] = [
                    'deposit_date'              => $row->deposit_date,
                    'document_date'             => $shipmentDate,
                    'registration_date'         => '',
                    'document_number'           => $row->vat_invoice_number ?? '',
                    'document_type'             => $row->transaction_type,
                    'transaction_type'          => $row->amount_description,
                    'currency'                  => $row->currency,
                    'amount'                    => round($row->amount, 2),
                    'unique_code_rif3'          => $row->order_id,
                    'buyer_tax_registration_type' => $row->buyer_tax_registration_type ?? '',
                    'buyer_vat_number'          => $row->buyer_vat_number ?? '',
                ];
            }

            $filePath = storage_path('/app/temp/Payment_' . Carbon::today()->format('dmY') . '.csv');
            $result = $this->streamCSV($data, $filePath);

            ResponseHandler::success('CSV per Payment generato con successo', ['file' => 'Payment.csv'], 'csv');
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
                if (fputcsv($handle, array_keys($data[0])) === false) {
                    throw new Exception("Errore nella scrittura dell'header CSV sul file: $filePath");
                }
                ResponseHandler::info("Scrittura delle righe CSV sul file: $filePath", [], 'csv');
                foreach ($data as $row) {
                    if (fputcsv($handle, $row) === false) {
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
