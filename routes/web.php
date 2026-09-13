<?php

use Illuminate\Support\Facades\Route;

// Tampilkan halaman chat (chat.blade.php) saat mengakses root URL (/)
Route::get('/', function () {
    return view('chat');
});
