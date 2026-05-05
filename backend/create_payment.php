<?php
header('Content-Type: application/json');
require_once '../config.php';
require_once '../db/db.php';

// Get JSON data from frontend
$input = file_get_contents('php://input');
$data = json_decode($input, true);

if (!$data || empty($data['items'])) {
    echo json_encode(['error' => 'Invalid order data']);
    exit;
}

$order_id = 'BROSULI-' . time() . '-' . rand(100, 999);
$total_amount = 0;
$item_details = [];
$items_for_db = [];

foreach ($data['items'] as $item) {
    $price = (int)$item['price'];
    $quantity = (int)$item['quantity'];
    $total_amount += $price * $quantity;
    $item_details[] = [
        'id' => $item['id'],
        'price' => $price,
        'quantity' => $quantity,
        'name' => substr($item['name'], 0, 50)
    ];
    $items_for_db[] = [
        'name' => $item['name'],
        'qty' => $quantity,
        'price' => $price
    ];
}

// Save to Database first as Pending
try {
    $customer_name = $data['customer_name'] ?? 'Pelanggan';
    $customer_phone = $data['customer_phone'] ?? '';

    $stmt = $pdo->prepare("INSERT INTO orders (order_id, customer_name, customer_address, total_amount, payment_status, items_json) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->execute([
        $order_id,
        $customer_name,
        $customer_phone, // We store phone in the address column for now to avoid DB migration
        $total_amount,
        'pending',
        json_encode($items_for_db)
    ]);
} catch (PDOException $e) {
    // If DB fails, we still want to try creating Midtrans transaction
}

// Midtrans API Request Payload
$payload = [
    'transaction_details' => [
        'order_id' => $order_id,
        'gross_amount' => $total_amount,
    ],
    'item_details' => $item_details,
    'customer_details' => [
        'first_name' => $customer_name,
        'phone' => $customer_phone,
    ],
    'callbacks' => [
        'finish' => BASE_URL . 'frontend/success.php'
    ]
];

$auth = base64_encode(MIDTRANS_SERVER_KEY . ':');
$url = MIDTRANS_IS_PRODUCTION 
    ? 'https://app.midtrans.com/snap/v1/transactions' 
    : 'https://app.sandbox.midtrans.com/snap/v1/transactions';

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
// Disable SSL Verification for Localhost/XAMPP issues
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'Accept: application/json',
    'Authorization: Basic ' . $auth
]);

$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curl_error = curl_error($ch);
curl_close($ch);

if ($http_code == 201 || $http_code == 200) {
    echo $response;
} else {
    http_response_code(500);
    echo json_encode([
        'error' => 'Failed to create transaction',
        'http_code' => $http_code,
        'curl_error' => $curl_error,
        'midtrans_response' => json_decode($response)
    ]);
}
?>
