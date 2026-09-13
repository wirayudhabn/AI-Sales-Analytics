<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    // Mengambil semua produk
    public function index()
    {
        $products = Product::all();
        return response()->json($products);
    }

    // Menyimpan produk baru (digunakan oleh sistem atau Gemini AI nantinya)
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string',
            'price' => 'required|numeric',
            'stock' => 'required|integer',
        ]);

        $product = Product::create($validated);
        
        return response()->json($product, 201);
    }

    // Mengupdate produk (opsional, jika diperlukan)
    public function update(Request $request, $id)
    {
        $product = Product::findOrFail($id);
        $product->update($request->all());
        return response()->json($product);
    }

    // Menghapus produk (opsional)
    public function destroy($id)
    {
        $product = Product::findOrFail($id);
        $product->delete();
        return response()->json(null, 204);
    }
}
