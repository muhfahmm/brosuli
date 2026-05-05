<?php
require_once '../auth/auth.php';
require_once '../../db/db.php';
requireLogin();

// Auto-create table if not exists (Foolproof)
$pdo->exec("CREATE TABLE IF NOT EXISTS orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id VARCHAR(50) UNIQUE NOT NULL,
    customer_name VARCHAR(100),
    customer_address TEXT,
    total_amount DECIMAL(15, 2),
    payment_status VARCHAR(20) DEFAULT 'pending',
    payment_method VARCHAR(20) DEFAULT 'Midtrans',
    items_json TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)");

// Fetch orders based on role
$role = $_SESSION['admin_role'] ?? 'superadmin';
$branch_id = $_SESSION['admin_branch_id'] ?? null;

if ($role == 'admin_cabang') {
    $stmt = $pdo->prepare("SELECT * FROM orders WHERE branch_id = ? ORDER BY created_at DESC");
    $stmt->execute([$branch_id]);
} else {
    $stmt = $pdo->query("SELECT * FROM orders ORDER BY created_at DESC");
}
$orders = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manajemen Pesanan - Admin Brosuli</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body { font-family: 'Outfit', sans-serif; background-color: #f8fafc; }
    </style>
</head>
<body class="bg-[#FDFCF6] min-h-screen flex">
    <?php include '../includes/sidebar.php'; ?>

    <!-- Main Content -->
    <main class="flex-1 flex flex-col">
        <div class="p-8">
        <header class="flex justify-between items-center mb-10">
            <div>
                <h1 class="text-3xl font-bold text-slate-800">Manajemen Pesanan</h1>
                <p class="text-slate-500 mt-1">Daftar transaksi pelanggan melalui Midtrans</p>
            </div>
        </header>

        <!-- Orders Table -->
        <div class="bg-white rounded-[2rem] shadow-sm border border-slate-100 overflow-hidden">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50/50 text-slate-400 uppercase text-xs font-bold tracking-widest">
                        <th class="px-8 py-6">ID Pesanan / Waktu</th>
                        <th class="px-8 py-6">Pelanggan</th>
                        <th class="px-8 py-6">Produk</th>
                        <th class="px-8 py-6">Total</th>
                        <th class="px-8 py-6">Status</th>
                        <th class="px-8 py-6">Metode</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50">
                    <?php foreach ($orders as $order): ?>
                    <tr class="hover:bg-slate-50/50 transition-colors">
                        <td class="px-8 py-6">
                            <div class="font-bold text-slate-700"><?php echo htmlspecialchars($order['order_id']); ?></div>
                            <div class="text-xs text-slate-400 mt-1"><?php echo date('d M Y, H:i', strtotime($order['created_at'])); ?></div>
                        </td>
                        <td class="px-8 py-6">
                            <div class="font-bold text-slate-700"><?php echo htmlspecialchars($order['customer_name']); ?></div>
                            <div class="text-xs text-slate-400 mt-1 max-w-[200px] truncate"><?php echo htmlspecialchars($order['customer_address']); ?></div>
                        </td>
                        <td class="px-8 py-6">
                            <div class="text-sm text-slate-600">
                                <?php 
                                $items = json_decode($order['items_json'], true);
                                foreach ($items as $item) {
                                    echo "• " . htmlspecialchars($item['name']) . " (x" . $item['qty'] . ")<br>";
                                }
                                ?>
                            </div>
                        </td>
                        <td class="px-8 py-6">
                            <div class="font-bold text-amber-600">Rp <?php echo number_format($order['total_amount'], 0, ',', '.'); ?></div>
                        </td>
                        <td class="px-8 py-6">
                            <?php 
                            $status_color = 'bg-slate-100 text-slate-500';
                            if ($order['payment_status'] == 'settlement') $status_color = 'bg-emerald-100 text-emerald-600';
                            if ($order['payment_status'] == 'pending') $status_color = 'bg-amber-100 text-amber-600';
                            if ($order['payment_status'] == 'failure') $status_color = 'bg-rose-100 text-rose-600';
                            ?>
                            <span class="px-4 py-1.5 rounded-full text-xs font-bold uppercase tracking-wider <?php echo $status_color; ?>">
                                <?php echo htmlspecialchars($order['payment_status']); ?>
                            </span>
                        </td>
                        <td class="px-8 py-6">
                            <span class="text-sm font-semibold text-slate-500">
                                <i class="fas <?php echo $order['payment_method'] == 'Cash' ? 'fa-money-bill-wave text-emerald-500' : 'fa-credit-card text-blue-500'; ?> mr-2"></i>
                                <?php echo htmlspecialchars($order['payment_method']); ?>
                            </span>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </main>
</body>
</html>
