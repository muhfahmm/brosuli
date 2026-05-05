<?php
$current_page = basename($_SERVER['PHP_SELF']);
?>
<!-- Sidebar -->
<aside class="w-64 bg-[#4A2C2A] text-white hidden md:flex flex-col sticky top-0 h-screen">
    <div class="p-6">
        <div class="flex items-center space-x-3 mb-1">
            <div class="w-8 h-8 bg-amber-500 rounded-lg flex items-center justify-center text-white font-bold italic">B</div>
            <h1 class="text-2xl font-bold tracking-wider">Brosuli</h1>
        </div>
        <p class="text-amber-300 text-sm ml-11">Admin Panel</p>
    </div>
    
    <nav class="flex-1 px-4 py-4 space-y-2 overflow-y-auto">
        <a href="../products/index.php" class="flex items-center space-x-3 p-3 rounded-xl transition-all <?php echo ($current_page == 'index.php') ? 'bg-white/10 text-white shadow-lg' : 'text-amber-100/70 hover:bg-white/5 hover:text-white'; ?>">
            <i class="fas fa-bread-slice w-5 text-center"></i>
            <span class="font-medium">Produk</span>
        </a>
        
        <a href="../products/categories.php" class="flex items-center space-x-3 p-3 rounded-xl transition-all <?php echo ($current_page == 'categories.php') ? 'bg-white/10 text-white shadow-lg' : 'text-amber-100/70 hover:bg-white/5 hover:text-white'; ?>">
            <i class="fas fa-tags w-5 text-center"></i>
            <span class="font-medium">Kategori</span>
        </a>
        
        <?php if (($_SESSION['admin_role'] ?? 'superadmin') == 'superadmin'): ?>
        <a href="../content/banners.php" class="flex items-center space-x-3 p-3 rounded-xl transition-all <?php echo ($current_page == 'banners.php') ? 'bg-white/10 text-white shadow-lg' : 'text-amber-100/70 hover:bg-white/5 hover:text-white'; ?>">
            <i class="fas fa-images w-5 text-center"></i>
            <span class="font-medium">Banner</span>
        </a>
        
        <a href="../products/best_sellers.php" class="flex items-center space-x-3 p-3 rounded-xl transition-all <?php echo ($current_page == 'best_sellers.php') ? 'bg-white/10 text-white shadow-lg' : 'text-amber-100/70 hover:bg-white/5 hover:text-white'; ?>">
            <i class="fas fa-star w-5 text-center"></i>
            <span class="font-medium">Best Sellers</span>
        </a>
        <?php endif; ?>
        
        <a href="../orders/orders.php" class="flex items-center space-x-3 p-3 rounded-xl transition-all <?php echo ($current_page == 'orders.php') ? 'bg-white/10 text-white shadow-lg' : 'text-amber-100/70 hover:bg-white/5 hover:text-white'; ?>">
            <i class="fas fa-shopping-cart w-5 text-center"></i>
            <span class="font-medium">Pesanan</span>
        </a>
        
        <a href="../orders/cashier.php" class="flex items-center space-x-3 p-3 rounded-xl transition-all <?php echo ($current_page == 'cashier.php') ? 'bg-white/10 text-white shadow-lg' : 'text-amber-100/70 hover:bg-white/5 hover:text-white'; ?>">
            <i class="fas fa-cash-register w-5 text-center"></i>
            <span class="font-medium">Kasir (POS)</span>
        </a>

        <?php if (($_SESSION['admin_role'] ?? 'superadmin') == 'superadmin'): ?>
        <a href="../admins/index.php" class="flex items-center space-x-3 p-3 rounded-xl transition-all <?php echo ($current_page == 'admins.php') ? 'bg-white/10 text-white shadow-lg' : 'text-amber-100/70 hover:bg-white/5 hover:text-white'; ?>">
            <i class="fas fa-user-shield w-5 text-center"></i>
            <span class="font-medium">Kelola Admin</span>
        </a>
        <?php endif; ?>
        
        <div class="pt-6 mt-6 border-t border-white/10">
            <a href="../../frontend/index.php" target="_blank" class="flex items-center space-x-3 p-3 rounded-xl text-amber-100/50 hover:bg-white/5 hover:text-white transition-all">
                <i class="fas fa-external-link-alt w-5 text-center"></i>
                <span class="font-medium">Lihat Website</span>
            </a>
        </div>
    </nav>
    
    <div class="p-4 border-t border-white/10">
        <a href="../auth/logout.php" class="flex items-center space-x-3 p-3 rounded-xl text-red-300 hover:bg-red-500/10 hover:text-red-100 transition-all">
            <i class="fas fa-sign-out-alt w-5 text-center"></i>
            <span class="font-medium">Logout</span>
        </a>
    </div>
</aside>
