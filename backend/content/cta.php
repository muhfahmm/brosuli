<?php
require_once '../auth/auth.php';
require_once '../../db/db.php';
requireLogin();

// Get current CTA section data
$stmt = $pdo->query("SELECT * FROM tb_cta_section LIMIT 1");
$cta = $stmt->fetch();

// Handle Update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_cta'])) {
    try {
        $stmt = $pdo->prepare("UPDATE tb_cta_section SET 
            section_title = ?,
            main_heading = ?,
            subtitle = ?,
            button_1_text = ?,
            button_1_link = ?,
            button_1_icon = ?,
            button_2_text = ?,
            button_2_link = ?,
            button_2_icon = ?,
            background_color = ?,
            text_color = ?,
            is_active = ?
            WHERE id = ?");
        
        $stmt->execute([
            $_POST['section_title'],
            $_POST['main_heading'],
            $_POST['subtitle'],
            $_POST['button_1_text'],
            $_POST['button_1_link'],
            $_POST['button_1_icon'],
            $_POST['button_2_text'],
            $_POST['button_2_link'],
            $_POST['button_2_icon'],
            $_POST['background_color'],
            $_POST['text_color'],
            isset($_POST['is_active']) ? 1 : 0,
            $cta['id']
        ]);
        
        $success_msg = "Konten Call To Action berhasil diperbarui!";
        // Refresh data
        $stmt = $pdo->query("SELECT * FROM tb_cta_section LIMIT 1");
        $cta = $stmt->fetch();
    } catch (Exception $e) {
        $error_msg = "Error: " . $e->getMessage();
    }
}

$bg_color_options = [
    'bg-primary' => 'Primary (Cokelat)',
    'bg-secondary' => 'Secondary (Kuning)',
    'bg-gradient-to-r from-primary to-secondary' => 'Gradient Cokelat-Kuning',
    'bg-slate-900' => 'Gelap',
];

$text_color_options = [
    'text-white' => 'Putih',
    'text-primary' => 'Cokelat',
    'text-gray-900' => 'Gelap',
];

$icon_options = [
    'fa-shopping-bag' => '🛍️ Tas Belanja',
    'fa-cart-shopping' => '🛒 Keranjang',
    'fa-phone' => '📞 Telepon',
    'fa-envelope' => '✉️ Email',
    'fa-message' => '💬 Pesan',
    'fa-arrow-right' => '→ Panah',
    'fa-check' => '✓ Check',
];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Call To Action - Brosuli Admin</title>
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
                    <h1 class="text-4xl font-bold text-primary mb-2">Kelola Call To Action</h1>
                    <p class="text-gray-600">Perbarui konten section "Ajakan Bertindak" di homepage</p>
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
                        <input type="text" name="section_title" value="<?php echo htmlspecialchars($cta['section_title']); ?>" required
                            class="w-full border-2 border-gray-200 rounded-xl px-4 py-3 focus:outline-none focus:border-primary">
                    </div>

                    <!-- Main Heading -->
                    <div>
                        <label class="block text-primary font-bold mb-3">Heading Utama</label>
                        <input type="text" name="main_heading" value="<?php echo htmlspecialchars($cta['main_heading']); ?>" required
                            class="w-full border-2 border-gray-200 rounded-xl px-4 py-3 focus:outline-none focus:border-primary">
                    </div>

                    <!-- Subtitle -->
                    <div>
                        <label class="block text-primary font-bold mb-3">Subtitle</label>
                        <textarea name="subtitle" rows="2"
                            class="w-full border-2 border-gray-200 rounded-xl px-4 py-3 focus:outline-none focus:border-primary resize-none"><?php echo htmlspecialchars($cta['subtitle']); ?></textarea>
                    </div>

                    <!-- Buttons Section -->
                    <div class="pt-6 border-t-2 border-gray-200">
                        <h3 class="text-2xl font-bold text-primary mb-6">Tombol CTA</h3>
                        
                        <!-- Button 1 -->
                        <div class="mb-8 p-6 bg-gray-50 rounded-xl">
                            <h4 class="font-bold text-primary mb-4">Tombol Pertama</h4>
                            <div class="space-y-4">
                                <div>
                                    <label class="block text-primary font-bold mb-2">Teks Tombol</label>
                                    <input type="text" name="button_1_text" value="<?php echo htmlspecialchars($cta['button_1_text']); ?>"
                                        class="w-full border-2 border-gray-200 rounded-xl px-4 py-3 focus:outline-none focus:border-primary">
                                </div>
                                <div>
                                    <label class="block text-primary font-bold mb-2">Link/URL</label>
                                    <input type="text" name="button_1_link" value="<?php echo htmlspecialchars($cta['button_1_link']); ?>"
                                        placeholder="frontend/catalog.php" class="w-full border-2 border-gray-200 rounded-xl px-4 py-3 focus:outline-none focus:border-primary">
                                </div>
                                <div>
                                    <label class="block text-primary font-bold mb-2">Icon</label>
                                    <select name="button_1_icon" class="w-full border-2 border-gray-200 rounded-xl px-4 py-3 focus:outline-none focus:border-primary">
                                        <?php foreach ($icon_options as $icon => $label): ?>
                                        <option value="<?php echo $icon; ?>" <?php echo $cta['button_1_icon'] == $icon ? 'selected' : ''; ?>>
                                            <?php echo $label; ?>
                                        </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <!-- Button 2 -->
                        <div class="mb-8 p-6 bg-gray-50 rounded-xl">
                            <h4 class="font-bold text-primary mb-4">Tombol Kedua</h4>
                            <div class="space-y-4">
                                <div>
                                    <label class="block text-primary font-bold mb-2">Teks Tombol</label>
                                    <input type="text" name="button_2_text" value="<?php echo htmlspecialchars($cta['button_2_text']); ?>"
                                        class="w-full border-2 border-gray-200 rounded-xl px-4 py-3 focus:outline-none focus:border-primary">
                                </div>
                                <div>
                                    <label class="block text-primary font-bold mb-2">Link/URL</label>
                                    <input type="text" name="button_2_link" value="<?php echo htmlspecialchars($cta['button_2_link']); ?>"
                                        placeholder="#contact-form" class="w-full border-2 border-gray-200 rounded-xl px-4 py-3 focus:outline-none focus:border-primary">
                                </div>
                                <div>
                                    <label class="block text-primary font-bold mb-2">Icon</label>
                                    <select name="button_2_icon" class="w-full border-2 border-gray-200 rounded-xl px-4 py-3 focus:outline-none focus:border-primary">
                                        <?php foreach ($icon_options as $icon => $label): ?>
                                        <option value="<?php echo $icon; ?>" <?php echo $cta['button_2_icon'] == $icon ? 'selected' : ''; ?>>
                                            <?php echo $label; ?>
                                        </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Styling Section -->
                    <div class="pt-6 border-t-2 border-gray-200">
                        <h3 class="text-2xl font-bold text-primary mb-6">Styling</h3>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-primary font-bold mb-3">Warna Background</label>
                                <select name="background_color" class="w-full border-2 border-gray-200 rounded-xl px-4 py-3 focus:outline-none focus:border-primary">
                                    <?php foreach ($bg_color_options as $color => $label): ?>
                                    <option value="<?php echo $color; ?>" <?php echo $cta['background_color'] == $color ? 'selected' : ''; ?>>
                                        <?php echo $label; ?>
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div>
                                <label class="block text-primary font-bold mb-3">Warna Teks</label>
                                <select name="text_color" class="w-full border-2 border-gray-200 rounded-xl px-4 py-3 focus:outline-none focus:border-primary">
                                    <?php foreach ($text_color_options as $color => $label): ?>
                                    <option value="<?php echo $color; ?>" <?php echo $cta['text_color'] == $color ? 'selected' : ''; ?>>
                                        <?php echo $label; ?>
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- Active Status -->
                    <div class="flex items-center space-x-3 p-4 bg-gray-50 rounded-xl">
                        <input type="checkbox" name="is_active" id="is_active" <?php echo $cta['is_active'] ? 'checked' : ''; ?>
                            class="w-5 h-5 rounded border-gray-300">
                        <label for="is_active" class="text-primary font-bold">Aktifkan section ini di homepage</label>
                    </div>

                    <!-- Submit Button -->
                    <div class="flex items-center space-x-4 pt-6">
                        <button type="submit" name="update_cta" class="flex-1 bg-primary hover:bg-secondary text-white font-bold py-4 rounded-xl transition-all shadow-lg transform hover:scale-105">
                            <i class="fas fa-save mr-2"></i>
                            Simpan Perubahan
                        </button>
                        <a href="../../index.php" target="_blank" class="px-6 py-4 bg-blue-500 hover:bg-blue-600 text-white font-bold rounded-xl transition-all">
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
