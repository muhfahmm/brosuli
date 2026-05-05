<?php
session_start();
require_once '../db/db.php';
require_once '../config.php';

// Fetch best selling products (limited to 3-6)
$stmt = $pdo->query("SELECT p.*, c.name as category_name 
                    FROM best_sellers bs 
                    JOIN products p ON bs.product_id = p.id 
                    LEFT JOIN categories c ON p.category_id = c.id 
                    ORDER BY bs.display_order ASC 
                    LIMIT 6");
$best_sellers = $stmt->fetchAll();

// If no best sellers defined, fall back to featured products
if (empty($best_sellers)) {
    $stmt = $pdo->query("SELECT p.*, c.name as category_name FROM products p LEFT JOIN categories c ON p.category_id = c.id WHERE p.is_featured = 1 LIMIT 6");
    $best_sellers = $stmt->fetchAll();
}

// Fetch categories
$cat_stmt = $pdo->query("SELECT * FROM categories");
$categories = $cat_stmt->fetchAll();

// Fetch active banners for hero slider
$banner_stmt = $pdo->query("SELECT * FROM banners WHERE is_active = 1 ORDER BY created_at DESC");
$banners = $banner_stmt->fetchAll();
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
    <script src="js/cart.js?v=1.1" defer></script>
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
            <div class="flex items-center space-x-2">
                <div class="w-10 h-10 bg-primary rounded-full flex items-center justify-center text-white font-serif text-xl italic shadow-lg">B</div>
                <span class="text-2xl font-serif font-bold tracking-tight">Brosuli</span>
            </div>
            <div class="hidden md:flex items-center space-x-8 font-medium">
                <a href="#home" class="hover:text-secondary transition-colors">Beranda</a>
                <a href="catalog.php" class="hover:text-secondary transition-colors">Menu Kami</a>
            </div>
            <div class="flex items-center space-x-4">
                <button onclick="document.getElementById('cart-sidebar').classList.remove('translate-x-full')" class="relative p-2 text-primary hover:text-secondary transition-colors">
                    <i class="fas fa-shopping-bag text-2xl"></i>
                    <span class="cart-count absolute -top-1 -right-1 bg-secondary text-white text-[10px] w-5 h-5 rounded-full flex items-center justify-center font-bold border-2 border-white">0</span>
                </button>
                <button onclick="location.href='catalog.php'" class="bg-primary text-white px-6 py-2 rounded-full font-semibold hover:bg-secondary transition-all shadow-md transform hover:scale-105 hidden sm:block">
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
                     style="background-image: url('../<?php echo $banner['image_url']; ?>');">
                    <div class="relative text-center px-6 max-w-4xl z-10">
                        <h1 class="text-4xl md:text-7xl font-serif font-bold mb-6 leading-tight drop-shadow-lg">
                            <?php echo htmlspecialchars($banner['title']); ?>
                        </h1>
                        <p class="text-base md:text-xl mb-10 opacity-90 max-w-2xl mx-auto font-light leading-relaxed drop-shadow-md">
                            <?php echo nl2br(htmlspecialchars($banner['subtitle'])); ?>
                        </p>
                        <div class="flex flex-col sm:flex-row justify-center items-center space-y-4 sm:space-y-0 sm:space-x-6">
                            <a href="catalog.php" class="w-full sm:w-auto bg-secondary text-white px-10 py-4 rounded-full font-bold text-lg hover:bg-amber-600 transition-all shadow-xl hover:shadow-secondary/50">
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
                        <a href="catalog.php" class="bg-secondary text-white px-10 py-4 rounded-full font-bold text-lg hover:bg-amber-600 transition-all">Jelajahi Menu</a>
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
                <a href="catalog.php" class="mt-6 md:mt-0 text-primary font-bold hover:text-secondary flex items-center space-x-2 border-b-2 border-primary hover:border-secondary transition-all pb-1">
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
                                <img src="../<?php echo $product['image_url']; ?>" 
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
                                <span class="text-xl font-bold text-primary">Rp <?php echo number_format($product['price'], 0, ',', '.'); ?></span>
                                <button onclick='addToCart({id: <?php echo $product['id']; ?>, name: "<?php echo addslashes($product['name']); ?>", price: <?php echo $product['price']; ?>, image: "../<?php echo $product['image_url']; ?>"})' 
                                    class="w-10 h-10 bg-primary text-white rounded-full flex items-center justify-center hover:bg-secondary transition-colors shadow-lg">
                                    <i class="fas fa-plus"></i>
                                </button>
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
                        <a href="https://wa.me/62895327349264" target="_blank" class="w-10 h-10 bg-cream text-primary rounded-full flex items-center justify-center hover:bg-primary hover:text-white transition-all"><i class="fab fa-whatsapp"></i></a>
                    </div>
                </div>
                
                <div>
                    <h4 class="font-bold text-lg mb-8">Tautan Cepat</h4>
                    <ul class="space-y-4 text-gray-500">
                        <li><a href="index.php#home" class="hover:text-secondary transition-colors">Beranda</a></li>
                        <li><a href="catalog.php" class="hover:text-secondary transition-colors">Menu Kami</a></li>
                        <li><a href="location.php" class="hover:text-secondary transition-colors">Lokasi</a></li>
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

        <!-- Customer Info Section -->
        <div class="px-6 py-4 bg-gray-50 border-t border-gray-100 space-y-3">
            <div class="relative">
                <input type="text" id="customer-name" placeholder="Nama Anda" class="w-full bg-white border border-gray-200 rounded-xl px-4 py-2 text-sm outline-none focus:ring-2 focus:ring-secondary transition-all">
                <i class="fas fa-user absolute right-4 top-3 text-gray-300 text-xs"></i>
            </div>
            <div class="relative">
                <input type="tel" id="customer-phone" placeholder="Nomor WhatsApp" class="w-full bg-white border border-gray-200 rounded-xl px-4 py-2 text-sm outline-none focus:ring-2 focus:ring-secondary transition-all">
                <i class="fas fa-phone absolute right-4 top-3 text-gray-300 text-xs"></i>
            </div>
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
    <a href="https://wa.me/62895327349264" target="_blank" class="fixed bottom-8 right-8 z-[100] bg-[#25D366] text-white w-16 h-16 rounded-full flex items-center justify-center shadow-2xl hover:scale-110 transition-all duration-300 group">
        <i class="fab fa-whatsapp text-3xl"></i>
        <span class="absolute right-full mr-4 bg-white text-primary px-4 py-2 rounded-xl text-sm font-bold shadow-xl opacity-0 group-hover:opacity-100 transition-opacity pointer-events-none whitespace-nowrap">
            Ada yang bisa kami bantu?
        </span>
    </a>

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
