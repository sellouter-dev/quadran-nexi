<?php

namespace App\Services;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use App\Services\ResponseHandler;

class DataGeneratorAmazon
{
    private $API_URL;
    private $client;

    /**
     * DataGeneratorAmazon constructor.
     *
     * Inizializza l'URL base e il client Guzzle con timeout personalizzati.
     */
    public function __construct()
    {
        $this->API_URL = env("API_URL");
        $this->client = new Client([
            'timeout'         => 0,
            'connect_timeout' => 0,
        ]);
    }

    /**
     * Effettua una chiamata GET all'API per l'endpoint specificato.
     *
     * @param string $endpoint Endpoint da chiamare.
     * @return array Risposta decodificata in array.
     */
    public function makeApiCall(string $endpoint)
    {
        $apiUrl = $this->API_URL . $endpoint;
        ResponseHandler::info("Calling API", ['url' => $apiUrl], 'sellouter');

        try {
            $response = $this->client->get($apiUrl, [
                'headers' => [
                    'Authorization' => 'Bearer dJQn4>501<#R',
                ],
                'verify' => false,
            ]);
            $body = $response->getBody()->getContents();
            ResponseHandler::info("API get success", ['url' => $apiUrl], 'sellouter');
            return json_decode($body, true);
        } catch (RequestException $e) {
            $response = $e->getResponse();
            ResponseHandler::error("API RequestException: " . $e->getMessage(), [
                'response'    => $response ? (string)$response->getBody() : 'No response',
                'status_code' => $response ? $response->getStatusCode() : 'null'
            ], 'sellouter');
            return [];
        } catch (\Exception $e) {
            ResponseHandler::error("API General Exception: " . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ], 'sellouter');
            return [];
        }
    }

    /**
     * Richiama l'endpoint per il calcolo dell'IVA e restituisce i dati.
     *
     * @return array Dati ricevuti dall'endpoint.
     */
    public function callVatCalculationApi()
    {
        ini_set('memory_limit', -1);
        try {
            ResponseHandler::info("Calling VAT Calculation API", [], 'sellouter');
            $response = $this->makeApiCall('/vat-calculation');
            return $response['data'] ?? [];
        } catch (\Exception $e) {
            ResponseHandler::error("Error in callVatCalculationApi", [
                'error' => $e->getMessage(),
                'file'  => $e->getFile(),
                'line'  => $e->getLine(),
            ], 'sellouter');
            return [];
        }
    }

    /**
     * Richiama l'endpoint per i dati Flatfile VAT Invoice e restituisce i dati.
     *
     * @return array Dati ricevuti dall'endpoint.
     */
    public function callFlatfileVatInvoiceDataApi()
    {
        ini_set('memory_limit', -1);
        try {
            ResponseHandler::info("Calling Flatfile VAT Invoice Data API", [], 'sellouter');
            $response = $this->makeApiCall('/flatfile-vat-invoice-data');
            return $response['data'] ?? [];
        } catch (\Exception $e) {
            ResponseHandler::error("Error in callFlatfileVatInvoiceDataApi", [
                'error' => $e->getMessage(),
                'file'  => $e->getFile(),
                'line'  => $e->getLine(),
            ], 'sellouter');
            return [];
        }
    }

    /**
     * Richiama l'endpoint per i dati delle Collections e restituisce i dati.
     *
     * @return array Dati ricevuti dall'endpoint.
     */
    public function callCollectionsDataApi()
    {
        ini_set('memory_limit', -1);
        try {
            ResponseHandler::info("Calling Collections Data API", [], 'sellouter');
            $response = $this->makeApiCall('/collections-data');
            return $response['data'] ?? [];
        } catch (\Exception $e) {
            ResponseHandler::error("Error in callCollectionsDataApi", [
                'error' => $e->getMessage(),
                'file'  => $e->getFile(),
                'line'  => $e->getLine(),
            ], 'sellouter');
            return [];
        }
    }

    /**
     * Richiama l'endpoint per i dati degli Seller Inventory Items e restituisce i dati.
     *
     * @return array Dati ricevuti dall'endpoint.
     */
    public function callSellerInventoryItemsApi()
    {
        ini_set('memory_limit', -1);
        try {
            ResponseHandler::info("Calling Seller Inventory Items API", [], 'sellouter');
            $response = $this->makeApiCall('/seller-inventory-items');
            return $response['data'] ?? [];
        } catch (\Exception $e) {
            ResponseHandler::error("Error in callSellerInventoryItemsApi", [
                'error' => $e->getMessage(),
                'file'  => $e->getFile(),
                'line'  => $e->getLine(),
            ], 'sellouter');
            return [];
        }
    }
}
