<?php
if (session_status() === PHP_SESSION_NONE) session_start();

// Nama halaman aktif untuk highlight menu
$halaman = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($pageTitle) ? $pageTitle . ' - Admin CineMax' : 'Admin CineMax' ?></title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
<div class="admin-wrapper">

<!-- ===== SIDEBAR ===== -->
<aside class="sidebar">

    <!-- Logo -->
    <div class="sidebar-logo">
        <div class="logo-icon">🛡️</div>
        <div>
            <div class="logo-text">Cine<span>Max</span></div>
            <span class="logo-sub">ADMIN PANEL</span>
        </div>
    </div>

    <!-- Menu -->
    <nav class="sidebar-menu">

        <div class="menu-label">Utama</div>
        <a href="index.php" class="<?= $halaman == 'index.php' ? 'active' : '' ?>">
            <i class="fas fa-home"></i> <span>Dashboard</span>
        </a>

        <div class="menu-label">Konten</div>
        <a href="films.php" class="<?= $halaman == 'films.php' ? 'active' : '' ?>">
            <i class="fas fa-film"></i> <span>Film</span>
        </a>
        <a href="jadwal.php" class="<?= $halaman == 'jadwal.php' ? 'active' : '' ?>">
            <i class="fas fa-calendar-alt"></i> <span>Jadwal Tayang</span>
        </a>
        <a href="studio.php" class="<?= $halaman == 'studio.php' ? 'active' : '' ?>">
            <i class="fas fa-building"></i> <span>Studio</span>
        </a>

        <div class="menu-label">Transaksi</div>
        <a href="transactions.php" class="<?= $halaman == 'transactions.php' ? 'active' : '' ?>">
            <i class="fas fa-receipt"></i> <span>Pemesanan</span>
        </a>

        <div class="menu-label">Pengguna</div>
        <a href="users.php" class="<?= $halaman == 'users.php' ? 'active' : '' ?>">
            <i class="fas fa-users"></i> <span>Pengguna</span>
        </a>
        <a href="admins.php" class="<?= $halaman == 'admins.php' ? 'active' : '' ?>">
            <i class="fas fa-user-shield"></i> <span>Admin</span>
        </a>

    </nav>

    <!-- Info user + tombol logout -->
    <div class="sidebar-footer">
        <div class="sidebar-user">
            <div class="user-avatar">
                <?= strtoupper(substr($_SESSION['admin_nama'] ?? 'A', 0, 1)) ?>
            </div>
            <div class="user-info">
                <div class="user-name"><?= htmlspecialchars($_SESSION['admin_nama'] ?? '') ?></div>
                <div class="user-role">Administrator</div>
            </div>
        </div>
        <a href="logout.php" class="btn-logout-sidebar">
            <i class="fas fa-sign-out-alt"></i> <span>Keluar</span>
        </a>
    </div>

</aside>

<!-- ===== KONTEN UTAMA ===== -->
<main class="admin-main">

    <!-- Topbar -->
    <div class="topbar">
        <div class="topbar-title">
            <?= isset($pageTitle) ? htmlspecialchars($pageTitle) : 'Dashboard' ?>
        </div>
        <div class="topbar-links">
            <a href="logout.php"><i class="fas fa-sign-out-alt"></i> Keluar</a>
        </div>
    </div>

    <!-- Konten halaman -->
    <div class="admin-content">
