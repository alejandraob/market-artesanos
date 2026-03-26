<?php

namespace App\Http\Controllers;

use App\Models\Artisan;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ArtisanController extends Controller
{
    public function index()
    {
        return response()->json(
            Artisan::with(['user:id,name,email,avatar', 'products:id,artisan_id,name,images', 'categories:id,name'])
                ->where('is_active', true)
                ->get()
        );
    }

    public function all()
    {
        return response()->json(
            Artisan::with(['user:id,name,email,avatar,phone', 'categories:id,name'])
                ->withCount('products')
                ->orderByDesc('created_at')
                ->get()
        );
    }

    public function show($id)
    {
        return response()->json(
            Artisan::with(['user:id,name,email,avatar', 'products', 'categories:id,name'])->findOrFail($id)
        );
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'phone' => 'nullable|string|max:50',
            'specialty' => 'required|string|max:255',
            'bio' => 'nullable|string',
            'location' => 'nullable|string|max:255',
            'photo' => 'nullable|image|mimes:jpeg,jpg,png,webp|max:2048',
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make(Str::random(16)),
            'role' => 'cliente',
            'phone' => $request->phone,
        ]);

        $photoPath = null;
        if ($request->hasFile('photo')) {
            $photoPath = $request->file('photo')->store('artisans', 'public');
        }

        $artisan = Artisan::create([
            'user_id' => $user->id,
            'specialty' => $request->specialty,
            'bio' => $request->bio,
            'location' => $request->location,
            'photo' => $photoPath,
            'is_active' => true,
        ]);

        if ($request->has('category_ids')) {
            $artisan->categories()->sync(json_decode($request->category_ids, true) ?? []);
        }

        return response()->json($artisan->load(['user:id,name,email,avatar,phone', 'categories:id,name']), 201);
    }

    public function update(Request $request, Artisan $artisan)
    {
        $request->validate([
            'name' => 'sometimes|string|max:255',
            'email' => 'sometimes|string|email|max:255|unique:users,email,' . $artisan->user_id,
            'phone' => 'nullable|string|max:50',
            'specialty' => 'sometimes|string|max:255',
            'bio' => 'nullable|string',
            'location' => 'nullable|string|max:255',
            'photo' => 'nullable|image|mimes:jpeg,jpg,png,webp|max:2048',
            'is_active' => 'sometimes|boolean',
        ]);

        if ($request->hasAny(['name', 'email', 'phone'])) {
            $artisan->user->update($request->only(['name', 'email', 'phone']));
        }

        if ($request->hasFile('photo')) {
            if ($artisan->photo) {
                Storage::disk('public')->delete($artisan->photo);
            }
            $artisan->photo = $request->file('photo')->store('artisans', 'public');
        }

        $artisan->fill($request->only(['specialty', 'bio', 'location', 'is_active']));
        $artisan->save();

        if ($request->has('category_ids')) {
            $artisan->categories()->sync(json_decode($request->category_ids, true) ?? []);
        }

        return response()->json($artisan->load(['user:id,name,email,avatar,phone', 'categories:id,name']));
    }

    public function destroy(Artisan $artisan)
    {
        if ($artisan->photo) {
            Storage::disk('public')->delete($artisan->photo);
        }
        $artisan->delete();
        return response()->json(null, 204);
    }
}
