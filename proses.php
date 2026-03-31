<?php

require_once 'config.php';
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
$item = $_POST['item'];
$qty = $_POST['qty'];

// 1. Validasi Stok di Database
$res = $conn->query("SELECT * FROM stok_gudang WHERE nama_item = '$item'");
$data = $res->fetch_assoc();
if ($data && $data['stok'] >= $qty) {
    $stok_baru = $data['stok'] - $qty;
    $conn->query("UPDATE stok_gudang SET stok = $stok_baru WHERE nama_item =
    '$item'");
    $status_msg = "Sukses! Pesanan $item ($qty) diproses. Sisa stok: $stok_baru";
} else {
    $status_msg = "Gagal! Stok $item tidak mencukupi atau item tidak ada.";
}

// 2. Kirim Notifikasi via API
kirimWA($status_msg);
function kirimWA($pesan) {
    $curl = curl_init();
    curl_setopt_array($curl, array(
        CURLOPT_URL => 'https://api.fonnte.com/send',
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => array('target' => WA_TARGET, 'message' =>
        $pesan),
        CURLOPT_HTTPHEADER => array("Authorization: " . WA_TOKEN),
    ));
    curl_exec($curl);
    curl_close($curl);
    echo "Respon Sistem: " . $pesan;
}
?>
