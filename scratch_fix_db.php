<?php
require_once 'db/db.php';
$old_num = '62881024367785';
$new_num = '62895327349264';

// Search and replace in banners table link_url
$stmt = $pdo->prepare("UPDATE banners SET link_url = REPLACE(link_url, ?, ?) WHERE link_url LIKE ?");
$stmt->execute([$old_num, $new_num, "%$old_num%"]);
$affected = $stmt->rowCount();

echo "Updated $affected rows in banners table.\n";
?>
