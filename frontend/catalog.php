<?php
session_start();
require_once '../db/db.php';
require_once '../config.php';

// Fetch all categories
$cat_stmt = $pdo->query("SELECT * FROM categories ORDER BY name ASC");
$categories = $cat_stmt->fetchAll();

// Fetch products based on category filter
$category_filter = isset($_GET['category']) ? $_GET['category'] : null;
if ($category_filter) {
    $stmt = $pdo->prepare("SELECT p.*, c.name as category_name FROM products p LEFT JOIN categories c ON p.category_id = c.id WHERE p.category_id = ? ORDER BY p.name ASC");
    $stmt->execute([$category_filter]);
} else {
    $stmt = $pdo->query("SELECT p.*, c.name as category_name FROM products p LEFT JOIN categories c ON p.category_id = c.id ORDER BY p.name ASC");
}
$products = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="id" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Katalog Menu - Brosuli Bakery</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;700&family=Playfair+Display:ital,wght@0,700;1,700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script src="js/cart.js?v=1.1" defer></script>
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
            background: rgba(255, 255, 255, 0.8);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
        }
    </style>
</head>
<body class="bg-cream text-primary overflow-x-hidden">

    <!-- Navbar -->
    <nav class="fixed top-0 left-0 right-0 z-50 glass border-b border-white/20">
        <div class="max-w-7xl mx-auto px-6 py-4 flex justify-between items-center">
            <a href="index.php" class="flex items-center space-x-2">
                <div class="w-10 h-10 bg-primary rounded-full flex items-center justify-center text-white font-serif text-xl italic shadow-lg">B</div>
                <span class="text-2xl font-serif font-bold tracking-tight">Brosuli</span>
            </a>
            <div class="hidden md:flex items-center space-x-8 font-medium">
                <a href="index.php#home" class="hover:text-secondary transition-colors">Beranda</a>
                <a href="catalog.php" class="text-secondary">Menu Kami</a>
            </div>
            <div class="flex items-center space-x-4">
                <button onclick="document.getElementById('cart-sidebar').classList.remove('translate-x-full')" class="relative p-2 text-primary hover:text-secondary transition-colors">
                    <i class="fas fa-shopping-bag text-2xl"></i>
                    <span class="cart-count absolute -top-1 -right-1 bg-secondary text-white text-[10px] w-5 h-5 rounded-full flex items-center justify-center font-bold border-2 border-white">0</span>
                </button>
                <a href="index.php" class="bg-primary text-white px-6 py-2 rounded-full font-semibold hover:bg-secondary transition-all shadow-md hidden sm:block">
                    Kembali
                </a>
            </div>
        </div>
    </nav>

    <!-- Catalog Header -->
    <header class="pt-32 pb-12 bg-white">
        <div class="max-w-7xl mx-auto px-6 text-center">
            <h1 class="text-4xl md:text-6xl font-serif font-bold mb-4">Katalog Menu</h1>
            <p class="text-gray-500 max-w-2xl mx-auto">Jelajahi koleksi panggangan segar kami, mulai dari roti artisan hingga kue surgawi.</p>
        </div>
    </header>

    <!-- Filters & Products -->
    <section class="py-12 min-h-screen">
        <div class="max-w-7xl mx-auto px-6">
            <!-- Category Filter -->
            <div class="flex flex-wrap justify-center gap-4 mb-16">
                <a href="catalog.php" class="px-6 py-2 rounded-full font-semibold transition-all <?php echo !$category_filter ? 'bg-primary text-white shadow-lg' : 'bg-white text-gray-600 hover:bg-gray-100 border border-gray-100'; ?>">
                    Semua
                </a>
                <?php foreach ($categories as $cat): ?>
                <a href="?category=<?php echo $cat['id']; ?>" 
                    class="px-6 py-2 rounded-full font-semibold transition-all <?php echo $category_filter == $cat['id'] ? 'bg-primary text-white shadow-lg' : 'bg-white text-gray-600 hover:bg-gray-100 border border-gray-100'; ?>">
                    <?php echo htmlspecialchars($cat['name']); ?>
                </a>
                <?php endforeach; ?>
            </div>

            <!-- Product Grid -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-10">
                <?php if (!empty($products)): ?>
                    <?php foreach ($products as $product): ?>
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
                    <div class="col-span-full py-20 text-center text-primary/40">
                        <i class="fas fa-bread-slice text-5xl mb-4 block"></i>
                        <p class="text-xl">Tidak ada produk dalam kategori ini.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="bg-white py-12 border-t border-amber-50">
        <div class="max-w-7xl mx-auto px-6 text-center">
            <div class="flex items-center justify-center space-x-2 mb-6">
                <div class="w-8 h-8 bg-primary rounded-full flex items-center justify-center text-white font-serif text-sm italic">B</div>
                <span class="text-xl font-serif font-bold tracking-tight">Brosuli</span>
            </div>
            <p class="text-gray-400 text-sm">&copy; 2026 Brosuli Bakery. Seluruh hak cipta dilindungi.</p>
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
    </a>

</body>
</html>
