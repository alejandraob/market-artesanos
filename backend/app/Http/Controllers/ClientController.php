<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

class ClientController extends Controller
{
    public function index()
    {
        return response()->json(
            User::where('role', 'cliente')
                ->whereDoesntHave('artisan')
                ->withCount('orders')
                ->orderByDesc('created_at')
                ->get(['id', 'name', 'email', 'phone', 'created_at'])
        );
    }

    public function show($id)
    {
        $client = User::where('role', 'cliente')
            ->whereDoesntHave('artisan')
            ->withCount('orders')
            ->findOrFail($id);

        $client->load('orders.items.product:id,name,images');

        return response()->json($client);
    }
}
