<?php
require_once '../auth/auth.php';
require_once '../../db/db.php';
requireLogin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'] ?? null;
    $name = $_POST['name'] ?? '';
    $barcode = $_POST['barcode'] ?? null;
    
    // Auto-generate barcode if empty
    if (empty($barcode)) {
        $barcode = 'BSL' . time() . str_pad(rand(0, 999), 3, '0', STR_PAD_LEFT);
    }

    $category_id = !empty($_POST['category_id']) ? $_POST['category_id'] : null;
    $price = $_POST['price'] ?? 0;
    $description = $_POST['description'] ?? '';
    $is_featured = isset($_POST['is_featured']) ? 1 : 0;
    
    $image_url = null;
    if (isset($_FILES['image']) && $_FILES['image']['error'] === 0) {
        $upload_dir = '../../uploads/';
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }
        $file_name = time() . '_' . basename($_FILES['image']['name']);
        $target_file = $upload_dir . $file_name;
        
        if (move_uploaded_file($_FILES['image']['tmp_name'], $target_file)) {
            $image_url = 'uploads/' . $file_name;
        }
    }

    if ($id) {
        // Update
        if ($image_url) {
            $stmt = $pdo->prepare("UPDATE products SET name = ?, barcode = ?, category_id = ?, price = ?, description = ?, image_url = ?, is_featured = ? WHERE id = ?");
            $stmt->execute([$name, $barcode, $category_id, $price, $description, $image_url, $is_featured, $id]);
        } else {
            $stmt = $pdo->prepare("UPDATE products SET name = ?, barcode = ?, category_id = ?, price = ?, description = ?, is_featured = ? WHERE id = ?");
            $stmt->execute([$name, $barcode, $category_id, $price, $description, $is_featured, $id]);
        }
    } else {
        // Create
        $stmt = $pdo->prepare("INSERT INTO products (name, barcode, category_id, price, description, image_url, is_featured) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$name, $barcode, $category_id, $price, $description, $image_url ?: '', $is_featured]);
    }

    header('Location: index.php');
    exit();
}
?>
