<?php

namespace App\Http\Controllers;

use App\Services\GeminiService;
use Illuminate\Http\Request;

class AgentController extends Controller
{
    protected $geminiService;

    // Dependency Injection: Laravel secara otomatis meng-inject GeminiService
    public function __construct(GeminiService $geminiService)
    {
        $this->geminiService = $geminiService;
    }

    // Menerima input teks dari pengguna dan meneruskannya ke layanan AI
    public function chat(Request $request)
    {
        // Validasi input: pastikan ada 'message'
        $request->validate([
            'message' => 'required|string',
        ]);

        $userMessage = $request->input('message');

        try {
            // Panggil method generateResponse() dari service untuk memproses percakapan
            $response = $this->geminiService->generateResponse($userMessage);
            
            // Kembalikan jawaban ke frontend
            return response()->json([
                'reply' => $response
            ]);
        } catch (\Exception $e) {
            // Jika terjadi error, kembalikan respons error 500
            return response()->json([
                'error' => 'Gagal terhubung ke AI: ' . $e->getMessage()
            ], 500);
        }
    }
}
