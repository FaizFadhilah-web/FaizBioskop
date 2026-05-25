<?php
require_once '../config/database.php';
require_once 'includes/auth.php';

// Statistik
$total_film      = $conn->query("SELECT COUNT(*) as c FROM films")->fetch_assoc()['c'];
$film_tayang     = $conn->query("SELECT COUNT(*) as c FROM films WHERE status='tayang'")->fetch_assoc()['c'];
$total_user      = $conn->query("SELECT COUNT(*) as c FROM users")->fetch_assoc()['c'];
$total_pesan     = $conn->query("SELECT COUNT(*) as c FROM pemesanan")->fetch_assoc()['c'];
$total_jadwal    = $conn->query("SELECT COUNT(*) as c FROM jadwal WHERE tanggal >= CURDATE()")->fetch_assoc()['c'];
$pendapatan      = $conn->query("SELECT COALESCE(SUM(total_harga),0) as s FROM pemesanan WHERE status='confirmed'")->fetch_assoc()['s'];
$pesan_confirmed = $conn->query("SELECT COUNT(*) as c FROM pemesanan WHERE status='confirmed'")->fetch_assoc()['c'];

// Pemesanan terbaru
$pemesanan_baru = $conn->query("
    SELECT p.kode_booking, p.total_harga, p.status, p.created_at,
           u.nama AS user_nama, f.judul AS film_judul
    FROM pemesanan p
    JOIN users u ON p.user_id = u.id
    JOIN jadwal j ON p.jadwal_id = j.id
    JOIN films f ON j.film_id = f.id
    ORDER BY p.created_at DESC
    LIMIT 8
");

// Film terpopuler
$film_populer = $conn->query("
    SELECT f.judul, f.genre, COUNT(p.id) AS total_pesan
    FROM films f
    LEFT JOIN jadwal j ON f.id = j.film_id
    LEFT JOIN pemesanan p ON j.id = p.jadwal_id AND p.status = 'confirmed'
    GROUP BY f.id
    ORDER BY total_pesan DESC
    LIMIT 5
");

$pageTitle = 'Dashboard';
include 'includes/header.php';
?>

<?php if (isset($_SESSION['flash'])): ?>
<div class="flash flash-<?= $_SESSION['flash']['type'] === 'success' ? 'sukses' : 'error' ?>">
    <i class="fas fa-<?= $_SESSION['flash']['type'] === 'success' ? 'check-circle' : 'exclamation-circle' ?>"></i>
    <?= htmlspecialchars($_SESSION['flash']['msg']) ?>
</div>
<?php unset($_SESSION['flash']); endif; ?>

<!-- Kartu Statistik -->
<div class="grid-4 mb-3">
    <div class="stat-card">
        <div class="stat-icon biru"><i class="fas fa-film text-biru"></i></div>
        <div>
            <div class="stat-num text-biru"><?= $total_film ?></div>
            <div class="stat-label">Total Film</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon hijau"><i class="fas fa-users text-hijau"></i></div>
        <div>
            <div class="stat-num text-hijau"><?= $total_user ?></div>
            <div class="stat-label">Pengguna</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon merah"><i class="fas fa-receipt text-merah"></i></div>
        <div>
            <div class="stat-num text-merah"><?= $total_pesan ?></div>
            <div class="stat-label">Total Pemesanan</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon kuning"><i class="fas fa-money-bill-wave text-kuning"></i></div>
        <div>
            <div class="stat-num text-kuning stat-num-kecil">
                Rp <?= number_format($pendapatan, 0, ',', '.') ?>
            </div>
            <div class="stat-label">Total Pendapatan</div>
        </div>
    </div>
</div>

<!-- Pemesanan Terbaru -->
<div class="box">
    <div class="box-header">
        <h3><i class="fas fa-receipt"></i> Pemesanan Terbaru</h3>
        <a href="transactions.php" class="btn-lihat"><i class="fas fa-eye"></i> Lihat Semua</a>
    </div>
    <div class="tabel-wrap">
        <table class="tabel">
            <thead>
                <tr>
                    <th>Kode Booking</th>
                    <th>Pengguna</th>
                    <th>Film</th>
                    <th>Total</th>
                    <th>Status</th>
                    <th>Tanggal</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($r = $pemesanan_baru->fetch_assoc()): ?>
                <tr>
                    <td class="text-kode"><?= htmlspecialchars($r['kode_booking']) ?></td>
                    <td><?= htmlspecialchars($r['user_nama']) ?></td>
                    <td><?= htmlspecialchars($r['film_judul']) ?></td>
                    <td class="text-kuning text-bold">Rp <?= number_format($r['total_harga'], 0, ',', '.') ?></td>
                    <td><span class="badge-status badge-<?= $r['status'] ?>"><?= ucfirst($r['status']) ?></span></td>
                    <td class="text-abu text-kecil"><?= date('d M Y H:i', strtotime($r['created_at'])) ?></td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Film Populer + Ringkasan -->
<div class="grid-2">

    <div class="box">
        <div class="box-header">
            <h3><i class="fas fa-fire text-merah"></i> Film Terpopuler</h3>
        </div>
        <div class="box-body">
            <?php $no = 1; while ($p = $film_populer->fetch_assoc()): ?>
            <div class="film-rank-item">
                <div class="rank-nomor"><?= $no++ ?></div>
                <div class="rank-info">
                    <div class="rank-judul"><?= htmlspecialchars($p['judul']) ?></div>
                    <div class="rank-genre"><?= htmlspecialchars($p['genre']) ?></div>
                </div>
                <div class="rank-tiket"><?= $p['total_pesan'] ?> <span>tiket</span></div>
            </div>
            <?php endwhile; ?>
        </div>
    </div>

    <div class="box">
        <div class="box-header">
            <h3><i class="fas fa-chart-bar"></i> Ringkasan</h3>
        </div>
        <div class="box-body">
            <?php
            $ringkasan = [
                ['ikon'=>'play-circle',    'warna'=>'hijau',  'label'=>'Film Sedang Tayang',       'nilai'=>$film_tayang],
                ['ikon'=>'calendar-check', 'warna'=>'biru',   'label'=>'Jadwal Aktif (Hari Ini+)', 'nilai'=>$total_jadwal],
                ['ikon'=>'check-circle',   'warna'=>'hijau',  'label'=>'Pemesanan Confirmed',      'nilai'=>$pesan_confirmed],
            ];
            ?>
            <?php foreach ($ringkasan as $item): ?>
            <div class="ringkasan-item">
                <span class="ringkasan-label">
                    <i class="fas fa-<?= $item['ikon'] ?> text-<?= $item['warna'] ?>"></i>
                    <?= $item['label'] ?>
                </span>
                <strong class="ringkasan-nilai text-<?= $item['warna'] ?>"><?= $item['nilai'] ?></strong>
            </div>
            <?php endforeach; ?>
        </div>
    </div>

</div>

<?php include 'includes/footer.php'; ?>
