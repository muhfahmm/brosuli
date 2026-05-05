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
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: '#4A2C2A',
                        secondary: '#D97706',
                    }
                }
            }
        }
    </script>
    <style>
        body { font-family: 'Inter', sans-serif; }
        @media print {
            .no-print { display: none !important; }
            .label-page { padding: 0 !important; margin: 0 !important; gap: 0 !important; }
            body { background: white; margin: 0; }
            @page { margin: 0; }
        }
        .label-card {
            width: 80mm;
            height: 50mm;
            border: 1px solid #e5e7eb;
            padding: 5mm;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            align-items: center;
            text-align: center;
            page-break-inside: avoid;
            background: white;
            border-radius: 4px;
        }
        .barcode-img {
            width: 100%;
            height: 25mm;
            object-fit: contain;
        }
    </style>
</head>
<body class="bg-gray-100 min-h-screen">
    <div class="no-print p-8 bg-white shadow-sm flex flex-col md:flex-row justify-between items-center gap-6 mb-8">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">Antrean Cetak Label</h1>
            <div class="mt-4 space-y-4">
                <?php foreach ($queue_items as $item): ?>
                <div class="flex items-center gap-4 bg-gray-50 p-4 rounded-xl border border-gray-100">
                    <div class="font-bold text-gray-700 w-48 truncate"><?php echo htmlspecialchars($item['name']); ?></div>
                    <div class="flex items-center gap-3">
                        <button onclick="updateQty(<?php echo $item['id']; ?>, -1)" class="w-10 h-10 rounded-full bg-white border-2 border-gray-200 flex items-center justify-center hover:bg-gray-100 hover:border-gray-300 transition-all font-bold text-xl text-gray-700">-</button>
                        <span class="text-xl font-bold w-10 text-center"><?php echo $item['quantity']; ?></span>
                        <button onclick="updateQty(<?php echo $item['id']; ?>, 1)" class="w-10 h-10 rounded-full bg-primary text-white flex items-center justify-center hover:bg-secondary transition-all font-bold text-xl shadow-md">+</button>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        <div class="flex flex-wrap gap-4 justify-center">
            <button onclick="window.print()" class="bg-blue-600 text-white px-6 py-4 rounded-xl font-bold shadow-lg hover:bg-blue-700 flex items-center gap-2">
                <i class="fas fa-print"></i>Cetak Sekarang
            </button>
            <button onclick="confirmClearQueue()" class="bg-rose-500 text-white px-6 py-4 rounded-xl font-bold shadow-lg hover:bg-rose-600">
                Kosongkan Antrean
            </button>
            <a href="index.php" class="bg-gray-200 text-gray-700 px-6 py-4 rounded-xl font-bold hover:bg-gray-300 transition-all flex items-center">Kembali</a>
        </div>
    </div>

    <script>
        function updateQty(id, delta) {
            const formData = new FormData();
            formData.append('id', id);
            formData.append('delta', delta);
            
            fetch('update_label_quantity.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    location.reload(); // Simple real-time update
                }
            });
        }
    </script>

    <div class="label-page p-8 space-y-12">
        <?php if (empty($queue_items)): ?>
            <p class="no-print text-gray-500 py-20 text-center w-full">Antrean kosong. Tambahkan produk dari daftar produk.</p>
        <?php endif; ?>

        <?php foreach ($queue_items as $item): ?>
            <div class="product-group space-y-4">
                <div class="no-print flex items-center gap-4 pb-2 border-b-2 border-gray-200">
                    <span class="bg-primary text-white px-4 py-1 rounded-lg text-sm font-bold">Produk:</span>
                    <h2 class="text-xl font-bold text-gray-800"><?php echo htmlspecialchars($item['name']); ?></h2>
                    <span class="text-gray-400">(<?php echo $item['quantity']; ?> Label)</span>
                </div>
                
                <div class="flex flex-wrap gap-4">
                    <?php for ($i = 0; $i < $item['quantity']; $i++): ?>
                        <div class="label-card shadow-sm border">
                            <div class="text-[10px] font-bold text-amber-600 uppercase tracking-widest border-b border-amber-100 pb-1 w-full mb-1">Brosuli Bakery</div>
                            <div class="text-[12px] font-bold text-gray-800 leading-tight mb-2">
                                <?php echo htmlspecialchars($item['name']); ?>
                            </div>
                            
                            <?php if ($item['barcode']): ?>
                                <div class="flex-1 flex flex-col justify-center w-full overflow-hidden">
                                    <img src="https://barcode.tec-it.com/barcode.ashx?data=<?php echo urlencode($item['barcode']); ?>&code=Code128&hide-text=on" 
                                         class="barcode-img">
                                    <div class="text-[8px] text-gray-400 mt-1"><?php echo htmlspecialchars($item['barcode']); ?></div>
                                </div>
                            <?php else: ?>
                                <div class="h-12 flex items-center justify-center text-[10px] text-gray-300 italic border border-gray-100 w-full">Tanpa Barcode</div>
                            <?php endif; ?>

                            <div class="text-xl font-bold text-[#4A2C2A] mt-1">
                                Rp <?php echo number_format($item['price'], 0, ',', '.'); ?>
                            </div>
                        </div>
                    <?php endfor; ?>
                </div>
            </div>
            <!-- Page break after each group for cleaner printing if large -->
            <div class="print-break h-1 no-print"></div>
        <?php endforeach; ?>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/js/all.min.js"></script>
    <?php include '../includes/modals/confirm_modal.php'; ?>
    
    <script>
        function confirmClearQueue() {
            showConfirm(
                'Kosongkan Antrean?', 
                'Semua produk dalam antrean cetak akan dihapus. Lanjutkan?', 
                function() {
                    window.location.href = '?clear=1';
                }
            );
        }
    </script>
</body>
</html>
