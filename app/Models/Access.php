<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use DateTimeInterface;

class Access extends Model
{
    protected $fillable = [
        'payment_id',
        'ticket_id',
        'code_id',
        'unification',
        'folio',
        'folio_encrypted',
        'name',
        'email',
        'phone',
        'code_name',
        'code_discount',
        'price',
        'promotion',
        'status',
        'quantity',
        'date_validation',
        'saved_in_crm'
    ];

    public function ticket() {
        return $this->belongsTo(Ticket::class);
    }

    public function payment() {
        return $this->belongsTo(Payment::class);
    }

    public function turns() {
        return $this->belongsToMany(Turn::class);
    }

    public function code() {
        return $this->belongsTo(Code::class);
    }

    public function responses() {
        return $this->hasMany(Response::class);
    }

    protected function serializeDate(DateTimeInterface $date) {
        return $date->setTimezone(config('app.timezone'))->format('Y-m-d H:i:s');
    }
}
