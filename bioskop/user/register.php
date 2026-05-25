<?php
require_once '../config/database.php';

if (isset($_SESSION['user_id'])) {
    header('Location: dashboard.php');
    exit;
}

$error   = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama     = trim($_POST['nama'] ?? '');
    $email    = trim($_POST['email'] ?? '');
    $no_hp    = trim($_POST['no_hp'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm  = $_POST['confirm_password'] ?? '';

    if (empty($nama) || empty($email) || empty($password)) {
        $error = 'Nama, email, dan password wajib diisi.';
    } elseif ($password !== $confirm) {
        $error = 'Konfirmasi password tidak cocok.';
    } elseif (strlen($password) < 6) {
        $error = 'Password minimal 6 karakter.';
    } else {
        $check = $conn->prepare("SELECT id FROM users WHERE email = ?");
        $check->bind_param("s", $email);
        $check->execute();
        if ($check->get_result()->num_rows > 0) {
            $error = 'Email sudah terdaftar. Gunakan email lain.';
        } else {
            $hashed = password_hash($password, PASSWORD_DEFAULT);
            $stmt   = $conn->prepare("INSERT INTO users (nama, email, password, no_hp) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("ssss", $nama, $email, $hashed, $no_hp);
            if ($stmt->execute()) {
                $success = 'Registrasi berhasil! Silakan masuk.';
            } else {
                $error = 'Terjadi kesalahan. Coba lagi.';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar - CineMax</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .register-page {
            min-height: 100vh; display: flex; align-items: center;
            justify-content: center;
            background: radial-gradient(ellipse at center, #1a0505 0%, #0a0a0a 70%);
            padding: 2rem;
        }
        .register-box { width: 100%; max-width: 480px; }
        .register-logo { text-align: center; margin-bottom: 2rem; }
        .register-logo .icon {
            width: 70px; height: 70px;
            background: linear-gradient(135deg, var(--primary), var(--primary-dark));
            border-radius: 16px; display: inline-flex; align-items: center;
            justify-content: center; font-size: 2rem; margin-bottom: 1rem;
            box-shadow: 0 8px 25px rgba(192,57,43,0.4);
        }
        .register-logo h1 { font-size: 2rem; font-weight: 800; }
        .register-logo h1 span { color: var(--primary-light); }
        .register-card {
            background: var(--bg-card); border: 1px solid var(--border);
            border-radius: 16px; padding: 2rem;
            box-shadow: 0 20px 60px rgba(0,0,0,0.5);
        }
        .register-card h2 { font-size: 1.4rem; margin-bottom: 1.5rem; padding-bottom: 1rem; border-bottom: 1px solid var(--border); }
        .input-icon { position: relative; }
        .input-icon i { position: absolute; left: 1rem; top: 50%; transform: translateY(-50%); color: var(--text-gray); }
        .input-icon .form-control { padding-left: 2.8rem; }
    </style>
</head>
<body>
<div class="register-page">
    <div class="register-box">
        <div class="register-logo">
            <div class="icon">🎬</div>
            <h1>Cine<span>Max</span></h1>
            <p style="color:var(--text-gray)">Buat akun baru Anda</p>
        </div>
        <div class="register-card">
            <h2><i class="fas fa-user-plus" style="color:var(--primary-light); margin-right:8px"></i>Daftar Akun</h2>

            <?php if ($error): ?>
            <div class="alert alert-danger"><i class="fas fa-exclamation-circle"></i> <?= htmlspecialchars($error) ?></div>
            <?php endif; ?>
            <?php if ($success): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i> <?= $success ?>
                <a href="login.php" style="color:var(--success); font-weight:700; margin-left:8px">Masuk Sekarang →</a>
            </div>
            <?php endif; ?>

            <form method="POST">
                <div class="form-group">
                    <label class="form-label">Nama Lengkap</label>
                    <div class="input-icon">
                        <i class="fas fa-user"></i>
                        <input type="text" name="nama" class="form-control" placeholder="Nama lengkap Anda"
                               value="<?= htmlspecialchars($_POST['nama'] ?? '') ?>" required>
                    </div>
                </div>
                <div class="form-group">
                    <label class="form-label">Email</label>
                    <div class="input-icon">
                        <i class="fas fa-envelope"></i>
                        <input type="email" name="email" class="form-control" placeholder="Email Anda"
                               value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" required>
                    </div>
                </div>
                <div class="form-group">
                    <label class="form-label">No. HP <span style="color:#555">(opsional)</span></label>
                    <div class="input-icon">
                        <i class="fas fa-phone"></i>
                        <input type="text" name="no_hp" class="form-control" placeholder="08xxxxxxxxxx"
                               value="<?= htmlspecialchars($_POST['no_hp'] ?? '') ?>">
                    </div>
                </div>
                <div class="form-group">
                    <label class="form-label">Password</label>
                    <div class="input-icon">
                        <i class="fas fa-lock"></i>
                        <input type="password" name="password" class="form-control" placeholder="Minimal 6 karakter" required>
                    </div>
                </div>
                <div class="form-group">
                    <label class="form-label">Konfirmasi Password</label>
                    <div class="input-icon">
                        <i class="fas fa-lock"></i>
                        <input type="password" name="confirm_password" class="form-control" placeholder="Ulangi password" required>
                    </div>
                </div>
                <button type="submit" class="btn btn-primary btn-block btn-lg mt-2">
                    <i class="fas fa-user-plus"></i> Daftar Sekarang
                </button>
            </form>

            <hr class="divider">
            <p class="text-center text-gray text-sm">
                Sudah punya akun?
                <a href="login.php" style="color:var(--primary-light); font-weight:600">Masuk di sini</a>
            </p>
        </div>
    </div>
</div>
</body>
</html>
