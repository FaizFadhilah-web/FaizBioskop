<?php
/**
 * admin_setup.php
 * Jalankan file ini SEKALI untuk membuat akun admin dengan password yang benar.
 * Hapus atau amankan file ini setelah digunakan.
 */
require_once 'config/database.php';

$username = 'admin';
$password = 'admin123';
$nama     = 'Super Admin';
$hashed   = password_hash($password, PASSWORD_DEFAULT);

// Update password admin yang sudah ada
$stmt = $conn->prepare("UPDATE admin SET password = ?, nama = ? WHERE username = ?");
$stmt->bind_param("sss", $hashed, $nama, $username);
$stmt->execute();

if ($stmt->affected_rows > 0) {
    echo "<p style='color:green'>✅ Password admin berhasil diupdate.</p>";
} else {
    // Jika belum ada, insert baru
    $stmt2 = $conn->prepare("INSERT INTO admin (nama, username, password) VALUES (?, ?, ?)");
    $stmt2->bind_param("sss", $nama, $username, $hashed);
    if ($stmt2->execute()) {
        echo "<p style='color:green'>✅ Akun admin berhasil dibuat.</p>";
    } else {
        echo "<p style='color:red'>❌ Gagal membuat akun admin.</p>";
    }
}

echo "<p>Username: <strong>$username</strong></p>";
echo "<p>Password: <strong>$password</strong></p>";
echo "<p><a href='admin/login.php'>→ Login ke Admin Panel</a></p>";
echo "<p style='color:red'><strong>⚠️ Hapus file ini setelah digunakan!</strong></p>";
