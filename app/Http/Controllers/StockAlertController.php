<?php

namespace App\Http\Controllers;

use App\Models\StockAlert;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class StockAlertController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'product_id' => 'required|exists:products,id',
            'email' => 'required|email|max:255',
        ]);

        StockAlert::firstOrCreate(
            ['product_id' => $data['product_id'], 'email' => $data['email']],
        );

        return response()->json(['success' => true]);
    }
}
