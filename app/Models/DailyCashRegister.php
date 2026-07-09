<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class DailyCashRegister extends Model
{
    use HasFactory;

    protected $fillable = [
        'date',
        'opening_cash',
        'closing_cash',
        'user_id'
    ];

    protected $casts = [
        'date' => 'date',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
