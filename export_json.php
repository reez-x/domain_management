<?php
require 'function.php'; // koneksi ke DB

header('Content-Type: application/json');
header('Content-Disposition: attachment; filename="domains.json"');

// Ambil data dari database
$sql = "SELECT id, item_type, name, provider, service_id, expire_date, auto_renew, cost, currency, note, created_at, updated_at FROM domains";
$result = $conn->query($sql);

$data = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $data[] = $row;
    }
}

// Output sebagai JSON
echo json_encode($data, JSON_PRETTY_PRINT);

$conn->close();
?>
