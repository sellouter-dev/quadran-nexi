<?php

namespace App\Models;

use Illuminate\Support\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class AmazonSpReportFlatfilev2settlement extends Model
{
    use HasFactory;

    protected $table = 'amazon_sp_report_flatfilev2settlement';

    protected $primaryKey = 'id'; // Imposta 'id' come chiave primaria
    public $incrementing = true; // Abilita l'auto-incremento
    protected $keyType = 'int'; // Specifica che 'id' è di tipo intero

    protected $fillable = [
        'requesttime',
        'reportid',
        'reportcreatedtime',
        'order_id',
        'posted_date',
        'settlement_id',
        'settlement_start_date',
        'settlement_end_date',
        'deposit_date',
        'total_amount',
        'currency',
        'sku',
        'adjustment_id',
        'amount',
        'amount_description',
        'amount_type',
        'fulfillment_id',
        'marketplace_name',
        'merchant_adjustment_item_id', // Nome aggiornato
        'merchant_order_id',
        'merchant_order_item_id', // Nome aggiornato
        'order_item_code',
        'posted_date_time',
        'promotion_id',
        'quantity_purchased',
        'shipment_id',
        'transaction_type',
        'created_at',
        'updated_at',
    ];

    /**
     * Aggiorna o crea i dati di una singola collezione nel database.
     *
     * @param array $data
     * @return void
     */
    public static function saveData(array $data)
    {
        // Mappa dei campi ricevuti dal software con quelli nel database
        $mappedData = [
            'requesttime'                 => isset($data['requesttime']) ? Carbon::parse($data['requesttime']) : null,
            'reportid'                    => $data['reportid'] ?? null,
            'reportcreatedtime'           => isset($data['reportcreatedtime']) ? Carbon::parse($data['reportcreatedtime']) : null,
            'order_id'                    => $data['order_id'] ?? null,
            'posted_date'                 => isset($data['posted_date']) ? Carbon::parse($data['posted_date'])->toDateString() : null,
            'settlement_id'               => $data['settlement_id'] ?? null,
            'settlement_start_date'       => isset($data['settlement_start_date']) ? Carbon::parse($data['settlement_start_date']) : null,
            'settlement_end_date'         => isset($data['settlement_end_date']) ? Carbon::parse($data['settlement_end_date']) : null,
            'deposit_date'                => isset($data['deposit_date']) ? Carbon::parse($data['deposit_date']) : null,
            'total_amount'                => is_numeric($data['total_amount']) ? (float) $data['total_amount'] : 0.0,
            'currency'                    => $data['currency'] ?? null,
            'sku'                         => $data['sku'] ?? null,
            'adjustment_id'               => $data['adjustment_id'] ?? null,
            'amount'                      => is_numeric($data['amount']) ? (float) $data['amount'] : 0.0,
            'amount_description'           => $data['amount_description'] ?? null,
            'amount_type'                 => $data['amount_type'] ?? null,
            'fulfillment_id'              => $data['fulfillment_id'] ?? null,
            'marketplace_name'            => $data['marketplace_name'] ?? null,
            'merchant_adjustment_item_id' => $data['merchant_adjustment_item_id'] ?? null,
            'merchant_order_id'           => $data['merchant_order_id'] ?? null,
            'merchant_order_item_id'      => $data['merchant_order_item_id'] ?? null,
            'order_item_code'             => $data['order_item_code'] ?? null,
            'posted_date_time'            => isset($data['posted_date_time']) ? Carbon::parse($data['posted_date_time']) : null,
            'promotion_id'                => $data['promotion_id'] ?? null,
            'quantity_purchased'          => isset($data['quantity_purchased']) ? (int) $data['quantity_purchased'] : null,
            'shipment_id'                 => $data['shipment_id'] ?? null,
            'transaction_type'            => $data['transaction_type'] ?? null,
            'created_at'                  => Carbon::now(),
            'updated_at'                  => Carbon::now(),
        ];


        self::updateOrCreate(
            [
                "order_id" => $mappedData["order_id"],
                "amount_description" => $mappedData["amount_description"],
                "amount" => $mappedData["amount"],
            ],
            $mappedData
        );
    }
}
