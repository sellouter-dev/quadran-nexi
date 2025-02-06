<?php

namespace App\Services;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Support\Facades\Log;

class DataGeneratorAmazon
{
    private $API_URL;
    private $client;

    public function __construct()
    {
        $this->API_URL = env("API_URL");
        $this->client = new Client([
            'timeout' => 0,
            'connect_timeout' => 0,
        ]);
    }

    public function makeApiCall(string $endpoint, int $pagina = 1)
    {
        ini_set('memory_limit', -1);
        ini_set('max_execution_time', -1);
        $apiUrl = $this->API_URL . $endpoint;
        ResponseHandler::info("Calling API", ['url' => $apiUrl, 'page' => $pagina], 'info_log');

        try {
            $response = $this->client->get($apiUrl, [
                'headers' => [
                    'Authorization' => 'Bearer dJQn4>501<#R',
                ],
                'verify' => false,
            ]);
            $body = $response->getBody()->getContents();

            return json_decode($body, true);
        } catch (RequestException $e) {
            $response = $e->getResponse();
            Log::error("API RequestException: " . $e->getMessage(), [
                'response' => $response ? (string) $response->getBody() : 'No response',
                'status_code' => $response ? $response->getStatusCode() : 'null'
            ]);
            return [];
        } catch (\Exception $e) {
            Log::error("API General Exception: " . $e->getMessage());
            return [];
        }
    }

    public function callVatCalculationApi()
    {
        ini_set('memory_limit', -1);
        $allData = [];
        $page = 1;

        do {
            $response = $this->makeApiCall('/vat-calculation', $page);
            $data = $response['data'] ?? [];
            $hasRecord = $response['hasRecord'] ?? false;
            $allData = array_merge($allData, $data);
            $page++;
            Log::info("Vat calculation data page $page", [
                'data_count' => count($data),
                'hasRecord' => $hasRecord,
            ]);
        } while ($hasRecord);

        return $allData;
    }

    public function callFlatfileVatInvoiceDataApi()
    {
        ini_set('memory_limit', -1);
        $allData = [];
        $page = 1;

        do {
            $response = $this->makeApiCall('/flatfile-vat-invoice-data', $page);
            $data = $response['data'] ?? [];
            $hasRecord = $response['hasRecord'] ?? false;
            $allData = array_merge($allData, $data);
            Log::info("Flat file vat data page $page", [
                'data_count' => count($data),
                'hasRecord' => $hasRecord,
            ]);
            $page++;
        } while ($hasRecord);

        return $allData;
    }

    public function callCollectionsDataApi()
    {
        ini_set('memory_limit', -1);
        $allData = [];
        $page = 1;

        do {
            $response = $this->makeApiCall('/collections-data', $page);
            $data = $response['data'] ?? [];
            $hasRecord = $response['hasRecord'] ?? false;
            $allData = array_merge($allData, $data);
            Log::info("Collections data page $page", [
                'data_count' => count($data),
                'hasRecord' => $hasRecord,
            ]);
            $page++;
        } while ($hasRecord);
        return $allData;
    }

    public function callSellerInventoryItemsApi()
    {
        ini_set('memory_limit', -1);
        $allData = [];
        $page = 1;
        do {
            $response = $this->makeApiCall('/seller-inventory-items', $page);
            $data = $response['data'] ?? [];
            $hasRecord = $response['hasRecord'] ?? false;
            $allData = array_merge($allData, $data);
            Log::info("Inventory data page $page", [
                'data_count' => count($data),
                'hasRecord' => $hasRecord,
            ]);
            $page++;
        } while ($hasRecord);
        return $allData;
    }
}
