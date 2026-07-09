<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class GarmentTarget extends Model
{
    use HasFactory;

    protected $fillable = [
        'name'
    ];

    public function garmentItems()
    {
        return $this->hasMany(GarmentItem::class);
    }
}
