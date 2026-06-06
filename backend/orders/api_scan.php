<?php
require_once '../../db/db.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $barcode = $_POST['barcode'] ?? null;
    
    if ($barcode) {
        // Clear previous queue (optional, to keep it fresh for 1 cashier)
        $pdo->exec("DELETE FROM tb_scan_queue");
        
        $stmt = $pdo->prepare("INSERT INTO tb_scan_queue (barcode) VALUES (?)");
        $success = $stmt->execute([$barcode]);
        
        if ($success) {
            echo json_encode(['success' => true, 'message' => 'Scan queued']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Database error']);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'No barcode provided']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid method']);
}
?>
