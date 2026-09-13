<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AI Admin Panel - UMKM</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f4f7f6;
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }
        .chat-container {
            width: 100%;
            max-width: 600px;
            background: white;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            overflow: hidden;
            display: flex;
            flex-direction: column;
            height: 80vh;
        }
        .header {
            background: #4a90e2;
            color: white;
            padding: 20px;
            text-align: center;
            font-size: 1.2rem;
            font-weight: bold;
        }
        .messages {
            flex-grow: 1;
            padding: 20px;
            overflow-y: auto;
            display: flex;
            flex-direction: column;
            gap: 15px;
            background: #fafafa;
        }
        .message {
            max-width: 80%;
            padding: 12px 16px;
            border-radius: 8px;
            line-height: 1.4;
            word-wrap: break-word;
        }
        .user-msg {
            background: #4a90e2;
            color: white;
            align-self: flex-end;
            border-bottom-right-radius: 0;
        }
        .ai-msg {
            background: #e1e4e8;
            color: #333;
            align-self: flex-start;
            border-bottom-left-radius: 0;
        }
        .input-area {
            display: flex;
            padding: 15px;
            background: white;
            border-top: 1px solid #ddd;
        }
        input[type="text"] {
            flex-grow: 1;
            padding: 12px;
            border: 1px solid #ccc;
            border-radius: 6px;
            outline: none;
            font-size: 1rem;
        }
        input[type="text"]:focus {
            border-color: #4a90e2;
        }
        button {
            padding: 12px 20px;
            margin-left: 10px;
            background: #4a90e2;
            color: white;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-size: 1rem;
            font-weight: bold;
        }
        button:hover {
            background: #357abd;
        }
        button:disabled {
            background: #9abce3;
            cursor: not-allowed;
        }
        .loading {
            align-self: flex-start;
            color: #777;
            font-style: italic;
            font-size: 0.9rem;
            display: none;
        }
    </style>
</head>
<body>

<div class="chat-container">
    <div class="header">
        👨‍💼 AI Admin Assistant
    </div>
    
    <div class="messages" id="messages-box">
        <div class="message ai-msg">
            Halo! Saya asisten AI Admin Panel Anda. Anda bisa mengetik perintah seperti: <br><br>
            - "Tambahkan produk Kopi Susu harganya 15000 stok 50"<br>
            - "Tampilkan daftar produk"<br>
            - "Ubah status order ID 1 jadi diproses"
        </div>
        <div class="loading" id="loading-indicator">AI sedang mengetik...</div>
    </div>
    
    <div class="input-area">
        <!-- Token CSRF penting di Laravel untuk keamanan setiap POST request -->
        <input type="hidden" id="csrf-token" value="{{ csrf_token() }}">
        <input type="text" id="user-input" placeholder="Ketik perintah Anda di sini..." autocomplete="off">
        <button id="send-btn" onclick="sendMessage()">Kirim</button>
    </div>
</div>

<script>
    const messagesBox = document.getElementById('messages-box');
    const userInput = document.getElementById('user-input');
    const sendBtn = document.getElementById('send-btn');
    const loadingIndicator = document.getElementById('loading-indicator');

    // Menangani penekanan tombol Enter
    userInput.addEventListener("keypress", function(event) {
        if (event.key === "Enter") {
            event.preventDefault();
            sendMessage();
        }
    });

    async function sendMessage() {
        const messageText = userInput.value.trim();
        if (!messageText) return;

        // Tampilkan pesan user ke layar
        addMessage(messageText, 'user-msg');
        
        // Bersihkan input dan matikan sementara
        userInput.value = '';
        toggleInput(false);

        try {
            // Gunakan fetch() API bawaan browser untuk mengirim request POST
            const response = await fetch('/api/agent/chat', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    // Untuk request web, ini bisa dihiraukan karena API route biasanya tidak butuh CSRF,
                    // Tapi sebagai best practice jika dipindah ke web route, kirimkan CSRF token
                },
                body: JSON.stringify({ message: messageText })
            });

            const data = await response.json();

            if (response.ok) {
                // Tampilkan pesan balasan AI
                addMessage(data.reply, 'ai-msg');
            } else {
                addMessage("Error: " + (data.error || "Gagal menghubungi server."), 'ai-msg');
            }
        } catch (error) {
            console.error("Fetch Error:", error);
            addMessage("Koneksi gagal. Silakan periksa jaringan dan server Laravel Anda.", 'ai-msg');
        } finally {
            // Nyalakan kembali input
            toggleInput(true);
        }
    }

    // Fungsi utilitas untuk menambahkan balon pesan ke dalam DOM
    function addMessage(text, className) {
        const msgDiv = document.createElement('div');
        msgDiv.className = `message ${className}`;
        
        // Format baris baru (\n) menjadi <br>
        msgDiv.innerHTML = text.replace(/\n/g, '<br>');
        
        // Sisipkan sebelum loading indicator agar loading selalu di bawah
        messagesBox.insertBefore(msgDiv, loadingIndicator);
        
        // Scroll otomatis ke bawah
        messagesBox.scrollTop = messagesBox.scrollHeight;
    }

    function toggleInput(enable) {
        userInput.disabled = !enable;
        sendBtn.disabled = !enable;
        loadingIndicator.style.display = enable ? 'none' : 'block';
        if (enable) userInput.focus();
        
        messagesBox.scrollTop = messagesBox.scrollHeight;
    }
</script>

</body>
</html>
