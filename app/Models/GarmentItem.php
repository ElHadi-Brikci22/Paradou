<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class GarmentItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'garment_target_id',
        'name',
        'image_path'
    ];

    public function garmentTarget()
    {
        return $this->belongsTo(GarmentTarget::class);
    }

    public function servicePrices()
    {
        return $this->hasMany(ServicePrice::class);
    }

    public function orderItems()
    {
        return $this->hasMany(OrderItem::class);
    }
}
