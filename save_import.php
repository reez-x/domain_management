<?php
session_start();
require 'function.php';

$action = $_POST['action'] ?? '';
$data = $_SESSION['import_data'] ?? [];

if (empty($data)) {
    header("Location: dashboard.php?import=empty");
    exit;
}

foreach ($data as $row) {
    if ($action === 'overwrite') {
        // replace jika ada
        $stmt = $conn->prepare("REPLACE INTO domains 
            (item_type, name, provider, service_id, expire_date, auto_renew, cost, currency, note, created_at, updated_at)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())");
        $stmt->bind_param("sssssidss",
            $row['item_type'],
            $row['name'],
            $row['provider'],
            $row['service_id'],
            $row['expire_date'],
            $row['auto_renew'],
            $row['cost'],
            $row['currency'],
            $row['note']
        );
        $stmt->execute();
        $stmt->close();
    } elseif ($action === 'skip') {
        // insert only if not exists
        $stmt = $conn->prepare("INSERT IGNORE INTO domains 
            (item_type, name, provider, service_id, expire_date, auto_renew, cost, currency, note, created_at, updated_at)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())");
        $stmt->bind_param("sssssidss",
            $row['item_type'],
            $row['name'],
            $row['provider'],
            $row['service_id'],
            $row['expire_date'],
            $row['auto_renew'],
            $row['cost'],
            $row['currency'],
            $row['note']
        );
        $stmt->execute();
        $stmt->close();
    } elseif ($action === 'insert') {
        // langsung insert (anggap tidak ada duplikat)
        $stmt = $conn->prepare("INSERT INTO domains 
            (item_type, name, provider, service_id, expire_date, auto_renew, cost, currency, note, created_at, updated_at)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())");
        $stmt->bind_param("sssssidss",
            $row['item_type'],
            $row['name'],
            $row['provider'],
            $row['service_id'],
            $row['expire_date'],
            $row['auto_renew'],
            $row['cost'],
            $row['currency'],
            $row['note']
        );
        $stmt->execute();
        $stmt->close();
    }
}

unset($_SESSION['import_data'], $_SESSION['duplicates']);
header("Location: dashboard.php?import=success");
exit;
