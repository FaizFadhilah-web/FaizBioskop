<?php
require_once '../config/database.php';
require_once 'includes/auth.php';

$action  = $_GET['action'] ?? 'list';
$id      = intval($_GET['id'] ?? 0);
$error   = '';

// Folder untuk menyimpan poster
define('POSTER_DIR', '../assets/uploads/posters/');
if (!is_dir(POSTER_DIR)) {
    mkdir(POSTER_DIR, 0755, true);
}

// ── HAPUS ────────────────────────────────────────────────────────────────────
if ($action === 'delete' && $id) {
    // Cek apakah film masih punya jadwal
    $cek = $conn->prepare("SELECT COUNT(*) as c FROM jadwal WHERE film_id = ?");
    $cek->bind_param("i", $id);
    $cek->execute();
    $ada_jadwal = $cek->get_result()->fetch_assoc()['c'];

    if ($ada_jadwal > 0) {
        $_SESSION['flash'] = ['type' => 'error', 'msg' => 'Film tidak bisa dihapus karena masih memiliki jadwal tayang.'];
    } else {
        // Hapus poster jika ada
        $cek_poster = $conn->prepare("SELECT poster FROM films WHERE id = ?");
        $cek_poster->bind_param("i", $id);
        $cek_poster->execute();
        $poster_lama = $cek_poster->get_result()->fetch_assoc()['poster'] ?? '';
        if ($poster_lama && file_exists(POSTER_DIR . $poster_lama)) {
            unlink(POSTER_DIR . $poster_lama);
        }

        $stmt = $conn->prepare("DELETE FROM films WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $_SESSION['flash'] = ['type' => 'success', 'msg' => 'Film berhasil dihapus.'];
    }
    header('Location: films.php');
    exit;
}

// ── SIMPAN (TAMBAH / EDIT) ────────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $judul    = trim($_POST['judul'] ?? '');
    $genre    = trim($_POST['genre'] ?? '');
    $durasi   = intval($_POST['durasi'] ?? 0);
    $rating   = trim($_POST['rating'] ?? '');
    $sinopsis = trim($_POST['sinopsis'] ?? '');
    $harga    = floatval($_POST['harga'] ?? 0);
    $status   = $_POST['status'] ?? 'tayang';
    $edit_id  = intval($_POST['edit_id'] ?? 0);

    if (!$judul || !$genre || !$durasi || !$harga) {
        $error  = 'Judul, genre, durasi, dan harga wajib diisi.';
        $action = $edit_id ? 'edit' : 'add';
    } else {
        // ── Proses Upload Foto ──────────────────────────────────────────────
        $poster_baru = '';
        if (!empty($_FILES['poster']['name'])) {
            $file      = $_FILES['poster'];
            $ext       = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            $allowed   = ['jpg', 'jpeg', 'png', 'webp'];
            $max_size  = 2 * 1024 * 1024; // 2 MB

            if (!in_array($ext, $allowed)) {
                $error  = 'Format foto tidak didukung. Gunakan JPG, PNG, atau WEBP.';
                $action = $edit_id ? 'edit' : 'add';
            } elseif ($file['size'] > $max_size) {
                $error  = 'Ukuran foto maksimal 2 MB.';
                $action = $edit_id ? 'edit' : 'add';
            } else {
                $nama_file = 'poster_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
                if (!move_uploaded_file($file['tmp_name'], POSTER_DIR . $nama_file)) {
                    $error  = 'Gagal mengupload foto. Coba lagi.';
                    $action = $edit_id ? 'edit' : 'add';
                } else {
                    $poster_baru = $nama_file;
                }
            }
        }

        if (!$error) {
            if ($edit_id) {
                // Ambil poster lama
                $s = $conn->prepare("SELECT poster FROM films WHERE id = ?");
                $s->bind_param("i", $edit_id);
                $s->execute();
                $poster_lama = $s->get_result()->fetch_assoc()['poster'] ?? '';

                if ($poster_baru) {
                    // Hapus poster lama jika ada yang baru
                    if ($poster_lama && file_exists(POSTER_DIR . $poster_lama)) {
                        unlink(POSTER_DIR . $poster_lama);
                    }
                    $stmt = $conn->prepare("UPDATE films SET judul=?, genre=?, durasi=?, rating=?, sinopsis=?, harga=?, status=?, poster=? WHERE id=?");
                    $stmt->bind_param("ssissdssi", $judul, $genre, $durasi, $rating, $sinopsis, $harga, $status, $poster_baru, $edit_id);
                } else {
                    // Tidak ada foto baru, pertahankan poster lama
                    $stmt = $conn->prepare("UPDATE films SET judul=?, genre=?, durasi=?, rating=?, sinopsis=?, harga=?, status=? WHERE id=?");
                    $stmt->bind_param("ssissdsi", $judul, $genre, $durasi, $rating, $sinopsis, $harga, $status, $edit_id);
                }
            } else {
                // Tambah film baru
                $stmt = $conn->prepare("INSERT INTO films (judul, genre, durasi, rating, sinopsis, harga, status, poster) VALUES (?,?,?,?,?,?,?,?)");
                $stmt->bind_param("ssissdss", $judul, $genre, $durasi, $rating, $sinopsis, $harga, $status, $poster_baru);
            }
            $stmt->execute();
            $_SESSION['flash'] = ['type' => 'success', 'msg' => $edit_id ? 'Film berhasil diperbarui.' : 'Film berhasil ditambahkan.'];
            header('Location: films.php');
            exit;
        }
    }
}

// ── AMBIL DATA UNTUK FORM EDIT ────────────────────────────────────────────────
$film_edit = null;
if ($action === 'edit' && $id) {
    $s = $conn->prepare("SELECT * FROM films WHERE id = ?");
    $s->bind_param("i", $id);
    $s->execute();
    $film_edit = $s->get_result()->fetch_assoc();
    if (!$film_edit) { header('Location: films.php'); exit; }
}

// ── DAFTAR FILM ───────────────────────────────────────────────────────────────
$cari          = trim($_GET['search'] ?? '');
$filter_status = $_GET['status'] ?? '';
$where         = "WHERE 1=1";
$params        = [];
$types         = '';

if ($cari)          { $where .= " AND judul LIKE ?"; $params[] = "%$cari%"; $types .= 's'; }
if ($filter_status) { $where .= " AND status = ?";   $params[] = $filter_status; $types .= 's'; }

if ($params) {
    $stmt = $conn->prepare("SELECT * FROM films $where ORDER BY id DESC");
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $daftar_film = $stmt->get_result();
} else {
    $daftar_film = $conn->query("SELECT * FROM films $where ORDER BY id DESC");
}

$pageTitle = 'Manajemen Film';
include 'includes/header.php';
?>

<?php if (isset($_SESSION['flash'])): ?>
<div class="flash flash-<?= $_SESSION['flash']['type'] === 'success' ? 'sukses' : 'error' ?>">
    <i class="fas fa-<?= $_SESSION['flash']['type'] === 'success' ? 'check-circle' : 'exclamation-circle' ?>"></i>
    <?= htmlspecialchars($_SESSION['flash']['msg']) ?>
</div>
<?php unset($_SESSION['flash']); endif; ?>

<?php if ($error): ?>
<div class="flash flash-error">
    <i class="fas fa-exclamation-circle"></i> <?= htmlspecialchars($error) ?>
</div>
<?php endif; ?>

<!-- Form Tambah / Edit -->
<?php if ($action === 'add' || $action === 'edit'): ?>
<div class="box">
    <div class="box-header">
        <h3>
            <i class="fas fa-<?= $action === 'edit' ? 'edit' : 'plus' ?>"></i>
            <?= $action === 'edit' ? 'Edit Film' : 'Tambah Film Baru' ?>
        </h3>
        <a href="films.php" class="btn-lihat"><i class="fas fa-arrow-left"></i> Kembali</a>
    </div>
    <div class="box-body">
        <form method="POST" enctype="multipart/form-data">
            <?php if ($action === 'edit'): ?>
            <input type="hidden" name="edit_id" value="<?= $film_edit['id'] ?>">
            <?php endif; ?>

            <div class="form-2col">
                <div class="form-group">
                    <label class="form-label">Judul Film *</label>
                    <input type="text" name="judul" class="form-control" required
                           value="<?= htmlspecialchars($film_edit['judul'] ?? $_POST['judul'] ?? '') ?>">
                </div>
                <div class="form-group">
                    <label class="form-label">Genre *</label>
                    <select name="genre" class="form-control" required>
                        <option value="">-- Pilih Genre --</option>
                        <?php foreach (['Action', 'Drama', 'Romantis', 'Horor', 'Komedi'] as $g): ?>
                        <option value="<?= $g ?>" <?= ($film_edit['genre'] ?? $_POST['genre'] ?? '') === $g ? 'selected' : '' ?>>
                            <?= $g ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div class="form-3col">
                <div class="form-group">
                    <label class="form-label">Durasi (menit) *</label>
                    <input type="number" name="durasi" class="form-control" min="1" required
                           value="<?= htmlspecialchars($film_edit['durasi'] ?? $_POST['durasi'] ?? '') ?>">
                </div>
                <div class="form-group">
                    <label class="form-label">Rating</label>
                    <select name="rating" class="form-control">
                        <?php foreach (['SU', '13+', '17+', '21+'] as $r): ?>
                        <option value="<?= $r ?>" <?= ($film_edit['rating'] ?? '') === $r ? 'selected' : '' ?>>
                            <?= $r ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Harga Tiket (Rp) *</label>
                    <input type="number" name="harga" class="form-control" min="0" step="1000" required
                           value="<?= htmlspecialchars($film_edit['harga'] ?? $_POST['harga'] ?? '') ?>">
                </div>
            </div>

            <div class="form-group">
                <label class="form-label">Sinopsis</label>
                <textarea name="sinopsis" class="form-control"><?= htmlspecialchars($film_edit['sinopsis'] ?? $_POST['sinopsis'] ?? '') ?></textarea>
            </div>

            <!-- Upload Poster -->
            <div class="form-group">
                <label class="form-label">
                    <i class="fas fa-image"></i> Foto Poster
                    <?= $action === 'edit' ? '(kosongkan jika tidak ingin mengubah)' : '' ?>
                </label>
                <div class="poster-upload-wrap">
                    <!-- Preview -->
                    <div class="poster-preview-box" id="previewBox">
                        <?php if ($action === 'edit' && !empty($film_edit['poster']) && file_exists('../assets/uploads/posters/' . $film_edit['poster'])): ?>
                            <img id="posterPreview"
                                 src="../assets/uploads/posters/<?= htmlspecialchars($film_edit['poster']) ?>"
                                 alt="Poster saat ini">
                            <span class="poster-preview-label">Poster saat ini</span>
                        <?php else: ?>
                            <img id="posterPreview" src="" alt="" style="display:none">
                            <div id="previewPlaceholder" class="poster-placeholder">
                                <i class="fas fa-image"></i>
                                <span>Preview foto</span>
                            </div>
                        <?php endif; ?>
                    </div>
                    <!-- Input -->
                    <div class="poster-upload-right">
                        <label for="posterInput" class="btn btn-outline btn-sm poster-upload-btn">
                            <i class="fas fa-upload"></i> Pilih Foto
                        </label>
                        <input type="file" id="posterInput" name="poster"
                               accept="image/jpeg,image/png,image/webp"
                               style="display:none"
                               onchange="previewPoster(this)">
                        <p class="form-hint">Format: JPG, PNG, WEBP. Maks. 2 MB.</p>
                        <p class="form-hint" id="namaFile" style="color:#aaa"></p>
                    </div>
                </div>
            </div>

            <div class="form-group form-group-narrow">
                <label class="form-label">Status</label>
                <select name="status" class="form-control">
                    <option value="tayang"      <?= ($film_edit['status'] ?? 'tayang') === 'tayang'      ? 'selected' : '' ?>>Sedang Tayang</option>
                    <option value="akan_tayang" <?= ($film_edit['status'] ?? '') === 'akan_tayang' ? 'selected' : '' ?>>Akan Tayang</option>
                    <option value="selesai"     <?= ($film_edit['status'] ?? '') === 'selesai'     ? 'selected' : '' ?>>Selesai</option>
                </select>
            </div>

            <div class="btn-group-form">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i>
                    <?= $action === 'edit' ? 'Simpan Perubahan' : 'Tambah Film' ?>
                </button>
                <a href="films.php" class="btn btn-outline">Batal</a>
            </div>
        </form>
    </div>
</div>

<style>
.poster-upload-wrap {
    display: flex;
    align-items: flex-start;
    gap: 1.5rem;
    flex-wrap: wrap;
}
.poster-preview-box {
    width: 140px;
    height: 200px;
    border: 2px dashed var(--border, #333);
    border-radius: 8px;
    overflow: hidden;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    background: #111;
    flex-shrink: 0;
    position: relative;
}
.poster-preview-box img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    display: block;
}
.poster-preview-label {
    position: absolute;
    bottom: 0;
    left: 0;
    right: 0;
    background: rgba(0,0,0,0.6);
    color: #ccc;
    font-size: 0.7rem;
    text-align: center;
    padding: 3px 0;
}
.poster-placeholder {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 8px;
    color: #555;
    font-size: 0.85rem;
}
.poster-placeholder i { font-size: 2rem; }
.poster-upload-right {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
    justify-content: center;
    padding-top: 0.5rem;
}
.poster-upload-btn {
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    gap: 6px;
}
.form-hint {
    font-size: 0.8rem;
    color: #777;
    margin: 0;
}
</style>

<script>
function previewPoster(input) {
    const preview  = document.getElementById('posterPreview');
    const placeholder = document.getElementById('previewPlaceholder');
    const namaFile = document.getElementById('namaFile');

    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            preview.src = e.target.result;
            preview.style.display = 'block';
            if (placeholder) placeholder.style.display = 'none';
        };
        reader.readAsDataURL(input.files[0]);
        namaFile.textContent = '📎 ' + input.files[0].name;
    }
}
</script>
<?php endif; ?>

<!-- Daftar Film -->
<div class="box">
    <div class="box-header">
        <h3><i class="fas fa-film"></i> Daftar Film (<?= $daftar_film->num_rows ?>)</h3>
        <a href="films.php?action=add" class="btn btn-primary btn-sm">
            <i class="fas fa-plus"></i> Tambah Film
        </a>
    </div>

    <!-- Filter pencarian -->
    <div class="filter-bar">
        <form method="GET" class="filter-form">
            <input type="text" name="search" class="form-control filter-input-lg"
                   placeholder="Cari judul film..." value="<?= htmlspecialchars($cari) ?>">
            <select name="status" class="form-control filter-input-md">
                <option value="">Semua Status</option>
                <option value="tayang"      <?= $filter_status === 'tayang'      ? 'selected' : '' ?>>Sedang Tayang</option>
                <option value="akan_tayang" <?= $filter_status === 'akan_tayang' ? 'selected' : '' ?>>Akan Tayang</option>
                <option value="selesai"     <?= $filter_status === 'selesai'     ? 'selected' : '' ?>>Selesai</option>
            </select>
            <button type="submit" class="btn btn-primary btn-sm"><i class="fas fa-search"></i> Cari</button>
            <?php if ($cari || $filter_status): ?>
            <a href="films.php" class="btn btn-outline btn-sm">Reset</a>
            <?php endif; ?>
        </form>
    </div>

    <div class="tabel-wrap">
        <table class="tabel">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Poster</th>
                    <th>Judul</th>
                    <th>Genre</th>
                    <th>Durasi</th>
                    <th>Rating</th>
                    <th>Harga</th>
                    <th>Status</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php $no = 1; while ($f = $daftar_film->fetch_assoc()): ?>
                <tr>
                    <td class="text-abu"><?= $no++ ?></td>
                    <td>
                        <?php
                        $poster_path = '../assets/uploads/posters/' . ($f['poster'] ?? '');
                        if (!empty($f['poster']) && file_exists($poster_path)):
                        ?>
                        <img src="../assets/uploads/posters/<?= htmlspecialchars($f['poster']) ?>"
                             alt="<?= htmlspecialchars($f['judul']) ?>"
                             style="width:45px; height:65px; object-fit:cover; border-radius:4px; border:1px solid #333;">
                        <?php else: ?>
                        <div style="width:45px; height:65px; background:#1a1a1a; border-radius:4px; border:1px solid #333;
                                    display:flex; align-items:center; justify-content:center; color:#555; font-size:1.2rem;">
                            <i class="fas fa-image"></i>
                        </div>
                        <?php endif; ?>
                    </td>
                    <td class="text-bold"><?= htmlspecialchars($f['judul']) ?></td>
                    <td class="text-abu text-kecil"><?= htmlspecialchars($f['genre']) ?></td>
                    <td><?= $f['durasi'] ?> mnt</td>
                    <td><?= htmlspecialchars($f['rating']) ?></td>
                    <td class="text-kuning text-bold">Rp <?= number_format($f['harga'], 0, ',', '.') ?></td>
                    <td>
                        <span class="badge-status badge-<?= $f['status'] ?>">
                            <?= str_replace('_', ' ', ucfirst($f['status'])) ?>
                        </span>
                    </td>
                    <td>
                        <div class="aksi-group">
                            <a href="films.php?action=edit&id=<?= $f['id'] ?>" class="btn-edit">
                                <i class="fas fa-edit"></i> Edit
                            </a>
                            <a href="films.php?action=delete&id=<?= $f['id'] ?>" class="btn-hapus"
                               onclick="return confirm('Hapus film <?= addslashes($f['judul']) ?>?')">
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
