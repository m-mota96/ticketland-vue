<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Exclusivity extends Model
{
    protected $fillable = [
        'user_id', 'name', 
    ];
}
