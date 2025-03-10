<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AmazonSpReportAmazonvatcalculation extends Model
{
    use HasFactory;

    protected $table = 'amazon_sp_report_amazonvatcalculation';

    protected $casts = [
        'order_id' => 'string',
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
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
    ];

    /**
     * Aggiorna o crea i dati di una singola collezione nel database.
     *
     * @param array $data
     * @return void
     */
    public static function saveData(array $data)
    {
        self::updateOrCreate(
            [
                'order_id' => $data['order_id']
            ],
            $data
        );
    }
}
