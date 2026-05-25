<?php
require_once '../config/database.php';
require_once 'includes/auth.php';

$action = $_GET['action'] ?? 'list';
$id     = intval($_GET['id'] ?? 0);
$error  = '';

// ── HAPUS ─────────────────────────────────────────────────────────────────────
if ($action === 'delete' && $id) {
    $cek = $conn->prepare("SELECT COUNT(*) as c FROM pemesanan WHERE user_id = ?");
    $cek->bind_param("i", $id);
    $cek->execute();
    $ada_pesan = $cek->get_result()->fetch_assoc()['c'];

    if ($ada_pesan > 0) {
        $_SESSION['flash'] = ['type' => 'error', 'msg' => 'Pengguna tidak bisa dihapus karena memiliki riwayat pemesanan.'];
    } else {
        $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $_SESSION['flash'] = ['type' => 'success', 'msg' => 'Pengguna berhasil dihapus.'];
    }
    header('Location: users.php');
    exit;
}

// ── SIMPAN (EDIT) ─────────────────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama    = trim($_POST['nama'] ?? '');
    $email   = trim($_POST['email'] ?? '');
    $no_hp   = trim($_POST['no_hp'] ?? '');
    $edit_id = intval($_POST['edit_id'] ?? 0);
    $new_pw  = trim($_POST['new_password'] ?? '');

    if (!$nama || !$email) {
        $error  = 'Nama dan email wajib diisi.';
        $action = 'edit';
    } else {
        if ($new_pw) {
            $hashed = password_hash($new_pw, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("UPDATE users SET nama=?, email=?, no_hp=?, password=? WHERE id=?");
            $stmt->bind_param("ssssi", $nama, $email, $no_hp, $hashed, $edit_id);
        } else {
            $stmt = $conn->prepare("UPDATE users SET nama=?, email=?, no_hp=? WHERE id=?");
            $stmt->bind_param("sssi", $nama, $email, $no_hp, $edit_id);
        }
        $stmt->execute();
        $_SESSION['flash'] = ['type' => 'success', 'msg' => 'Data pengguna berhasil diperbarui.'];
        header('Location: users.php');
        exit;
    }
}

// ── AMBIL DATA UNTUK FORM EDIT ────────────────────────────────────────────────
$user_edit = null;
if ($action === 'edit' && $id) {
    $s = $conn->prepare("SELECT * FROM users WHERE id = ?");
    $s->bind_param("i", $id);
    $s->execute();
    $user_edit = $s->get_result()->fetch_assoc();
    if (!$user_edit) { header('Location: users.php'); exit; }
}

// ── DAFTAR PENGGUNA ───────────────────────────────────────────────────────────
$cari  = trim($_GET['search'] ?? '');
$where = $cari ? "WHERE u.nama LIKE '%$cari%' OR u.email LIKE '%$cari%'" : "WHERE 1=1";

$daftar_user = $conn->query("
    SELECT u.*,
           COUNT(p.id) AS total_pesan,
           COALESCE(SUM(CASE WHEN p.status='confirmed' THEN p.total_harga ELSE 0 END), 0) AS total_spend
    FROM users u
    LEFT JOIN pemesanan p ON u.id = p.user_id
    $where
    GROUP BY u.id
    ORDER BY u.created_at DESC
");

$pageTitle = 'Manajemen Pengguna';
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
<?php if ($action === 'edit' && $user_edit): ?>
<div class="box">
    <div class="box-header">
        <h3><i class="fas fa-user-edit"></i> Edit Pengguna</h3>
        <a href="users.php" class="btn-lihat"><i class="fas fa-arrow-left"></i> Kembali</a>
    </div>
    <div class="box-body">
        <form method="POST">
            <input type="hidden" name="edit_id" value="<?= $user_edit['id'] ?>">
            <div class="form-2col">
                <div class="form-group">
                    <label class="form-label">Nama Lengkap *</label>
                    <input type="text" name="nama" class="form-control" required value="<?= htmlspecialchars($user_edit['nama']) ?>">
                </div>
                <div class="form-group">
                    <label class="form-label">Email *</label>
                    <input type="email" name="email" class="form-control" required value="<?= htmlspecialchars($user_edit['email']) ?>">
                </div>
            </div>
            <div class="form-2col">
                <div class="form-group">
                    <label class="form-label">No. HP</label>
                    <input type="text" name="no_hp" class="form-control" value="<?= htmlspecialchars($user_edit['no_hp'] ?? '') ?>">
                </div>
                <div class="form-group">
                    <label class="form-label">Password Baru</label>
                    <input type="password" name="new_password" class="form-control" placeholder="Kosongkan jika tidak diubah">
                </div>
            </div>
            <div class="btn-group-form">
                <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Simpan</button>
                <a href="users.php" class="btn btn-outline">Batal</a>
            </div>
        </form>
    </div>
</div>
<?php endif; ?>

<!-- Daftar Pengguna -->
<div class="box">
    <div class="box-header">
        <h3><i class="fas fa-users"></i> Daftar Pengguna (<?= $daftar_user->num_rows ?>)</h3>
    </div>
    <div class="filter-bar">
        <form method="GET" class="filter-form">
            <input type="text" name="search" class="form-control filter-input-xl"
                   placeholder="Cari nama atau email..." value="<?= htmlspecialchars($cari) ?>">
            <button type="submit" class="btn btn-primary btn-sm"><i class="fas fa-search"></i> Cari</button>
            <?php if ($cari): ?>
            <a href="users.php" class="btn btn-outline btn-sm">Reset</a>
            <?php endif; ?>
        </form>
    </div>
    <div class="tabel-wrap">
        <table class="tabel">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Nama</th>
                    <th>Email</th>
                    <th>No. HP</th>
                    <th>Total Pesan</th>
                    <th>Total Spend</th>
                    <th>Bergabung</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php $no = 1; while ($u = $daftar_user->fetch_assoc()): ?>
                <tr>
                    <td class="text-abu"><?= $no++ ?></td>
                    <td class="text-bold"><?= htmlspecialchars($u['nama']) ?></td>
                    <td class="text-abu"><?= htmlspecialchars($u['email']) ?></td>
                    <td class="text-abu"><?= htmlspecialchars($u['no_hp'] ?? '-') ?></td>
                    <td><?= $u['total_pesan'] ?> tiket</td>
                    <td class="text-kuning text-bold">Rp <?= number_format($u['total_spend'], 0, ',', '.') ?></td>
                    <td class="text-abu text-kecil"><?= date('d M Y', strtotime($u['created_at'])) ?></td>
                    <td>
                        <div class="aksi-group">
                            <a href="users.php?action=edit&id=<?= $u['id'] ?>" class="btn-edit"><i class="fas fa-edit"></i> Edit</a>
                            <a href="users.php?action=delete&id=<?= $u['id'] ?>" class="btn-hapus"
                               onclick="return confirm('Hapus pengguna <?= addslashes($u['nama']) ?>?')">
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
