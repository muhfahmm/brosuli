<?php
require_once '../../db/db.php';
session_start();

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    if ($password !== $confirm_password) {
        $error = 'Passwords do not match';
    } else {
        // Check if username already exists
        $stmt = $pdo->prepare("SELECT id FROM admin WHERE username = ?");
        $stmt->execute([$username]);
        if ($stmt->fetch()) {
            $error = 'Username already taken';
        } else {
            // Hash password and insert
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("INSERT INTO admin (username, password) VALUES (?, ?)");
            if ($stmt->execute([$username, $hashed_password])) {
                $success = 'Registration successful! You can now login.';
            } else {
                $error = 'Something went wrong. Please try again.';
            }
        }
    }
}
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
