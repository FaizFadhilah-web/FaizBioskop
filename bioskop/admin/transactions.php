<?php
require_once '../config/database.php';
require_once 'includes/auth.php';

$action     = $_GET['action'] ?? 'list';
$id         = intval($_GET['id'] ?? 0);
$detail     = null;
$kursi_list = [];

// ── HAPUS PEMESANAN ───────────────────────────────────────────────────────────
if ($action === 'delete' && $id) {
    // Hapus data terkait terlebih dahulu (kursi_terpesan & detail_pemesanan)
    $del_kt = $conn->prepare("DELETE FROM kursi_terpesan WHERE pemesanan_id = ?");
    $del_kt->bind_param("i", $id);
    $del_kt->execute();

    $del_dp = $conn->prepare("DELETE FROM detail_pemesanan WHERE pemesanan_id = ?");
    $del_dp->bind_param("i", $id);
    $del_dp->execute();

    $del_p = $conn->prepare("DELETE FROM pemesanan WHERE id = ?");
    $del_p->bind_param("i", $id);
    $del_p->execute();

    $_SESSION['flash'] = ['type' => 'success', 'msg' => 'Pemesanan berhasil dihapus.'];
    header('Location: transactions.php');
    exit;
}

// ── UPDATE STATUS ─────────────────────────────────────────────────────────────
if ($action === 'update_status' && $id && isset($_GET['status'])) {
    $new_status = $_GET['status'];
    if (in_array($new_status, ['pending', 'confirmed'])) {
        $stmt = $conn->prepare("UPDATE pemesanan SET status=? WHERE id=?");
        $stmt->bind_param("si", $new_status, $id);
        $stmt->execute();

        if ($new_status === 'confirmed') {
            // Ambil jadwal_id dari pemesanan ini
            $p = $conn->prepare("SELECT jadwal_id FROM pemesanan WHERE id=?");
            $p->bind_param("i", $id);
            $p->execute();
            $jadwal_id_p = $p->get_result()->fetch_assoc()['jadwal_id'];

            // Ambil semua kursi dari detail_pemesanan
            $dp = $conn->prepare("SELECT kursi_id FROM detail_pemesanan WHERE pemesanan_id=?");
            $dp->bind_param("i", $id);
            $dp->execute();
            $kursi_rows = $dp->get_result()->fetch_all(MYSQLI_ASSOC);

            // Insert ke kursi_terpesan
            foreach ($kursi_rows as $row) {
                $ins = $conn->prepare("INSERT IGNORE INTO kursi_terpesan (jadwal_id, kursi_id, pemesanan_id) VALUES (?,?,?)");
                $ins->bind_param("iii", $jadwal_id_p, $row['kursi_id'], $id);
                $ins->execute();
            }
        }

        if ($new_status === 'pending') {
            // Batalkan konfirmasi — bebaskan kursi kembali
            $del = $conn->prepare("DELETE FROM kursi_terpesan WHERE pemesanan_id=?");
            $del->bind_param("i", $id);
            $del->execute();
        }

        $_SESSION['flash'] = ['type' => 'success', 'msg' => 'Status pemesanan berhasil diperbarui.'];
    }
    header('Location: transactions.php');
    exit;
}

// ── DETAIL ────────────────────────────────────────────────────────────────────
if ($action === 'detail' && $id) {
    $s = $conn->prepare("
        SELECT p.*, u.nama AS user_nama, u.email AS user_email, u.no_hp,
               f.judul AS film_judul, f.genre, j.tanggal, j.jam_tayang, st.nama AS studio_nama
        FROM pemesanan p
        JOIN users u ON p.user_id = u.id
        JOIN jadwal j ON p.jadwal_id = j.id
        JOIN films f ON j.film_id = f.id
        JOIN studio st ON j.studio_id = st.id
        WHERE p.id = ?
    ");
    $s->bind_param("i", $id);
    $s->execute();
    $detail = $s->get_result()->fetch_assoc();

    $ks = $conn->prepare("
        SELECT k.kode_kursi
        FROM detail_pemesanan dp
        JOIN kursi k ON dp.kursi_id = k.id
        WHERE dp.pemesanan_id = ?
    ");
    $ks->bind_param("i", $id);
    $ks->execute();
    $kursi_list = $ks->get_result()->fetch_all(MYSQLI_ASSOC);
}

// ── LIST ─────────────────────────────────────────────────────────────────────
$cari   = trim($_GET['search'] ?? '');
$status = $_GET['status'] ?? '';
$tgl    = $_GET['tanggal'] ?? '';
$where  = "WHERE 1=1";
$params = [];
$types  = '';

if ($cari) {
    $where .= " AND (p.kode_booking LIKE ? OR u.nama LIKE ? OR f.judul LIKE ?)";
    $params[] = "%$cari%"; $params[] = "%$cari%"; $params[] = "%$cari%";
    $types .= 'sss';
}
if ($status) { $where .= " AND p.status = ?";          $params[] = $status; $types .= 's'; }
if ($tgl)    { $where .= " AND DATE(p.created_at) = ?"; $params[] = $tgl;    $types .= 's'; }

$query = "
    SELECT p.id, p.kode_booking, p.total_harga, p.status, p.created_at,
           u.nama AS user_nama, f.judul AS film_judul, j.tanggal, j.jam_tayang,
           (SELECT COUNT(*) FROM detail_pemesanan dp WHERE dp.pemesanan_id = p.id) AS jumlah_kursi
    FROM pemesanan p
    JOIN users u ON p.user_id = u.id
    JOIN jadwal j ON p.jadwal_id = j.id
    JOIN films f ON j.film_id = f.id
    $where
    ORDER BY p.created_at DESC
";

if ($params) {
    $stmt = $conn->prepare($query);
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $daftar = $stmt->get_result();
} else {
    $daftar = $conn->query($query);
}

// Ringkasan status
$confirmed = $conn->query("SELECT COUNT(*) as c, COALESCE(SUM(total_harga),0) as s FROM pemesanan WHERE status='confirmed'")->fetch_assoc();
$pending   = $conn->query("SELECT COUNT(*) as c FROM pemesanan WHERE status='pending'")->fetch_assoc()['c'];

$pageTitle = 'Manajemen Transaksi';
include 'includes/header.php';
?>

<?php if (isset($_SESSION['flash'])): ?>
<div class="flash flash-<?= $_SESSION['flash']['type'] === 'success' ? 'sukses' : 'error' ?>">
    <i class="fas fa-<?= $_SESSION['flash']['type'] === 'success' ? 'check-circle' : 'exclamation-circle' ?>"></i>
    <?= htmlspecialchars($_SESSION['flash']['msg']) ?>
</div>
<?php unset($_SESSION['flash']); endif; ?>

<!-- ===== HALAMAN DETAIL ===== -->
<?php if ($action === 'detail' && $detail): ?>
<div class="box">
    <div class="box-header">
        <h3><i class="fas fa-receipt"></i> Detail Pemesanan</h3>
        <a href="transactions.php" class="btn-lihat"><i class="fas fa-arrow-left"></i> Kembali</a>
    </div>
    <div class="box-body">
        <div class="grid-2">

            <!-- Kolom kiri: info pemesanan -->
            <div>
                <div class="seksi-judul">Info Pemesanan</div>
                <div class="detail-row"><span class="detail-key">Kode Booking</span><span class="detail-val text-kode"><?= htmlspecialchars($detail['kode_booking']) ?></span></div>
                <div class="detail-row"><span class="detail-key">Status</span><span class="detail-val"><span class="badge-status badge-<?= $detail['status'] ?>"><?= ucfirst($detail['status']) ?></span></span></div>
                <div class="detail-row"><span class="detail-key">Total Harga</span><span class="detail-val text-kuning text-bold">Rp <?= number_format($detail['total_harga'], 0, ',', '.') ?></span></div>
                <div class="detail-row"><span class="detail-key">Tanggal Pesan</span><span class="detail-val"><?= date('d M Y H:i', strtotime($detail['created_at'])) ?></span></div>
                <div class="detail-row"><span class="detail-key">Kursi</span><span class="detail-val"><?= implode(', ', array_column($kursi_list, 'kode_kursi')) ?: '-' ?></span></div>

                <div class="seksi-judul">Ubah Status</div>
                <div class="status-actions">
                    <?php if ($detail['status'] === 'pending'): ?>
                    <a href="transactions.php?action=update_status&id=<?= $id ?>&status=confirmed"
                       class="btn-lihat" onclick="return confirm('Konfirmasi pemesanan ini?')">
                        <i class="fas fa-check-circle"></i> Konfirmasi Pemesanan
                    </a>
                    <?php elseif ($detail['status'] === 'confirmed'): ?>
                    <a href="transactions.php?action=update_status&id=<?= $id ?>&status=pending"
                       class="btn-edit" onclick="return confirm('Set kembali ke Pending?')">
                        <i class="fas fa-clock"></i> Set ke Pending
                    </a>
                    <?php endif; ?>
                </div>

                <div class="seksi-judul">Hapus Pemesanan</div>
                <a href="transactions.php?action=delete&id=<?= $id ?>"
                   class="btn-hapus"
                   onclick="return confirm('Hapus permanen pemesanan <?= addslashes($detail['kode_booking']) ?>? Tindakan ini tidak dapat dibatalkan.')">
                    <i class="fas fa-trash"></i> Hapus Permanen
                </a>
            </div>

            <!-- Kolom kanan: info pengguna & film -->
            <div>
                <div class="seksi-judul">Info Pengguna</div>
                <div class="detail-row"><span class="detail-key">Nama</span><span class="detail-val"><?= htmlspecialchars($detail['user_nama']) ?></span></div>
                <div class="detail-row"><span class="detail-key">Email</span><span class="detail-val"><?= htmlspecialchars($detail['user_email']) ?></span></div>
                <div class="detail-row"><span class="detail-key">No. HP</span><span class="detail-val"><?= htmlspecialchars($detail['no_hp'] ?? '-') ?></span></div>

                <div class="seksi-judul">Info Film & Jadwal</div>
                <div class="detail-row"><span class="detail-key">Film</span><span class="detail-val text-bold"><?= htmlspecialchars($detail['film_judul']) ?></span></div>
                <div class="detail-row"><span class="detail-key">Genre</span><span class="detail-val"><?= htmlspecialchars($detail['genre']) ?></span></div>
                <div class="detail-row"><span class="detail-key">Studio</span><span class="detail-val"><?= htmlspecialchars($detail['studio_nama']) ?></span></div>
                <div class="detail-row"><span class="detail-key">Tanggal</span><span class="detail-val"><?= date('d M Y', strtotime($detail['tanggal'])) ?></span></div>
                <div class="detail-row"><span class="detail-key">Jam</span><span class="detail-val"><?= substr($detail['jam_tayang'], 0, 5) ?></span></div>
            </div>

        </div>
    </div>
</div>
<?php endif; ?>

<!-- ===== STATISTIK ===== -->
<div class="grid-2 mb-3">
    <div class="stat-card">
        <div class="stat-icon hijau"><i class="fas fa-check-circle text-hijau"></i></div>
        <div>
            <div class="stat-num text-hijau"><?= $confirmed['c'] ?></div>
            <div class="stat-label">Confirmed · Rp <?= number_format($confirmed['s'], 0, ',', '.') ?></div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon kuning"><i class="fas fa-clock text-kuning"></i></div>
        <div>
            <div class="stat-num text-kuning"><?= $pending ?></div>
            <div class="stat-label">Menunggu Konfirmasi</div>
        </div>
    </div>
</div>

<!-- ===== DAFTAR PEMESANAN ===== -->
<div class="box">
    <div class="box-header">
        <h3><i class="fas fa-receipt"></i> Daftar Pemesanan (<?= $daftar->num_rows ?>)</h3>
    </div>
    <div class="filter-bar">
        <form method="GET" class="filter-form">
            <input type="text" name="search" class="form-control filter-input-lg"
                   placeholder="Cari kode, nama, film..." value="<?= htmlspecialchars($cari) ?>">
            <select name="status" class="form-control filter-input-md">
                <option value="">Semua Status</option>
                <option value="confirmed" <?= $status === 'confirmed' ? 'selected' : '' ?>>Confirmed</option>
                <option value="pending"   <?= $status === 'pending'   ? 'selected' : '' ?>>Pending</option>
            </select>
            <input type="date" name="tanggal" class="form-control filter-input-sm"
                   value="<?= htmlspecialchars($tgl) ?>">
            <button type="submit" class="btn btn-primary btn-sm"><i class="fas fa-search"></i> Cari</button>
            <?php if ($cari || $status || $tgl): ?>
            <a href="transactions.php" class="btn btn-outline btn-sm">Reset</a>
            <?php endif; ?>
        </form>
    </div>
    <div class="tabel-wrap">
        <table class="tabel">
            <thead>
                <tr>
                    <th>Kode Booking</th>
                    <th>Pengguna</th>
                    <th>Film</th>
                    <th>Jadwal</th>
                    <th>Kursi</th>
                    <th>Total</th>
                    <th>Status</th>
                    <th>Tanggal Pesan</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($p = $daftar->fetch_assoc()): ?>
                <tr>
                    <td class="text-kode text-kecil"><?= htmlspecialchars($p['kode_booking']) ?></td>
                    <td><?= htmlspecialchars($p['user_nama']) ?></td>
                    <td class="text-bold tabel-judul-film"><?= htmlspecialchars($p['film_judul']) ?></td>
                    <td class="text-abu text-kecil"><?= date('d M Y', strtotime($p['tanggal'])) ?> <?= substr($p['jam_tayang'], 0, 5) ?></td>
                    <td><?= $p['jumlah_kursi'] ?> kursi</td>
                    <td class="text-kuning text-bold">Rp <?= number_format($p['total_harga'], 0, ',', '.') ?></td>
                    <td><span class="badge-status badge-<?= $p['status'] ?>"><?= ucfirst($p['status']) ?></span></td>
                    <td class="text-abu text-kecil"><?= date('d M Y H:i', strtotime($p['created_at'])) ?></td>
                    <td>
                        <div class="aksi-group">
                            <a href="transactions.php?action=detail&id=<?= $p['id'] ?>" class="btn-lihat">
                                <i class="fas fa-eye"></i> Detail
                            </a>
                            <?php if ($p['status'] === 'pending'): ?>
                            <a href="transactions.php?action=update_status&id=<?= $p['id'] ?>&status=confirmed"
                               class="btn-edit" style="background:rgba(39,174,96,0.1); color:#27ae60; border-color:rgba(39,174,96,0.3);"
                               onclick="return confirm('Konfirmasi pemesanan <?= addslashes($p['kode_booking']) ?>?')">
                                <i class="fas fa-check"></i> Konfirmasi
                            </a>
                            <?php endif; ?>
                            <a href="transactions.php?action=delete&id=<?= $p['id'] ?>" class="btn-hapus"
                               onclick="return confirm('Hapus pemesanan <?= addslashes($p['kode_booking']) ?>? Tindakan ini tidak dapat dibatalkan.')">
                                <i class="fas fa-trash"></i> Hapus
                            </a>
                        </div>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
