<?php
include 'function.php';

if (isset($_POST['id'])) {
    $id = intval($_POST['id']);
    $sql = "DELETE FROM domains WHERE id = $id";
    if (runQuery($sql)) {
        header("Location: dashboard.php?msg=deleted");
        exit;
    } else {
        echo "Gagal menghapus data.";
    }
} else {
    echo "ID tidak ditemukan.";
}
?>
