<?php
// database/factories/BlogFactory.php

namespace Database\Factories;

use App\Models\Blog;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class BlogFactory extends Factory
{
    protected $model = Blog::class;

    public function definition()
    {
        return [
            'title' => $this->faker->sentence,
            'slug' => $this->faker->unique()->slug,
            'description' => $this->faker->paragraph,
            'content' => $this->faker->paragraphs(3, true),
            'seo_title' => $this->faker->sentence,
            'seo_description' => $this->faker->paragraph,
            'keywords' => implode(',', $this->faker->words(5)),
            'video_url' => $this->faker->optional()->url,
            'created_by' => User::factory(), // relationschip wit a user
            'image_url' => $this->faker->imageUrl(800, 600),
            'image_path' => 'fake/path/to/image.jpg',
            'image_path_url' => 'https://example.com/fake/path/to/image.jpg',
            'image_path_supabase' => 'public/blogs/image.jpg',
            'image_path_supabase_url' => 'https://your-project.supabase.co/storage/v1/object/public/blogs/image.jpg',
            'file_size' => $this->faker->numberBetween(10000, 500000), // bytes
            'file_type' => 'image/jpeg',
            'is_available' => $this->faker->boolean(80), // 80% true
        ];
    }

    public function configure()
{
    return $this->afterCreating(function (Blog $blog) {
        $blog->tags()->attach(
            Tag::factory()->count(2)->create()->pluck('id')->toArray()
        );
    });
}

}
