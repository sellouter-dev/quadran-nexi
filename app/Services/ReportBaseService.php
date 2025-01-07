<?php

namespace App\Services;

use SellingPartnerApi\SellingPartnerApi;
use SellingPartnerApi\Enums\Endpoint;
use SellingPartnerApi\Seller\SellerConnector;
use App\Models\CustomerCredential;

abstract class ReportBaseService
{
    /**
     * Get the SellerConnector connector.
     *
     * @param CustomerCredential $credential The customer credential.
     * @return SellerConnector The SellingPartnerApi connector.
     */
    public function getConnector(CustomerCredential $credential): SellerConnector | null
    {
        try{
            return SellingPartnerApi::seller(
                clientId: $credential->lwa_client_id,
                clientSecret: $credential->lwa_client_secret,
                refreshToken: $credential->lwa_refresh_token,
                endpoint: Endpoint::EU
            );
        }
        catch(\Exception $e){
            ResponseHandler::error('Failed to get connector: ', [
                'messages' => $e->getMessage(),
                'code' => $e->getCode(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
            ]);
            return null;
        }
    }

    /**
     * Convert and save the report data.
     *
     * @param string $reportContent The report content.
     * @param CustomerCredential $credential The customer credential.
     * @return array The report array.
     */
    public function saveCsvReport(string $reportContent, CustomerCredential $credential): void
    {
        try{
            $rows = array_filter(explode("\n", $reportContent), 'trim');
            if (count($rows) <= 1) {
                ResponseHandler::info('report content is empty.');
                return;
            }

            $headers = str_getcsv(array_shift($rows), "\t");

            // Convert headers to lowercase and replace spaces with underscores
            $headers = array_map(function ($header) {
                return strtolower(str_replace([' ', '/'], '_', $header));
            }, $headers);

            array_unshift($headers, 'customer_unique_id', 'marketplace_id');

            array_map(function ($row) use ($headers, $credential) {
                $row = $credential->customer_unique_id . "\t" . $credential->marketplace_id . "\t" . $row;
                $currentRowData = str_getcsv($row, "\t");
                $formattedRow = array_combine($headers, $currentRowData);

                // Save the formatted row
                $this->saveReportData($formattedRow);

                return $formattedRow;
            }, $rows);

            ResponseHandler::success('Report saved successfully.');
        }
        catch(\Exception $e) {
            ResponseHandler::error('Failed to save report: ', [
                'messages' => $e->getMessage(),
                'code' => $e->getCode(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
            ]);
        }

    }

    /**
     * Save the report data.
     *
     * @param array $formattedReport
     *
     * @return void
     */
    abstract protected function saveReportData(array $formattedReport): void;
}
