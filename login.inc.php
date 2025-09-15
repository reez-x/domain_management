<?php
session_start();
require_once "function.php";


$errors = [];

// Ambil input
$username = trim($_POST['username'] ?? '');
$password = trim($_POST['password'] ?? '');

// Validasi
if ($username === '') {
    $errors[] = "Username wajib diisi.";
}
if ($password === '') {
    $errors[] = "Password wajib diisi.";
}

if (!empty($errors)) {
    $_SESSION['error'] = $errors;
    header("Location: login.php");
    exit;
}


$stmt = $conn->prepare("SELECT id, username, password FROM admins WHERE username = ?");
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();

if ($result && $row = $result->fetch_assoc()) {

    if (password_verify($password, $row['password'])) {

        $_SESSION['user_id'] = $row['id'];
        $_SESSION['username'] = $row['username'];
        header("Location: dashboard.php");
        exit;
    } else {
        $errors[] = "Password salah.";
    }
} else {
    $errors[] = "Username tidak ditemukan.";
}

$stmt->close();


$_SESSION['error'] = $errors;
header("Location: login.php");
exit;
