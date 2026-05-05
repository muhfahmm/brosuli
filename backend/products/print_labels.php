<?php
require_once '../auth/auth.php';
require_once '../../db/db.php';
requireLogin();

// Fetch all items in the queue
$stmt = $pdo->query("SELECT q.*, p.name, p.price, p.barcode FROM label_print_queue q JOIN products p ON q.product_id = p.id");
$queue_items = $stmt->fetchAll();

// Clear queue after fetching if user confirms? No, let's keep it until they click "Clear"
if (isset($_GET['clear'])) {
    $pdo->exec("DELETE FROM label_print_queue");
    header('Location: print_labels.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Cetak Label Harga - Brosuli</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; }
        @media print {
            .no-print { display: none; }
            .label-page { padding: 0; margin: 0; }
        }
        .label-card {
            width: 50mm;
            height: 30mm;
            border: 1px dashed #ccc;
            padding: 2mm;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            align-items: center;
            text-align: center;
            page-break-inside: avoid;
            background: white;
        }
    </style>
</head>
<body class="bg-gray-100 min-h-screen">
    <div class="no-print p-8 bg-white shadow-sm flex justify-between items-center mb-8">
        <h1 class="text-2xl font-bold text-gray-800">Antrean Cetak Label</h1>
        <div class="space-x-4">
            <button onclick="window.print()" class="bg-blue-600 text-white px-6 py-2 rounded-lg font-bold shadow-lg hover:bg-blue-700">
                <i class="fas fa-print mr-2"></i>Cetak Sekarang
            </button>
            <a href="?clear=1" onclick="return confirm('Kosongkan antrean?')" class="bg-rose-500 text-white px-6 py-2 rounded-lg font-bold shadow-lg hover:bg-rose-600">
                Kosongkan Antrean
            </a>
            <a href="index.php" class="text-gray-500 hover:underline">Kembali</a>
        </div>
    </div>

    <div class="label-page flex flex-wrap justify-center gap-4 p-4">
        <?php if (empty($queue_items)): ?>
            <p class="no-print text-gray-500 py-20 text-center w-full">Antrean kosong. Tambahkan produk dari daftar produk.</p>
        <?php endif; ?>

        <?php foreach ($queue_items as $item): ?>
            <?php for ($i = 0; $i < $item['quantity']; $i++): ?>
                <div class="label-card shadow-sm rounded-lg">
                    <div class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">Brosuli Bakery</div>
                    <div class="text-xs font-bold text-gray-800 leading-tight my-1 truncate w-full">
                        <?php echo htmlspecialchars($item['name']); ?>
                    </div>
                    
                    <?php if ($item['barcode']): ?>
                        <img src="https://barcode.tec-it.com/barcode.ashx?data=<?php echo urlencode($item['barcode']); ?>&code=Code128&translate-esc=on" 
                             class="h-8 max-w-full">
                        <div class="text-[8px] font-mono text-gray-500"><?php echo htmlspecialchars($item['barcode']); ?></div>
                    <?php else: ?>
                        <div class="h-8 flex items-center justify-center text-[8px] text-gray-300 italic border border-gray-100 w-full">Tanpa Barcode</div>
                    <?php endif; ?>

                    <div class="text-sm font-bold text-amber-600 mt-1">
                        Rp <?php echo number_format($item['price'], 0, ',', '.'); ?>
                    </div>
                </div>
            <?php endfor; ?>
        <?php endforeach; ?>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/js/all.min.js"></script>
</body>
</html>
