<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ComicGenre extends Model
{
    protected $table = 'comic_genres';
    protected $fillable = ['comic_id', 'genre_id'];
    public $timestamps = false; // Bảng trung gian không cần timestamps

    // Quan hệ
    public function comic()
    {
        return $this->belongsTo(Comic::class, 'comic_id');
    }

    public function genre()
    {
        return $this->belongsTo(Genre::class, 'genre_id');
    }
}