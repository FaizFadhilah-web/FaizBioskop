<?php
require_once '../config/database.php';

if (isset($_SESSION['user_id'])) {
    header('Location: dashboard.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($email) || empty($password)) {
        $error = 'Email dan password wajib diisi.';
    } else {
        $stmt = $conn->prepare("SELECT id, nama, email, password FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        $user   = $result->fetch_assoc();

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id']    = $user['id'];
            $_SESSION['user_nama']  = $user['nama'];
            $_SESSION['user_email'] = $user['email'];
            header('Location: dashboard.php');
            exit;
        } else {
            $error = 'Email atau password salah.';
        }
    }
}

$pageTitle = 'Masuk';
$basePath  = '../';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Masuk - CineMax</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .login-page {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: radial-gradient(ellipse at center, #1a0505 0%, #0a0a0a 70%);
            padding: 2rem;
        }
        .login-box { width: 100%; max-width: 440px; }
        .login-logo { text-align: center; margin-bottom: 2rem; }
        .login-logo .icon {
            width: 70px; height: 70px;
            background: linear-gradient(135deg, var(--primary), var(--primary-dark));
            border-radius: 16px;
            display: inline-flex; align-items: center; justify-content: center;
            font-size: 2rem; margin-bottom: 1rem;
            box-shadow: 0 8px 25px rgba(192,57,43,0.4);
        }
        .login-logo h1 { font-size: 2rem; font-weight: 800; }
        .login-logo h1 span { color: var(--primary-light); }
        .login-logo p { color: var(--text-gray); margin-top: 0.3rem; }
        .login-card {
            background: var(--bg-card);
            border: 1px solid var(--border);
            border-radius: 16px;
            padding: 2rem;
            box-shadow: 0 20px 60px rgba(0,0,0,0.5);
        }
        .login-card h2 {
            font-size: 1.4rem; margin-bottom: 1.5rem;
            padding-bottom: 1rem; border-bottom: 1px solid var(--border);
        }
        .input-icon { position: relative; }
        .input-icon i {
            position: absolute; left: 1rem; top: 50%;
            transform: translateY(-50%); color: var(--text-gray);
        }
        .input-icon .form-control { padding-left: 2.8rem; }
    </style>
</head>
<body>
<div class="login-page">
    <div class="login-box">
        <div class="login-logo">
            <div class="icon">🎬</div>
            <h1>Cine<span>Max</span></h1>
            <p>Tiket Bioskop Online Terpercaya</p>
        </div>
        <div class="login-card">
            <h2><i class="fas fa-sign-in-alt" style="color:var(--primary-light); margin-right:8px"></i>Masuk ke Akun</h2>

            <?php if ($error): ?>
            <div class="alert alert-danger">
                <i class="fas fa-exclamation-circle"></i> <?= htmlspecialchars($error) ?>
            </div>
            <?php endif; ?>

            <form method="POST">
                <div class="form-group">
                    <label class="form-label">Email</label>
                    <div class="input-icon">
                        <i class="fas fa-envelope"></i>
                        <input type="email" name="email" class="form-control"
                               placeholder="Masukkan email Anda" required>
                    </div>
                </div>
                <div class="form-group">
                    <label class="form-label">Password</label>
                    <div class="input-icon">
                        <i class="fas fa-lock"></i>
                        <input type="password" name="password" class="form-control"
                               placeholder="Masukkan password Anda" required>
                    </div>
                </div>
                <button type="submit" class="btn btn-primary btn-block btn-lg mt-2">
                    <i class="fas fa-sign-in-alt"></i> Masuk
                </button>
            </form>

            <hr class="divider">
            <p class="text-center text-gray text-sm">
                Belum punya akun?
                <a href="register.php" style="color:var(--primary-light); font-weight:600">Daftar Sekarang</a>
            </p>
        </div>
    </div>
</div>
</body>
</html>
