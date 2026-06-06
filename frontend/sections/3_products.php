<!-- Featured Products -->
<section id="products" class="py-24">
    <div class="max-w-7xl mx-auto px-6">
        <div class="flex flex-col md:flex-row md:items-end justify-between mb-16 product-header">
            <div>
                <span class="text-secondary font-bold tracking-widest uppercase text-sm">Terlaris</span>
                <h2 class="text-4xl md:text-5xl font-serif font-bold mt-2 text-primary">Kreasi Unggulan</h2>
            </div>
            <a href="frontend/catalog.php" class="mt-6 md:mt-0 text-primary font-bold hover:text-secondary flex items-center space-x-2 border-b-2 border-primary hover:border-secondary transition-all pb-1">
                <span>Lihat Semua Produk</span>
                <i class="fas fa-arrow-right text-xs"></i>
            </a>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-10 product-grid">
            <?php if (!empty($best_sellers)): ?>
                <?php foreach ($best_sellers as $product): ?>
                <div class="bg-white rounded-[2.5rem] overflow-hidden shadow-sm hover:shadow-2xl transition-all duration-500 group border border-amber-50">
                    <div class="relative h-64 overflow-hidden bg-cream">
                        <?php if ($product['image_url']): ?>
                            <img src="<?php echo $product['image_url']; ?>" 
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
                            <div class="flex flex-col">
                                <span class="text-xl font-bold text-primary">Rp <?php echo number_format($product['price'], 0, ',', '.'); ?></span>
                                <?php if ($selected_branch_id): ?>
                                <span class="text-[10px] font-bold uppercase tracking-tighter <?php echo $product['stock'] > 0 ? 'text-green-500' : 'text-red-500'; ?>">
                                    <?php echo $product['stock'] > 0 ? 'Stok Tersedia (' . $product['stock'] . ')' : 'Stok Habis'; ?>
                                </span>
                                <?php endif; ?>
                            </div>
                            <?php if (!$selected_branch_id || $product['stock'] > 0): ?>
                            <button onclick='handleAddToCart(<?php echo json_encode(["id" => $product["id"], "name" => $product["name"], "price" => (float)$product["price"], "image" => $product["image_url"]]); ?>)' 
                                class="w-10 h-10 bg-primary text-white rounded-full flex items-center justify-center hover:bg-secondary transition-colors shadow-lg">
                                <i class="fas fa-plus"></i>
                            </button>
                            <?php else: ?>
                            <button disabled class="w-10 h-10 bg-gray-200 text-gray-400 rounded-full flex items-center justify-center cursor-not-allowed">
                                <i class="fas fa-times"></i>
                            </button>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="col-span-full py-12 text-center text-primary/40">
                    <i class="fas fa-bread-slice text-4xl mb-4 block"></i>
                    <p>Belum ada produk unggulan yang ditampilkan.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</section>
