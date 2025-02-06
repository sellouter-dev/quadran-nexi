<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\SellerInventoryItem;
use Firebase\JWT\JWT;
use Firebase\JWT\JWK;
use GuzzleHttp\Client;

/**
 * @OA\Info(
 *     title="Quadran - Nexi API",
 *     version="1.0.0",
 *     description="This is the API documentation for the Quadran - Nexi API.",
 * )
 * @OA\Tag(
 *     name="Auth",
 *     description="Endpoints for authentication."
 * )
 * @OA\Tag(
 *     name="Inventory",
 *     description="Endpoints for managing and retrieving inventory items."
 * ),
 *
 */
class SellerInventoryItemsController extends Controller
{
    private $requiredScope = 'Tech_SapSellouter';

    private function getPublicKeys()
    {
        $jwksUrl = env('JWKS_URL');
        $client = new Client();
        $response = $client->get($jwksUrl);
        $jwks = json_decode($response->getBody(), true);

        return JWK::parseKeySet($jwks);
    }

    private function validateToken($token)
    {
        try {
            $publicKeys = $this->getPublicKeys();
            $decoded = JWT::decode($token, $publicKeys);

            if ($decoded->exp < time()) {
                return response()->json(['error' => 'ERROR 9020: JWT expired'], 401);
            }

            if (!isset($decoded->scope) || !in_array($this->requiredScope, explode(' ', $decoded->scope))) {
                return response()->json(['error' => 'ERROR 9030: Scope check failed'], 403);
            }

            return $decoded;
        } catch (\Exception $e) {
            return response()->json(['error' => 'ERROR 9010: Signature validation failed', 'message' => $e->getMessage()], 401);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/seller-inventory-items",
     *     summary="Get Seller Inventory Items",
     *     description="Retrieve a list of inventory items with optional filters.",
     *     tags={"Inventory"},
     *     security={{"Bearer":{}}},
     *     @OA\Parameter(
     *         name="date_from",
     *         in="query",
     *         description="Start date for filtering inventory items. Format: 2024-01-01",
     *         required=false,
     *         @OA\Schema(
     *             type="string",
     *             format="date",
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="date_to",
     *         in="query",
     *         description="End date for filtering inventory items. Format: 2024-01-01",
     *         required=false,
     *         @OA\Schema(
     *             type="string",
     *             format="date"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="fnsku",
     *         in="query",
     *         description="Filter by FNSKU",
     *         required=false,
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="asin",
     *         in="query",
     *         description="Filter by ASIN",
     *         required=false,
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="msku",
     *         in="query",
     *         description="Filter by MSKU",
     *         required=false,
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="page",
     *         in="query",
     *         description="Page number for pagination",
     *         required=false,
     *         @OA\Schema(
     *             type="integer",
     *             example=1
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="A list of inventory items",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="data", type="array",
     *                 @OA\Items(
     *                     @OA\Property(property="date", type="string", format="date"),
     *                     @OA\Property(property="fnsku", type="string"),
     *                     @OA\Property(property="asin", type="string"),
     *                     @OA\Property(property="msku", type="string"),
     *                     @OA\Property(property="title", type="string"),
     *                     @OA\Property(property="disposition", type="string"),
     *                     @OA\Property(property="starting_warehouse_balance", type="integer"),
     *                     @OA\Property(property="in_transit_between_warehouses", type="integer"),
     *                     @OA\Property(property="receipts", type="integer"),
     *                     @OA\Property(property="customer_shipments", type="integer"),
     *                     @OA\Property(property="customer_returns", type="integer"),
     *                     @OA\Property(property="vendor_returns", type="integer"),
     *                     @OA\Property(property="warehouse_transfer_in_out", type="integer"),
     *                     @OA\Property(property="found", type="integer"),
     *                     @OA\Property(property="lost", type="integer"),
     *                     @OA\Property(property="damaged", type="integer"),
     *                     @OA\Property(property="disposed", type="integer"),
     *                     @OA\Property(property="other_events", type="integer"),
     *                     @OA\Property(property="ending_warehouse_balance", type="integer"),
     *                     @OA\Property(property="unknown_events", type="integer"),
     *                     @OA\Property(property="location", type="string")
     *                 )
     *             ),
     *             @OA\Property(property="meta", type="object",
     *                 @OA\Property(property="total", type="integer", example=150),
     *                 @OA\Property(property="actual_count", type="integer", example=15),
     *                 @OA\Property(property="limit", type="integer", example=100),
     *                 @OA\Property(property="page", type="integer", example=1),
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Unauthorized")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Server error",
     *         @OA\JsonContent(
     *             @OA\Property(property="error", type="string"),
     *             @OA\Property(property="code", type="integer"),
     *             @OA\Property(property="file", type="string"),
     *             @OA\Property(property="line", type="integer"),
     *             @OA\Property(property="trace", type="string")
     *         )
     *     )
     * )
     */
    public function getInventory(Request $request)
    {
        $authHeader = $request->header('Authorization');
        if (!$authHeader || !preg_match('/Bearer\s(.*)/', $authHeader, $matches)) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $token = $matches[1];
        $validationResult = $this->validateToken($token);
        if ($validationResult instanceof \Illuminate\Http\JsonResponse) {
            return $validationResult;
        }

        $columns = [
            'date',
            'fnsku',
            'asin',
            'msku',
            'title',
            'disposition',
            'starting_warehouse_balance',
            'in_transit_between_warehouses',
            'receipts',
            'customer_shipments',
            'customer_returns',
            'vendor_returns',
            'warehouse_transfer_in_out',
            'found',
            'lost',
            'damaged',
            'disposed',
            'other_events',
            'ending_warehouse_balance',
            'unknown_events',
            'location',
        ];

        try {
            $query = SellerInventoryItem::select($columns);

            if ($request->has('date_from')) {
                $query->where('date', '>=', $request->date_from);
            }

            if ($request->has('date_to')) {
                $query->where('date', '<=', $request->date_to);
            }

            if ($request->has('fnsku')) {
                $query->where('fnsku', '=', $request->fnsku);
            }

            if ($request->has('asin')) {
                $query->where('asin', '=', $request->asin);
            }

            if ($request->has('msku')) {
                $query->where('msku', '=', $request->msku);
            }

            // Definisci il limite fisso di 100 elementi
            $limit = 100;

            $page = $request->get('page', 1);
            $totalItems = (clone $query)->count();
            $offset = ($page - 1) * $limit;
            $inventoryItems = $query->offset($offset)->limit($limit)->get();

            // Prepariamo la risposta con i metadati di paginazione

            $response = [
                'data' => $inventoryItems,
                'meta' => [
                    'total' => $totalItems,
                    'actual_count' => count($inventoryItems),
                    'limit' => $limit,
                    'page' => $page,
                ]
            ];

            return response()->json($response, 200);
        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage(),
                'code' => $e->getCode(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
            ], 500);
        }
    }
}
