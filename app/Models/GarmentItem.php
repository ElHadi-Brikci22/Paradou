<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class GarmentItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'garment_target_id',
        'garment_subcategory_id',
        'name',
        'image_path',
        'standard_weight',
        'unit_type',
        'is_carpet'
    ];

    protected $casts = [
        'is_carpet' => 'boolean',
    ];

    public function isCarpet(): bool
    {
        return (bool) ($this->is_carpet 
            || $this->unit_type === 'm2' 
            || stripos($this->name, 'tapis') !== false 
            || stripos($this->name, 'm²') !== false 
            || stripos($this->name, 'm2') !== false);
    }

    public function getStandardWeightKgAttribute()
    {
        return $this->standard_weight ? round(floatval($this->standard_weight) / 1000, 3) : 0;
    }

    public function garmentTarget()
    {
        return $this->belongsTo(GarmentTarget::class);
    }

    public function garmentSubcategory()
    {
        return $this->belongsTo(GarmentSubcategory::class);
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
