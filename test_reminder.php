<?php
session_start();
require 'function.php';

// pastikan admin sudah login
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$admin_id = $_SESSION['user_id'];

// ambil nomor wa admin
$res = $conn->query("SELECT whatsapp FROM admins WHERE id = $admin_id LIMIT 1");
$admin = $res->fetch_assoc();
$wa = $admin['whatsapp'] ?? '';

if (!$wa) {
    die("Nomor WhatsApp admin belum diisi.");
}

// convert ke format internasional
if (substr($wa, 0, 1) === '0') {
    $wa = '62' . substr($wa, 1);
}

// ambil random data dari domains
$res2 = $conn->query("SELECT * FROM domains ORDER BY RAND() LIMIT 1");
$domain = $res2->fetch_assoc();

if (!$domain) {
    die("Tidak ada data domain untuk dites.");
}

// hitung hari tersisa
$today = new DateTime();
$exp   = new DateTime($domain['expire_date']);
$diff  = $today->diff($exp)->days;

// isi pesan
$message = "🔔 *Reminder Testing*\n\n".
           "Item: {$domain['item_type']}\n".
           "Nama: {$domain['name']}\n".
           "Provider: {$domain['provider']}\n".
           "Auto-Renew: {$domain['auto_renew']}\n".
           "Cost: {$domain['cost']} {$domain['currency']}\n".
           "Expired dalam: {$diff} hari\n";

// token fonnte
$fonnte_token = "2Q988GtF92TTmSEAEaiC";

$curl = curl_init();
curl_setopt_array($curl, array(
    CURLOPT_URL => 'https://api.fonnte.com/send',
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST => true,
    CURLOPT_POSTFIELDS => array(
        'target' => $wa,
        'message' => $message
    ),
    CURLOPT_HTTPHEADER => array(
        "Authorization: $fonnte_token"
    ),
));
$response = curl_exec($curl);
curl_close($curl);

// tampilkan respon untuk debug
echo $response;
?>
