<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ConektaCustomer extends Model
{
    protected $fillable = [
        'customer_id', 'email', 'name', 'phone', 
    ];
}
