<?php

namespace App\Models;

use Illuminate\Support\Facades\Log;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class FlatfileVatInvoiceData extends Model
{
    use HasFactory;

    protected $table = 'flatfile_vat_invoice_data';

    protected $fillable = [
        'buyer_name',
        'buyer_address',
        'buyer_postal_code',
        'buyer_city',
        'buyer_country',
        'buyer_province_code',
        'buyer_tax_registration_type',
        'buyer_vat_number',
    ];

    /**
     * Aggiorna o crea i dati di una singola fattura nel database.
     *
     * @param array $data
     * @return void
     */
    public static function saveInvoiceData(array $data)
    {
        Log::info('Data: ' . json_encode($data));
        self::updateOrCreate(
            [ // Condizioni per trovare il record esistente
                'buyer_name' => $data['buyer_name'],
                'buyer_address' => $data['buyer_address'],
                'buyer_postal_code' => $data['buyer_postal_code'],
                'buyer_city' => $data['buyer_city'],
                'buyer_country' => $data['buyer_country'],
                'buyer_province_code' => $data['buyer_province_code'],
                'buyer_tax_registration_type' => $data['buyer_tax_registration_type'],
                'buyer_vat_number' => $data['buyer_vat_number'],
            ],
            $data // Dati da aggiornare o creare
        );
    }
}
