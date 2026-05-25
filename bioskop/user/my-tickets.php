<?php
require_once '../config/database.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['user_id'];

$stmt = $conn->prepare("
    SELECT p.id, p.kode_booking, p.user_id, p.jadwal_id, p.total_harga, p.status, p.created_at,
           f.judul, f.genre, f.durasi,
           j.tanggal, j.jam_tayang,
           s.nama as studio_nama,
           COUNT(dp.id) as jumlah_kursi
    FROM pemesanan p
    JOIN jadwal j ON p.jadwal_id = j.id
    JOIN films f ON j.film_id = f.id
    JOIN studio s ON j.studio_id = s.id
    LEFT JOIN detail_pemesanan dp ON dp.pemesanan_id = p.id
    WHERE p.user_id = ?
    GROUP BY p.id, p.kode_booking, p.user_id, p.jadwal_id, p.total_harga, p.status, p.created_at,
             f.judul, f.genre, f.durasi, j.tanggal, j.jam_tayang, s.nama
    ORDER BY p.created_at DESC
");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$tikets = $stmt->get_result();

$hari  = ['Minggu','Senin','Selasa','Rabu','Kamis','Jumat','Sabtu'];
$bulan = ['','Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'];

$pageTitle = 'Tiket Saya';
$basePath  = '../';
$extraCSS  = '<style>
.my-tickets-page { padding: 2rem 0; }
.page-title-bar {
    background: linear-gradient(135deg, #0a0a0a 0%, #1a0505 100%);
    padding: 2rem 0;
    border-bottom: 1px solid var(--border);
    margin-bottom: 2rem;
}
.ticket-item {
    background: var(--bg-card);
    border: 1px solid var(--border);
    border-radius: 12px;
    overflow: hidden;
    transition: all 0.3s;
    margin-bottom: 1rem;
}
.ticket-item:hover { border-color: var(--primary); box-shadow: 0 5px 20px rgba(192,57,43,0.2); }
.ticket-item-header {
    background: linear-gradient(135deg, #1a0505, #0a0a0a);
    padding: 1rem 1.5rem;
    display: flex;
    justify-content: space-between;
    align-items: center;
    border-bottom: 1px solid var(--border);
}
.ticket-item-body {
    padding: 1.2rem 1.5rem;
    display: grid;
    grid-template-columns: 1fr auto;
    gap: 1rem;
    align-items: center;
}
.ticket-film-name { font-size: 1.2rem; font-weight: 800; margin-bottom: 0.5rem; }
.ticket-meta-row { display: flex; flex-wrap: wrap; gap: 1rem; font-size: 0.85rem; color: var(--text-gray); }
.ticket-meta-row span { display: flex; align-items: center; gap: 5px; }
.ticket-meta-row i { color: var(--primary-light); }
.ticket-item-actions { display: flex; flex-direction: column; gap: 0.5rem; align-items: flex-end; }
.ticket-price { font-size: 1.2rem; font-weight: 800; color: var(--gold); }
.empty-state { text-align: center; padding: 5rem 2rem; color: var(--text-gray); }
.empty-state .icon { font-size: 5rem; margin-bottom: 1rem; }
.empty-state h3 { font-size: 1.5rem; margin-bottom: 0.5rem; }
@media (max-width: 600px) {
    .ticket-item-body { grid-template-columns: 1fr; }
    .ticket-item-actions { align-items: flex-start; flex-direction: row; flex-wrap: wrap; }
}
</style>';

include '../includes/header.php';
?>

<div class="page-title-bar">
    <div class="container">
        <h1 style="font-size:2rem; font-weight:800">
            <i class="fas fa-ticket-alt" style="color:var(--primary-light)"></i>
            Tiket <span style="color:var(--primary-light)">Saya</span>
        </h1>
        <p style="color:var(--text-gray); margin-top:0.3rem">Riwayat pemesanan tiket bioskop Anda</p>
    </div>
</div>

<div class="my-tickets-page">
    <div class="container">
        <?php if ($tikets->num_rows > 0): ?>
        <p style="color:var(--text-gray); margin-bottom:1.5rem; font-size:0.9rem">
            Total <strong style="color:white"><?= $tikets->num_rows ?></strong> pemesanan
        </p>

        <?php while ($t = $tikets->fetch_assoc()):
            $dt      = new DateTime($t['tanggal']);
            $tgl_str = $hari[$dt->format('w')] . ', ' . $dt->format('j') . ' ' . $bulan[(int)$dt->format('n')] . ' ' . $dt->format('Y');
            $is_past = $dt < new DateTime('today');
        ?>
        <div class="ticket-item">
            <div class="ticket-item-header">
                <div style="display:flex; align-items:center; gap:10px">
                    <span style="font-family:monospace; font-size:0.9rem; color:var(--primary-light); font-weight:700">
                        <?= htmlspecialchars($t['kode_booking']) ?>
                    </span>
                    <?php if ($t['status'] === 'confirmed'): ?>
                    <span class="badge badge-success">✓ Confirmed</span>
                    <?php elseif ($t['status'] === 'pending'): ?>
                    <span class="badge" style="background:rgba(243,156,18,0.15); color:#f39c12; border:1px solid rgba(243,156,18,0.4); padding:0.18rem 0.6rem; border-radius:20px; font-size:0.72rem; font-weight:600;">⏳ Menunggu Konfirmasi</span>
                    <?php else: ?>
                    <span class="badge badge-gray"><?= ucfirst($t['status']) ?></span>
                    <?php endif; ?>
                    <?php if ($is_past): ?>
                    <span class="badge badge-gray">Selesai</span>
                    <?php elseif ($t['status'] === 'confirmed'): ?>
                    <span class="badge badge-success">Aktif</span>
                    <?php endif; ?>
                </div>
                <span style="font-size:0.8rem; color:var(--text-gray)">
                    <i class="fas fa-clock"></i>
                    Dipesan: <?= date('d/m/Y H:i', strtotime($t['created_at'])) ?>
                </span>
            </div>
            <div class="ticket-item-body">
                <div class="ticket-item-info">
                    <div class="ticket-film-name">🎬 <?= htmlspecialchars($t['judul']) ?></div>
                    <div class="ticket-meta-row">
                        <span><i class="fas fa-calendar"></i> <?= $tgl_str ?></span>
                        <span><i class="fas fa-clock"></i> <?= substr($t['jam_tayang'], 0, 5) ?> WIB</span>
                        <span><i class="fas fa-building"></i> <?= htmlspecialchars($t['studio_nama']) ?></span>
                        <span><i class="fas fa-couch"></i> <?= $t['jumlah_kursi'] ?> kursi</span>
                        <span><i class="fas fa-tag"></i> <?= htmlspecialchars($t['genre']) ?></span>
                    </div>
                </div>
                <div class="ticket-item-actions">
                    <div class="ticket-price">Rp <?= number_format($t['total_harga'], 0, ',', '.') ?></div>
                    <a href="ticket-detail.php?id=<?= $t['id'] ?>" class="btn btn-primary btn-sm">
                        <i class="fas fa-eye"></i> Lihat Tiket
                    </a>
                    <?php if ($t['status'] === 'confirmed'): ?>
                    <a href="ticket-detail.php?id=<?= $t['id'] ?>" 
                       onclick="setTimeout(()=>window.print(),800); return true;"
                       class="btn btn-outline btn-sm">
                        <i class="fas fa-print"></i> Cetak
                    </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <?php endwhile; ?>

        <?php else: ?>
        <div class="empty-state">
            <div class="icon">🎟️</div>
            <h3>Belum Ada Tiket</h3>
            <p>Anda belum pernah memesan tiket. Yuk, pesan sekarang!</p>
            <a href="films.php" class="btn btn-primary btn-lg mt-3">
                <i class="fas fa-film"></i> Lihat Film Sekarang
            </a>
        </div>
        <?php endif; ?>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
