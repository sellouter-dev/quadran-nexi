<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DepositDateHistory extends Model
{
    /**
     * Nome della tabella associata.
     *
     * @var string
     */
    protected $table = 'deposit_date_history';

    /**
     * Chiave primaria della tabella.
     *
     * @var string
     */
    protected $primaryKey = 'id';

    /**
     * La chiave primaria non è autoincrementante in stile Laravel (int auto-increment), ma gestita da Oracle.
     *
     * @var bool
     */
    public $incrementing = true;

    /**
     * Il tipo della chiave primaria.
     *
     * @var string
     */
    protected $keyType = 'int';

    /**
     * Disabilita i timestamp automatici di Laravel (created_at, updated_at).
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * Attributi assegnabili in massa.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'deposit_date',
        'created_at',
    ];

    /**
     * Cast automatici dei campi.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'deposit_date' => 'datetime',
        'created_at'   => 'datetime',
    ];
}
