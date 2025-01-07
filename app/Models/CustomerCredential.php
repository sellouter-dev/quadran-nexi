<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;

/**
 * Class CustomerCredential
 *
 * @package App\Models\Credentials
 *
 * Represents customer credentials stored in the database.
 */
class CustomerCredential extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'customer_unique_id',
        'type',
        'marketplace_id',
        'token',
        'lwa_client_id',
        'lwa_client_secret',
        'lwa_refresh_token',
        'access_key_id',
        'secret_access_key',
        'role_arn',
        'profile_id',
        'dsp_profile_id',
        'region',
        'is_active',
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
            'customer_unique_id' => 'string',
            'marketplace_id' => 'integer',
            'lwa_client_secret' => 'encrypted',
            'lwa_refresh_token' => 'encrypted',
            'secret_access_key' => 'encrypted',
            'is_active' => 'boolean',
        ];
    }

    /**
     * Scope a query to filter by customer ID.
     *
     * @param  Builder<CustomerCredential>  $query
     * @param  string  $customerId
     * @return Builder<CustomerCredential>
     */
    public function scopeCustomerId($query, string $customerId)
    {
        return $query->where('customer_unique_id', $customerId);
    }

    /**
     * Define a relationship with the Customer model.
     *
     * @return BelongsTo<Customer, CustomerCredential>
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class, 'customer_unique_id', 'unique_id');
    }

    /**
     * Define a relationship with the Marketplace model.
     *
     * @return BelongsTo<Marketplace, CustomerCredential>
     */
    public function marketplace(): BelongsTo
    {
        return $this->belongsTo(Marketplace::class);
    }

    /**
     * Scope a query to filter by marketplace ID.
     *
     * @param  Builder<CustomerCredential>  $query
     * @param  int  $marketplaceId
     * @return Builder<CustomerCredential>
     */
    public function scopeMarketplaceId($query, int $marketplaceId)
    {
        return $query->where('marketplace_id', $marketplaceId);
    }

    /**
     * Scope a query to get vendor customer credentials.
     *
     * @param  Builder<CustomerCredential>  $query
     * @return Builder<CustomerCredential>
     */
    public function scopeVendor($query)
    {
        return $query->where('type', 'vendor');
    }

    /**
     * Scope a query to get seller customer credentials.
     *
     * @param  Builder<CustomerCredential>  $query
     * @return Builder<CustomerCredential>
     */
    public function scopeSeller($query)
    {
        return $query->where('type', 'seller');
    }

    /**
     * Scope a query to get active customer credentials.
     *
     * @param  Builder<CustomerCredential>  $query
     * @return Builder<CustomerCredential>
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
