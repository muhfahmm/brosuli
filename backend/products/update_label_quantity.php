<?php
require_once '../auth/auth.php';
require_once '../../db/db.php';
requireLogin();

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'] ?? null;
    $delta = $_POST['delta'] ?? 0;
    
    if ($id) {
        // Update quantity
        $stmt = $pdo->prepare("UPDATE tb_label_print_queue SET quantity = GREATEST(1, quantity + ?) WHERE id = ?");
        $success = $stmt->execute([$delta, $id]);
        
        // Fetch new quantity
        $stmt = $pdo->prepare("SELECT quantity FROM tb_label_print_queue WHERE id = ?");
        $stmt->execute([$id]);
        $new_qty = $stmt->fetchColumn();
        
        echo json_encode(['success' => $success, 'new_quantity' => $new_qty]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Invalid ID']);
    }
}
?>
