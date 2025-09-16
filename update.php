<?php
include 'function.php';


error_reporting(E_ALL);
ini_set('display_errors', 1);

$id = $_POST['id'] ?? '';
$type = $_POST['type'] ?? '';
$name = $_POST['name'] ?? '';
$provider = $_POST['provider'] ?? '';
$expiry = !empty($_POST['expiry']) ? $_POST['expiry'] : null;
$serviceId = $_POST['serviceId'] ?? '';
$cost = !empty($_POST['cost']) ? $_POST['cost'] : 0;
$currency = $_POST['currency'] ?? '';
$autoRenew = isset($_POST['autoRenew']) ? 1 : 0;
$note = $_POST['note'] ?? '';

$stmt = $conn->prepare("UPDATE domains SET 
    item_type=?,
    name=?,
    provider=?,
    service_id=?,
    expire_date=?,
    auto_renew=?,
    cost=?,
    currency=?,
    note=?
    WHERE id=?");

if (!$stmt) {
    die("Prepare failed: " . $conn->error);
}

// Bind sesuai tipe: s=string, i=integer, d=double
$stmt->bind_param("sssssiisss", $type, $name, $provider, $serviceId, $expiry, $autoRenew, $cost, $currency, $note, $id);

if (!$stmt->execute()) {
    die("Execute failed: " . $stmt->error);
}

$stmt->close();

header("Location: dashboard.php?update=success");
exit;

