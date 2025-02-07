<?php

namespace App\Services;

use App\Models\InvoiceTrack;
use App\Models\DataCollection;
use App\Models\SellerInventoryItem;
use App\Services\DataGeneratorAmazon;
use App\Models\FlatfileVatInvoiceData;

class APIDataFetcherService
{
    private $dataGenerator;

    public function __construct()
    {
        $this->dataGenerator = new DataGeneratorAmazon();
    }

    /**
     * saveDataSellerInventoryItemsApi
     *
     * @return \Illuminate\Http\JsonResponse|void
     */
    public function saveDataSellerInventoryItemsApi()
    {
        ini_set('max_execution_time', 0);
        ini_set('memory_limit', -1);
        ResponseHandler::info('Starting saveDataSellerInventoryItemsApi process', [], 'info_log');

        try {
            ResponseHandler::info('Calling Seller Inventory Items API', [], 'info_log');
            $response = $this->dataGenerator->callSellerInventoryItemsApi();

            $totalItems = count($response);
            ResponseHandler::info('Processing inventory items', ['total_items' => $totalItems], 'info_log');

            foreach ($response as $index => $row) {
                SellerInventoryItem::create(
                    [
                        'asin' => $row['asin'],
                        'date' => isset($row['date']) ? date('Y-m-d', strtotime($row['date'])) : null,
                        'fnsku' => $row['fnsku'] ?? null,
                        'msku' => $row['msku'] ?? null,
                        'title' => $row['title'] ?? null,
                        'disposition' => $row['disposition'] ?? null,
                        'starting_warehouse_balance' => $row['starting_warehouse_balance'] ?? 0,
                        'in_transit_between_warehouses' => $row['in_transit_between_warehouses'] ?? 0,
                        'receipts' => $row['receipts'] ?? 0,
                        'customer_shipments' => $row['customer_shipments'] ?? 0,
                        'customer_returns' => $row['customer_returns'] ?? 0,
                        'vendor_returns' => $row['vendor_returns'] ?? 0,
                        'warehouse_transfer_in_out' => $row['warehouse_transfer_in_out'] ?? 0,
                        'found' => $row['found'] ?? 0,
                        'lost' => $row['lost'] ?? 0,
                        'damaged' => $row['damaged'] ?? 0,
                        'disposed' => $row['disposed'] ?? 0,
                        'other_events' => $row['other_events'] ?? 0,
                        'ending_warehouse_balance' => $row['ending_warehouse_balance'] ?? 0,
                        'unknown_events' => $row['unknown_events'] ?? 0,
                        'location' => $row['location'] ?? null,
                    ]
                );
            }

            ResponseHandler::success('Seller inventory items saved successfully', [
                'total_processed' => $totalItems
            ], 'success_log');
            return response()->json(['message' => 'Dati di Seller Inventory Items salvati con successo'], 200);
        } catch (\Exception $e) {
            ResponseHandler::error('Exception in saveDataSellerInventoryItemsApi', [
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ], 'error_log');

            return response()->json([
                'error' => $e->getMessage(),
                'code' => $e->getCode()
            ], 500);
        }
    }

    public function fetchAndStoreInvoiceData()
    {
        ini_set('max_execution_time', 0);
        ini_set('memory_limit', -1);
        ResponseHandler::info('Fetching VAT Calculation Data', [], 'info_log');

        try {
            $response = $this->dataGenerator->callVatCalculationApi();
            $totalRecords = count($response);
            ResponseHandler::info('Processing Invoice Records', ['total_records' => $totalRecords], 'info_log');

            foreach ($response as $row) {
                InvoiceTrack::saveInvoiceTrackData($row);
            }

            ResponseHandler::success('Invoice data fetched and saved successfully', ['total_saved' => $totalRecords], 'success_log');
        } catch (\Exception $e) {
            ResponseHandler::error('Error in fetching invoice data', ['error' => $e->getMessage()], 'error_log');
        }
    }

    public function fetchAndStoreFlatfileVatData()
    {
        ini_set('max_execution_time', 0);
        ini_set('memory_limit', -1);
        ResponseHandler::info('Fetching Flatfile VAT Invoice Data', [], 'info_log');

        try {
            $response = $this->dataGenerator->callFlatfileVatInvoiceDataApi();
            $totalRecords = count($response);

            foreach ($response as $row) {
                FlatfileVatInvoiceData::saveInvoiceData($row);
            }

            ResponseHandler::success('Flatfile VAT invoice data fetched and saved successfully', ['total_saved' => $totalRecords], 'success_log');
        } catch (\Exception $e) {
            ResponseHandler::error('Error in fetching flatfile VAT data', ['error' => $e->getMessage()], 'error_log');
        }
    }

    public function fetchAndStoreCollectionData()
    {
        ini_set('max_execution_time', 0);
        ini_set('memory_limit', -1);
        ResponseHandler::info('Fetching Collections Data', [], 'info_log');

        try {
            $response = $this->dataGenerator->callCollectionsDataApi();
            $totalRecords = count($response);

            foreach ($response as $row) {
                DataCollection::saveCollectionData($row);
            }

            ResponseHandler::success('Collection data fetched and saved successfully', ['total_saved' => $totalRecords], 'success_log');
        } catch (\Exception $e) {
            ResponseHandler::error('Error in fetching collection data', ['error' => $e->getMessage()], 'error_log');
        }
    }
}
