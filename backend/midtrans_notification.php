<?php
require_once '../config.php';
require_once '../db/db.php';

// Get JSON data from Midtrans
$input = file_get_contents('php://input');
$notification = json_decode($input, true);

if (!$notification) exit;

$order_id = $notification['order_id'];
$transaction_status = $notification['transaction_status'];
$fraud_status = $notification['fraud_status'];

// Simplified status mapping
$status = 'pending';

if ($transaction_status == 'capture') {
    if ($fraud_status == 'challenge') {
        $status = 'challenge';
    } else if ($fraud_status == 'accept') {
        $status = 'settlement';
    }
} else if ($transaction_status == 'settlement') {
    $status = 'settlement';
} else if ($transaction_status == 'cancel' || $transaction_status == 'deny' || $transaction_status == 'expire') {
    $status = 'failure';
} else if ($transaction_status == 'pending') {
    $status = 'pending';
}

// Update Database
try {
    $stmt = $pdo->prepare("UPDATE orders SET payment_status = ? WHERE order_id = ?");
    $stmt->execute([$status, $order_id]);
    echo "OK";
} catch (PDOException $e) {
    http_response_code(500);
}
?>
