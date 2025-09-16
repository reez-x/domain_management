<?php
include 'function.php'; // koneksi DB

header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=domains_export.csv');

// buka output stream
$output = fopen('php://output', 'w');

// tulis header kolom sesuai database
fputcsv($output, [
    'id',
    'item_type',
    'name',
    'provider',
    'service_id',
    'expire_date',
    'auto_renew',
    'cost',
    'currency',
    'note',
    'created_at',
    'updated_at'
]);

// query data persis dengan struktur tabel
$sql = "SELECT 
            id,
            item_type,
            name,
            provider,
            service_id,
            expire_date,
            auto_renew,
            cost,
            currency,
            note,
            created_at,
            updated_at
        FROM domains";
$data = getData($sql);

// tulis setiap baris ke CSV
foreach ($data as $row) {
    fputcsv($output, $row);
}

fclose($output);
exit;
