<?php
session_start();
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $branch_id = $_POST['branch_id'] ?? null;
    
    if ($branch_id !== null && $branch_id !== '') {
        $_SESSION['user_branch_id'] = $branch_id;
        echo json_encode(['success' => true]);
    } else {
        // Reset/Clear branch selection
        unset($_SESSION['user_branch_id']);
        echo json_encode(['success' => true, 'message' => 'Branch cleared']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
}
?>
