<?php
require_once '../config/database.php';
require_once 'includes/auth.php';

$action = $_GET['action'] ?? 'list';
$id     = intval($_GET['id'] ?? 0);
$error  = '';

// ── HAPUS ─────────────────────────────────────────────────────────────────────
if ($action === 'delete' && $id) {
    $cek = $conn->prepare("SELECT COUNT(*) as c FROM jadwal WHERE studio_id = ?");
    $cek->bind_param("i", $id);
    $cek->execute();
    $ada_jadwal = $cek->get_result()->fetch_assoc()['c'];

    if ($ada_jadwal > 0) {
        $_SESSION['flash'] = ['type' => 'error', 'msg' => 'Studio tidak bisa dihapus karena masih memiliki jadwal.'];
    } else {
        $conn->query("DELETE FROM kursi WHERE studio_id = $id");
        $stmt = $conn->prepare("DELETE FROM studio WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $_SESSION['flash'] = ['type' => 'success', 'msg' => 'Studio berhasil dihapus.'];
    }
    header('Location: studio.php');
    exit;
}

// ── SIMPAN ────────────────────────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama      = trim($_POST['nama'] ?? '');
    $kapasitas = intval($_POST['kapasitas'] ?? 0);
    $edit_id   = intval($_POST['edit_id'] ?? 0);

    if (!$nama || $kapasitas < 1) {
        $error  = 'Nama dan kapasitas wajib diisi.';
        $action = $edit_id ? 'edit' : 'add';
    } else {
        if ($edit_id) {
            $stmt = $conn->prepare("UPDATE studio SET nama=?, kapasitas=? WHERE id=?");
            $stmt->bind_param("sii", $nama, $kapasitas, $edit_id);
        } else {
            $stmt = $conn->prepare("INSERT INTO studio (nama, kapasitas) VALUES (?,?)");
            $stmt->bind_param("si", $nama, $kapasitas);
        }
        $stmt->execute();

        // Generate kursi otomatis untuk studio baru
        if (!$edit_id) {
            $studio_id_baru  = $stmt->insert_id; // pakai $stmt->insert_id, bukan $conn->insert_id
            $baris_list      = range('A', 'Z');
            $kursi_per_baris = 10;
            $sisa            = $kapasitas;
            $b               = 0;

            // Siapkan prepared statement sekali di luar loop (lebih efisien)
            $ins = $conn->prepare("INSERT INTO kursi (studio_id, kode_kursi, baris, nomor) VALUES (?, ?, ?, ?)");

            while ($sisa > 0 && $b < count($baris_list)) {
                $baris        = $baris_list[$b];
                $jumlah_baris = min($kursi_per_baris, $sisa);
                for ($n = 1; $n <= $jumlah_baris; $n++) {
                    $kode = $baris . $n;
                    $ins->bind_param("issi", $studio_id_baru, $kode, $baris, $n);
                    $ins->execute();
                }
                $sisa -= $jumlah_baris;
                $b++;
            }
        }

        $_SESSION['flash'] = ['type' => 'success', 'msg' => $edit_id ? 'Studio berhasil diperbarui.' : 'Studio berhasil ditambahkan.'];
        header('Location: studio.php');
        exit;
    }
}

// ── AMBIL DATA UNTUK FORM EDIT ────────────────────────────────────────────────
$studio_edit = null;
if ($action === 'edit' && $id) {
    $s = $conn->prepare("SELECT * FROM studio WHERE id = ?");
    $s->bind_param("i", $id);
    $s->execute();
    $studio_edit = $s->get_result()->fetch_assoc();
    if (!$studio_edit) { header('Location: studio.php'); exit; }
}

// ── DAFTAR STUDIO ─────────────────────────────────────────────────────────────
$daftar_studio = $conn->query("
    SELECT s.*,
           COUNT(DISTINCT k.id) AS total_kursi,
           COUNT(DISTINCT j.id) AS total_jadwal
    FROM studio s
    LEFT JOIN kursi k ON s.id = k.studio_id
    LEFT JOIN jadwal j ON s.id = j.studio_id
    GROUP BY s.id
    ORDER BY s.id
");

$pageTitle = 'Manajemen Studio';
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

<!-- Form Tambah / Edit -->
<?php if ($action === 'add' || $action === 'edit'): ?>
<div class="box">
    <div class="box-header">
        <h3>
            <i class="fas fa-<?= $action === 'edit' ? 'edit' : 'plus' ?>"></i>
            <?= $action === 'edit' ? 'Edit Studio' : 'Tambah Studio Baru' ?>
        </h3>
        <a href="studio.php" class="btn-lihat"><i class="fas fa-arrow-left"></i> Kembali</a>
    </div>
    <div class="box-body">
        <form method="POST">
            <?php if ($action === 'edit'): ?>
            <input type="hidden" name="edit_id" value="<?= $studio_edit['id'] ?>">
            <?php endif; ?>

            <div class="form-2col">
                <div class="form-group">
                    <label class="form-label">Nama Studio *</label>
                    <input type="text" name="nama" class="form-control" required
                           value="<?= htmlspecialchars($studio_edit['nama'] ?? $_POST['nama'] ?? '') ?>">
                </div>
                <div class="form-group">
                    <label class="form-label">Kapasitas Kursi *</label>
                    <input type="number" name="kapasitas" class="form-control" min="1" required
                           value="<?= htmlspecialchars($studio_edit['kapasitas'] ?? $_POST['kapasitas'] ?? '') ?>">
                </div>
            </div>

            <div class="btn-group-form">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i>
                    <?= $action === 'edit' ? 'Simpan Perubahan' : 'Tambah Studio' ?>
                </button>
                <a href="studio.php" class="btn btn-outline">Batal</a>
            </div>
        </form>
    </div>
</div>
<?php endif; ?>

<!-- Daftar Studio -->
<div class="box">
    <div class="box-header">
        <h3><i class="fas fa-building"></i> Daftar Studio</h3>
        <a href="studio.php?action=add" class="btn btn-primary btn-sm">
            <i class="fas fa-plus"></i> Tambah Studio
        </a>
    </div>
    <div class="tabel-wrap">
        <table class="tabel">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Nama Studio</th>
                    <th>Kapasitas</th>
                    <th>Kursi di DB</th>
                    <th>Total Jadwal</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php $no = 1; while ($s = $daftar_studio->fetch_assoc()): ?>
                <tr>
                    <td class="text-abu"><?= $no++ ?></td>
                    <td class="text-bold"><?= htmlspecialchars($s['nama']) ?></td>
                    <td><?= $s['kapasitas'] ?> kursi</td>
                    <td><?= $s['total_kursi'] ?> kursi</td>
                    <td><?= $s['total_jadwal'] ?> jadwal</td>
                    <td>
                        <div class="aksi-group">
                            <a href="studio.php?action=edit&id=<?= $s['id'] ?>" class="btn-edit">
                                <i class="fas fa-edit"></i> Edit
                            </a>
                            <a href="studio.php?action=delete&id=<?= $s['id'] ?>" class="btn-hapus"
                               onclick="return confirm('Hapus studio <?= addslashes($s['nama']) ?>?')">
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
