<!-- CTA Section (Dinamis dari tb_cta_section) -->
<section class="py-20 <?php 
    // Get CTA section data
    $cta_stmt = $pdo->query("SELECT * FROM tb_cta_section WHERE is_active = 1 LIMIT 1");
    $cta_data = $cta_stmt->fetch();
    echo $cta_data ? htmlspecialchars($cta_data['background_color']) : 'bg-primary'; 
?>">
    <div class="max-w-4xl mx-auto px-6 text-center">
        <?php if ($cta_data): ?>
        <h2 class="text-4xl md:text-5xl font-serif font-bold <?php echo htmlspecialchars($cta_data['text_color']); ?> mb-6">
            <?php echo htmlspecialchars($cta_data['main_heading']); ?>
        </h2>
        <p class="<?php echo htmlspecialchars($cta_data['text_color']); ?>/80 text-lg mb-10 leading-relaxed">
            <?php echo htmlspecialchars($cta_data['subtitle']); ?>
        </p>
        <div class="flex flex-col sm:flex-row justify-center items-center gap-4 sm:gap-6">
            <a href="<?php echo htmlspecialchars($cta_data['button_1_link']); ?>" class="inline-block bg-secondary hover:bg-amber-600 text-primary px-10 py-5 rounded-full font-bold text-lg transition-all shadow-xl transform hover:scale-105">
                <i class="fas <?php echo htmlspecialchars($cta_data['button_1_icon']); ?> mr-2"></i>
                <?php echo htmlspecialchars($cta_data['button_1_text']); ?>
            </a>
            <a href="<?php echo htmlspecialchars($cta_data['button_2_link']); ?>" class="inline-block bg-white/20 hover:bg-white/30 <?php echo htmlspecialchars($cta_data['text_color']); ?> px-10 py-5 rounded-full font-bold text-lg transition-all shadow-xl transform hover:scale-105 border-2 border-white/30">
                <i class="fas <?php echo htmlspecialchars($cta_data['button_2_icon']); ?> mr-2"></i>
                <?php echo htmlspecialchars($cta_data['button_2_text']); ?>
            </a>
        </div>
        <?php else: ?>
        <h2 class="text-4xl md:text-5xl font-serif font-bold text-white mb-6">Siap Merasakan Kelezatan?</h2>
        <p class="text-white/80 text-lg mb-10 leading-relaxed">
            Jangan lewatkan kesempatan untuk menikmati brownies premium Brosuli yang dibuat dengan cinta dan bahan terbaik.
        </p>
        <a href="frontend/catalog.php" class="inline-block bg-secondary hover:bg-amber-600 text-primary px-10 py-5 rounded-full font-bold text-lg transition-all shadow-xl transform hover:scale-105">
            <i class="fas fa-shopping-bag mr-2"></i>
            Pesan Sekarang
        </a>
        <?php endif; ?>
    </div>
</section>
