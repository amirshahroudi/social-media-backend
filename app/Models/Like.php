<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Like extends Model
{
    use HasFactory;
    protected $fillable = ['user_id', 'likable_id', 'likable_type'];

    protected static function booted()
    {
        parent::booted();
        static::created(function (Like $like) {
            $like->likable()->increment('like_count');
        });
        static::deleted(function (Like $like) {
            $like->likable()->decrement('like_count');
        });
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function likable()
    {
        return $this->morphTo();
    }
}
