<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class SellerInventoryItem extends Model
{
    use HasFactory;

    protected $table = 'seller_inventory_items';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'report_date',
        'fnsku',
        'asin',
        'msku',
        'title',
        'disposition',
        'starting_warehouse_balance',
        'in_transit_between_warehouses',
        'receipts',
        'customer_shipments',
        'customer_returns',
        'vendor_returns',
        'warehouse_transfer_in_out',
        'found',
        'lost',
        'damaged',
        'disposed',
        'other_events',
        'ending_warehouse_balance',
        'unknown_events',
        'location',
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
            'starting_warehouse_balance' => 'integer',
            'in_transit_between_warehouses' => 'integer',
            'receipts' => 'integer',
            'customer_shipments' => 'integer',
            'customer_returns' => 'integer',
            'vendor_returns' => 'integer',
            'warehouse_transfer_in_out' => 'integer',
            'found' => 'integer',
            'lost' => 'integer',
            'damaged' => 'integer',
            'disposed' => 'integer',
            'other_events' => 'integer',
            'ending_warehouse_balance' => 'integer',
            'unknown_events' => 'integer',
        ];
    }

    /**
     * Accessor per formattare report_date in YYYY-MM-DD.
     *
     * @param  string|null  $value
     * @return string|null
     */
    public function getReportDateAttribute($value)
    {
        return $value ? Carbon::parse($value)->toDateString() : null;
    }
}