<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\SellerInventoryItem;
use Firebase\JWT\JWT;
use Firebase\JWT\JWK;
use GuzzleHttp\Client;
use App\Services\ResponseHandler;

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
 * )
 */
class SellerInventoryItemsController extends Controller
{
    /**
     * Scope richiesto per l'autorizzazione.
     *
     * @var string
     */
    private $requiredScope = 'Tech_SapSellouter';

    /**
     * Estrae l'header JWT dal token.
     *
     * @param string $jwt Il token
     * @return array L'header JWT
     * @throws \Exception In caso di errore nell'estrazione dell'header.
     */
    private function extractJwtHeader($jwt)
    {
        try {
            $parts = explode('.', $jwt);
            if (count($parts) < 2) {
                throw new \Exception('JWT non valido');
            }

            $header = json_decode(base64_decode(strtr($parts[0], '-_', '+/')), true);
            return $header;
        } catch (\Exception $e) {
            ResponseHandler::error("Errore durante l'estrazione dell'header JWT", [
                'errore' => $e->getMessage(),
                'file'   => $e->getFile(),
                'linea'  => $e->getLine()
            ], 'inventory');
            throw $e;
        }
    }

    /**
     * Recupera le chiavi pubbliche dal JWKS e restituisce solo quella associata al token.
     *
     * @param string $jwt Il token JWT da cui estrarre kid e alg.
     * @return array La chiave pubblica da usare per la validazione.
     * @throws \Exception In caso di errore nel recupero o parsing delle chiavi.
     */
    private function getPublicKeys($jwt)
    {
        try {
            // Estrai header
            $header = $this->extractJwtHeader($jwt);
            $kid = $header['kid'] ?? null;
            $alg = $header['alg'] ?? 'RS512';

            if (!$kid) {
                throw new \Exception('kid non presente nel JWT');
            }

            // Recupera JWKS
            $jwksUrl = config('services_external.jwks_url');
            $client = new Client(['verify' => false]);
            $response = $client->get($jwksUrl);
            $jwks = json_decode($response->getBody(), true);

            ResponseHandler::info("Recupero delle chiavi pubbliche dal JWKS", ['jwks' => $jwks], 'inventory');

            // Cerca la chiave con lo stesso kid
            foreach ($jwks['keys'] as &$key) {
                if (!isset($key['alg'])) {
                    $key['alg'] = $alg; // aggiunge dinamicamente l'alg
                }
            }

            // Crea solo il sottoinsieme di chiavi che servono
            $filteredJwks = [
                'keys' => array_values(array_filter($jwks['keys'], fn($k) => $k['kid'] === $kid))
            ];

            if (empty($filteredJwks['keys'])) {
                throw new \Exception("Nessuna chiave trovata per kid: $kid");
            }

            $publicKeys = JWK::parseKeySet($filteredJwks);

            ResponseHandler::success("Chiavi pubbliche filtrate e recuperate correttamente", [
                'kid' => $kid,
                'alg' => $alg,
                'keys_found' => count($filteredJwks['keys'])
            ], 'inventory');

            return $publicKeys;
        } catch (\Exception $e) {
            ResponseHandler::error("Errore nel recupero delle chiavi pubbliche dal JWKS", [
                'errore' => $e->getMessage(),
                'file'   => $e->getFile(),
                'linea'  => $e->getLine()
            ], 'inventory');
            throw $e;
        }
    }

    /**
     * Valida il token JWT utilizzando le chiavi pubbliche.
     *
     * @param string $token Il token JWT da validare.
     * @return mixed Il token decodificato se valido, altrimenti una risposta JSON di errore.
     */
    private function validateToken($token)
    {
        try {
            $publicKeys = $this->getPublicKeys($token);
            $decoded = JWT::decode($token, $publicKeys);
            ResponseHandler::info("Token JWT decodificato correttamente", ['token' => $token], 'inventory');
            if ($decoded->exp < time()) {
                ResponseHandler::error("JWT scaduto", ['exp' => $decoded->exp, 'adesso' => time()], 'inventory');
                return response()->json(['error' => 'ERROR 9020: JWT expired'], 401);
            }

            if (!isset($decoded->scope) || !in_array($this->requiredScope, explode(' ', $decoded->scope))) {
                ResponseHandler::error("Verifica dello scope fallita", [
                    'scope'    => $decoded->scope ?? null,
                    'richiesto' => $this->requiredScope
                ], 'inventory');
                return response()->json(['error' => 'ERROR 9030: Scope check failed'], 403);
            }

            ResponseHandler::success("Token JWT validato correttamente", ['token' => $token], 'inventory');
            return $decoded;
        } catch (\Exception $e) {
            ResponseHandler::error("Validazione della firma del JWT fallita", [
                'errore' => $e->getMessage(),
                'file'   => $e->getFile(),
                'linea'  => $e->getLine()
            ], 'inventory');
            return response()->json(['error' => 'ERROR 9010: Signature validation failed', 'message' => $e->getMessage()], 401);
        }
    }

    /**
     * Endpoint per ottenere gli inventory items.
     *
     * @OA\Get(
     *     path="/api/seller-inventory-items",
     *     summary="Get Seller Inventory Items",
     *     description="Retrieve a list of inventory items with optional filters.",
     *     tags={"Inventory"},
     *     security={{"Bearer":{}}},
     *     @OA\Parameter(
     *         name="date_from",
     *         in="query",
     *         description="Data inizio per filtrare gli inventory items. Formato: 2024-01-01",
     *         required=false,
     *         @OA\Schema(
     *             type="string",
     *             format="date"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="date_to",
     *         in="query",
     *         description="Data fine per filtrare gli inventory items. Formato: 2024-01-01",
     *         required=false,
     *         @OA\Schema(
     *             type="string",
     *             format="date"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="fnsku",
     *         in="query",
     *         description="Filtra per FNSKU",
     *         required=false,
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="asin",
     *         in="query",
     *         description="Filtra per ASIN",
     *         required=false,
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="msku",
     *         in="query",
     *         description="Filtra per MSKU",
     *         required=false,
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="page",
     *         in="query",
     *         description="Numero di pagina per la paginazione",
     *         required=false,
     *         @OA\Schema(
     *             type="integer",
     *             example=1
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Una lista di inventory items",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="report_date", type="array",
     *                 @OA\Items(
     *                     @OA\Property(property="report_date", type="string", format="date"),
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
     *                 @OA\Property(property="page", type="integer", example=1)
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
        ResponseHandler::info("Richiesta per il recupero degli inventory items ricevuta", ['parametri_query' => $request->query()], 'inventory');

        // $authHeader = $request->header('Authorization');
        // if (!$authHeader || !preg_match('/Bearer\s(.*)/', $authHeader, $matches)) {
        //     ResponseHandler::error("Header Authorization mancante o non valido", [], 'inventory');
        //     return response()->json(['message' => 'Non autorizzato'], 401);
        // }

        // $token = $matches[1];
        // $validationResult = $this->validateToken($token);
        // if ($validationResult instanceof \Illuminate\Http\JsonResponse) {
        //     return $validationResult;
        // }

        try {
            ResponseHandler::info("Avvio della query per il recupero degli inventory items (token valido)", ['token_valid' => true], 'inventory');

            $columns = [
                'report_date',
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

            $query = SellerInventoryItem::select($columns);

            if ($request->has('date_from')) {
                $query->where('report_date', '>=', $request->date_from);
            }

            if ($request->has('date_to')) {
                $query->where('report_date', '<=', $request->date_to);
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

            // $limit = 100;
            // $page = $request->get('page', 1);
            // $totalItems = (clone $query)->count();
            // $offset = ($page - 1) * $limit;
            // $inventoryItems = $query->offset($offset)->limit($limit)->get();
            $inventoryItems = $query->get();

            ResponseHandler::success("Query per il recupero degli inventory items eseguita con successo", [
                'item_ritornati' => count($inventoryItems),
            ], 'inventory');

            $response = [
                'data' => $inventoryItems,
                'meta' => [
                    'actual_count' => count($inventoryItems),
                ]
            ];

            return response()->json($response, 200);
        } catch (\Exception $e) {
            ResponseHandler::error("Errore durante il recupero degli inventory items", [
                'errore' => $e->getMessage(),
                'code'   => $e->getCode(),
                'file'   => $e->getFile(),
                'linea'  => $e->getLine(),
                'trace'  => $e->getTraceAsString()
            ], 'inventory');
            return response()->json([
                'error' => $e->getMessage(),
                'code'  => $e->getCode(),
                'file'  => $e->getFile(),
                'linea' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ], 500);
        }
    }
}