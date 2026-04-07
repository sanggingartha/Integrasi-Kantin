<?php
// notification_service.php
require_once 'config.php';

// Menerima input JSON dari service lain
$input = file_get_contents('php://input');
$data = json_decode($input, true);

if (isset($data['message'])) {
    $pesan = $data['message'];

    // Implementasi Challenge 2: [WARNING] jika status gagal
    if (isset($data['status']) && $data['status'] == 'gagal') {
        $pesan = "[WARNING] " . strtoupper($pesan);
    }

    $curl = curl_init();
    curl_setopt_array($curl, array(
        CURLOPT_URL => 'https://api.fonnte.com/send',
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => array(
            'target' => WA_TARGET,
            'message' => $pesan
        ),
        CURLOPT_HTTPHEADER => array("Authorization: " . WA_TOKEN),
    ));
    $response = curl_exec($curl);
    curl_close($curl);
    echo $response;
}
