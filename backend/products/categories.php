<?php
require_once '../auth/auth.php';
require_once '../../db/db.php';
requireLogin();

// Fetch categories
$stmt = $pdo->query("SELECT * FROM tb_categories ORDER BY name ASC");
$categories = $stmt->fetchAll();

// Handle Delete
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    try {
        $stmt = $pdo->prepare("DELETE FROM tb_categories WHERE id = ?");
        $stmt->execute([$id]);
        header('Location: categories.php');
    } catch (PDOException $e) {
        $error = "Cannot delete category as it is being used by products.";
    }
}

// Handle Add
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_category'])) {
    $name = $_POST['name'] ?? '';
    $description = $_POST['description'] ?? '';
    if ($name) {
        $stmt = $pdo->prepare("INSERT INTO tb_categories (name, description) VALUES (?, ?)");
        $stmt->execute([$name, $description]);
        header('Location: categories.php');
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Categories - Brosuli Admin</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style> body { font-family: 'Outfit', sans-serif; } </style>
</head>
<body class="bg-[#FDFCF6] min-h-screen flex">
    <?php include '../includes/sidebar.php'; ?>

    <!-- Main Content -->
    <main class="flex-1 flex flex-col">
        <header class="bg-white shadow-sm px-8 py-4 flex justify-between items-center">
            <h2 class="text-xl font-semibold text-gray-800">Category Management</h2>
            <button onclick="document.getElementById('addModal').classList.remove('hidden')" 
                class="bg-amber-600 text-white px-4 py-2 rounded-lg hover:bg-amber-700 transition-colors flex items-center space-x-2">
                <i class="fas fa-plus"></i>
                <span>Add Category</span>
            </button>
        </header>

        <div class="p-8">
            <?php if (isset($error)): ?>
                <div class="bg-red-50 border-l-4 border-red-500 text-red-700 p-4 mb-6"><?php echo $error; ?></div>
            <?php endif; ?>

            <div class="bg-white rounded-xl shadow-sm overflow-hidden border border-gray-100 max-w-2xl">
                <table class="w-full text-left">
                    <thead class="bg-gray-50 border-b border-gray-100">
                        <tr>
                            <th class="px-6 py-4 text-xs font-semibold text-gray-500 uppercase">ID</th>
                            <th class="px-6 py-4 text-xs font-semibold text-gray-500 uppercase">Category Name</th>
                            <th class="px-6 py-4 text-xs font-semibold text-gray-500 uppercase">Description</th>
                            <th class="px-6 py-4 text-xs font-semibold text-gray-500 uppercase text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <?php foreach ($categories as $cat): ?>
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="px-6 py-4 text-gray-500"><?php echo $cat['id']; ?></td>
                            <td class="px-6 py-4 font-medium text-gray-800"><?php echo htmlspecialchars($cat['name']); ?></td>
                            <td class="px-6 py-4 text-gray-500 text-sm italic"><?php echo htmlspecialchars($cat['description'] ?: '-'); ?></td>
                            <td class="px-6 py-4 text-right">
                                <button onclick="deleteCategory(<?php echo $cat['id']; ?>)" 
                                    class="text-red-400 hover:text-red-600 transition-colors">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>

    <!-- Add Category Modal -->
    <div id="addModal" class="fixed inset-0 bg-black/50 hidden flex items-center justify-center p-4 z-50">
        <div class="bg-white rounded-2xl max-w-md w-full p-8 shadow-2xl relative">
            <button onclick="document.getElementById('addModal').classList.add('hidden')" class="absolute top-4 right-4 text-gray-400 hover:text-gray-600"><i class="fas fa-times text-xl"></i></button>
            <h3 class="text-2xl font-bold text-gray-800 mb-6">Add Category</h3>
            <form method="POST" class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Category Name</label>
                    <input type="text" name="name" required class="w-full px-4 py-2 rounded-lg border border-gray-200 outline-none focus:ring-2 focus:ring-amber-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                    <textarea name="description" rows="4" class="w-full px-4 py-2 rounded-lg border border-gray-200 outline-none focus:ring-2 focus:ring-amber-500" placeholder="Describe this category..."></textarea>
                </div>
                <button type="submit" name="add_category" class="w-full bg-[#4A2C2A] text-white py-3 rounded-lg font-semibold hover:bg-[#3D2422]">
                    Save Category
                </button>
            </form>
        </div>
    </div>
    <?php include '../includes/modals/confirm_modal.php'; ?>

    <script>
    function deleteCategory(id) {
        showConfirm(
            'Hapus Kategori?', 
            'Seluruh produk dalam kategori ini mungkin akan terpengaruh. Lanjutkan?', 
            function() {
                window.location.href = '?delete=' + id;
            }
        );
    }
    </script>
</body>
</html>
