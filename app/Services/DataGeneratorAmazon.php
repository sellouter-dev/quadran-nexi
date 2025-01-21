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
     * @param int $page.
     *
     * @return ResponseInterface
     *
     */
    public function callVatCalculationApi($page)
    {
        try {
            $client = new Client();
            $apiUrl = $this->API_URL . '/vat-calculation';

            return $client->get($apiUrl, [
                'query' => [
                    'page' => $page,
                    'key' => 'dJQn4>501<#R'  // Sicurezza da migliorare, vedi sotto
                ]
            ]);
        } catch (\Exception $e) {
            Log::info('Error: ' . $e->getMessage());
        }
    }

    /**
     * callFlatfileVatInvoiceDataApi
     *
     * @param int $page
     *
     * @return ResponseInterface
     *
     * */
    public function callFlatfileVatInvoiceDataApi($page)
    {
        try {
            $client = new Client();
            $apiUrl = $this->API_URL . '/flatfile-vat-invoice-data';

            return $client->get($apiUrl, [
                'query' => [
                    'page' => $page,
                    'key' => 'dJQn4>501<#R'
                ]
            ]);
        } catch (\Exception $e) {
            Log::info('Error: ' . $e->getMessage());
        }
    }

    /**
     * callCollectionsDataApi
     *
     * @param int $page
     *
     * @return ResponseInterface
     */
    public function callCollectionsDataApi($page)
    {
        try {
            $client = new Client();
            $apiUrl = $this->API_URL . '/collections-data';

            return $client->get($apiUrl, [
                'query' => [
                    'page' => $page,
                    'key' => 'dJQn4>501<#R'
                ]
            ]);
        } catch (\Exception $e) {
            Log::info('Error: ' . $e->getMessage());
        }
    }

    /**
     * callSellerInventoryItemsApi
     *
     * @param int $page
     * @return ResponseInterface
     */
    public function callSellerInventoryItemsApi($page)
    {
        try {
            $client = new Client();
            $apiUrl = $this->API_URL . '/seller-inventory-items';

            return $client->get($apiUrl, [
                'query' => [
                    'page' => $page,
                    'key' => 'dJQn4>501<#R'
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Error in callSellerInventoryItemsApi: ' . $e->getMessage());
            return null;
        }
    }
}
