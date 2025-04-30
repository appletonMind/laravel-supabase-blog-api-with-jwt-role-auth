<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;


class Blog extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'slug',
        'description',
        'content',
        'seo_title',
        'seo_description',
        'keywords',
        'video_url',
        'created_by',
        'image_url',
        'image_path', 
        'image_path_url', 
        'image_path_supabase',
        'image_path_supabase_url',
        'file_size',    
        'file_type',    
        'is_available', // Indicates whether the blog is visible or not
    ];

  
    protected $hidden = [
        'image_path',
        'image_path_supabase',
        'file_type',
        //prrivate fields
    ];

    protected $casts = [
        'is_available'    => 'boolean',
    ];

// in Blog.php:
public function author()
{
    return $this->belongsTo(User::class, 'created_by');
}

public function tags()
{
    return $this->belongsToMany(Tag::class);
}

 // Relation with the comments
 public function comments()
 {
     return $this->hasMany(Comment::class);
 }


}
