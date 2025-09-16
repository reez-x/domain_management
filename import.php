<?php
session_start();
require_once 'function.php';

if (isset($_POST['import'])) {
    $file = $_FILES['file']['tmp_name'];
    $ext = strtolower(pathinfo($_FILES['file']['name'], PATHINFO_EXTENSION));

    $importData = [];
    if ($ext === "csv") {
        $handle = fopen($file, "r");
        fgetcsv($handle, 1000, ","); // skip header
        while (($row = fgetcsv($handle, 1000, ",")) !== FALSE) {
            $importData[] = [
                "item_type"   => $row[1],
                "name"        => $row[2],
                "provider"    => $row[3],
                "service_id"  => $row[4],
                "expire_date" => $row[5],
                "auto_renew"  => $row[6],
                "cost"        => $row[7],
                "currency"    => $row[8],
                "note"        => $row[9],
            ];
        }
        fclose($handle);
    } elseif ($ext === "json") {
        $json = file_get_contents($file);
        $importData = json_decode($json, true);
    }

    // cek duplikasi
    $duplicates = 0;
    foreach ($importData as $row) {
        $stmt = $conn->prepare("SELECT COUNT(*) FROM domains WHERE name=? AND item_type=?");
        $stmt->bind_param("ss", $row['name'], $row['item_type']);
        $stmt->execute();
        $stmt->bind_result($count);
        $stmt->fetch();
        if ($count > 0) $duplicates++;
        $stmt->close();
    }

    $_SESSION['import_data'] = $importData;
    $_SESSION['duplicates']  = $duplicates;

    // 🚨 Pastikan tidak ada output sebelum ini
    header("Location: dashboard.php?show_import_confirm=1");
    exit;
}
