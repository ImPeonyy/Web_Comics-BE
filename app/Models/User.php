<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens;

    const DEFAULT_AVATAR = 'https://res.cloudinary.com/do2rk0jz8/image/upload/v1743480981/avatars/default_avatar.webp';

    protected $fillable = ['username', 'email', 'password', 'exp', 'role', 'avatar'];
    protected $hidden = ['password']; // Ẩn password khi trả về JSON

    // Quan hệ
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
}