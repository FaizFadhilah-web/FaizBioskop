-- Database Setup untuk Website Tiket Bioskop
CREATE DATABASE IF NOT EXISTS bioskop_db CHARACTER SET utf8 COLLATE utf8_general_ci;
USE bioskop_db;

-- Tabel Users
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nama VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    no_hp VARCHAR(20),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Tabel Films
CREATE TABLE IF NOT EXISTS films (
    id INT AUTO_INCREMENT PRIMARY KEY,
    judul VARCHAR(200) NOT NULL,
    genre VARCHAR(100),
    durasi INT,
    rating VARCHAR(10),
    sinopsis TEXT,
    poster VARCHAR(255),
    harga DECIMAL(10,2) DEFAULT 50000,
    status ENUM('tayang','akan_tayang','selesai') DEFAULT 'tayang',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Tabel Studio
CREATE TABLE IF NOT EXISTS studio (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nama VARCHAR(50) NOT NULL,
    kapasitas INT DEFAULT 100
);

-- Tabel Jadwal
CREATE TABLE IF NOT EXISTS jadwal (
    id INT AUTO_INCREMENT PRIMARY KEY,
    film_id INT,
    studio_id INT,
    tanggal DATE,
    jam_tayang TIME,
    FOREIGN KEY (film_id) REFERENCES films(id),
    FOREIGN KEY (studio_id) REFERENCES studio(id)
);

-- Tabel Kursi
CREATE TABLE IF NOT EXISTS kursi (
    id INT AUTO_INCREMENT PRIMARY KEY,
    studio_id INT,
    kode_kursi VARCHAR(10),
    baris CHAR(1),
    nomor INT,
    FOREIGN KEY (studio_id) REFERENCES studio(id)
);

-- Tabel Pemesanan
CREATE TABLE IF NOT EXISTS pemesanan (
    id INT AUTO_INCREMENT PRIMARY KEY,
    kode_booking VARCHAR(20) UNIQUE NOT NULL,
    user_id INT,
    jadwal_id INT,
    total_harga DECIMAL(10,2),
    status ENUM('pending','confirmed','cancelled') DEFAULT 'confirmed',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id),
    FOREIGN KEY (jadwal_id) REFERENCES jadwal(id)
);

-- Tabel Detail Pemesanan (kursi yang dipesan)
CREATE TABLE IF NOT EXISTS detail_pemesanan (
    id INT AUTO_INCREMENT PRIMARY KEY,
    pemesanan_id INT,
    kursi_id INT,
    FOREIGN KEY (pemesanan_id) REFERENCES pemesanan(id),
    FOREIGN KEY (kursi_id) REFERENCES kursi(id)
);

-- Tabel Kursi Terpesan (per jadwal)
CREATE TABLE IF NOT EXISTS kursi_terpesan (
    id INT AUTO_INCREMENT PRIMARY KEY,
    jadwal_id INT,
    kursi_id INT,
    pemesanan_id INT,
    FOREIGN KEY (jadwal_id) REFERENCES jadwal(id),
    FOREIGN KEY (kursi_id) REFERENCES kursi(id),
    FOREIGN KEY (pemesanan_id) REFERENCES pemesanan(id)
);

-- Insert data studio
INSERT INTO studio (nama, kapasitas) VALUES 
('Studio 1', 80),
('Studio 2', 80),
('Studio 3', 80);

-- Insert kursi untuk Studio 1 (baris A-H, 10 kursi per baris)
INSERT INTO kursi (studio_id, kode_kursi, baris, nomor)
SELECT 1, CONCAT(b, n), b, n
FROM 
    (SELECT 'A' AS b UNION SELECT 'B' UNION SELECT 'C' UNION SELECT 'D' 
     UNION SELECT 'E' UNION SELECT 'F' UNION SELECT 'G' UNION SELECT 'H') baris,
    (SELECT 1 AS n UNION SELECT 2 UNION SELECT 3 UNION SELECT 4 UNION SELECT 5
     UNION SELECT 6 UNION SELECT 7 UNION SELECT 8 UNION SELECT 9 UNION SELECT 10) nomor;

-- Insert kursi untuk Studio 2
INSERT INTO kursi (studio_id, kode_kursi, baris, nomor)
SELECT 2, CONCAT(b, n), b, n
FROM 
    (SELECT 'A' AS b UNION SELECT 'B' UNION SELECT 'C' UNION SELECT 'D' 
     UNION SELECT 'E' UNION SELECT 'F' UNION SELECT 'G' UNION SELECT 'H') baris,
    (SELECT 1 AS n UNION SELECT 2 UNION SELECT 3 UNION SELECT 4 UNION SELECT 5
     UNION SELECT 6 UNION SELECT 7 UNION SELECT 8 UNION SELECT 9 UNION SELECT 10) nomor;

-- Insert kursi untuk Studio 3
INSERT INTO kursi (studio_id, kode_kursi, baris, nomor)
SELECT 3, CONCAT(b, n), b, n
FROM 
    (SELECT 'A' AS b UNION SELECT 'B' UNION SELECT 'C' UNION SELECT 'D' 
     UNION SELECT 'E' UNION SELECT 'F' UNION SELECT 'G' UNION SELECT 'H') baris,
    (SELECT 1 AS n UNION SELECT 2 UNION SELECT 3 UNION SELECT 4 UNION SELECT 5
     UNION SELECT 6 UNION SELECT 7 UNION SELECT 8 UNION SELECT 9 UNION SELECT 10) nomor;

-- Insert data film
INSERT INTO films (judul, genre, durasi, rating, sinopsis, poster, harga, status) VALUES
('Avengers: Secret Wars', 'Action, Sci-Fi', 180, 'PG-13', 'Para Avengers kembali bersatu untuk menghadapi ancaman terbesar dari multiverse yang mengancam seluruh realitas.', 'avengers.jpg', 75000, 'tayang'),
('Dune: Part Three', 'Sci-Fi, Adventure', 165, 'PG-13', 'Paul Atreides melanjutkan perjalanannya di planet Arrakis, menghadapi takdir yang telah lama menunggunya.', 'dune.jpg', 70000, 'tayang'),
('The Dark Knight Returns', 'Action, Drama', 155, 'PG-13', 'Bruce Wayne kembali mengenakan jubah Batman untuk menghadapi ancaman baru yang mengguncang Gotham City.', 'batman.jpg', 65000, 'tayang'),
('Interstellar 2', 'Sci-Fi, Drama', 170, 'PG', 'Sebuah misi luar angkasa baru membawa manusia ke dimensi yang belum pernah dijelajahi sebelumnya.', 'interstellar.jpg', 70000, 'tayang'),
('Fast & Furious 11', 'Action', 140, 'PG-13', 'Dom Toretto dan keluarganya menghadapi musuh paling berbahaya dalam sejarah mereka.', 'fast.jpg', 60000, 'tayang'),
('Spider-Man: Beyond', 'Action, Adventure', 150, 'PG', 'Peter Parker menghadapi ancaman dari dimensi lain yang mengancam seluruh New York.', 'spiderman.jpg', 65000, 'akan_tayang');

-- Insert jadwal tayang
INSERT INTO jadwal (film_id, studio_id, tanggal, jam_tayang) VALUES
(1, 1, CURDATE(), '10:00:00'),
(1, 1, CURDATE(), '13:00:00'),
(1, 2, CURDATE(), '16:00:00'),
(1, 2, CURDATE(), '19:00:00'),
(2, 1, CURDATE(), '11:00:00'),
(2, 3, CURDATE(), '14:00:00'),
(2, 3, CURDATE(), '17:00:00'),
(3, 2, CURDATE(), '10:30:00'),
(3, 1, CURDATE(), '15:30:00'),
(4, 3, CURDATE(), '12:00:00'),
(4, 2, CURDATE(), '18:00:00'),
(5, 1, CURDATE(), '20:00:00'),
(5, 3, CURDATE(), '20:30:00'),
(1, 1, DATE_ADD(CURDATE(), INTERVAL 1 DAY), '10:00:00'),
(2, 2, DATE_ADD(CURDATE(), INTERVAL 1 DAY), '13:00:00'),
(3, 3, DATE_ADD(CURDATE(), INTERVAL 1 DAY), '16:00:00');

-- Insert user demo
INSERT INTO users (nama, email, password, no_hp) VALUES
('Admin Demo', 'admin@bioskop.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '081234567890'),
('User Demo', 'user@bioskop.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '089876543210');
-- Password untuk kedua user: password

-- ============================================================
-- TABEL ADMIN (Panel Administrasi)
-- ============================================================
CREATE TABLE IF NOT EXISTS admin (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nama VARCHAR(100) NOT NULL,
    username VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Insert akun admin default
-- Username: admin | Password: admin123
-- Hash dibuat dengan: password_hash('admin123', PASSWORD_DEFAULT)
INSERT INTO admin (nama, username, password) VALUES
('Super Admin', 'admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi');
-- Catatan: Hash di atas adalah hash dari string "password" (sama dengan user demo).
-- Untuk password "admin123", jalankan admin_setup.php setelah import database.
