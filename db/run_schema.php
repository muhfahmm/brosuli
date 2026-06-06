<?php
require_once 'db.php';

try {
    // Read schema file
    $schema = file_get_contents('schema.sql');
    
    // Split by semicolon and execute each statement
    $statements = array_filter(array_map('trim', explode(';', $schema)));
    
    foreach ($statements as $statement) {
        if (!empty($statement)) {
            // Skip comments
            if (strpos(trim($statement), '--') === 0) {
                continue;
            }
            
            $pdo->exec($statement);
        }
    }
    
    echo "<div style='background: #d4edda; color: #155724; padding: 20px; border-radius: 5px; font-family: Arial;'>";
    echo "<h3>✅ Schema berhasil dijalankan!</h3>";
    echo "<p>Semua tabel dengan prefix tb_ telah berhasil dibuat.</p>";
    echo "<p><a href='../../index.php' style='color: #155724; text-decoration: none; font-weight: bold;'>← Kembali ke Homepage</a></p>";
    echo "</div>";
    
} catch (PDOException $e) {
    echo "<div style='background: #f8d7da; color: #721c24; padding: 20px; border-radius: 5px; font-family: Arial;'>";
    echo "<h3>❌ Error saat menjalankan schema:</h3>";
    echo "<pre>" . htmlspecialchars($e->getMessage()) . "</pre>";
    echo "<p><a href='../../index.php' style='color: #721c24; text-decoration: none; font-weight: bold;'>← Kembali ke Homepage</a></p>";
    echo "</div>";
}
?>
