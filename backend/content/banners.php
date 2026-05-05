<?php
require_once '../auth/auth.php';
require_once '../../db/db.php';
requireLogin();

// Fetch banners
$stmt = $pdo->query("SELECT * FROM banners ORDER BY created_at DESC");
$banners = $stmt->fetchAll();

// Handle Delete
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $stmt = $pdo->prepare("DELETE FROM banners WHERE id = ?");
    $stmt->execute([$id]);
    header('Location: banners.php');
    exit();
}

// Handle Add
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_banner'])) {
    $title = $_POST['title'] ?? '';
    $subtitle = $_POST['subtitle'] ?? '';
    $link_url = $_POST['link_url'] ?? '';
    $image_url = '';

    if (isset($_FILES['image']) && $_FILES['image']['error'] === 0) {
        $upload_dir = '../../uploads/banners/';
        if (!is_dir($upload_dir)) mkdir($upload_dir, 0777, true);
        $file_name = time() . '_' . basename($_FILES['image']['name']);
        if (move_uploaded_file($_FILES['image']['tmp_name'], $upload_dir . $file_name)) {
            $image_url = 'uploads/banners/' . $file_name;
        }
    }

    if ($image_url) {
        $stmt = $pdo->prepare("INSERT INTO banners (title, subtitle, image_url, link_url) VALUES (?, ?, ?, ?)");
        $stmt->execute([$title, $subtitle, $image_url, $link_url]);
        header('Location: banners.php');
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Banners - Brosuli Admin</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style> body { font-family: 'Outfit', sans-serif; } </style>
</head>
<body class="bg-[#FDFCF6] min-h-screen flex">
    <?php include '../includes/sidebar.php'; ?>

    <main class="flex-1 flex flex-col">
        <header class="bg-white shadow-sm px-8 py-4 flex justify-between items-center">
            <h2 class="text-xl font-semibold text-gray-800">Banner Management</h2>
            <button onclick="document.getElementById('addModal').classList.remove('hidden')" 
                class="bg-amber-600 text-white px-4 py-2 rounded-lg hover:bg-amber-700 transition-colors flex items-center space-x-2">
                <i class="fas fa-plus"></i>
                <span>Add Banner</span>
            </button>
        </header>

        <div class="p-8">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                <?php foreach ($banners as $banner): ?>
                <div class="bg-white rounded-2xl shadow-sm overflow-hidden border border-gray-100 group">
                    <div class="relative h-48 overflow-hidden">
                        <img src="../../<?php echo $banner['image_url']; ?>" class="w-full h-full object-cover">
                        <div class="absolute inset-0 bg-black/40 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center space-x-4">
                            <button onclick="deleteBanner(<?php echo $banner['id']; ?>)" class="bg-red-500 text-white w-10 h-10 rounded-full flex items-center justify-center hover:bg-red-600">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    </div>
                    <div class="p-6">
                        <h3 class="font-bold text-gray-800 mb-1"><?php echo htmlspecialchars($banner['title'] ?: 'Untitled Banner'); ?></h3>
                        <p class="text-sm text-gray-500 mb-2 line-clamp-2"><?php echo htmlspecialchars($banner['subtitle'] ?: ''); ?></p>
                        <p class="text-xs text-gray-400 truncate"><?php echo htmlspecialchars($banner['link_url'] ?: 'No link'); ?></p>
                    </div>
                </div>
                <?php endforeach; ?>
                <?php if (empty($banners)): ?>
                    <div class="col-span-full py-20 text-center text-gray-400">
                        <i class="fas fa-images text-5xl mb-4 block opacity-20"></i>
                        No banners found.
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </main>

    <!-- Add Banner Modal -->
    <div id="addModal" class="fixed inset-0 bg-black/50 hidden flex items-center justify-center p-4 z-50">
        <div class="bg-white rounded-2xl max-w-md w-full p-8 shadow-2xl relative">
            <button onclick="document.getElementById('addModal').classList.add('hidden')" class="absolute top-4 right-4 text-gray-400 hover:text-gray-600"><i class="fas fa-times text-xl"></i></button>
            <h3 class="text-2xl font-bold text-gray-800 mb-6">Add Banner</h3>
            <form method="POST" enctype="multipart/form-data" class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Banner Title</label>
                    <input type="text" name="title" class="w-full px-4 py-2 rounded-lg border border-gray-200 outline-none focus:ring-2 focus:ring-amber-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Banner Subtitle</label>
                    <textarea name="subtitle" rows="3" class="w-full px-4 py-2 rounded-lg border border-gray-200 outline-none focus:ring-2 focus:ring-amber-500"></textarea>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Link URL (Optional)</label>
                    <input type="text" name="link_url" class="w-full px-4 py-2 rounded-lg border border-gray-200 outline-none focus:ring-2 focus:ring-amber-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Banner Image</label>
                    <input type="file" name="image" required class="w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-amber-50 file:text-amber-700 hover:file:bg-amber-100">
                </div>
                <button type="submit" name="add_banner" class="w-full bg-[#4A2C2A] text-white py-3 rounded-lg font-semibold hover:bg-[#3D2422]">
                    Save Banner
                </button>
            </form>
        </div>
    </div>
    <?php include '../includes/modals/confirm_modal.php'; ?>

    <script>
    function deleteBanner(id) {
        showConfirm(
            'Hapus Banner?', 
            'Banner ini akan segera dihapus dari halaman depan. Lanjutkan?', 
            function() {
                window.location.href = '?delete=' + id;
            }
        );
    }
    </script>
</body>
</html>
