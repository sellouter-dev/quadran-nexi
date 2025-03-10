<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AmazonSpReportFlatfilevatinvoicedatavidr extends Model
{
    use HasFactory;

    protected $primaryKey = 'order_id';

    protected $table = 'amazon_sp_report_flatfilevatinvoicedatavidr';

    public $timestamps = false;

    protected $casts = [
        'order_id' => 'string',
    ];

    protected $fillable = [
        'requesttime',
        'marketplace',
        'asin',
        'sku',
        'order_id',
        'order_item_id',
        'shipment_date',
        'order_date',
        'bill_address_1',
        'bill_address_2',
        'bill_address_3',
        'bill_city',
        'bill_country',
        'bill_postal_code',
        'bill_state',
        'billing_name',
        'billing_phone_number',
        'buyer_company_name',
        'buyer_e_invoice_account_id',
        'buyer_name',
        'buyer_tax_registration_type',
        'buyer_vat_number', // buyer_tax_registration
        'citation_de',
        'citation_en',
        'citation_es',
        'citation_fr',
        'citation_it',
        'currency',
        'export_outside_eu',
        'fulfilled_by',
        'gift_promo_vat_amount',
        'gift_promo_vat_excl_amount', // giftwrap_tax_exclusive_promo_amount
        'gift_promo_vat_incl_amount', // giftwrap_tax_inclusive_promo_amount
        'gift_promo_vat_rate',
        'gift_promotion_id',
        'gift_wrap_vat_amount', // giftwrap_tax_amount
        'gift_wrap_vat_excl_amount',
        'gift_wrap_vat_incl_amount',
        'gift_wrap_vat_rate',
        'invoice_correction_details',
        'invoice_number',
        'invoice_status',
        'invoice_status_description',
        'is_amazon_invoiced',
        'is_business_order',
        'is_buyer_physically_present',
        'is_invoice_corrected',
        'is_seller_physically_present',
        'item_promo_vat_amount', // our_price_tax_amount_promo
        'item_promo_vat_excl_amount', // our_price_tax_exclusive_promo_amount
        'item_promo_vat_incl_amount', // our_price_tax_inclusive_promo_amount
        'item_promo_vat_rate',
        'item_promotion_id',
        'item_vat_amount', // our_price_tax_amount
        'item_vat_excl_amount', // our_price_tax_exclusive_selling_price
        'item_vat_incl_amount', // our_price_tax_inclusive_selling_price
        'item_vat_rate', // tax_rate
        'legacy_customer_order_item_id',
        'original_vat_invoice_number', // vat_invoice_number
        'price_designation',
        'product_name',
        'purchase_order_number',
        'quantity_purchased',
        'recipient_name',
        'recommended_invoice_format',
        'seller_vat_number',
        'ship_address_1',
        'ship_address_2',
        'ship_address_3',
        'ship_city',
        'ship_country',
        'ship_from_city',
        'ship_from_country',
        'ship_from_postal_code',
        'ship_from_state',
        'ship_phone_number',
        'ship_postal_code',
        'ship_promotion_id',
        'ship_service_level',
        'ship_state',
        'shipping_id',
        'shipping_promo_vat_amount', // shipping_tax_amount_promo
        'shipping_promo_vat_excl_amount', // shipping_tax_exclusive_promo_amount
        'shipping_promo_vat_incl_amount', // shipping_tax_inclusive_promo_amount
        'shipping_promo_vat_rate',
        'shipping_vat_amount', // shipping_tax_amount
        'shipping_vat_excl_amount', // shipping_tax_exclusive_selling_price
        'shipping_vat_incl_amount', // shipping_tax_inclusive_selling_price
        'shipping_vat_rate',
        'transaction_id',
        'transaction_type',
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
