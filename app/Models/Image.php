<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Image extends Model
{
    protected $fillable = ['chapter_id', 'image_url', 'image_order'];

    // Quan hệ
    public function chapter()
    {
        return $this->belongsTo(Chapter::class);
    }
}
