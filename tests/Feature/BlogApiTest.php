<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Blog;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Http;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Services\SupabaseService;


class BlogApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Disable your custom auth & JWT middlewares so they don’t block the tests.
        $this->withoutMiddleware(\App\Http\Middleware\IsUserAuth::class);
        $this->withoutMiddleware(\App\Http\Middleware\IsAdmin::class);
        $this->withoutMiddleware(\App\Http\Middleware\CheckJWT::class);
        $this->app->instance(
            SupabaseService::class,
            $this->createMock(SupabaseService::class)
        );
    }

    /** @test */
    public function index_returns_list_of_blogs()
    {
        // Arrange: create 3 blogs
        Blog::factory()->count(3)->create();

        // Act: call GET /blogs
        $response = $this->getJson('/blogs');

        // Assert: 200 + JSON with 3 items
        $response->assertOk()
                 ->assertJsonCount(3)
                 ->assertJsonStructure([['id','title','slug','created_by']]);
    }

    /** @test */
    public function show_returns_single_blog_by_id()
    {
        // Arrange
        $blog = Blog::factory()->create();

        // Act
        $response = $this->getJson("/blogs/{$blog->id}");

        // Assert
        $response->assertOk()
                 ->assertJsonFragment(['id' => $blog->id]);
    }

    /** @test */
    public function show_returns_single_blog_by_slug()
    {
        // Arrange
        $blog = Blog::factory()->create();

        // Act
        $response = $this->getJson("/blogs/slug/{$blog->slug}");

        // Assert
        $response->assertOk()
                 ->assertJsonFragment(['slug' => $blog->slug]);
    }

    /** @test */
public function store_creates_blog_with_image_and_supabase()
{
    // Arrange: fake the public disk and Supabase HTTP
    $disk = Storage::fake('public');              // ← grab the fake disk instance
    Http::fake(['*' => Http::response('', 200)]);

    // Create & authenticate an admin user on the 'api' guard
    $admin = User::factory()->admin()->create();
    $this->actingAs($admin, 'api');

    // Prepare a fake upload
    $file = UploadedFile::fake()->image('photo.jpg')->size(1000);

    // Act: hit POST /blogs/create-blogs
    $response = $this->postJson('/blogs/create-blogs', [
        'title'   => 'Test Title',
        'slug'    => 'test-slug',
        'description' => 'Descripción corta del blog', // <- requerido
        'content' => 'Test content',
        'image'   => $file,
    ]);

    // Assert: created
    $response->dump();  
    $response->assertStatus(201)
             ->assertJsonFragment(['message' => 'blog added successfully']);

    // **Instead** of Storage::disk('public')->assertExists(...):
    $this->assertTrue(
        $disk->exists('images/blogs/'.$file->hashName()),
        'The uploaded image should exist on the public disk.'
    );

    // And the DB was updated
    $this->assertDatabaseHas('blogs', [
        'title'      => 'Test Title',
        'slug'       => 'test-slug',
        'created_by' => $admin->id,
    ]);
}


    /** @test */
    public function update_changes_blog_title()
    {
        Http::fake(['*' => Http::response('', 200)]);

        $admin = User::factory()->admin()->create();
        $blog  = Blog::factory()->create();

        // Authenticate on the same guard you check in middleware
        $this->actingAs($admin, 'api');

        $response = $this->patchJson("/blogs/{$blog->id}", [
            'title' => 'Updated Title',
        ]);

        $response->assertOk()
                 ->assertJsonFragment(['message' => 'Blog updated successfully']);

        $this->assertDatabaseHas('blogs', [
            'id'    => $blog->id,
            'title' => 'Updated Title',
        ]);
    }

    /** @test */
    public function destroy_deletes_blog()
    {
        Http::fake(['*' => Http::response('', 200)]);

        $admin = User::factory()->admin()->create();
        $blog  = Blog::factory()->create();

        $this->actingAs($admin, 'api');

        $response = $this->deleteJson("/blogs/{$blog->id}");

        $response->assertOk()
                 ->assertJsonFragment(['message' => 'Blog deleted successfully']);

        $this->assertDatabaseMissing('blogs', ['id' => $blog->id]);
    }
}
