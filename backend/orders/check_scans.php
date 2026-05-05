<?php
require_once '../../db/db.php';

header('Content-Type: application/json');

// Get the latest scan
$stmt = $pdo->query("SELECT * FROM scan_queue ORDER BY created_at DESC LIMIT 1");
$scan = $stmt->fetch();

if ($scan) {
    // Delete it so it's not processed again
    $stmt = $pdo->prepare("DELETE FROM scan_queue WHERE id = ?");
    $stmt->execute([$scan['id']]);
    
    echo json_encode(['success' => true, 'barcode' => $scan['barcode']]);
} else {
    echo json_encode(['success' => false]);
}
?>
