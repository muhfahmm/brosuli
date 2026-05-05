// Cart Management System
let cart = JSON.parse(localStorage.getItem('brosuli_cart')) || [];
let removedItemIds = JSON.parse(localStorage.getItem('brosuli_removed_ids')) || [];
let isPaid = false; // Payment status flag

function saveCart() {
    localStorage.setItem('brosuli_cart', JSON.stringify(cart));
    localStorage.setItem('brosuli_removed_ids', JSON.stringify(removedItemIds));
    updateCartUI();
}

function addToCart(product) {
    if (removedItemIds.includes(product.id)) {
        showToast(`❌ ${product.name} telah dihapus dan tidak dapat ditambahkan lagi.`);
        return;
    }
    
    const existingItem = cart.find(item => item.id === product.id);
    if (existingItem) {
        existingItem.quantity += 1;
    } else {
        cart.push({ ...product, quantity: 1 });
    }
    isPaid = false; // Reset payment status if cart changes
    saveCart();
    showToast(`${product.name} ditambahkan ke keranjang!`);
}

function removeFromCart(id) {
    const item = cart.find(item => item.id === id);
    if (item) {
        item.isDeleted = true;
        if (!removedItemIds.includes(id)) {
            removedItemIds.push(id);
        }
        showToast(`Produk "${item.name}" telah dihapus.`);
    }
    isPaid = false; // Reset payment status if cart changes
    saveCart();
}

function updateQuantity(id, delta) {
    const item = cart.find(item => item.id === id);
    if (item) {
        item.quantity += delta;
        if (item.quantity <= 0) {
            removeFromCart(id);
        } else {
            isPaid = false; // Reset payment status if cart changes
            saveCart();
        }
    }
}

function updateCartUI() {
    const cartCount = document.querySelectorAll('.cart-count');
    const totalItems = cart.reduce((sum, item) => sum + item.quantity, 0);
    cartCount.forEach(el => el.textContent = totalItems);

    const cartItemsList = document.getElementById('cart-items-list');
    const cartTotalEl = document.getElementById('cart-total-price');
    const waButton = document.getElementById('wa-checkout-btn');
    
    if (cartItemsList) {
        if (cart.length === 0) {
            cartItemsList.innerHTML = '<div class="text-center py-10 text-gray-400">Keranjang masih kosong.</div>';
            cartTotalEl.textContent = 'Rp 0';
        } else {
            cartItemsList.innerHTML = cart.map(item => {
                const subtotal = item.price * item.quantity;
                if (!item.isDeleted) total += subtotal;

                return `
                    <div class="flex items-center space-x-4 p-4 ${item.isDeleted ? 'bg-gray-100 opacity-60' : 'bg-cream/30 border-amber-50'} rounded-2xl border">
                        <img src="${item.image}" class="w-16 h-16 object-cover rounded-xl shadow-sm ${item.isDeleted ? 'grayscale' : ''}">
                        <div class="flex-1">
                            <h4 class="font-bold text-sm ${item.isDeleted ? 'text-gray-400' : 'text-primary'}">${item.name}</h4>
                            <p class="text-xs ${item.isDeleted ? 'text-gray-400' : 'text-amber-600 font-bold'}">
                                ${item.isDeleted ? 'Produk telah dihapus' : `Rp ${Number(item.price).toLocaleString('id-ID')}`}
                            </p>
                            ${!item.isDeleted ? `
                                <div class="flex items-center space-x-3 mt-2">
                                    <button onclick="updateQuantity(${item.id}, -1)" class="w-6 h-6 rounded-full border border-primary/20 flex items-center justify-center hover:bg-primary hover:text-white transition-all">
                                        <i class="fas fa-minus text-[10px]"></i>
                                    </button>
                                    <span class="text-sm font-bold">${item.quantity}</span>
                                    <button onclick="updateQuantity(${item.id}, 1)" class="w-6 h-6 rounded-full border border-primary/20 flex items-center justify-center hover:bg-primary hover:text-white transition-all">
                                        <i class="fas fa-plus text-[10px]"></i>
                                    </button>
                                </div>
                            ` : ''}
                        </div>
                        ${!item.isDeleted ? `
                            <button onclick="removeFromCart(${item.id})" class="text-gray-300 hover:text-red-500 transition-colors">
                                <i class="fas fa-trash-alt"></i>
                            </button>
                        ` : `
                            <i class="fas fa-info-circle text-gray-300"></i>
                        `}
                    </div>
                `;
            }).join('');
            cartTotalEl.textContent = `Rp ${total.toLocaleString('id-ID')}`;
        }
    }

    // Smart Button Logic
    if (waButton) {
        if (isPaid) {
            waButton.classList.remove('opacity-50', 'grayscale', 'cursor-not-allowed');
            waButton.classList.add('animate-pulse');
            waButton.innerHTML = '<i class="fab fa-whatsapp text-2xl"></i><span>Konfirmasi Pesanan Lunas</span>';
        } else {
            waButton.classList.add('opacity-50', 'grayscale', 'cursor-not-allowed');
            waButton.classList.remove('animate-pulse');
            waButton.innerHTML = '<i class="fas fa-lock text-sm"></i><span>Bayar Dulu via Midtrans</span>';
        }
    }
}

function showToast(message) {
    const toast = document.createElement('div');
    toast.className = 'fixed bottom-24 left-1/2 -translate-x-1/2 bg-primary text-white px-6 py-3 rounded-full shadow-2xl z-[200] animate-bounce text-sm font-bold';
    toast.textContent = message;
    document.body.appendChild(toast);
    setTimeout(() => toast.remove(), 3000);
}

function payWithMidtrans() {
    const activeItems = cart.filter(item => !item.isDeleted);
    if (activeItems.length === 0) {
        showToast('Keranjang Anda masih kosong!');
        return;
    }

    const customerName = document.getElementById('customer-name')?.value;
    const customerPhone = document.getElementById('customer-phone')?.value;

    if (!customerName || !customerPhone) {
        showToast('Mohon isi Nama dan Nomor WhatsApp Anda!');
        return;
    }

    const orderData = {
        items: activeItems,
        customer_name: customerName,
        customer_phone: customerPhone
    };

    showToast('Menyiapkan pembayaran...');

    fetch('../backend/orders/create_payment.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(orderData)
    })
    .then(response => response.json())
    .then(data => {
        if (data.token) {
            snap.pay(data.token, {
                onSuccess: function(result) {
                    isPaid = true;
                    updateCartUI();
                    showToast('Pembayaran Berhasil! Silakan konfirmasi ke WA.');
                },
                onPending: function(result) {
                    showToast('Menunggu pembayaran...');
                },
                onError: function(result) {
                    showToast('Pembayaran gagal!');
                },
                onClose: function() {
                    showToast('Pembayaran dibatalkan.');
                }
            });
        } else {
            showToast('Gagal membuat transaksi: ' + (data.error || 'Unknown error'));
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showToast('Terjadi kesalahan sistem.');
    });
}

function checkoutWhatsApp() {
    if (!isPaid) {
        showToast('🔒 Silakan bayar terlebih dahulu via Midtrans!');
        return;
    }

    const customerName = document.getElementById('customer-name')?.value;
    const customerPhone = document.getElementById('customer-phone')?.value;

    const phoneNumber = '62895327349264';
    let message = `*Halo Brosuli Bakery!* 🍞✨\n\n`;
    message += `*STATUS: PEMBAYARAN LUNAS (MIDTRANS)* ✅\n`;
    message += `----------------------------\n`;
    message += `👤 *Nama:* ${customerName}\n`;
    message += `📞 *No. WA:* ${customerPhone}\n`;
    message += `----------------------------\n\n`;
    
    let total = 0;
    cart.forEach((item, index) => {
        const subtotal = item.price * item.quantity;
        total += subtotal;
        message += `• *${item.name}* (${item.quantity}x)\n`;
    });

    message += `\n💰 *Total:* Rp ${total.toLocaleString('id-ID')}\n`;
    message += `----------------------------\n\n`;
    message += `Saya sudah membayar via Midtrans. Mohon segera diproses ya, terima kasih! 🙏`;

    const encodedMessage = encodeURIComponent(message);
    window.open(`https://wa.me/${phoneNumber}?text=${encodedMessage}`, '_blank');
}

// Initial UI Update
document.addEventListener('DOMContentLoaded', updateCartUI);

