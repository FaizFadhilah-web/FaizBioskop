<?php
require_once '../config/database.php';
require_once 'includes/auth.php';

$action = $_GET['action'] ?? 'list';
$id     = intval($_GET['id'] ?? 0);
$error  = '';

// ── AKTIFKAN / NONAKTIFKAN ────────────────────────────────────────────────────
if ($action === 'toggle' && $id) {
    if ($id == $_SESSION['admin_id']) {
        $_SESSION['flash'] = ['type' => 'error', 'msg' => 'Tidak bisa menonaktifkan akun Anda sendiri.'];
    } else {
        $conn->query("UPDATE admin SET is_active = NOT is_active WHERE id=$id");
        $_SESSION['flash'] = ['type' => 'success', 'msg' => 'Status admin berhasil diubah.'];
    }
    header('Location: admins.php');
    exit;
}

// ── HAPUS ─────────────────────────────────────────────────────────────────────
if ($action === 'delete' && $id) {
    if ($id == $_SESSION['admin_id']) {
        $_SESSION['flash'] = ['type' => 'error', 'msg' => 'Tidak bisa menghapus akun Anda sendiri.'];
    } else {
        $stmt = $conn->prepare("DELETE FROM admin WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $_SESSION['flash'] = ['type' => 'success', 'msg' => 'Admin berhasil dihapus.'];
    }
    header('Location: admins.php');
    exit;
}

// ── SIMPAN (EDIT SAJA) ────────────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama     = trim($_POST['nama'] ?? '');
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');
    $edit_id  = intval($_POST['edit_id'] ?? 0);

    if (!$edit_id) {
        header('Location: admins.php');
        exit;
    }

    if (!$nama || !$username) {
        $error  = 'Nama dan username wajib diisi.';
        $action = 'edit';
    } else {
        // Cek username sudah dipakai atau belum
        $cek = $conn->prepare("SELECT id FROM admin WHERE username=? AND id!=?");
        $cek->bind_param("si", $username, $edit_id);
        $cek->execute();

        if ($cek->get_result()->num_rows > 0) {
            $error  = 'Username sudah digunakan.';
            $action = 'edit';
        } else {
            if ($password) {
                $hashed = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $conn->prepare("UPDATE admin SET nama=?, username=?, password=? WHERE id=?");
                $stmt->bind_param("sssi", $nama, $username, $hashed, $edit_id);
            } else {
                $stmt = $conn->prepare("UPDATE admin SET nama=?, username=? WHERE id=?");
                $stmt->bind_param("ssi", $nama, $username, $edit_id);
            }
            $stmt->execute();
            $_SESSION['flash'] = ['type' => 'success', 'msg' => 'Admin berhasil diperbarui.'];
            header('Location: admins.php');
            exit;
        }
    }
}

// ── AMBIL DATA UNTUK FORM EDIT ────────────────────────────────────────────────
$admin_edit = null;
if ($action === 'edit' && $id) {
    $s = $conn->prepare("SELECT * FROM admin WHERE id = ?");
    $s->bind_param("i", $id);
    $s->execute();
    $admin_edit = $s->get_result()->fetch_assoc();
    if (!$admin_edit) { header('Location: admins.php'); exit; }
}

// ── DAFTAR ADMIN ──────────────────────────────────────────────────────────────
$daftar_admin = $conn->query("SELECT * FROM admin ORDER BY id");

$pageTitle = 'Manajemen Admin';
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

<!-- Form Edit -->
<?php if ($action === 'edit'): ?>
<div class="box">
    <div class="box-header">
        <h3>
            <i class="fas fa-user-edit"></i>
            Edit Admin
        </h3>
        <a href="admins.php" class="btn-lihat"><i class="fas fa-arrow-left"></i> Kembali</a>
    </div>
    <div class="box-body">
        <form method="POST">
            <input type="hidden" name="edit_id" value="<?= $admin_edit['id'] ?>">

            <div class="form-2col">
                <div class="form-group">
                    <label class="form-label">Nama Lengkap *</label>
                    <input type="text" name="nama" class="form-control" required
                           value="<?= htmlspecialchars($admin_edit['nama'] ?? $_POST['nama'] ?? '') ?>">
                </div>
                <div class="form-group">
                    <label class="form-label">Username *</label>
                    <input type="text" name="username" class="form-control" required
                           value="<?= htmlspecialchars($admin_edit['username'] ?? $_POST['username'] ?? '') ?>">
                </div>
            </div>

            <div class="form-group form-group-narrow">
                <label class="form-label">Password <small style="color:#888">(kosongkan jika tidak diubah)</small></label>
                <input type="password" name="password" class="form-control"
                       placeholder="Kosongkan jika tidak diubah">
            </div>

            <div class="btn-group-form">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Simpan Perubahan
                </button>
                <a href="admins.php" class="btn btn-outline">Batal</a>
            </div>
        </form>
    </div>
</div>
<?php endif; ?>

<!-- Daftar Admin -->
<div class="box">
    <div class="box-header">
        <h3><i class="fas fa-user-shield"></i> Daftar Admin</h3>
    </div>
    <div class="tabel-wrap">
        <table class="tabel">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Nama</th>
                    <th>Username</th>
                    <th>Status</th>
                    <th>Dibuat</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php $no = 1; while ($a = $daftar_admin->fetch_assoc()): ?>
                <tr>
                    <td class="text-abu"><?= $no++ ?></td>
                    <td class="text-bold">
                        <?= htmlspecialchars($a['nama']) ?>
                        <?php if ($a['id'] == $_SESSION['admin_id']): ?>
                        <span class="badge badge-primary badge-anda">Anda</span>
                        <?php endif; ?>
                    </td>
                    <td class="text-kode"><?= htmlspecialchars($a['username']) ?></td>
                    <td>
                        <span class="badge-status <?= $a['is_active'] ? 'badge-aktif' : 'badge-nonaktif' ?>">
                            <?= $a['is_active'] ? 'Aktif' : 'Nonaktif' ?>
                        </span>
                    </td>
                    <td class="text-abu text-kecil"><?= date('d M Y', strtotime($a['created_at'])) ?></td>
                    <td>
                        <div class="aksi-group">
                            <a href="admins.php?action=edit&id=<?= $a['id'] ?>" class="btn-edit">
                                <i class="fas fa-edit"></i> Edit
                            </a>
                            <?php if ($a['id'] != $_SESSION['admin_id']): ?>
                            <a href="admins.php?action=toggle&id=<?= $a['id'] ?>"
                               class="<?= $a['is_active'] ? 'btn-hapus' : 'btn-lihat' ?>"
                               onclick="return confirm('<?= $a['is_active'] ? 'Nonaktifkan' : 'Aktifkan' ?> admin ini?')">
                                <i class="fas fa-<?= $a['is_active'] ? 'ban' : 'check' ?>"></i>
                                <?= $a['is_active'] ? 'Nonaktifkan' : 'Aktifkan' ?>
                            </a>
                            <a href="admins.php?action=delete&id=<?= $a['id'] ?>" class="btn-hapus"
                               onclick="return confirm('Hapus admin ini?')">
                                <i class="fas fa-trash"></i>
                            </a>
                            <?php endif; ?>
                        </div>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
