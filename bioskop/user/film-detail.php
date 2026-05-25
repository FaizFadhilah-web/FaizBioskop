<?php
require_once '../config/database.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$film_id = intval($_GET['id'] ?? 0);
if (!$film_id) { header('Location: films.php'); exit; }

$stmt = $conn->prepare("SELECT * FROM films WHERE id = ?");
$stmt->bind_param("i", $film_id);
$stmt->execute();
$film = $stmt->get_result()->fetch_assoc();
if (!$film) { header('Location: films.php'); exit; }

$jadwal_stmt = $conn->prepare("
    SELECT j.*, s.nama as studio_nama, s.kapasitas,
           (SELECT COUNT(*) FROM kursi_terpesan kt WHERE kt.jadwal_id = j.id) as kursi_terisi
    FROM jadwal j
    JOIN studio s ON j.studio_id = s.id
    WHERE j.film_id = ? AND j.tanggal >= CURDATE()
    ORDER BY j.tanggal, j.jam_tayang
");
$jadwal_stmt->bind_param("i", $film_id);
$jadwal_stmt->execute();
$jadwals = $jadwal_stmt->get_result();

$jadwal_by_date = [];
while ($j = $jadwals->fetch_assoc()) {
    $jadwal_by_date[$j['tanggal']][] = $j;
}

$pageTitle = $film['judul'];
$basePath  = '../';
include '../includes/header.php';
?>

<style>
.film-detail-hero {
    background: linear-gradient(135deg, #0a0a0a 0%, #1a0505 50%, #0a0a0a 100%);
    padding: 3rem 0; border-bottom: 1px solid var(--border);
}
.film-detail-layout {
    display: grid; grid-template-columns: 280px 1fr;
    gap: 2.5rem; align-items: start;
}
.film-poster-big {
    width: 100%; aspect-ratio: 2/3;
    background: linear-gradient(135deg, #1a0505, #2a0a0a);
    border-radius: 12px; display: flex; align-items: center;
    justify-content: center; font-size: 6rem;
    border: 2px solid var(--border); box-shadow: 0 10px 40px rgba(0,0,0,0.5);
    overflow: hidden;
}
.film-poster-big img { width:100%; height:100%; object-fit:cover; display:block; border-radius:10px; }
.film-detail-title { font-size: 2.2rem; font-weight: 900; margin-bottom: 0.5rem; }
.film-detail-meta { display: flex; flex-wrap: wrap; gap: 1rem; margin: 1rem 0; }
.meta-item { display: flex; align-items: center; gap: 6px; font-size: 0.9rem; color: var(--text-gray); }
.meta-item i { color: var(--primary-light); }
.film-sinopsis-full { color: var(--text-light); line-height: 1.8; margin: 1rem 0; }
.jadwal-section { margin-top: 2rem; }
.jadwal-date-group { margin-bottom: 1.5rem; }
.jadwal-date-label {
    font-size: 0.85rem; color: var(--text-gray); font-weight: 600;
    text-transform: uppercase; letter-spacing: 1px; margin-bottom: 0.8rem;
    display: flex; align-items: center; gap: 8px;
}
.jadwal-date-label::before { content: ''; width: 3px; height: 16px; background: var(--primary); border-radius: 2px; }
.jadwal-grid { display: flex; flex-wrap: wrap; gap: 0.8rem; }
.jadwal-btn {
    background: var(--bg-card2); border: 1px solid var(--border);
    border-radius: 10px; padding: 0.8rem 1.2rem; cursor: pointer;
    transition: all 0.3s; text-align: center; min-width: 130px;
    text-decoration: none; display: block;
}
.jadwal-btn:hover { border-color: var(--primary); background: rgba(192,57,43,0.1); }
.jadwal-btn .time { font-size: 1.2rem; font-weight: 800; color: var(--text-white); }
.jadwal-btn .studio { font-size: 0.75rem; color: var(--text-gray); margin-top: 2px; }
.jadwal-btn .seats {
    font-size: 0.75rem; margin-top: 4px; padding: 2px 8px;
    border-radius: 10px; display: inline-block;
}
.seats-ok  { background: rgba(39,174,96,0.2);  color: #5dde8a; }
.seats-low { background: rgba(243,156,18,0.2); color: var(--gold); }
.seats-full{ background: rgba(192,57,43,0.2);  color: #ff6b6b; }
.jadwal-btn.full { opacity: 0.5; cursor: not-allowed; pointer-events: none; }
@media (max-width: 768px) {
    .film-detail-layout { grid-template-columns: 1fr; }
    .film-poster-big { max-width: 200px; margin: 0 auto; }
}
</style>

<div class="film-detail-hero">
    <div class="container">
        <div class="film-detail-layout">
            <div class="film-poster-big">
                <?php
                $poster_path = '../assets/uploads/posters/' . ($film['poster'] ?? '');
                if (!empty($film['poster']) && file_exists($poster_path)): ?>
                <img src="<?= htmlspecialchars($poster_path) ?>" alt="<?= htmlspecialchars($film['judul']) ?>">
                <?php else: ?>🎬<?php endif; ?>
            </div>
            <div>
                <div class="d-flex align-center gap-2 mb-2">
                    <span class="badge badge-primary"><?= $film['status'] === 'tayang' ? 'Sedang Tayang' : 'Akan Tayang' ?></span>
                    <span class="badge badge-gold">⭐ <?= htmlspecialchars($film['rating']) ?></span>
                </div>
                <h1 class="film-detail-title"><?= htmlspecialchars($film['judul']) ?></h1>
                <div class="film-detail-meta">
                    <div class="meta-item"><i class="fas fa-tag"></i> <?= htmlspecialchars($film['genre']) ?></div>
                    <div class="meta-item"><i class="fas fa-clock"></i> <?= $film['durasi'] ?> menit</div>
                    <div class="meta-item"><i class="fas fa-ticket-alt"></i> Rp <?= number_format($film['harga'], 0, ',', '.') ?> / kursi</div>
                </div>
                <p class="film-sinopsis-full"><?= htmlspecialchars($film['sinopsis']) ?></p>

                <div class="jadwal-section">
                    <h3 style="font-size:1.2rem; margin-bottom:1rem; color:var(--primary-light)">
                        <i class="fas fa-calendar-alt"></i> Pilih Jadwal Tayang
                    </h3>
                    <?php if (empty($jadwal_by_date)): ?>
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i> Belum ada jadwal tayang tersedia.
                    </div>
                    <?php else: ?>
                    <?php foreach ($jadwal_by_date as $tanggal => $jadwals_hari): ?>
                    <div class="jadwal-date-group">
                        <div class="jadwal-date-label">
                            <?php
                            $dt       = new DateTime($tanggal);
                            $today    = new DateTime('today');
                            $tomorrow = new DateTime('tomorrow');
                            if ($dt == $today) echo 'Hari Ini — ';
                            elseif ($dt == $tomorrow) echo 'Besok — ';
                            $hari  = ['Minggu','Senin','Selasa','Rabu','Kamis','Jumat','Sabtu'];
                            $bulan = ['','Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'];
                            echo $hari[$dt->format('w')] . ', ' . $dt->format('j') . ' ' . $bulan[(int)$dt->format('n')] . ' ' . $dt->format('Y');
                            ?>
                        </div>
                        <div class="jadwal-grid">
                            <?php foreach ($jadwals_hari as $j):
                                $sisa = $j['kapasitas'] - $j['kursi_terisi'];
                                $full = $sisa <= 0;
                                $low  = $sisa <= 10 && $sisa > 0;
                            ?>
                            <a href="<?= $full ? '#' : 'seat-selection.php?jadwal_id='.$j['id'] ?>"
                               class="jadwal-btn <?= $full ? 'full' : '' ?>">
                                <div class="time"><?= substr($j['jam_tayang'], 0, 5) ?></div>
                                <div class="studio"><?= htmlspecialchars($j['studio_nama']) ?></div>
                                <span class="seats <?= $full ? 'seats-full' : ($low ? 'seats-low' : 'seats-ok') ?>">
                                    <?= $full ? 'Penuh' : ($low ? "$sisa kursi tersisa" : "$sisa kursi") ?>
                                </span>
                            </a>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
