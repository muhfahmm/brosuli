<?php
require_once 'auth.php';
require_once '../db/db.php';
requireLogin();

// Fetch best sellers with product details
$stmt = $pdo->query("SELECT bs.*, p.name as product_name, p.image_url, c.name as category_name 
                    FROM best_sellers bs 
                    JOIN products p ON bs.product_id = p.id 
                    LEFT JOIN categories c ON p.category_id = c.id 
                    ORDER BY bs.display_order ASC");
$best_sellers = $stmt->fetchAll();

// Fetch all products for the selection modal
$product_stmt = $pdo->query("SELECT id, name FROM products ORDER BY name ASC");
$all_products = $product_stmt->fetchAll();

// Handle Delete
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $stmt = $pdo->prepare("DELETE FROM best_sellers WHERE id = ?");
    $stmt->execute([$id]);
    header('Location: best_sellers.php');
    exit();
}

// Handle Add
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_best_seller'])) {
    $product_id = $_POST['product_id'] ?? '';
    $display_order = $_POST['display_order'] ?? 0;
    
    if ($product_id) {
        $stmt = $pdo->prepare("INSERT INTO best_sellers (product_id, display_order) VALUES (?, ?)");
        $stmt->execute([$product_id, $display_order]);
        header('Location: best_sellers.php');
        exit();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Best Sellers - Brosuli Admin</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style> body { font-family: 'Outfit', sans-serif; } </style>
</head>
<body class="bg-[#FDFCF6] min-h-screen flex">
    <?php include 'sidebar.php'; ?>

    <main class="flex-1 flex flex-col">
        <header class="bg-white shadow-sm px-8 py-4 flex justify-between items-center">
            <h2 class="text-xl font-semibold text-gray-800">Best Sellers Management</h2>
            <button onclick="document.getElementById('addModal').classList.remove('hidden')" 
                class="bg-amber-600 text-white px-4 py-2 rounded-lg hover:bg-amber-700 transition-colors flex items-center space-x-2">
                <i class="fas fa-plus"></i>
                <span>Add Best Seller</span>
            </button>
        </header>

        <div class="p-8">
            <div class="bg-white rounded-xl shadow-sm overflow-hidden border border-gray-100 max-w-4xl">
                <table class="w-full text-left">
                    <thead class="bg-gray-50 border-b border-gray-100">
                        <tr>
                            <th class="px-6 py-4 text-xs font-semibold text-gray-500 uppercase">Product</th>
                            <th class="px-6 py-4 text-xs font-semibold text-gray-500 uppercase">Category</th>
                            <th class="px-6 py-4 text-xs font-semibold text-gray-500 uppercase">Order</th>
                            <th class="px-6 py-4 text-xs font-semibold text-gray-500 uppercase text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <?php foreach ($best_sellers as $bs): ?>
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="px-6 py-4 flex items-center space-x-4">
                                <img src="../<?php echo $bs['image_url'] ?: 'https://via.placeholder.com/40'; ?>" class="w-10 h-10 object-cover rounded-lg">
                                <span class="font-medium text-gray-800"><?php echo htmlspecialchars($bs['product_name']); ?></span>
                            </td>
                            <td class="px-6 py-4 text-gray-500"><?php echo htmlspecialchars($bs['category_name']); ?></td>
                            <td class="px-6 py-4 text-gray-500"><?php echo $bs['display_order']; ?></td>
                            <td class="px-6 py-4 text-right">
                                <a href="?delete=<?php echo $bs['id']; ?>" onclick="return confirm('Remove from best sellers?')" class="text-red-400 hover:text-red-600">
                                    <i class="fas fa-trash"></i>
                                </a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <?php if (empty($best_sellers)): ?>
                        <tr>
                            <td colspan="4" class="px-6 py-12 text-center text-gray-400 italic">No best sellers added yet.</td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>

    <!-- Add Best Seller Modal -->
    <div id="addModal" class="fixed inset-0 bg-black/50 hidden flex items-center justify-center p-4 z-50">
        <div class="bg-white rounded-2xl max-w-md w-full p-8 shadow-2xl relative">
            <button onclick="document.getElementById('addModal').classList.add('hidden')" class="absolute top-4 right-4 text-gray-400 hover:text-gray-600"><i class="fas fa-times text-xl"></i></button>
            <h3 class="text-2xl font-bold text-gray-800 mb-6">Add Best Seller</h3>
            <form method="POST" class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Select Product</label>
                    <select name="product_id" required class="w-full px-4 py-2 rounded-lg border border-gray-200 outline-none focus:ring-2 focus:ring-amber-500">
                        <option value="">-- Choose Product --</option>
                        <?php foreach ($all_products as $prod): ?>
                            <option value="<?php echo $prod['id']; ?>"><?php echo htmlspecialchars($prod['name']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Display Order</label>
                    <input type="number" name="display_order" value="0" class="w-full px-4 py-2 rounded-lg border border-gray-200 outline-none focus:ring-2 focus:ring-amber-500">
                </div>
                <button type="submit" name="add_best_seller" class="w-full bg-[#4A2C2A] text-white py-3 rounded-lg font-semibold hover:bg-[#3D2422]">
                    Add to Best Sellers
                </button>
            </form>
        </div>
    </div>
</body>
</html>
