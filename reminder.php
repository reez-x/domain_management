<?php
// reminder.php
date_default_timezone_set("Asia/Jakarta");

// Koneksi database
$conn = new mysqli("localhost", "root", "", "domain_management");
if ($conn->connect_error) {
    die("Koneksi gagal: " . $conn->connect_error);
}

// Ambil admin yang sedang login
session_start();
if (!isset($_SESSION['user_id'])) {
    die("Admin belum login");
}

$user_id = $_SESSION['user_id'];
$resultAdmin = $conn->query("SELECT whatsapp FROM admins WHERE id = $user_id");
$admin = $resultAdmin->fetch_assoc();

// Convert nomor WA ke format internasional (62)
$waNumber = $admin['whatsapp'];
if (substr($waNumber, 0, 1) === "0") {
    $waNumber = "62" . substr($waNumber, 1);
}

// Ambil semua domain
$result = $conn->query("SELECT domain_name, expired_date FROM domains");

while ($row = $result->fetch_assoc()) {
    $domain = $row['domain_name'];
    $expired = $row['expired_date'];

    $today = new DateTime();
    $expDate = new DateTime($expired);
    $diff = (int)$today->diff($expDate)->format("%r%a"); // sisa hari (+/-)

    // Kirim WA hanya kalau persis 30, 7, atau 3 hari sebelum expired
    if (in_array($diff, [30, 7, 3])) {
        $pesan = "⚠️ Reminder Domain Expired\n\n"
               . "Domain: *$domain*\n"
               . "Tanggal Expired: $expired\n"
               . "Sisa waktu: $diff hari lagi.";

        kirimWA($waNumber, $pesan);
    }
}

function kirimWA($no, $pesan) {
    $curl = curl_init();
    curl_setopt_array($curl, [
        CURLOPT_URL => "https://api.fonnte.com/send",
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => [
            "target" => $no,
            "message" => $pesan,
        ],
        CURLOPT_HTTPHEADER => [
            "Authorization: 2Q988GtF92TTmSEAEaiC" // ganti token
        ],
    ]);
    $response = curl_exec($curl);
    curl_close($curl);
    echo $response; // untuk debug
}
?>
