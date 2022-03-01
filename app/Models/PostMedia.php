<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PostMedia extends Model
{
    use HasFactory;
    protected $table = 'postMedias';
    protected $fillable = ['post_id', 'media_url', 'priority'];

    public function post()
    {
        return $this->belongsTo(Post::class);
    }
}
