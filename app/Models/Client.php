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

    protected $appends = ['is_passager'];

    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    public function isPassager(): bool
    {
        return $this->code === 'GUEST' 
            || stripos($this->name, 'passage') !== false 
            || stripos($this->name, 'passager') !== false;
    }

    public function getIsPassagerAttribute(): bool
    {
        return $this->isPassager();
    }
}
