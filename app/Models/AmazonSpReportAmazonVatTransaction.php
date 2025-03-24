<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * Modello Eloquent per la tabella amazon_sp_report_amazonvattransactions
 *
 * Questo modello rappresenta la tabella `amazon_sp_report_amazonvattransactions`
 * e viene utilizzato per interagire con il database tramite Laravel ORM.
 */
class AmazonSpReportAmazonVatTransaction extends Model
{
    use HasFactory;

    /**
     * Nome della tabella associata a questo modello.
     *
     * @var string
     */
    protected $table = 'amazon_sp_report_amazonvattransactions';

    /**
     * Chiave primaria della tabella.
     *
     * @var string
     */
    protected $primaryKey = 'id'; // Se la tabella ha un ID come chiave primaria

    /**
     * Specifica se la chiave primaria è auto-incrementante.
     *
     * @var bool
     */
    public $incrementing = false; // Cambia a true se il campo ID è auto-incrementante

    /**
     * Tipo della chiave primaria.
     *
     * @var string
     */
    protected $keyType = 'string'; // Usa 'int' se la chiave primaria è un numero intero

    /**
     * Disattiva i timestamp automatici di Laravel (created_at e updated_at).
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * Campi che possono essere riempiti in massa (Mass Assignment).
     *
     * @var array
     */
    protected $fillable = [
        'activity_period',
        'activity_transaction_id',
        'arrival_address',
        'arrival_city',
        'arrival_country',
        'arrival_post_code',
        'asin',
        'buyer_name',
        'buyer_vat_number', // buyer_tax_registration
        'buyer_vat_number_country',
        'commodity_code',
        'commodity_code_supplementary_unit',
        'cost_price_of_items',
        'delivery_conditions',
        'departure_country',
        'departure_post_code',
        'depature_city',
        'export_outside_eu',
        'gift_wrap_amt_vat_excl',
        'gift_wrap_amt_vat_incl',
        'gift_wrap_vat_amt',
        'gift_wrap_vat_rate_percent',
        'invoice_url',
        'item_description',
        'item_manufacture_country',
        'item_qty_supplementary_unit',
        'item_weight',
        'marketplace',
        'price_of_items_amt_vat_excl',
        'price_of_items_amt_vat_incl',
        'price_of_items_vat_amt',
        'price_of_items_vat_rate_percent',
        'product_tax_code',
        'program_type',
        'promo_gift_wrap_amt_vat_excl',
        'promo_gift_wrap_amt_vat_incl',
        'promo_gift_wrap_vat_amt',
        'promo_price_of_items_amt_vat_excl',
        'promo_price_of_items_amt_vat_incl',
        'promo_price_of_items_vat_amt',
        'promo_ship_charge_amt_vat_excl',
        'promo_ship_charge_amt_vat_incl',
        'promo_ship_charge_vat_amt',
        'qty',
        'requesttime',
        'sale_arrival_country',
        'sale_depart_country',
        'sales_channel',
        'seller_arrival_country_vat_number',
        'seller_arrival_vat_number_country',
        'seller_depart_country_vat_number',
        'seller_depart_vat_number_country',
        'seller_sku',
        'ship_charge_amt_vat_excl',
        'ship_charge_amt_vat_incl',
        'ship_charge_vat_amt',
        'ship_charge_vat_rate_percent',
        'statistical_code_arrival',
        'statistical_code_depart',
        'supplier_name',
        'supplier_vat_number',
        'tax_calculation_date',
        'tax_collection_responsibility',
        'tax_reporting_scheme',
        'taxable_jurisdiction',
        'taxable_jurisdiction_level',
        'total_activity_supplementary_unit',
        'total_activity_value_amt_vat_excl',
        'total_activity_value_amt_vat_incl',
        'total_activity_value_vat_amt',
        'total_activity_weight',
        'total_gift_wrap_amt_vat_excl',
        'total_gift_wrap_amt_vat_incl',
        'total_gift_wrap_vat_amt',
        'total_price_of_items_amt_vat_excl',
        'total_price_of_items_amt_vat_incl',
        'total_price_of_items_vat_amt',
        'total_ship_charge_amt_vat_excl',
        'total_ship_charge_amt_vat_incl',
        'total_ship_charge_vat_amt',
        'transaction_arrival_date',
        'transaction_complete_date', // shipment_date
        'transaction_currency_code',
        'transaction_depart_date',
        'transaction_event_id',
        'transaction_seller_vat_number',
        'transaction_seller_vat_number_country',
        'transaction_type',
        'transportation_mode',
        'unique_account_identifier',
        'vat_calculation_imputation_country',
        'vat_inv_converted_amt',
        'vat_inv_currency_code',
        'vat_inv_exchange_rate',
        'vat_inv_exchange_rate_date',
        'vat_inv_number' // vat_invoice_number
    ];

    public static function saveData(array $data)
    {
        $mappedData = [
            // fill in the fields here
            "activity_period" => $data["activity_period"] ?? null,
            "activity_transaction_id" => $data["activity_transaction_id"] ?? null,
            "arrival_address" => $data["arrival_address"] ?? null,
            "arrival_city" => $data["arrival_city"] ?? null,
            "arrival_country" => $data["arrival_country"] ?? null,
            "arrival_post_code" => $data["arrival_post_code"] ?? null,
            "asin" => $data["asin"] ?? null,
            "buyer_name" => $data["buyer_name"] ?? null,
            "buyer_vat_number" => $data["buyer_vat_number"] ?? null,
            "buyer_vat_number_country" => $data["buyer_vat_number_country"] ?? null,
            "commodity_code" => $data["commodity_code"] ?? null,
            "commodity_code_supplementary_unit" => $data["commodity_code_supplementary_unit"] ?? null,
            "cost_price_of_items" => (float) ($data["cost_price_of_items"] ?? 0),
            "delivery_conditions" => $data["delivery_conditions"] ?? null,
            "departure_country" => $data["departure_country"] ?? null,
            "departure_post_code" => $data["departure_post_code"] ?? null,
            "depature_city" => $data["depature_city"] ?? null,
            "export_outside_eu" => isset($data["export_outside_eu"]) ? ($data["export_outside_eu"] ? "1" : "0") : null,
            "gift_wrap_amt_vat_excl" => (float) ($data["gift_wrap_amt_vat_excl"] ?? 0),
            "gift_wrap_amt_vat_incl" => (float) ($data["gift_wrap_amt_vat_incl"] ?? 0),
            "gift_wrap_vat_amt" => (float) ($data["gift_wrap_vat_amt"] ?? 0),
            "gift_wrap_vat_rate_percent" => (float) ($data["gift_wrap_vat_rate_percent"] ?? 0),
            "invoice_url" => $data["invoice_url"] ?? null,
            "item_description" => $data["item_description"] ?? null,
            "item_manufacture_country" => $data["item_manufacture_country"] ?? null,
            "item_qty_supplementary_unit" => (int) ($data["item_qty_supplementary_unit"] ?? 0),
            "item_weight" => (float) ($data["item_weight"] ?? 0),
            "marketplace" => $data["marketplace"] ?? null,
            "price_of_items_amt_vat_excl" => (float) ($data["price_of_items_amt_vat_excl"] ?? 0),
            "price_of_items_amt_vat_incl" => (float) ($data["price_of_items_amt_vat_incl"] ?? 0),
            "price_of_items_vat_amt" => (float) ($data["price_of_items_vat_amt"] ?? 0),
            "price_of_items_vat_rate_percent" => (float) ($data["price_of_items_vat_rate_percent"] ?? 0),
            "product_tax_code" => $data["product_tax_code"] ?? null,
            "program_type" => $data["program_type"] ?? null,
            "qty" => (int) ($data["qty"] ?? 0),
            "requesttime" => isset($data["requesttime"]) ? Carbon::parse($data["requesttime"]) : null,
            "sale_arrival_country" => $data["sale_arrival_country"] ?? null,
            "sale_depart_country" => $data["sale_depart_country"] ?? null,
            "sales_channel" => $data["sales_channel"] ?? null,
            "seller_sku" => $data["seller_sku"] ?? null,
            "ship_charge_amt_vat_excl" => (float) ($data["ship_charge_amt_vat_excl"] ?? 0),
            "ship_charge_amt_vat_incl" => (float) ($data["ship_charge_amt_vat_incl"] ?? 0),
            "ship_charge_vat_amt" => (float) ($data["ship_charge_vat_amt"] ?? 0),
            "ship_charge_vat_rate_percent" => (float) ($data["ship_charge_vat_rate_percent"] ?? 0),
            "tax_calculation_date" => isset($data["tax_calculation_date"]) ? Carbon::parse($data["tax_calculation_date"]) : null,
            "transaction_arrival_date" => isset($data["transaction_arrival_date"]) ? Carbon::parse($data["transaction_arrival_date"]) : null,
            "transaction_complete_date" => isset($data["transaction_complete_date"]) ? Carbon::parse($data["transaction_complete_date"]) : null,
            "transaction_currency_code" => $data["transaction_currency_code"] ?? null,
            "transaction_depart_date" => isset($data["transaction_depart_date"]) ? Carbon::parse($data["transaction_depart_date"]) : null,
            "transaction_event_id" => $data["transaction_event_id"] ?? null,
            "transaction_seller_vat_number" => $data["transaction_seller_vat_number"] ?? null,
            "transaction_seller_vat_number_country" => $data["transaction_seller_vat_number_country"] ?? null,
            "transaction_type" => $data["transaction_type"] ?? null,
            "transportation_mode" => $data["transportation_mode"] ?? null,
            "unique_account_identifier" => $data["unique_account_identifier"] ?? null,
            "vat_calculation_imputation_country" => $data["vat_calculation_imputation_country"] ?? null,
            "vat_inv_converted_amt" => (float) ($data["vat_inv_converted_amt"] ?? 0),
            "vat_inv_currency_code" => $data["vat_inv_currency_code"] ?? null,
            "vat_inv_exchange_rate" => (float) ($data["vat_inv_exchange_rate"] ?? 0),
            "vat_inv_exchange_rate_date" => isset($data["vat_inv_exchange_rate_date"]) ? Carbon::parse($data["vat_inv_exchange_rate_date"]) : null,
            "vat_inv_number" => $data["vat_inv_number"] ?? null,
        ];

        self::updateOrCreate(
            [
                "activity_transaction_id" => $mappedData["activity_transaction_id"],
                "buyer_vat_number" => $mappedData["buyer_vat_number"],
            ],
            $mappedData
        );
    }
}
