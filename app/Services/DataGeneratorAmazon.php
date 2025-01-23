<?php

namespace App\Services;

use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;
use Psr\Http\Message\ResponseInterface;

class DataGeneratorAmazon
{
    private $API_URL;

    public function __construct()
    {
        $this->API_URL = env("API_URL");
    }

    /**
     * callVatCalculationApi
     *
     *
     * @return ResponseInterface
     *
     */
    public function callVatCalculationApi()
    {
        try {
            $client = new Client();
            $apiUrl = $this->API_URL . '/vat-calculation';
            ResponseHandler::info('Calling VAT calculation API', ['url' => $apiUrl], 'info_log');

            return $client->get($apiUrl, [
                'headers' => [
                    'Authorization' => 'Bearer dJQn4>501<#R'
                ]
            ]);
        } catch (\Exception $e) {
            Log::info('Error: ' . $e->getMessage());
        }
    }

    /**
     * callFlatfileVatInvoiceDataApi
     *
     *
     * @return ResponseInterface
     *
     * */
    public function callFlatfileVatInvoiceDataApi()
    {
        try {
            $client = new Client();
            $apiUrl = $this->API_URL . '/flatfile-vat-invoice-data';
            ResponseHandler::info('Calling Flatfile VAT invoice data API', ['url' => $apiUrl], 'info_log');
            return $client->get($apiUrl, [
                'headers' => [
                    'Authorization' => 'Bearer dJQn4>501<#R'
                ]
            ]);
        } catch (\Exception $e) {
            Log::info('Error: ' . $e->getMessage());
        }
    }

    /**
     * callCollectionsDataApi
     *
     *
     * @return ResponseInterface
     */
    public function callCollectionsDataApi()
    {
        try {
            $client = new Client();
            $apiUrl = $this->API_URL . '/collections-data';
            ResponseHandler::info('Call Collections Data API', ['url' => $apiUrl], 'info_log');
            return $client->get($apiUrl, [
                'headers' => [
                    'Authorization' => 'Bearer dJQn4>501<#R'
                ]
            ]);
        } catch (\Exception $e) {
            Log::info('Error: ' . $e->getMessage());
        }
    }

    /**
     * callSellerInventoryItemsApi
     *
     * @return ResponseInterface
     */
    public function callSellerInventoryItemsApi()
    {
        try {
            $client = new Client();
            $apiUrl = $this->API_URL . '/seller-inventory-items';
            ResponseHandler::info('Call Seller Inventory Items API', ['url' => $apiUrl], 'info_log');
            return $client->get($apiUrl, [
                'headers' => [
                    'Authorization' => 'Bearer dJQn4>501<#R'
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Error in callSellerInventoryItemsApi: ' . $e->getMessage());
            return null;
        }
    }
}
