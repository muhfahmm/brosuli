<?php
require_once 'db/db.php';

try {
    $pdo->exec("CREATE TABLE IF NOT EXISTS branch_inventory (
        id INT AUTO_INCREMENT PRIMARY KEY,
        branch_id INT NOT NULL,
        product_id INT NOT NULL,
        stock INT DEFAULT 0,
        UNIQUE KEY (branch_id, product_id),
        FOREIGN KEY (branch_id) REFERENCES branches(id) ON DELETE CASCADE,
        FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
    )");

    // Initialize inventory for all products across all branches if empty
    $branches = $pdo->query("SELECT id FROM branches")->fetchAll(PDO::FETCH_COLUMN);
    $products = $pdo->query("SELECT id FROM products")->fetchAll(PDO::FETCH_COLUMN);

    foreach ($branches as $branch_id) {
        foreach ($products as $product_id) {
            $stmt = $pdo->prepare("INSERT IGNORE INTO branch_inventory (branch_id, product_id, stock) VALUES (?, ?, 0)");
            $stmt->execute([$branch_id, $product_id]);
        }
    }
    
    echo "Migration successful: branch_inventory table created and initialized.";
} catch (Exception $e) {
    echo "Migration failed: " . $e->getMessage();
}
?>
