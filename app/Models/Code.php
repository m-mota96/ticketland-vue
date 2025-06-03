<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Code extends Model
{
    protected $fillable = [
        'event_id', 'email', 'customer_name', 'code', 'quantity', 'used', 'reserved', 'discount', 'expiration', 'status', 
    ];

    public function event() {
        return $this->belongsTo(Event::class);
    }

    public function tickets() {
        return $this->belongsToMany(Ticket::class)->withPivot('used', 'reserved')->orderBy('name');
    }

    public function accesses() {
        return $this->belongsToMany(Access::class)->withPivot('ticket_price', 'discount');
    }

    public function accesses_payed() {
        return $this->belongsToMany(Access::class)->withPivot('ticket_price', 'discount')->whereHas('payment', function($query) {
            return $query->where('status', 'payed');
        });
    }

    public function payments() {
        return $this->hasMany(Payment::class);
    }
}
