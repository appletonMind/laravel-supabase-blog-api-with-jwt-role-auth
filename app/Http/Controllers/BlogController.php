<?php

namespace App\Http\Controllers;

use App\Models\Blog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;




class BlogController extends Controller
{


    /**
     * get All the blogs.
     */
    public function index()
    {
        $blogs = Blog::latest()->with('author')->get();
        return response()->json($blogs, 200);
    }

    /**
     * get blog by ID.
     */
    public function show($id)
    {
        $blog = Blog::with('author')->find($id);

        if (!$blog) {
            return response()->json(['message' => 'Blog not found'], 404);
        }

        return response()->json($blog, 200);
    }

    /**
     * create a blog (only admin).
     */
    public function store(Request $request)
    {
        $user = $request->user();
        if (!$user || $user->role !== 'admin') {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $validator = Validator::make($request->all(), [
            'title' => 'required|string|min:5|max:255',
            'slug' => 'required|string|unique:blogs,slug',
            'description' => 'nullable|string|max:1000', // o 'max:1000' dependiendo de lo que decidas
            'content' => 'required|string',
            'seo_title' => 'nullable|string|max:255',
            'seo_description' => 'nullable|string|max:255',
            'keywords' => 'nullable|string',
            'image_url' => 'nullable|url',
            'image' => 'required|image|mimes:jpg,jpeg,png|max:5120', // máximo 5MB

        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }



        // save image local
    $imagePath = $request->file('image')->store('images/blogs/','public');
    $imagePublicPath = asset('storage/images/blogs/' . basename($imagePath)); // acceso público

    // 3) we prepare to  upload the file to Supabase Storage
  $file       = $request->file('image');
  $fileName   = 'blog/' . uniqid('blog') . '.' . $file->getClientOriginalExtension();
  $bucket     = env('SUPABASE_BUCKET');
  $storageUrl = env('SUPABASE_URL') . "/storage/v1/object/{$bucket}/{$fileName}";

  // 4) We make the PUT request
  $response = Http::withHeaders([
    'apikey'        => env('SUPABASE_API_KEY'),
    'Authorization' => 'Bearer ' . env('SUPABASE_API_KEY'),
    'Content-Type'  => $file->getMimeType(),
])->withBody(
    file_get_contents($file->getRealPath()), // <-- cuerpo puro, no array
    $file->getMimeType() // <-- tipo MIME
)->put($storageUrl);

  if ($response->failed()) {
      return response()->json([
          'message' => 'Error uploading to Supabase Storage',
          'details' => $response->body()
      ], 500);
  }

  // 5) Now we have the route in Supabase
  $supabaseImagePath = $fileName;
  $supabaseImagePathpublic = rtrim(env('SUPABASE_URL'), '/') . '/storage/v1/object/public/' . $supabaseImagePath;

        $blog = Blog::create([
            'title' => $request->title,
            'slug' => $request->slug,
            'description' => $request->description,
            'content' => $request->content,
            'seo_title' => $request->seo_title,
            'seo_description' => $request->seo_description,
            'keywords' => $request->keywords,
            'file_size' => $request->file('image')->getSize(),
            'file_type' => $request->file('image')->getClientMimeType(),
            'image_url' => $request->image_url,
            'image_path' => $imagePath,
            'image_path_url' => $imagePublicPath,
            'image_path_supabase' => $supabaseImagePath,
            'image_path_supabase_url' => $supabaseImagePathpublic,
            'created_by' => $user->id,
        ]);

        $clean = $blog->toArray();

        // 2) We recursively traverse and clean each string
array_walk_recursive($clean, function (&$value) {
    if (is_string($value)) {
        // delete invalid bytes 
        // $value = iconv('UTF-8', 'UTF-8//IGNORE', $value);
        $value = mb_convert_encoding($value, 'UTF-8', 'UTF-8');
    }
});

return response()->json([
    'message' => 'blog added successfully',
    'blogs' => $blog->only([
        'title', 'slug', 'description', 'description', 'content', 'seo_title', 'keywords',
        'image_path_url', 'image_path', 'created_by'
    ]),
], 201, [], JSON_UNESCAPED_UNICODE);
    }

    /**
 * update a blog (only admin).
 */
public function update(Request $request, $id)
{
    $user = $request->user();
    if (!$user || $user->role !== 'admin') {
        return response()->json(['message' => 'Unauthorized'], 403);
    }

    $blog = Blog::find($id);
    if (!$blog) {
        return response()->json(['message' => 'Blog not found'], 404);
    }

    // Define validation rules
    $validator = Validator::make($request->all(), [
        'title' => 'sometimes|string|min:5|max:255',
        'slug' => 'sometimes|string|unique:blogs,slug,' . $blog->id,
        'description' => 'nullable|string',
        'content' => 'sometimes|string',
        'seo_title' => 'nullable|string|max:255',
        'seo_description' => 'nullable|string|max:255',
        'keywords' => 'nullable|string',
        'image_url' => 'nullable|url',
        'image' => 'nullable|image|mimes:jpg,jpeg,png|max:5120', // máximo 5MB

    ]);

    if ($validator->fails()) {
        return response()->json(['errors' => $validator->errors()], 422);
    }

     // 2) If jpg/png arrives, we delete the previous one in Supabase and upload the new one
     if ($request->hasFile('image')) {
        if ($blog->image_path_supabase) {
            $bucket = env('SUPABASE_BUCKET');
            $url = rtrim(env('SUPABASE_URL'), '/')
                 . "/storage/v1/object/{$bucket}/{$blog->image_path_supabase}";
            $del = Http::withHeaders([
                'apikey'        => env('SUPABASE_API_KEY'),
                'Authorization' => 'Bearer ' . env('SUPABASE_API_KEY'),
            ])->delete($url);
            if ($del->failed()) {
                Log::error("Supabase delete error: " . $del->body());
                return response()->json(['message'=>'Error deleting old file'], 500);
            }
        }

        // up new
        $f    = $request->file('image');
        $name = 'products/' . uniqid('product_') . '.' . $f->getClientOriginalExtension();
        $url  = rtrim(env('SUPABASE_URL'), '/')
              . "/storage/v1/object/" . env('SUPABASE_BUCKET') . "/{$name}";
        $up   = Http::withHeaders([
            'apikey'        => env('SUPABASE_API_KEY'),
            'Authorization' => 'Bearer ' . env('SUPABASE_API_KEY'),
            'Content-Type'  => $f->getClientMimeType(),
        ])->withBody(file_get_contents($f->getRealPath()), $f->getClientMimeType())
          ->put($url);
        if ($up->failed()) {
            return response()->json(['message'=>'Error uploading new file'], 500);
        }

        $blog->image_path_supabase = $name;
        $blog->file_size = $f->getSize();
        $blog->file_type = $f->getClientMimeType();
    }

    // 3) If an image arrives, we delete the old one and save it locally.
    if ($request->hasFile('image')) {
        if ($blog->image_path) {
            Storage::disk('public')->delete($blog->image_path);
        }
        $imgPath = $request->file('image')->store('images/blogs', 'public');
        $blog->image_path = $imgPath;
         // 3) generate and assign public URL
    $blog->image_path_url = asset('storage/' . $imgPath);
    }


    // Update the fields that were sent in the request
    $blog->fill($request->only([
        'title',
        'slug',
        'description',
        'content',
        'seo_title',
        'seo_description',
        'keywords',
        'image_url',
    ]));

    // Save changes
    $blog->save();

    // Limit author data to only id and username
    $author = $blog->author()->select('id', 'username')->first();

    // Reply with the updated blog and modified author
    return response()->json([
        'message' => 'Blog updated successfully',
        'blog' => $blog,
        'author' => $author
    ], 200);
}

    /**
     * Delete a blog (admin only).
     */
    public function destroy(Request $request, $id)
    {
        $user = $request->user();
        if (!$user || $user->role !== 'admin') {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $blog = Blog::find($id);
        if (!$blog) {
            return response()->json(['message' => 'Blog not found'], 404);
        }

          // 1) delete in Supabase
    if ($blog->image_path_supabase) {
        $bucket     = env('SUPABASE_BUCKET');
        $supabaseUrl = rtrim(env('SUPABASE_URL'), '/');
        $url = "{$supabaseUrl}/storage/v1/object/{$bucket}/{$blog->image_path_supabase}";

        $deleteResponse = Http::withHeaders([
            'apikey'        => env('SUPABASE_API_KEY'),
            'Authorization' => 'Bearer ' . env('SUPABASE_API_KEY'),
        ])->delete($url);

        if ($deleteResponse->failed()) {
            Log::error("Error deleting Supabase file: " . $deleteResponse->body());
            // opcional: return response()->json([...], 500);
        }
    }


         // 2) delete local image 
    if ($blog->image_path) {
        // image_path is, example, "images/products/xyz.png"
        Storage::disk('public')->delete($blog->image_path);
    }

        $blog->delete();

        return response()->json(['message' => 'Blog deleted successfully'], 200);
    }

    public function getBySlug($slug)
{
    $blog = Blog::with('author:id,username')->where('slug', $slug)->first();

    if (!$blog) {
        return response()->json(['message' => 'Blog not found'], 404);
    }

    return response()->json($blog, 200);
}

}
