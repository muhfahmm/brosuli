<!-- Testimonials Section (Dinamis dari tb_testimonials) -->
<section id="testimonials" class="py-24 bg-gradient-to-b from-cream to-white">
    <div class="max-w-7xl mx-auto px-6">
        <?php
        // Get testimonials section info
        $testimonial_info_stmt = $pdo->query("SELECT section_title, main_heading FROM tb_testimonials LIMIT 1");
        $testimonial_info = $testimonial_info_stmt->fetch();
        
        // Get active testimonials from database
        $testimonial_stmt = $pdo->query("SELECT * FROM tb_testimonials WHERE is_active = 1 ORDER BY display_order ASC LIMIT 3");
        $testimonials_data = $testimonial_stmt->fetchAll();
        
        if (!empty($testimonials_data)):
        ?>
        <div class="text-center mb-16">
            <span class="text-secondary font-bold tracking-widest uppercase text-sm">Kepuasan Pelanggan</span>
            <h2 class="text-4xl md:text-5xl font-serif font-bold mt-4 text-primary">Apa Kata Pelanggan Kami?</h2>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-10">
            <?php foreach ($testimonials_data as $testimonial): ?>
            <div class="bg-white rounded-3xl p-10 shadow-lg border-l-4 border-secondary">
                <div class="flex items-center mb-4">
                    <div class="flex space-x-1">
                        <?php for ($i = 0; $i < 5; $i++): ?>
                            <i class="fas fa-star <?php echo $i < $testimonial['rating'] ? 'text-secondary' : 'text-gray-300'; ?> text-lg"></i>
                        <?php endfor; ?>
                    </div>
                    <span class="text-xs font-bold text-gray-400 ml-2"><?php echo $testimonial['rating']; ?>/5</span>
                </div>
                <p class="text-gray-600 leading-relaxed mb-6 italic">
                    "<?php echo htmlspecialchars($testimonial['testimonial_text']); ?>"
                </p>
                <div class="flex items-center space-x-3">
                    <div class="w-12 h-12 bg-secondary rounded-full flex items-center justify-center text-white font-bold">
                        <?php echo htmlspecialchars($testimonial['customer_initial']); ?>
                    </div>
                    <div>
                        <h4 class="font-bold text-primary"><?php echo htmlspecialchars($testimonial['customer_name']); ?></h4>
                        <p class="text-xs text-gray-400"><?php echo htmlspecialchars($testimonial['customer_type']); ?></p>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php else: ?>
        <div class="text-center py-12 text-gray-500">
            <i class="fas fa-comments text-4xl mb-4 block opacity-30"></i>
            <p>Belum ada testimonial.</p>
        </div>
        <?php endif; ?>
    </div>
</section>
