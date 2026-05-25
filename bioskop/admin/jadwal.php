<?php
require_once '../config/database.php';
require_once 'includes/auth.php';

$action = $_GET['action'] ?? 'list';
$id     = intval($_GET['id'] ?? 0);
$error  = '';

// ── HAPUS ─────────────────────────────────────────────────────────────────────
if ($action === 'delete' && $id) {
    $cek = $conn->prepare("SELECT COUNT(*) as c FROM pemesanan WHERE jadwal_id = ?");
    $cek->bind_param("i", $id);
    $cek->execute();
    $ada_pesan = $cek->get_result()->fetch_assoc()['c'];

    if ($ada_pesan > 0) {
        $_SESSION['flash'] = ['type' => 'error', 'msg' => 'Jadwal tidak bisa dihapus karena sudah ada pemesanan.'];
    } else {
        $conn->query("DELETE FROM kursi_terpesan WHERE jadwal_id = $id");
        $stmt = $conn->prepare("DELETE FROM jadwal WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $_SESSION['flash'] = ['type' => 'success', 'msg' => 'Jadwal berhasil dihapus.'];
    }
    header('Location: jadwal.php');
    exit;
}

// ── SIMPAN ────────────────────────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $film_id   = intval($_POST['film_id'] ?? 0);
    $studio_id = intval($_POST['studio_id'] ?? 0);
    $tanggal   = trim($_POST['tanggal'] ?? '');
    $jam       = trim($_POST['jam_tayang'] ?? '');
    $edit_id   = intval($_POST['edit_id'] ?? 0);

    if (!$film_id || !$studio_id || !$tanggal || !$jam) {
        $error  = 'Semua field wajib diisi.';
        $action = $edit_id ? 'edit' : 'add';
    } else {
        if ($edit_id) {
            $stmt = $conn->prepare("UPDATE jadwal SET film_id=?, studio_id=?, tanggal=?, jam_tayang=? WHERE id=?");
            $stmt->bind_param("iissi", $film_id, $studio_id, $tanggal, $jam, $edit_id);
        } else {
            $stmt = $conn->prepare("INSERT INTO jadwal (film_id, studio_id, tanggal, jam_tayang) VALUES (?,?,?,?)");
            $stmt->bind_param("iiss", $film_id, $studio_id, $tanggal, $jam);
        }
        $stmt->execute();
        $_SESSION['flash'] = ['type' => 'success', 'msg' => $edit_id ? 'Jadwal berhasil diperbarui.' : 'Jadwal berhasil ditambahkan.'];
        header('Location: jadwal.php');
        exit;
    }
}

// ── AMBIL DATA UNTUK FORM EDIT ────────────────────────────────────────────────
$jadwal_edit = null;
if ($action === 'edit' && $id) {
    $s = $conn->prepare("SELECT * FROM jadwal WHERE id = ?");
    $s->bind_param("i", $id);
    $s->execute();
    $jadwal_edit = $s->get_result()->fetch_assoc();
    if (!$jadwal_edit) { header('Location: jadwal.php'); exit; }
}

// Data dropdown
$semua_film   = $conn->query("SELECT id, judul, poster FROM films WHERE status IN ('tayang','akan_tayang') ORDER BY judul");
$semua_studio = $conn->query("SELECT id, nama FROM studio ORDER BY nama");

// ── DAFTAR JADWAL ─────────────────────────────────────────────────────────────
$filter_tgl = $_GET['tanggal'] ?? '';
$where      = $filter_tgl ? "WHERE j.tanggal = '$filter_tgl'" : "WHERE 1=1";

$daftar_jadwal = $conn->query("
    SELECT j.*, f.judul AS film_judul, f.poster AS film_poster, s.nama AS studio_nama,
           (SELECT COUNT(*) FROM pemesanan p WHERE p.jadwal_id = j.id AND p.status = 'confirmed') AS total_pesan
    FROM jadwal j
    JOIN films f ON j.film_id = f.id
    JOIN studio s ON j.studio_id = s.id
    $where
    ORDER BY j.tanggal DESC, j.jam_tayang ASC
");

$pageTitle = 'Jadwal Tayang';
include 'includes/header.php';
?>

<?php if (isset($_SESSION['flash'])): ?>
<div class="flash flash-<?= $_SESSION['flash']['type'] === 'success' ? 'sukses' : 'error' ?>">
    <i class="fas fa-<?= $_SESSION['flash']['type'] === 'success' ? 'check-circle' : 'exclamation-circle' ?>"></i>
    <?= htmlspecialchars($_SESSION['flash']['msg']) ?>
</div>
<?php unset($_SESSION['flash']); endif; ?>

<?php if ($error): ?>
<div class="flash flash-error"><i class="fas fa-exclamation-circle"></i> <?= htmlspecialchars($error) ?></div>
<?php endif; ?>

<!-- Form Tambah -->
<?php if ($action === 'add'): ?>
<div class="box">
    <div class="box-header">
        <h3><i class="fas fa-plus"></i> Tambah Jadwal Baru</h3>
        <a href="jadwal.php" class="btn-lihat"><i class="fas fa-arrow-left"></i> Kembali</a>
    </div>
    <div class="box-body">
        <form method="POST">
            <div class="form-2col">
                <div class="form-group">
                    <label class="form-label">Film *</label>
                    <input type="hidden" name="film_id" id="film_id_input" value="" required>
                    <div class="film-picker-grid" id="filmPickerGrid">
                        <?php
                        $semua_film->data_seek(0);
                        while ($f = $semua_film->fetch_assoc()):
                            $poster_path = '../assets/uploads/posters/' . ($f['poster'] ?? '');
                        ?>
                        <div class="film-picker-item"
                             data-id="<?= $f['id'] ?>"
                             onclick="pilihFilm(this, <?= $f['id'] ?>)">
                            <div class="film-picker-poster">
                                <?php if (!empty($f['poster']) && file_exists($poster_path)): ?>
                                <img src="<?= htmlspecialchars($poster_path) ?>" alt="<?= htmlspecialchars($f['judul']) ?>">
                                <?php else: ?>
                                <span>🎬</span>
                                <?php endif; ?>
                            </div>
                            <div class="film-picker-judul"><?= htmlspecialchars($f['judul']) ?></div>
                        </div>
                        <?php endwhile; ?>
                    </div>
                    <p class="form-hint" style="margin-top:0.4rem;">Klik poster untuk memilih film</p>
                </div>
                <div class="form-group">
                    <label class="form-label">Studio *</label>
                    <select name="studio_id" class="form-control" required>
                        <option value="">-- Pilih Studio --</option>
                        <?php
                        $semua_studio->data_seek(0);
                        while ($s = $semua_studio->fetch_assoc()):
                        ?>
                        <option value="<?= $s['id'] ?>"><?= htmlspecialchars($s['nama']) ?></option>
                        <?php endwhile; ?>
                    </select>
                </div>
            </div>
            <div class="form-2col">
                <div class="form-group">
                    <label class="form-label">Tanggal *</label>
                    <input type="date" name="tanggal" class="form-control" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Jam Tayang *</label>
                    <input type="time" name="jam_tayang" class="form-control" required>
                </div>
            </div>
            <div class="btn-group-form">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Tambah Jadwal
                </button>
                <a href="jadwal.php" class="btn btn-outline">Batal</a>
            </div>
        </form>
    </div>
</div>
<?php endif; ?>

<!-- Form Edit — simpel, 1 jadwal -->
<?php if ($action === 'edit' && $jadwal_edit): ?>
<?php
// Ambil nama film dan studio untuk ditampilkan
$info_film   = $conn->query("SELECT judul, poster FROM films WHERE id = " . intval($jadwal_edit['film_id']))->fetch_assoc();
$info_studio = $conn->query("SELECT nama FROM studio WHERE id = " . intval($jadwal_edit['studio_id']))->fetch_assoc();
$poster_edit = '../assets/uploads/posters/' . ($info_film['poster'] ?? '');
?>
<div class="box">
    <div class="box-header">
        <h3><i class="fas fa-edit"></i> Edit Jadwal</h3>
        <a href="jadwal.php" class="btn-lihat"><i class="fas fa-arrow-left"></i> Kembali</a>
    </div>
    <div class="box-body">

        <!-- Info film yang diedit (read-only) -->
        <div class="edit-jadwal-film-info">
            <div class="edit-jadwal-poster">
                <?php if (!empty($info_film['poster']) && file_exists($poster_edit)): ?>
                <img src="<?= htmlspecialchars($poster_edit) ?>" alt="<?= htmlspecialchars($info_film['judul']) ?>">
                <?php else: ?>
                <span>🎬</span>
                <?php endif; ?>
            </div>
            <div>
                <div style="font-size:1.1rem; font-weight:800; margin-bottom:0.3rem;">
                    <?= htmlspecialchars($info_film['judul']) ?>
                </div>
                <div style="font-size:0.82rem; color:#888;">
                    <i class="fas fa-hashtag"></i> ID Jadwal: <strong style="color:#aaa"><?= $jadwal_edit['id'] ?></strong>
                    &nbsp;&nbsp;
                    <i class="fas fa-building"></i> Studio saat ini: <strong style="color:#aaa"><?= htmlspecialchars($info_studio['nama']) ?></strong>
                </div>
                <div style="font-size:0.8rem; color:#666; margin-top:0.3rem;">
                    <i class="fas fa-info-circle"></i> Film tidak bisa diubah. Hapus jadwal ini dan buat baru jika ingin mengganti film.
                </div>
            </div>
        </div>

        <form method="POST">
            <input type="hidden" name="edit_id"  value="<?= $jadwal_edit['id'] ?>">
            <input type="hidden" name="film_id"  value="<?= $jadwal_edit['film_id'] ?>">

            <div class="form-3col">
                <div class="form-group">
                    <label class="form-label">Studio *</label>
                    <select name="studio_id" class="form-control" required>
                        <?php
                        $semua_studio->data_seek(0);
                        while ($s = $semua_studio->fetch_assoc()):
                        ?>
                        <option value="<?= $s['id'] ?>" <?= $jadwal_edit['studio_id'] == $s['id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($s['nama']) ?>
                        </option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Tanggal *</label>
                    <input type="date" name="tanggal" class="form-control" required
                           value="<?= htmlspecialchars($jadwal_edit['tanggal']) ?>">
                </div>
                <div class="form-group">
                    <label class="form-label">Jam Tayang *</label>
                    <input type="time" name="jam_tayang" class="form-control" required
                           value="<?= htmlspecialchars(substr($jadwal_edit['jam_tayang'], 0, 5)) ?>">
                </div>
            </div>

            <div class="btn-group-form">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Simpan Perubahan
                </button>
                <a href="jadwal.php" class="btn btn-outline">Batal</a>
            </div>
        </form>
    </div>
</div>

<style>
.edit-jadwal-film-info {
    display: flex;
    align-items: center;
    gap: 1.2rem;
    background: #111;
    border: 1px solid #2a2a2a;
    border-radius: 10px;
    padding: 1rem 1.2rem;
    margin-bottom: 1.5rem;
}
.edit-jadwal-poster {
    width: 60px;
    height: 85px;
    border-radius: 6px;
    overflow: hidden;
    background: #1a1a1a;
    border: 1px solid #333;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.8rem;
    flex-shrink: 0;
}
.edit-jadwal-poster img { width:100%; height:100%; object-fit:cover; display:block; }
</style>
<?php endif; ?>

<!-- Daftar Jadwal -->
<div class="box">
    <div class="box-header">
        <h3><i class="fas fa-calendar-alt"></i> Daftar Jadwal (<?= $daftar_jadwal->num_rows ?>)</h3>
        <a href="jadwal.php?action=add" class="btn btn-primary btn-sm">
            <i class="fas fa-plus"></i> Tambah Jadwal
        </a>
    </div>

    <!-- Filter tanggal -->
    <div class="filter-bar">
        <form method="GET" class="filter-form">
            <label class="form-label filter-label">Filter Tanggal:</label>
            <input type="date" name="tanggal" class="form-control filter-input-md"
                   value="<?= htmlspecialchars($filter_tgl) ?>">
            <button type="submit" class="btn btn-primary btn-sm"><i class="fas fa-filter"></i> Filter</button>
            <?php if ($filter_tgl): ?>
            <a href="jadwal.php" class="btn btn-outline btn-sm">Reset</a>
            <?php endif; ?>
        </form>
    </div>

    <div class="tabel-wrap">
        <table class="tabel">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Film</th>
                    <th>Studio</th>
                    <th>Tanggal</th>
                    <th>Jam</th>
                    <th>Tiket Terjual</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php $no = 1; while ($j = $daftar_jadwal->fetch_assoc()):
                    $poster_path = '../assets/uploads/posters/' . ($j['film_poster'] ?? '');
                ?>
                <tr>
                    <td class="text-abu"><?= $no++ ?></td>
                    <td>
                        <div style="display:flex; align-items:center; gap:0.6rem;">
                            <div style="width:32px; height:46px; border-radius:4px; overflow:hidden;
                                        background:#1a1a1a; border:1px solid #2a2a2a;
                                        display:flex; align-items:center; justify-content:center;
                                        font-size:1rem; flex-shrink:0;">
                                <?php if (!empty($j['film_poster']) && file_exists($poster_path)): ?>
                                <img src="<?= htmlspecialchars($poster_path) ?>"
                                     alt="" style="width:100%; height:100%; object-fit:cover; display:block;">
                                <?php else: ?>
                                🎬
                                <?php endif; ?>
                            </div>
                            <span class="text-bold"><?= htmlspecialchars($j['film_judul']) ?></span>
                        </div>
                    </td>
                    <td><?= htmlspecialchars($j['studio_nama']) ?></td>
                    <td><?= date('d M Y', strtotime($j['tanggal'])) ?></td>
                    <td>
                        <span class="badge badge-primary"><?= substr($j['jam_tayang'], 0, 5) ?></span>
                    </td>
                    <td class="<?= $j['total_pesan'] > 0 ? 'text-hijau' : 'text-abu' ?> text-bold">
                        <?= $j['total_pesan'] ?> tiket
                    </td>
                    <td>
                        <div class="aksi-group">
                            <a href="jadwal.php?action=edit&id=<?= $j['id'] ?>" class="btn-edit">
                                <i class="fas fa-edit"></i> Edit
                            </a>
                            <a href="jadwal.php?action=delete&id=<?= $j['id'] ?>" class="btn-hapus"
                               onclick="return confirm('Hapus jadwal ini?')">
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

<style>
.film-picker-grid {
    display: flex;
    flex-wrap: wrap;
    gap: 0.6rem;
    max-height: 280px;
    overflow-y: auto;
    padding: 0.5rem;
    background: #111;
    border: 1px solid #2a2a2a;
    border-radius: 8px;
}
.film-picker-item {
    width: 80px;
    cursor: pointer;
    border-radius: 6px;
    border: 2px solid transparent;
    transition: all 0.2s;
    overflow: hidden;
    background: #1a1a1a;
}
.film-picker-item:hover { border-color: #555; }
.film-picker-item.selected { border-color: #c0392b; }
.film-picker-poster {
    width: 100%;
    height: 110px;
    background: #222;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.8rem;
    position: relative;
    overflow: hidden;
}
.film-picker-poster img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    display: block;
}
.film-picker-check {
    position: absolute;
    top: 4px;
    right: 4px;
    color: #c0392b;
    font-size: 1.1rem;
    background: rgba(0,0,0,0.7);
    border-radius: 50%;
    line-height: 1;
}
.film-picker-judul {
    font-size: 0.68rem;
    text-align: center;
    padding: 4px 4px 5px;
    color: #ccc;
    line-height: 1.3;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}
.film-picker-item.selected .film-picker-judul { color: #fff; font-weight: 700; }
</style>

<script>
function pilihFilm(el, id) {
    // Hapus selected dari semua item
    document.querySelectorAll('.film-picker-item').forEach(function(item) {
        item.classList.remove('selected');
        var chk = item.querySelector('.film-picker-check');
        if (chk) chk.remove();
    });
    // Set selected ke item yang diklik
    el.classList.add('selected');
    var posterDiv = el.querySelector('.film-picker-poster');
    if (!posterDiv.querySelector('.film-picker-check')) {
        var chk = document.createElement('div');
        chk.className = 'film-picker-check';
        chk.innerHTML = '<i class="fas fa-check-circle"></i>';
        posterDiv.appendChild(chk);
    }
    // Set nilai hidden input
    document.getElementById('film_id_input').value = id;
}
</script>
