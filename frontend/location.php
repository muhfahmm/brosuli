<?php
session_start();
require_once '../db/db.php';
require_once '../config.php';

// Fetch branches from database
$branch_stmt = $pdo->query("SELECT * FROM branches ORDER BY name ASC");
$branches_db = $branch_stmt->fetchAll();

// Get selected branch from session
$selected_branch_id = $_SESSION['user_branch_id'] ?? null;
$selected_branch_name = 'Pilih Cabang';

if ($selected_branch_id) {
    foreach ($branches_db as $branch) {
        if ($branch['id'] == $selected_branch_id) {
            $selected_branch_name = $branch['name'];
            break;
        }
    }
}

// Keep the map URLs but map them to the DB branches if possible, 
// or just use the DB branches and add map URLs if they were in the original array.
// For now, I'll merge them.
$branches = [];
foreach ($branches_db as $b_db) {
    $map_url = '';
    // Find matching map URL from original list
    $original_maps = [
        'Brosuli Boyolali (Utama)' => 'https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3955.423!2d110.5997076!3d-7.5242328!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x2e7a6f89085a6635%3A0x8c6cde5506ae2013!2sOleh%20Oleh%20Khas%20Boyolali%20%7C%20Brownies%20Susu%20Boyolali%20%7C%20Brosuli%20Boyolali!5e0',
        'Brosuli Mojosongo' => 'https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3955.456!2d110.638!3d-7.523!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x2e7a6f6990498a4d%3A0x7d6a598716b6070a!2sBrosuli%20Mojosongo!5e0',
        'Brosuli Kartasura' => 'https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3955.024!2d110.738!3d-7.572!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x2e7a1500131098fb%3A0x2fa2dc22888056d4!2sBrownies%20Susu%20Boyolali%20%7C%20Brosuli%20Kartasura!5e0',
        'Brosuli Baki' => 'https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3954.727!2d110.782!3d-7.604!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x2e7a1552de0c535f%3A0xf2d01b628e513881!2sBrownies%20Susu%20Boyolali%20%7C%20Brosuli%20Baki!5e0',
        'Brosuli Mojolaban' => 'https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3954.673!2d110.867!3d-7.604!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x2e7a1760df291679%3A0xbe2ace869208571a!2sBrownies%20Susu%20Boyolali%20%7C%20Brosuli%20Mojolaban!5e0',
        'Brosuli Colomadu' => 'https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3955.3!2d110.76!3d-7.531!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x2e7a15f43fe33869%3A0xf50501cf0c8427b1!2sBrownies%20Susu%20Boyolali%20%7C%20Brosuli%20Colomadu!5e0',
        'Brosuli Pedan' => 'https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3953.197!2d110.703!3d-7.694!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x2e7a41d6ab1417b7%3A0x44557a83949a2df8!2sBrownies%20Susu%20Boyolali%20%7C%20Brosuli%20Pedan!5e0',
        'Brosuli Jatinom' => 'https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3953.5!2d110.65!3d-7.65!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x2e7a43ec985b0d01%3A0x5d9e5d4a6f8b9e6f!2sBrosuli%20Jatinom!5e0'
    ];
    $branches[] = [
        'id' => $b_db['id'],
        'name' => $b_db['name'],
        'address' => $b_db['address'],
        'map_url' => $original_maps[$b_db['name']] ?? ''
    ];
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lokasi Cabang - Brosuli Bakery</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;700&family=Playfair+Display:ital,wght@0,700;1,700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: '#4A2C2A',
                        secondary: '#D97706',
                        accent: '#F59E0B',
                        cream: '#FFFBEB',
                    },
                    fontFamily: {
                        sans: ['Outfit', 'sans-serif'],
                        serif: ['Playfair Display', 'serif'],
                    }
                }
            }
        }
    </script>
    <style>
        .glass {
            background: rgba(255, 255, 255, 0.7);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
        }
        .zigzag-card:nth-child(even) .card-inner {
            flex-direction: row-reverse;
        }
    </style>
</head>
<body class="bg-cream text-primary overflow-x-hidden">

    <!-- Navbar -->
    <nav class="fixed top-0 left-0 right-0 z-50 glass border-b border-white/20">
        <div class="max-w-7xl mx-auto px-6 py-4 flex justify-between items-center">
            <a href="../index.php" class="flex items-center space-x-2">
                <div class="w-10 h-10 bg-primary rounded-full flex items-center justify-center text-white font-serif text-xl italic shadow-lg">B</div>
                <span class="text-2xl font-serif font-bold tracking-tight">Brosuli</span>
            </a>
            <div class="flex items-center space-x-8 font-medium">
                <a href="../index.php" class="hover:text-secondary transition-colors">Beranda</a>
                <a href="catalog.php" class="hover:text-secondary transition-colors">Menu Kami</a>
                <button onclick="document.getElementById('branchModal').classList.remove('hidden')" class="flex items-center space-x-2 text-secondary hover:text-primary transition-all bg-secondary/10 px-4 py-1.5 rounded-full border border-secondary/20">
                    <i class="fas fa-map-marker-alt"></i>
                    <span class="text-sm font-bold"><?php echo htmlspecialchars($selected_branch_name); ?></span>
                </button>
            </div>
            <div class="flex items-center space-x-4">
                <button onclick="toggleCart()" class="relative p-2 text-primary hover:text-secondary transition-colors">
                    <i class="fas fa-shopping-bag text-2xl"></i>
                    <span class="cart-count absolute -top-1 -right-1 bg-secondary text-white text-[10px] w-5 h-5 rounded-full flex items-center justify-center font-bold border-2 border-white">0</span>
                </button>
                <button onclick="location.href='catalog.php'" class="bg-primary text-white px-6 py-2 rounded-full font-semibold hover:bg-secondary transition-all shadow-md transform hover:scale-105 hidden sm:block">
                    Pesan Sekarang
                </button>
            </div>
        </div>
    </nav>

    <!-- Cart Sidebar -->
    <div id="cart-sidebar" class="fixed inset-y-0 right-0 w-full sm:w-[450px] bg-white z-[100] shadow-2xl transform translate-x-full transition-transform duration-500 flex flex-col">
        <div class="p-8 bg-primary text-white flex justify-between items-center">
            <div>
                <h3 class="text-xl font-bold font-serif">Keranjang Saya</h3>
                <p class="text-xs opacity-70"><span class="cart-count">0</span> Produk Terpilih</p>
            </div>
            <button onclick="document.getElementById('cart-sidebar').classList.add('translate-x-full')" class="w-10 h-10 rounded-full bg-white/10 flex items-center justify-center hover:bg-white/20 transition-all">
                <i class="fas fa-times text-xl"></i>
            </button>
        </div>
        
        <div id="cart-items-list" class="flex-1 overflow-y-auto p-6 space-y-6">
            <!-- Items populated by JS -->
        </div>


        
        <div class="p-8 bg-white border-t border-gray-100 space-y-3">
            <div class="flex justify-between items-center mb-4">
                <span class="text-gray-500 font-medium">Total Pembayaran:</span>
                <span id="cart-total-price" class="text-2xl font-bold text-primary">Rp 0</span>
            </div>
            <button onclick="payWithMidtrans()" class="w-full bg-primary text-white py-4 rounded-2xl font-bold text-lg hover:bg-secondary transition-all shadow-xl flex items-center justify-center space-x-3">
                <i class="fas fa-credit-card"></i>
                <span>Bayar Sekarang (Midtrans)</span>
            </button>
            <button id="wa-checkout-btn" onclick="checkoutWhatsApp()" class="w-full bg-[#25D366] text-white py-4 rounded-2xl font-bold text-lg hover:bg-[#20bd5c] transition-all shadow-xl shadow-green-100 flex items-center justify-center space-x-3">
                <i class="fab fa-whatsapp text-2xl"></i>
                <span>Pesan via WhatsApp</span>
            </button>
            <p class="text-[10px] text-center text-gray-400 mt-4 uppercase tracking-widest">Pesan hari ini, Panggang hari ini</p>
        </div>
    </div>
    <script src="js/cart.js?v=1.2" defer></script>

    <!-- Header Section -->
    <header class="pt-40 pb-20 text-center px-6">
        <div class="max-w-3xl mx-auto">
            <span class="text-secondary font-bold tracking-widest uppercase text-sm mb-4 block">Temukan Kami</span>
            <h1 class="text-5xl md:text-6xl font-serif font-bold text-primary mb-6">Jaringan Cabang Brosuli</h1>
            <p class="text-gray-500 text-lg leading-relaxed">Dari Boyolali hingga Klaten, kami hadir di 8 lokasi strategis untuk menyajikan kelezatan brownies susu terbaik untuk Anda.</p>
        </div>
    </header>

    <!-- Floating WhatsApp Button -->
    <a href="https://wa.me/62895327349264?text=Halo%20Brosuli%20Bakery%2C%20saya%20ingin%20bertanya..." target="_blank" class="fixed bottom-8 right-8 z-[100] bg-[#25D366] text-white w-16 h-16 rounded-full flex items-center justify-center shadow-2xl hover:scale-110 transition-all duration-300 group">
        <i class="fab fa-whatsapp text-3xl"></i>
    </a>
    
    <!-- Zig-Zag Branches Section -->
    <section class="max-w-7xl mx-auto px-6 pb-32 space-y-24">
        <?php foreach ($branches as $index => $branch): ?>
        <div class="zigzag-card">
            <div class="card-inner flex flex-col lg:flex-row items-center gap-12">
                <!-- Map Content -->
                <div class="w-full lg:w-1/2 h-[450px] rounded-[3rem] overflow-hidden shadow-2xl border-8 border-white transform hover:scale-[1.02] transition-transform duration-500">
                    <iframe 
                        src="<?php echo $branch['map_url']; ?>" 
                        width="100%" 
                        height="100%" 
                        style="border:0;" 
                        allowfullscreen="" 
                        loading="lazy" 
                        referrerpolicy="no-referrer-when-downgrade">
                    </iframe>
                </div>
                
                <!-- Info Content -->
                <div class="w-full lg:w-1/2 space-y-6 text-center lg:text-left px-4">
                    <div class="inline-block px-4 py-1 bg-secondary/10 text-secondary rounded-full text-xs font-bold uppercase tracking-widest mb-2">
                        Cabang #<?php echo $index + 1; ?>
                    </div>
                    <h2 class="text-3xl md:text-4xl font-serif font-bold text-primary">
                        <?php echo htmlspecialchars($branch['name']); ?>
                    </h2>
                    <div class="space-y-4">
                        <div class="flex items-start justify-center lg:justify-start space-x-3 text-gray-500">
                            <i class="fas fa-map-marker-alt mt-1 text-secondary text-lg"></i>
                            <p class="text-lg leading-relaxed"><?php echo htmlspecialchars($branch['address']); ?></p>
                        </div>
                        <div class="flex items-center justify-center lg:justify-start space-x-3 text-gray-500">
                            <i class="fas fa-clock text-secondary"></i>
                            <p class="font-semibold">07:00 - 20:00 (Buka Setiap Hari)</p>
                        </div>
                    </div>
                    <div class="pt-6 flex flex-col sm:flex-row gap-4 justify-center lg:justify-start">
                        <a href="https://wa.me/62895327349264?text=Halo%20Brosuli%20<?php echo urlencode($branch['name']); ?>%2C%20saya%20ingin%20bertanya..." 
                           target="_blank" 
                           class="px-8 py-4 bg-primary text-white rounded-2xl font-bold hover:bg-secondary transition-all flex items-center justify-center space-x-3 shadow-xl">
                            <i class="fab fa-whatsapp text-xl"></i>
                            <span>Hubungi Cabang</span>
                        </a>
                        <a href="https://www.google.com/maps/search/<?php echo urlencode($branch['name'] . ' ' . $branch['address']); ?>" 
                           target="_blank" 
                           class="px-8 py-4 bg-white text-primary border border-primary/10 rounded-2xl font-bold hover:bg-gray-50 transition-all flex items-center justify-center space-x-3 shadow-sm">
                            <i class="fas fa-directions text-xl"></i>
                            <span>Petunjuk Arah</span>
                        </a>
                    </div>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </section>

    <!-- Footer -->
    <footer class="bg-primary text-white py-24 px-6 text-center">
        <div class="max-w-4xl mx-auto space-y-8">
            <div class="flex items-center justify-center space-x-2">
                <div class="w-12 h-12 bg-white text-primary rounded-full flex items-center justify-center font-serif text-2xl italic shadow-lg">B</div>
                <span class="text-3xl font-serif font-bold tracking-tight">Brosuli</span>
            </div>
            <p class="opacity-60 max-w-lg mx-auto">Kami terus berkembang untuk menjadi bakery pilihan utama Anda di seluruh Jawa Tengah.</p>
            <div class="pt-8 border-t border-white/10 text-xs opacity-40">
                &copy; 2026 Brosuli Bakery. Seluruh hak cipta dilindungi.
            </div>
        </div>
    </footer>

    <!-- Branch Selection Modal -->
    <div id="branchModal" class="hidden fixed inset-0 bg-black/60 backdrop-blur-sm z-[100] flex items-center justify-center p-4">
        <div class="bg-white rounded-[2.5rem] w-full max-w-xl shadow-2xl overflow-hidden animate-in fade-in zoom-in duration-500 border border-white/20">
            <div class="p-8 bg-primary text-white text-center relative">
                <button onclick="document.getElementById('branchModal').classList.add('hidden')" class="absolute top-6 right-8 text-white/50 hover:text-white transition-all">
                    <i class="fas fa-times text-xl"></i>
                </button>
                <div class="w-16 h-16 bg-white/10 rounded-2xl flex items-center justify-center mx-auto mb-4 border border-white/20">
                    <i class="fas fa-map-marked-alt text-3xl"></i>
                </div>
                <h3 class="text-3xl font-serif font-bold">Pilih Cabang Brosuli</h3>
                <p class="text-white/70 mt-2">Pilih lokasi terdekat untuk mendapatkan layanan terbaik kami</p>
            </div>
            <div class="p-8 max-h-[60vh] overflow-y-auto space-y-3 bg-[#FDFCF6]">
                <!-- Option: Cabang Pusat -->
                <button onclick="selectBranch(1, 'Brosuli Boyolali (Pusat)')" 
                    class="w-full text-left p-6 rounded-3xl border-2 transition-all group <?php echo 1 === (int)$selected_branch_id ? 'border-secondary bg-secondary/5 shadow-md' : 'border-emerald-100 bg-white hover:border-emerald-300 hover:shadow-lg'; ?>">
                    <div class="flex justify-between items-center">
                        <div class="flex items-center space-x-4">
                            <div class="w-12 h-12 bg-emerald-100 text-emerald-600 rounded-2xl flex items-center justify-center text-xl shadow-inner">
                                <i class="fas fa-star"></i>
                            </div>
                            <div>
                                <h4 class="font-bold text-lg group-hover:text-emerald-600 transition-colors">Brosuli Boyolali (Pusat)</h4>
                                <p class="text-xs text-gray-500 mt-0.5 italic">Pusat Produksi & Distribusi Utama</p>
                            </div>
                        </div>
                        <div class="w-8 h-8 rounded-full flex items-center justify-center transition-all <?php echo 1 === (int)$selected_branch_id ? 'bg-emerald-500 text-white' : 'bg-gray-50 text-gray-300 group-hover:bg-emerald-50 group-hover:text-emerald-500'; ?>">
                            <i class="fas fa-check text-xs"></i>
                        </div>
                    </div>
                </button>

                <div class="py-2 flex items-center gap-4 text-gray-300">
                    <div class="h-px flex-1 bg-gray-100"></div>
                    <span class="text-[10px] font-bold uppercase tracking-widest">Cabang Lainnya</span>
                    <div class="h-px flex-1 bg-gray-100"></div>
                </div>

                <?php foreach ($branches as $branch): ?>
                <?php if ($branch['id'] == 1) continue; ?>
                <button onclick="selectBranch(<?php echo $branch['id']; ?>, '<?php echo addslashes($branch['name']); ?>')" 
                    class="w-full text-left p-6 rounded-3xl border-2 transition-all group <?php echo (int)$branch['id'] === (int)$selected_branch_id ? 'border-secondary bg-secondary/5 shadow-md' : 'border-gray-100 bg-white hover:border-secondary/30 hover:shadow-lg'; ?>">
                    <div class="flex justify-between items-center">
                        <div>
                            <h4 class="font-bold text-lg group-hover:text-secondary transition-colors"><?php echo htmlspecialchars($branch['name']); ?></h4>
                            <p class="text-sm text-gray-500 mt-1 flex items-center gap-2">
                                <i class="fas fa-location-dot text-gray-300"></i>
                                <?php echo htmlspecialchars($branch['address']); ?>
                            </p>
                        </div>
                        <div class="w-8 h-8 rounded-full flex items-center justify-center transition-all <?php echo (int)$branch['id'] === (int)$selected_branch_id ? 'bg-secondary text-white' : 'bg-gray-50 text-gray-300 group-hover:bg-secondary/10 group-hover:text-secondary'; ?>">
                            <i class="fas fa-chevron-right text-xs"></i>
                        </div>
                    </div>
                </button>
                <?php endforeach; ?>
            </div>
            <div class="p-6 bg-gray-50 text-center border-t border-gray-100 flex flex-col gap-2">
                <?php if ($selected_branch_id): ?>
                <button onclick="selectBranch('', 'Pilih Cabang')" class="text-xs text-secondary font-bold hover:underline">
                    <i class="fas fa-undo mr-1"></i> Reset Pilihan / Lihat Semua
                </button>
                <?php endif; ?>
                <p class="text-[10px] text-gray-400">Pilihan cabang akan menyesuaikan ketersediaan stok dan layanan kurir.</p>
            </div>
        </div>
    </div>

    <script>
        function selectBranch(id, name) {
            const formData = new FormData();
            formData.append('branch_id', id);
            
            fetch('api/set_branch.php', {
                method: 'POST',
                body: formData
            })
            .then(() => {
                location.reload();
            });
        }
    </script>
</body>
</html>
