<?php
require_once '../auth/auth.php';
require_once '../../db/db.php';
requireLogin();

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $customer_name = $_POST['customer_name'] ?? 'Walk-in Customer';
        $total_amount = $_POST['total_amount'] ?? 0;
        $items_json = $_POST['items_json'] ?? '[]';
        $payment_method = $_POST['payment_method'] ?? 'Cash';
        
        // Generate unique Order ID
        $order_id = 'BRSL-POS-' . strtoupper(substr(uniqid(), -5)) . '-' . date('dmY');

        $branch_id = $_SESSION['admin_branch_id'] ?? null;
        
        $stmt = $pdo->prepare("INSERT INTO orders (order_id, customer_name, customer_address, total_amount, payment_status, payment_method, items_json, branch_id) 
                               VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        
        $success = $stmt->execute([
            $order_id,
            $customer_name,
            'Kasir / Offline Transaction',
            $total_amount,
            'settlement', // Auto-settled for Cashier
            $payment_method,
            $items_json,
            $branch_id
        ]);

        if ($success) {
            echo json_encode(['success' => true, 'order_id' => $order_id]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to save order to database.']);
        }
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
}
?>
