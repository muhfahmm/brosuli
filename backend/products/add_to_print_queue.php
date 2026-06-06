<?php
require_once '../auth/auth.php';
require_once '../../db/db.php';
requireLogin();

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $product_id = $_POST['product_id'] ?? null;
    
    if ($product_id) {
        // Check if this product is already in the queue
        $stmt = $pdo->prepare("SELECT id FROM tb_label_print_queue WHERE product_id = ?");
        $stmt->execute([$product_id]);
        $existing = $stmt->fetch();
        
        if ($existing) {
            // If already exists, increment quantity
            $stmt = $pdo->prepare("UPDATE tb_label_print_queue SET quantity = quantity + 1 WHERE id = ?");
            $success = $stmt->execute([$existing['id']]);
        } else {
            // Insert new item
            $stmt = $pdo->prepare("INSERT INTO tb_label_print_queue (product_id, quantity) VALUES (?, 1)");
            $success = $stmt->execute([$product_id]);
        }
        
        echo json_encode(['success' => $success]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Invalid product ID']);
    }
}
?>
