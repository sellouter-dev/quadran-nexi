<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\User;
use App\Models\CustomerCredential;

class Customer extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'unique_id',
        'company_name',
        'first_name',
        'last_name',
        'email',
        'phone',
        'web_site',
        'turnover_projection',
        'address',
        'zip_code',
        'city',
        'province',
        'country',
        'vat_number',
        'fiscal_code',
        'amazon_seller',
        'amazon_vendor',
        'amazon_advertising',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'id' => 'integer',
            'unique_id' => 'string',
            'amazon_seller' => 'boolean',
            'amazon_vendor' => 'boolean',
            'amazon_advertising' => 'boolean',
        ];
    }

    /**
     * The "booted" method of the model.
     *
     * @return void
     */
    protected static function booted()
    {
        static::creating(function ($customer) {
            $customer->unique_id = Str::uuid();
        });
    }

    /**
     * Get the users for the customer.
     *
     * @return HasMany<User>
     */
    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    /**
     * Get the customerCredentials for the customer.
     *
     * @return HasMany<CustomerCredential>
     */
    public function customerCredentials(): HasMany
    {
        return $this->hasMany(CustomerCredential::class);
    }
}
