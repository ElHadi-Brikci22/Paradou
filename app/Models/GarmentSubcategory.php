<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class GarmentSubcategory extends Model
{
    use HasFactory;

    protected $fillable = [
        'garment_target_id',
        'name',
        'sort_order'
    ];

    protected $casts = [
        'sort_order' => 'integer',
    ];

    public function garmentTarget()
    {
        return $this->belongsTo(GarmentTarget::class);
    }

    public function garmentItems()
    {
        return $this->hasMany(GarmentItem::class);
    }
}
