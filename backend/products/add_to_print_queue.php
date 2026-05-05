<?php
require_once '../auth/auth.php';
require_once '../../db/db.php';
requireLogin();

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $product_id = $_POST['product_id'] ?? null;
    
    if ($product_id) {
        // Check if queue has any product at all
        $stmt = $pdo->query("SELECT id FROM label_print_queue LIMIT 1");
        $queued = $stmt->fetch();
        
        if ($queued) {
            echo json_encode(['success' => false, 'message' => 'Antrean sudah berisi produk. Silakan cetak atau kosongkan antrean sebelum menambah produk lain.']);
            exit();
        }

        // Insert new item
        $stmt = $pdo->prepare("INSERT INTO label_print_queue (product_id) VALUES (?)");
        $success = $stmt->execute([$product_id]);
        
        echo json_encode(['success' => $success]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Invalid product ID']);
    }
}
?>
