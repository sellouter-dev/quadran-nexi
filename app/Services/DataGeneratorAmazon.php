<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;

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
     * @return \Psr\Http\Message\ResponseInterface
     *
     */
    public function callVatCalculationApi($page)
    {
        try {
            $client = new \GuzzleHttp\Client();
            $apiUrl = $this->API_URL . '/vat-calculation';
            return $client->post($apiUrl, [
                'form_params' => [
                    'page' => $page,
                    'key' => 'dJQn4>501<#R'
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
     * @return \Psr\Http\Message\ResponseInterface
     *
     * */
    public function callFlatfileVatInvoiceDataApi($page)
    {
        try {
            $client = new \GuzzleHttp\Client();
            $apiUrl = $this->API_URL . '/flatfile-vat-invoice-data';
            Log::info('API URL: ' . $apiUrl);

            return $client->post($apiUrl, [
                'form_params' => [
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
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function callCollectionsDataApi($page)
    {
        try {
            $client = new \GuzzleHttp\Client();
            $apiUrl = $this->API_URL . '/collections-data';

            return $client->post($apiUrl, [
                'form_params' => [
                    'page' => $page,
                    'key' => 'dJQn4>501<#R'
                ]
            ]);
        } catch (\Exception $e) {
            Log::info('Error: ' . $e->getMessage());
        }
    }
}
