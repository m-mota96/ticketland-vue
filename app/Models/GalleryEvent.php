<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GalleryEvent extends Model
{
    protected $fillable = [
        'event_id', 'name', 'type', 
    ];
}
