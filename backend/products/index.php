<?php
require_once '../auth/auth.php';
require_once '../../db/db.php';
requireLogin();

// Auto-initialize branch_inventory if missing
try {
    $pdo->exec("CREATE TABLE IF NOT EXISTS tb_branch_inventory (
        id INT AUTO_INCREMENT PRIMARY KEY,
        branch_id INT NOT NULL,
        product_id INT NOT NULL,
        stock INT DEFAULT 0,
        UNIQUE KEY (branch_id, product_id),
        FOREIGN KEY (branch_id) REFERENCES tb_branches(id) ON DELETE CASCADE,
        FOREIGN KEY (product_id) REFERENCES tb_products(id) ON DELETE CASCADE
    )");
} catch (Exception $e) {}

// Fetch products with branch-specific stock
$admin_branch_id = $_SESSION['admin_branch_id'] ?? null;
if ($admin_branch_id) {
    // Branch Admin: Only show products that ARE added to this branch (INNER JOIN)
    $stmt = $pdo->prepare("SELECT p.*, c.name as category_name, bi.stock as branch_stock 
                         FROM tb_products p 
                         INNER JOIN tb_branch_inventory bi ON p.id = bi.product_id 
                         LEFT JOIN tb_categories c ON p.category_id = c.id 
                         WHERE bi.branch_id = ?
                         ORDER BY p.created_at DESC");
    $stmt->execute([$admin_branch_id]);
    $products = $stmt->fetchAll();

    // Fetch products NOT YET in this branch (from catalog)
    $stmt_catalog = $pdo->prepare("SELECT p.*, c.name as category_name 
                                  FROM tb_products p 
                                  LEFT JOIN tb_categories c ON p.category_id = c.id 
                                  WHERE p.id NOT IN (SELECT product_id FROM tb_branch_inventory WHERE branch_id = ?)
                                  ORDER BY p.name ASC");
    $stmt_catalog->execute([$admin_branch_id]);
    $catalog_products = $stmt_catalog->fetchAll();
} else {
    // Superadmin: Fetch everything
    $stmt = $pdo->query("SELECT p.*, c.name as category_name, (SELECT SUM(stock) FROM tb_branch_inventory WHERE product_id = p.id) as branch_stock 
                        FROM tb_products p 
                        LEFT JOIN tb_categories c ON p.category_id = c.id 
                        ORDER BY p.created_at DESC");
    $products = $stmt->fetchAll();
}

// Handle Claiming Product from Catalog
if (isset($_POST['claim_product']) && $admin_branch_id) {
    $p_id = $_POST['product_id'];
    $stmt = $pdo->prepare("INSERT IGNORE INTO tb_branch_inventory (branch_id, product_id, stock) VALUES (?, ?, 0)");
    $stmt->execute([$admin_branch_id, $p_id]);
    header('Location: index.php');
    exit();
}

// Handle AJAX stock update
if (isset($_POST['ajax_update_stock']) && $admin_branch_id) {
    $p_id = $_POST['product_id'];
    $new_stock = $_POST['stock'];
    
    $stmt = $pdo->prepare("INSERT INTO tb_branch_inventory (branch_id, product_id, stock) VALUES (?, ?, ?) 
                         ON DUPLICATE KEY UPDATE stock = ?");
    $success = $stmt->execute([$admin_branch_id, $p_id, $new_stock, $new_stock]);
    
    echo json_encode(['success' => $success]);
    exit();
}

// Fetch all categories for the modal
$cat_stmt = $pdo->query("SELECT * FROM tb_categories ORDER BY name ASC");
$categories = $cat_stmt->fetchAll();

// Deletion logic
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $stmt = $pdo->prepare("DELETE FROM tb_products WHERE id = ?");
    $stmt->execute([$id]);
    header('Location: index.php');
    exit();
}

// Fetch branch info
$branch_name = 'Seluruh Cabang (Pusat)';
if (!empty($_SESSION['admin_branch_id'])) {
    $stmt = $pdo->prepare("SELECT name FROM tb_branches WHERE id = ?");
    $stmt->execute([$_SESSION['admin_branch_id']]);
    $branch_name = $stmt->fetchColumn() ?: 'Cabang Tidak Diketahui';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Brosuli Admin</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body { font-family: 'Outfit', sans-serif; }
    </style>
</head>
<body class="bg-[#FDFCF6] min-h-screen flex">
    <?php include '../includes/sidebar.php'; ?>

    <!-- Main Content -->
    <main class="flex-1 flex flex-col">
        <!-- Header -->
        <header class="bg-white shadow-sm px-8 py-4 flex justify-between items-center border-b border-gray-100">
            <div class="flex flex-col">
                <h2 class="text-xl font-bold text-gray-800 tracking-tight">Manajemen Produk</h2>
                <div class="flex items-center gap-2 mt-0.5">
                    <span class="flex items-center gap-1.5 text-[11px] font-bold uppercase tracking-wider bg-amber-50 text-amber-700 px-2 py-0.5 rounded-md border border-amber-100">
                        <i class="fas fa-store text-[9px]"></i>
                        <?php echo htmlspecialchars($branch_name); ?>
                    </span>
                    <span class="text-[11px] font-bold uppercase tracking-wider <?php echo ($_SESSION['admin_role'] ?? '') == 'superadmin' ? 'bg-purple-50 text-purple-700 border-purple-100' : 'bg-blue-50 text-blue-700 border-blue-100'; ?> px-2 py-0.5 rounded-md border">
                        <?php echo ($_SESSION['admin_role'] ?? 'superadmin'); ?>
                    </span>
                </div>
            </div>
            <div class="flex items-center space-x-6">
                <a href="print_labels.php" class="text-amber-600 hover:text-amber-800 font-semibold flex items-center space-x-2 transition-all hover:scale-105">
                    <i class="fas fa-print"></i>
                    <span>Antrean Cetak</span>
                </a>
                <div class="h-8 w-px bg-gray-200"></div>
                <div class="flex items-center space-x-3">
                    <div class="flex flex-col text-right">
                        <span class="text-sm font-bold text-gray-800 leading-none"><?php echo $_SESSION['admin_username']; ?></span>
                        <span class="text-[10px] text-gray-400">Pengelola Aktif</span>
                    </div>
                    <div class="w-10 h-10 bg-amber-100 text-amber-700 rounded-xl flex items-center justify-center font-bold shadow-inner">
                        <?php echo strtoupper(substr($_SESSION['admin_username'], 0, 1)); ?>
                    </div>
                </div>
                <?php if ($admin_branch_id): ?>
                <button onclick="document.getElementById('catalogModal').classList.remove('hidden')" 
                    class="bg-emerald-600 text-white px-5 py-2.5 rounded-xl hover:bg-emerald-700 transition-all flex items-center space-x-2 shadow-lg hover:shadow-emerald-200 transform hover:-translate-y-0.5">
                    <i class="fas fa-box-open"></i>
                    <span class="font-bold">Ambil dari Katalog</span>
                </button>
                <?php endif; ?>

                <?php if (!$admin_branch_id): ?>
                <button onclick="document.getElementById('addModal').classList.remove('hidden')" 
                    class="bg-amber-600 text-white px-5 py-2.5 rounded-xl hover:bg-amber-700 transition-all flex items-center space-x-2 shadow-lg hover:shadow-amber-200 transform hover:-translate-y-0.5">
                    <i class="fas fa-plus"></i>
                    <span class="font-bold">Tambah Produk Baru</span>
                </button>
                <?php endif; ?>
            </div>
        </header>

        <!-- Dashboard Content -->
        <div class="p-8">
            <div class="bg-white rounded-xl shadow-sm overflow-hidden border border-gray-100">
                <table class="w-full text-left">
                    <thead class="bg-gray-50 border-b border-gray-100">
                        <tr>
                            <th class="px-6 py-4 text-xs font-semibold text-gray-500 uppercase">Image</th>
                            <th class="px-6 py-4 text-xs font-semibold text-gray-500 uppercase">Barcode / QR</th>
                            <th class="px-6 py-4 text-xs font-semibold text-gray-500 uppercase">Product Name</th>
                            <th class="px-6 py-4 text-xs font-semibold text-gray-500 uppercase">Category</th>
                            <th class="px-6 py-4 text-xs font-semibold text-gray-500 uppercase">Price</th>
                            <th class="px-6 py-4 text-xs font-semibold text-gray-500 uppercase">Stok (Realtime)</th>
                            <th class="px-6 py-4 text-xs font-semibold text-gray-500 uppercase text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <?php foreach ($products as $product): ?>
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="px-6 py-4">
                                <img src="../../<?php echo $product['image_url'] ?: 'https://via.placeholder.com/60'; ?>" 
                                    class="w-12 h-12 object-cover rounded-lg border border-gray-200">
                            </td>
                            <td class="px-6 py-4">
                                <?php if ($product['barcode']): ?>
                                    <div class="flex justify-center">
                                        <img src="https://barcode.tec-it.com/barcode.ashx?data=<?php echo urlencode($product['barcode']); ?>&code=Code128&dpi=96&hide-text=on" 
                                            class="h-10 max-w-[150px] object-contain" title="<?php echo htmlspecialchars($product['barcode']); ?>">
                                    </div>
                                <?php else: ?>
                                    <div class="text-center text-gray-300 text-xs italic">Belum ada</div>
                                <?php endif; ?>
                            </td>
                            <td class="px-6 py-4 font-medium text-gray-800"><?php echo htmlspecialchars($product['name']); ?></td>
                            <td class="px-6 py-4 text-gray-600"><?php echo htmlspecialchars($product['category_name']); ?></td>
                            <td class="px-6 py-4 font-semibold text-amber-600">Rp <?php echo number_format($product['price'], 0, ',', '.'); ?></td>
                            <td class="px-6 py-4">
                                <?php if ($admin_branch_id): ?>
                                    <div class="flex items-center space-x-2">
                                        <input type="number" 
                                            value="<?php echo (int)$product['branch_stock']; ?>" 
                                            onchange="updateStock(<?php echo $product['id']; ?>, this.value)"
                                            class="w-20 px-3 py-1 border border-gray-200 rounded-lg focus:ring-2 focus:ring-amber-500 outline-none text-center font-bold text-gray-800"
                                        >
                                        <span id="status-<?php echo $product['id']; ?>" class="text-[10px] hidden"></span>
                                    </div>
                                <?php else: ?>
                                    <span class="px-3 py-1 bg-gray-100 text-gray-700 rounded-full text-xs font-bold border border-gray-200">
                                        Total: <?php echo (int)$product['branch_stock']; ?>
                                    </span>
                                <?php endif; ?>
                            </td>
                            <td class="px-6 py-4 text-right space-x-3">
                                <button onclick="addToPrintQueue(<?php echo $product['id']; ?>)" class="text-emerald-500 hover:text-emerald-700 transition-colors" title="Print Label">
                                    <i class="fas fa-print"></i>
                                </button>
                                <button onclick="openEditModal(<?php echo $product['id']; ?>)" class="text-blue-500 hover:text-blue-700 transition-colors">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button onclick="openDeleteModal(<?php echo $product['id']; ?>)" class="text-red-400 hover:text-red-600 transition-colors">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <?php if (empty($products)): ?>
                        <tr>
                            <td colspan="5" class="px-6 py-12 text-center text-gray-500">
                                <i class="fas fa-inbox text-4xl mb-3 block opacity-20"></i>
                                No products found. Add your first product!
                            </td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>

    <!-- Add Product Modal (Simple) -->
    <div id="addModal" class="fixed inset-0 bg-black/50 hidden flex items-center justify-center p-4 z-50">
        <div class="bg-white rounded-2xl max-w-lg w-full p-8 shadow-2xl relative">
            <button onclick="document.getElementById('addModal').classList.add('hidden')" 
                class="absolute top-4 right-4 text-gray-400 hover:text-gray-600">
                <i class="fas fa-times text-xl"></i>
            </button>
            <h3 class="text-2xl font-bold text-gray-800 mb-6">Add New Product</h3>
            <form action="process_product.php" method="POST" enctype="multipart/form-data" class="space-y-4">
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Product Name</label>
                        <input type="text" name="name" required class="w-full px-4 py-2 rounded-lg border border-gray-200 outline-none focus:ring-2 focus:ring-amber-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Barcode / QR Code</label>
                        <input type="text" name="barcode" class="w-full px-4 py-2 rounded-lg border border-gray-200 outline-none focus:ring-2 focus:ring-amber-500" placeholder="Kosongkan untuk isi otomatis">
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Category</label>
                        <select name="category_id" required class="w-full px-4 py-2 rounded-lg border border-gray-200 outline-none focus:ring-2 focus:ring-amber-500">
                            <option value="">Select Category</option>
                            <?php foreach ($categories as $cat): ?>
                                <option value="<?php echo $cat['id']; ?>"><?php echo htmlspecialchars($cat['name']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Price</label>
                        <input type="number" name="price" required class="w-full px-4 py-2 rounded-lg border border-gray-200 outline-none focus:ring-2 focus:ring-amber-500">
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                    <textarea name="description" rows="3" class="w-full px-4 py-2 rounded-lg border border-gray-200 outline-none focus:ring-2 focus:ring-amber-500"></textarea>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Product Image</label>
                    <input type="file" name="image" class="w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-amber-50 file:text-amber-700 hover:file:bg-amber-100">
                </div>
                <div class="flex items-center space-x-2">
                    <input type="checkbox" name="is_featured" id="is_featured" class="w-4 h-4 text-amber-600 border-gray-300 rounded focus:ring-amber-500">
                    <label for="is_featured" class="text-sm text-gray-700">Mark as Featured</label>
                </div>
                <button type="submit" class="w-full bg-[#4A2C2A] text-white py-3 rounded-lg font-semibold hover:bg-[#3D2422] transition-all shadow-lg">
                    Save Product
                </button>
            </form>
        </div>
    </div>
    
    <?php include 'product_modals.php'; ?>

    <!-- Catalog Selection Modal (Only for Branch Admin) -->
    <?php if ($admin_branch_id): ?>
    <div id="catalogModal" class="hidden fixed inset-0 bg-black/60 backdrop-blur-sm z-[100] flex items-center justify-center p-4">
        <div class="bg-white rounded-[2rem] w-full max-w-4xl shadow-2xl overflow-hidden border border-gray-100 flex flex-col max-h-[90vh]">
            <div class="p-8 bg-emerald-600 text-white flex justify-between items-center">
                <div>
                    <h3 class="text-2xl font-bold">Katalog Produk Pusat</h3>
                    <p class="text-emerald-100 text-sm">Pilih produk yang ingin Anda tampilkan di cabang Anda</p>
                </div>
                <button onclick="document.getElementById('catalogModal').classList.add('hidden')" class="w-10 h-10 rounded-full bg-white/10 flex items-center justify-center hover:bg-white/20 transition-all">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>
            
            <div class="p-8 overflow-y-auto grid grid-cols-1 md:grid-cols-2 gap-6 bg-gray-50">
                <?php if (empty($catalog_products)): ?>
                    <div class="col-span-full py-12 text-center text-gray-400 italic">
                        Semua produk katalog sudah ada di cabang Anda.
                    </div>
                <?php endif; ?>
                
                <?php foreach ($catalog_products as $cat_p): ?>
                <div class="bg-white p-4 rounded-2xl border border-gray-100 shadow-sm flex items-center space-x-4 hover:shadow-md transition-all">
                    <img src="../../<?php echo $cat_p['image_url'] ?: 'https://via.placeholder.com/60'; ?>" class="w-16 h-16 object-cover rounded-xl border border-gray-100">
                    <div class="flex-1">
                        <h4 class="font-bold text-gray-800"><?php echo htmlspecialchars($cat_p['name']); ?></h4>
                        <p class="text-xs text-gray-500 uppercase font-semibold tracking-wider"><?php echo htmlspecialchars($cat_p['category_name']); ?></p>
                        <p class="text-sm font-bold text-emerald-600 mt-1">Rp <?php echo number_format($cat_p['price'], 0, ',', '.'); ?></p>
                    </div>
                    <form method="POST">
                        <input type="hidden" name="product_id" value="<?php echo $cat_p['id']; ?>">
                        <button type="submit" name="claim_product" class="bg-emerald-100 text-emerald-700 px-4 py-2 rounded-xl text-xs font-bold hover:bg-emerald-600 hover:text-white transition-all">
                            Aktifkan
                        </button>
                    </form>
                </div>
                <?php endforeach; ?>
            </div>
            
            <div class="p-6 bg-white border-t border-gray-100 text-center">
                <p class="text-xs text-gray-400">Produk yang diaktifkan akan muncul di dashboard stok Anda dengan jumlah awal 0.</p>
            </div>
        </div>
    </div>
    <?php endif; ?>
    <?php include '../includes/modals/success_modal.php'; ?>
    <?php include '../includes/modals/error_modal.php'; ?>
    <?php include '../includes/modals/confirm_modal.php'; ?>

    <script>
        function addToPrintQueue(productId) {
            fetch('add_to_print_queue.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: 'product_id=' + productId
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    showSuccess('Produk ditambahkan ke antrean cetak label.');
                } else {
                    showError(data.message);
                }
            });
        }
    </script>
    <script>
        function updateStock(productId, newValue) {
            const statusSpan = document.getElementById('status-' + productId);
            statusSpan.innerText = 'Updating...';
            statusSpan.className = 'text-[10px] text-amber-500 block';

            const formData = new FormData();
            formData.append('ajax_update_stock', '1');
            formData.append('product_id', productId);
            formData.append('stock', newValue);

            fetch('index.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    statusSpan.innerText = 'Saved!';
                    statusSpan.className = 'text-[10px] text-green-500 block';
                    setTimeout(() => {
                        statusSpan.classList.add('hidden');
                    }, 2000);
                } else {
                    statusSpan.innerText = 'Error!';
                    statusSpan.className = 'text-[10px] text-red-500 block';
                }
            })
            .catch(() => {
                statusSpan.innerText = 'Error!';
                statusSpan.className = 'text-[10px] text-red-500 block';
            });
        }
    </script>
</body>
</html>
