<?php
// Konfigurasi koneksi
$DB_HOST = "localhost";
$DB_USER = "root";
$DB_PASS = "";
$DB_NAME = "domain_management";

// Buat koneksi global
$conn = new mysqli($DB_HOST, $DB_USER, $DB_PASS, $DB_NAME);

// Cek error koneksi
if ($conn->connect_error) {
    die("Koneksi gagal: " . $conn->connect_error);
}

/**
 * Jalankan query SELECT dan kembalikan hasil dalam bentuk array asosiatif.
 */
function getData($sql) {
    global $conn;
    $result = $conn->query($sql);
    $rows = [];
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $rows[] = $row;
        }
    }
    return $rows;
}

/**
 * Jalankan query INSERT/UPDATE/DELETE
 */
function runQuery($sql) {
    global $conn;
    return $conn->query($sql);
}
?>
