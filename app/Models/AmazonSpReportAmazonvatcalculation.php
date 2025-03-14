<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

class AmazonSpReportAmazonvatcalculation extends Model
{
    use HasFactory;

    protected $table = 'amazon_sp_report_amazonvatcalculation';
    protected $primaryKey = 'id'; // Chiave primaria della tabella
    public $incrementing = true; // Auto-incremento abilitato
    protected $keyType = 'int'; // Tipo intero per la chiave primaria

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'requesttime',
        'order_id',
        'asin',
        'buyer_e_invoice_account_id',
        'buyer_tax_registration',
        'buyer_tax_registration_jurisdiction',
        'buyer_tax_registration_type',
        'converted_tax_amount',
        'currency',
        'einvoice_url',
        'export_outside_eu',
        'giftwrap_tax_amount',
        'giftwrap_tax_amount_promo',
        'giftwrap_tax_exclusive_promo_amount',
        'giftwrap_tax_exclusive_selling_price',
        'giftwrap_tax_inclusive_promo_amount',
        'giftwrap_tax_inclusive_selling_price',
        'invoice_correction_details',
        'invoice_level_currency_code',
        'invoice_level_exchange_rate',
        'invoice_level_exchange_rate_date',
        'invoice_url',
        'is_amazon_invoiced',
        'is_invoice_corrected',
        'jurisdiction_level',
        'jurisdiction_name',
        'marketplace_id',
        'merchant_id',
        'order_date',
        'original_vat_invoice_number',
        'our_price_tax_amount',
        'our_price_tax_amount_promo',
        'our_price_tax_exclusive_promo_amount',
        'our_price_tax_exclusive_selling_price',
        'our_price_tax_inclusive_promo_amount',
        'our_price_tax_inclusive_selling_price',
        'product_tax_code',
        'quantity',
        'return_fc_country',
        'sdi_invoice_delivery_status',
        'sdi_invoice_error_code',
        'sdi_invoice_error_description',
        'sdi_invoice_status_last_updated_date',
        'seller_tax_registration',
        'seller_tax_registration_jurisdiction',
        'ship_from_city',
        'ship_from_country',
        'ship_from_postal_code',
        'ship_from_state',
        'ship_from_tax_location_code',
        'ship_to_city',
        'ship_to_country',
        'ship_to_location_code',
        'ship_to_postal_code',
        'ship_to_state',
        'shipment_date',
        'shipment_id',
        'shipping_tax_amount',
        'shipping_tax_amount_promo',
        'shipping_tax_exclusive_promo_amount',
        'shipping_tax_exclusive_selling_price',
        'shipping_tax_inclusive_promo_amount',
        'shipping_tax_inclusive_selling_price',
        'sku',
        'tax_address_role',
        'tax_calculation_date',
        'tax_calculation_reason_code',
        'tax_collection_responsibility',
        'tax_rate',
        'tax_reporting_scheme',
        'tax_type',
        'transaction_id',
        'transaction_type',
        'vat_invoice_number',
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
        $mappedData = [
            'requesttime' => isset($data['requesttime']) ? Carbon::parse($data['requesttime']) : null,
            'order_id' => $data['order_id'] ?? null,
            'asin' => $data['asin'] ?? null,
            'buyer_e_invoice_account_id' => $data['buyer_e_invoice_account_id'] ?? null,
            'buyer_tax_registration' => $data['buyer_tax_registration'] ?? null,
            'buyer_tax_registration_jurisdiction' => $data['buyer_tax_registration_jurisdiction'] ?? null,
            'buyer_tax_registration_type' => $data['buyer_tax_registration_type'] ?? null,
            'converted_tax_amount' => (float) $data['converted_tax_amount'],
            'currency' => $data['currency'] ?? null,
            'einvoice_url' => $data['einvoice_url'] ?? null,
            'export_outside_eu' => isset($data['export_outside_eu']) ? ($data['export_outside_eu'] ? '1' : '0') : null,
            'giftwrap_tax_amount' => (float) $data['giftwrap_tax_amount'],
            'giftwrap_tax_amount_promo' => (float) $data['giftwrap_tax_amount_promo'],
            'giftwrap_tax_exclusive_promo_amount' => (float) $data['giftwrap_tax_exclusive_promo_amount'],
            'giftwrap_tax_exclusive_selling_price' => (float) $data['giftwrap_tax_exclusive_selling_price'],
            'giftwrap_tax_inclusive_promo_amount' => (float) $data['giftwrap_tax_inclusive_promo_amount'],
            'giftwrap_tax_inclusive_selling_price' => (float) $data['giftwrap_tax_inclusive_selling_price'],
            'invoice_correction_details' => $data['invoice_correction_details'] ?? null,
            'invoice_level_currency_code' => $data['invoice_level_currency_code'] ?? null,
            'invoice_level_exchange_rate' => (float) $data['invoice_level_exchange_rate'],
            'invoice_level_exchange_rate_date' => isset($data['invoice_level_exchange_rate_date']) ? Carbon::parse($data['invoice_level_exchange_rate_date']) : null,
            'invoice_url' => $data['invoice_url'] ?? null,
            'is_amazon_invoiced' => isset($data['is_amazon_invoiced']) ? ($data['is_amazon_invoiced'] ? '1' : '0') : null,
            'is_invoice_corrected' => isset($data['is_invoice_corrected']) ? ($data['is_invoice_corrected'] ? '1' : '0') : null,
            'jurisdiction_level' => $data['jurisdiction_level'] ?? null,
            'jurisdiction_name' => $data['jurisdiction_name'] ?? null,
            'marketplace_id' => $data['marketplace_id'] ?? null,
            'merchant_id' => $data['merchant_id'] ?? null,
            'order_date' => isset($data['order_date']) ? Carbon::parse($data['order_date'])->toDateString() : null,
            'original_vat_invoice_number' => $data['original_vat_invoice_number'] ?? null,
            'our_price_tax_amount' => (float) $data['our_price_tax_amount'],
            'our_price_tax_exclusive_selling_price' => (float) $data['our_price_tax_exclusive_selling_price'],
            'product_tax_code' => $data['product_tax_code'] ?? null,
            'quantity' => (int) $data['quantity'],
            'seller_tax_registration' => $data['seller_tax_registration'] ?? null,
            'seller_tax_registration_jurisdiction' => $data['seller_tax_registration_jurisdiction'] ?? null,
            'ship_from_city' => $data['ship_from_city'] ?? null,
            'ship_from_country' => $data['ship_from_country'] ?? null,
            'ship_from_postal_code' => $data['ship_from_postal_code'] ?? null,
            'ship_from_state' => $data['ship_from_state'] ?? null,
            'ship_to_city' => $data['ship_to_city'] ?? null,
            'ship_to_country' => $data['ship_to_country'] ?? null,
            'ship_to_postal_code' => $data['ship_to_postal_code'] ?? null,
            'ship_to_state' => $data['ship_to_state'] ?? null,
            'shipment_date' => isset($data['shipment_date']) ? Carbon::parse($data['shipment_date'])->toDateString() : null,
            'shipment_id' => $data['shipment_id'] ?? null,
            'sku' => $data['sku'] ?? null,
            'tax_address_role' => $data['tax_address_role'] ?? null,
            'tax_rate' => (float) $data['tax_rate'],
            'tax_type' => $data['tax_type'] ?? null,
            'transaction_id' => $data['transaction_id'] ?? null,
            'transaction_type' => $data['transaction_type'] ?? null,
            'vat_invoice_number' => $data['vat_invoice_number'] ?? null,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ];

        // Inserisce o aggiorna il record
        self::updateOrCreate(
            [
                "transaction_id" => $mappedData["transaction_id"],
                "order_id" => $mappedData["order_id"],
                "asin" => $mappedData["asin"],
            ],
            $mappedData
        );
    }
}
