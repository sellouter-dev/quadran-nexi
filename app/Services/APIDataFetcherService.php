<?php

namespace App\Services;

use Exception;
use Carbon\Carbon;
use App\Models\DepositDateHistory;
use App\Models\SellerInventoryItem;
use App\Services\DataGeneratorAmazon;
use App\Models\AmazonSpReportAmazonVatTransaction;
use App\Models\AmazonSpReportFlatfilev2settlement;
use App\Models\AmazonSpReportFlatfilevatinvoicedatavidr;

class APIDataFetcherService
{
    /**
     * @var DataGeneratorAmazon Istanza del generatore dei dati utilizzato per chiamare le API.
     */
    private $dataGenerator;

    /**
     * APIDataFetcherService constructor.
     *
     * Inizializza il generatore di dati.
     */
    public function __construct()
    {
        $this->dataGenerator = new DataGeneratorAmazon();
    }

    /**
     * Esegue il fetch dei dati da Seller Inventory Items API e li salva nel database.
     *
     * Effettua il logging dei vari step utilizzando il channel "sellouter".
     *
     * @return \Illuminate\Http\JsonResponse Risposta JSON con il risultato dell'operazione.
     */
    public function fetchAndSaveDataSellerInventoryItemsApi()
    {
        ini_set('memory_limit', '-1');
        ini_set('max_execution_time', '0');
        // Log di inizio processo
        ResponseHandler::info('Avvio del processo di salvataggio per Seller Inventory Items API', [], 'sellouter');

        try {
            ResponseHandler::info('Chiamata all\'API di Seller Inventory Items', [], 'sellouter');
            $response = $this->dataGenerator->callSellerInventoryItemsApi();

            $totalItems = count($response);
            ResponseHandler::info('Elaborazione degli inventory items', ['totale_item' => $totalItems], 'sellouter');

            foreach ($response as $index => $row) {
                SellerInventoryItem::create([
                    'asin'                          => $row['asin'],
                    'report_date'                   => isset($row['date']) ? date('Y-m-d', strtotime($row['date'])) : null,
                    'fnsku'                         => $row['fnsku'] ?? null,
                    'msku'                          => $row['msku'] ?? null,
                    'title'                         => $row['title'] ?? null,
                    'disposition'                   => $row['disposition'] ?? null,
                    'starting_warehouse_balance'    => $row['starting_warehouse_balance'] ?? 0,
                    'in_transit_between_warehouses' => $row['in_transit_between_warehouses'] ?? 0,
                    'receipts'                      => $row['receipts'] ?? 0,
                    'customer_shipments'            => $row['customer_shipments'] ?? 0,
                    'customer_returns'              => $row['customer_returns'] ?? 0,
                    'vendor_returns'                => $row['vendor_returns'] ?? 0,
                    'warehouse_transfer_in_out'     => $row['warehouse_transfer_in_out'] ?? 0,
                    'found'                         => $row['found'] ?? 0,
                    'lost'                          => $row['lost'] ?? 0,
                    'damaged'                       => $row['damaged'] ?? 0,
                    'disposed'                      => $row['disposed'] ?? 0,
                    'other_events'                  => $row['other_events'] ?? 0,
                    'ending_warehouse_balance'      => $row['ending_warehouse_balance'] ?? 0,
                    'unknown_events'                => $row['unknown_events'] ?? 0,
                    'location'                      => $row['location'] ?? null,
                ]);
            }

            ResponseHandler::success('Seller inventory items salvati con successo', [
                'totale_processati' => $totalItems
            ], 'sellouter');

            return response()->json(['messaggio' => 'Dati di Seller Inventory Items salvati con successo'], 200);
        } catch (Exception $e) {
            ResponseHandler::error('Eccezione in fetchAndSaveDataSellerInventoryItemsApi', [
                'errore' => $e->getMessage(),
                'file'   => $e->getFile(),
                'linea'  => $e->getLine(),
            ], 'sellouter');

            return response()->json([
                'errore' => $e->getMessage(),
                'codice' => $e->getCode()
            ], 500);
        }
    }

    /**
     * Esegue il fetch dei dati per il calcolo dell'IVA e li salva tramite il modello InvoiceTrack.
     *
     * Effettua il logging degli eventi utilizzando il channel "sellouter".
     *
     * @return void
     */
    public function fetchAndStoreInvoiceDataVidr()
    {
        ini_set('memory_limit', '-1');
        ini_set('max_execution_time', '0');
        ResponseHandler::info('Recupero dati per il calcolo dell\'IVA', [], 'sellouter');

        try {
            $response = $this->dataGenerator->callVatCalculationApi();
            $totalRecords = count($response);

            foreach ($response as $row) {
                AmazonSpReportFlatfilevatinvoicedatavidr::saveData($row);
            }

            ResponseHandler::success('Dati delle fatture recuperati e salvati con successo', ['totale_salvati' => $totalRecords], 'sellouter');
        } catch (Exception $e) {
            ResponseHandler::error('Errore nel recupero dei dati delle fatture', ['errore' => $e->getMessage()], 'sellouter');
        }
    }

    /**
     * Esegue il fetch dei dati Flatfile VAT Invoice e li salva tramite il modello FlatfileVatInvoiceData.
     *
     * Effettua il logging degli eventi utilizzando il channel "sellouter".
     *
     * @return void
     */
    public function fetchAndStoreVatTransactionData()
    {
        ini_set('memory_limit', '-1');
        ini_set('max_execution_time', '0');
        ResponseHandler::info('Recupero dati Flatfile VAT Invoice', [], 'sellouter');

        try {
            $response = $this->dataGenerator->callFlatfileVatInvoiceDataApi();
            $totalRecords = count($response);

            foreach ($response as $row) {
                AmazonSpReportAmazonVatTransaction::saveData($row);
            }

            ResponseHandler::success('Dati Flatfile VAT Invoice recuperati e salvati con successo', ['totale_salvati' => $totalRecords], 'sellouter');
        } catch (Exception $e) {
            ResponseHandler::error('Errore nel recupero dei dati Flatfile VAT', ['errore' => $e->getMessage()], 'sellouter');
        }
    }

    /**
     * Esegue il fetch dei dati delle collections e li salva tramite il modello DataCollection.
     *
     * Effettua il logging degli eventi utilizzando il channel "sellouter".
     *
     * @return void
     */
    public function fetchAndStoreFlatfilev2()
    {
        ini_set('memory_limit', '-1');
        ini_set('max_execution_time', '0');
        ResponseHandler::info('Recupero dati delle collections', [], 'sellouter');

        try {
            $response = $this->dataGenerator->callCollectionsDataApi();
            $totalRecords = count($response);
            ResponseHandler::info('Elaborazione dei dati delle collections', ['totale_record' => $totalRecords], 'sellouter');
            foreach ($response as $row) {
                $depositDate = Carbon::parse($row['deposit_date'])->format('Y-m-d');

                $alreadyExists = DepositDateHistory::where('deposit_date', $depositDate)->exists();

                if (!$alreadyExists) {
                    DepositDateHistory::create([
                        'deposit_date' => $depositDate,
                        'created_at' => now(),
                    ]);
                }

                AmazonSpReportFlatfilev2settlement::saveData($row);
            }

            ResponseHandler::success('Dati delle collections recuperati e salvati con successo', ['totale_salvati' => $totalRecords], 'sellouter');
        } catch (Exception $e) {
            ResponseHandler::error('Errore nel recupero dei dati delle collections', ['errore' => $e->getMessage()], 'sellouter');
        }
    }
}
