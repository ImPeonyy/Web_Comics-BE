<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Chapter extends Model
{
    protected $fillable = ['comic_id', 'chapter_order', 'title'];

    public function getCreatedAtAttribute($value)
    {
        return \Carbon\Carbon::parse($value)->timezone('Asia/Ho_Chi_Minh')->format('m/d/Y H:i:s');
    }

    public function getUpdatedAtAttribute($value)
    {
        return \Carbon\Carbon::parse($value)->timezone('Asia/Ho_Chi_Minh')->format('m/d/Y H:i:s');
    }

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