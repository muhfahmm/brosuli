<!-- Contact Us Section (Dinamis dari tb_contact_section) -->
<section id="contact-form" class="py-24 bg-cream">
    <div class="max-w-4xl mx-auto px-6">
        <?php
        // Get contact section data from database
        $contact_stmt = $pdo->query("SELECT * FROM tb_contact_section WHERE is_active = 1 LIMIT 1");
        $contact_data = $contact_stmt->fetch();
        
        if ($contact_data):
        ?>
        <div class="text-center mb-16">
            <span class="text-secondary font-bold tracking-widest uppercase text-sm"><?php echo htmlspecialchars($contact_data['section_title']); ?></span>
            <h2 class="text-4xl md:text-5xl font-serif font-bold mt-4 text-primary"><?php echo htmlspecialchars($contact_data['main_heading']); ?></h2>
            <p class="text-gray-500 text-lg mt-4"><?php echo htmlspecialchars($contact_data['subtitle']); ?></p>
        </div>

        <div class="bg-white rounded-3xl p-10 shadow-lg border border-amber-50">
            <?php if ($contact_success): ?>
            <div class="mb-6 p-4 bg-green-100 text-green-700 rounded-lg flex items-center space-x-3 animate-pulse">
                <i class="fas fa-check-circle"></i>
                <span>✅ Pesan Anda berhasil dikirim! Kami akan merespons dalam 24 jam.</span>
            </div>
            <?php endif; ?>
            <form method="POST" class="space-y-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-primary font-bold mb-3">Nama Anda *</label>
                        <input type="text" name="contact_name" placeholder="Masukkan nama lengkap" required
                            class="w-full bg-cream border-2 border-gray-200 rounded-2xl px-6 py-4 focus:outline-none focus:border-secondary transition-colors">
                    </div>
                    <div>
                        <label class="block text-primary font-bold mb-3">Email *</label>
                        <input type="email" name="contact_email" placeholder="email@example.com" required
                            class="w-full bg-cream border-2 border-gray-200 rounded-2xl px-6 py-4 focus:outline-none focus:border-secondary transition-colors">
                    </div>
                </div>

                <div>
                    <label class="block text-primary font-bold mb-3">Pesan Anda *</label>
                    <textarea name="contact_message" placeholder="Tulis pesan atau pertanyaan Anda di sini..." rows="6" required
                        class="w-full bg-cream border-2 border-gray-200 rounded-2xl px-6 py-4 focus:outline-none focus:border-secondary transition-colors resize-none"></textarea>
                </div>

                <button type="submit" class="w-full bg-primary hover:bg-secondary text-white font-bold py-4 rounded-2xl transition-all shadow-lg transform hover:scale-105">
                    <i class="fas fa-paper-plane mr-2"></i>
                    <?php echo htmlspecialchars($contact_data['form_submit_button']); ?>
                </button>
            </form>

            <p class="text-center text-gray-400 text-sm mt-6">
                <?php echo htmlspecialchars($contact_data['contact_note']); ?>: 
                <a href="https://wa.me/<?php echo htmlspecialchars($contact_data['whatsapp_number']); ?>" target="_blank" class="text-green-500 hover:text-green-600 font-bold">WhatsApp</a> 
                | 
                <a href="tel:<?php echo htmlspecialchars($contact_data['phone_number']); ?>" class="text-secondary hover:text-amber-700 font-bold">Telepon</a>
            </p>
        </div>
        <?php endif; ?>
    </div>
</section>
