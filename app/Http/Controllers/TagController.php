<?php
namespace App\Http\Controllers;

use App\Models\Tag;
use App\Models\Blog;
use Illuminate\Http\Request;

class TagController extends Controller
{
    // Get all tags
    public function getTags()
    {
        $tags = Tag::all();
        return response()->json($tags);
    }

    // Create a new tag
    public function createTag(Request $request)
    {
        // Verify that the user is authenticated and is an administrator
        $user = $request->user();
        if (!$user || $user->role !== 'admin') {
            return response()->json(['message' => 'Unauthorized or not an admin'], 401);
        }

        // Validate the data
        $validated = $request->validate([
            'name' => 'required|string|max:100|unique:tags,name',
        ]);

        // Create the tag
        $tag = Tag::create([
            'name' => $validated['name'],
        ]);

        return response()->json(['message' => 'Tag created successfully', 'tag' => $tag], 201);
    }

    // Update an existing tag
    public function updateTag(Request $request, $id)
    {
        // Verify that the user is authenticated and is an administrator
        $user = $request->user();
        if (!$user || $user->role !== 'admin') {
            return response()->json(['message' => 'Unauthorized or not an admin'], 401);
        }

        // search by tags
        $tag = Tag::find($id);
        if (!$tag) {
            return response()->json(['message' => 'Tag not found'], 404);
        }

        // Validate and update the tag
        $validated = $request->validate([
            'name' => 'required|string|max:100|unique:tags,name,' . $id,
        ]);

        $tag->update(['name' => $validated['name']]);

        return response()->json(['message' => 'Tag updated successfully', 'tag' => $tag], 200);
    }

    // Assign tags to a product
    public function assignTagsToBlogs(Request $request, $blogId)
    {
        // Verify that the user is authenticated
        $user = $request->user();
        if (!$user) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        // Validate the product
        $blog = Blog::find($blogId);
        if (!$blog) {
            return response()->json(['message' => 'Product not found'], 404);
        }

        // Validate the provided tags
        $validated = $request->validate([
            'tags' => 'required|array',
            'tags.*' => 'exists:tags,id', // Make sure the tags exist
        ]);

        // Assign tags to the product
        $blog->tags()->sync($validated['tags']); // We use sync() to assign the tags without duplicates

        return response()->json([
            'message' => 'Tags assigned successfully',
            'tags'    => $blog->tags()->pluck('name', 'id'),
        ], 200);
    }

    // Get a tag by ID
public function getTagById($id)
{
    // Search for the tag
    $tag = Tag::find($id);

    if (!$tag) {
        return response()->json(['message' => 'Tag not found'], 404);
    }

    return response()->json($tag, 200);
}

// Delete a tag
public function deleteTag(Request $request, $id)
{
    // Verify that the user is authenticated and is an administrator
    $user = $request->user();
    if (!$user || $user->role !== 'admin') {
        return response()->json(['message' => 'Unauthorized or not an admin'], 401);
    }

    $tag = Tag::find($id);
    if (!$tag) {
        return response()->json(['message' => 'Tag not found'], 404);
    }

    $tag->delete();

    return response()->json(['message' => 'Tag deleted successfully'], 200);
}


}
