<?php

namespace App\Services;

use SellingPartnerApi\Seller\ReportsV20210630;
use SellingPartnerApi\Seller\ReportsV20210630\Dto\CreateReportSpecification;
use Carbon\Carbon;
use App\Models\CustomerCredential;
use App\Models\SellerInventoryItem;
use Exception;
use Response;

class InventoryService extends ReportBaseService
{
    const REPORT_TYPE = 'GET_LEDGER_SUMMARY_VIEW_DATA';
    const AGGREGATED_BY_TIME_PERIOD = 'DAILY';
    const AGGREGATE_BY_LOCATION = 'COUNTRY';

    /**
     * Fetch the inventory report.
     *
     * @param CustomerCredential $credential The customer credential.
     * @return void
     */
    public function fetchInventory(CustomerCredential $credential): void
    {
        try {
            $sellerConnector = $this->getConnector($credential);
            if ($sellerConnector == null) {
                ResponseHandler::error('Failed to get seller connector.');
                return;
            }
            $reportsApi = $sellerConnector->reportsV20210630();

            $specifications = new CreateReportSpecification(
                reportType: self::REPORT_TYPE,
                marketplaceIds: [$credential->marketplace->marketplace_id],
                reportOptions: [
                    'aggregatedByTimePeriod' => self::AGGREGATED_BY_TIME_PERIOD,
                    'aggregateByLocation' => self::AGGREGATE_BY_LOCATION,
                ],
                dataStartTime: Carbon::now()->subDays(60),
                dataEndTime: Carbon::yesterday()
            );

            $reportId = $this->createReport($reportsApi, $specifications);

            if (!$reportId) {
                ResponseHandler::error('Failed to create report. Report id is null.');
                return;
            }

            ResponseHandler::success('Inventory report created successfully.');

            $this->waitForReportCompletion($reportsApi, $reportId);
            $this->fetchReportData($reportsApi, $reportId, $credential);
        } catch (Exception $e) {
            ResponseHandler::error('failed to fetch inventory: ', [
                'messages' => $e->getMessage(),
                'code' => $e->getCode(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
            ]);
            if (str_contains($e->getMessage(), '429')) {
                $this->fetchInventory($credential);
            }
        }
    }

    /**
     * Create the report.
     *
     * @param ReportsV20210630\Api $reportsApi
     * @param CreateReportSpecification $specifications
     * @return string|null
     */
    private function createReport(ReportsV20210630\Api $reportsApi, CreateReportSpecification $specifications): ?string
    {
        try {
            $report = $reportsApi->createReport($specifications)->body();
            $reportJson = json_decode($report, true);
            return $reportJson['reportId'] ?? null;
        } catch (Exception $e) {
            ResponseHandler::error('failed to create report: ', [
                'code' => $e->getCode(),
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
            ]);
            return null;
        }
    }

    /**
     * Save the report.
     *
     * @param array $report
     * @return array
     */
    protected function saveReportData(array $formattedReport): void
    {
        try {
            $formattedReport['date'] = Carbon::parse($formattedReport['date'])->format('Y-m-d');

            SellerInventoryItem::updateOrCreate(
                [
                    'customer_unique_id' => $formattedReport['customer_unique_id'],
                    'marketplace_id' => $formattedReport['marketplace_id'],
                    'date' => $formattedReport['date'],
                    'fnsku' => $formattedReport['fnsku'],
                    'asin' => $formattedReport['asin'],
                    'msku' => $formattedReport['msku'],
                ],
                $formattedReport
            );
        } catch (Exception $e) {
            ResponseHandler::error('failed to save report: ', [
                'code' => $e->getCode(),
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
            ]);
        }
    }

    /**
     * Wait for the report to be completed.
     *
     * @param ReportsV20210630\Api $reportsApi
     * @param string $reportId
     * @return void
     */
    private function waitForReportCompletion(ReportsV20210630\Api $reportsApi, string $reportId): void
    {
        $attempt = 0;
        $waitTime = 10;

        while ($attempt < 5) {
            try {
                $result = $reportsApi->getReport($reportId)->body();
                $resultJSON = json_decode($result, true);
                $status = $resultJSON['processingStatus'] ?? 'UNKNOWN';

                if ($status === 'DONE') {
                    ResponseHandler::success('Report completed: ' . $reportId);
                    return;
                }

                if ($status === 'CANCELLED') {
                    ResponseHandler::info('Report canceled: ' . $reportId);
                    return;
                }

                sleep($waitTime);
                $attempt++;
                $waitTime *= 2;
            } catch (\Exception $e) {
                ResponseHandler::error('Failed to get report: ' . $reportId, [
                    'messages' => $e->getMessage(),
                    'code' => $e->getCode(),
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                    'trace' => $e->getTraceAsString(),
                ]);
            }
        }
    }

    /**
     * Download the report data.
     *
     * @param ReportsV20210630\Api $reportsApi
     * @param string $reportId
     * @param CustomerCredential $credential
     *
     * @return void
     */
    private function fetchReportData(ReportsV20210630\Api $reportsApi, string $reportId, CustomerCredential $credential): void
    {
        try {
            $result = $reportsApi->getReport($reportId)->body();
            $resultJSON = json_decode($result, true);
            $documentId = $resultJSON['reportDocumentId'] ?? null;

            if (!$documentId) {
                Response::error('Failed to get report document id.');
                return;
            }

            $reportData = $reportsApi->getReportDocument($documentId, 'GET_LEDGER_SUMMARY_VIEW_DATA')->body();
            $reportDataJSON = json_decode($reportData, true);
            $reportZip = file_get_contents($reportDataJSON['url'] ?? '');

            if (!$reportZip) {
                ResponseHandler::error('Failed to get report data zip file.');
                return;
            }

            $reportCsv = gzdecode($reportZip);
            $this->saveCsvReport($reportCsv, $credential);
        } catch (\Exception $e) {
            ResponseHandler::error('Failed to fetch Report Data: ' . $reportId, [
                'messages' => $e->getMessage(),
                'code' => $e->getCode(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
            ]);
        }
    }
}
