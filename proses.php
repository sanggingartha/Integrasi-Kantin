<?php

require_once 'config.php';
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

$item = $_POST['item'];
$qty  = intval($_POST['qty']); 

if ($qty <= 0) {
    $status_msg = "[WARNING] Jumlah pesanan tidak valid. Qty harus lebih dari 0.";
    kirimWA($status_msg);
    exit; 
}

$res = $conn->query("SELECT * FROM stok_gudang WHERE nama_item = '$item'");
$data = $res->fetch_assoc();

if ($data && $data['stok'] >= $qty) {
    $stok_baru = $data['stok'] - $qty;

    $conn->query("UPDATE stok_gudang 
                  SET stok = $stok_baru 
                  WHERE nama_item = '$item'");

    $status_msg = "Sukses! Pesanan $item ($qty) diproses. Sisa stok: $stok_baru";
} else {
    $status_msg = "Gagal! Stok $item tidak mencukupi atau item tidak ada.";
}


kirimWA($status_msg);


function kirimWA($pesan) {

    // Jika pesan gagal → tambahkan prefix WARNING
    if (str_contains($pesan, "Gagal")) {
        $pesan = "[WARNING] " . $pesan;
    }

    $curl = curl_init();

    curl_setopt_array($curl, array(
        CURLOPT_URL => 'https://api.fonnte.com/send',
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => array(
            'target'  => WA_TARGET,
            'message' => $pesan
        ),
        CURLOPT_HTTPHEADER => array(
            "Authorization: " . WA_TOKEN
        ),
    ));

    curl_exec($curl);
    curl_close($curl);

    echo "Respon Sistem: " . $pesan;
}
?>