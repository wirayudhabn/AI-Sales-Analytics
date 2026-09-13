<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\AgentController;

// Rute untuk AI Agent
Route::post('/agent/chat', [AgentController::class, 'chat']);

// Rute CRUD Produk
Route::get('/products', [ProductController::class, 'index']);
Route::post('/products', [ProductController::class, 'store']);
Route::patch('/products/{id}', [ProductController::class, 'update']);
Route::delete('/products/{id}', [ProductController::class, 'destroy']);

// Rute CRUD Pesanan
Route::get('/orders', [OrderController::class, 'index']);
Route::patch('/orders/{id}/status', [OrderController::class, 'updateStatus']);

// (Opsional) Jika perlu CRUD Customer, tambahkan di sini
// Route::get('/customers', [CustomerController::class, 'index']);
