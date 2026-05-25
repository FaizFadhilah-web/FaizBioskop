<?php
require_once '../config/database.php';

// Sudah login? langsung ke dashboard
if (isset($_SESSION['admin_id'])) {
    header('Location: index.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if (!$username || !$password) {
        $error = 'Username dan password wajib diisi.';
    } else {
        $stmt = $conn->prepare("SELECT id, nama, username, password FROM admin WHERE username = ? AND is_active = 1");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $admin = $stmt->get_result()->fetch_assoc();

        if ($admin && password_verify($password, $admin['password'])) {
            $_SESSION['admin_id']       = $admin['id'];
            $_SESSION['admin_nama']     = $admin['nama'];
            $_SESSION['admin_username'] = $admin['username'];
            header('Location: index.php');
            exit;
        } else {
            $error = 'Username atau password salah.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Admin - CineMax</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            background: #0a0a0a;
            padding: 1.5rem;
        }
        .login-wrap { width: 100%; max-width: 400px; }
        .login-logo {
            text-align: center;
            margin-bottom: 1.5rem;
        }
        .login-logo .ikon {
            font-size: 2.5rem;
            display: block;
            margin-bottom: 0.5rem;
        }
        .login-logo h1 { font-size: 1.8rem; font-weight: 800; }
        .login-logo h1 span { color: #5dade2; }
        .login-logo p { color: #888; font-size: 0.88rem; margin-top: 0.3rem; }
        .login-card {
            background: #141414;
            border: 1px solid #2a2a2a;
            border-radius: 12px;
            padding: 1.8rem;
        }
        .login-card h2 {
            font-size: 1.1rem;
            margin-bottom: 1.2rem;
            padding-bottom: 0.8rem;
            border-bottom: 1px solid #2a2a2a;
            color: #ccc;
        }
        
        .link-kembali a { color: #666; }
        .link-kembali a:hover { color: #5dade2; }
        .btn-masuk {
            width: 100%;
            padding: 0.8rem;
            background: #1c3a5a;
            border: 1px solid #2e5a8a;
            border-radius: 8px;
            color: #5dade2;
            font-size: 0.95rem;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.2s;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }
        .btn-masuk:hover { background: #234a72; }
    </style>
</head>
<body>
<div class="login-wrap">

    <div class="login-logo">
        <span class="ikon">🛡️</span>
        <h1>Cine<span>Max</span></h1>
        <p>Panel Administrasi</p>
    </div>

    <div class="login-card">
        <h2><i class="fas fa-user-shield"></i> Masuk sebagai Admin</h2>

        <?php if ($error): ?>
        <div class="alert alert-danger">
            <i class="fas fa-exclamation-circle"></i> <?= htmlspecialchars($error) ?>
        </div>
        <?php endif; ?>

        <form method="POST">
            <div class="form-group">
                <label class="form-label">Username</label>
                <input type="text" name="username" class="form-control"
                       placeholder="Username admin"
                       required>
            </div>
            <div class="form-group">
                <label class="form-label">Password</label>
                <input type="password" name="password" class="form-control"
                       placeholder="Password" required>
            </div>
            <button type="submit" class="btn-masuk">
                <i class="fas fa-sign-in-alt"></i> Masuk
            </button>
        </form>
    </div>

    <div class="link-kembali">
        <a href="../index.php"><i class="fas fa-arrow-left"></i> Kembali ke Website</a>
    </div>

</div>
</body>
</html>
