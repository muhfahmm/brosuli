<?php
require_once '../auth/auth.php';
require_once '../../db/db.php';
requireLogin();

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $product_id = $_POST['product_id'] ?? null;
    
    if ($product_id) {
        // Check if already in queue, if yes increase quantity, else insert
        $stmt = $pdo->prepare("SELECT id, quantity FROM label_print_queue WHERE product_id = ?");
        $stmt->execute([$product_id]);
        $existing = $stmt->fetch();
        
        if ($existing) {
            $stmt = $pdo->prepare("UPDATE label_print_queue SET quantity = quantity + 1 WHERE id = ?");
            $success = $stmt->execute([$existing['id']]);
        } else {
            $stmt = $pdo->prepare("INSERT INTO label_print_queue (product_id) VALUES (?)");
            $success = $stmt->execute([$product_id]);
        }
        
        echo json_encode(['success' => $success]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Invalid product ID']);
    }
}
?>
