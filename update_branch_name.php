<?php
require_once 'db/db.php';
$pdo->exec("UPDATE branches SET name = 'Brosuli Boyolali (Pusat)' WHERE id = 1");
echo "Updated branch name to 'Brosuli Boyolali (Pusat)'";
?>
