<?php

namespace App\Services;

use App\Models\InvoiceTrack;
use App\Models\DataCollection;
use App\Models\SellerInventoryItem;
use App\Services\DataGeneratorAmazon;
use App\Models\FlatfileVatInvoiceData;
use App\Services\FileEncryptionService;

class CSVGeneratorService
{
    protected $fileEncryptionService;
    private $dataGenerator;

    public function __construct()
    {
        $this->fileEncryptionService = new FileEncryptionService();
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
                // ResponseHandler::info('Processing ASIN', [
                //     'index' => $index + 1,
                //     'asin' => $row['asin'],
                //     'customer_unique_id' => $row['customer_unique_id'],
                // ], 'info_log');

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

    /**
     * downloadDataCalculationComputed
     *
     * @return \Illuminate\Http\JsonResponse|void
     */
    public function downloadDataCalculationComputed()
    {
        ini_set('max_execution_time', 0);
        ini_set('memory_limit', -1);
        ResponseHandler::info('Starting downloadDataCalculationComputed process', [], 'info_log');

        $arrayData = [];
        try {
            ResponseHandler::info('Calling VAT Calculation API', [], 'info_log');
            $response = $this->dataGenerator->callVatCalculationApi();

            $totalRecords = count($response);
            ResponseHandler::info('Processing records', ['total_records' => $totalRecords], 'info_log');

            foreach ($response as $index => $row) {
                // ResponseHandler::info('Processing record', [
                //     'index' => $index + 1,
                //     'document_number' => $row['document_number'],
                //     'document_date' => $row['document_date'],
                // ], 'info_log');

                $data = [
                    'document_date' => $row['document_date'],
                    'registration_date' => $row['registration_date'],
                    'document_number' => $row['document_number'],
                    'document_type' => $row['document_type'],
                    'currency' => $row['currency'],
                    'gross_amount' => $row['gross_amount'],
                    'net_amount' => $row['net_amount'],
                    'vat_amount' => $row['vat_amount'],
                    'split_payment' => $row['split_payment'],
                    'vat_code' => $row['vat_code'],
                    'unique_code_rif3' => $row['unique_code_rif3'],
                    'buyer_tax_registration_type' => $row['buyer_tax_registration_type'],
                    'buyer_vat_number' => $row['buyer_vat_number'],
                ];

                $arrayData[] = [
                    'Data Doc' => $row['document_date'],
                    'Data Reg' => $row['registration_date'],
                    'Numero Doc' => $row['document_number'],
                    'Tipo Documento' => $row['document_type'],
                    'Divisa' => $row['currency'],
                    'Importo Lordo' => $row['gross_amount'],
                    'Importo Netto' => $row['net_amount'],
                    'Importo IVA' => $row['vat_amount'],
                    'Split Payment' => $row['split_payment'],
                    'Codice IVA' => $row['vat_code'],
                    'Codice Univoco (chiave RIF3)' => $row['unique_code_rif3'],
                    'Tipo Di Registrazione (CF o VAT)' => $row['buyer_tax_registration_type'],
                    'N. Partita IVA Cliente Finale' => $row['buyer_vat_number'],
                ];

                InvoiceTrack::saveInvoiceTrackData($data);
            }

            ResponseHandler::success('Data downloaded and saved successfully', ['total_saved' => $totalRecords], 'success_log');
            $filePath = storage_path('app/temp/InvoiceTrack.csv');
            ResponseHandler::info('Generating CSV file', ['file_path' => $filePath], 'info_log');

            $result = $this->streamCSV($arrayData, $filePath);

            ResponseHandler::success('InvoiceTrack.csv created successfully', ['file_path' => $filePath], 'success_log');
            return $result;
        } catch (\Exception $e) {
            ResponseHandler::error('Exception in downloadDataCalculationComputed', [
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
            ], 'error_log');

            return response()->json([
                'error' => $e->getMessage(),
                'code' => $e->getCode()
            ], 500);
        }
    }

    /**
     * downloadDataOfFlatfilevatinvoicedata
     *
     * Questa funzione esegue un ciclo per chiamare un'API esterna che restituisce
     * i dati paginati relativi alla flat file VAT invoice data. La funzione continua
     * a richiamare l'API fino a quando non vengono ricevuti tutti i dati
     * e poi genera un file CSV con il risultato.
     *
     * @return \Illuminate\Http\JsonResponse|void
     * In caso di successo, restituisce un file CSV contenente i dati ottenuti dall'API.
     * In caso di errore, restituisce una risposta JSON con i dettagli dell'errore.
     */
    public function downloadDataOfFlatfilevatinvoicedata()
    {
        ini_set('max_execution_time', 0);
        ini_set('memory_limit', -1);
        ResponseHandler::info('Starting downloadDataOfFlatfilevatinvoicedata process', [], 'info_log');

        try {
            $arrayData = [];
            ResponseHandler::info('Calling Flatfile VAT Invoice Data API', [], 'info_log');

            $response = $this->dataGenerator->callFlatfileVatInvoiceDataApi();
            ResponseHandler::info('API response received downloadDataOfFlatfilevatinvoicedata', ['response' => $response], 'info_log');

            $totalRecords = count($response);
            ResponseHandler::info('Processing records', ['total_records' => $totalRecords], 'info_log');

            foreach ($response as $index => $row) {
                ResponseHandler::info('Processing record', [
                    'index' => $index + 1,
                    'buyer_vat_number' => $row['buyer_vat_number'],
                    'buyer_name' => $row['buyer_name']
                ], 'info_log');

                $data = [
                    'buyer_name' => $row['buyer_name'],
                    'buyer_address' => $row['buyer_address'],
                    'buyer_postal_code' => $row['buyer_postal_code'],
                    'buyer_city' => $row['buyer_city'],
                    'buyer_country' => $row['buyer_country'],
                    'buyer_province_code' => $row['buyer_province_code'],
                    'buyer_tax_registration_type' => $row['buyer_tax_registration_type'],
                    'buyer_vat_number' => $row['buyer_vat_number'],
                ];

                $arrayData[] = [
                    'Denominazione Del Cliente' => $row['buyer_name'],
                    'Indirizzo Di Residenza Del Cliente' => $row['buyer_address'],
                    'CAP Di Residenza Del Cliente' => $row['buyer_postal_code'],
                    'Località Di Residenza Del Cliente' => $row['buyer_city'],
                    'Paese Di Residenza Del Cliente' => $row['buyer_country'],
                    'Codice Provincia Di Residenza Del Cliente' => $row['buyer_province_code'],
                    'Tipo Di Registrazione (CF o VAT)' => $row['buyer_tax_registration_type'],
                    'N. Partita IVA Cliente Finale' => $row['buyer_vat_number'],
                ];

                FlatfileVatInvoiceData::saveInvoiceData($data);
            }

            ResponseHandler::success('Flatfile VAT invoice data processed successfully', [
                'total_saved' => $totalRecords
            ], 'success_log');

            $filePath = storage_path('app/temp/Flatfilevatinvoicedata.csv');

            ResponseHandler::info('Generating CSV file', ['file_path' => $filePath], 'info_log');

            $result = $this->streamCSV($arrayData, $filePath);

            ResponseHandler::success('Flatfilevatinvoicedata.csv created successfully', ['file_path' => $filePath], 'success_log');
            return $result;
        } catch (\Exception $e) {
            ResponseHandler::error('Exception in downloadDataOfFlatfilevatinvoicedata', [
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
            ], 'error_log');

            return response()->json([
                'error' => $e->getMessage(),
                'code' => $e->getCode()
            ], 500);
        }
    }


    /**
     * downloadDataOfCollections
     *
     * Questa funzione esegue un ciclo per chiamare un'API esterna che restituisce
     * i dati paginati relativi alla "flat file VAT invoice data". La funzione continua
     * a richiamare l'API fino a quando non vengono ricevuti tutti i dati
     * e poi genera un file CSV con il risultato.
     *
     * @return \Illuminate\Http\JsonResponse|void
     * In caso di successo, restituisce un file CSV contenente i dati ottenuti dall'API.
     * In caso di errore, restituisce una risposta JSON con i dettagli dell'errore.
     */
    public function downloadDataOfCollections()
    {
        ini_set('max_execution_time', 0);
        ini_set('memory_limit', -1);
        ResponseHandler::info('Starting downloadDataOfCollections process', [], 'info_log');
        $arrayData = [];

        try {
            ResponseHandler::info('Calling Collections Data API', [], 'info_log');
            $response = $this->dataGenerator->callCollectionsDataApi();
            ResponseHandler::info('API response received', ['response' => $response], 'info_log');

            $totalRecords = count($response);
            ResponseHandler::info('Processing records', ['total_records' => $totalRecords], 'info_log');

            foreach ($response as $index => $row) {

                $data = [
                    'deposit_date' => $row['deposit_date'],
                    'document_date' => $row['document_date'],
                    'registration_date' => $row['registration_date'],
                    'document_number' => $row['document_number'],
                    'document_type' => $row['document_type'],
                    'transaction_type' => $row['transaction_type'],
                    'currency' => $row['currency'],
                    'amount' => $row['amount'],
                    'unique_code_rif3' => $row['unique_code_rif3'],
                    'buyer_tax_registration_type' => $row['buyer_tax_registration_type'],
                    'buyer_vat_number' => $row['buyer_vat_number'],
                ];

                $arrayData[] = [
                    'Data Di Pagamento' => $row['deposit_date'],
                    'Data Doc' => $row['document_date'],
                    'Data Reg' => $row['registration_date'],
                    'Numero Doc' => $row['document_number'],
                    'Tipo Documento' => $row['document_type'],
                    'Tipo Transazione' => $row['transaction_type'],
                    'Divisa' => $row['currency'],
                    'Importo' => $row['amount'],
                    'Codice Univoco (chiave RIF3)' => $row['unique_code_rif3'],
                    'Tipo Di Registrazione (CF o VAT)' => $row['buyer_tax_registration_type'],
                    'N. Partita IVA Cliente Finale' => $row['buyer_vat_number']
                ];

                DataCollection::saveCollectionData($data);
            }

            ResponseHandler::success('Collections data processed successfully', ['total_saved' => $totalRecords], 'success_log');

            $filePath = storage_path('app/temp/FlatFileSettlement.csv');
            $result = $this->streamCSV($arrayData, $filePath);

            ResponseHandler::success('FlatFileSettlement.csv created successfully', ['file_path' => $filePath], 'success_log');
            return $result;
        } catch (\Exception $e) {
            ResponseHandler::error('Exception in downloadDataOfCollections', [
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
            ], 'error_log');

            return response()->json([
                'error' => $e->getMessage(),
                'code' => $e->getCode()
            ], 500);
        }
    }

    /**
     * Metodo privato per gestire lo streaming dei dati in CSV
     *
     * @param array $data
     * @param string $filePath
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function streamCSV($data, $filePath)
    {
        ResponseHandler::info('Starting CSV streaming process', ['file_path' => $filePath], 'info_log');

        try {
            $handle = fopen($filePath, 'w+');
            ResponseHandler::info('File opened successfully', ['file_path' => $filePath], 'info_log');

            if (!empty($data)) {
                ResponseHandler::info('Data found, writing to CSV', ['total_records' => count($data)], 'info_log');

                $columns = array_keys($data[0]);
                fputcsv($handle, $columns);

                foreach ($data as $index => $row) {
                    fputcsv($handle, $row);
                }
            } else {
                ResponseHandler::info('No data found to write to CSV', [], 'warning_log');
            }

            fclose($handle);
            ResponseHandler::success('CSV file created successfully', ['file_path' => $filePath], 'success_log');

            try {
                ResponseHandler::info('Starting file encryption', ['file_path' => $filePath], 'info_log');
                return $this->fileEncryptionService->saveFile($filePath);
            } catch (\Exception $e) {
                ResponseHandler::error('Error in file encryption', [
                    'error' => $e->getMessage(),
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                ], 'error_log');

                return response()->json([
                    'error' => 'Error encrypting file: ' . $e->getMessage(),
                    'code' => $e->getCode(),
                ], 500);
            }
        } catch (\Exception $e) {
            ResponseHandler::error('Exception in streamCSV process', [
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
            ], 'error_log');

            return response()->json([
                'error' => $e->getMessage(),
                'code' => $e->getCode()
            ], 500);
        } finally {
            if (file_exists($filePath)) {
                unlink($filePath);
                ResponseHandler::info('Temporary file deleted', ['file_path' => $filePath], 'info_log');
            } else {
                ResponseHandler::warning('Temporary file not found for deletion', ['file_path' => $filePath], 'warning_log');
            }
        }
    }
}
