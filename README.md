# AI-Agent-Admin-Panel (Scaffold Project)

Proyek ini adalah kerangka (scaffold) dasar untuk aplikasi admin panel berbasis percakapan (chat) menggunakan Laravel dan Gemini AI Function Calling. Proyek ini sangat cocok untuk pembelajaran integrasi AI tingkat pemula.

## 🚀 Fitur Utama
- **Backend:** Laravel 10/11
- **Database:** SQLite (Tanpa perlu install MySQL)
- **Frontend:** Single-page app (Blade view + Vanilla JavaScript `fetch()`)
- **AI Integrasi:** Gemini 1.5 Flash melalui Laravel HTTP Facade (tanpa package tambahan).
- **Alur Function Calling:** AI dapat mengeksekusi operasi CRUD database (tambah produk, ubah status) berdasarkan perintah *natural language* pengguna.

---

## 🛠️ Persiapan dan Setup

### 1. Dapatkan API Key Gemini
1. Buka [Google AI Studio](https://aistudio.google.com/app/apikey).
2. Buat API Key baru dan salin kunci tersebut.

### 2. Setup Proyek
1. Buka file `.env` di root direktori.
2. Cari baris `GEMINI_API_KEY` (biasanya di bawah pengaturan database).
3. Ganti nilainya dengan kunci yang baru saja Anda salin:
   ```env
   GEMINI_API_KEY=AIzaSy...
   ```
4. Pastikan `DB_CONNECTION=sqlite` ada di file `.env`. (Sudah dikonfigurasi).
5. Buat file database SQLite yang dibutuhkan jika belum ada:
   ```bash
   touch database/database.sqlite
   ```
   *(Untuk Windows PowerShell: `New-Item database/database.sqlite` atau cukup jalankan migrate, Laravel terbaru akan otomatis menawarkannya)*
6. Jalankan instalasi dependensi jika belum (Jika menggunakan hasil *clone*):
   ```bash
   composer install
   ```
7. Jalankan Migrasi Database untuk membuat tabel:
   ```bash
   php artisan migrate
   ```

### 3. Jalankan Aplikasi
Gunakan perintah artisan bawaan Laravel untuk menjalankan server:
```bash
php artisan serve
```
Akses aplikasi melalui browser: [http://localhost:8000](http://localhost:8000)

---

## 📖 Penjelasan Alur (Workflow)

Cara kerja integrasi ini secara rinci:

1. **User Mengetik Pesan (Blade)**:
   Di `resources/views/chat.blade.php`, user mengetik perintah (misal: *"Tampilkan daftar produk"*). JavaScript akan menggunakan `fetch()` untuk mengirim request `POST` ke endpoint `/api/agent/chat`.

2. **Controller Menerima Request (AgentController)**:
   Request diterima oleh `AgentController`. Controller akan memvalidasi pesan dan meneruskannya ke `GeminiService::generateResponse($message)`.

3. **Laravel Memanggil Gemini (GeminiService - Tahap 1)**:
   Di dalam service, Laravel mengirim *HTTP POST* ke endpoint Google API. Bersama pesan tersebut, Laravel mengirimkan definisi `function_declarations` yang memberitahu Gemini tentang "alat" apa saja yang dimilikinya (misal: `get_products`, `create_product`).

4. **Gemini Memilih Fungsi (Function Calling)**:
   Gemini AI membaca pesan pengguna. Karena pesan mengatakan *"Tampilkan daftar produk"*, AI secara cerdas memutuskan bahwa ia harus memanggil fungsi `get_products` daripada sekadar menjawab dengan teks. AI membalas ke Laravel dengan instruksi pemanggilan fungsi.

5. **Laravel Mengeksekusi Operasi Database**:
   Di blok `if (isset($part['functionCall']))` pada `GeminiService`, aplikasi mendeteksi perintah tersebut. Laravel kemudian menjalankan query database lokal (`Product::all()`) dan mendapatkan hasilnya.

6. **Mengirim Hasil Balik ke Gemini (Tahap 2)**:
   Laravel mengirim satu request *HTTP POST* lagi ke Gemini. Kali ini berisi histori pesan awal, instruksi panggil fungsi, dan **hasil dari database** (dalam format JSON).

7. **Gemini Merangkai Jawaban Alami**:
   Dengan berbekal data dari database, Gemini sekarang merangkai jawaban bahasa manusia (contoh: *"Berikut adalah daftar produk Anda: Kopi Susu (Stok 50)..."*). Jawaban ini dikembalikan ke Laravel.

8. **Jawaban Ditampilkan**:
   Controller meneruskan teks dari Gemini ke frontend. JavaScript menampilkan balasan AI di layar percakapan.

---

Semoga dokumentasi ini membantu tim kelompok kalian memahami *Function Calling* di Laravel! Silakan periksa komentar di setiap file (`GeminiService.php`, Controller, Model) untuk pemahaman mendalam tentang setiap baris kodenya.
