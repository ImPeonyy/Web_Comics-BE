<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Statistic extends Model
{
    protected $fillable = ['comic_id', 'view_count'];

    // Quan hệ
    public function comic()
    {
        return $this->belongsTo(Comic::class);
    }
}