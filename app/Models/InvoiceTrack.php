<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InvoiceTrack extends Model
{
    use HasFactory;

    protected $table = 'invoice_tracks';

    protected $fillable = [
        'document_date',
        'registration_date',
        'document_number',
        'document_type',
        'currency',
        'gross_amount',
        'net_amount',
        'vat_amount',
        'split_payment',
        'vat_code',
        'unique_code_rif3',
        'buyer_tax_registration_type',
        'buyer_vat_number',
    ];

    /**
     * Aggiorna o crea i dati di una singola fattura nel database.
     *
     * @param array $data
     * @return void
     */
    public static function saveInvoiceTrackData(array $data)
    {
        self::updateOrCreate(
            [ // Condizioni per trovare il record esistente
                'document_date' => $data['document_date'],
                'registration_date' => $data['registration_date'],
                'document_number' => $data['document_number'],
                'document_type' => $data['document_type'],
                'currency' => $data['currency'],
                'unique_code_rif3' => $data['unique_code_rif3'],
                'buyer_tax_registration_type' => $data['buyer_tax_registration_type'],
                'buyer_vat_number' => $data['buyer_vat_number'],
            ],
            $data // Dati da aggiornare o creare
        );
    }
}
