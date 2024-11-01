<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GallerySlider extends Model
{
    protected $fillable = [
        'title', 'date', 'image'
    ];
    
    public $timestamps = false;
}
