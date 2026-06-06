<?php
require_once '../auth/auth.php';
require_once '../../db/db.php';
requireLogin();

// Get current about section data
$stmt = $pdo->query("SELECT * FROM tb_about_section LIMIT 1");
$about = $stmt->fetch();

// Handle Update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_about'])) {
    try {
        $stmt = $pdo->prepare("UPDATE tb_about_section SET 
            section_title = ?,
            main_heading = ?,
            main_description = ?,
            description_2 = ?,
            button_text = ?,
            button_link = ?,
            feature_1_title = ?,
            feature_1_description = ?,
            feature_2_title = ?,
            feature_2_description = ?,
            feature_3_title = ?,
            feature_3_description = ?,
            is_active = ?
            WHERE id = ?");
        
        $stmt->execute([
            $_POST['section_title'],
            $_POST['main_heading'],
            $_POST['main_description'],
            $_POST['description_2'],
            $_POST['button_text'],
            $_POST['button_link'],
            $_POST['feature_1_title'],
            $_POST['feature_1_description'],
            $_POST['feature_2_title'],
            $_POST['feature_2_description'],
            $_POST['feature_3_title'],
            $_POST['feature_3_description'],
            isset($_POST['is_active']) ? 1 : 0,
            $about['id']
        ]);
        
        $success_msg = "Konten Tentang Kami berhasil diperbarui!";
        // Refresh data
        $stmt = $pdo->query("SELECT * FROM tb_about_section LIMIT 1");
        $about = $stmt->fetch();
    } catch (Exception $e) {
        $error_msg = "Error: " . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Tentang Kami - Brosuli Admin</title>
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
            <div class="p-8 max-w-4xl mx-auto">
                <div class="mb-8">
                    <h1 class="text-4xl font-bold text-primary mb-2">Kelola Tentang Kami</h1>
                    <p class="text-gray-600">Perbarui konten section "Tentang Kami" di homepage</p>
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

                <form method="POST" class="bg-white rounded-2xl shadow-lg p-8 space-y-6">
                    <!-- Section Title -->
                    <div>
                        <label class="block text-primary font-bold mb-3">Judul Section</label>
                        <input type="text" name="section_title" value="<?php echo htmlspecialchars($about['section_title']); ?>" required
                            class="w-full border-2 border-gray-200 rounded-xl px-4 py-3 focus:outline-none focus:border-primary">
                    </div>

                    <!-- Main Heading -->
                    <div>
                        <label class="block text-primary font-bold mb-3">Heading Utama</label>
                        <input type="text" name="main_heading" value="<?php echo htmlspecialchars($about['main_heading']); ?>" required
                            class="w-full border-2 border-gray-200 rounded-xl px-4 py-3 focus:outline-none focus:border-primary">
                    </div>

                    <!-- Main Description -->
                    <div>
                        <label class="block text-primary font-bold mb-3">Paragraf Pertama</label>
                        <textarea name="main_description" rows="4" required
                            class="w-full border-2 border-gray-200 rounded-xl px-4 py-3 focus:outline-none focus:border-primary resize-none"><?php echo htmlspecialchars($about['main_description']); ?></textarea>
                    </div>

                    <!-- Description 2 -->
                    <div>
                        <label class="block text-primary font-bold mb-3">Paragraf Kedua</label>
                        <textarea name="description_2" rows="4" required
                            class="w-full border-2 border-gray-200 rounded-xl px-4 py-3 focus:outline-none focus:border-primary resize-none"><?php echo htmlspecialchars($about['description_2']); ?></textarea>
                    </div>

                    <!-- Button -->
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-primary font-bold mb-3">Teks Tombol</label>
                            <input type="text" name="button_text" value="<?php echo htmlspecialchars($about['button_text']); ?>"
                                class="w-full border-2 border-gray-200 rounded-xl px-4 py-3 focus:outline-none focus:border-primary">
                        </div>
                        <div>
                            <label class="block text-primary font-bold mb-3">Link Tombol</label>
                            <input type="text" name="button_link" value="<?php echo htmlspecialchars($about['button_link']); ?>"
                                class="w-full border-2 border-gray-200 rounded-xl px-4 py-3 focus:outline-none focus:border-primary">
                        </div>
                    </div>

                    <!-- Features -->
                    <div class="pt-6 border-t-2 border-gray-200">
                        <h3 class="text-2xl font-bold text-primary mb-6">Fitur-Fitur (3 Keunggulan)</h3>
                        
                        <?php for ($i = 1; $i <= 3; $i++): ?>
                        <div class="mb-6 p-6 bg-gray-50 rounded-xl">
                            <h4 class="font-bold text-primary mb-4">Fitur <?php echo $i; ?></h4>
                            <div class="space-y-4">
                                <div>
                                    <label class="block text-primary font-bold mb-2">Judul</label>
                                    <input type="text" name="feature_<?php echo $i; ?>_title" value="<?php echo htmlspecialchars($about["feature_{$i}_title"]); ?>"
                                        class="w-full border-2 border-gray-200 rounded-xl px-4 py-3 focus:outline-none focus:border-primary">
                                </div>
                                <div>
                                    <label class="block text-primary font-bold mb-2">Deskripsi</label>
                                    <textarea name="feature_<?php echo $i; ?>_description" rows="3"
                                        class="w-full border-2 border-gray-200 rounded-xl px-4 py-3 focus:outline-none focus:border-primary resize-none"><?php echo htmlspecialchars($about["feature_{$i}_description"]); ?></textarea>
                                </div>
                            </div>
                        </div>
                        <?php endfor; ?>
                    </div>

                    <!-- Active Status -->
                    <div class="flex items-center space-x-3 p-4 bg-gray-50 rounded-xl">
                        <input type="checkbox" name="is_active" id="is_active" <?php echo $about['is_active'] ? 'checked' : ''; ?>
                            class="w-5 h-5 rounded border-gray-300">
                        <label for="is_active" class="text-primary font-bold">Aktifkan section ini di homepage</label>
                    </div>

                    <!-- Submit Button -->
                    <div class="flex items-center space-x-4 pt-6">
                        <button type="submit" name="update_about" class="flex-1 bg-primary hover:bg-secondary text-white font-bold py-4 rounded-xl transition-all shadow-lg transform hover:scale-105">
                            <i class="fas fa-save mr-2"></i>
                            Simpan Perubahan
                        </button>
                        <a href="../../index.php#about" target="_blank" class="px-6 py-4 bg-blue-500 hover:bg-blue-600 text-white font-bold rounded-xl transition-all">
                            <i class="fas fa-external-link-alt mr-2"></i>
                            Lihat di Website
                        </a>
                    </div>
                </form>
            </div>
        </main>
    </div>
</body>
</html>
