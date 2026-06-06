<?php
require_once '../../db/db.php';
session_start();

$error = '';
$success = '';

// Check if a superadmin already exists
$super_stmt = $pdo->query("SELECT COUNT(*) FROM tb_admin WHERE role = 'superadmin'");
$superadmin_exists = $super_stmt->fetchColumn() > 0;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    $role = $_POST['role'] ?? 'admin_cabang';
    $branch_id = $_POST['branch_id'] ?? null;

    // Force role to admin_cabang if superadmin already exists
    if ($superadmin_exists && $role === 'superadmin') {
        $error = 'Pendaftaran Superadmin sudah ditutup. Silakan daftar sebagai Admin Cabang.';
    } else {
        if (empty($branch_id)) $branch_id = null;

        if ($password !== $confirm_password) {
            $error = 'Passwords do not match';
        } else {
            // Check if username already exists
            $stmt = $pdo->prepare("SELECT id FROM tb_admin WHERE username = ?");
            $stmt->execute([$username]);
            if ($stmt->fetch()) {
                $error = 'Username already taken';
            } else {
                // Hash password and insert
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare("INSERT INTO tb_admin (username, password, role, branch_id) VALUES (?, ?, ?, ?)");
                if ($stmt->execute([$username, $hashed_password, $role, $branch_id])) {
                    $success = 'Registration successful! You can now login.';
                    // Refresh superadmin exists status after successful registration
                    $superadmin_exists = true;
                } else {
                    $error = 'Something went wrong. Please try again.';
                }
            }
        }
    }
}

// Fetch branches for selection
$branch_stmt = $pdo->query("SELECT id, name FROM tb_branches ORDER BY name ASC");
$branches = $branch_stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register Admin - Brosuli</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Outfit', sans-serif; }
    </style>
</head>
<body class="bg-[#FFFBEB] flex items-center justify-center min-h-screen">
    <div class="bg-white p-8 rounded-2xl shadow-xl w-full max-w-md border border-amber-100">
        <div class="text-center mb-8">
            <h1 class="text-3xl font-bold text-[#4A2C2A]">Brosuli Admin</h1>
            <p class="text-amber-600">Create a new admin account</p>
        </div>

        <?php if ($error): ?>
            <div class="bg-red-50 border-l-4 border-red-500 text-red-700 p-4 mb-6 rounded shadow-sm">
                <?php echo $error; ?>
            </div>
        <?php endif; ?>

        <?php if ($success): ?>
            <div class="bg-green-50 border-l-4 border-green-500 text-green-700 p-4 mb-6 rounded shadow-sm">
                <?php echo $success; ?>
                <div class="mt-2">
                    <a href="login.php" class="font-bold underline">Go to Login</a>
                </div>
            </div>
        <?php endif; ?>

        <form method="POST" class="space-y-6">
            <div>
                <label class="block text-sm font-medium text-amber-900 mb-1">Username</label>
                <input type="text" name="username" required 
                    class="w-full px-4 py-3 rounded-lg border border-amber-200 focus:ring-2 focus:ring-amber-500 focus:border-amber-500 outline-none transition-all">
            </div>
            <div>
                <label class="block text-sm font-medium text-amber-900 mb-1">Password</label>
                <input type="password" name="password" required 
                    class="w-full px-4 py-3 rounded-lg border border-amber-200 focus:ring-2 focus:ring-amber-500 focus:border-amber-500 outline-none transition-all">
            </div>
            <div>
                <label class="block text-sm font-medium text-amber-900 mb-1">Confirm Password</label>
                <input type="password" name="confirm_password" required 
                    class="w-full px-4 py-3 rounded-lg border border-amber-200 focus:ring-2 focus:ring-amber-500 focus:border-amber-500 outline-none transition-all">
            </div>
            <div>
                <label class="block text-sm font-medium text-amber-900 mb-1">Role</label>
                <select name="role" id="roleSelect" onchange="toggleBranchSelect()" class="w-full px-4 py-3 rounded-lg border border-amber-200 focus:ring-2 focus:ring-amber-500 outline-none transition-all">
                    <option value="admin_cabang">Admin Cabang (Terbatas)</option>
                    <?php if (!$superadmin_exists): ?>
                        <option value="superadmin">Superadmin (Akses Penuh)</option>
                    <?php endif; ?>
                </select>
                <?php if ($superadmin_exists): ?>
                    <p class="text-[10px] text-amber-600 mt-1 italic">* Pendaftaran Superadmin sudah penuh. Akun baru akan menjadi Admin Cabang.</p>
                <?php endif; ?>
            </div>
            <div id="branchSelectWrapper">
                <label class="block text-sm font-medium text-amber-900 mb-1">Wilayah Cabang</label>
                <select name="branch_id" class="w-full px-4 py-3 rounded-lg border border-amber-200 focus:ring-2 focus:ring-amber-500 outline-none transition-all">
                    <option value="">Pilih Cabang...</option>
                    <?php foreach ($branches as $branch): ?>
                        <option value="<?php echo $branch['id']; ?>"><?php echo htmlspecialchars($branch['name']); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <script>
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
            <button type="submit" 
                class="w-full bg-[#4A2C2A] text-white py-3 rounded-lg font-semibold hover:bg-[#3D2422] transition-colors shadow-lg hover:shadow-xl transform hover:-translate-y-0.5 active:translate-y-0">
                Register Account
            </button>
            <div class="text-center mt-4">
                <a href="login.php" class="text-amber-700 hover:text-amber-900 text-sm font-medium">Already have an account? Sign In</a>
            </div>
        </form>
    </div>
</body>
</html>
