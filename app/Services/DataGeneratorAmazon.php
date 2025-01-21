<?php

namespace App\Services;

use GuzzleHttp\Client;
use App\Models\SellerInventoryItem;
use Illuminate\Support\Facades\Log;
use Psr\Http\Message\ResponseInterface;

class DataGeneratorAmazon
{
    private $API_URL;

    public function __construct()
    {
        $this->API_URL = "http://192.168.1.118:521/api";
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
