<?php
require_once 'config.php';
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

// Batas minimal stok untuk memicu peringatan
$threshold = 5;

// Ambil semua item yang stoknya kritis
$sql = "SELECT nama_item, stok FROM stok_gudang WHERE stok < $threshold";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    echo "Ditemukan " . $result->num_rows . " item dengan stok kritis.<br>";

    while ($row = $result->fetch_assoc()) {
        $nama = $row['nama_item'];
        $stok = $row['stok'];

        // Siapkan pesan peringatan
        $pesan = "PERINGATAN STOK RENDAH: Item '$nama' sisa $stok. Segera lakukan pengadaan!";

        // PANGGIL Microservice Notifikasi
        $notif_url = 'http://localhost/integrasi%20sistem/notification_service.php';
        $payload = json_encode([
            'message' => $pesan,
            'status' => 'gagal' // Kita set 'gagal' agar muncul tanda [WARNING] di WA
        ]);

        $ch = curl_init($notif_url);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type:application/json'));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $res = curl_exec($ch);
        curl_close($ch);

        echo "- Notifikasi dikirim untuk: $nama (Respon: $res)<br>";
    }
} else {
    echo "Semua stok aman (di atas $threshold).";
}
