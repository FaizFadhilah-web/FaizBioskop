<?php
require_once '../config/database.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$jadwal_id = intval($_GET['jadwal_id'] ?? 0);
if (!$jadwal_id) { header('Location: films.php'); exit; }

$stmt = $conn->prepare("
    SELECT j.*, f.judul, f.harga, f.rating, f.durasi, f.genre,
           s.nama as studio_nama, s.kapasitas, s.id as studio_id
    FROM jadwal j
    JOIN films f ON j.film_id = f.id
    JOIN studio s ON j.studio_id = s.id
    WHERE j.id = ?
");
$stmt->bind_param("i", $jadwal_id);
$stmt->execute();
$jadwal = $stmt->get_result()->fetch_assoc();
if (!$jadwal) { header('Location: films.php'); exit; }

$kursi_stmt = $conn->prepare("SELECT * FROM kursi WHERE studio_id = ? ORDER BY baris, nomor");
$kursi_stmt->bind_param("i", $jadwal['studio_id']);
$kursi_stmt->execute();
$all_kursi = $kursi_stmt->get_result()->fetch_all(MYSQLI_ASSOC);

$terpesan_stmt = $conn->prepare("SELECT kursi_id FROM kursi_terpesan WHERE jadwal_id = ?");
$terpesan_stmt->bind_param("i", $jadwal_id);
$terpesan_stmt->execute();
$terpesan_result = $terpesan_stmt->get_result();
$terpesan_ids = [];
while ($t = $terpesan_result->fetch_assoc()) {
    $terpesan_ids[] = $t['kursi_id'];
}

$kursi_by_baris = [];
foreach ($all_kursi as $k) {
    $kursi_by_baris[$k['baris']][] = $k;
}

$pageTitle = 'Pilih Kursi';
$basePath  = '../';
$hari  = ['Minggu','Senin','Selasa','Rabu','Kamis','Jumat','Sabtu'];
$bulan = ['','Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'];
$dt    = new DateTime($jadwal['tanggal']);
$tgl_str = $hari[$dt->format('w')] . ', ' . $dt->format('j') . ' ' . $bulan[(int)$dt->format('n')] . ' ' . $dt->format('Y');

include '../includes/header.php';
?>

<style>
.seat-page { padding: 2rem 0; }
.seat-layout { display: grid; grid-template-columns: 1fr 340px; gap: 2rem; align-items: start; }
.screen-area { text-align: center; margin-bottom: 2rem; }
.screen {
    background: linear-gradient(to bottom, rgba(192,57,43,0.6), rgba(192,57,43,0.1));
    border-radius: 50% 50% 0 0 / 20px 20px 0 0;
    padding: 0.6rem 2rem; display: inline-block; color: var(--text-gray);
    font-size: 0.8rem; letter-spacing: 3px; text-transform: uppercase;
    margin-bottom: 2rem; min-width: 300px; border-top: 3px solid var(--primary);
}
.seat-row { display: flex; align-items: center; justify-content: center; gap: 6px; margin-bottom: 8px; }
.row-label { width: 24px; text-align: center; font-size: 0.8rem; color: var(--text-gray); font-weight: 700; flex-shrink: 0; }
.seat {
    width: 36px; height: 36px; border-radius: 6px 6px 3px 3px;
    border: 1px solid var(--border); background: var(--bg-card2);
    cursor: pointer; transition: all 0.2s; display: flex; align-items: center;
    justify-content: center; font-size: 0.7rem; color: var(--text-gray);
    position: relative; flex-shrink: 0;
}
.seat::before {
    content: ''; position: absolute; bottom: -4px; left: 4px; right: 4px;
    height: 4px; background: inherit; border-radius: 0 0 3px 3px; opacity: 0.5;
}
.seat:hover:not(.taken):not(.selected) { background: rgba(192,57,43,0.3); border-color: var(--primary); transform: scale(1.1); }
.seat.available { background: var(--bg-card2); border-color: #333; }
.seat.selected { background: var(--primary); border-color: var(--primary-light); color: white; transform: scale(1.05); box-shadow: 0 0 10px rgba(192,57,43,0.5); }
.seat.taken { background: #1a1a1a; border-color: #222; cursor: not-allowed; opacity: 0.4; }
.seat-gap { width: 20px; flex-shrink: 0; }
.seat-legend { display: flex; justify-content: center; gap: 1.5rem; margin-top: 1.5rem; flex-wrap: wrap; }
.legend-item { display: flex; align-items: center; gap: 6px; font-size: 0.8rem; color: var(--text-gray); }
.legend-box { width: 20px; height: 20px; border-radius: 4px; border: 1px solid; }
.legend-available { background: var(--bg-card2); border-color: #333; }
.legend-selected { background: var(--primary); border-color: var(--primary-light); }
.legend-taken { background: #1a1a1a; border-color: #222; opacity: 0.4; }
.summary-panel { background: var(--bg-card); border: 1px solid var(--border); border-radius: 12px; overflow: hidden; position: sticky; top: 90px; }
.summary-header { background: linear-gradient(135deg, #1a0505, #0a0a0a); padding: 1.2rem 1.5rem; border-bottom: 1px solid var(--border); }
.summary-header h3 { font-size: 1.1rem; font-weight: 700; }
.summary-body { padding: 1.5rem; }
.summary-row { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 0.8rem; font-size: 0.9rem; }
.summary-row .label { color: var(--text-gray); }
.summary-row .value { font-weight: 600; text-align: right; max-width: 60%; }
.summary-divider { border: none; border-top: 1px solid var(--border); margin: 1rem 0; }
.summary-total { display: flex; justify-content: space-between; align-items: center; font-size: 1.1rem; font-weight: 800; }
.summary-total .total-price { color: var(--primary-light); font-size: 1.3rem; }
.selected-seats-display {
    min-height: 40px; background: var(--bg-input); border: 1px solid var(--border);
    border-radius: 8px; padding: 0.6rem 0.8rem; margin: 0.8rem 0;
    font-size: 0.85rem; color: var(--text-gray); display: flex; flex-wrap: wrap; gap: 4px; align-items: center;
}
.seat-tag { background: var(--primary); color: white; padding: 2px 8px; border-radius: 4px; font-size: 0.8rem; font-weight: 600; }
.max-seats-info { font-size: 0.8rem; color: var(--text-gray); text-align: center; margin-bottom: 0.8rem; }
@media (max-width: 900px) { .seat-layout { grid-template-columns: 1fr; } .summary-panel { position: static; } }
</style>

<div class="seat-page">
    <div class="container">
        <div class="d-flex align-center gap-2 mb-3" style="font-size:0.85rem; color:var(--text-gray)">
            <a href="films.php" style="color:var(--text-gray)">Film</a>
            <i class="fas fa-chevron-right" style="font-size:0.7rem"></i>
            <a href="film-detail.php?id=<?= $jadwal['film_id'] ?>" style="color:var(--text-gray)"><?= htmlspecialchars($jadwal['judul']) ?></a>
            <i class="fas fa-chevron-right" style="font-size:0.7rem"></i>
            <span style="color:var(--primary-light)">Pilih Kursi</span>
        </div>

        <div class="seat-layout">
            <div>
                <div class="card mb-3">
                    <div class="card-header">
                        <i class="fas fa-couch" style="color:var(--primary-light)"></i>
                        Pilih Kursi — <?= htmlspecialchars($jadwal['studio_nama']) ?>
                    </div>
                    <div class="card-body">
                        <div class="screen-area"><div class="screen">🎬 LAYAR</div></div>
                        <?php foreach ($kursi_by_baris as $baris => $kursis): ?>
                        <div class="seat-row">
                            <div class="row-label"><?= $baris ?></div>
                            <?php
                            $mid = ceil(count($kursis) / 2);
                            foreach ($kursis as $idx => $k):
                                $is_taken = in_array($k['id'], $terpesan_ids);
                                $class = $is_taken ? 'taken' : 'available';
                                if ($idx === $mid): ?><div class="seat-gap"></div><?php endif; ?>
                                <div class="seat <?= $class ?>"
                                     data-id="<?= $k['id'] ?>"
                                     data-kode="<?= htmlspecialchars($k['kode_kursi']) ?>"
                                     <?= $is_taken ? '' : 'onclick="toggleSeat(this)"' ?>
                                     title="Kursi <?= htmlspecialchars($k['kode_kursi']) ?>">
                                    <?= htmlspecialchars($k['kode_kursi']) ?>
                                </div>
                            <?php endforeach; ?>
                            <div class="row-label"><?= $baris ?></div>
                        </div>
                        <?php endforeach; ?>
                        <div class="seat-legend">
                            <div class="legend-item"><div class="legend-box legend-available"></div> Tersedia</div>
                            <div class="legend-item"><div class="legend-box legend-selected"></div> Dipilih</div>
                            <div class="legend-item"><div class="legend-box legend-taken"></div> Terisi</div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="summary-panel">
                <div class="summary-header">
                    <h3><i class="fas fa-receipt" style="color:var(--primary-light)"></i> Ringkasan Pesanan</h3>
                </div>
                <div class="summary-body">
                    <div class="summary-row"><span class="label">Film</span><span class="value"><?= htmlspecialchars($jadwal['judul']) ?></span></div>
                    <div class="summary-row"><span class="label">Tanggal</span><span class="value"><?= $tgl_str ?></span></div>
                    <div class="summary-row"><span class="label">Jam</span><span class="value"><?= substr($jadwal['jam_tayang'], 0, 5) ?> WIB</span></div>
                    <div class="summary-row"><span class="label">Studio</span><span class="value"><?= htmlspecialchars($jadwal['studio_nama']) ?></span></div>
                    <div class="summary-row"><span class="label">Harga/Kursi</span><span class="value" style="color:var(--primary-light)">Rp <?= number_format($jadwal['harga'], 0, ',', '.') ?></span></div>
                    <hr class="summary-divider">
                    <div style="font-size:0.85rem; color:var(--text-gray); margin-bottom:0.5rem">Kursi Dipilih:</div>
                    <div class="selected-seats-display" id="selectedSeatsDisplay">
                        <span id="noSeatMsg" style="color:#555">Belum ada kursi dipilih</span>
                    </div>
                    <div class="max-seats-info">Maksimal 8 kursi per pemesanan</div>
                    <hr class="summary-divider">
                    <div class="summary-total">
                        <span>Total</span>
                        <span class="total-price" id="totalPrice">Rp 0</span>
                    </div>
                    <form method="POST" action="booking.php" id="bookingForm">
                        <input type="hidden" name="jadwal_id" value="<?= $jadwal_id ?>">
                        <input type="hidden" name="kursi_ids" id="kursiIdsInput" value="">
                        <input type="hidden" name="total_harga" id="totalHargaInput" value="0">
                        <button type="submit" class="btn btn-primary btn-block btn-lg mt-3" id="bookBtn" disabled>
                            <i class="fas fa-ticket-alt"></i> Pesan Sekarang
                        </button>
                    </form>
                    <p class="text-center text-sm text-gray mt-2">
                        <i class="fas fa-shield-alt" style="color:var(--success)"></i> Pemesanan aman & terjamin
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
const selectedSeats = {};
const hargaPerKursi = <?= $jadwal['harga'] ?>;
const maxSeats = 8;

function toggleSeat(el) {
    const id = el.dataset.id, kode = el.dataset.kode;
    if (el.classList.contains('selected')) {
        el.classList.remove('selected'); el.classList.add('available');
        delete selectedSeats[id];
    } else {
        if (Object.keys(selectedSeats).length >= maxSeats) { alert('Maksimal ' + maxSeats + ' kursi!'); return; }
        el.classList.remove('available'); el.classList.add('selected');
        selectedSeats[id] = kode;
    }
    updateSummary();
}

function updateSummary() {
    const ids = Object.keys(selectedSeats), kodes = Object.values(selectedSeats);
    const total = ids.length * hargaPerKursi;
    const display = document.getElementById('selectedSeatsDisplay');
    display.innerHTML = '';
    if (!kodes.length) {
        display.innerHTML = '<span style="color:#555">Belum ada kursi dipilih</span>';
        document.getElementById('bookBtn').disabled = true;
    } else {
        kodes.forEach(k => { const t = document.createElement('span'); t.className = 'seat-tag'; t.textContent = k; display.appendChild(t); });
        document.getElementById('bookBtn').disabled = false;
    }
    document.getElementById('totalPrice').textContent = 'Rp ' + total.toLocaleString('id-ID');
    document.getElementById('kursiIdsInput').value = ids.join(',');
    document.getElementById('totalHargaInput').value = total;
}

document.getElementById('bookingForm').addEventListener('submit', function(e) {
    if (!document.getElementById('kursiIdsInput').value) { e.preventDefault(); alert('Pilih minimal 1 kursi!'); }
});
</script>

<?php include '../includes/footer.php'; ?>
