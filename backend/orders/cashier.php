<?php
require_once '../auth/auth.php';
require_once '../../db/db.php';
requireLogin();

// Fetch products
$stmt = $pdo->query("SELECT p.*, c.name as category_name FROM products p LEFT JOIN categories c ON p.category_id = c.id ORDER BY p.name ASC");
$products = $stmt->fetchAll();

// Fetch categories
$cat_stmt = $pdo->query("SELECT * FROM categories ORDER BY name ASC");
$categories = $cat_stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kasir (POS) - Brosuli Admin</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script src="https://unpkg.com/html5-qrcode"></script>
    <style>
        body { font-family: 'Outfit', sans-serif; background-color: #FDFCF6; }
        .product-card:hover .add-btn { transform: translateY(0); opacity: 1; }
        .add-btn { transform: translateY(10px); opacity: 0; transition: all 0.3s ease; }
        .scrollbar-hide::-webkit-scrollbar { display: none; }
        .scrollbar-hide { -ms-overflow-style: none; scrollbar-width: none; }
        .focus-lock { border-color: #D97706 !important; box-shadow: 0 0 0 4px rgba(217, 119, 6, 0.1) !important; }
        .lock-indicator { animation: pulse-orange 2s cubic-bezier(0.4, 0, 0.6, 1) infinite; }
        @keyframes pulse-orange { 0%, 100% { opacity: 1; } 50% { opacity: .5; } }
    </style>
</head>
<body class="min-h-screen flex">
    <?php include '../includes/sidebar.php'; ?>

    <!-- Main POS Interface -->
    <main class="flex-1 flex h-screen overflow-hidden">
        <!-- Product Section -->
        <div class="flex-1 flex flex-col p-8 overflow-hidden">
            <header class="flex justify-between items-center mb-8">
                <div>
                    <h2 class="text-3xl font-bold text-gray-800">Kasir Pintar</h2>
                    <p class="text-gray-500">Pilih produk untuk pesanan baru</p>
                </div>
                <div class="flex items-center space-x-4">
                    <div class="relative group">
                        <i id="lockIcon" class="fas fa-lock absolute left-4 top-1/2 -translate-y-1/2 text-amber-600 lock-indicator"></i>
                        <input type="text" id="barcodeScanner" placeholder="Scanner Locked..." autofocus
                            class="pl-12 pr-6 py-3 bg-amber-50 border-2 border-amber-200 rounded-2xl outline-none focus:ring-0 w-48 shadow-sm text-amber-900 font-bold placeholder:text-amber-300 focus-lock transition-all"
                            onblur="setTimeout(() => this.focus(), 10)">
                    </div>
                    <button onclick="toggleCameraScanner()" class="w-12 h-12 bg-amber-100 text-amber-600 rounded-2xl flex items-center justify-center hover:bg-amber-200 transition-all shadow-sm" title="Gunakan Kamera">
                        <i class="fas fa-camera text-xl"></i>
                    </button>
                    <div class="relative">
                        <i class="fas fa-search absolute left-4 top-1/2 -translate-y-1/2 text-gray-400"></i>
                        <input type="text" id="productSearch" placeholder="Cari roti..." 
                            class="pl-12 pr-6 py-3 bg-white border border-gray-100 rounded-2xl outline-none focus:ring-2 focus:ring-amber-500 w-64 shadow-sm">
                    </div>
                </div>
            </header>

            <!-- Categories Filter -->
            <div class="flex space-x-3 mb-8 overflow-x-auto scrollbar-hide pb-2">
                <button onclick="filterCategory('all')" class="cat-filter active px-6 py-2 rounded-full bg-amber-600 text-white font-semibold shadow-md whitespace-nowrap">Semua</button>
                <?php foreach ($categories as $cat): ?>
                    <button onclick="filterCategory('<?php echo htmlspecialchars($cat['name']); ?>')" 
                        class="cat-filter px-6 py-2 rounded-full bg-white text-gray-600 font-semibold border border-gray-100 hover:bg-amber-50 whitespace-nowrap transition-colors">
                        <?php echo htmlspecialchars($cat['name']); ?>
                    </button>
                <?php endforeach; ?>
            </div>

            <!-- Products Grid -->
            <div class="flex-1 overflow-y-auto pr-2 scrollbar-hide">
                <div class="grid grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6" id="productGrid">
                    <?php foreach ($products as $product): ?>
                    <div class="product-card bg-white rounded-3xl p-4 shadow-sm border border-gray-50 flex flex-col group cursor-pointer transition-all hover:shadow-xl hover:-translate-y-1" 
                         data-name="<?php echo strtolower(htmlspecialchars($product['name'])); ?>"
                         data-category="<?php echo htmlspecialchars($product['category_name']); ?>"
                         onclick="addToCart(<?php echo htmlspecialchars(json_encode($product)); ?>)">
                        <div class="relative aspect-square rounded-2xl overflow-hidden mb-4 bg-gray-50">
                            <img src="../../<?php echo $product['image_url'] ?: 'https://via.placeholder.com/200'; ?>" 
                                 class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500">
                            <div class="absolute inset-0 bg-black/20 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center">
                                <span class="bg-white text-[#4A2C2A] px-4 py-2 rounded-full font-bold shadow-lg add-btn">
                                    <i class="fas fa-plus mr-2"></i>Tambah
                                </span>
                            </div>
                        </div>
                        <div class="px-1">
                            <h3 class="font-bold text-gray-800 line-clamp-1"><?php echo htmlspecialchars($product['name']); ?></h3>
                            <p class="text-xs text-gray-400 mb-3"><?php echo htmlspecialchars($product['category_name']); ?></p>
                            <div class="flex justify-between items-center mt-auto">
                                <span class="text-amber-600 font-bold">Rp <?php echo number_format($product['price'], 0, ',', '.'); ?></span>
                                <span class="text-[10px] bg-amber-50 text-amber-700 px-2 py-0.5 rounded-full font-bold uppercase">Tersedia</span>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <!-- Cart Section -->
        <div class="w-[400px] bg-white border-l border-gray-100 flex flex-col shadow-2xl z-10">
            <div class="p-8 border-b border-gray-50">
                <div class="flex items-center justify-between mb-2">
                    <h3 class="text-xl font-bold text-gray-800">Detail Pesanan</h3>
                    <button onclick="clearCart()" class="text-rose-500 text-sm font-semibold hover:underline">Hapus Semua</button>
                </div>
                <p class="text-gray-400 text-sm">Pelanggan: <span class="text-gray-700 font-medium">Walk-in Customer</span></p>
            </div>

            <!-- Cart Items -->
            <div class="flex-1 overflow-y-auto p-8 space-y-6" id="cartItems">
                <!-- Empty State -->
                <div id="emptyCart" class="flex flex-col items-center justify-center h-full text-center py-10">
                    <div class="w-24 h-24 bg-gray-50 rounded-full flex items-center justify-center mb-4">
                        <i class="fas fa-shopping-basket text-gray-200 text-4xl"></i>
                    </div>
                    <p class="text-gray-400">Keranjang masih kosong</p>
                </div>
            </div>

            <!-- Summary -->
            <div class="p-8 bg-gray-50/50 space-y-4">
                <div class="flex justify-between text-gray-500">
                    <span>Subtotal</span>
                    <span id="subtotal">Rp 0</span>
                </div>
                <div class="flex justify-between text-gray-500">
                    <span>Pajak (0%)</span>
                    <span>Rp 0</span>
                </div>
                <div class="pt-4 border-t border-gray-200 flex justify-between items-center">
                    <span class="text-lg font-bold text-gray-800">Total</span>
                    <span id="total" class="text-2xl font-bold text-amber-600">Rp 0</span>
                </div>
                <button onclick="openCheckoutModal()" id="checkoutBtn" disabled 
                    class="w-full bg-[#4A2C2A] text-white py-4 rounded-2xl font-bold text-lg shadow-lg hover:bg-[#3D2422] transition-all disabled:opacity-50 disabled:cursor-not-allowed mt-4">
                    Proses Pembayaran
                </button>
            </div>
        </div>
    </main>

    <!-- Checkout Modal -->
    <div id="checkoutModal" class="fixed inset-0 bg-black/60 hidden items-center justify-center p-4 z-[100] backdrop-blur-sm">
        <div class="bg-white rounded-[2.5rem] max-w-md w-full p-10 shadow-2xl relative">
            <button onclick="closeCheckoutModal()" class="absolute top-6 right-6 text-gray-400 hover:text-gray-600 transition-colors">
                <i class="fas fa-times text-2xl"></i>
            </button>
            
            <div class="text-center mb-8">
                <div class="w-20 h-20 bg-amber-50 rounded-full flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-receipt text-amber-600 text-3xl"></i>
                </div>
                <h3 class="text-2xl font-bold text-gray-800">Konfirmasi Pesanan</h3>
                <p class="text-gray-500 mt-1">Selesaikan transaksi kasir</p>
            </div>

            <form id="checkoutForm" class="space-y-6">
                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-2">Nama Pelanggan (Opsional)</label>
                    <input type="text" id="customerName" placeholder="Contoh: Budi Santoso" 
                        class="w-full px-6 py-4 rounded-2xl border border-gray-100 outline-none focus:ring-2 focus:ring-amber-500 bg-gray-50/50">
                </div>
                
                <div class="bg-amber-50 p-6 rounded-3xl border border-amber-100">
                    <div class="flex justify-between items-center text-amber-900 mb-2">
                        <span class="font-medium">Total yang harus dibayar</span>
                    </div>
                    <div id="modalTotal" class="text-3xl font-bold text-amber-700">Rp 0</div>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <button type="button" onclick="closeCheckoutModal()" class="py-4 rounded-2xl font-bold text-gray-400 hover:bg-gray-50 transition-colors">
                        Batal
                    </button>
                    <button type="submit" class="bg-emerald-600 text-white py-4 rounded-2xl font-bold shadow-lg shadow-emerald-600/20 hover:bg-emerald-700 transition-all">
                        Selesaikan
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Camera Scanner Modal -->
    <div id="cameraModal" class="fixed inset-0 bg-black/80 hidden items-center justify-center p-4 z-[150] backdrop-blur-md">
        <div class="bg-white rounded-[2.5rem] max-w-lg w-full p-8 shadow-2xl relative">
            <button onclick="toggleCameraScanner()" class="absolute top-6 right-6 text-gray-400 hover:text-gray-600 z-10">
                <i class="fas fa-times text-2xl"></i>
            </button>
            <div class="text-center mb-6">
                <h3 class="text-xl font-bold text-gray-800">Scan Barcode / QR Code</h3>
                <p class="text-sm text-gray-500">Arahkan kode ke kotak kamera</p>
            </div>
            <div id="reader" class="rounded-3xl overflow-hidden border-4 border-amber-50 bg-gray-900 aspect-square"></div>
            <div class="mt-6 flex flex-col items-center space-y-4">
                <button id="torchBtn" onclick="toggleTorch()" class="hidden bg-amber-500 text-white px-6 py-2 rounded-full font-bold hover:bg-amber-600 transition-all flex items-center space-x-2">
                    <i class="fas fa-lightbulb"></i>
                    <span>Nyalakan Senter</span>
                </button>
                <p id="scannerStatus" class="text-amber-600 font-medium animate-pulse">Menunggu kamera...</p>
            </div>
        </div>
    </div>

    <!-- Success Modal -->
    <div id="successModal" class="fixed inset-0 bg-black/60 hidden items-center justify-center p-4 z-[110] backdrop-blur-sm">
        <div class="bg-white rounded-[2.5rem] max-w-sm w-full p-10 shadow-2xl text-center">
            <div class="w-24 h-24 bg-emerald-50 rounded-full flex items-center justify-center mx-auto mb-6">
                <i class="fas fa-check text-emerald-500 text-5xl"></i>
            </div>
            <h3 class="text-2xl font-bold text-gray-800 mb-2">Transaksi Berhasil!</h3>
            <p class="text-gray-500 mb-8">Pesanan telah disimpan ke dalam sistem.</p>
            <div class="space-y-3">
                <button id="printReceiptBtn" class="w-full bg-emerald-600 text-white py-4 rounded-2xl font-bold shadow-lg hover:bg-emerald-700 transition-all flex items-center justify-center space-x-2">
                    <i class="fas fa-print"></i>
                    <span>Cetak Struk</span>
                </button>
                <button onclick="window.location.reload()" class="w-full bg-gray-100 text-gray-600 py-4 rounded-2xl font-bold hover:bg-gray-200 transition-all">
                    Kembali ke Kasir
                </button>
            </div>
        </div>
    </div>

    <script>
        const allProducts = <?php echo json_encode($products); ?>;
        let html5QrCode = null;
        let isTorchOn = false;
        let cart = [];

        function addToCart(product) {
            const existingItem = cart.find(item => item.id === product.id);
            if (existingItem) {
                existingItem.qty += 1;
            } else {
                cart.push({
                    id: product.id,
                    name: product.name,
                    price: parseFloat(product.price),
                    image: product.image_url,
                    qty: 1
                });
            }
            renderCart();
            
            // Audio Feedback
            const audio = new Audio('https://assets.mixkit.co/active_storage/sfx/2568/2568-preview.mp3');
            audio.play().catch(e => {});
        }

        function updateQty(id, delta) {
            const item = cart.find(item => item.id === id);
            if (item) {
                item.qty += delta;
                if (item.qty <= 0) {
                    cart = cart.filter(item => item.id !== id);
                }
                renderCart();
            }
        }

        function removeFromCart(id) {
            cart = cart.filter(item => item.id !== id);
            renderCart();
        }

        function clearCart() {
            if (confirm('Kosongkan keranjang?')) {
                cart = [];
                renderCart();
            }
        }

        function renderCart() {
            const cartContainer = document.getElementById('cartItems');
            const emptyState = document.getElementById('emptyCart');
            const checkoutBtn = document.getElementById('checkoutBtn');
            
            if (cart.length === 0) {
                cartContainer.innerHTML = '';
                cartContainer.appendChild(emptyState);
                checkoutBtn.disabled = true;
                updateTotals(0);
                return;
            }

            cartContainer.innerHTML = '';
            let total = 0;

            cart.forEach(item => {
                const itemTotal = item.price * item.qty;
                total += itemTotal;
                
                const itemEl = document.createElement('div');
                itemEl.className = 'flex items-center space-x-4 animate-fadeIn';
                itemEl.innerHTML = `
                    <div class="w-16 h-16 rounded-2xl overflow-hidden bg-gray-50 flex-shrink-0">
                        <img src="../../${item.image || 'https://via.placeholder.com/100'}" class="w-full h-full object-cover">
                    </div>
                    <div class="flex-1 min-w-0">
                        <h4 class="font-bold text-gray-800 truncate">${item.name}</h4>
                        <p class="text-amber-600 font-bold text-sm">Rp ${new Intl.NumberFormat('id-ID').format(item.price)}</p>
                    </div>
                    <div class="flex items-center bg-gray-50 rounded-xl p-1">
                        <button onclick="updateQty(${item.id}, -1)" class="w-8 h-8 flex items-center justify-center text-gray-500 hover:text-amber-600 transition-colors">
                            <i class="fas fa-minus text-xs"></i>
                        </button>
                        <span class="w-8 text-center font-bold text-gray-700">${item.qty}</span>
                        <button onclick="updateQty(${item.id}, 1)" class="w-8 h-8 flex items-center justify-center text-gray-500 hover:text-amber-600 transition-colors">
                            <i class="fas fa-plus text-xs"></i>
                        </button>
                    </div>
                `;
                cartContainer.appendChild(itemEl);
            });

            checkoutBtn.disabled = false;
            updateTotals(total);
        }

        function updateTotals(total) {
            document.getElementById('subtotal').innerText = 'Rp ' + new Intl.NumberFormat('id-ID').format(total);
            document.getElementById('total').innerText = 'Rp ' + new Intl.NumberFormat('id-ID').format(total);
            document.getElementById('modalTotal').innerText = 'Rp ' + new Intl.NumberFormat('id-ID').format(total);
        }

        // Camera Scanner Logic
        async function toggleCameraScanner() {
            const modal = document.getElementById('cameraModal');
            const isHidden = modal.classList.contains('hidden');
            
            if (isHidden) {
                modal.classList.remove('hidden');
                modal.classList.add('flex');
                startScanner();
            } else {
                modal.classList.add('hidden');
                modal.classList.remove('flex');
                stopScanner();
            }
        }

        async function startScanner() {
            html5QrCode = new Html5Qrcode("reader");
            const config = { fps: 15, qrbox: { width: 300, height: 300 } };
            
            try {
                await html5QrCode.start(
                    { facingMode: "environment" }, 
                    config,
                    (decodedText) => {
                        const product = allProducts.find(p => p.barcode === decodedText);
                        if (product) {
                            addToCart(product);
                            document.getElementById('scannerStatus').innerText = "Berhasil: " + product.name;
                            document.getElementById('scannerStatus').classList.replace('text-amber-600', 'text-emerald-600');
                            
                            setTimeout(() => {
                                document.getElementById('scannerStatus').innerText = "Kamera Aktif! Arahkan barcode berikutnya...";
                                document.getElementById('scannerStatus').classList.replace('text-emerald-600', 'text-amber-600');
                            }, 2000);
                        } else {
                            document.getElementById('scannerStatus').innerText = "Barcode tidak terdaftar: " + decodedText;
                            document.getElementById('scannerStatus').classList.replace('text-amber-600', 'text-rose-600');
                            setTimeout(() => {
                                document.getElementById('scannerStatus').innerText = "Kamera Aktif! Arahkan barcode berikutnya...";
                                document.getElementById('scannerStatus').classList.replace('text-rose-600', 'text-amber-600');
                            }, 2000);
                        }
                    }
                );

                document.getElementById('scannerStatus').innerText = "Kamera Aktif! Arahkan kode ke kotak di atas";
                document.getElementById('scannerStatus').classList.remove('animate-pulse');

                // Check if flashlight is supported
                const cameraCapabilities = html5QrCode.getRunningTrackCapabilities();
                if (cameraCapabilities.torch) {
                    document.getElementById('torchBtn').classList.remove('hidden');
                }
            } catch (err) {
                console.error(err);
                document.getElementById('scannerStatus').innerText = "Gagal mengakses kamera.";
            }
        }

        async function toggleTorch() {
            isTorchOn = !isTorchOn;
            try {
                await html5QrCode.applyVideoConstraints({
                    advanced: [{ torch: isTorchOn }]
                });
                const btn = document.getElementById('torchBtn');
                btn.innerHTML = isTorchOn ? '<i class="fas fa-lightbulb"></i> Matikan Senter' : '<i class="fas fa-lightbulb"></i> Nyalakan Senter';
                btn.classList.toggle('bg-amber-600', isTorchOn);
            } catch (err) {
                console.error("Torch error:", err);
            }
        }

        async function stopScanner() {
            if (html5QrCode) {
                await html5QrCode.stop();
                html5QrCode = null;
            }
        }

        // Barcode Scanner Logic
        const barcodeInput = document.getElementById('barcodeScanner');
        
        // Prevent manual typing if desired, or just keep it focused
        barcodeInput.addEventListener('keydown', function(e) {
            // Optional: prevent backspace/delete to keep it clean, but Enter is essential
            if (e.key === 'Enter') {
                e.preventDefault(); // Prevent form submission if any
            }
        });

        barcodeInput.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                const barcode = this.value.trim();
                if (barcode) {
                    const product = allProducts.find(p => p.barcode === barcode);
                    if (product) {
                        addToCart(product);
                        // Visual feedback
                        this.classList.add('bg-emerald-100');
                        document.getElementById('lockIcon').classList.replace('text-amber-600', 'text-emerald-600');
                        setTimeout(() => {
                            this.classList.remove('bg-emerald-100');
                            document.getElementById('lockIcon').classList.replace('text-emerald-600', 'text-amber-600');
                        }, 500);
                    } else {
                        // Error feedback
                        this.classList.add('bg-rose-100');
                        document.getElementById('lockIcon').classList.replace('text-amber-600', 'text-rose-600');
                        setTimeout(() => {
                            this.classList.remove('bg-rose-100');
                            document.getElementById('lockIcon').classList.replace('text-rose-600', 'text-amber-600');
                        }, 500);
                    }
                    this.value = ''; // Clear for next scan
                }
            }
        });

        // Global key listener to catch scanner input even if focus is lost (safety net)
        document.addEventListener('keydown', function(e) {
            // If user starts typing elsewhere, we force focus back unless they are in search
            if (document.activeElement.id !== 'barcodeScanner' && 
                document.activeElement.id !== 'productSearch' && 
                document.activeElement.tagName !== 'INPUT' && 
                document.activeElement.tagName !== 'TEXTAREA') {
                barcodeInput.focus();
            }
        });

        // Polling for Python AI Scanner
        setInterval(() => {
            fetch('check_scans.php')
                .then(res => res.json())
                .then(data => {
                    if (data.success && data.barcode) {
                        const product = allProducts.find(p => p.barcode === data.barcode);
                        if (product) {
                            addToCart(product);
                            // Visual feedback on the lock icon
                            const lockIcon = document.getElementById('lockIcon');
                            lockIcon.classList.replace('text-amber-600', 'text-emerald-600');
                            setTimeout(() => lockIcon.classList.replace('text-emerald-600', 'text-amber-600'), 1000);
                        }
                    }
                })
                .catch(err => console.error("Polling error:", err));
        }, 500);

        // Search & Filter
        document.getElementById('productSearch').addEventListener('input', function(e) {
            const term = e.target.value.toLowerCase();
            const cards = document.querySelectorAll('.product-card');
            cards.forEach(card => {
                const name = card.getAttribute('data-name');
                if (name.includes(term)) {
                    card.style.display = 'flex';
                } else {
                    card.style.display = 'none';
                }
            });
        });

        function filterCategory(cat) {
            const buttons = document.querySelectorAll('.cat-filter');
            buttons.forEach(btn => {
                if (btn.innerText.toLowerCase() === cat.toLowerCase() || (cat === 'all' && btn.innerText === 'Semua')) {
                    btn.classList.add('bg-amber-600', 'text-white');
                    btn.classList.remove('bg-white', 'text-gray-600');
                } else {
                    btn.classList.remove('bg-amber-600', 'text-white');
                    btn.classList.add('bg-white', 'text-gray-600');
                }
            });

            const cards = document.querySelectorAll('.product-card');
            cards.forEach(card => {
                if (cat === 'all' || card.getAttribute('data-category') === cat) {
                    card.style.display = 'flex';
                } else {
                    card.style.display = 'none';
                }
            });
        }

        // Modal Controls
        function openCheckoutModal() {
            document.getElementById('checkoutModal').classList.remove('hidden');
            document.getElementById('checkoutModal').classList.add('flex');
        }

        function closeCheckoutModal() {
            document.getElementById('checkoutModal').classList.add('hidden');
            document.getElementById('checkoutModal').classList.remove('flex');
            // Focus will be handled by the onblur lock
        }

        // Form Submit
        document.getElementById('checkoutForm').addEventListener('submit', function(e) {
            e.preventDefault();
            const customerName = document.getElementById('customerName').value || 'Walk-in Customer';
            const total = cart.reduce((sum, item) => sum + (item.price * item.qty), 0);
            
            const formData = new FormData();
            formData.append('customer_name', customerName);
            formData.append('total_amount', total);
            formData.append('items_json', JSON.stringify(cart));
            formData.append('payment_method', 'Cash');

            fetch('process_cashier.php', {
                method: 'POST',
                body: formData
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    closeCheckoutModal();
                    // Set print action
                    document.getElementById('printReceiptBtn').onclick = () => {
                        window.open('receipt.php?order_id=' + data.order_id, '_blank');
                    };
                    document.getElementById('successModal').classList.remove('hidden');
                    document.getElementById('successModal').classList.add('flex');
                } else {
                    alert('Error: ' + data.message);
                }
            })
            .catch(err => {
                console.error(err);
                alert('Terjadi kesalahan saat memproses pesanan.');
            });
        });
    </script>
</body>
</html>
