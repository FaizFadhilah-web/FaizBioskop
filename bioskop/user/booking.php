<?php
require_once '../config/database.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: films.php');
    exit;
}

$user_id     = $_SESSION['user_id'];
$jadwal_id   = intval($_POST['jadwal_id'] ?? 0);
$kursi_ids   = trim($_POST['kursi_ids'] ?? '');
$total_harga = floatval($_POST['total_harga'] ?? 0);

if (!$jadwal_id || !$kursi_ids || $total_harga <= 0) {
    header('Location: films.php');
    exit;
}

$kursi_arr = array_filter(array_map('intval', explode(',', $kursi_ids)));
if (empty($kursi_arr) || count($kursi_arr) > 8) {
    header('Location: films.php');
    exit;
}

$j_stmt = $conn->prepare("SELECT j.*, f.judul, f.harga, s.nama as studio_nama FROM jadwal j JOIN films f ON j.film_id=f.id JOIN studio s ON j.studio_id=s.id WHERE j.id=?");
$j_stmt->bind_param("i", $jadwal_id);
$j_stmt->execute();
$jadwal = $j_stmt->get_result()->fetch_assoc();
if (!$jadwal) { header('Location: films.php'); exit; }

$placeholders = implode(',', array_fill(0, count($kursi_arr), '?'));
$types_str    = str_repeat('i', count($kursi_arr) + 1);
$params_check = array_merge([$jadwal_id], $kursi_arr);
$check_stmt   = $conn->prepare("SELECT COUNT(*) as cnt FROM kursi_terpesan WHERE jadwal_id=? AND kursi_id IN ($placeholders)");
$check_stmt->bind_param($types_str, ...$params_check);
$check_stmt->execute();
$already = $check_stmt->get_result()->fetch_assoc()['cnt'];

if ($already > 0) {
    $_SESSION['error'] = 'Beberapa kursi yang Anda pilih sudah terpesan. Silakan pilih kursi lain.';
    header("Location: seat-selection.php?jadwal_id=$jadwal_id");
    exit;
}

$kode_booking = 'CMX' . strtoupper(substr(md5(uniqid()), 0, 8));

$conn->begin_transaction();
try {
    $p_stmt = $conn->prepare("INSERT INTO pemesanan (kode_booking, user_id, jadwal_id, total_harga, status) VALUES (?, ?, ?, ?, 'pending')");
    $p_stmt->bind_param("siid", $kode_booking, $user_id, $jadwal_id, $total_harga);
    $p_stmt->execute();
    $pemesanan_id = $conn->insert_id;

    // Simpan detail kursi saja, kursi_terpesan baru diisi saat admin konfirmasi
    foreach ($kursi_arr as $kursi_id) {
        $d_stmt = $conn->prepare("INSERT INTO detail_pemesanan (pemesanan_id, kursi_id) VALUES (?, ?)");
        $d_stmt->bind_param("ii", $pemesanan_id, $kursi_id);
        $d_stmt->execute();
    }

    $conn->commit();
    header("Location: ticket-detail.php?id=$pemesanan_id");
    exit;

} catch (Exception $e) {
    $conn->rollback();
    $_SESSION['error'] = 'Terjadi kesalahan saat memproses pemesanan. Coba lagi.';
    header("Location: seat-selection.php?jadwal_id=$jadwal_id");
    exit;
}
