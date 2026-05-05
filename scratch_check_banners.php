<?php
require_once 'db/db.php';
$stmt = $pdo->query("SELECT * FROM banners");
while ($row = $stmt->fetch()) {
    echo "ID: " . $row['id'] . " | Title: " . $row['title'] . " | Link: " . $row['link_url'] . "\n";
}
?>
