<?php
require_once '../auth/auth.php';
require_once '../../db/db.php';
requireLogin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    $role = $_POST['role'] ?? 'superadmin';
    $branch_id = $_POST['branch_id'] ?? null;
    
    // Ensure branch_id is actually NULL if empty string
    if (empty($branch_id)) $branch_id = null;
    
    if (!empty($username) && !empty($password)) {
        // Check if username exists
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM admin WHERE username = ?");
        $stmt->execute([$username]);
        if ($stmt->fetchColumn() > 0) {
            header('Location: index.php?msg=error_exists');
            exit();
        }

        // Hash password
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        
        $stmt = $pdo->prepare("INSERT INTO admin (username, password, role, branch_id) VALUES (?, ?, ?, ?)");
        if ($stmt->execute([$username, $hashed_password, $role, $branch_id])) {
            header('Location: index.php?msg=added');
        } else {
            header('Location: index.php?msg=error');
        }
    } else {
        header('Location: index.php?msg=error_empty');
    }
} else {
    header('Location: index.php');
}
?>
