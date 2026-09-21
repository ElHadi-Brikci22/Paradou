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
        'total_weight',
        'paid_amount',
        'balance_amount',
        'remarks',
        'is_express',
        'uuid',
        'pos_terminal_code',
        'synced_at',
    ];

    protected $casts = [
        'order_date' => 'datetime',
        'target_delivery_date' => 'date',
        'actual_delivery_date' => 'datetime',
        'synced_at' => 'datetime',
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

    public function items()
    {
        return $this->orderItems();
    }

    public function isGuestOrder(): bool
    {
        return !$this->client || $this->client->isPassager();
    }

    public function isCredit(): bool
    {
        return in_array($this->status, ['delivered', 'partially_delivered']) && floatval($this->balance_amount) > 0;
    }
}
