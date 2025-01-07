<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DataCollection extends Model
{
    use HasFactory;
    protected $table = 'data_collections';

    protected $fillable = [
        'deposit_date',
        'document_date',
        'registration_date',
        'document_number',
        'document_type',
        'transaction_type',
        'currency',
        'amount',
        'unique_code_rif3',
        'buyer_tax_registration_type',
        'buyer_vat_number',
        'order_id'
    ];

    /**
     * Aggiorna o crea i dati di una singola collezione nel database.
     *
     * @param array $data
     * @return void
     */
    public static function saveCollectionData(array $data)
    {
        self::updateOrCreate(
            [
                'deposit_date' => $data['deposit_date'],
                'document_date' => $data['document_date'],
                'registration_date' => $data['registration_date'],
                'document_number' => $data['document_number'],
                'document_type' => $data['document_type'],
                'transaction_type' => $data['transaction_type'],
                'currency' => $data['currency'],
                'unique_code_rif3' => $data['unique_code_rif3'],
                'buyer_tax_registration_type' => $data['buyer_tax_registration_type'],
                'buyer_vat_number' => $data['buyer_vat_number']
            ],
            $data
        );
    }
}
