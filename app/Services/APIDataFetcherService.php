<?php

namespace App\Services;

use App\Models\InvoiceTrack;
use App\Models\DataCollection;
use App\Models\SellerInventoryItem;
use App\Services\DataGeneratorAmazon;
use App\Models\FlatfileVatInvoiceData;
use Exception;

/**
 * Class APIDataFetcherService
 *
 * Questo servizio si occupa di richiamare diverse API (ad esempio per Seller Inventory Items,
 * calcolo dell'IVA, flatfile VAT invoice data, e collection data) e di salvare i dati ottenuti.
 * Durante l'intero processo viene utilizzato il channel di log "sellouter" per avere un log consistente.
 *
 * @package App\Services
 */
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
        // Log di inizio processo
        ResponseHandler::info('Starting saveDataSellerInventoryItemsApi process', [], 'sellouter');

        try {
            ResponseHandler::info('Calling Seller Inventory Items API', [], 'sellouter');
            $response = $this->dataGenerator->callSellerInventoryItemsApi();

            $totalItems = count($response);
            ResponseHandler::info('Processing inventory items', ['total_items' => $totalItems], 'sellouter');

            foreach ($response as $index => $row) {
                SellerInventoryItem::create([
                    'asin'                          => $row['asin'],
                    'date'                          => isset($row['date']) ? date('Y-m-d', strtotime($row['date'])) : null,
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

            ResponseHandler::success('Seller inventory items saved successfully', [
                'total_processed' => $totalItems
            ], 'sellouter');

            return response()->json(['message' => 'Dati di Seller Inventory Items salvati con successo'], 200);
        } catch (Exception $e) {
            ResponseHandler::error('Exception in saveDataSellerInventoryItemsApi', [
                'error' => $e->getMessage(),
                'file'  => $e->getFile(),
                'line'  => $e->getLine(),
            ], 'sellouter');

            return response()->json([
                'error' => $e->getMessage(),
                'code'  => $e->getCode()
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
    public function fetchAndStoreInvoiceData()
    {
        ResponseHandler::info('Fetching VAT Calculation Data', [], 'sellouter');

        try {
            $response = $this->dataGenerator->callVatCalculationApi();
            $totalRecords = count($response);

            foreach ($response as $row) {
                InvoiceTrack::saveInvoiceTrackData($row);
            }

            ResponseHandler::success('Invoice data fetched and saved successfully', ['total_saved' => $totalRecords], 'sellouter');
        } catch (Exception $e) {
            ResponseHandler::error('Error in fetching invoice data', ['error' => $e->getMessage()], 'sellouter');
        }
    }

    /**
     * Esegue il fetch dei dati Flatfile VAT Invoice e li salva tramite il modello FlatfileVatInvoiceData.
     *
     * Effettua il logging degli eventi utilizzando il channel "sellouter".
     *
     * @return void
     */
    public function fetchAndStoreFlatfileVatData()
    {
        ResponseHandler::info('Fetching Flatfile VAT Invoice Data', [], 'sellouter');

        try {
            $response = $this->dataGenerator->callFlatfileVatInvoiceDataApi();
            $totalRecords = count($response);

            foreach ($response as $row) {
                FlatfileVatInvoiceData::saveInvoiceData($row);
            }

            ResponseHandler::success('Flatfile VAT invoice data fetched and saved successfully', ['total_saved' => $totalRecords], 'sellouter');
        } catch (Exception $e) {
            ResponseHandler::error('Error in fetching flatfile VAT data', ['error' => $e->getMessage()], 'sellouter');
        }
    }

    /**
     * Esegue il fetch dei dati delle collections e li salva tramite il modello DataCollection.
     *
     * Effettua il logging degli eventi utilizzando il channel "sellouter".
     *
     * @return void
     */
    public function fetchAndStoreCollectionData()
    {
        ResponseHandler::info('Fetching Collections Data', [], 'sellouter');

        try {
            $response = $this->dataGenerator->callCollectionsDataApi();
            $totalRecords = count($response);

            foreach ($response as $row) {
                DataCollection::saveCollectionData($row);
            }

            ResponseHandler::success('Collection data fetched and saved successfully', ['total_saved' => $totalRecords], 'sellouter');
        } catch (Exception $e) {
            ResponseHandler::error('Error in fetching collection data', ['error' => $e->getMessage()], 'sellouter');
        }
    }
}
