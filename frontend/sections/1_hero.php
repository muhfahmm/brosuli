<!-- Hero Section with Swiper (Dinamis dari tb_banners) -->
<section id="home" class="relative h-[85vh] md:h-screen pt-16 overflow-hidden">
    <div class="swiper heroSwiper h-full w-full">
        <div class="swiper-wrapper">
            <?php 
            // Get active banners from database
            $banner_stmt = $pdo->query("SELECT * FROM tb_banners WHERE is_active = 1 ORDER BY created_at DESC");
            $active_banners = $banner_stmt->fetchAll();
            
            if (!empty($active_banners)):
                foreach ($active_banners as $banner): 
            ?>
            <div class="swiper-slide hero-slide flex items-center justify-center text-white" 
                 style="background-image: url('<?php echo htmlspecialchars($banner['image_url']); ?>');">
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
            <?php 
                endforeach;
            else:
            ?>
            <div class="swiper-slide hero-slide flex items-center justify-center text-white bg-primary">
                <div class="relative text-center px-6 max-w-4xl z-10">
                    <h1 class="text-4xl md:text-7xl font-serif font-bold mb-6 leading-tight">Selamat Datang di Brosuli</h1>
                    <p class="text-base md:text-xl mb-10 opacity-90 max-w-2xl mx-auto font-light leading-relaxed">Pengalaman Bakery Otentik Sejak 2009</p>
                    <a href="frontend/catalog.php" class="bg-secondary text-white px-10 py-4 rounded-full font-bold text-lg hover:bg-amber-600 transition-all">Jelajahi Menu</a>
                </div>
            </div>
            <?php endif; ?>
        </div>
        
        <?php if (!empty($active_banners) && count($active_banners) > 1): ?>
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
