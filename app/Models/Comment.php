<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;


class Comment extends Model
{

    use HasFactory;  

    protected $fillable = ['comment', 'blog_id', 'user_id'];

    // Relationship with the blog
    public function blog()
    {
        return $this->belongsTo(Blog::class);
    }

    // Relationship with the users
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}