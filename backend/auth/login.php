<?php
require_once '../../db/db.php';
session_start();

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';

    $stmt = $pdo->prepare("SELECT * FROM tb_admin WHERE username = ?");
    $stmt->execute([$username]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['admin_id'] = $user['id'];
        $_SESSION['admin_username'] = $user['username'];
        $_SESSION['admin_role'] = $user['role'];
        $_SESSION['admin_branch_id'] = $user['branch_id'];
        header('Location: ../products/index.php');
        exit();
    } else {
        $error = 'Invalid username or password';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Admin - Brosuli</title>
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
            <p class="text-amber-600">Please sign in to your account</p>
        </div>

        <?php if ($error): ?>
            <div class="bg-red-50 border-l-4 border-red-500 text-red-700 p-4 mb-6 rounded shadow-sm">
                <?php echo $error; ?>
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
            <button type="submit" 
                class="w-full bg-[#4A2C2A] text-white py-3 rounded-lg font-semibold hover:bg-[#3D2422] transition-colors shadow-lg hover:shadow-xl transform hover:-translate-y-0.5 active:translate-y-0">
                Sign In
            </button>
            <div class="text-center mt-6">
                <p class="text-amber-900/60 text-sm mb-3">Don't have an admin account?</p>
                <a href="register.php" 
                    class="inline-block w-full border-2 border-[#4A2C2A] text-[#4A2C2A] py-2.5 rounded-lg font-semibold hover:bg-[#4A2C2A] hover:text-white transition-all transform hover:-translate-y-0.5 active:translate-y-0">
                    Register New Admin
                </a>
            </div>
        </form>
    </div>
</body>
</html>
