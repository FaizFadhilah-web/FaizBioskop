<?php
require_once '../config/database.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$pemesanan_id = intval($_GET['id'] ?? 0);
if (!$pemesanan_id) { header('Location: my-tickets.php'); exit; }

$stmt = $conn->prepare("
    SELECT p.*, u.nama as user_nama, u.email as user_email, u.no_hp,
           f.judul, f.genre, f.durasi, f.rating, f.harga as harga_per_kursi,
           j.tanggal, j.jam_tayang, s.nama as studio_nama
    FROM pemesanan p
    JOIN users u ON p.user_id = u.id
    JOIN jadwal j ON p.jadwal_id = j.id
    JOIN films f ON j.film_id = f.id
    JOIN studio s ON j.studio_id = s.id
    WHERE p.id = ? AND p.user_id = ?
");
$stmt->bind_param("ii", $pemesanan_id, $_SESSION['user_id']);
$stmt->execute();
$tiket = $stmt->get_result()->fetch_assoc();
if (!$tiket) { header('Location: my-tickets.php'); exit; }

$kursi_stmt = $conn->prepare("
    SELECT k.kode_kursi, k.baris, k.nomor
    FROM detail_pemesanan dp
    JOIN kursi k ON dp.kursi_id = k.id
    WHERE dp.pemesanan_id = ?
    ORDER BY k.baris, k.nomor
");
$kursi_stmt->bind_param("i", $pemesanan_id);
$kursi_stmt->execute();
$kursi_list = $kursi_stmt->get_result()->fetch_all(MYSQLI_ASSOC);

$hari  = ['Minggu','Senin','Selasa','Rabu','Kamis','Jumat','Sabtu'];
$bulan = ['','Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'];
$dt    = new DateTime($tiket['tanggal']);
$tgl_str = $hari[$dt->format('w')] . ', ' . $dt->format('j') . ' ' . $bulan[(int)$dt->format('n')] . ' ' . $dt->format('Y');

$pageTitle = 'Detail Tiket';
$basePath  = '../';
include '../includes/header.php';
?>

<style>
.ticket-page { padding: 2rem 0; }
.ticket-container { max-width: 700px; margin: 0 auto; }
.ticket-success-banner {
    background: linear-gradient(135deg, rgba(39,174,96,0.15), rgba(39,174,96,0.05));
    border: 1px solid rgba(39,174,96,0.4); border-radius: 12px;
    padding: 1.5rem; text-align: center; margin-bottom: 2rem;
}
.ticket-success-banner .icon { font-size: 3rem; margin-bottom: 0.5rem; }
.ticket-success-banner h2 { color: #5dde8a; font-size: 1.5rem; }
.ticket-success-banner p { color: var(--text-gray); margin-top: 0.3rem; }
.ticket-card {
    background: var(--bg-card); border: 1px solid var(--border);
    border-radius: 16px; overflow: hidden;
    box-shadow: 0 20px 60px rgba(0,0,0,0.5); margin-bottom: 1.5rem;
}
.ticket-header {
    background: linear-gradient(135deg, var(--primary-dark), #1a0505);
    padding: 1.5rem 2rem; display: flex; justify-content: space-between;
    align-items: center; border-bottom: 2px dashed rgba(255,255,255,0.1);
}
.ticket-brand { font-size: 1.3rem; font-weight: 800; }
.ticket-brand span { color: var(--primary-light); }
.ticket-body { padding: 2rem; }
.ticket-film-title { font-size: 1.8rem; font-weight: 900; margin-bottom: 0.3rem; color: var(--text-white); }
.ticket-film-meta { display: flex; gap: 1rem; flex-wrap: wrap; margin-bottom: 1.5rem; }
.ticket-film-meta span { font-size: 0.85rem; color: var(--text-gray); display: flex; align-items: center; gap: 5px; }
.ticket-film-meta i { color: var(--primary-light); }
.ticket-info-grid { display: grid; grid-template-columns: repeat(2, 1fr); gap: 1.2rem; margin-bottom: 1.5rem; }
.ticket-info-label { font-size: 0.75rem; color: var(--text-gray); text-transform: uppercase; letter-spacing: 1px; margin-bottom: 0.3rem; }
.ticket-info-value { font-size: 1rem; font-weight: 700; color: var(--text-white); }
.ticket-divider { border: none; border-top: 2px dashed var(--border); margin: 1.5rem 0; position: relative; }
.ticket-divider::before, .ticket-divider::after {
    content: ''; position: absolute; top: -12px; width: 24px; height: 24px;
    background: var(--bg-dark); border-radius: 50%; border: 1px solid var(--border);
}
.ticket-divider::before { left: -32px; }
.ticket-divider::after { right: -32px; }
.seats-display { display: flex; flex-wrap: wrap; gap: 8px; margin-top: 0.5rem; }
.seat-chip { background: var(--primary); color: white; padding: 0.3rem 0.8rem; border-radius: 6px; font-weight: 700; font-size: 0.9rem; }
.ticket-footer {
    background: var(--bg-card2); padding: 1.2rem 2rem;
    display: flex; justify-content: space-between; align-items: center;
    border-top: 1px solid var(--border);
}
.booking-code { font-family: 'Courier New', monospace; font-size: 1.1rem; font-weight: 800; color: var(--primary-light); letter-spacing: 2px; }
.total-amount { font-size: 1.3rem; font-weight: 900; color: var(--gold); }
.action-buttons { display: flex; gap: 1rem; justify-content: center; flex-wrap: wrap; }

@media print {
    body { background: white !important; color: black !important; }
    .navbar, .footer, .action-buttons, .ticket-success-banner, .breadcrumb-nav { display: none !important; }
    .ticket-card { border: 2px solid #333 !important; box-shadow: none !important; }
    .ticket-header { background: #c0392b !important; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
    .ticket-body { background: white !important; color: black !important; }
    .ticket-film-title { color: black !important; }
    .ticket-info-value { color: black !important; }
    .ticket-footer { background: #f5f5f5 !important; }
    .seat-chip { background: #c0392b !important; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
    .booking-code { color: #c0392b !important; }
    .total-amount { color: #e67e22 !important; }
    .ticket-page { padding: 0 !important; }
}
</style>

<div class="ticket-page">
    <div class="container">
        <div class="ticket-container">
            <div class="breadcrumb-nav d-flex align-center gap-2 mb-3" style="font-size:0.85rem; color:var(--text-gray)">
                <a href="dashboard.php" style="color:var(--text-gray)">Beranda</a>
                <i class="fas fa-chevron-right" style="font-size:0.7rem"></i>
                <a href="my-tickets.php" style="color:var(--text-gray)">Tiket Saya</a>
                <i class="fas fa-chevron-right" style="font-size:0.7rem"></i>
                <span style="color:var(--primary-light)">Detail Tiket</span>
            </div>

            <?php if ($tiket['status'] === 'confirmed'): ?>
            <div class="ticket-success-banner">
                <div class="icon">🎉</div>
                <h2>Pemesanan Dikonfirmasi!</h2>
                <p>Tiket Anda telah dikonfirmasi. Tunjukkan kode booking saat masuk bioskop.</p>
            </div>
            <?php else: ?>
            <div class="ticket-success-banner" style="background:linear-gradient(135deg,rgba(243,156,18,0.15),rgba(243,156,18,0.05)); border-color:rgba(243,156,18,0.4);">
                <div class="icon">⏳</div>
                <h2 style="color:#f39c12;">Menunggu Konfirmasi Admin</h2>
                <p>Pemesanan Anda sedang diproses. Admin akan segera mengkonfirmasi tiket Anda.</p>
            </div>
            <?php endif; ?>

            <div class="ticket-card">
                <div class="ticket-header">
                    <div class="ticket-brand">🎬 Cine<span>Max</span></div>
                    <?php if ($tiket['status'] === 'confirmed'): ?>
                    <div style="background:rgba(39,174,96,0.2); border:1px solid #27ae60; color:#5dde8a; padding:0.3rem 0.8rem; border-radius:20px; font-size:0.8rem; font-weight:700;">
                        ✓ CONFIRMED
                    </div>
                    <?php else: ?>
                    <div style="background:rgba(243,156,18,0.2); border:1px solid #f39c12; color:#f39c12; padding:0.3rem 0.8rem; border-radius:20px; font-size:0.8rem; font-weight:700;">
                        ⏳ MENUNGGU KONFIRMASI
                    </div>
                    <?php endif; ?>
                </div>

                <div class="ticket-body">
                    <h2 class="ticket-film-title"><?= htmlspecialchars($tiket['judul']) ?></h2>
                    <div class="ticket-film-meta">
                        <span><i class="fas fa-tag"></i> <?= htmlspecialchars($tiket['genre']) ?></span>
                        <span><i class="fas fa-clock"></i> <?= $tiket['durasi'] ?> menit</span>
                        <span><i class="fas fa-star" style="color:var(--gold)"></i> <?= htmlspecialchars($tiket['rating']) ?></span>
                    </div>
                    <div class="ticket-info-grid">
                        <div><div class="ticket-info-label"><i class="fas fa-calendar"></i> Tanggal</div><div class="ticket-info-value"><?= $tgl_str ?></div></div>
                        <div><div class="ticket-info-label"><i class="fas fa-clock"></i> Jam Tayang</div><div class="ticket-info-value"><?= substr($tiket['jam_tayang'], 0, 5) ?> WIB</div></div>
                        <div><div class="ticket-info-label"><i class="fas fa-building"></i> Studio</div><div class="ticket-info-value"><?= htmlspecialchars($tiket['studio_nama']) ?></div></div>
                        <div><div class="ticket-info-label"><i class="fas fa-user"></i> Nama Pemesan</div><div class="ticket-info-value"><?= htmlspecialchars($tiket['user_nama']) ?></div></div>
                    </div>
                    <div class="ticket-info-label" style="margin-bottom:0.5rem">
                        <i class="fas fa-couch"></i> Kursi (<?= count($kursi_list) ?> kursi)
                    </div>
                    <div class="seats-display">
                        <?php foreach ($kursi_list as $k): ?>
                        <span class="seat-chip"><?= htmlspecialchars($k['kode_kursi']) ?></span>
                        <?php endforeach; ?>
                    </div>
                    <hr class="ticket-divider">
                    <div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:1rem">
                        <div>
                            <div class="ticket-info-label">Harga per Kursi</div>
                            <div style="font-size:0.95rem; color:var(--text-gray)">
                                Rp <?= number_format($tiket['harga_per_kursi'], 0, ',', '.') ?> × <?= count($kursi_list) ?> kursi
                            </div>
                        </div>
                        <div style="width:80px; height:80px; background:white; border-radius:8px; display:flex; align-items:center; justify-content:center; font-size:2.5rem;">📱</div>
                    </div>
                </div>

                <div class="ticket-footer">
                    <div>
                        <div style="font-size:0.75rem; color:var(--text-gray); margin-bottom:3px">KODE BOOKING</div>
                        <div class="booking-code"><?= htmlspecialchars($tiket['kode_booking']) ?></div>
                    </div>
                    <div style="text-align:right">
                        <div style="font-size:0.75rem; color:var(--text-gray); margin-bottom:3px">TOTAL PEMBAYARAN</div>
                        <div class="total-amount">Rp <?= number_format($tiket['total_harga'], 0, ',', '.') ?></div>
                    </div>
                </div>
            </div>

            <div class="action-buttons">
                <?php if ($tiket['status'] === 'confirmed'): ?>
                <button onclick="window.print()" class="btn btn-primary btn-lg">
                    <i class="fas fa-print"></i> Cetak Tiket
                </button>
                <?php endif; ?>
                <a href="my-tickets.php" class="btn btn-outline">
                    <i class="fas fa-ticket-alt"></i> Tiket Saya
                </a>
                <a href="films.php" class="btn btn-outline">
                    <i class="fas fa-film"></i> Pesan Lagi
                </a>
            </div>

            <div class="card mt-3">
                <div class="card-body">
                    <h4 style="margin-bottom:1rem; color:var(--primary-light)">
                        <i class="fas fa-info-circle"></i> Informasi Penting
                    </h4>
                    <ul style="list-style:none; display:flex; flex-direction:column; gap:0.6rem">
                        <li style="font-size:0.9rem; color:var(--text-gray)"><i class="fas fa-check-circle" style="color:var(--success); margin-right:8px"></i>Hadir minimal 15 menit sebelum jam tayang</li>
                        <li style="font-size:0.9rem; color:var(--text-gray)"><i class="fas fa-check-circle" style="color:var(--success); margin-right:8px"></i>Tunjukkan kode booking kepada petugas</li>
                        <li style="font-size:0.9rem; color:var(--text-gray)"><i class="fas fa-check-circle" style="color:var(--success); margin-right:8px"></i>Tiket tidak dapat dikembalikan atau ditukar</li>
                        <li style="font-size:0.9rem; color:var(--text-gray)"><i class="fas fa-check-circle" style="color:var(--success); margin-right:8px"></i>Dilarang membawa makanan dan minuman dari luar</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
