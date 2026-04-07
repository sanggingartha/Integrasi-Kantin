<?php
// proses.php (Sebagai Order Service)
require_once 'config.php';
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

$item = $_POST['item'];
$qty = $_POST['qty'];

// Challenge 1: Validasi Input 
if ($qty <= 0) {
    $status = 'gagal';
    $msg = "Jumlah tidak valid! Qty harus lebih dari 0.";

    // kirim ke notification service
    kirimNotifikasi($msg, $status);
    exit;
}

$res = $conn->query("SELECT * FROM stok_gudang WHERE nama_item = '$item'");
$data = $res->fetch_assoc();

if ($data && $data['stok'] >= $qty) {
    $stok_baru = $data['stok'] - $qty;
    $conn->query("UPDATE stok_gudang SET stok = $stok_baru WHERE nama_item = '$item'");
    $status = 'sukses';
    $msg = "Pesanan $item ($qty) sukses. Sisa: $stok_baru";
} else {
    $status = 'gagal';
    $msg = "Stok $item tidak mencukupi!";
}

kirimNotifikasi($msg, $status);

function kirimNotifikasi($msg, $status)
{
    $service_url = 'http://localhost/integrasi%20sistem/notification_service.php';

    $payload = json_encode([
        'message' => $msg,
        'status'  => $status
    ]);

    $ch = curl_init($service_url);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type:application/json'));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    $result = curl_exec($ch);
    curl_close($ch);

    echo "Respon Microservice: " . $msg;
}
