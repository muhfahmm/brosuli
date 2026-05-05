<?php
require_once 'db/db.php';
$stmt = $pdo->query("SELECT id, name, COUNT(*) as count FROM branches GROUP BY name HAVING count > 1");
$duplicates = $stmt->fetchAll();

if (!empty($duplicates)) {
    echo "Found duplicates:\n";
    foreach ($duplicates as $dup) {
        echo "Branch: " . $dup['name'] . " (" . $dup['count'] . " times)\n";
        // Delete all but the lowest ID for each name
        $pdo->prepare("DELETE FROM branches WHERE name = ? AND id > (SELECT min_id FROM (SELECT MIN(id) as min_id FROM branches WHERE name = ?) as tmp)")
            ->execute([$dup['name'], $dup['name']]);
        echo "Cleaned up " . ($dup['count'] - 1) . " entries for " . $dup['name'] . "\n";
    }
} else {
    echo "No duplicates found by name.\n";
}

$stmt = $pdo->query("SELECT * FROM branches");
print_r($stmt->fetchAll());
?>
