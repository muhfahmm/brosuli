<?php
session_start();
require_once 'db/db.php';
require_once 'config.php';

// Get selected branch from session
$selected_branch_id = $_SESSION['user_branch_id'] ?? null;

if ($selected_branch_id) {
    $stmt = $pdo->prepare("SELECT p.*, c.name as category_name, COALESCE(bi.stock, 0) as stock 
                        FROM tb_best_sellers bs 
                        JOIN tb_products p ON bs.product_id = p.id 
                        INNER JOIN tb_branch_inventory bi ON p.id = bi.product_id AND bi.branch_id = ?
                        LEFT JOIN tb_categories c ON p.category_id = c.id 
                        ORDER BY bs.display_order ASC LIMIT 6");
    $stmt->execute([$selected_branch_id]);
    $best_sellers = $stmt->fetchAll();

    if (empty($best_sellers)) {
        $stmt = $pdo->prepare("SELECT p.*, c.name as category_name, bi.stock 
                             FROM tb_products p 
                             INNER JOIN tb_branch_inventory bi ON p.id = bi.product_id AND bi.branch_id = ?
                             LEFT JOIN tb_categories c ON p.category_id = c.id 
                             WHERE p.is_featured = 1 LIMIT 6");
        $stmt->execute([$selected_branch_id]);
        $best_sellers = $stmt->fetchAll();
    }
} else {
    $stmt = $pdo->query("SELECT p.*, c.name as category_name, 0 as stock 
                        FROM tb_best_sellers bs 
                        JOIN tb_products p ON bs.product_id = p.id 
                        LEFT JOIN tb_categories c ON p.category_id = c.id 
                        ORDER BY bs.display_order ASC LIMIT 6");
    $best_sellers = $stmt->fetchAll();

    if (empty($best_sellers)) {
        $stmt = $pdo->query("SELECT p.*, c.name as category_name, 0 as stock FROM tb_products p LEFT JOIN tb_categories c ON p.category_id = c.id WHERE p.is_featured = 1 LIMIT 6");
        $best_sellers = $stmt->fetchAll();
    }
}

$selected_branch_name = "Pilih Cabang";
if ($selected_branch_id) {
    $stmt = $pdo->prepare("SELECT name FROM tb_branches WHERE id = ?");
    $stmt->execute([$selected_branch_id]);
    $selected_branch_name = $stmt->fetchColumn() ?: "Pilih Cabang";
}

$cat_stmt = $pdo->query("SELECT * FROM tb_categories");
$categories = $cat_stmt->fetchAll();

$banner_stmt = $pdo->query("SELECT * FROM tb_banners WHERE is_active = 1 ORDER BY created_at DESC");
$banners = $banner_stmt->fetchAll();

$pdo->exec("DELETE FROM tb_branches WHERE id NOT IN (SELECT min_id FROM (SELECT MIN(id) as min_id FROM tb_branches GROUP BY name) as tmp)");

$branch_stmt = $pdo->query("SELECT * FROM tb_branches GROUP BY name ORDER BY name ASC");
$branches = $branch_stmt->fetchAll();

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
                <a href="#contact-form" class="hover:text-secondary transition-colors">Hubungi Kami</a>
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

    <!-- Include Sections -->
    <?php include 'frontend/sections/1_hero.php'; ?>
    <?php include 'frontend/sections/2_categories.php'; ?>
    <?php include 'frontend/sections/3_products.php'; ?>
    <?php include 'frontend/sections/4_about.php'; ?>
    <?php include 'frontend/sections/5_whyus.php'; ?>
    <?php include 'frontend/sections/6_testimonials.php'; ?>
    <?php include 'frontend/sections/7_contact.php'; ?>
    <?php include 'frontend/sections/8_cta.php'; ?>

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
                        <li><a href="index.php#contact-form" class="hover:text-secondary transition-colors">Hubungi Kami</a></li>
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
        
        <div id="cart-items-list" class="flex-1 overflow-y-auto p-6 space-y-6"></div>

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
                <h3 class="text-3xl font-serif font-bold">Pilih Cabang Brosuli</h3>
            </div>
            <div class="p-8 max-h-[60vh] overflow-y-auto space-y-3 bg-[#FDFCF6]">
                <button onclick="selectBranch(1, 'Brosuli Boyolali (Pusat)')" 
                    class="w-full text-left p-6 rounded-3xl border-2 transition-all group <?php echo 1 === (int)$selected_branch_id ? 'border-secondary bg-secondary/5 shadow-md' : 'border-emerald-100 bg-white hover:border-emerald-300 hover:shadow-lg'; ?>">
                    <div class="flex justify-between items-center">
                        <div class="flex items-center space-x-4">
                            <div class="w-12 h-12 bg-emerald-100 text-emerald-600 rounded-2xl flex items-center justify-center text-xl shadow-inner">
                                <i class="fas fa-star"></i>
                            </div>
                            <div>
                                <h4 class="font-bold text-lg group-hover:text-emerald-600 transition-colors">Brosuli Boyolali (Pusat)</h4>
                            </div>
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
                    <h4 class="font-bold text-lg"><?php echo htmlspecialchars($branch['name']); ?></h4>
                </button>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <script>
        function selectBranch(id, name) {
            const formData = new FormData();
            formData.append('branch_id', id);
            fetch('frontend/api/set_branch.php', {
                method: 'POST',
                body: formData
            }).then(() => {
                location.reload();
            });
        }

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
