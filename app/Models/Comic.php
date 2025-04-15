<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Comic extends Model
{
    protected $fillable = ['title', 'description', 'author', 'cover_image', 'status'];

    public function getCreatedAtAttribute($value)
    {
        return \Carbon\Carbon::parse($value)->timezone('Asia/Ho_Chi_Minh')->format('m/d/Y H:i:s');
    }

    public function getUpdatedAtAttribute($value)
    {
        return \Carbon\Carbon::parse($value)->timezone('Asia/Ho_Chi_Minh')->format('m/d/Y H:i:s');
    }

    // Quan hệ
    public function genres()
    {
        return $this->belongsToMany(Genre::class, 'comic_genres', 'comic_id', 'genre_id');
    }

    public function chapters()
    {
        return $this->hasMany(Chapter::class);
    }

    public function comments()
    {
        return $this->hasMany(Comment::class);
    }

    public function favorites()
    {
        return $this->hasMany(Favorite::class);
    }

    public function history()
    {
        return $this->hasMany(History::class);
    }

    public function statistics()
    {
        return $this->hasMany(Statistic::class);
    }
}