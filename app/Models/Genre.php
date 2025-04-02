<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Genre extends Model
{
    protected $fillable = ['name', 'description'];

    // Quan hệ
    public function comics()
    {
        return $this->belongsToMany(Comic::class, 'comic_genres', 'genre_id', 'comic_id');
    }
}