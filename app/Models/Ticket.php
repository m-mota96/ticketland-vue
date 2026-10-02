<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Ticket extends Model
{
    protected $fillable = [
        'event_id',
        'name',
        'description',
        'price',
        'quantity',
        'sales',
        'reserved',
        'stored',
        'valid',
        'use_turns',
        'promotion',
        'date_promotion',
        'start_sale',
        'stop_sale',
        'min_reservation',
        'max_reservation',
        'package',
        'number_of_access',
        'order',
        'status',
        'crm_event_id',
        'saved_in_crm'
    ];

    public function event() {
        return $this->belongsTo(Event::class);
    }

    public function access() {
        return $this->hasMany(Access::class);
    }
    
    public function questions() {
        return $this->belongsToMany(Question::class);
    }

    public function codes() {
        return $this->belongsToMany(Code::class)->withPivot('used', 'reserved');
    }
}
