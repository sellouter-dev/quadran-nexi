<?php

namespace App\Services;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use App\Services\ResponseHandler;
use Exception;

class DataGeneratorAmazon
{
    private $API_URL;
    private $client;
    /**
     * Access token ottenuto dal token endpoint.
     *
     * @var string|null
     */
    private $accessToken = null;

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
     * Ottiene un access token dal token endpoint OAuth.
     *
     * Il token viene richiesto effettuando una POST all'endpoint impostato in OAUTH_TOKEN_URL
     * inviando i parametri grant_type, client_id e client_secret.
     *
     * @return string|null Il token se ottenuto correttamente, altrimenti null.
     */
    private function getAccessToken()
    {
        // Se già presente un token, lo restituisce (potrebbe essere implementata una logica di scadenza)
        if ($this->accessToken) {
            return $this->accessToken;
        }

        $tokenUrl = env('OAUTH_TOKEN_URL');
        $clientId = env('OAUTH_CLIENT_ID');
        $clientSecret = env('OAUTH_CLIENT_SECRET');

        try {
            ResponseHandler::info("Requesting access token", [
                'token_url'   => $tokenUrl,
                'client_id'   => $clientId
            ], 'sellouter');

            $response = $this->client->post($tokenUrl, [
                'headers' => [
                    'Content-Type' => 'application/x-www-form-urlencoded',
                ],
                'form_params' => [
                    'grant_type'    => 'client_credentials',
                    'client_id'     => $clientId,
                    'client_secret' => $clientSecret,
                ],
                'verify' => false,
            ]);

            $body = $response->getBody()->getContents();
            $data = json_decode($body, true);

            if (isset($data['access_token'])) {
                $this->accessToken = $data['access_token'];
                ResponseHandler::info("Access token obtained successfully", ['access_token' => $this->accessToken], 'sellouter');
                return $this->accessToken;
            } else {
                ResponseHandler::error("Access token not found in response", ['response' => $data], 'sellouter');
                return null;
            }
        } catch (Exception $e) {
            ResponseHandler::error("Error obtaining access token", [
                'error' => $e->getMessage(),
                'file'  => $e->getFile(),
                'line'  => $e->getLine(),
            ], 'sellouter');
            return null;
        }
    }

    /**
     * Effettua una chiamata GET all'API per l'endpoint specificato, utilizzando l'access token ottenuto.
     *
     * @param string $endpoint Endpoint da chiamare.
     * @return array Risposta decodificata in array.
     */
    public function makeApiCall(string $endpoint)
    {
        $apiUrl = $this->API_URL . $endpoint;
        $accessToken = $this->getAccessToken();

        if (!$accessToken) {
            ResponseHandler::error("No access token available", [], 'sellouter');
            return [];
        }

        ResponseHandler::info("Calling API", ['url' => $apiUrl, 'accessToken' => $accessToken], 'sellouter');

        try {
            // OUR API TOKEN: dJQn4>501<#R
            $response = $this->client->get($apiUrl, [
                'headers' => [
                    'Authorization' => 'Bearer ' . $accessToken,
                ],
                'verify' => false,
            ]);
            $body = $response->getBody()->getContents();
            ResponseHandler::info("API get success", ['url' => $apiUrl, 'body' => $body], 'sellouter');
            return json_decode($body, true);
        } catch (RequestException $e) {
            $response = $e->getResponse();
            ResponseHandler::error("API RequestException: " . $e->getMessage(), [
                'response'    => $response ? (string)$response->getBody() : 'No response',
                'status_code' => $response ? $response->getStatusCode() : 'null'
            ], 'sellouter');
            return [];
        } catch (Exception $e) {
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
        try {
            ResponseHandler::info("Calling VAT Calculation API", [], 'sellouter');
            $response = $this->makeApiCall('/vat-calculation');
            return $response['data'] ?? [];
        } catch (Exception $e) {
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
        try {
            ResponseHandler::info("Calling Flatfile VAT Invoice Data API", [], 'sellouter');
            $response = $this->makeApiCall('/flatfile-vat-invoice-data');
            return $response['data'] ?? [];
        } catch (Exception $e) {
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
        try {
            ResponseHandler::info("Calling Collections Data API", [], 'sellouter');
            $response = $this->makeApiCall('/collections-data');
            return $response['data'] ?? [];
        } catch (Exception $e) {
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
        try {
            ResponseHandler::info("Calling Seller Inventory Items API", [], 'sellouter');
            $response = $this->makeApiCall('/seller-inventory-items');
            return $response['data'] ?? [];
        } catch (Exception $e) {
            ResponseHandler::error("Error in callSellerInventoryItemsApi", [
                'error' => $e->getMessage(),
                'file'  => $e->getFile(),
                'line'  => $e->getLine(),
            ], 'sellouter');
            return [];
        }
    }
}
