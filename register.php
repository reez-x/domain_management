<?php
session_start();


if (isset($_SESSION['user_id'])) {
    header("Location: dashboard.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Register</title>
  <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
  <style>
    :root {
      --bg: #0f172a;
      --card: #111827;
      --muted: #94a3b8;
      --text: #e5e7eb;
      --accent: #22d3ee;
      --border: #1f2937;
      --input: #0b1221;
      --error: #fff;
    }
    body {
      margin: 0;
      min-height: 100vh;
      display: flex;
      align-items: center;
      justify-content: center;
      background: linear-gradient(180deg, #0b1024, #0a122e 60%, #0b132a);
      color: var(--text);
    }
    .card {
      background: rgba(17, 24, 39, .85);
      backdrop-filter: blur(6px);
      border: 1px solid var(--border);
      border-radius: 14px;
      color: var(--text);
    }
    .card-header {
      border-bottom: 1px solid var(--border);
    }
    label {
      color: var(--muted);
      font-size: 13px;
      margin-bottom: 4px;
    }
    .form-control {
      background: var(--input);
      border: 1px solid var(--border);
      color: var(--text);
      border-radius: 10px;
      padding: 10px 12px;
    }
    .form-control:focus {
      border-color: var(--accent);
      box-shadow: 0 0 0 0.2rem rgba(34, 211, 238, 0.25);
    }
    .btn-primary {
      background: var(--accent);
      border: none;
      border-radius: 12px;
      font-weight: 600;
    }
    .btn-primary:hover {
      background: #06b6d4;
    }
    a {
      color: var(--accent);
      text-decoration: none;
    }
    a:hover {
      text-decoration: underline;
    }
    #notif {
      background: #dc3545;
      color: var(--error);
      padding: 8px;
      border-radius: 8px;
      text-align: center;
      margin-bottom: 10px;
    }
  </style>
</head>
<body>
  <div class="container">
    <div class="row justify-content-center">
      <div class="col-md-6">

        <?php if (isset($_SESSION["error"])): ?>
          <div id="notif">
            <?php 
              foreach ($_SESSION["error"] as $err) {
                  echo "<p>$err</p>";
              }
              unset($_SESSION["error"]);
            ?>
          </div>
        <?php endif; ?>

        <div class="card mt-2 shadow">
          <div class="card-header text-center">
            <h4>Register</h4>
          </div>
          <div class="card-body">
            <form action="register.inc.php" method="POST">
              <div class="form-group">
                <label for="username">Username</label>
                <input type="text" name="username" id="username" class="form-control" required>
              </div>
              <div class="form-group">
                <label for="whatsapp">No. WhatsApp</label>
                <input type="text" name="whatsapp" id="whatsapp" class="form-control" placeholder="08xxxxxxxxxx" required>
              </div>
              <div class="form-group">
                <label for="password">Password</label>
                <input type="password" name="password" id="password" class="form-control" required>
              </div>
              <div class="form-group">
  <label for="confirm_password">Konfirmasi Password</label>
  <input type="password" name="confirm_password" id="confirm_password" class="form-control" required>
</div>
              <button type="submit" class="btn btn-primary btn-block">Register</button>
              <div class="mt-3 text-center">
                Sudah punya akun? <a href="login.php">Login di sini</a>
              </div>
            </form>
          </div>
        </div>

      </div>
    </div>
  </div>
</body>
</html>
