-- ============================================================
-- Script tambahan: Tabel Admin untuk Panel Administrasi
-- Jalankan ini jika database bioskop_db sudah ada sebelumnya
-- ============================================================
USE bioskop_db;

CREATE TABLE IF NOT EXISTS admin (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nama VARCHAR(100) NOT NULL,
    username VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Akun admin default
-- Username: admin
-- Password: password  (hash bcrypt dari string "password")
-- GANTI password setelah login pertama menggunakan halaman admin/admins.php
INSERT IGNORE INTO admin (nama, username, password) VALUES
('Super Admin', 'admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi');

-- Atau gunakan admin_setup.php untuk membuat akun dengan password custom
