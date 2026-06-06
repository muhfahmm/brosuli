<!-- Categories Section -->
<section id="categories" class="py-24 bg-white">
    <div class="max-w-7xl mx-auto px-6">
        <div class="text-center mb-16 category-header">
            <span class="text-secondary font-bold tracking-widest uppercase text-sm">Spesialisasi Kami</span>
            <h2 class="text-4xl md:text-5xl font-serif font-bold mt-2">Dipanggang dengan Kasih Sayang</h2>
        </div>
        
        <!-- Desktop Grid View -->
        <div class="hidden md:grid grid-cols-2 md:grid-cols-4 gap-8 category-grid">
            <?php
            $cat_icons = [
                'Roti' => 'fa-bread-slice',
                'Kue' => 'fa-birthday-cake',
                'Pastry' => 'fa-croissant',
                'Kue Kering' => 'fa-cookie-bite'
            ];
            foreach ($categories as $category):
                $icon = isset($cat_icons[$category['name']]) ? $cat_icons[$category['name']] : 'fa-bread-slice';
                $category_slug = strtolower(str_replace(' ', '-', $category['name']));
            ?>
            <a href="frontend/catalog.php?category=<?php echo urlencode($category['name']); ?>" class="group bg-cream p-8 rounded-3xl text-center hover:bg-primary hover:text-white transition-all duration-500 cursor-pointer shadow-sm hover:shadow-2xl transform hover:-translate-y-2 no-underline">
                <div class="w-16 h-16 bg-white text-secondary rounded-2xl flex items-center justify-center text-3xl mb-6 mx-auto group-hover:scale-110 transition-transform shadow-inner group-hover:bg-white/20 group-hover:text-white">
                    <i class="fas <?php echo $icon; ?>"></i>
                </div>
                <h3 class="text-xl font-bold mb-2 group-hover:text-white"><?php echo htmlspecialchars($category['name']); ?></h3>
                <p class="text-sm opacity-60 group-hover:opacity-90 group-hover:text-white">Jelajahi koleksi lezat kami dari <?php echo strtolower($category['name']); ?>.</p>
            </a>
            <?php endforeach; ?>
        </div>

        <!-- Mobile Tab Horizontal Scroll View -->
        <div class="md:hidden">
            <div class="relative">
                <!-- Left Scroll Button -->
                <button onclick="scrollCategories('left')" class="absolute left-0 top-1/2 -translate-y-1/2 z-10 bg-white shadow-xl rounded-full w-10 h-10 flex items-center justify-center hover:bg-secondary hover:text-white transition-all">
                    <i class="fas fa-chevron-left"></i>
                </button>

                <!-- Scrollable Container -->
                <div id="categories-scroll" class="overflow-x-auto scrollbar-hide">
                    <div class="flex gap-4 pb-4 min-w-max px-12">
                        <?php
                        foreach ($categories as $category):
                            $icon = isset($cat_icons[$category['name']]) ? $cat_icons[$category['name']] : 'fa-bread-slice';
                        ?>
                        <a href="frontend/catalog.php?category=<?php echo urlencode($category['name']); ?>" class="flex-shrink-0 w-40 bg-cream p-6 rounded-2xl text-center hover:bg-primary hover:text-white transition-all duration-300 shadow-sm hover:shadow-xl transform hover:-translate-y-1 no-underline">
                            <div class="w-12 h-12 bg-white text-secondary rounded-xl flex items-center justify-center text-2xl mb-4 mx-auto group-hover:scale-105 transition-transform shadow-inner">
                                <i class="fas <?php echo $icon; ?>"></i>
                            </div>
                            <h3 class="text-base font-bold mb-1 text-primary hover:text-white"><?php echo htmlspecialchars($category['name']); ?></h3>
                            <p class="text-xs opacity-60 hover:opacity-90">Lihat koleksi</p>
                        </a>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- Right Scroll Button -->
                <button onclick="scrollCategories('right')" class="absolute right-0 top-1/2 -translate-y-1/2 z-10 bg-white shadow-xl rounded-full w-10 h-10 flex items-center justify-center hover:bg-secondary hover:text-white transition-all">
                    <i class="fas fa-chevron-right"></i>
                </button>
            </div>
        </div>
    </div>
</section>

<style>
    .scrollbar-hide::-webkit-scrollbar {
        display: none;
    }
    .scrollbar-hide {
        -ms-overflow-style: none;
        scrollbar-width: none;
    }
</style>

<script>
    function scrollCategories(direction) {
        const container = document.getElementById('categories-scroll');
        const scrollAmount = 280; // width of item (160px) + gap (4) + padding
        
        if (direction === 'left') {
            container.scrollBy({
                left: -scrollAmount,
                behavior: 'smooth'
            });
        } else if (direction === 'right') {
            container.scrollBy({
                left: scrollAmount,
                behavior: 'smooth'
            });
        }
    }

    // Optional: Auto-hide scroll buttons based on scroll position
    const container = document.getElementById('categories-scroll');
    if (container) {
        container.addEventListener('scroll', function() {
            const leftBtn = document.querySelectorAll('button')[0];
            const rightBtn = document.querySelectorAll('button')[1];
            
            if (this.scrollLeft <= 0) {
                leftBtn?.classList.add('opacity-30', 'cursor-not-allowed');
            } else {
                leftBtn?.classList.remove('opacity-30', 'cursor-not-allowed');
            }
            
            if (this.scrollLeft >= this.scrollWidth - this.clientWidth) {
                rightBtn?.classList.add('opacity-30', 'cursor-not-allowed');
            } else {
                rightBtn?.classList.remove('opacity-30', 'cursor-not-allowed');
            }
        });
    }
</script>
