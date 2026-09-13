<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            // Relasi ke tabel products (product_id).
            // constrained() otomatis merujuk ke id di tabel products
            // cascadeOnDelete() artinya jika produk dihapus, maka order ini juga terhapus.
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            // Nama pelanggan yang memesan. (Bisa juga berelasi ke tabel customers, tapi disederhanakan sesuai request).
            $table->string('customer_name');
            // Status pesanan, nilai default 'pending'
            $table->string('status')->default('pending');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
