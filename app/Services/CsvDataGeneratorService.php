<?php

use App\Models\InvoiceTrack;
use App\Models\DataCollection;
use App\Models\FlatfileVatInvoiceData;
use App\Services\FileEncryptionService;

class CSVGeneratorService
{
    protected $fileEncryptionService;

    public function __construct()
    {
        $this->fileEncryptionService = new FileEncryptionService();
    }

    public function generateInvoiceCSV()
    {
        $data = InvoiceTrack::all()->toArray();
        return $this->streamCSV($data, storage_path('app/temp/InvoiceTrack.csv'));
    }

    public function generateFlatfileVatCSV()
    {
        $data = FlatfileVatInvoiceData::all()->toArray();
        return $this->streamCSV($data, storage_path('app/temp/Flatfilevatinvoicedata.csv'));
    }

    public function generateCollectionCSV()
    {
        $data = DataCollection::all()->toArray();
        return $this->streamCSV($data, storage_path('app/temp/FlatFileSettlement.csv'));
    }

    private function streamCSV($data, $filePath)
    {
        try {
            $handle = fopen($filePath, 'w+');
            if (!empty($data)) {
                fputcsv($handle, array_keys($data[0]));
                foreach ($data as $row) {
                    fputcsv($handle, $row);
                }
            }
            fclose($handle);
            return $this->fileEncryptionService->saveFile($filePath);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage(), 'code' => $e->getCode()], 500);
        } finally {
            if (file_exists($filePath)) {
                unlink($filePath);
            }
        }
    }
}
