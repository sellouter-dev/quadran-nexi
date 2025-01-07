<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use App\Services\CSVGeneratorService;

class DownloadCSVController extends Controller
{
    protected $csvGeneratorService;

    public function __construct()
    {
        $this->csvGeneratorService = new CSVGeneratorService();
    }

    // public function downloadDataOfVatTransaction(Request $request){
    //     if (!Auth::check()) {
    //         return response()->json(['message' => 'Unauthorized'], 401);
    //     }
    //     try {
    //         $query = AmazonSpReportAmazonvattransactions::query();

    //         if ($request->has('start_date') && $request->has('end_date')) {
    //             $startDate = $request->input('start_date');
    //             $endDate = $request->input('end_date');
    //             $query->whereBetween('transaction_complete_date', [$startDate, $endDate]);
    //         } else if ($request->has('start_date')) {
    //             $startDate = $request->input('start_date');
    //             $query->where('transaction_complete_date', '>=', $startDate);
    //         } else if ($request->has('end_date')) {
    //             $endDate = $request->input('end_date');
    //             $query->where('transaction_complete_date', '<=', $endDate);
    //         }

    //         $data = [];
    //         $query->chunk(1000, function($rows) use (&$data) {
    //             foreach ($rows as $row) {
    //                 $data[] = $row->toArray();
    //             }
    //         });

    //         $filePath = storage_path('app/temp/AmazonSpReportAmazonvattransactions.csv');
    //         $result =  $this->csvGeneratorService->streamCSV($data, $filePath);

    //         return $result;
    //     } catch (\Exception $e) {
    //         return response()->json([
    //             'error' => $e->getMessage(),
    //             'code' => $e->getCode(),
    //             'file' => $e->getFile(),
    //             'line' => $e->getLine(),
    //             'trace' => $e->getTraceAsString(),
    //         ], 500);
    //     }
    // }

    // public function downloadDataOfVatCalculation(Request $request) {
    //     if (!Auth::check()) {
    //         return response()->json(['message' => 'Unauthorized'], 401);
    //     }
    //     try {
    //         $query = AmazonSpReportAmazonvatcalculation::query();

    //         if ($request->has('start_date') && $request->has('end_date')) {
    //             $startDate = $request->input('start_date');
    //             $endDate = $request->input('end_date');
    //             $query->whereBetween('shipment_date', [$startDate, $endDate]);
    //         } else if ($request->has('start_date')) {
    //             $startDate = $request->input('start_date');
    //             $query->where('shipment_date', '>=', $startDate);
    //         } else if ($request->has('end_date')) {
    //             $endDate = $request->input('end_date');
    //             $query->where('shipment_date', '<=', $endDate);
    //         }

    //         $data = [];
    //         $query->chunk(1000, function($rows) use (&$data) {
    //             foreach ($rows as $row) {
    //                 $data[] = $row->toArray();
    //             }
    //         });

    //         $filePath = storage_path('app/temp/AmazonSpReportAmazonvatcalculation.csv');
    //         $result = $this->csvGeneratorService->streamCSV($data, $filePath);

    //         return $result;
    //     } catch (\Exception $e) {
    //         return response()->json([
    //             'error' => $e->getMessage(),
    //             'code' => $e->getCode(),
    //             'file' => $e->getFile(),
    //             'line' => $e->getLine(),
    //             'trace' => $e->getTraceAsString(),
    //         ], 500);
    //     }
    // }

    public function downloadDataCalculationComputed()
    {
        if (!Auth::check()) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }
        $this->csvGeneratorService->downloadDataCalculationComputed();
    }

    public function downloadDataOfFlatfilevatinvoicedata()
    {
        if (!Auth::check()) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }
        $this->csvGeneratorService->downloadDataOfFlatfilevatinvoicedata();
    }

    public function downloadDataOfCollections()
    {
        if (!Auth::check()) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }
        $this->csvGeneratorService->downloadDataOfCollections();
    }
}
