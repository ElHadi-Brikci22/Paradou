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
        'quantity',
        'unit_price',
        'total_price',
        'colors',
        'defects',
        'stains',
        'is_ready',
        'notes'
    ];

    protected $casts = [
        'colors' => 'array',
        'defects' => 'array',
        'stains' => 'array',
        'is_ready' => 'boolean',
    ];

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
