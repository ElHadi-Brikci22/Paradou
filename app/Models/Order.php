<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'ticket_number',
        'client_id',
        'user_id',
        'status',
        'is_paid',
        'order_date',
        'target_delivery_date',
        'actual_delivery_date',
        'discount_percent',
        'discount_type',
        'discount_amount',
        'total_amount',
        'paid_amount',
        'balance_amount',
        'remarks',
        'is_express'
    ];

    protected $casts = [
        'order_date' => 'datetime',
        'target_delivery_date' => 'date',
        'actual_delivery_date' => 'datetime',
        'is_paid' => 'boolean',
        'is_express' => 'boolean',
    ];

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function orderItems()
    {
        return $this->hasMany(OrderItem::class);
    }
}
