<?php

namespace App\Services;

use App\Models\Product;
use App\Models\Order;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GeminiService
{
    // URL Endpoint API Gemini. Kita menggunakan model gemini-1.5-flash untuk kecepatan dan support function calling.
    protected $baseUrl = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-flash:generateContent';

    /**
     * Fungsi utama untuk memproses chat dari user.
     */
    public function generateResponse($userMessage)
    {
        $apiKey = env('GEMINI_API_KEY');

        // Menyusun payload (data yang dikirim) sesuai standar Gemini API untuk function calling
        $payload = [
            'contents' => [
                [
                    'role' => 'user',
                    'parts' => [
                        ['text' => $userMessage]
                    ]
                ]
            ],
            // Deklarasi fungsi (tools) yang bisa dipanggil oleh Gemini jika dibutuhkan
            'tools' => [
                [
                    'function_declarations' => $this->getFunctionDeclarations()
                ]
            ]
        ];

        // 1. Kirim pesan user ke Gemini
        $response = Http::withHeaders([
            'Content-Type' => 'application/json',
        ])->post($this->baseUrl . '?key=' . $apiKey, $payload);

        $responseData = $response->json();

        // Cek jika ada bagian response dari Gemini
        if (isset($responseData['candidates'][0]['content']['parts'])) {
            $parts = $responseData['candidates'][0]['content']['parts'];
            
            // Loop setiap part yang dikembalikan
            foreach ($parts as $part) {
                // 2. Jika Gemini memutuskan untuk memanggil fungsi (Function Calling)
                if (isset($part['functionCall'])) {
                    $functionCall = $part['functionCall'];
                    $functionName = $functionCall['name'];
                    $args = $functionCall['args'] ?? [];

                    // 3. Eksekusi fungsi lokal (di Laravel/Database)
                    $functionResult = $this->executeFunction($functionName, $args);

                    // 4. Kirim kembali hasil eksekusi ke Gemini agar ia bisa merangkai jawaban akhir (natural language)
                    return $this->sendFunctionResultToGemini($functionName, $functionResult, $userMessage);
                }
                
                // Jika tidak ada pemanggilan fungsi, langsung kembalikan teks balasan biasa
                if (isset($part['text'])) {
                    return $part['text'];
                }
            }
        }

        return "Maaf, saya tidak mengerti atau ada kesalahan sistem.";
    }

    /**
     * Mendeklarasikan fungsi-fungsi yang dimengerti oleh Gemini.
     */
    private function getFunctionDeclarations()
    {
        return [
            [
                'name' => 'create_product',
                'description' => 'Tambahkan produk baru ke dalam database.',
                'parameters' => [
                    'type' => 'OBJECT',
                    'properties' => [
                        'name' => ['type' => 'STRING', 'description' => 'Nama produk'],
                        'price' => ['type' => 'NUMBER', 'description' => 'Harga produk dalam nominal angka'],
                        'stock' => ['type' => 'INTEGER', 'description' => 'Jumlah stok awal produk'],
                    ],
                    'required' => ['name', 'price', 'stock']
                ]
            ],
            [
                'name' => 'get_products',
                'description' => 'Ambil daftar produk yang ada di sistem, opsional bisa di filter.',
                'parameters' => [
                    'type' => 'OBJECT',
                    'properties' => [
                        'filter_name' => ['type' => 'STRING', 'description' => 'Kata kunci nama produk yang dicari']
                    ]
                ]
            ],
            [
                'name' => 'update_order_status',
                'description' => 'Perbarui status sebuah pesanan (order).',
                'parameters' => [
                    'type' => 'OBJECT',
                    'properties' => [
                        'order_id' => ['type' => 'INTEGER', 'description' => 'ID dari order yang ingin diubah'],
                        'status' => ['type' => 'STRING', 'description' => 'Status baru (contoh: diproses, selesai)']
                    ],
                    'required' => ['order_id', 'status']
                ]
            ],
            [
                'name' => 'get_order_summary',
                'description' => 'Dapatkan ringkasan semua pesanan saat ini.',
                'parameters' => [
                    'type' => 'OBJECT',
                    'properties' => [
                        'period' => ['type' => 'STRING', 'description' => 'Periode ringkasan, misal hari ini, minggu ini (opsional)']
                    ]
                ]
            ]
        ];
    }

    /**
     * Menjalankan query ke database sesuai fungsi yang dipanggil Gemini.
     */
    private function executeFunction($name, $args)
    {
        try {
            switch ($name) {
                case 'create_product':
                    $product = Product::create([
                        'name' => $args['name'],
                        'price' => $args['price'],
                        'stock' => $args['stock'],
                    ]);
                    return ["status" => "success", "message" => "Produk {$product->name} berhasil ditambahkan dengan ID {$product->id}."];

                case 'get_products':
                    $query = Product::query();
                    if (isset($args['filter_name'])) {
                        $query->where('name', 'like', '%' . $args['filter_name'] . '%');
                    }
                    $products = $query->get();
                    return ["status" => "success", "data" => $products->toArray()];

                case 'update_order_status':
                    $order = Order::find($args['order_id']);
                    if (!$order) {
                        return ["status" => "error", "message" => "Order dengan ID {$args['order_id']} tidak ditemukan."];
                    }
                    $order->update(['status' => $args['status']]);
                    return ["status" => "success", "message" => "Status order ID {$order->id} berhasil diubah menjadi {$args['status']}."];

                case 'get_order_summary':
                    $totalOrders = Order::count();
                    $pendingOrders = Order::where('status', 'pending')->count();
                    $completedOrders = Order::where('status', 'completed')->count();
                    return [
                        "status" => "success", 
                        "data" => [
                            "total" => $totalOrders, 
                            "pending" => $pendingOrders, 
                            "completed" => $completedOrders
                        ]
                    ];

                default:
                    return ["status" => "error", "message" => "Fungsi tidak dikenali."];
            }
        } catch (\Exception $e) {
            return ["status" => "error", "message" => "Terjadi kesalahan pada database: " . $e->getMessage()];
        }
    }

    /**
     * Mengirim hasil eksekusi (seperti success/error message atau array data) 
     * kembali ke Gemini untuk diubah menjadi kalimat alami.
     */
    private function sendFunctionResultToGemini($functionName, $functionResult, $originalUserMessage)
    {
        $apiKey = env('GEMINI_API_KEY');

        // Buat history percakapan: Pesan user awal -> Pemanggilan Fungsi -> Hasil dari Fungsi (Local)
        $payload = [
            'contents' => [
                // 1. Pesan awal dari user
                [
                    'role' => 'user',
                    'parts' => [['text' => $originalUserMessage]]
                ],
                // 2. Pemanggilan fungsi oleh model (sebelumnya)
                [
                    'role' => 'model',
                    'parts' => [
                        [
                            'functionCall' => [
                                'name' => $functionName,
                                'args' => [] // Args tidak harus sama persis di history, tapi strukturnya dibutuhkan
                            ]
                        ]
                    ]
                ],
                // 3. Kita memberikan respons hasil fungsi kembali sebagai 'functionResponse'
                [
                    'role' => 'function',
                    'parts' => [
                        [
                            'functionResponse' => [
                                'name' => $functionName,
                                'response' => $functionResult
                            ]
                        ]
                    ]
                ]
            ]
        ];

        // Kirim request kedua ke Gemini
        $response = Http::withHeaders([
            'Content-Type' => 'application/json',
        ])->post($this->baseUrl . '?key=' . $apiKey, $payload);

        $responseData = $response->json();

        // Gemini sekarang akan menjawab dengan bahasa natural berdasarkan $functionResult yang kita berikan
        if (isset($responseData['candidates'][0]['content']['parts'][0]['text'])) {
            return $responseData['candidates'][0]['content']['parts'][0]['text'];
        }

        return "Fungsi berhasil dieksekusi, tapi gagal memproses balasan akhir dari AI.";
    }
}
