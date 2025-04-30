<?php

namespace Database\Factories;


use App\Models\Comment;
use App\Models\Blog;
use Illuminate\Database\Eloquent\Factories\Factory;

class CommentFactory extends Factory
{
    protected $model = Comment::class;

    public function definition()
    {
        return [
            'blog_id' => Blog::factory(),
            'comment' => $this->faker->sentence,
            'user_id' => 1 // You can adjust this according to the users
        ];
    }
}
