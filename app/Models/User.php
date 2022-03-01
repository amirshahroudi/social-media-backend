<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    const MAX_BIO_CHARS = 150;

    const ADMIN = 'admin';
    const USER = 'user';

    const PUBLIC_ACCOUNT = false;
    const PRIVATE_ACCOUNT = true;

    const DEFAULT_PRIVACY = User::PUBLIC_ACCOUNT;
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'username',
        'bio',
        'post_count',
        'following_count',
        'follower_count',
        'profile_image_url',
        'privacy',
        'email',
        'type',
        'email_verified_at',
        'password',
    ];
    //todo set number of following and followers in User Model
    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    public function posts()
    {
        return $this->hasMany(Post::class);
    }

    public function comments()
    {
        return $this->hasMany(Comment::class);
    }

    public function likes()
    {
        return $this->hasMany(Like::class);
    }

    public function followers()
    {
        //todo test created at
        return $this->belongsToMany(User::class, 'user_user', 'user_id', 'follower_id')->withTimestamps();
    }

    public function followings()
    {
        return $this->belongsToMany(User::class, 'user_user', 'follower_id', 'user_id');
    }

    public function receivedFollowRequests()
    {
        return $this->belongsToMany(User::class, 'follow_requests', 'request_to', 'request_from');
    }

    public function sentFollowRequests()
    {
        return $this->belongsToMany(User::class, 'follow_requests', 'request_from', 'request_to');
    }
}
