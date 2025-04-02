<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Chapter extends Model
{
    protected $fillable = ['comic_id', 'chapter_order', 'title'];

    // Quan hệ
    public function comic()
    {
        return $this->belongsTo(Comic::class);
    }

    public function images()
    {
        return $this->hasMany(Image::class);
    }

    public function history()
    {
        return $this->hasMany(History::class);
    }
}