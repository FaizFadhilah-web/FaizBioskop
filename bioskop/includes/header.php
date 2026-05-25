<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($pageTitle) ? $pageTitle . ' - CineMax' : 'CineMax - Tiket Bioskop Online' ?></title>
    <link rel="stylesheet" href="<?= $basePath ?? '' ?>assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <?php if (isset($extraCSS)) echo $extraCSS; ?>
</head>
<body>

<nav class="navbar">
    <div class="navbar-inner">
        <a href="dashboard.php" class="navbar-brand">
            <div class="logo-icon">🎬</div>
            Cine<span>Max</span>
        </a>

        <?php if (isset($_SESSION['user_id'])): ?>
        <ul class="navbar-nav">
            <li><a href="dashboard.php" class="<?= basename($_SERVER['PHP_SELF']) == 'dashboard.php' ? 'active' : '' ?>">
                <i class="fas fa-home"></i> Beranda
            </a></li>
            <li><a href="films.php" class="<?= basename($_SERVER['PHP_SELF']) == 'films.php' ? 'active' : '' ?>">
                <i class="fas fa-film"></i> Film
            </a></li>
            <li><a href="my-tickets.php" class="<?= basename($_SERVER['PHP_SELF']) == 'my-tickets.php' ? 'active' : '' ?>">
                <i class="fas fa-ticket-alt"></i> Tiket Saya
            </a></li>
            <li>
                <span style="color: var(--text-gray); font-size:0.9rem;">
                    <i class="fas fa-user-circle" style="color:var(--primary-light)"></i>
                    <?= htmlspecialchars($_SESSION['user_nama']) ?>
                </span>
            </li>
            <li><a href="logout.php" class="btn-logout">
                <i class="fas fa-sign-out-alt"></i> Keluar
            </a></li>
        </ul>
        <?php else: ?>
        <ul class="navbar-nav">
            <li><a href="login.php">
                <i class="fas fa-sign-in-alt"></i> Masuk
            </a></li>
            <li><a href="register.php" class="btn-logout">
                <i class="fas fa-user-plus"></i> Daftar
            </a></li>
        </ul>
        <?php endif; ?>
    </div>
</nav>
