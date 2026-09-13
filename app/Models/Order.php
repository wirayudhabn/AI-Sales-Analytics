<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    // Kolom untuk tabel orders
    protected $fillable = [
        'product_id',
        'customer_name',
        'status', // Contoh: pending, processed, completed
    ];

    // Relasi: Setiap Order memiliki satu Product.
    // Membantu untuk memanggil relasi: $order->product->name
    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
