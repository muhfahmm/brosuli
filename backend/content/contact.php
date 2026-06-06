<?php
require_once '../auth/auth.php';
require_once '../../db/db.php';
requireLogin();

// Handle AJAX requests for delete/update operations
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json');
    
    // Handle Delete
    if (isset($_POST['delete_msg'])) {
        $id = $_POST['delete_msg'];
        try {
            $stmt = $pdo->prepare("DELETE FROM tb_contact_messages WHERE id = ?");
            $stmt->execute([$id]);
            echo json_encode(['success' => true, 'message' => 'Pesan berhasil dihapus!']);
            exit();
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
            exit();
        }
    }
    
    // Handle Mark as Read
    if (isset($_POST['mark_read'])) {
        $id = $_POST['mark_read'];
        try {
            $stmt = $pdo->prepare("UPDATE tb_contact_messages SET status = 'read' WHERE id = ?");
            $stmt->execute([$id]);
            echo json_encode(['success' => true, 'message' => 'Pesan ditandai telah terbaca!']);
            exit();
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
            exit();
        }
    }
    
    // Handle Mark All as Read
    if (isset($_POST['mark_all_read'])) {
        try {
            $stmt = $pdo->prepare("UPDATE tb_contact_messages SET status = 'read' WHERE status = 'unread'");
            $stmt->execute();
            echo json_encode(['success' => true, 'message' => 'Semua pesan ditandai telah terbaca!']);
            exit();
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
            exit();
        }
    }
    
    // Handle Delete All Read
    if (isset($_POST['delete_all_read'])) {
        try {
            $stmt = $pdo->prepare("DELETE FROM tb_contact_messages WHERE status = 'read'");
            $stmt->execute();
            echo json_encode(['success' => true, 'message' => 'Semua pesan yang terbaca telah dihapus!']);
            exit();
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
            exit();
        }
    }
}

// Get current contact section data
$stmt = $pdo->query("SELECT * FROM tb_contact_section LIMIT 1");
$contact = $stmt->fetch();

// Handle Update (Form submission)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_contact'])) {
    try {
        $stmt = $pdo->prepare("UPDATE tb_contact_section SET 
            section_title = ?,
            main_heading = ?,
            subtitle = ?,
            form_submit_button = ?,
            contact_note = ?,
            whatsapp_number = ?,
            phone_number = ?,
            is_active = ?
            WHERE id = ?");
        
        $stmt->execute([
            $_POST['section_title'],
            $_POST['main_heading'],
            $_POST['subtitle'],
            $_POST['form_submit_button'],
            $_POST['contact_note'],
            $_POST['whatsapp_number'],
            $_POST['phone_number'],
            isset($_POST['is_active']) ? 1 : 0,
            $contact['id']
        ]);
        
        $success_msg = "Konten Hubungi Kami berhasil diperbarui!";
        // Refresh data
        $stmt = $pdo->query("SELECT * FROM tb_contact_section LIMIT 1");
        $contact = $stmt->fetch();
    } catch (Exception $e) {
        $error_msg = "Error: " . $e->getMessage();
    }
}

// Get contact messages
$stmt = $pdo->query("SELECT * FROM tb_contact_messages ORDER BY created_at DESC LIMIT 50");
$messages = $stmt->fetchAll();

// Count unread messages
$unread_stmt = $pdo->query("SELECT COUNT(*) FROM tb_contact_messages WHERE status = 'unread'");
$unread_count = $unread_stmt->fetchColumn();

// Count read messages
$read_stmt = $pdo->query("SELECT COUNT(*) FROM tb_contact_messages WHERE status = 'read'");
$read_count = $read_stmt->fetchColumn();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Hubungi Kami - Brosuli Admin</title>
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
                    <h1 class="text-4xl font-bold text-primary mb-2">Kelola Hubungi Kami</h1>
                    <p class="text-gray-600">Perbarui konten form contact dan lihat pesan pelanggan</p>
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

                <!-- Contact Section Settings -->
                <form method="POST" class="bg-white rounded-2xl shadow-lg p-8 space-y-6 mb-8">
                    <h2 class="text-2xl font-bold text-primary">Pengaturan Section</h2>

                    <!-- Section Title -->
                    <div>
                        <label class="block text-primary font-bold mb-3">Judul Section</label>
                        <input type="text" name="section_title" value="<?php echo htmlspecialchars($contact['section_title']); ?>" required
                            class="w-full border-2 border-gray-200 rounded-xl px-4 py-3 focus:outline-none focus:border-primary">
                    </div>

                    <!-- Main Heading -->
                    <div>
                        <label class="block text-primary font-bold mb-3">Heading Utama</label>
                        <input type="text" name="main_heading" value="<?php echo htmlspecialchars($contact['main_heading']); ?>" required
                            class="w-full border-2 border-gray-200 rounded-xl px-4 py-3 focus:outline-none focus:border-primary">
                    </div>

                    <!-- Subtitle -->
                    <div>
                        <label class="block text-primary font-bold mb-3">Subtitle</label>
                        <textarea name="subtitle" rows="2"
                            class="w-full border-2 border-gray-200 rounded-xl px-4 py-3 focus:outline-none focus:border-primary resize-none"><?php echo htmlspecialchars($contact['subtitle']); ?></textarea>
                    </div>

                    <!-- Form Submit Button -->
                    <div>
                        <label class="block text-primary font-bold mb-3">Teks Tombol Submit</label>
                        <input type="text" name="form_submit_button" value="<?php echo htmlspecialchars($contact['form_submit_button']); ?>"
                            class="w-full border-2 border-gray-200 rounded-xl px-4 py-3 focus:outline-none focus:border-primary">
                    </div>

                    <!-- Contact Note -->
                    <div>
                        <label class="block text-primary font-bold mb-3">Catatan Kontak (di bawah form)</label>
                        <input type="text" name="contact_note" value="<?php echo htmlspecialchars($contact['contact_note']); ?>"
                            class="w-full border-2 border-gray-200 rounded-xl px-4 py-3 focus:outline-none focus:border-primary">
                    </div>

                    <!-- Contact Information -->
                    <div class="pt-6 border-t-2 border-gray-200">
                        <h3 class="text-xl font-bold text-primary mb-4">Informasi Kontak</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-primary font-bold mb-3">Nomor WhatsApp</label>
                                <input type="text" name="whatsapp_number" value="<?php echo htmlspecialchars($contact['whatsapp_number']); ?>"
                                    placeholder="62895327349264" class="w-full border-2 border-gray-200 rounded-xl px-4 py-3 focus:outline-none focus:border-primary">
                                <p class="text-xs text-gray-500 mt-1">Format: 62 (tanpa +)</p>
                            </div>
                            <div>
                                <label class="block text-primary font-bold mb-3">Nomor Telepon</label>
                                <input type="text" name="phone_number" value="<?php echo htmlspecialchars($contact['phone_number']); ?>"
                                    placeholder="+6289532734926" class="w-full border-2 border-gray-200 rounded-xl px-4 py-3 focus:outline-none focus:border-primary">
                                <p class="text-xs text-gray-500 mt-1">Format: +62 atau 0</p>
                            </div>
                        </div>
                    </div>

                    <!-- Active Status -->
                    <div class="flex items-center space-x-3 p-4 bg-gray-50 rounded-xl">
                        <input type="checkbox" name="is_active" id="is_active" <?php echo $contact['is_active'] ? 'checked' : ''; ?>
                            class="w-5 h-5 rounded border-gray-300">
                        <label for="is_active" class="text-primary font-bold">Aktifkan section ini di homepage</label>
                    </div>

                    <!-- Submit Button -->
                    <div class="flex items-center space-x-4 pt-6">
                        <button type="submit" name="update_contact" class="flex-1 bg-primary hover:bg-secondary text-white font-bold py-4 rounded-xl transition-all shadow-lg transform hover:scale-105">
                            <i class="fas fa-save mr-2"></i>
                            Simpan Perubahan
                        </button>
                        <a href="../../index.php#contact-form" target="_blank" class="px-6 py-4 bg-blue-500 hover:bg-blue-600 text-white font-bold rounded-xl transition-all">
                            <i class="fas fa-external-link-alt mr-2"></i>
                            Lihat di Website
                        </a>
                    </div>
                </form>

                <!-- Contact Messages -->
                <div class="bg-white rounded-2xl shadow-lg overflow-hidden">
                    <div class="p-8 border-b border-gray-200 flex items-center justify-between gap-4">
                        <h2 class="text-2xl font-bold text-primary">Pesan Masuk (<?php echo count($messages); ?> total)</h2>
                        <div class="flex items-center gap-3 flex-wrap justify-end">
                            <?php if ($unread_count > 0): ?>
                            <button type="button" onclick="markAllRead()" class="inline-flex items-center gap-2 bg-green-500 hover:bg-green-600 text-white px-4 py-2 rounded-full font-bold text-sm transition-all whitespace-nowrap">
                                <i class="fas fa-check-double"></i>
                                Tandai Semua Terbaca
                            </button>
                            <span class="bg-red-500 text-white px-4 py-2 rounded-full font-bold inline-flex items-center gap-2 whitespace-nowrap">
                                <i class="fas fa-bell"></i><?php echo $unread_count; ?> Belum Terbaca
                            </span>
                            <?php else: ?>
                            <span class="bg-green-100 text-green-700 px-4 py-2 rounded-full font-bold inline-flex items-center gap-2">
                                <i class="fas fa-check-circle"></i>Semua Terbaca
                            </span>
                            <?php endif; ?>
                            
                            <?php if ($read_count > 0): ?>
                            <button type="button" onclick="deleteAllRead()" class="inline-flex items-center gap-2 bg-red-500 hover:bg-red-600 text-white px-4 py-2 rounded-full font-bold text-sm transition-all whitespace-nowrap">
                                <i class="fas fa-trash-alt"></i>
                                Hapus Semua Terbaca (<?php echo $read_count; ?>)
                            </button>
                            <?php endif; ?>
                        </div>
                    </div>

                    <?php if (empty($messages)): ?>
                    <div class="p-8 text-center text-gray-500">
                        <i class="fas fa-inbox text-4xl mb-4 block opacity-30"></i>
                        <p>Belum ada pesan masuk</p>
                    </div>
                    <?php else: ?>
                    <div class="space-y-4 p-8">
                        <?php foreach ($messages as $msg): ?>
                        <div class="border-l-4 <?php echo $msg['status'] === 'unread' ? 'border-red-500 bg-red-50' : 'border-gray-300 bg-gray-50'; ?> p-6 rounded-r-lg hover:shadow-lg transition-all">
                            <div class="flex items-start justify-between mb-3">
                                <div class="flex-1">
                                    <div class="flex items-center gap-3">
                                        <h4 class="font-bold text-primary text-lg"><?php echo htmlspecialchars($msg['contact_name']); ?></h4>
                                        <?php if ($msg['status'] === 'unread'): ?>
                                        <span class="bg-red-500 text-white text-xs font-bold px-3 py-1 rounded-full">
                                            <i class="fas fa-circle-dot mr-1"></i>BELUM TERBACA
                                        </span>
                                        <?php endif; ?>
                                    </div>
                                    <p class="text-sm text-gray-500"><?php echo htmlspecialchars($msg['contact_email']); ?></p>
                                </div>
                                <span class="text-xs text-gray-400 whitespace-nowrap ml-4"><?php echo date('d M Y H:i', strtotime($msg['created_at'])); ?></span>
                            </div>
                            <p class="text-gray-700 leading-relaxed mb-4"><?php echo nl2br(htmlspecialchars($msg['contact_message'])); ?></p>
                            
                            <!-- Action Buttons -->
                            <div class="flex items-center gap-2 pt-3 border-t border-gray-200">
                                <?php if ($msg['status'] === 'unread'): ?>
                                <button type="button" onclick="markRead(<?php echo $msg['id']; ?>)" class="inline-flex items-center gap-2 bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg font-semibold text-sm transition-all">
                                    <i class="fas fa-check-circle"></i>
                                    Tandai Terbaca
                                </button>
                                <?php else: ?>
                                <span class="inline-flex items-center gap-2 bg-green-100 text-green-700 px-4 py-2 rounded-lg font-semibold text-sm">
                                    <i class="fas fa-check-double"></i>
                                    Sudah Terbaca
                                </span>
                                <?php endif; ?>
                                
                                <button type="button" onclick="deleteMessage(<?php echo $msg['id']; ?>)" class="inline-flex items-center gap-2 bg-red-500 hover:bg-red-600 text-white px-4 py-2 rounded-lg font-semibold text-sm transition-all">
                                    <i class="fas fa-trash"></i>
                                    Hapus
                                </button>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </main>
    </div>

    <script>
        // Notification system
        function showNotification(message, type = 'success') {
            const bgColor = type === 'success' ? 'bg-green-100' : 'bg-red-100';
            const textColor = type === 'success' ? 'text-green-700' : 'text-red-700';
            const icon = type === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle';
            
            const notif = document.createElement('div');
            notif.className = `fixed top-4 right-4 ${bgColor} ${textColor} p-4 rounded-lg flex items-center gap-3 shadow-lg z-50 animate-pulse`;
            notif.innerHTML = `<i class="fas ${icon}"></i><span>${message}</span>`;
            
            document.body.appendChild(notif);
            
            setTimeout(() => {
                notif.remove();
            }, 3000);
        }

        // Mark single message as read
        function markRead(msgId) {
            if (!confirm('Tandai pesan ini sebagai terbaca?')) return;
            
            const formData = new FormData();
            formData.append('mark_read', msgId);
            
            fetch('<?php echo $_SERVER['PHP_SELF']; ?>', {
                method: 'POST',
                body: formData
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    showNotification(data.message, 'success');
                    location.reload();
                } else {
                    showNotification(data.message, 'error');
                }
            })
            .catch(err => {
                console.error(err);
                showNotification('Error: ' + err.message, 'error');
            });
        }

        // Mark all messages as read
        function markAllRead() {
            if (!confirm('Tandai semua pesan sebagai terbaca?')) return;
            
            const formData = new FormData();
            formData.append('mark_all_read', '1');
            
            fetch('<?php echo $_SERVER['PHP_SELF']; ?>', {
                method: 'POST',
                body: formData
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    showNotification(data.message, 'success');
                    location.reload();
                } else {
                    showNotification(data.message, 'error');
                }
            })
            .catch(err => {
                console.error(err);
                showNotification('Error: ' + err.message, 'error');
            });
        }

        // Delete single message
        function deleteMessage(msgId) {
            if (!confirm('Yakin hapus pesan ini?')) return;
            
            const formData = new FormData();
            formData.append('delete_msg', msgId);
            
            fetch('<?php echo $_SERVER['PHP_SELF']; ?>', {
                method: 'POST',
                body: formData
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    showNotification(data.message, 'success');
                    location.reload();
                } else {
                    showNotification(data.message, 'error');
                }
            })
            .catch(err => {
                console.error(err);
                showNotification('Error: ' + err.message, 'error');
            });
        }

        // Delete all read messages
        function deleteAllRead() {
            if (!confirm('Hapus semua pesan yang sudah terbaca?')) return;
            
            const formData = new FormData();
            formData.append('delete_all_read', '1');
            
            fetch('<?php echo $_SERVER['PHP_SELF']; ?>', {
                method: 'POST',
                body: formData
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    showNotification(data.message, 'success');
                    location.reload();
                } else {
                    showNotification(data.message, 'error');
                }
            })
            .catch(err => {
                console.error(err);
                showNotification('Error: ' + err.message, 'error');
            });
        }
    </script>
</body>
</html>
