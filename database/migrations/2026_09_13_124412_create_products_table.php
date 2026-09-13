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
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            // Nama produk, bertipe string (varchar)
            $table->string('name');
            // Harga produk, menggunakan decimal.
            // 10 digit total, 2 digit desimal (misal: 99999999.99)
            $table->decimal('price', 10, 2);
            // Stok produk, bertipe integer dengan nilai default 0
            $table->integer('stock')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
