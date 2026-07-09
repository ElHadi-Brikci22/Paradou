<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ServicePrice extends Model
{
    use HasFactory;

    protected $fillable = [
        'service_id',
        'garment_item_id',
        'price'
    ];

    public function service()
    {
        return $this->belongsTo(Service::class);
    }

    public function garmentItem()
    {
        return $this->belongsTo(GarmentItem::class);
    }
}
