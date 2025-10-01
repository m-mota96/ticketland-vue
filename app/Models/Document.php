<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Document extends Model
{
    protected $fillable = [
        'user_id', 'document', 'type', 'status', 
    ];

    public function user() {
        return $this->belongsTo(User::class);
    }
}
