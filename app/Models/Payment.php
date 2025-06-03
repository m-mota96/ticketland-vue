<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use DateTimeInterface;

class Payment extends Model
{
    protected $fillable = [
        'event_id', 'order_id', 'code_id', 'name', 'email', 'phone', 'type', 'reference', 'amount', 'code', 'discount', 'status', 
    ];

    public function accesses() {
        return $this->hasMany(Access::class);
    }

    public function event() {
        return $this->belongsTo(Event::class);
    }

    public function discountCode() {
        return $this->belongsTo(Code::class);
    }

    protected function serializeDate(DateTimeInterface $date) {
        return $date->setTimezone(config('app.timezone'))->format('Y-m-d H:i:s');
    }
}
