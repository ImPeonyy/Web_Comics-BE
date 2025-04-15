<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Statistic extends Model
{
    protected $fillable = ['comic_id', 'view_count'];

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
}