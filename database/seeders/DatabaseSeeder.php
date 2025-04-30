<?php

namespace Database\Seeders;

use App\Models\Blog;
use App\Models\Tag;
use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Crear algunos usuarios
    User::factory(5)->create();

    // Crear algunos tags
    Tag::factory(10)->create();

    // Crear blogs con comentarios y tags
    Blog::factory()
        ->count(10)
        ->hasComments(3)
        ->hasTags(2)
        ->create();
    }
}
