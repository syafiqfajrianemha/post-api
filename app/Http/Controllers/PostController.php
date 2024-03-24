<?php

namespace App\Http\Controllers;

use App\Http\Resources\PostResource;
use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class PostController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $posts = Post::latest()->paginate(5);

        return new PostResource(true, "Posts fetched successfully", $posts);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'image'     => 'required|image|mimes:png,jpg,jpeg,gif,svg|max:2048',
            'title'     => 'required',
            'content'   => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()
            ], 422);
        }

        if ($request->hasFile('image')) {
            $image = $request->file('image');

            if ($image->isValid()) {
                $imageName = time() . '.' . $image->getClientOriginalExtension();
                $image->move('storage/posts', $imageName);
            }
        }

        $post = Post::create([
            'image'     => $imageName,
            'title'     => $request->title,
            'content'   => $request->content
        ]);

        return new PostResource(true, 'Post successfully added', $post);
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $post = Post::findOrFail($id);

        return new PostResource(true, 'Post fetched successfully', $post);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Post $post)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'image'     => 'image|mimes:png,jpg,jpeg,gif,svg|max:2048',
            'title'     => 'required',
            'content'   => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()
            ], 422);
        }

        $post = Post::findOrFail($id);

        if ($request->hasFile('image')) {
            $image = $request->file('image');

            Storage::delete('public/posts/' . basename($post->image));

            if ($image->isValid()) {
                $imageName = time() . '.' . $image->getClientOriginalExtension();
                $image->move('storage/posts', $imageName);
            }

            $post->update([
                'image'     => $imageName,
                'title'     => $request->title,
                'content'   => $request->content
            ]);
        } else {
            $post->update([
                'title'     => $request->title,
                'content'   => $request->content
            ]);
        }

        return new PostResource(true, 'Post successfully updated', $post);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $post = Post::findOrFail($id);

        Storage::delete('public/posts/' . basename($post->image));
        $post->delete();

        return new PostResource(true, 'Post successfully deleted');
    }
}
