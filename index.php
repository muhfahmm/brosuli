<?php
session_start();
require_once 'db/db.php';
require_once 'config.php';

// Get selected branch from session
$selected_branch_id = $_SESSION['user_branch_id'] ?? null;

if ($selected_branch_id) {
    // Fetch best selling products with stock (Only those active in this branch)
    $stmt = $pdo->prepare("SELECT p.*, c.name as category_name, COALESCE(bi.stock, 0) as stock 
                        FROM best_sellers bs 
                        JOIN products p ON bs.product_id = p.id 
                        INNER JOIN branch_inventory bi ON p.id = bi.product_id AND bi.branch_id = ?
                        LEFT JOIN categories c ON p.category_id = c.id 
                        ORDER BY bs.display_order ASC 
                        LIMIT 6");
    $stmt->execute([$selected_branch_id]);
    $best_sellers = $stmt->fetchAll();

    // If no best sellers defined, fall back to featured products
    if (empty($best_sellers)) {
        $stmt = $pdo->prepare("SELECT p.*, c.name as category_name, bi.stock 
                             FROM products p 
                             INNER JOIN branch_inventory bi ON p.id = bi.product_id AND bi.branch_id = ?
                             LEFT JOIN categories c ON p.category_id = c.id 
                             WHERE p.is_featured = 1 LIMIT 6");
        $stmt->execute([$selected_branch_id]);
        $best_sellers = $stmt->fetchAll();
    }
} else {
    // No branch selected: Show global products (No stock info needed for now)
    $stmt = $pdo->query("SELECT p.*, c.name as category_name, 0 as stock 
                        FROM best_sellers bs 
                        JOIN products p ON bs.product_id = p.id 
                        LEFT JOIN categories c ON p.category_id = c.id 
                        ORDER BY bs.display_order ASC 
                        LIMIT 6");
    $best_sellers = $stmt->fetchAll();

    if (empty($best_sellers)) {
        $stmt = $pdo->query("SELECT p.*, c.name as category_name, 0 as stock FROM products p LEFT JOIN categories c ON p.category_id = c.id WHERE p.is_featured = 1 LIMIT 6");
        $best_sellers = $stmt->fetchAll();
    }
}

// Get branch name for display
$selected_branch_name = "Pilih Cabang";
if ($selected_branch_id) {
    $stmt = $pdo->prepare("SELECT name FROM branches WHERE id = ?");
    $stmt->execute([$selected_branch_id]);
    $selected_branch_name = $stmt->fetchColumn() ?: "Pilih Cabang";
}

// Fetch categories
$cat_stmt = $pdo->query("SELECT * FROM categories");
$categories = $cat_stmt->fetchAll();

// Fetch active banners for hero slider
$banner_stmt = $pdo->query("SELECT * FROM banners WHERE is_active = 1 ORDER BY created_at DESC");
$banners = $banner_stmt->fetchAll();

// Aggressive Cleanup for duplicates (Runs every time index is loaded until clean)
$pdo->exec("DELETE FROM branches WHERE id NOT IN (SELECT min_id FROM (SELECT MIN(id) as min_id FROM branches GROUP BY name) as tmp)");

// Fetch branches for selection
$branch_stmt = $pdo->query("SELECT * FROM branches GROUP BY name ORDER BY name ASC");
$branches = $branch_stmt->fetchAll();

// Get selected branch from session
$selected_branch_id = $_SESSION['user_branch_id'] ?? null;
$selected_branch_name = 'Pilih Cabang';

if ($selected_branch_id) {
    foreach ($branches as $branch) {
        if ($branch['id'] == $selected_branch_id) {
            $selected_branch_name = $branch['name'];
            break;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Brosuli - Pengalaman Bakery Otentik</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;700&family=Playfair+Display:ital,wght@0,700;1,700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script src="frontend/js/cart.js?v=1.2" defer></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css" />
    <script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>
    <script src="https://app.midtrans.com/snap/snap.js" data-client-key="<?php echo MIDTRANS_CLIENT_KEY; ?>"></script>
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
        .hero-slide {
            background-size: cover;
            background-position: center;
            position: relative;
        }
        .hero-slide::before {
            content: '';
            position: absolute;
            inset: 0;
            background: linear-gradient(to bottom, rgba(74, 44, 42, 0.3), rgba(74, 44, 42, 0.8));
        }
    </style>
</head>
<body class="bg-cream text-primary overflow-x-hidden">

    <!-- Navbar -->
    <nav class="fixed top-0 left-0 right-0 z-50 transition-all duration-300 glass border-b border-white/20">
        <div class="max-w-7xl mx-auto px-6 py-4 flex justify-between items-center">
            <a href="index.php" class="flex items-center space-x-2">
                <div class="w-10 h-10 bg-primary rounded-full flex items-center justify-center text-white font-serif text-xl italic shadow-lg">B</div>
                <span class="text-2xl font-serif font-bold tracking-tight">Brosuli</span>
            </a>
            <div class="hidden md:flex items-center space-x-8 font-medium">
                <a href="#home" class="hover:text-secondary transition-colors">Beranda</a>
                <a href="frontend/catalog.php" class="hover:text-secondary transition-colors">Menu Kami</a>
                <a href="frontend/contact.php" class="hover:text-secondary transition-colors">Hubungi Kami</a>
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
                <button onclick="location.href='frontend/catalog.php'" class="bg-primary text-white px-6 py-2 rounded-full font-semibold hover:bg-secondary transition-all shadow-md transform hover:scale-105 hidden sm:block">
                    Pesan Sekarang
                </button>
            </div>
        </div>
    </nav>

    <!-- Hero Section with Swiper -->
    <section id="home" class="relative h-[85vh] md:h-screen pt-16 overflow-hidden">
        <div class="swiper heroSwiper h-full w-full">
            <div class="swiper-wrapper">
                <?php foreach ($banners as $banner): ?>
                <div class="swiper-slide hero-slide flex items-center justify-center text-white" 
                     style="background-image: url('<?php echo $banner['image_url']; ?>');">
                    <div class="relative text-center px-6 max-w-4xl z-10">
                        <h1 class="text-4xl md:text-7xl font-serif font-bold mb-6 leading-tight drop-shadow-lg">
                            <?php echo htmlspecialchars($banner['title']); ?>
                        </h1>
                        <p class="text-base md:text-xl mb-10 opacity-90 max-w-2xl mx-auto font-light leading-relaxed drop-shadow-md">
                            <?php echo nl2br(htmlspecialchars($banner['subtitle'])); ?>
                        </p>
                        <div class="flex flex-col sm:flex-row justify-center items-center space-y-4 sm:space-y-0 sm:space-x-6">
                            <a href="frontend/catalog.php" class="w-full sm:w-auto bg-secondary text-white px-10 py-4 rounded-full font-bold text-lg hover:bg-amber-600 transition-all shadow-xl hover:shadow-secondary/50">
                                Jelajahi Menu
                            </a>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
                
                <?php if (empty($banners)): ?>
                <div class="swiper-slide hero-slide flex items-center justify-center text-white bg-primary">
                    <div class="relative text-center px-6 max-w-4xl z-10">
                        <h1 class="text-4xl md:text-7xl font-serif font-bold mb-6 leading-tight">Selamat Datang di Brosuli</h1>
                        <p class="text-base md:text-xl mb-10 opacity-90 max-w-2xl mx-auto font-light leading-relaxed">Pengalaman Bakery Otentik Sejak 2009</p>
                        <a href="frontend/catalog.php" class="bg-secondary text-white px-10 py-4 rounded-full font-bold text-lg hover:bg-amber-600 transition-all">Jelajahi Menu</a>
                    </div>
                </div>
                <?php endif; ?>
            </div>
            
            <?php if (count($banners) > 1): ?>
                <div class="swiper-pagination !bottom-10"></div>
                <div class="swiper-button-next !text-white !right-10 hidden md:flex !w-14 !h-14 !bg-white/10 !backdrop-blur-md !rounded-full hover:!bg-white/20 transition-all after:!content-['']">
                    <i class="fas fa-chevron-right text-xl"></i>
                </div>
                <div class="swiper-button-prev !text-white !left-10 hidden md:flex !w-14 !h-14 !bg-white/10 !backdrop-blur-md !rounded-full hover:!bg-white/20 transition-all after:!content-['']">
                    <i class="fas fa-chevron-left text-xl"></i>
                </div>
            <?php endif; ?>
        </div>
        
        <div class="absolute bottom-4 left-1/2 -translate-x-1/2 animate-bounce opacity-60 z-20 text-white">
            <i class="fas fa-chevron-down text-xl"></i>
        </div>
    </section>

    <!-- Categories Section -->
    <section id="categories" class="py-24 bg-white">
        <div class="max-w-7xl mx-auto px-6">
            <div class="text-center mb-16 category-header">
                <span class="text-secondary font-bold tracking-widest uppercase text-sm">Spesialisasi Kami</span>
                <h2 class="text-4xl md:text-5xl font-serif font-bold mt-2">Dipanggang dengan Kasih Sayang</h2>
            </div>
            
            <div class="grid grid-cols-2 md:grid-cols-4 gap-8 category-grid">
                <?php
                $cat_icons = [
                    'Roti' => 'fa-bread-slice',
                    'Kue' => 'fa-birthday-cake',
                    'Pastry' => 'fa-croissant',
                    'Kue Kering' => 'fa-cookie-bite'
                ];
                foreach ($categories as $category):
                    $icon = isset($cat_icons[$category['name']]) ? $cat_icons[$category['name']] : 'fa-bread-slice';
                ?>
                <div class="group bg-cream p-8 rounded-3xl text-center hover:bg-primary hover:text-white transition-all duration-500 cursor-pointer shadow-sm hover:shadow-2xl transform hover:-translate-y-2">
                    <div class="w-16 h-16 bg-white text-secondary rounded-2xl flex items-center justify-center text-3xl mb-6 mx-auto group-hover:scale-110 transition-transform shadow-inner">
                        <i class="fas <?php echo $icon; ?>"></i>
                    </div>
                    <h3 class="text-xl font-bold mb-2"><?php echo htmlspecialchars($category['name']); ?></h3>
                    <p class="text-sm opacity-60 group-hover:opacity-80">Jelajahi koleksi lezat kami dari <?php echo strtolower($category['name']); ?>.</p>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- Featured Products -->
    <section id="products" class="py-24">
        <div class="max-w-7xl mx-auto px-6">
            <div class="flex flex-col md:flex-row md:items-end justify-between mb-16 product-header">
                <div>
                    <span class="text-secondary font-bold tracking-widest uppercase text-sm">Terlaris</span>
                    <h2 class="text-4xl md:text-5xl font-serif font-bold mt-2 text-primary">Kreasi Unggulan</h2>
                </div>
                <a href="frontend/catalog.php" class="mt-6 md:mt-0 text-primary font-bold hover:text-secondary flex items-center space-x-2 border-b-2 border-primary hover:border-secondary transition-all pb-1">
                    <span>Lihat Semua Produk</span>
                    <i class="fas fa-arrow-right text-xs"></i>
                </a>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-10 product-grid">
                <?php if (!empty($best_sellers)): ?>
                    <?php foreach ($best_sellers as $product): ?>
                    <div class="bg-white rounded-[2.5rem] overflow-hidden shadow-sm hover:shadow-2xl transition-all duration-500 group border border-amber-50">
                        <div class="relative h-64 overflow-hidden bg-cream">
                            <?php if ($product['image_url']): ?>
                                <img src="<?php echo $product['image_url']; ?>" 
                                    class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-700">
                            <?php else: ?>
                                <div class="w-full h-full flex items-center justify-center text-primary/20">
                                    <i class="fas fa-image text-5xl"></i>
                                </div>
                            <?php endif; ?>
                            <div class="absolute top-4 right-4 bg-white/90 backdrop-blur-md text-primary px-4 py-1 rounded-full text-xs font-bold shadow-sm">
                                <?php echo htmlspecialchars($product['category_name']); ?>
                            </div>
                        </div>
                        <div class="p-8">
                            <h3 class="text-xl font-bold mb-2 group-hover:text-secondary transition-colors"><?php echo htmlspecialchars($product['name']); ?></h3>
                            <p class="text-gray-500 text-sm mb-6 line-clamp-2"><?php echo htmlspecialchars($product['description']); ?></p>
                            <div class="flex items-center justify-between">
                                <div class="flex flex-col">
                                    <span class="text-xl font-bold text-primary">Rp <?php echo number_format($product['price'], 0, ',', '.'); ?></span>
                                    <?php if ($selected_branch_id): ?>
                                    <span class="text-[10px] font-bold uppercase tracking-tighter <?php echo $product['stock'] > 0 ? 'text-green-500' : 'text-red-500'; ?>">
                                        <?php echo $product['stock'] > 0 ? 'Stok Tersedia (' . $product['stock'] . ')' : 'Stok Habis'; ?>
                                    </span>
                                    <?php endif; ?>
                                </div>
                                <?php if (!$selected_branch_id || $product['stock'] > 0): ?>
                                <button onclick='handleAddToCart(<?php echo json_encode(["id" => $product["id"], "name" => $product["name"], "price" => (float)$product["price"], "image" => $product["image_url"]]); ?>)' 
                                    class="w-10 h-10 bg-primary text-white rounded-full flex items-center justify-center hover:bg-secondary transition-colors shadow-lg">
                                    <i class="fas fa-plus"></i>
                                </button>
                                <?php else: ?>
                                <button disabled class="w-10 h-10 bg-gray-200 text-gray-400 rounded-full flex items-center justify-center cursor-not-allowed">
                                    <i class="fas fa-times"></i>
                                </button>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="col-span-full py-12 text-center text-primary/40">
                        <i class="fas fa-bread-slice text-4xl mb-4 block"></i>
                        <p>Belum ada produk unggulan yang ditampilkan.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <!-- About Us Section -->
    <section id="about" class="py-24 bg-primary text-white">
        <div class="max-w-7xl mx-auto px-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-16 items-center">
                <div>
                    <span class="text-secondary font-bold tracking-widest uppercase text-sm">Tentang Kami</span>
                    <h2 class="text-4xl md:text-5xl font-serif font-bold mt-4 mb-6">Warisan Kelezatan Sejak 2009</h2>
                    <p class="text-white/80 leading-relaxed mb-6 text-lg">
                        Brosuli Bakery memulai perjalanan dengan visi sederhana: menghadirkan brownies berkualitas tinggi dengan cita rasa autentik yang tak terlupakan. Dari sebuah toko kecil, kami telah berkembang menjadi jaringan bakery terpercaya di Jawa Tengah.
                    </p>
                    <p class="text-white/80 leading-relaxed mb-8 text-lg">
                        Setiap produk dibuat dengan bahan-bahan pilihan terbaik dan resep rahasia yang telah teruji selama bertahun-tahun. Komitmen kami adalah memberikan pengalaman kuliner terbaik untuk setiap pelanggan setia.
                    </p>
                    <a href="frontend/catalog.php" class="inline-block bg-secondary hover:bg-amber-600 text-primary px-8 py-4 rounded-full font-bold transition-all shadow-lg">
                        Jelajahi Produk Kami
                    </a>
                </div>
                <div class="space-y-8">
                    <div class="bg-white/10 backdrop-blur-md p-8 rounded-2xl border border-white/20">
                        <div class="flex items-start space-x-4">
                            <div class="w-12 h-12 bg-secondary text-primary rounded-full flex items-center justify-center text-xl flex-shrink-0 font-bold">
                                ✓
                            </div>
                            <div>
                                <h3 class="text-xl font-bold mb-2">Bahan Premium</h3>
                                <p class="text-white/70">Menggunakan bahan-bahan berkualitas tinggi pilihan untuk setiap produk.</p>
                            </div>
                        </div>
                    </div>
                    <div class="bg-white/10 backdrop-blur-md p-8 rounded-2xl border border-white/20">
                        <div class="flex items-start space-x-4">
                            <div class="w-12 h-12 bg-secondary text-primary rounded-full flex items-center justify-center text-xl flex-shrink-0 font-bold">
                                ✓
                            </div>
                            <div>
                                <h3 class="text-xl font-bold mb-2">Resep Rahasia</h3>
                                <p class="text-white/70">Resep tradisional yang telah disempurnakan selama lebih dari satu dekade.</p>
                            </div>
                        </div>
                    </div>
                    <div class="bg-white/10 backdrop-blur-md p-8 rounded-2xl border border-white/20">
                        <div class="flex items-start space-x-4">
                            <div class="w-12 h-12 bg-secondary text-primary rounded-full flex items-center justify-center text-xl flex-shrink-0 font-bold">
                                ✓
                            </div>
                            <div>
                                <h3 class="text-xl font-bold mb-2">Konsistensi Terjamin</h3>
                                <p class="text-white/70">Kualitas rasa yang sama di setiap pembelian, di mana pun Anda berada.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Why Choose Us Section -->
    <section id="why-us" class="py-24 bg-white">
        <div class="max-w-7xl mx-auto px-6">
            <div class="text-center mb-16">
                <span class="text-secondary font-bold tracking-widest uppercase text-sm">Keunggulan Kami</span>
                <h2 class="text-4xl md:text-5xl font-serif font-bold mt-4 text-primary">Mengapa Memilih Brosuli?</h2>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-10">
                <div class="bg-cream rounded-3xl p-10 text-center hover:shadow-2xl hover:scale-105 transition-all duration-300 border border-secondary/10">
                    <div class="w-20 h-20 bg-secondary text-white rounded-full flex items-center justify-center text-4xl mx-auto mb-6">
                        <i class="fas fa-heart"></i>
                    </div>
                    <h3 class="text-2xl font-bold text-primary mb-4">Dibuat dengan Cinta</h3>
                    <p class="text-gray-500 leading-relaxed">Setiap gigitan mencerminkan dedikasi dan passion kami dalam menciptakan produk terbaik untuk Anda.</p>
                </div>
                <div class="bg-cream rounded-3xl p-10 text-center hover:shadow-2xl hover:scale-105 transition-all duration-300 border border-secondary/10">
                    <div class="w-20 h-20 bg-secondary text-white rounded-full flex items-center justify-center text-4xl mx-auto mb-6">
                        <i class="fas fa-truck"></i>
                    </div>
                    <h3 class="text-2xl font-bold text-primary mb-4">Pengiriman Cepat</h3>
                    <p class="text-gray-500 leading-relaxed">Nikmati produk fresh kami dengan sistem pengiriman yang cepat dan aman langsung ke pintu rumah Anda.</p>
                </div>
                <div class="bg-cream rounded-3xl p-10 text-center hover:shadow-2xl hover:scale-105 transition-all duration-300 border border-secondary/10">
                    <div class="w-20 h-20 bg-secondary text-white rounded-full flex items-center justify-center text-4xl mx-auto mb-6">
                        <i class="fas fa-star"></i>
                    </div>
                    <h3 class="text-2xl font-bold text-primary mb-4">Kualitas Terjamin</h3>
                    <p class="text-gray-500 leading-relaxed">Standar kualitas internasional dengan sentuhan lokal yang membuat produk kami unik dan istimewa.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Testimonials Section -->
    <section id="testimonials" class="py-24 bg-gradient-to-b from-cream to-white">
        <div class="max-w-7xl mx-auto px-6">
            <div class="text-center mb-16">
                <span class="text-secondary font-bold tracking-widest uppercase text-sm">Kepuasan Pelanggan</span>
                <h2 class="text-4xl md:text-5xl font-serif font-bold mt-4 text-primary">Apa Kata Pelanggan Kami?</h2>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-10">
                <div class="bg-white rounded-3xl p-10 shadow-lg border-l-4 border-secondary">
                    <div class="flex items-center mb-4">
                        <div class="flex space-x-1">
                            <i class="fas fa-star text-secondary text-lg"></i>
                            <i class="fas fa-star text-secondary text-lg"></i>
                            <i class="fas fa-star text-secondary text-lg"></i>
                            <i class="fas fa-star text-secondary text-lg"></i>
                            <i class="fas fa-star text-secondary text-lg"></i>
                        </div>
                        <span class="text-xs font-bold text-gray-400 ml-2">5/5</span>
                    </div>
                    <p class="text-gray-600 leading-relaxed mb-6 italic">
                        "Brownies Brosuli adalah yang terbaik yang pernah saya coba! Teksturnya lembut, rasanya nikmat, dan pelayanannya super ramah. Pasti beli lagi!"
                    </p>
                    <div class="flex items-center space-x-3">
                        <div class="w-12 h-12 bg-secondary rounded-full flex items-center justify-center text-white font-bold">
                            SR
                        </div>
                        <div>
                            <h4 class="font-bold text-primary">Siti Rahayu</h4>
                            <p class="text-xs text-gray-400">Pelanggan Setia</p>
                        </div>
                    </div>
                </div>
                <div class="bg-white rounded-3xl p-10 shadow-lg border-l-4 border-secondary">
                    <div class="flex items-center mb-4">
                        <div class="flex space-x-1">
                            <i class="fas fa-star text-secondary text-lg"></i>
                            <i class="fas fa-star text-secondary text-lg"></i>
                            <i class="fas fa-star text-secondary text-lg"></i>
                            <i class="fas fa-star text-secondary text-lg"></i>
                            <i class="fas fa-star text-secondary text-lg"></i>
                        </div>
                        <span class="text-xs font-bold text-gray-400 ml-2">5/5</span>
                    </div>
                    <p class="text-gray-600 leading-relaxed mb-6 italic">
                        "Sering membeli untuk hadiah teman dan keluarga. Kemasan cantik, isi fresh, dan selalu membuat orang senang. Highly recommended!"
                    </p>
                    <div class="flex items-center space-x-3">
                        <div class="w-12 h-12 bg-secondary rounded-full flex items-center justify-center text-white font-bold">
                            BD
                        </div>
                        <div>
                            <h4 class="font-bold text-primary">Bambang Dwi</h4>
                            <p class="text-xs text-gray-400">Pelanggan Setia</p>
                        </div>
                    </div>
                </div>
                <div class="bg-white rounded-3xl p-10 shadow-lg border-l-4 border-secondary">
                    <div class="flex items-center mb-4">
                        <div class="flex space-x-1">
                            <i class="fas fa-star text-secondary text-lg"></i>
                            <i class="fas fa-star text-secondary text-lg"></i>
                            <i class="fas fa-star text-secondary text-lg"></i>
                            <i class="fas fa-star text-secondary text-lg"></i>
                            <i class="fas fa-star text-secondary text-lg"></i>
                        </div>
                        <span class="text-xs font-bold text-gray-400 ml-2">5/5</span>
                    </div>
                    <p class="text-gray-600 leading-relaxed mb-6 italic">
                        "Setiap kali ada acara spesial, pasti pesan Brosuli. Kualitas konsisten, delivery tepat waktu, dan harganya worth it banget!"
                    </p>
                    <div class="flex items-center space-x-3">
                        <div class="w-12 h-12 bg-secondary rounded-full flex items-center justify-center text-white font-bold">
                            AW
                        </div>
                        <div>
                            <h4 class="font-bold text-primary">Ani Wijaya</h4>
                            <p class="text-xs text-gray-400">Pelanggan Setia</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="py-20 bg-primary">
        <div class="max-w-4xl mx-auto px-6 text-center">
            <h2 class="text-4xl md:text-5xl font-serif font-bold text-white mb-6">Siap Merasakan Kelezatan?</h2>
            <p class="text-white/80 text-lg mb-10 leading-relaxed">
                Jangan lewatkan kesempatan untuk menikmati brownies premium Brosuli yang dibuat dengan cinta dan bahan terbaik.
            </p>
            <a href="frontend/catalog.php" class="inline-block bg-secondary hover:bg-amber-600 text-primary px-10 py-5 rounded-full font-bold text-lg transition-all shadow-xl transform hover:scale-105">
                Pesan Sekarang - Fresh & Lezat
            </a>
        </div>
    </section>

    <!-- Footer -->
    <footer id="contact" class="bg-white pt-24 pb-12 border-t border-amber-50">

        <div class="max-w-7xl mx-auto px-6">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-16 mb-20">
                <div class="col-span-1 md:col-span-1">
                    <div class="flex items-center space-x-2 mb-8">
                        <div class="w-10 h-10 bg-primary rounded-full flex items-center justify-center text-white font-serif text-xl italic">B</div>
                        <span class="text-2xl font-serif font-bold tracking-tight">Brosuli</span>
                    </div>
                    <p class="text-gray-500 leading-relaxed mb-8">
                        Melayani masyarakat dengan panggangan segar dan artisan sejak 2009. Kualitas dan rasa adalah prioritas kami.
                    </p>
                    <div class="flex space-x-4">
                        <a href="#" class="w-10 h-10 bg-cream text-primary rounded-full flex items-center justify-center hover:bg-primary hover:text-white transition-all"><i class="fab fa-instagram"></i></a>
                        <a href="#" class="w-10 h-10 bg-cream text-primary rounded-full flex items-center justify-center hover:bg-primary hover:text-white transition-all"><i class="fab fa-facebook-f"></i></a>
                        <a href="https://wa.me/62895327349264?text=Halo%20Brosuli%20Bakery%2C%20saya%20ingin%20bertanya..." target="_blank" class="w-10 h-10 bg-cream text-primary rounded-full flex items-center justify-center hover:bg-primary hover:text-white transition-all"><i class="fab fa-whatsapp"></i></a>
                    </div>
                </div>
                
                <div>
                    <h4 class="font-bold text-lg mb-8">Tautan Cepat</h4>
                    <ul class="space-y-4 text-gray-500">
                        <li><a href="index.php#home" class="hover:text-secondary transition-colors">Beranda</a></li>
                        <li><a href="frontend/catalog.php" class="hover:text-secondary transition-colors">Menu Kami</a></li>
                        <li><a href="frontend/contact.php" class="hover:text-secondary transition-colors">Hubungi Kami</a></li>
                        <li><a href="frontend/location.php" class="hover:text-secondary transition-colors">Lokasi</a></li>
                    </ul>
                </div>
                
                <div>
                    <h4 class="font-bold text-lg mb-8">Jam Buka</h4>
                    <ul class="space-y-4 text-gray-500">
                        <li class="flex justify-between"><span>Sen - Jum</span> <span>07:00 - 20:00</span></li>
                        <li class="flex justify-between"><span>Sab - Min</span> <span>08:00 - 21:00</span></li>
                    </ul>
                </div>
                
                <div>
                    <h4 class="font-bold text-lg mb-8">Buletin</h4>
                    <p class="text-sm text-gray-500 mb-6">Bergabunglah dengan klub bakery kami untuk pembaruan segar dan penawaran eksklusif.</p>
                    <form class="relative">
                        <input type="email" placeholder="Alamat email Anda" class="w-full bg-cream border-none rounded-full px-6 py-4 outline-none focus:ring-2 focus:ring-secondary transition-all">
                        <button class="absolute right-2 top-2 bg-primary text-white w-10 h-10 rounded-full hover:bg-secondary transition-colors">
                            <i class="fas fa-paper-plane text-sm"></i>
                        </button>
                    </form>
                </div>
            </div>
            
            <div class="pt-12 border-t border-amber-50 text-center text-gray-400 text-sm">
                <p>&copy; 2026 Brosuli Bakery. Seluruh hak cipta dilindungi. <br class="md:hidden"> Dirancang untuk selera terbaik.</p>
            </div>
        </div>
    </footer>

    <!-- Cart Sidebar -->
    <div id="cart-sidebar" class="fixed inset-y-0 right-0 w-full md:w-[400px] bg-white z-[150] shadow-2xl translate-x-full transition-transform duration-500 ease-in-out flex flex-col">
        <div class="p-6 border-b border-gray-100 flex justify-between items-center bg-primary text-white">
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

    <!-- Floating WhatsApp Button -->
    <a href="https://wa.me/62895327349264?text=Halo%20Brosuli%20Bakery%2C%20saya%20ingin%20bertanya..." target="_blank" class="fixed bottom-8 right-8 z-[100] bg-[#25D366] text-white w-16 h-16 rounded-full flex items-center justify-center shadow-2xl hover:scale-110 transition-all duration-300 group">
        <i class="fab fa-whatsapp text-3xl"></i>
        <span class="absolute right-full mr-4 bg-white text-primary px-4 py-2 rounded-xl text-sm font-bold shadow-xl opacity-0 group-hover:opacity-100 transition-opacity pointer-events-none whitespace-nowrap">
            Ada yang bisa kami bantu?
        </span>
    </a>

    <!-- Branch Selection Modal -->
    <div id="branchModal" class="hidden fixed inset-0 bg-black/60 backdrop-blur-sm z-[200] flex items-center justify-center p-4">
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
                <?php if ($branch['id'] == 1) continue; // Skip main branch as it's already shown above ?>
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
        const selectedBranchId = <?php echo json_encode($selected_branch_id); ?>;

        function handleAddToCart(product) {
            // No longer forcing branch selection, default to master if not selected
            addToCart(product);
        }

        function selectBranch(id, name) {
            const formData = new FormData();
            formData.append('branch_id', id);
            
            fetch('frontend/api/set_branch.php', {
                method: 'POST',
                body: formData
            })
            .then(() => {
                location.reload();
            });
        }
    </script>

    <script>
        // Navbar scroll effect
        window.addEventListener('scroll', () => {
            const nav = document.querySelector('nav');
            if (window.scrollY > 50) {
                nav.classList.add('py-2', 'shadow-xl');
                nav.classList.remove('py-4');
            } else {
                nav.classList.add('py-4');
                nav.classList.remove('py-2', 'shadow-xl');
            }
        });

        // Initialize Swiper Hero Slider
        if (document.querySelector('.heroSwiper')) {
            const swiper = new Swiper('.heroSwiper', {
                loop: true,
                autoplay: {
                    delay: 5000,
                    disableOnInteraction: false,
                },
                pagination: {
                    el: '.swiper-pagination',
                    clickable: true,
                },
                navigation: {
                    nextEl: '.swiper-button-next',
                    prevEl: '.swiper-button-prev',
                },
                effect: 'fade',
                fadeEffect: {
                    crossFade: true
                }
            });
        }
    </script>
</body>
</html>
