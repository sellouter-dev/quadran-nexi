<?php

namespace App\Services;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use App\Services\ResponseHandler;
use Exception;

class DataGeneratorAmazon
{
    private $API_URL;
    private $API_URL_QUADRAN;
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
        $this->API_URL_QUADRAN = env("API_URL_QUADRAN");
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
            ResponseHandler::info("Richiesta token di accesso", [
                'token_url' => $tokenUrl,
                'client_id' => $clientId
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
                ResponseHandler::success("Token di accesso ottenuto con successo", ['access_token' => $this->accessToken], 'sellouter');
                return $this->accessToken;
            } else {
                ResponseHandler::error("Token di accesso non trovato nella risposta", ['response' => $data], 'sellouter');
                return null;
            }
        } catch (Exception $e) {
            ResponseHandler::error("Errore durante l'ottenimento del token di accesso", [
                'errore' => $e->getMessage(),
                'file'   => $e->getFile(),
                'linea'  => $e->getLine(),
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

        try {
            if (!env("CALL_DIRECTLY_QUADRAN_API")) {
                $apiUrl = $this->API_URL . $endpoint;
                $accessToken = $this->getAccessToken();
                if (!$accessToken) {
                    ResponseHandler::error("Token di accesso non disponibile", [], 'sellouter');
                    return [];
                }

                ResponseHandler::info("Chiamata API", ['url' => $apiUrl, 'accessToken' => $accessToken], 'sellouter');

                $response = $this->client->get($apiUrl, [
                    'headers' => [
                        'Authorization' => 'Bearer ' . $accessToken,
                    ],
                    'verify' => false,
                ]);
            } else {
                $apiUrl =  $this->API_URL_QUADRAN . $endpoint;
                ResponseHandler::info("Chiamata API", ['url' => $apiUrl], 'sellouter');

                $response = $this->client->get($apiUrl, [
                    'headers' => [
                        'Content-Type' => 'application/json',
                        'Authorization' => 'Bearer dJQn4>501<#R'
                    ],
                    'verify' => false,
                ]);
            }

            $body = $response->getBody()->getContents();
            ResponseHandler::success("Chiamata API eseguita con successo", ['url' => $apiUrl], 'sellouter');
            return json_decode($body, true);
        } catch (RequestException $e) {
            $response = $e->getResponse();
            ResponseHandler::error("RequestException API: " . $e->getMessage(), [
                'response'    => $response ? (string)$response->getBody() : 'Nessuna risposta',
                'status_code' => $response ? $response->getStatusCode() : 'null'
            ], 'sellouter');
            return [];
        } catch (Exception $e) {
            ResponseHandler::error("Eccezione Generale API: " . $e->getMessage(), [
                'file' => $e->getFile(),
                'linea' => $e->getLine()
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
            ResponseHandler::info("Chiamata API per il calcolo dell'IVA", [], 'sellouter');
            $response = $this->makeApiCall('/vat-calculation');
            return $response['data'] ?? [];
        } catch (Exception $e) {
            ResponseHandler::error("Errore in callVatCalculationApi", [
                'errore' => $e->getMessage(),
                'file'   => $e->getFile(),
                'linea'  => $e->getLine(),
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
            ResponseHandler::info("Chiamata API per Flatfile VAT Invoice Data", [], 'sellouter');
            $response = $this->makeApiCall('/flatfile-vat-invoice-data');
            return $response['data'] ?? [];
        } catch (Exception $e) {
            ResponseHandler::error("Errore in callFlatfileVatInvoiceDataApi", [
                'errore' => $e->getMessage(),
                'file'   => $e->getFile(),
                'linea'  => $e->getLine(),
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
            ResponseHandler::info("Chiamata API per Collections Data", [], 'sellouter');
            $response = $this->makeApiCall('/collections-data');
            return $response['data'] ?? [];
        } catch (Exception $e) {
            ResponseHandler::error("Errore in callCollectionsDataApi", [
                'errore' => $e->getMessage(),
                'file'   => $e->getFile(),
                'linea'  => $e->getLine(),
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
            ResponseHandler::info("Chiamata API per Seller Inventory Items", [], 'sellouter');
            $response = $this->makeApiCall('/seller-inventory-items');
            return $response['data'] ?? [];
        } catch (Exception $e) {
            ResponseHandler::error("Errore in callSellerInventoryItemsApi", [
                'errore' => $e->getMessage(),
                'file'   => $e->getFile(),
                'linea'  => $e->getLine(),
            ], 'sellouter');
            return [];
        }
    }
}
