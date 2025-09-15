<?php
session_start();
include 'function.php'; 

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');
    $confirm_password = trim($_POST['confirm_password']);
    $whatsapp = trim($_POST['whatsapp'] ?? '');
    


    $_SESSION['error'] = [];


    if (empty($username) || empty($whatsapp) || empty($password) || empty($confirm_password)) {
        $_SESSION['error'][] = "Semua field wajib diisi.";
    }


    if (strlen($password) < 6) {
        $_SESSION['error'][] = "Password minimal 6 karakter!";
    }

    if ($password !== $confirm_password) {
        $_SESSION['error'][] = "Password dan konfirmasi password tidak cocok.";
    }


    if (!preg_match('/^08[0-9]{8,11}$/', $whatsapp)) {
        $_SESSION['error'][] = "Nomor WhatsApp tidak valid!";
    }


    if (!empty($_SESSION['error'])) {
        header("Location: register.php");
        exit;
    }

    // cek username
    $check = $conn->prepare("SELECT id FROM admins WHERE username = ?");
    $check->bind_param("s", $username);
    $check->execute();
    $check->store_result();

    if ($check->num_rows > 0) {
        $_SESSION['error'][] = "Username sudah terpakai, silakan gunakan yang lain.";
        header("Location: register.php");
        exit;
    }
    $check->close();


    $hashed_password = password_hash($password, PASSWORD_DEFAULT);


    $stmt = $conn->prepare("INSERT INTO admins (username, password, whatsapp) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $username, $hashed_password, $whatsapp);

    if ($stmt->execute()) {
        unset($_SESSION['error']);
        $_SESSION['success'] = "Registrasi berhasil, silakan login.";
        header("Location: login.php");
        exit;
    } else {
        $_SESSION['error'][] = "Registrasi gagal: " . $stmt->error;
        header("Location: register.php");
        exit;
    }

    $stmt->close();
}
?>
