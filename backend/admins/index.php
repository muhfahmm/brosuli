<?php
require_once '../auth/auth.php';
require_once '../../db/db.php';
requireLogin();

// Auto-create branches table and modify others if needed
$pdo->exec("CREATE TABLE IF NOT EXISTS branches (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    address TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)");

// Add columns to admin and orders if not exists
try {
    $pdo->exec("ALTER TABLE admin ADD COLUMN role ENUM('superadmin', 'admin_cabang') DEFAULT 'superadmin'");
    $pdo->exec("ALTER TABLE admin ADD COLUMN branch_id INT NULL");
    $pdo->exec("ALTER TABLE admin ADD FOREIGN KEY (branch_id) REFERENCES branches(id) ON DELETE SET NULL");
} catch (Exception $e) {}

try {
    $pdo->exec("ALTER TABLE orders ADD COLUMN branch_id INT NULL");
    $pdo->exec("ALTER TABLE orders ADD FOREIGN KEY (branch_id) REFERENCES branches(id) ON DELETE SET NULL");
} catch (Exception $e) {}

// Seed branches if empty or missing
$branches_to_seed = [
    ['Brosuli Boyolali (Utama)', 'Jl. Pandanaran No.275, Sidoharjo, Banaran, Kec. Boyolali'],
    ['Brosuli Mojosongo', 'Ruko Techno Park, Jl. Merdeka Timur, Mojosongo'],
    ['Brosuli Kartasura', 'Jl. Brigjen Katamso, Ngemplak, Kartasura'],
    ['Brosuli Baki', 'Jl. Ovensari Raya No.21, Kadilangu, Baki'],
    ['Brosuli Mojolaban', 'Jl. Lettu Rm.Hartono No.39, Gadingan, Mojolaban'],
    ['Brosuli Colomadu', 'Jl. Adi Sumarmo, Krobyongan, Gawanan'],
    ['Brosuli Pedan', 'Jl. Raya Ps. Pedan, Kedungan, Pedan'],
    ['Brosuli Jatinom', 'Jl. Klaten-Boyolali No.KM. 8, Bonyokan, Jatinom']
];

foreach ($branches_to_seed as $b) {
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM branches WHERE name = ?");
    $stmt->execute([$b[0]]);
    if ($stmt->fetchColumn() == 0) {
        $pdo->prepare("INSERT INTO branches (name, address) VALUES (?, ?)")->execute($b);
    }
}

// One-time cleanup for existing duplicates
$pdo->exec("DELETE t1 FROM branches t1 INNER JOIN branches t2 WHERE t1.id > t2.id AND t1.name = t2.name");

// Fetch all admins with branch info
$stmt = $pdo->query("SELECT a.id, a.username, a.role, a.created_at, b.name as branch_name 
                     FROM admin a 
                     LEFT JOIN branches b ON a.branch_id = b.id 
                     ORDER BY a.created_at DESC");
$admins = $stmt->fetchAll();

// Fetch all branches for the dropdown
$branch_stmt = $pdo->query("SELECT id, name FROM branches ORDER BY name ASC");
$branches = $branch_stmt->fetchAll();

// Handle deletion
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    
    // Prevent deleting the last admin or yourself if needed (basic check)
    $stmt = $pdo->query("SELECT COUNT(*) FROM admin");
    $count = $stmt->fetchColumn();
    
    if ($count > 1) {
        $stmt = $pdo->prepare("DELETE FROM admin WHERE id = ?");
        $stmt->execute([$id]);
        header('Location: index.php?msg=success');
    } else {
        header('Location: index.php?msg=error_last');
    }
    exit();
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Admin - Brosuli Admin</title>
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
        <header class="bg-white shadow-sm px-8 py-4 flex justify-between items-center">
            <h2 class="text-xl font-semibold text-gray-800">Kelola Pengelola Admin</h2>
            <div class="flex items-center space-x-4">
                <span class="text-gray-600 italic">Akun Aktif: <strong><?php echo $_SESSION['admin_username']; ?></strong></span>
                <button onclick="document.getElementById('addAdminModal').classList.remove('hidden')" 
                    class="bg-amber-600 text-white px-4 py-2 rounded-lg hover:bg-amber-700 transition-colors flex items-center space-x-2 shadow-md">
                    <i class="fas fa-plus"></i>
                    <span>Tambah Admin Baru</span>
                </button>
            </div>
        </header>

        <!-- Content -->
        <div class="p-8">
            <?php if (isset($_GET['msg'])): ?>
                <?php if ($_GET['msg'] == 'success'): ?>
                    <div class="bg-green-100 border border-green-200 text-green-700 px-4 py-3 rounded-lg mb-6">Admin berhasil dihapus.</div>
                <?php elseif ($_GET['msg'] == 'error_last'): ?>
                    <div class="bg-red-100 border border-red-200 text-red-700 px-4 py-3 rounded-lg mb-6">Gagal: Minimal harus ada 1 admin di sistem.</div>
                <?php elseif ($_GET['msg'] == 'added'): ?>
                    <div class="bg-blue-100 border border-blue-200 text-blue-700 px-4 py-3 rounded-lg mb-6">Admin baru berhasil ditambahkan!</div>
                <?php endif; ?>
            <?php endif; ?>

            <div class="bg-white rounded-xl shadow-sm overflow-hidden border border-gray-100">
                <table class="w-full text-left">
                    <thead class="bg-gray-50 border-b border-gray-100">
                        <tr>
                            <th class="px-6 py-4 text-xs font-semibold text-gray-500 uppercase tracking-wider">ID</th>
                            <th class="px-6 py-4 text-xs font-semibold text-gray-500 uppercase tracking-wider">Username</th>
                            <th class="px-6 py-4 text-xs font-semibold text-gray-500 uppercase tracking-wider">Role</th>
                            <th class="px-6 py-4 text-xs font-semibold text-gray-500 uppercase tracking-wider">Wilayah Kelola</th>
                            <th class="px-6 py-4 text-xs font-semibold text-gray-500 uppercase tracking-wider">Tanggal Terdaftar</th>
                            <th class="px-6 py-4 text-xs font-semibold text-gray-500 uppercase tracking-wider text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <?php foreach ($admins as $admin): ?>
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="px-6 py-4 text-sm text-gray-400">#<?php echo $admin['id']; ?></td>
                            <td class="px-6 py-4">
                                <div class="flex items-center space-x-3">
                                    <div class="w-8 h-8 bg-amber-100 text-amber-700 rounded-full flex items-center justify-center font-bold">
                                        <?php echo strtoupper(substr($admin['username'], 0, 1)); ?>
                                    </div>
                                    <span class="font-medium text-gray-800"><?php echo htmlspecialchars($admin['username']); ?></span>
                                    <?php if ($admin['username'] == $_SESSION['admin_username']): ?>
                                        <span class="text-[10px] bg-blue-100 text-blue-600 px-2 py-0.5 rounded-full uppercase font-bold">Anda</span>
                                    <?php endif; ?>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <span class="px-3 py-1 rounded-full text-[10px] font-bold uppercase <?php echo $admin['role'] == 'superadmin' ? 'bg-purple-100 text-purple-600' : 'bg-amber-100 text-amber-600'; ?>">
                                    <?php echo $admin['role']; ?>
                                </span>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-600">
                                <?php echo $admin['branch_name'] ?: '<span class="text-gray-300 italic">Semua Cabang</span>'; ?>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-500">
                                <?php echo date('d M Y, H:i', strtotime($admin['created_at'])); ?>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <?php if ($admin['username'] != $_SESSION['admin_username']): ?>
                                <button onclick="confirmDelete(<?php echo $admin['id']; ?>)" class="text-red-400 hover:text-red-600 transition-colors">
                                    <i class="fas fa-trash"></i>
                                </button>
                                <?php else: ?>
                                <span class="text-gray-300 italic text-xs">Proteksi</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>

    <!-- Add Admin Modal -->
    <div id="addAdminModal" class="hidden fixed inset-0 bg-black/50 backdrop-blur-sm z-50 flex items-center justify-center p-4">
        <div class="bg-white rounded-2xl w-full max-w-md shadow-2xl overflow-hidden animate-in fade-in zoom-in duration-300">
            <div class="p-6 bg-amber-600 text-white flex justify-between items-center">
                <h3 class="text-xl font-bold">Tambah Admin Baru</h3>
                <button onclick="document.getElementById('addAdminModal').classList.add('hidden')" class="hover:rotate-90 transition-transform">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>
            <form action="process_admin.php" method="POST" class="p-6 space-y-4">
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Username</label>
                    <input type="text" name="username" required class="w-full px-4 py-2 rounded-xl border border-gray-200 focus:ring-2 focus:ring-amber-500 outline-none transition-all">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Password</label>
                    <input type="password" name="password" required class="w-full px-4 py-2 rounded-xl border border-gray-200 focus:ring-2 focus:ring-amber-500 outline-none transition-all">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Role</label>
                    <select name="role" id="roleSelect" onchange="toggleBranchSelect()" class="w-full px-4 py-2 rounded-xl border border-gray-200 focus:ring-2 focus:ring-amber-500 outline-none transition-all">
                        <option value="superadmin">Superadmin (Akses Penuh)</option>
                        <option value="admin_cabang">Admin Cabang (Terbatas)</option>
                    </select>
                </div>
                <div id="branchSelectWrapper" class="hidden">
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Wilayah Cabang</label>
                    <select name="branch_id" class="w-full px-4 py-2 rounded-xl border border-gray-200 focus:ring-2 focus:ring-amber-500 outline-none transition-all">
                        <option value="">Pilih Cabang...</option>
                        <?php foreach ($branches as $branch): ?>
                            <option value="<?php echo $branch['id']; ?>"><?php echo htmlspecialchars($branch['name']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="pt-4">
                    <button type="submit" class="w-full bg-amber-600 text-white py-3 rounded-xl font-bold hover:bg-amber-700 transition-all shadow-lg">Simpan Akun Admin</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Confirm Modal Integration -->
    <?php include '../includes/modals/confirm_modal.php'; ?>

    <script>
        function confirmDelete(id) {
            showConfirm(
                'Hapus Admin?', 
                'Akun ini tidak akan bisa login lagi ke Admin Panel. Lanjutkan?', 
                function() {
                    window.location.href = '?delete=' + id;
                }
            );
        }

        function toggleBranchSelect() {
            const role = document.getElementById('roleSelect').value;
            const wrapper = document.getElementById('branchSelectWrapper');
            if (role === 'admin_cabang') {
                wrapper.classList.remove('hidden');
            } else {
                wrapper.classList.add('hidden');
            }
        }
    </script>
</body>
</html>
