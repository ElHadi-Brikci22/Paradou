<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Client extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'name',
        'phone',
        'email',
        'address',
        'remarks',
        'discount_percent',
        'credit'
    ];

    public function orders()
    {
        return $this->hasMany(Order::class);
    }
}
