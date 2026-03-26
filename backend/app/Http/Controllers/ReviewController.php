<?php

namespace App\Http\Controllers;

use App\Models\Review;
use Illuminate\Http\Request;

class ReviewController extends Controller
{
    public function index($productId)
    {
        $reviews = Review::where('product_id', $productId)
            ->with('user:id,name')
            ->orderByDesc('created_at')
            ->get();

        $avg = $reviews->avg('rating');

        return response()->json([
            'reviews' => $reviews,
            'average' => $avg ? round($avg, 1) : null,
            'count' => $reviews->count(),
        ]);
    }

    public function store(Request $request, $productId)
    {
        $request->validate([
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'nullable|string|max:1000',
        ]);

        $existing = Review::where('user_id', $request->user()->id)
            ->where('product_id', $productId)
            ->first();

        if ($existing) {
            return response()->json(['message' => 'Ya dejaste una resena para este producto.'], 422);
        }

        $review = Review::create([
            'user_id' => $request->user()->id,
            'product_id' => $productId,
            'rating' => $request->rating,
            'comment' => $request->comment,
        ]);

        return response()->json($review->load('user:id,name'), 201);
    }

    public function destroy(Request $request, $id)
    {
        $review = Review::findOrFail($id);

        if ($review->user_id !== $request->user()->id && !in_array($request->user()->role, ['admin', 'presidente'])) {
            return response()->json(['message' => 'No autorizado.'], 403);
        }

        $review->delete();
        return response()->json(null, 204);
    }
}
