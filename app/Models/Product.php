<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    // Menentukan kolom apa saja yang bisa diisi secara massal (mass assignment).
    // Ini penting agar kita bisa menyimpan data langsung menggunakan Product::create([...])
    protected $fillable = [
        'name',
        'price',
        'stock',
    ];
}
