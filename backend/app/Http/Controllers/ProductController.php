<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $query = Product::with(['category', 'artisan.user:id,name']);

        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        if ($request->has('featured')) {
            $query->where('is_featured', true);
        }

        if (!$request->has('include_inactive')) {
            $query->where('is_active', true);
        }

        return response()->json($query->paginate(15));
    }

    public function allForAdmin()
    {
        return response()->json(
            Product::with(['category:id,name', 'artisan.user:id,name'])
                ->orderByDesc('created_at')
                ->get()
        );
    }

    public function store(Request $request)
    {
        $request->validate([
            'category_id' => 'required|exists:categories,id',
            'artisan_id' => 'required|exists:artisans,id',
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'price' => 'required|numeric|min:0',
            'stock' => 'required|integer|min:0',
        ]);

        $data = $request->all();
        $data['slug'] = Str::slug($data['name']) . '-' . uniqid();

        if ($request->hasFile('image_files')) {
            $images = [];
            foreach ($request->file('image_files') as $file) {
                $images[] = $file->store('products', 'public');
            }
            $data['images'] = $images;
        }

        $product = Product::create($data);

        return response()->json($product->load('category', 'artisan.user:id,name'), 201);
    }

    public function show($identifier)
    {
        $query = Product::with(['category', 'artisan.user:id,name']);
        if (is_numeric($identifier)) {
            $product = $query->findOrFail($identifier);
        } else {
            $product = $query->where('slug', $identifier)->firstOrFail();
        }
        return response()->json($product);
    }

    public function update(Request $request, Product $product)
    {
        $user = $request->user();
        if ($user->role === 'cliente') {
            return response()->json(['message' => 'No autorizado'], 403);
        }

        $data = $request->all();
        if (isset($data['name'])) {
            $data['slug'] = Str::slug($data['name']) . '-' . uniqid();
        }

        if ($request->hasFile('image_files')) {
            $images = $product->images ?? [];
            foreach ($request->file('image_files') as $file) {
                $images[] = $file->store('products', 'public');
            }
            $data['images'] = $images;
        }

        $product->update($data);

        return response()->json($product->load('category', 'artisan.user:id,name'));
    }

    public function toggleActive(Request $request, Product $product)
    {
        $product->update(['is_active' => !$product->is_active]);
        return response()->json($product);
    }

    public function destroy(Product $product)
    {
        $product->delete();
        return response()->json(null, 204);
    }
}
