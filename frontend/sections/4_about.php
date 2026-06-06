<!-- About Us Section (Dinamis dari tb_about_section) -->
<section id="about" class="py-24 bg-primary text-white">
    <div class="max-w-7xl mx-auto px-6">
        <?php
        // Get about section data from database
        $about_stmt = $pdo->query("SELECT * FROM tb_about_section WHERE is_active = 1 LIMIT 1");
        $about_data = $about_stmt->fetch();
        
        if ($about_data):
        ?>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-16 items-center">
            <div>
                <span class="text-secondary font-bold tracking-widest uppercase text-sm"><?php echo htmlspecialchars($about_data['section_title']); ?></span>
                <h2 class="text-4xl md:text-5xl font-serif font-bold mt-4 mb-6"><?php echo htmlspecialchars($about_data['main_heading']); ?></h2>
                <p class="text-white/80 leading-relaxed mb-6 text-lg">
                    <?php echo nl2br(htmlspecialchars($about_data['main_description'])); ?>
                </p>
                <p class="text-white/80 leading-relaxed mb-8 text-lg">
                    <?php echo nl2br(htmlspecialchars($about_data['description_2'])); ?>
                </p>
                <a href="<?php echo htmlspecialchars($about_data['button_link']); ?>" class="inline-block bg-secondary hover:bg-amber-600 text-primary px-8 py-4 rounded-full font-bold transition-all shadow-lg">
                    <?php echo htmlspecialchars($about_data['button_text']); ?>
                </a>
            </div>
            <div class="space-y-8">
                <!-- Feature 1 -->
                <div class="bg-white/10 backdrop-blur-md p-8 rounded-2xl border border-white/20">
                    <div class="flex items-start space-x-4">
                        <div class="w-12 h-12 bg-secondary text-primary rounded-full flex items-center justify-center text-xl flex-shrink-0 font-bold">
                            ✓
                        </div>
                        <div>
                            <h3 class="text-xl font-bold mb-2"><?php echo htmlspecialchars($about_data['feature_1_title']); ?></h3>
                            <p class="text-white/70"><?php echo htmlspecialchars($about_data['feature_1_description']); ?></p>
                        </div>
                    </div>
                </div>
                <!-- Feature 2 -->
                <div class="bg-white/10 backdrop-blur-md p-8 rounded-2xl border border-white/20">
                    <div class="flex items-start space-x-4">
                        <div class="w-12 h-12 bg-secondary text-primary rounded-full flex items-center justify-center text-xl flex-shrink-0 font-bold">
                            ✓
                        </div>
                        <div>
                            <h3 class="text-xl font-bold mb-2"><?php echo htmlspecialchars($about_data['feature_2_title']); ?></h3>
                            <p class="text-white/70"><?php echo htmlspecialchars($about_data['feature_2_description']); ?></p>
                        </div>
                    </div>
                </div>
                <!-- Feature 3 -->
                <div class="bg-white/10 backdrop-blur-md p-8 rounded-2xl border border-white/20">
                    <div class="flex items-start space-x-4">
                        <div class="w-12 h-12 bg-secondary text-primary rounded-full flex items-center justify-center text-xl flex-shrink-0 font-bold">
                            ✓
                        </div>
                        <div>
                            <h3 class="text-xl font-bold mb-2"><?php echo htmlspecialchars($about_data['feature_3_title']); ?></h3>
                            <p class="text-white/70"><?php echo htmlspecialchars($about_data['feature_3_description']); ?></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </div>
</section>
