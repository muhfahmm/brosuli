<?php
require_once 'auth.php';
require_once '../db/db.php';
requireLogin();

// Fetch products
$stmt = $pdo->query("SELECT p.*, c.name as category_name FROM products p LEFT JOIN categories c ON p.category_id = c.id ORDER BY p.created_at DESC");
$products = $stmt->fetchAll();

// Fetch all categories for the modal
$cat_stmt = $pdo->query("SELECT * FROM categories ORDER BY name ASC");
$categories = $cat_stmt->fetchAll();

// Handle Delete
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $stmt = $pdo->prepare("DELETE FROM products WHERE id = ?");
    $stmt->execute([$id]);
    header('Location: index.php');
    exit();
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
    <!-- Sidebar -->
    <aside class="w-64 bg-[#4A2C2A] text-white hidden md:flex flex-col">
        <div class="p-6">
            <h1 class="text-2xl font-bold">Brosuli</h1>
            <p class="text-amber-300 text-sm">Admin Panel</p>
        </div>
        <nav class="flex-1 px-4 py-4 space-y-2">
            <a href="index.php" class="flex items-center space-x-3 bg-white/10 p-3 rounded-lg">
                <i class="fas fa-bread-slice"></i>
                <span>Products</span>
            </a>
            <a href="categories.php" class="flex items-center space-x-3 hover:bg-white/5 p-3 rounded-lg transition-colors">
                <i class="fas fa-tags"></i>
                <span>Categories</span>
            </a>
            <a href="banners.php" class="flex items-center space-x-3 hover:bg-white/5 p-3 rounded-lg transition-colors">
                <i class="fas fa-images"></i>
                <span>Banners</span>
            </a>
        </nav>
        <div class="p-4 border-t border-white/10">
            <a href="logout.php" class="flex items-center space-x-3 text-red-300 hover:text-red-100 transition-colors">
                <i class="fas fa-sign-out-alt"></i>
                <span>Logout</span>
            </a>
        </div>
    </aside>

    <!-- Main Content -->
    <main class="flex-1 flex flex-col">
        <!-- Header -->
        <header class="bg-white shadow-sm px-8 py-4 flex justify-between items-center">
            <h2 class="text-xl font-semibold text-gray-800">Product Management</h2>
            <div class="flex items-center space-x-4">
                <span class="text-gray-600">Welcome, <strong><?php echo $_SESSION['admin_username']; ?></strong></span>
                <button onclick="document.getElementById('addModal').classList.remove('hidden')" 
                    class="bg-amber-600 text-white px-4 py-2 rounded-lg hover:bg-amber-700 transition-colors flex items-center space-x-2 shadow-md">
                    <i class="fas fa-plus"></i>
                    <span>Add Product</span>
                </button>
            </div>
        </header>

        <!-- Dashboard Content -->
        <div class="p-8">
            <div class="bg-white rounded-xl shadow-sm overflow-hidden border border-gray-100">
                <table class="w-full text-left">
                    <thead class="bg-gray-50 border-b border-gray-100">
                        <tr>
                            <th class="px-6 py-4 text-xs font-semibold text-gray-500 uppercase">Image</th>
                            <th class="px-6 py-4 text-xs font-semibold text-gray-500 uppercase">Product Name</th>
                            <th class="px-6 py-4 text-xs font-semibold text-gray-500 uppercase">Category</th>
                            <th class="px-6 py-4 text-xs font-semibold text-gray-500 uppercase">Price</th>
                            <th class="px-6 py-4 text-xs font-semibold text-gray-500 uppercase text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <?php foreach ($products as $product): ?>
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="px-6 py-4">
                                <img src="<?php echo $product['image_url'] ?: 'https://via.placeholder.com/60'; ?>" 
                                    class="w-12 h-12 object-cover rounded-lg border border-gray-200">
                            </td>
                            <td class="px-6 py-4 font-medium text-gray-800"><?php echo htmlspecialchars($product['name']); ?></td>
                            <td class="px-6 py-4 text-gray-600"><?php echo htmlspecialchars($product['category_name']); ?></td>
                            <td class="px-6 py-4 font-semibold text-amber-600">Rp <?php echo number_format($product['price'], 0, ',', '.'); ?></td>
                            <td class="px-6 py-4 text-right space-x-3">
                                <a href="#" class="text-blue-500 hover:text-blue-700"><i class="fas fa-edit"></i></a>
                                <a href="?delete=<?php echo $product['id']; ?>" 
                                    onclick="return confirm('Are you sure?')"
                                    class="text-red-400 hover:text-red-600 transition-colors">
                                    <i class="fas fa-trash"></i>
                                </a>
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
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Product Name</label>
                    <input type="text" name="name" required class="w-full px-4 py-2 rounded-lg border border-gray-200 outline-none focus:ring-2 focus:ring-amber-500">
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
</body>
</html>
