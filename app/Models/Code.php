<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Code extends Model
{
    protected $fillable = [
        'email', 'customer_name', 'code', 'quantity', 'discount', 'expiration', 'status', 
    ];

    public function tickets() {
        return $this->belongsToMany(Ticket::class)->withPivot('used', 'reserved');
    }

    public function accesses() {
        return $this->belongsToMany(Access::class)->withPivot('ticket_price', 'discount');
    }

    public function accesses_payed() {
        return $this->belongsToMany(Access::class)->withPivot('ticket_price', 'discount')->whereHas('payment', function($query) {
            return $query->where('status', 'payed');
        });
    }
}
