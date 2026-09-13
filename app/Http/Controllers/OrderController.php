<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    // Mengambil semua order
    public function index()
    {
        // Menyertakan relasi product menggunakan with('product')
        $orders = Order::with('product')->get();
        return response()->json($orders);
    }

    // Memperbarui status pesanan
    public function updateStatus(Request $request, $id)
    {
        $validated = $request->validate([
            'status' => 'required|string',
        ]);

        $order = Order::findOrFail($id);
        $order->update(['status' => $validated['status']]);

        return response()->json($order);
    }
}
