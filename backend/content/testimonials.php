<?php
require_once '../auth/auth.php';
require_once '../../db/db.php';
requireLogin();

// Handle Delete
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    try {
        $stmt = $pdo->prepare("DELETE FROM tb_testimonials WHERE id = ?");
        $stmt->execute([$id]);
        $success_msg = "Testimonial berhasil dihapus!";
    } catch (Exception $e) {
        $error_msg = "Error: " . $e->getMessage();
    }
}

// Handle Add
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_testimonial'])) {
    try {
        $stmt = $pdo->prepare("INSERT INTO tb_testimonials (customer_name, customer_initial, rating, testimonial_text, customer_type, display_order) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([
            $_POST['customer_name'],
            strtoupper(substr($_POST['customer_name'], 0, 2)),
            $_POST['rating'],
            $_POST['testimonial_text'],
            $_POST['customer_type'],
            $_POST['display_order']
        ]);
        $success_msg = "Testimonial berhasil ditambahkan!";
    } catch (Exception $e) {
        $error_msg = "Error: " . $e->getMessage();
    }
}

// Get all testimonials
$stmt = $pdo->query("SELECT * FROM tb_testimonials ORDER BY display_order ASC");
$testimonials = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Testimonial - Brosuli Admin</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: '#4A2C2A',
                        secondary: '#D97706',
                    }
                }
            }
        }
    </script>
</head>
<body class="bg-gray-100">
    <div class="flex h-screen">
        <?php include '../includes/sidebar.php'; ?>
        
        <main class="flex-1 overflow-auto">
            <div class="p-8 max-w-6xl mx-auto">
                <div class="mb-8">
                    <h1 class="text-4xl font-bold text-primary mb-2">Kelola Testimonial</h1>
                    <p class="text-gray-600">Kelola testimonial pelanggan di homepage</p>
                </div>

                <?php if (isset($success_msg)): ?>
                <div class="mb-6 p-4 bg-green-100 text-green-700 rounded-lg flex items-center space-x-3">
                    <i class="fas fa-check-circle"></i>
                    <span><?php echo $success_msg; ?></span>
                </div>
                <?php endif; ?>

                <?php if (isset($error_msg)): ?>
                <div class="mb-6 p-4 bg-red-100 text-red-700 rounded-lg flex items-center space-x-3">
                    <i class="fas fa-exclamation-circle"></i>
                    <span><?php echo $error_msg; ?></span>
                </div>
                <?php endif; ?>

                <!-- Add Testimonial Form -->
                <div class="bg-white rounded-2xl shadow-lg p-8 mb-8">
                    <h2 class="text-2xl font-bold text-primary mb-6">Tambah Testimonial Baru</h2>
                    <form method="POST" class="space-y-4">
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div>
                                <label class="block text-primary font-bold mb-2">Nama Pelanggan *</label>
                                <input type="text" name="customer_name" required
                                    class="w-full border-2 border-gray-200 rounded-xl px-4 py-3 focus:outline-none focus:border-primary"
                                    placeholder="Nama lengkap">
                            </div>
                            <div>
                                <label class="block text-primary font-bold mb-2">Rating</label>
                                <select name="rating" class="w-full border-2 border-gray-200 rounded-xl px-4 py-3 focus:outline-none focus:border-primary">
                                    <option value="5" selected>⭐⭐⭐⭐⭐ 5/5</option>
                                    <option value="4">⭐⭐⭐⭐ 4/5</option>
                                    <option value="3">⭐⭐⭐ 3/5</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-primary font-bold mb-2">Urutan Tampil</label>
                                <input type="number" name="display_order" value="0" min="0"
                                    class="w-full border-2 border-gray-200 rounded-xl px-4 py-3 focus:outline-none focus:border-primary">
                            </div>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-primary font-bold mb-2">Tipe Pelanggan</label>
                                <input type="text" name="customer_type" value="Pelanggan Setia"
                                    class="w-full border-2 border-gray-200 rounded-xl px-4 py-3 focus:outline-none focus:border-primary">
                            </div>
                        </div>
                        <div>
                            <label class="block text-primary font-bold mb-2">Testimoni *</label>
                            <textarea name="testimonial_text" rows="3" required
                                class="w-full border-2 border-gray-200 rounded-xl px-4 py-3 focus:outline-none focus:border-primary resize-none"
                                placeholder="Tulis testimoni pelanggan..."></textarea>
                        </div>
                        <button type="submit" name="add_testimonial" class="bg-primary hover:bg-secondary text-white font-bold py-3 px-6 rounded-xl transition-all">
                            <i class="fas fa-plus mr-2"></i>
                            Tambah Testimonial
                        </button>
                    </form>
                </div>

                <!-- Testimonials List -->
                <div class="bg-white rounded-2xl shadow-lg overflow-hidden">
                    <div class="p-8 border-b border-gray-200">
                        <h2 class="text-2xl font-bold text-primary">Daftar Testimonial (<?php echo count($testimonials); ?>)</h2>
                    </div>

                    <?php if (empty($testimonials)): ?>
                    <div class="p-8 text-center text-gray-500">
                        <i class="fas fa-comments text-4xl mb-4 block opacity-30"></i>
                        <p>Belum ada testimonial. Tambahkan yang pertama!</p>
                    </div>
                    <?php else: ?>
                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-4 text-left text-sm font-bold text-gray-600">Nama</th>
                                    <th class="px-6 py-4 text-left text-sm font-bold text-gray-600">Rating</th>
                                    <th class="px-6 py-4 text-left text-sm font-bold text-gray-600">Testimoni</th>
                                    <th class="px-6 py-4 text-left text-sm font-bold text-gray-600">Urutan</th>
                                    <th class="px-6 py-4 text-left text-sm font-bold text-gray-600">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200">
                                <?php foreach ($testimonials as $testimonial): ?>
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4">
                                        <div class="flex items-center space-x-3">
                                            <div class="w-10 h-10 bg-secondary text-white rounded-full flex items-center justify-center font-bold">
                                                <?php echo $testimonial['customer_initial']; ?>
                                            </div>
                                            <span class="font-bold text-primary"><?php echo htmlspecialchars($testimonial['customer_name']); ?></span>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <span class="text-yellow-500">
                                            <?php echo str_repeat('★', $testimonial['rating']) . str_repeat('☆', 5 - $testimonial['rating']); ?>
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-600 max-w-xs truncate">
                                        <?php echo htmlspecialchars($testimonial['testimonial_text']); ?>
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                        <span class="bg-blue-100 text-blue-700 px-3 py-1 rounded-full text-sm font-bold">
                                            <?php echo $testimonial['display_order']; ?>
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                        <a href="?delete=<?php echo $testimonial['id']; ?>" onclick="return confirm('Yakin hapus testimonial ini?')" class="text-red-500 hover:text-red-700 font-bold">
                                            <i class="fas fa-trash"></i>
                                        </a>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </main>
    </div>
</body>
</html>
