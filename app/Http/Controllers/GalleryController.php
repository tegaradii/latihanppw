<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Post;

class GalleryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $data = array(
            'id' => "posts",
            'menu' => 'Gallery',
            'galleries' => Post::where('picture', '!=', '')->whereNotNull('picture')->orderBy('created_at', 'desc')->paginate(20),
        );
        return view('gallery.index')->with($data);
    }

    public function apiPagination(Request $request)
    {
        // Ambil parameter pagination (default 10 per halaman)
        $perPage = $request->input('per_page', 10);

        // Fetch data dengan pagination
        $posts = Post::paginate($perPage);

        // Struktur respons JSON
        return response()->json([
            'message' => 'Galleries processed successfully',
            'success' => true,
            'data' => [
                'galleries' => $posts->items(),
                'pagination' => [
                    'current_page' => $posts->currentPage(),
                    'total_pages' => $posts->lastPage(),
                    'total_items' => $posts->total(),
                    'per_page' => $posts->perPage(),
                ]
            ]
        ]);
    }

    public function api()
    {
        $data_post_bergambar = Post::where('picture', '!=', '')->whereNotNull('picture')->orderBy('created_at', 'desc')->get();

        // Mereturn respons dalam format JSON
        return response()->json([
            'success' => true,
            'message' => "Berhasil mendapatkan semua data gallery",
            'data' => $data_post_bergambar,
        ], 200);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('gallery.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|max:255',
            'description' => 'required',
            'picture' => 'required|image|max:1999'
        ]);
        if ($request->hasFile('picture')) {
            $filenameWithExt = $request->file('picture')->getClientOriginalName();
            $filenameWithoutExt = pathinfo($filenameWithExt, PATHINFO_FILENAME);
            $extension = $request->file('picture')->getClientOriginalExtension();
            $filenameSimpan = $filenameWithoutExt . '_' . time() . '.' . $extension;

            $path = $request->file('picture')->storeAs('posts_image', $filenameSimpan);
        } else {
            $path = 'noimage.png';
        }

        Post::create([
            'title' => $request->title,
            'description' => $request->description,
            'picture' => $path,
        ]);
        return redirect()->route('gallery.index')->with('success', 'Berhasil menambahkan data gallery baru');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
