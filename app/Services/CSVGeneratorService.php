<?php

namespace App\Services;

use App\Models\InvoiceTrack;
use App\Models\DataCollection;
use Illuminate\Http\JsonResponse;
use App\Models\SellerInventoryItem;
use Illuminate\Support\Facades\Log;
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
     * @return @return \Illuminate\Http\JsonResponse|void
     */
    public function saveDataSellerInventoryItemsApi()
    {
        ini_set('max_execution_time', 0);
        try {
            $page = 1;
            $hasMorePages = true;

            while ($hasMorePages) {
                try {
                    $response = $this->dataGenerator->callSellerInventoryItemsApi($page);

                    if ($response != null && $response->getStatusCode() === 200) {
                        $responseData = json_decode($response->getBody(), true);

                        foreach ($responseData['data'] as $row) {
                            Log::info('Processing ASIN: ' . $row['asin']);

                            SellerInventoryItem::updateOrCreate(
                                [
                                    'customer_unique_id' => $row['customer_unique_id'],
                                    'asin' => $row['asin'],  // Identificatore unico per l'aggiornamento
                                ],
                                [
                                    'marketplace_id' => $row['marketplace_id'] ?? null,
                                    'date' => $row['date'] ?? null,
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

                        $hasMorePages = $page < $responseData['last_page'];
                        $page++;
                    } else {
                        Log::error('Error API response: ' . $response->getBody());
                        $hasMorePages = false;
                    }
                } catch (\Exception $e) {
                    Log::error('Error API call: ' . $e->getMessage());
                    $hasMorePages = false;
                }
            }

            return response()->json(['message' => 'Dati di Seller Inventory Items salvati con successo'], 200);
        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage(),
                'code' => $e->getCode(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
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
        try {
            $page = 1;
            $hasMorePages = true;
            $arrayData = [];
            while ($hasMorePages) {
                try {
                    $response = $this->dataGenerator->callVatCalculationApi($page);

                    if ($response->getStatusCode() === 200) {
                        $responseData = json_decode($response->getBody(), true);

                        foreach ($responseData['data'] as $row) {
                            Log::info('Data: ' . $row['document_date']);
                            $data = [
                                'document_date' => $row['document_date'],
                                'registration_date' => $row['registration_date'],
                                'document_number' => $row['document_number'],
                                'document_type' => $row['document_type'],
                                'currency' => $row['currency'], // Divisa
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

                        $hasMorePages = $page < $responseData['last_page'];
                        $page++;
                    } else {
                        Log::error('Error API: ' . $response->getBody());
                        $hasMorePages = false;
                    }
                } catch (\Exception $e) {
                    Log::error('Error API: ' . $e->getMessage());
                    $hasMorePages = false;
                }
            }

            $filePath = storage_path('app/temp/InvoiceTrack.csv');
            $result = $this->streamCSV($arrayData, $filePath);
            Log::info('InvoiceTrack.csv created');
            return $result;
        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage(),
                'code' => $e->getCode(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
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
        try {
            $page = 1;
            $hasMorePages = true;
            $arrayData = [];
            while ($hasMorePages) {
                try {
                    $response = $this->dataGenerator->callFlatfileVatInvoiceDataApi($page);

                    if ($response->getStatusCode() === 200) {
                        $responseData = json_decode($response->getBody(), true);

                        foreach ($responseData['data'] as $row) {

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

                        $hasMorePages = $page < $responseData['last_page'];
                        $page++; // Incrementa il numero della pagina per la successiva chiamata
                    } else {
                        Log::error('Error API: ' . $response->getBody());
                        return response()->json([
                            'error' => 'Errore API: ' . $response->getBody(),
                        ], 500);
                        $hasMorePages = false;
                    }
                } catch (\Exception $e) {
                    Log::error('Error API: ' . $e->getMessage());
                    return response()->json([
                        'error' => 'Errore API: ' . $e->getMessage(),
                    ], 500);
                    $hasMorePages = false;
                }
            }
            Log::info('Creation file...');
            $filePath = storage_path('app/temp/Flatfilevatinvoicedata.csv');
            return $this->streamCSV($arrayData, $filePath);
        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage(),
                'code' => $e->getCode(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
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
        try {
            $page = 1;
            $hasMorePages = true;
            $arrayData = [];
            while ($hasMorePages) {
                try {
                    $response = $this->dataGenerator->callCollectionsDataApi($page);

                    if ($response != null && $response->getStatusCode() === 200) {
                        $responseData = json_decode($response->getBody(), true);

                        foreach ($responseData['data'] as $row) {
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
                            Log::info(
                                'Data: ' .
                                    "Data Di Pagamento: " . $row['deposit_date'] . "\n" .
                                    "Data Doc: " . $row['document_date'] . "\n" .
                                    "Data Reg: " . $row['registration_date'] . "\n" .
                                    "Numero Doc: " . $row['document_number'] . "\n" .
                                    "Tipo Documento: " . $row['document_type'] . "\n" .
                                    "Tipo Transazione: " . $row['transaction_type'] . "\n" .
                                    "Divisa: " . $row['currency'] . "\n" .
                                    "Importo: " . $row['amount'] . "\n" .
                                    "Codice Univoco (chiave RIF3): " . $row['unique_code_rif3'] . "\n" .
                                    "Tipo Di Registrazione (CF o VAT): " . $row['buyer_tax_registration_type'] . "\n" .
                                    "N. Partita IVA Cliente Finale: " . $row['buyer_vat_number']
                            );

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
                            $arrayData[] = $data;
                            // Chiama il metodo del modello per salvare i dati
                            DataCollection::saveCollectionData($data);
                        }

                        $hasMorePages = $page < $responseData['last_page'];
                        $page++;
                        Log::info('API call for page: ' . $page);
                    } else {
                        throw new \Exception("Errore API: " . $response->getBody());
                    }
                } catch (\Exception $e) {
                    Log::error('Error API: ' . $e->getMessage());
                    $hasMorePages = false;
                }
            }

            $filePath = storage_path('app/temp/FlatFileSettlement.csv');
            return $this->streamCSV($arrayData, $filePath);
        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage(),
                'code' => $e->getCode(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
            ], 500);
        }
    }

    /**
     * Metodo privato per gestire lo streaming dei dati in CSV
     *
     * @param array $data
     * @param string $filePath
     *
     * @return JsonResponse
     */
    public function streamCSV($data, $filePath)
    {
        Log::info('Stream CSV');
        try {
            $handle = fopen($filePath, 'w+');
            Log::info('File opened');
            if (!empty($data)) {
                Log::info('Data is not empty');
                $columns = array_keys($data[0]);
                fputcsv($handle, $columns);
                foreach ($data as $row) {
                    fputcsv($handle, $row);
                }
            } else {
                Log::info('Data is empty');
            }
            Log::info('File created');
            fclose($handle);
            try {
                return $this->fileEncryptionService->saveFile($filePath);
            } catch (\Exception $e) {
                return response()->json([
                    'error' => $e->getMessage(),
                    'code' => $e->getCode(),
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                    'trace' => $e->getTraceAsString(),
                ], 500);
            }
        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage(),
                'code' => $e->getCode(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
            ], 500);
        } finally {
            unlink($filePath);
        }
    }
}
