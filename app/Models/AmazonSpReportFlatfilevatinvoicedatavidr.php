<?php

namespace App\Models;

use Illuminate\Support\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class AmazonSpReportFlatfilevatinvoicedatavidr extends Model
{
    use HasFactory;

    protected $table = 'amazon_sp_report_flatfilevatinvoicedatavidr';

    protected $primaryKey = 'id'; // Imposta 'id' come chiave primaria
    public $incrementing = true; // Abilita l'auto-incremento
    protected $keyType = 'int'; // Specifica che 'id' è di tipo intero

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
        $mappedData = [
            'requesttime' => isset($data['requesttime']) ? Carbon::parse($data['requesttime']) : null,
            'marketplace' => $data['marketplace'] ?? null,
            'asin' => $data['asin'] ?? null,
            'sku' => $data['sku'] ?? null,
            'order_id' => $data['order_id'] ?? null,
            'order_item_id' => $data['order_item_id'] ?? null,
            'shipment_date' => isset($data['shipment_date']) ? Carbon::parse($data['shipment_date']) : null,
            'order_date' => isset($data['order_date']) ? Carbon::parse($data['order_date']) : null,
            'bill_address_1' => $data['bill_address_1'] ?? null,
            'bill_address_2' => $data['bill_address_2'] ?? null,
            'bill_address_3' => $data['bill_address_3'] ?? null,
            'bill_city' => $data['bill_city'] ?? null,
            'bill_country' => $data['bill_country'] ?? null,
            'bill_postal_code' => $data['bill_postal_code'] ?? null,
            'bill_state' => $data['bill_state'] ?? null,
            'billing_name' => $data['billing_name'] ?? null,
            'billing_phone_number' => $data['billing_phone_number'] ?? null,
            'buyer_company_name' => $data['buyer_company_name'] ?? null,
            'buyer_e_invoice_account_id' => $data['buyer_e_invoice_account_id'] ?? null,
            'buyer_name' => $data['buyer_name'] ?? null,
            'buyer_tax_registration_type' => $data['buyer_tax_registration_type'] ?? null,
            'buyer_vat_number' => $data['buyer_vat_number_divr'] ?? null,
            'citation_de' => $data['citation_de'] ?? null,
            'citation_en' => $data['citation_en'] ?? null,
            'citation_es' => $data['citation_es'] ?? null,
            'citation_fr' => $data['citation_fr'] ?? null,
            'citation_it' => $data['citation_it'] ?? null,
            'currency' => $data['currency'] ?? null,
            'export_outside_eu' => isset($data['export_outside_eu']) ? ($data['export_outside_eu'] ? '1' : '0') : null,
            'fulfilled_by' => $data['fulfilled_by'] ?? null,
            'gift_promo_vat_amount' => (float) $data['gift_promo_vat_amount'],
            'gift_promo_vat_excl_amount' => (float) $data['gift_promo_vat_excl_amount'],
            'gift_promo_vat_incl_amount' => (float) $data['gift_promo_vat_incl_amount'],
            'gift_promo_vat_rate' => (float) $data['gift_promo_vat_rate'],
            'gift_promotion_id' => $data['gift_promotion_id'] ?? null,
            'gift_wrap_vat_amount' => (float) $data['gift_wrap_vat_amount'],
            'gift_wrap_vat_excl_amount' => (float) $data['gift_wrap_vat_excl_amount'],
            'gift_wrap_vat_incl_amount' => (float) $data['gift_wrap_vat_incl_amount'],
            'gift_wrap_vat_rate' => (float) $data['gift_wrap_vat_rate'],
            'invoice_correction_details' => $data['invoice_correction_details'] ?? null,
            'invoice_number' => $data['invoice_number'] ?? null,
            'invoice_status' => $data['invoice_status'] ?? null,
            'invoice_status_description' => $data['invoice_status_description'] ?? null,
            'is_amazon_invoiced' => isset($data['is_amazon_invoiced']) ? ($data['is_amazon_invoiced'] ? '1' : '0') : null,
            'is_business_order' => isset($data['is_business_order']) ? ($data['is_business_order'] ? '1' : '0') : null,
            'is_buyer_physically_present' => isset($data['is_buyer_physically_present']) ? ($data['is_buyer_physically_present'] ? '1' : '0') : null,
            'is_invoice_corrected' => isset($data['is_invoice_corrected']) ? ($data['is_invoice_corrected'] ? '1' : '0') : null,
            'is_seller_physically_present' => isset($data['is_seller_physically_present']) ? ($data['is_seller_physically_present'] ? '1' : '0') : null,
            'item_promo_vat_amount' => (float) $data['item_promo_vat_amount'],
            'item_promo_vat_excl_amount' => (float) $data['item_promo_vat_excl_amount'],
            'item_promo_vat_incl_amount' => (float) $data['item_promo_vat_incl_amount'],
            'item_promo_vat_rate' => (float) $data['item_promo_vat_rate'],
            'item_promotion_id' => $data['item_promotion_id'] ?? null,
            'item_vat_amount' => (float) $data['item_vat_amount'],
            'item_vat_excl_amount' => (float) $data['item_vat_excl_amount'],
            'item_vat_incl_amount' => (float) $data['item_vat_incl_amount'],
            'item_vat_rate' => (float) $data['item_vat_rate'],
            'legacy_customer_order_item_id' => $data['legacy_customer_order_item_id'] ?? null,
            'original_vat_invoice_number' => $data['original_vat_invoice_number'] ?? null,
            'price_designation' => $data['price_designation'] ?? null,
            'product_name' => $data['product_name'] ?? null,
            'purchase_order_number' => $data['purchase_order_number'] ?? null,
            'quantity_purchased' => (int) $data['quantity_purchased'],
            'recipient_name' => $data['recipient_name'] ?? null,
            'recommended_invoice_format' => $data['recommended_invoice_format'] ?? null,
            'seller_vat_number' => $data['seller_vat_number'] ?? null,
            'ship_address_1' => $data['ship_address_1'] ?? null,
            'ship_address_2' => $data['ship_address_2'] ?? null,
            'ship_address_3' => $data['ship_address_3'] ?? null,
            'ship_city' => $data['ship_city'] ?? null,
            'ship_country' => $data['ship_country'] ?? null,
            'ship_from_city' => $data['ship_from_city'] ?? null,
            'ship_from_country' => $data['ship_from_country'] ?? null,
            'ship_from_postal_code' => $data['ship_from_postal_code'] ?? null,
            'ship_from_state' => $data['ship_from_state'] ?? null,
            'ship_phone_number' => $data['ship_phone_number'] ?? null,
            'ship_postal_code' => $data['ship_postal_code'] ?? null,
            'ship_promotion_id' => $data['ship_promotion_id'] ?? null,
            'ship_service_level' => $data['ship_service_level'] ?? null,
            'ship_state' => $data['ship_state'] ?? null,
            'shipping_id' => $data['shipping_id'] ?? null,
            'shipping_promo_vat_amount' => (float) $data['shipping_promo_vat_amount'],
            'shipping_promo_vat_excl_amount' => (float) $data['shipping_promo_vat_excl_amount'],
            'shipping_promo_vat_incl_amount' => (float) $data['shipping_promo_vat_incl_amount'],
            'shipping_promo_vat_rate' => (float) $data['shipping_promo_vat_rate'],
            'shipping_vat_amount' => (float) $data['shipping_vat_amount'],
            'shipping_vat_excl_amount' => (float) $data['shipping_vat_excl_amount'],
            'shipping_vat_incl_amount' => (float) $data['shipping_vat_incl_amount'],
            'shipping_vat_rate' => (float) $data['shipping_vat_rate'],
            'transaction_id' => $data['transaction_id'] ?? null,
            'transaction_type' => $data['transaction_type'] ?? null,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ];

        self::updateOrCreate(
            [
                "transaction_id" => $mappedData["transaction_id"],
                "order_id" => $mappedData["order_id"],
                "asin" => $mappedData["asin"],
                "order_item_id" => $mappedData["order_item_id"],
            ],
            $mappedData
        );
    }
}
