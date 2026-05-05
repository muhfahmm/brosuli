<?php
require_once '../config.php';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pembayaran Berhasil - Brosuli Bakery</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;700&family=Playfair+Display:wght@700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body { font-family: 'Outfit', sans-serif; background-color: #FFFBEB; }
        .success-card { background: rgba(255, 255, 255, 0.8); backdrop-filter: blur(12px); border-radius: 2rem; border: 1px solid rgba(255,255,255,0.3); }
    </style>
</head>
<body class="min-h-screen flex items-center justify-center p-6">
    <div class="max-w-md w-full text-center">
        <div class="success-card p-10 shadow-2xl animate-fade-in">
            <div class="w-24 h-24 bg-green-100 text-green-500 rounded-full flex items-center justify-center mx-auto mb-8 animate-bounce">
                <i class="fas fa-check text-4xl"></i>
            </div>
            <h1 class="text-3xl font-serif font-bold text-primary mb-4">Pembayaran Berhasil!</h1>
            <p class="text-gray-500 mb-8 leading-relaxed">Terima kasih telah berbelanja di Brosuli Bakery. Pesanan Anda sedang kami proses dan akan segera dikirim.</p>
            
            <div class="space-y-4">
                <a href="index.php" class="block w-full bg-primary text-white py-4 rounded-2xl font-bold hover:bg-secondary transition-all shadow-xl">
                    Kembali ke Beranda
                </a>
                <button onclick="window.print()" class="text-gray-400 hover:text-primary transition-colors text-sm font-medium">
                    Cetak Bukti Pembayaran
                </button>
            </div>
        </div>
        
        <p class="mt-8 text-gray-400 text-sm italic">"Pesan hari ini, Panggang hari ini"</p>
    </div>
</body>
</html>
