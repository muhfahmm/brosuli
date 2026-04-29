<?php
require_once '../db/db.php';

// Fetch featured products
$stmt = $pdo->query("SELECT p.*, c.name as category_name FROM products p LEFT JOIN categories c ON p.category_id = c.id WHERE p.is_featured = 1 LIMIT 4");
$featured_products = $stmt->fetchAll();

// Fetch categories
$cat_stmt = $pdo->query("SELECT * FROM categories");
$categories = $cat_stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Brosuli - Authentic Bakery Experience</title>
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
        .hero-gradient {
            background: linear-gradient(to bottom, rgba(74, 44, 42, 0.4), rgba(74, 44, 42, 0.8)), url('https://images.unsplash.com/photo-1509440159596-0249088772ff?ixlib=rb-1.2.1&auto=format&fit=crop&w=1920&q=80');
            background-size: cover;
            background-position: center;
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
                <a href="#home" class="hover:text-secondary transition-colors">Home</a>
                <a href="#products" class="hover:text-secondary transition-colors">Our Menu</a>
                <a href="#about" class="hover:text-secondary transition-colors">Our Story</a>
                <a href="#contact" class="hover:text-secondary transition-colors">Contact</a>
            </div>
            <div class="flex items-center space-x-4">
                <button class="bg-primary text-white px-6 py-2 rounded-full font-semibold hover:bg-secondary transition-all shadow-md transform hover:scale-105">
                    Order Now
                </button>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section id="home" class="relative h-screen flex items-center justify-center hero-gradient text-white pt-16">
        <div class="text-center px-6 max-w-4xl animate-fade-in-up">
            <h1 class="text-5xl md:text-7xl font-serif font-bold mb-6 leading-tight">
                Crafting <span class="italic text-secondary">Sweet Moments</span> Every Single Day.
            </h1>
            <p class="text-lg md:text-xl mb-10 opacity-90 max-w-2xl mx-auto font-light leading-relaxed">
                From artisan breads to celestial cakes, we bring the finest ingredients and traditional recipes to your table.
            </p>
            <div class="flex flex-col sm:flex-row justify-center items-center space-y-4 sm:space-y-0 sm:space-x-6">
                <a href="#products" class="w-full sm:w-auto bg-secondary text-white px-10 py-4 rounded-full font-bold text-lg hover:bg-amber-600 transition-all shadow-xl hover:shadow-secondary/50">
                    Explore Menu
                </a>
                <a href="#about" class="w-full sm:w-auto bg-white/10 backdrop-blur-md border border-white/30 text-white px-10 py-4 rounded-full font-bold text-lg hover:bg-white hover:text-primary transition-all">
                    Our Story
                </a>
            </div>
        </div>
        <div class="absolute bottom-10 left-1/2 -translate-x-1/2 animate-bounce opacity-60">
            <i class="fas fa-chevron-down text-2xl"></i>
        </div>
    </section>

    <!-- Categories Section -->
    <section class="py-24 bg-white">
        <div class="max-w-7xl mx-auto px-6">
            <div class="text-center mb-16">
                <span class="text-secondary font-bold tracking-widest uppercase text-sm">Our Specialties</span>
                <h2 class="text-4xl md:text-5xl font-serif font-bold mt-2">Baked with Love</h2>
            </div>
            
            <div class="grid grid-cols-2 md:grid-cols-4 gap-8">
                <?php
                $cat_icons = [
                    'Breads' => 'fa-bread-slice',
                    'Cakes' => 'fa-birthday-cake',
                    'Pastries' => 'fa-croissant',
                    'Cookies' => 'fa-cookie-bite'
                ];
                foreach ($categories as $category):
                    $icon = isset($cat_icons[$category['name']]) ? $cat_icons[$category['name']] : 'fa-bread-slice';
                ?>
                <div class="group bg-cream p-8 rounded-3xl text-center hover:bg-primary hover:text-white transition-all duration-500 cursor-pointer shadow-sm hover:shadow-2xl transform hover:-translate-y-2">
                    <div class="w-16 h-16 bg-white text-secondary rounded-2xl flex items-center justify-center text-3xl mb-6 mx-auto group-hover:scale-110 transition-transform shadow-inner">
                        <i class="fas <?php echo $icon; ?>"></i>
                    </div>
                    <h3 class="text-xl font-bold mb-2"><?php echo htmlspecialchars($category['name']); ?></h3>
                    <p class="text-sm opacity-60 group-hover:opacity-80">Explore our delicious collection of <?php echo strtolower($category['name']); ?>.</p>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- Featured Products -->
    <section id="products" class="py-24">
        <div class="max-w-7xl mx-auto px-6">
            <div class="flex flex-col md:flex-row md:items-end justify-between mb-16">
                <div>
                    <span class="text-secondary font-bold tracking-widest uppercase text-sm">Best Sellers</span>
                    <h2 class="text-4xl md:text-5xl font-serif font-bold mt-2 text-primary">Signature Creations</h2>
                </div>
                <a href="#" class="mt-6 md:mt-0 text-primary font-bold hover:text-secondary flex items-center space-x-2 border-b-2 border-primary hover:border-secondary transition-all pb-1">
                    <span>View All Products</span>
                    <i class="fas fa-arrow-right text-xs"></i>
                </a>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-10">
                <?php if (!empty($featured_products)): ?>
                    <?php foreach ($featured_products as $product): ?>
                    <div class="bg-white rounded-[2.5rem] overflow-hidden shadow-sm hover:shadow-2xl transition-all duration-500 group border border-amber-50">
                        <div class="relative h-64 overflow-hidden">
                            <img src="../<?php echo $product['image_url'] ?: 'https://images.unsplash.com/photo-1555507036-ab1f4038808a?ixlib=rb-1.2.1&auto=format&fit=crop&w=800&q=80'; ?>" 
                                class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-700">
                            <div class="absolute top-4 right-4 bg-white/90 backdrop-blur-md text-primary px-4 py-1 rounded-full text-xs font-bold shadow-sm">
                                <?php echo htmlspecialchars($product['category_name']); ?>
                            </div>
                        </div>
                        <div class="p-8">
                            <h3 class="text-xl font-bold mb-2 group-hover:text-secondary transition-colors"><?php echo htmlspecialchars($product['name']); ?></h3>
                            <p class="text-gray-500 text-sm mb-6 line-clamp-2"><?php echo htmlspecialchars($product['description']); ?></p>
                            <div class="flex items-center justify-between">
                                <span class="text-xl font-bold text-primary">Rp <?php echo number_format($product['price'], 0, ',', '.'); ?></span>
                                <button class="w-10 h-10 bg-primary text-white rounded-full flex items-center justify-center hover:bg-secondary transition-colors shadow-lg">
                                    <i class="fas fa-plus"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <!-- Fallback items if DB is empty -->
                    <div class="bg-white rounded-[2.5rem] overflow-hidden shadow-sm hover:shadow-2xl transition-all duration-500 group border border-amber-50">
                        <div class="relative h-64 overflow-hidden">
                            <img src="https://images.unsplash.com/photo-1555507036-ab1f4038808a?ixlib=rb-1.2.1&auto=format&fit=crop&w=800&q=80" 
                                class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-700">
                            <div class="absolute top-4 right-4 bg-white/90 backdrop-blur-md text-primary px-4 py-1 rounded-full text-xs font-bold shadow-sm">Bread</div>
                        </div>
                        <div class="p-8">
                            <h3 class="text-xl font-bold mb-2 group-hover:text-secondary transition-colors">Artisan Sourdough</h3>
                            <p class="text-gray-500 text-sm mb-6">Fermented for 24 hours for that perfect tangy crust.</p>
                            <div class="flex items-center justify-between">
                                <span class="text-xl font-bold text-primary">Rp 45.000</span>
                                <button class="w-10 h-10 bg-primary text-white rounded-full flex items-center justify-center hover:bg-secondary transition-colors shadow-lg">
                                    <i class="fas fa-plus"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                    <div class="bg-white rounded-[2.5rem] overflow-hidden shadow-sm hover:shadow-2xl transition-all duration-500 group border border-amber-50">
                        <div class="relative h-64 overflow-hidden">
                            <img src="https://images.unsplash.com/photo-1488477181946-6428a0291777?ixlib=rb-1.2.1&auto=format&fit=crop&w=800&q=80" 
                                class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-700">
                            <div class="absolute top-4 right-4 bg-white/90 backdrop-blur-md text-primary px-4 py-1 rounded-full text-xs font-bold shadow-sm">Cake</div>
                        </div>
                        <div class="p-8">
                            <h3 class="text-xl font-bold mb-2 group-hover:text-secondary transition-colors">Classic Cheesecake</h3>
                            <p class="text-gray-500 text-sm mb-6">Creamy, rich, and topped with fresh seasonal berries.</p>
                            <div class="flex items-center justify-between">
                                <span class="text-xl font-bold text-primary">Rp 120.000</span>
                                <button class="w-10 h-10 bg-primary text-white rounded-full flex items-center justify-center hover:bg-secondary transition-colors shadow-lg">
                                    <i class="fas fa-plus"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <!-- About Section -->
    <section id="about" class="py-24 bg-primary text-white overflow-hidden">
        <div class="max-w-7xl mx-auto px-6">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-20 items-center">
                <div class="relative">
                    <div class="grid grid-cols-2 gap-4">
                        <img src="https://images.unsplash.com/photo-1517433367423-c7e5b0f35086?ixlib=rb-1.2.1&auto=format&fit=crop&w=600&q=80" 
                            class="rounded-3xl h-80 w-full object-cover mt-12 shadow-2xl">
                        <img src="https://images.unsplash.com/photo-1555507036-ab1f4038808a?ixlib=rb-1.2.1&auto=format&fit=crop&w=600&q=80" 
                            class="rounded-3xl h-80 w-full object-cover shadow-2xl">
                    </div>
                    <div class="absolute -bottom-6 -right-6 bg-secondary p-8 rounded-3xl shadow-2xl hidden md:block animate-bounce">
                        <p class="text-4xl font-serif font-bold">15+</p>
                        <p class="text-sm opacity-80">Years of Heritage</p>
                    </div>
                </div>
                <div>
                    <span class="text-secondary font-bold tracking-widest uppercase text-sm">Since 2009</span>
                    <h2 class="text-4xl md:text-6xl font-serif font-bold mt-4 mb-8 leading-tight">Authenticity in <br>Every Crumb</h2>
                    <p class="text-lg opacity-80 mb-8 leading-relaxed font-light">
                        At Brosuli, we believe that baking is an art form. Our journey began in a small family kitchen with a simple mission: to create breads and pastries that evoke warmth and nostalgia.
                    </p>
                    <p class="text-lg opacity-80 mb-10 leading-relaxed font-light">
                        Today, we continue to use traditional slow-fermentation methods and the finest organic ingredients sourced from local farmers to ensure every bite is a celebration of flavor.
                    </p>
                    <div class="grid grid-cols-2 gap-8">
                        <div>
                            <h4 class="text-secondary font-bold text-xl mb-2">Natural Ingredients</h4>
                            <p class="text-sm opacity-60">No preservatives, just pure nature's bounty.</p>
                        </div>
                        <div>
                            <h4 class="text-secondary font-bold text-xl mb-2">Artisan Method</h4>
                            <p class="text-sm opacity-60">Hand-kneaded and baked in stone ovens.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Testimonials -->
    <section class="py-24 bg-cream">
        <div class="max-w-7xl mx-auto px-6">
            <div class="text-center mb-16">
                <span class="text-secondary font-bold tracking-widest uppercase text-sm">Customer Stories</span>
                <h2 class="text-4xl md:text-5xl font-serif font-bold mt-2">What They Say</h2>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-3 gap-10">
                <div class="bg-white p-10 rounded-[2.5rem] shadow-sm border border-amber-50">
                    <div class="text-amber-400 mb-6 flex space-x-1">
                        <i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i>
                    </div>
                    <p class="text-gray-600 italic mb-8 leading-relaxed">"The best sourdough in the city! The crust is perfectly crunchy and the inside is so airy. I come here every morning."</p>
                    <div class="flex items-center space-x-4">
                        <div class="w-12 h-12 bg-gray-200 rounded-full overflow-hidden">
                            <img src="https://i.pravatar.cc/100?u=1" alt="User">
                        </div>
                        <div>
                            <h4 class="font-bold">Sarah Jenkins</h4>
                            <p class="text-xs text-gray-400">Regular Customer</p>
                        </div>
                    </div>
                </div>
                <div class="bg-white p-10 rounded-[2.5rem] shadow-sm border border-amber-50">
                    <div class="text-amber-400 mb-6 flex space-x-1">
                        <i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i>
                    </div>
                    <p class="text-gray-600 italic mb-8 leading-relaxed">"Ordered a custom cake for my daughter's birthday and it was a masterpiece. Not just beautiful, but incredibly delicious."</p>
                    <div class="flex items-center space-x-4">
                        <div class="w-12 h-12 bg-gray-200 rounded-full overflow-hidden">
                            <img src="https://i.pravatar.cc/100?u=2" alt="User">
                        </div>
                        <div>
                            <h4 class="font-bold">Michael Chen</h4>
                            <p class="text-xs text-gray-400">Local Resident</p>
                        </div>
                    </div>
                </div>
                <div class="bg-white p-10 rounded-[2.5rem] shadow-sm border border-amber-50">
                    <div class="text-amber-400 mb-6 flex space-x-1">
                        <i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i>
                    </div>
                    <p class="text-gray-600 italic mb-8 leading-relaxed">"The pastries are divine. The croissant flakes just melt in your mouth. Truly an authentic experience."</p>
                    <div class="flex items-center space-x-4">
                        <div class="w-12 h-12 bg-gray-200 rounded-full overflow-hidden">
                            <img src="https://i.pravatar.cc/100?u=3" alt="User">
                        </div>
                        <div>
                            <h4 class="font-bold">Emma Wilson</h4>
                            <p class="text-xs text-gray-400">Pastry Lover</p>
                        </div>
                    </div>
                </div>
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
                        Serving the community with fresh, artisan bakes since 2009. Quality and taste are our priority.
                    </p>
                    <div class="flex space-x-4">
                        <a href="#" class="w-10 h-10 bg-cream text-primary rounded-full flex items-center justify-center hover:bg-primary hover:text-white transition-all"><i class="fab fa-instagram"></i></a>
                        <a href="#" class="w-10 h-10 bg-cream text-primary rounded-full flex items-center justify-center hover:bg-primary hover:text-white transition-all"><i class="fab fa-facebook-f"></i></a>
                        <a href="#" class="w-10 h-10 bg-cream text-primary rounded-full flex items-center justify-center hover:bg-primary hover:text-white transition-all"><i class="fab fa-whatsapp"></i></a>
                    </div>
                </div>
                
                <div>
                    <h4 class="font-bold text-lg mb-8">Quick Links</h4>
                    <ul class="space-y-4 text-gray-500">
                        <li><a href="#" class="hover:text-secondary transition-colors">Home</a></li>
                        <li><a href="#" class="hover:text-secondary transition-colors">Our Menu</a></li>
                        <li><a href="#" class="hover:text-secondary transition-colors">Our Story</a></li>
                        <li><a href="#" class="hover:text-secondary transition-colors">Locations</a></li>
                    </ul>
                </div>
                
                <div>
                    <h4 class="font-bold text-lg mb-8">Opening Hours</h4>
                    <ul class="space-y-4 text-gray-500">
                        <li class="flex justify-between"><span>Mon - Fri</span> <span>07:00 - 20:00</span></li>
                        <li class="flex justify-between"><span>Sat - Sun</span> <span>08:00 - 21:00</span></li>
                        <li class="flex justify-between text-secondary"><span>Bakery Fresh</span> <span>Everyday</span></li>
                    </ul>
                </div>
                
                <div>
                    <h4 class="font-bold text-lg mb-8">Newsletter</h4>
                    <p class="text-sm text-gray-500 mb-6">Join our bakery club for fresh updates and exclusive offers.</p>
                    <form class="relative">
                        <input type="email" placeholder="Your email address" class="w-full bg-cream border-none rounded-full px-6 py-4 outline-none focus:ring-2 focus:ring-secondary transition-all">
                        <button class="absolute right-2 top-2 bg-primary text-white w-10 h-10 rounded-full hover:bg-secondary transition-colors">
                            <i class="fas fa-paper-plane text-sm"></i>
                        </button>
                    </form>
                </div>
            </div>
            
            <div class="pt-12 border-t border-amber-50 text-center text-gray-400 text-sm">
                <p>&copy; 2026 Brosuli Bakery. All rights reserved. <br class="md:hidden"> Designed for the finest tastes.</p>
            </div>
        </div>
    </footer>

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
    </script>
</body>
</html>
