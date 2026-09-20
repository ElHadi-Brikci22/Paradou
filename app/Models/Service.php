<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Service extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'code',
        'sort_order',
        'price',
        'wholesale_price'
    ];

    protected $casts = [
        'sort_order' => 'integer',
        'price' => 'decimal:2',
        'wholesale_price' => 'decimal:2',
    ];

    public function isKilo(): bool
    {
        return $this->code === 'au_kilo' || str_contains(strtolower($this->name), 'kilo') || $this->id === 4;
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
