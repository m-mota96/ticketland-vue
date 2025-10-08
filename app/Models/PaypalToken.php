<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PaypalToken extends Model
{
    protected $fillable = [
        'access_token',
        'token_type',
        'expires_in',
        'created_at_token',
    ];
}
