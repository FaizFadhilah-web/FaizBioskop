<?php
/**
 * seat_reset.php
 * Membersihkan kursi_terpesan untuk jadwal yang sudah selesai tayang.
 * Dipanggil otomatis setiap kali ada request ke website.
 *
 * Logika: jadwal dianggap selesai jika
 *   DATETIME(tanggal, jam_tayang) < NOW()
 * artinya jam tayang sudah lewat, kursi bisa direset untuk jadwal berikutnya.
 */

// Hindari eksekusi berulang dalam satu request
if (defined('SEAT_RESET_DONE')) return;
define('SEAT_RESET_DONE', true);

// Gunakan session sebagai throttle — reset maksimal sekali per menit
// agar tidak membebani DB di setiap request
if (isset($_SESSION['_seat_reset_last']) && (time() - $_SESSION['_seat_reset_last']) < 60) {
    return;
}
$_SESSION['_seat_reset_last'] = time();

// Hapus kursi_terpesan milik jadwal yang sudah lewat,
// HANYA untuk pemesanan yang masih pending (belum dikonfirmasi admin).
// Pemesanan yang sudah confirmed TIDAK direset agar kursi tetap terkunci.
$conn->query("
    DELETE kt FROM kursi_terpesan kt
    JOIN jadwal j ON kt.jadwal_id = j.id
    JOIN pemesanan p ON kt.pemesanan_id = p.id
    WHERE TIMESTAMP(j.tanggal, j.jam_tayang) < NOW()
      AND p.status = 'pending'
");
