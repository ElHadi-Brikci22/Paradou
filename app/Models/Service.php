<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Service extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'code'
    ];

    public function servicePrices()
    {
        return $this->hasMany(ServicePrice::class);
    }

    public function orderItems()
    {
        return $this->hasMany(OrderItem::class);
    }
}
