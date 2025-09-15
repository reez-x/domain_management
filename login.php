<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Login</title>
  <!-- Bootstrap CSS -->
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
    }

    body {
      margin: 0;
      min-height: 100vh;
      display: flex;
      align-items: center;
      justify-content: center;
      background: linear-gradient(180deg, #0b1024, #0a122e 60%, #0b132a);
      color: var(--text);
      font-family: system-ui, -apple-system, Segoe UI, Roboto, Ubuntu, sans-serif;
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
      background: #1f2937;
      color: var(--accent);
      padding: 8px 12px;
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


        <div class="card mt-5 shadow">
          <div class="card-header text-center">
            <h4>Login</h4>
          </div>
          <div class="card-body">
            <form action="../includes/login.inc.php" method="POST">
              <div class="form-group">
                <label for="username">Username</label>
                <input type="text" name="username" id="username" class="form-control">
              </div>
              <div class="form-group">
                <label for="password">Password</label>
                <input type="password" name="password" id="password" class="form-control">
              </div>
              <button type="submit" class="btn btn-primary btn-block">Login</button>
            </form>
          </div>

      </div>
    </div>
  </div>

  <!-- Bootstrap JS and dependencies -->
  <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.2/dist/umd/popper.min.js"></script>
  <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>

</html>
