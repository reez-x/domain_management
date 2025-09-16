<?php

include 'function.php';


if($_SERVER['REQUEST_METHOD'] === 'POST') {
    $type = $_POST['type'];
    $name = $_POST['name'];
    $provider = $_POST['provider'];
    $serviceId = $_POST['serviceId'] ?? '';
    $expiry = $_POST['expiry'];
    $cost = $_POST['cost'] ?? 0;
    $currency = $_POST['currency'] ?? '';
    $autoRenew = isset($_POST['autoRenew']) ? 1 : 0;
    $note = $_POST['note'] ?? '';

    $sql = "INSERT INTO domains (item_type, name, provider, service_id, expire_date, cost, currency, auto_renew, note)
            VALUES ('$type', '$name', '$provider', '$serviceId', '$expiry', '$cost', '$currency', '$autoRenew', '$note')";

    if(runQuery($sql)) {
        header("Location: dashboard.php?msg=success");
        exit;
    } else {
        echo "Gagal menambahkan data: " . $conn->error;
    }
} else {
echo "Tipe, Nama, dan Tanggal Expire wajib diisi!";
}
?>


