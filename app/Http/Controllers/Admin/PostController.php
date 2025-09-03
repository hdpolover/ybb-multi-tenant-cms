<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Post;
use App\Models\Term;
use App\Models\Media;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class PostController extends Controller
{
    /**
     * Display a listing of posts
     */
    public function index(Request $request)
    {
        $query = Post::with(['creator', 'terms', 'media'])->latest();

        // Filter by type
        if ($request->has('type') && $request->type) {
            $query->where('type', $request->type);
        }

        // Filter by status
        if ($request->has('status') && $request->status) {
            $query->where('status', $request->status);
        }

        // Search
        if ($request->has('search') && $request->search) {
            $query->where(function ($q) use ($request) {
                $q->where('title', 'like', '%' . $request->search . '%')
                  ->orWhere('content', 'like', '%' . $request->search . '%');
            });
        }

        $posts = $query->paginate(20);

        return view('admin.posts.index', compact('posts'));
    }

    /**
     * Show the form for creating a new post
     */
    public function create()
    {
        $categories = Term::where('type', 'category')->get();
        $tags = Term::where('type', 'tag')->get();

        return view('admin.posts.create', compact('categories', 'tags'));
    }

    /**
     * Store a newly created post
     */
    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'excerpt' => 'nullable|string|max:500',
            'type' => 'required|in:post,page,news,guide',
            'status' => 'required|in:draft,published,archived',
            'featured_image' => 'nullable|image|max:2048',
            'seo_title' => 'nullable|string|max:255',
            'seo_description' => 'nullable|string|max:255',
            'categories' => 'nullable|array',
            'tags' => 'nullable|array',
        ]);

        $data = $request->all();
        $data['slug'] = Str::slug($request->title);
        $data['tenant_id'] = tenant('id');
        $data['created_by'] = auth()->id();

        // Ensure unique slug
        $originalSlug = $data['slug'];
        $counter = 1;
        while (Post::where('slug', $data['slug'])->exists()) {
            $data['slug'] = $originalSlug . '-' . $counter;
            $counter++;
        }

        $post = Post::create($data);

        // Handle featured image
        if ($request->hasFile('featured_image')) {
            $post->addMediaFromRequest('featured_image')
                ->toMediaCollection('featured');
        }

        // Attach categories and tags
        if ($request->has('categories')) {
            $post->terms()->attach($request->categories);
        }
        if ($request->has('tags')) {
            $post->terms()->attach($request->tags);
        }

        return redirect()->route('admin.posts.index')
            ->with('success', 'Post created successfully.');
    }

    /**
     * Display the specified post
     */
    public function show(Post $post)
    {
        $post->load(['creator', 'terms', 'media']);
        
        return view('admin.posts.show', compact('post'));
    }

    /**
     * Show the form for editing the specified post
     */
    public function edit(Post $post)
    {
        $post->load(['terms', 'media']);
        $categories = Term::where('type', 'category')->get();
        $tags = Term::where('type', 'tag')->get();

        return view('admin.posts.edit', compact('post', 'categories', 'tags'));
    }

    /**
     * Update the specified post
     */
    public function update(Request $request, Post $post)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'excerpt' => 'nullable|string|max:500',
            'type' => 'required|in:post,page,news,guide',
            'status' => 'required|in:draft,published,archived',
            'featured_image' => 'nullable|image|max:2048',
            'seo_title' => 'nullable|string|max:255',
            'seo_description' => 'nullable|string|max:255',
            'categories' => 'nullable|array',
            'tags' => 'nullable|array',
        ]);

        $data = $request->all();
        $data['updated_by'] = auth()->id();

        // Update slug if title changed
        if ($request->title !== $post->title) {
            $data['slug'] = Str::slug($request->title);
            
            // Ensure unique slug
            $originalSlug = $data['slug'];
            $counter = 1;
            while (Post::where('slug', $data['slug'])->where('id', '!=', $post->id)->exists()) {
                $data['slug'] = $originalSlug . '-' . $counter;
                $counter++;
            }
        }

        $post->update($data);

        // Handle featured image
        if ($request->hasFile('featured_image')) {
            $post->clearMediaCollection('featured');
            $post->addMediaFromRequest('featured_image')
                ->toMediaCollection('featured');
        }

        // Sync categories and tags
        $termIds = collect();
        if ($request->has('categories')) {
            $termIds = $termIds->merge($request->categories);
        }
        if ($request->has('tags')) {
            $termIds = $termIds->merge($request->tags);
        }
        $post->terms()->sync($termIds->toArray());

        return redirect()->route('admin.posts.index')
            ->with('success', 'Post updated successfully.');
    }

    /**
     * Remove the specified post
     */
    public function destroy(Post $post)
    {
        $post->terms()->detach();
        $post->clearMediaCollection();
        $post->delete();

        return redirect()->route('admin.posts.index')
            ->with('success', 'Post deleted successfully.');
    }
}