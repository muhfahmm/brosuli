<?php
require_once 'auth.php';
require_once '../db/db.php';
requireLogin();

$order_id = $_GET['order_id'] ?? '';

if (!$order_id) {
    die("Order ID not provided.");
}

$stmt = $pdo->prepare("SELECT * FROM orders WHERE order_id = ?");
$stmt->execute([$order_id]);
$order = $stmt->fetch();

if (!$order) {
    die("Order not found.");
}

$items = json_decode($order['items_json'], true);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Struk Pembayaran - <?php echo $order['order_id']; ?></title>
    <style>
        body {
            font-family: 'Courier New', Courier, monospace;
            width: 80mm;
            margin: 0 auto;
            padding: 10px;
            font-size: 12px;
            line-height: 1.4;
            color: #000;
        }
        .header {
            text-align: center;
            margin-bottom: 15px;
        }
        .logo {
            font-size: 20px;
            font-weight: bold;
            margin-bottom: 5px;
        }
        .tagline {
            font-size: 10px;
            font-style: italic;
        }
        .divider {
            border-bottom: 1px dashed #000;
            margin: 10px 0;
        }
        .info {
            margin-bottom: 10px;
        }
        .items table {
            width: 100%;
            border-collapse: collapse;
        }
        .items td {
            vertical-align: top;
            padding: 2px 0;
        }
        .total-section {
            margin-top: 10px;
            padding-top: 5px;
        }
        .total-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 2px;
        }
        .grand-total {
            font-weight: bold;
            font-size: 14px;
            margin-top: 5px;
            border-top: 1px solid #000;
            padding-top: 5px;
        }
        .footer {
            text-align: center;
            margin-top: 25px;
            font-size: 10px;
        }
        @media print {
            .no-print {
                display: none;
            }
            body {
                width: 100%;
                margin: 0;
                padding: 0;
            }
        }
    </style>
</head>
<body onload="window.print()">
    <div class="header">
        <div class="logo">BROSULI BAKERY</div>
        <div class="tagline">"Pesan hari ini, Panggang hari ini"</div>
        <div class="divider"></div>
    </div>

    <div class="info">
        <div>Tgl: <?php echo date('d/m/Y H:i', strtotime($order['created_at'])); ?></div>
        <div>No : <?php echo $order['order_id']; ?></div>
        <div>Ksr: <?php echo $_SESSION['admin_username']; ?></div>
        <div>Plg: <?php echo htmlspecialchars($order['customer_name']); ?></div>
    </div>

    <div class="divider"></div>

    <div class="items">
        <table>
            <?php foreach ($items as $item): ?>
            <tr>
                <td style="width: 70%;">
                    <?php echo htmlspecialchars($item['name']); ?><br>
                    <small><?php echo $item['qty']; ?> x <?php echo number_format($item['price'], 0, ',', '.'); ?></small>
                </td>
                <td style="width: 30%; text-align: right;">
                    Rp <?php echo number_format($item['price'] * $item['qty'], 0, ',', '.'); ?>
                </td>
            </tr>
            <?php endforeach; ?>
        </table>
    </div>

    <div class="divider"></div>

    <div class="total-section">
        <div class="total-row">
            <span>Subtotal</span>
            <span>Rp <?php echo number_format($order['total_amount'], 0, ',', '.'); ?></span>
        </div>
        <div class="total-row">
            <span>Diskon</span>
            <span>Rp 0</span>
        </div>
        <div class="total-row grand-total">
            <span>TOTAL</span>
            <span>Rp <?php echo number_format($order['total_amount'], 0, ',', '.'); ?></span>
        </div>
        <div class="total-row" style="margin-top: 10px;">
            <span>Metode:</span>
            <span><?php echo $order['payment_method']; ?></span>
        </div>
    </div>

    <div class="footer">
        <div>-- Terima Kasih --</div>
        <div>Barang yang sudah dibeli</div>
        <div>tidak dapat ditukar/dikembalikan</div>
        <div style="margin-top: 5px;">Follow Instagram: @brosuli.bakery</div>
    </div>

    <div class="no-print" style="margin-top: 30px; text-align: center;">
        <button onclick="window.close()" style="padding: 10px 20px; cursor: pointer; background: #4A2C2A; color: white; border: none; border-radius: 5px;">Tutup</button>
        <p style="font-size: 10px; color: #666; margin-top: 10px;">(Printer thermal akan otomatis mencetak)</p>
    </div>
</body>
</html>
