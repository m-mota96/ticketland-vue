<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PaymentMethod extends Model
{
    protected $fillable = [
        'name', 'sku', 'commission'
    ];

    public function events() {
        return $this->belongsToMany(Event::class);
    }
}
