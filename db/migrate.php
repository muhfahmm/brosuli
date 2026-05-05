<?php
require_once 'db.php';

try {
    $sql = "
    -- Add Branches table
    CREATE TABLE IF NOT EXISTS branches (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(100) NOT NULL,
        address TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    );

    -- Modify Admin table
    -- Check if columns already exist to avoid errors
    $checkAdmin = \$pdo->query(\"SHOW COLUMNS FROM admin LIKE 'role'\");
    if (\$checkAdmin->rowCount() == 0) {
        \$pdo->exec(\"ALTER TABLE admin ADD COLUMN role ENUM('superadmin', 'admin_cabang') DEFAULT 'superadmin'\");
        \$pdo->exec(\"ALTER TABLE admin ADD COLUMN branch_id INT NULL\");
        \$pdo->exec(\"ALTER TABLE admin ADD FOREIGN KEY (branch_id) REFERENCES branches(id) ON DELETE SET NULL\");
    }

    -- Modify Orders table
    \$checkOrders = \$pdo->query(\"SHOW COLUMNS FROM orders LIKE 'branch_id'\");
    if (\$checkOrders->rowCount() == 0) {
        \$pdo->exec(\"ALTER TABLE orders ADD COLUMN branch_id INT NULL\");
        \$pdo->exec(\"ALTER TABLE orders ADD FOREIGN KEY (branch_id) REFERENCES branches(id) ON DELETE SET NULL\");
    }

    -- Insert some initial branches if empty
    \$stmt = \$pdo->query(\"SELECT COUNT(*) FROM branches\");
    if (\$stmt->fetchColumn() == 0) {
        \$pdo->exec(\"INSERT INTO branches (name, address) VALUES 
        ('Brosuli Boyolali (Utama)', 'Jl. Pandanaran No.275, Sidoharjo, Banaran, Kec. Boyolali'),
        ('Brosuli Mojosongo', 'Ruko Techno Park, Jl. Merdeka Timur, Mojosongo'),
        ('Brosuli Kartasura', 'Jl. Brigjen Katamso, Ngemplak, Kartasura'),
        ('Brosuli Baki', 'Jl. Ovensari Raya No.21, Kadilangu, Baki'),
        ('Brosuli Mojolaban', 'Jl. Lettu Rm.Hartono No.39, Gadingan, Mojolaban'),
        ('Brosuli Colomadu', 'Jl. Adi Sumarmo, Krobyongan, Gawanan'),
        ('Brosuli Pedan', 'Jl. Raya Ps. Pedan, Kedungan, Pedan'),
        ('Brosuli Jatinom', 'Jl. Klaten-Boyolali No.KM. 8, Bonyokan, Jatinom')\");
    }

    echo \"Migration successful!\";
    ";
    
    // I will write a simple php file and run it
    file_put_contents('run_migration.php', "<?php require_once 'db.php'; try { " . $sql . " } catch(Exception \$e) { echo \$e->getMessage(); }");
    
} catch (Exception $e) {
    echo $e->getMessage();
}
