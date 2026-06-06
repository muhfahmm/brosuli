<!-- Why Choose Us Section (Dinamis dari tb_whyus_section) -->
<section id="why-us" class="py-24 bg-white">
    <div class="max-w-7xl mx-auto px-6">
        <?php
        // Get why us section data from database
        $whyus_stmt = $pdo->query("SELECT * FROM tb_whyus_section WHERE is_active = 1 LIMIT 1");
        $whyus_data = $whyus_stmt->fetch();
        
        if ($whyus_data):
        ?>
        <div class="text-center mb-16">
            <span class="text-secondary font-bold tracking-widest uppercase text-sm"><?php echo htmlspecialchars($whyus_data['section_title']); ?></span>
            <h2 class="text-4xl md:text-5xl font-serif font-bold mt-4 text-primary"><?php echo htmlspecialchars($whyus_data['main_heading']); ?></h2>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-10">
            <!-- Feature 1 -->
            <div class="bg-cream rounded-3xl p-10 text-center hover:shadow-2xl hover:scale-105 transition-all duration-300 border border-secondary/10">
                <div class="w-20 h-20 bg-secondary text-white rounded-full flex items-center justify-center text-4xl mx-auto mb-6">
                    <i class="fas <?php echo htmlspecialchars($whyus_data['feature_1_icon']); ?>"></i>
                </div>
                <h3 class="text-2xl font-bold text-primary mb-4"><?php echo htmlspecialchars($whyus_data['feature_1_title']); ?></h3>
                <p class="text-gray-500 leading-relaxed"><?php echo htmlspecialchars($whyus_data['feature_1_description']); ?></p>
            </div>
            <!-- Feature 2 -->
            <div class="bg-cream rounded-3xl p-10 text-center hover:shadow-2xl hover:scale-105 transition-all duration-300 border border-secondary/10">
                <div class="w-20 h-20 bg-secondary text-white rounded-full flex items-center justify-center text-4xl mx-auto mb-6">
                    <i class="fas <?php echo htmlspecialchars($whyus_data['feature_2_icon']); ?>"></i>
                </div>
                <h3 class="text-2xl font-bold text-primary mb-4"><?php echo htmlspecialchars($whyus_data['feature_2_title']); ?></h3>
                <p class="text-gray-500 leading-relaxed"><?php echo htmlspecialchars($whyus_data['feature_2_description']); ?></p>
            </div>
            <!-- Feature 3 -->
            <div class="bg-cream rounded-3xl p-10 text-center hover:shadow-2xl hover:scale-105 transition-all duration-300 border border-secondary/10">
                <div class="w-20 h-20 bg-secondary text-white rounded-full flex items-center justify-center text-4xl mx-auto mb-6">
                    <i class="fas <?php echo htmlspecialchars($whyus_data['feature_3_icon']); ?>"></i>
                </div>
                <h3 class="text-2xl font-bold text-primary mb-4"><?php echo htmlspecialchars($whyus_data['feature_3_title']); ?></h3>
                <p class="text-gray-500 leading-relaxed"><?php echo htmlspecialchars($whyus_data['feature_3_description']); ?></p>
            </div>
        </div>
        <?php endif; ?>
    </div>
</section>
