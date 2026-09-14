<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class OrderItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'service_id',
        'garment_item_id',
        'pieces',
        'weight',
        'length',
        'width',
        'area',
        'is_measured',
        'quantity',
        'unit_price',
        'total_price',
        'colors',
        'defects',
        'stains',
        'is_ready',
        'is_delivered',
        'delivered_at',
        'notes'
    ];

    protected $casts = [
        'length' => 'float',
        'width' => 'float',
        'area' => 'float',
        'is_measured' => 'boolean',
        'colors' => 'array',
        'defects' => 'array',
        'stains' => 'array',
        'is_ready' => 'boolean',
        'is_delivered' => 'boolean',
        'delivered_at' => 'datetime',
    ];

    public function isCarpet(): bool
    {
        if ($this->garmentItem && method_exists($this->garmentItem, 'isCarpet') && $this->garmentItem->isCarpet()) {
            return true;
        }
        return $this->area !== null || $this->length !== null || $this->width !== null;
    }

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function service()
    {
        return $this->belongsTo(Service::class);
    }

    public function garmentItem()
    {
        return $this->belongsTo(GarmentItem::class);
    }
}
