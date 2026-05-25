-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Waktu pembuatan: 26 Bulan Mei 2026 pada 01.53
-- Versi server: 10.4.32-MariaDB
-- Versi PHP: 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `bioskop_db`
--

-- --------------------------------------------------------

--
-- Struktur dari tabel `admin`
--

CREATE TABLE `admin` (
  `id` int(11) NOT NULL,
  `nama` varchar(100) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Dumping data untuk tabel `admin`
--

INSERT INTO `admin` (`id`, `nama`, `username`, `password`, `is_active`, `created_at`) VALUES
(1, 'Super marns', 'marns', '$2y$10$FsPzqU/fH6fakwxQ6YhLAefwd0ClXrfhYharKXCjm/da.tLf1iqNi', 1, '2026-05-06 04:15:57'),
(3, 'pasipapa', 'pasi', '$2y$10$c7knjZnGZ.ee5sSAlrv5oeYV8sHqwLJxoTete6l6Mbb9qv5bVl1Q2', 1, '2026-05-08 05:48:59');

-- --------------------------------------------------------

--
-- Struktur dari tabel `detail_pemesanan`
--

CREATE TABLE `detail_pemesanan` (
  `id` int(11) NOT NULL,
  `pemesanan_id` int(11) DEFAULT NULL,
  `kursi_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Dumping data untuk tabel `detail_pemesanan`
--

INSERT INTO `detail_pemesanan` (`id`, `pemesanan_id`, `kursi_id`) VALUES
(16, 10, 43),
(17, 10, 51),
(32, 19, 154),
(33, 19, 170),
(34, 20, 187),
(35, 20, 195),
(36, 21, 43),
(37, 21, 51),
(38, 22, 35),
(39, 22, 51),
(40, 23, 20),
(41, 23, 36),
(42, 24, 152),
(43, 25, 44),
(44, 25, 60),
(48, 28, 369),
(49, 29, 144),
(50, 30, 161),
(51, 30, 169),
(52, 31, 155),
(53, 31, 179),
(54, 32, 268),
(55, 32, 304),
(56, 33, 282),
(57, 33, 294),
(58, 34, 18),
(59, 34, 50),
(60, 35, 153),
(61, 35, 177),
(64, 37, 276),
(65, 37, 294),
(66, 38, 278),
(67, 38, 284),
(68, 39, 274),
(69, 39, 286),
(70, 40, 281),
(71, 40, 287),
(72, 41, 282),
(73, 41, 288);

-- --------------------------------------------------------

--
-- Struktur dari tabel `films`
--

CREATE TABLE `films` (
  `id` int(11) NOT NULL,
  `judul` varchar(200) NOT NULL,
  `genre` varchar(100) DEFAULT NULL,
  `durasi` int(11) DEFAULT NULL,
  `rating` varchar(10) DEFAULT NULL,
  `sinopsis` text DEFAULT NULL,
  `poster` varchar(255) DEFAULT NULL,
  `harga` decimal(10,2) DEFAULT 50000.00,
  `status` enum('tayang','akan_tayang','selesai') DEFAULT 'tayang',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Dumping data untuk tabel `films`
--

INSERT INTO `films` (`id`, `judul`, `genre`, `durasi`, `rating`, `sinopsis`, `poster`, `harga`, `status`, `created_at`) VALUES
(9, 'Liga Jalanan', 'Action', 120, '13+', 'sekelompok anak muda yang mengejar mimpinya', 'poster_1778542455_38e9ceb1.jpeg', 10000.00, 'akan_tayang', '2026-05-08 11:39:14'),
(13, 'Manusia Duyung Eksekutif', 'Komedi', 30, 'SU', 'Kisah anak remaja yang takut air, jadi jarang mandi', 'poster_1778488657_5965c1a6.jpeg', 10000.00, 'tayang', '2026-05-10 14:32:55'),
(14, 'Dua Jalan', 'Horor', 120, '17+', '2 Remaja kuat dan hebat', 'poster_1778487615_3fe8d8b2.jpeg', 30000.00, 'tayang', '2026-05-10 14:47:53'),
(15, 'Penjaga gang', 'Komedi', 140, '13+', 'Penjaga gang rumah layaknya security', 'poster_1778485535_bde50042.jpeg', 10000.00, 'tayang', '2026-05-11 07:45:35'),
(16, 'New Year New Me', 'Action', 120, '13+', 'sekelompok anak muda yang ingin berubah di tahun baru', 'poster_1778488900_3c03ba8d.jpeg', 30000.00, 'tayang', '2026-05-11 08:41:40'),
(18, 'Warisan Penjaga', 'Horor', 60, '17+', 'warisan warisan', 'poster_1778546668_3c255557.jpeg', 50000.00, 'tayang', '2026-05-12 00:44:28'),
(19, 'Penjaga Gawang', 'Action', 128, 'SU', 'Penjaga gawang terbaik dari cibogo', 'poster_1778547672_287fee52.jpeg', 40000.00, 'tayang', '2026-05-12 01:01:12'),
(20, 'Persahabatan yang Terkecoh', 'Komedi', 140, '13+', 'sejuta tawa sejuta kenangan', 'poster_1778547736_353a7156.jpeg', 15000.00, 'tayang', '2026-05-12 01:02:16'),
(21, 'Petualangan Impian Kecil', 'Romantis', 120, 'SU', 'kisah anak remaja yang kembali bertemu dengan cinta pertama nya', 'poster_1778548782_5df86337.jpeg', 30000.00, 'tayang', '2026-05-12 01:19:43'),
(22, 'api malam', 'Action', 120, '13+', 'qapi api api', 'poster_1778556979_36fb8ea9.jpeg', 10000.00, 'tayang', '2026-05-12 03:36:19');

-- --------------------------------------------------------

--
-- Struktur dari tabel `jadwal`
--

CREATE TABLE `jadwal` (
  `id` int(11) NOT NULL,
  `film_id` int(11) DEFAULT NULL,
  `studio_id` int(11) DEFAULT NULL,
  `tanggal` date DEFAULT NULL,
  `jam_tayang` time DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Dumping data untuk tabel `jadwal`
--

INSERT INTO `jadwal` (`id`, `film_id`, `studio_id`, `tanggal`, `jam_tayang`) VALUES
(22, 9, 1, '2026-05-09', '20:00:00'),
(26, 13, 5, '2026-05-10', '19:00:00'),
(27, 14, 5, '2026-05-11', '21:00:00'),
(28, 15, 2, '2026-05-11', '20:00:00'),
(29, 15, 1, '2026-05-11', '10:00:00'),
(30, 16, 2, '2026-05-12', '10:00:00'),
(32, 14, 2, '2026-05-12', '12:00:00'),
(33, 14, 1, '2026-05-12', '13:00:00'),
(34, 14, 3, '2026-05-12', '15:00:00'),
(35, 14, 5, '2026-05-12', '18:00:00'),
(37, 18, 3, '2026-05-12', '13:00:00'),
(38, 19, 1, '2026-05-12', '12:00:00'),
(39, 20, 3, '2026-05-19', '13:00:00'),
(40, 21, 3, '2026-05-12', '15:00:00'),
(41, 16, 2, '2026-05-12', '14:00:00'),
(42, 22, 2, '2026-05-12', '11:00:00');

-- --------------------------------------------------------

--
-- Struktur dari tabel `kursi`
--

CREATE TABLE `kursi` (
  `id` int(11) NOT NULL,
  `studio_id` int(11) DEFAULT NULL,
  `kode_kursi` varchar(10) DEFAULT NULL,
  `baris` char(1) DEFAULT NULL,
  `nomor` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Dumping data untuk tabel `kursi`
--

INSERT INTO `kursi` (`id`, `studio_id`, `kode_kursi`, `baris`, `nomor`) VALUES
(1, 1, 'A1', 'A', 1),
(2, 1, 'B1', 'B', 1),
(3, 1, 'C1', 'C', 1),
(4, 1, 'D1', 'D', 1),
(5, 1, 'E1', 'E', 1),
(6, 1, 'F1', 'F', 1),
(7, 1, 'G1', 'G', 1),
(8, 1, 'H1', 'H', 1),
(9, 1, 'A2', 'A', 2),
(10, 1, 'B2', 'B', 2),
(11, 1, 'C2', 'C', 2),
(12, 1, 'D2', 'D', 2),
(13, 1, 'E2', 'E', 2),
(14, 1, 'F2', 'F', 2),
(15, 1, 'G2', 'G', 2),
(16, 1, 'H2', 'H', 2),
(17, 1, 'A3', 'A', 3),
(18, 1, 'B3', 'B', 3),
(19, 1, 'C3', 'C', 3),
(20, 1, 'D3', 'D', 3),
(21, 1, 'E3', 'E', 3),
(22, 1, 'F3', 'F', 3),
(23, 1, 'G3', 'G', 3),
(24, 1, 'H3', 'H', 3),
(25, 1, 'A4', 'A', 4),
(26, 1, 'B4', 'B', 4),
(27, 1, 'C4', 'C', 4),
(28, 1, 'D4', 'D', 4),
(29, 1, 'E4', 'E', 4),
(30, 1, 'F4', 'F', 4),
(31, 1, 'G4', 'G', 4),
(32, 1, 'H4', 'H', 4),
(33, 1, 'A5', 'A', 5),
(34, 1, 'B5', 'B', 5),
(35, 1, 'C5', 'C', 5),
(36, 1, 'D5', 'D', 5),
(37, 1, 'E5', 'E', 5),
(38, 1, 'F5', 'F', 5),
(39, 1, 'G5', 'G', 5),
(40, 1, 'H5', 'H', 5),
(41, 1, 'A6', 'A', 6),
(42, 1, 'B6', 'B', 6),
(43, 1, 'C6', 'C', 6),
(44, 1, 'D6', 'D', 6),
(45, 1, 'E6', 'E', 6),
(46, 1, 'F6', 'F', 6),
(47, 1, 'G6', 'G', 6),
(48, 1, 'H6', 'H', 6),
(49, 1, 'A7', 'A', 7),
(50, 1, 'B7', 'B', 7),
(51, 1, 'C7', 'C', 7),
(52, 1, 'D7', 'D', 7),
(53, 1, 'E7', 'E', 7),
(54, 1, 'F7', 'F', 7),
(55, 1, 'G7', 'G', 7),
(56, 1, 'H7', 'H', 7),
(57, 1, 'A8', 'A', 8),
(58, 1, 'B8', 'B', 8),
(59, 1, 'C8', 'C', 8),
(60, 1, 'D8', 'D', 8),
(61, 1, 'E8', 'E', 8),
(62, 1, 'F8', 'F', 8),
(63, 1, 'G8', 'G', 8),
(64, 1, 'H8', 'H', 8),
(65, 1, 'A9', 'A', 9),
(66, 1, 'B9', 'B', 9),
(67, 1, 'C9', 'C', 9),
(68, 1, 'D9', 'D', 9),
(69, 1, 'E9', 'E', 9),
(70, 1, 'F9', 'F', 9),
(71, 1, 'G9', 'G', 9),
(72, 1, 'H9', 'H', 9),
(73, 1, 'A10', 'A', 10),
(74, 1, 'B10', 'B', 10),
(75, 1, 'C10', 'C', 10),
(76, 1, 'D10', 'D', 10),
(77, 1, 'E10', 'E', 10),
(78, 1, 'F10', 'F', 10),
(79, 1, 'G10', 'G', 10),
(80, 1, 'H10', 'H', 10),
(128, 2, 'A1', 'A', 1),
(129, 2, 'B1', 'B', 1),
(130, 2, 'C1', 'C', 1),
(131, 2, 'D1', 'D', 1),
(132, 2, 'E1', 'E', 1),
(133, 2, 'F1', 'F', 1),
(134, 2, 'G1', 'G', 1),
(135, 2, 'H1', 'H', 1),
(136, 2, 'A2', 'A', 2),
(137, 2, 'B2', 'B', 2),
(138, 2, 'C2', 'C', 2),
(139, 2, 'D2', 'D', 2),
(140, 2, 'E2', 'E', 2),
(141, 2, 'F2', 'F', 2),
(142, 2, 'G2', 'G', 2),
(143, 2, 'H2', 'H', 2),
(144, 2, 'A3', 'A', 3),
(145, 2, 'B3', 'B', 3),
(146, 2, 'C3', 'C', 3),
(147, 2, 'D3', 'D', 3),
(148, 2, 'E3', 'E', 3),
(149, 2, 'F3', 'F', 3),
(150, 2, 'G3', 'G', 3),
(151, 2, 'H3', 'H', 3),
(152, 2, 'A4', 'A', 4),
(153, 2, 'B4', 'B', 4),
(154, 2, 'C4', 'C', 4),
(155, 2, 'D4', 'D', 4),
(156, 2, 'E4', 'E', 4),
(157, 2, 'F4', 'F', 4),
(158, 2, 'G4', 'G', 4),
(159, 2, 'H4', 'H', 4),
(160, 2, 'A5', 'A', 5),
(161, 2, 'B5', 'B', 5),
(162, 2, 'C5', 'C', 5),
(163, 2, 'D5', 'D', 5),
(164, 2, 'E5', 'E', 5),
(165, 2, 'F5', 'F', 5),
(166, 2, 'G5', 'G', 5),
(167, 2, 'H5', 'H', 5),
(168, 2, 'A6', 'A', 6),
(169, 2, 'B6', 'B', 6),
(170, 2, 'C6', 'C', 6),
(171, 2, 'D6', 'D', 6),
(172, 2, 'E6', 'E', 6),
(173, 2, 'F6', 'F', 6),
(174, 2, 'G6', 'G', 6),
(175, 2, 'H6', 'H', 6),
(176, 2, 'A7', 'A', 7),
(177, 2, 'B7', 'B', 7),
(178, 2, 'C7', 'C', 7),
(179, 2, 'D7', 'D', 7),
(180, 2, 'E7', 'E', 7),
(181, 2, 'F7', 'F', 7),
(182, 2, 'G7', 'G', 7),
(183, 2, 'H7', 'H', 7),
(184, 2, 'A8', 'A', 8),
(185, 2, 'B8', 'B', 8),
(186, 2, 'C8', 'C', 8),
(187, 2, 'D8', 'D', 8),
(188, 2, 'E8', 'E', 8),
(189, 2, 'F8', 'F', 8),
(190, 2, 'G8', 'G', 8),
(191, 2, 'H8', 'H', 8),
(192, 2, 'A9', 'A', 9),
(193, 2, 'B9', 'B', 9),
(194, 2, 'C9', 'C', 9),
(195, 2, 'D9', 'D', 9),
(196, 2, 'E9', 'E', 9),
(197, 2, 'F9', 'F', 9),
(198, 2, 'G9', 'G', 9),
(199, 2, 'H9', 'H', 9),
(200, 2, 'A10', 'A', 10),
(201, 2, 'B10', 'B', 10),
(202, 2, 'C10', 'C', 10),
(203, 2, 'D10', 'D', 10),
(204, 2, 'E10', 'E', 10),
(205, 2, 'F10', 'F', 10),
(206, 2, 'G10', 'G', 10),
(207, 2, 'H10', 'H', 10),
(255, 3, 'A1', 'A', 1),
(256, 3, 'B1', 'B', 1),
(257, 3, 'C1', 'C', 1),
(258, 3, 'D1', 'D', 1),
(259, 3, 'E1', 'E', 1),
(260, 3, 'F1', 'F', 1),
(261, 3, 'A2', 'A', 2),
(262, 3, 'B2', 'B', 2),
(263, 3, 'C2', 'C', 2),
(264, 3, 'D2', 'D', 2),
(265, 3, 'E2', 'E', 2),
(266, 3, 'F2', 'F', 2),
(267, 3, 'A3', 'A', 3),
(268, 3, 'B3', 'B', 3),
(269, 3, 'C3', 'C', 3),
(270, 3, 'D3', 'D', 3),
(271, 3, 'E3', 'E', 3),
(272, 3, 'F3', 'F', 3),
(273, 3, 'A4', 'A', 4),
(274, 3, 'B4', 'B', 4),
(275, 3, 'C4', 'C', 4),
(276, 3, 'D4', 'D', 4),
(277, 3, 'E4', 'E', 4),
(278, 3, 'F4', 'F', 4),
(279, 3, 'A5', 'A', 5),
(280, 3, 'B5', 'B', 5),
(281, 3, 'C5', 'C', 5),
(282, 3, 'D5', 'D', 5),
(283, 3, 'E5', 'E', 5),
(284, 3, 'F5', 'F', 5),
(285, 3, 'A6', 'A', 6),
(286, 3, 'B6', 'B', 6),
(287, 3, 'C6', 'C', 6),
(288, 3, 'D6', 'D', 6),
(289, 3, 'E6', 'E', 6),
(290, 3, 'F6', 'F', 6),
(291, 3, 'A7', 'A', 7),
(292, 3, 'B7', 'B', 7),
(293, 3, 'C7', 'C', 7),
(294, 3, 'D7', 'D', 7),
(295, 3, 'E7', 'E', 7),
(296, 3, 'F7', 'F', 7),
(297, 3, 'A8', 'A', 8),
(298, 3, 'B8', 'B', 8),
(299, 3, 'C8', 'C', 8),
(300, 3, 'D8', 'D', 8),
(301, 3, 'E8', 'E', 8),
(302, 3, 'F8', 'F', 8),
(303, 3, 'A9', 'A', 9),
(304, 3, 'B9', 'B', 9),
(305, 3, 'C9', 'C', 9),
(306, 3, 'D9', 'D', 9),
(307, 3, 'E9', 'E', 9),
(308, 3, 'F9', 'F', 9),
(309, 3, 'A10', 'A', 10),
(310, 3, 'B10', 'B', 10),
(311, 3, 'C10', 'C', 10),
(312, 3, 'D10', 'D', 10),
(313, 3, 'E10', 'E', 10),
(314, 3, 'F10', 'F', 10),
(315, 3, 'G1', 'G', 1),
(316, 3, 'G2', 'G', 2),
(317, 3, 'G3', 'G', 3),
(318, 3, 'G4', 'G', 4),
(319, 3, 'G5', 'G', 5),
(320, 3, 'G6', 'G', 6),
(321, 3, 'G7', 'G', 7),
(322, 3, 'G8', 'G', 8),
(323, 3, 'G9', 'G', 9),
(324, 3, 'G10', 'G', 10),
(325, 3, 'H1', 'H', 1),
(326, 3, 'H2', 'H', 2),
(327, 3, 'H3', 'H', 3),
(328, 3, 'H4', 'H', 4),
(329, 3, 'H5', 'H', 5),
(330, 3, 'H6', 'H', 6),
(331, 3, 'H7', 'H', 7),
(332, 3, 'H8', 'H', 8),
(333, 3, 'H9', 'H', 9),
(334, 3, 'H10', 'H', 10),
(335, 5, 'A1', 'A', 1),
(336, 5, 'A2', 'A', 2),
(337, 5, 'A3', 'A', 3),
(338, 5, 'A4', 'A', 4),
(339, 5, 'A5', 'A', 5),
(340, 5, 'A6', 'A', 6),
(341, 5, 'A7', 'A', 7),
(342, 5, 'A8', 'A', 8),
(343, 5, 'A9', 'A', 9),
(344, 5, 'A10', 'A', 10),
(345, 5, 'B1', 'B', 1),
(346, 5, 'B2', 'B', 2),
(347, 5, 'B3', 'B', 3),
(348, 5, 'B4', 'B', 4),
(349, 5, 'B5', 'B', 5),
(350, 5, 'B6', 'B', 6),
(351, 5, 'B7', 'B', 7),
(352, 5, 'B8', 'B', 8),
(353, 5, 'B9', 'B', 9),
(354, 5, 'B10', 'B', 10),
(355, 5, 'C1', 'C', 1),
(356, 5, 'C2', 'C', 2),
(357, 5, 'C3', 'C', 3),
(358, 5, 'C4', 'C', 4),
(359, 5, 'C5', 'C', 5),
(360, 5, 'C6', 'C', 6),
(361, 5, 'C7', 'C', 7),
(362, 5, 'C8', 'C', 8),
(363, 5, 'C9', 'C', 9),
(364, 5, 'C10', 'C', 10),
(365, 5, 'D1', 'D', 1),
(366, 5, 'D2', 'D', 2),
(367, 5, 'D3', 'D', 3),
(368, 5, 'D4', 'D', 4),
(369, 5, 'D5', 'D', 5),
(370, 5, 'D6', 'D', 6),
(371, 5, 'D7', 'D', 7),
(372, 5, 'D8', 'D', 8),
(373, 5, 'D9', 'D', 9),
(374, 5, 'D10', 'D', 10),
(375, 5, 'E1', 'E', 1),
(376, 5, 'E2', 'E', 2),
(377, 5, 'E3', 'E', 3),
(378, 5, 'E4', 'E', 4),
(379, 5, 'E5', 'E', 5),
(380, 5, 'E6', 'E', 6),
(381, 5, 'E7', 'E', 7),
(382, 5, 'E8', 'E', 8),
(383, 5, 'E9', 'E', 9),
(384, 5, 'E10', 'E', 10),
(385, 5, 'F1', 'F', 1),
(386, 5, 'F2', 'F', 2),
(387, 5, 'F3', 'F', 3),
(388, 5, 'F4', 'F', 4),
(389, 5, 'F5', 'F', 5),
(390, 5, 'F6', 'F', 6),
(391, 5, 'F7', 'F', 7),
(392, 5, 'F8', 'F', 8),
(393, 5, 'F9', 'F', 9),
(394, 5, 'F10', 'F', 10),
(395, 5, 'G1', 'G', 1),
(396, 5, 'G2', 'G', 2),
(397, 5, 'G3', 'G', 3),
(398, 5, 'G4', 'G', 4),
(399, 5, 'G5', 'G', 5),
(400, 5, 'G6', 'G', 6),
(401, 5, 'G7', 'G', 7),
(402, 5, 'G8', 'G', 8),
(403, 5, 'G9', 'G', 9),
(404, 5, 'G10', 'G', 10),
(405, 5, 'H1', 'H', 1),
(406, 5, 'H2', 'H', 2),
(407, 5, 'H3', 'H', 3),
(408, 5, 'H4', 'H', 4),
(409, 5, 'H5', 'H', 5),
(410, 5, 'H6', 'H', 6),
(411, 5, 'H7', 'H', 7),
(412, 5, 'H8', 'H', 8),
(413, 5, 'H9', 'H', 9),
(414, 5, 'H10', 'H', 10),
(495, 7, 'A1', 'A', 1),
(496, 7, 'A2', 'A', 2),
(497, 7, 'A3', 'A', 3),
(498, 7, 'A4', 'A', 4),
(499, 7, 'A5', 'A', 5),
(500, 7, 'A6', 'A', 6),
(501, 7, 'A7', 'A', 7),
(502, 7, 'A8', 'A', 8),
(503, 7, 'A9', 'A', 9),
(504, 7, 'A10', 'A', 10),
(505, 7, 'B1', 'B', 1),
(506, 7, 'B2', 'B', 2),
(507, 7, 'B3', 'B', 3),
(508, 7, 'B4', 'B', 4),
(509, 7, 'B5', 'B', 5),
(510, 7, 'B6', 'B', 6),
(511, 7, 'B7', 'B', 7),
(512, 7, 'B8', 'B', 8),
(513, 7, 'B9', 'B', 9),
(514, 7, 'B10', 'B', 10),
(515, 7, 'C1', 'C', 1),
(516, 7, 'C2', 'C', 2),
(517, 7, 'C3', 'C', 3),
(518, 7, 'C4', 'C', 4),
(519, 7, 'C5', 'C', 5),
(520, 7, 'C6', 'C', 6),
(521, 7, 'C7', 'C', 7),
(522, 7, 'C8', 'C', 8),
(523, 7, 'C9', 'C', 9),
(524, 7, 'C10', 'C', 10),
(525, 7, 'D1', 'D', 1),
(526, 7, 'D2', 'D', 2),
(527, 7, 'D3', 'D', 3),
(528, 7, 'D4', 'D', 4),
(529, 7, 'D5', 'D', 5),
(530, 7, 'D6', 'D', 6),
(531, 7, 'D7', 'D', 7),
(532, 7, 'D8', 'D', 8),
(533, 7, 'D9', 'D', 9),
(534, 7, 'D10', 'D', 10),
(535, 7, 'E1', 'E', 1),
(536, 7, 'E2', 'E', 2),
(537, 7, 'E3', 'E', 3),
(538, 7, 'E4', 'E', 4),
(539, 7, 'E5', 'E', 5),
(540, 7, 'E6', 'E', 6),
(541, 7, 'E7', 'E', 7),
(542, 7, 'E8', 'E', 8),
(543, 7, 'E9', 'E', 9),
(544, 7, 'E10', 'E', 10),
(545, 7, 'F1', 'F', 1),
(546, 7, 'F2', 'F', 2),
(547, 7, 'F3', 'F', 3),
(548, 7, 'F4', 'F', 4),
(549, 7, 'F5', 'F', 5),
(550, 7, 'F6', 'F', 6),
(551, 7, 'F7', 'F', 7),
(552, 7, 'F8', 'F', 8),
(553, 7, 'F9', 'F', 9),
(554, 7, 'F10', 'F', 10),
(555, 7, 'G1', 'G', 1),
(556, 7, 'G2', 'G', 2),
(557, 7, 'G3', 'G', 3),
(558, 7, 'G4', 'G', 4),
(559, 7, 'G5', 'G', 5),
(560, 7, 'G6', 'G', 6),
(561, 7, 'G7', 'G', 7),
(562, 7, 'G8', 'G', 8),
(563, 7, 'G9', 'G', 9),
(564, 7, 'G10', 'G', 10),
(565, 7, 'H1', 'H', 1),
(566, 7, 'H2', 'H', 2),
(567, 7, 'H3', 'H', 3),
(568, 7, 'H4', 'H', 4),
(569, 7, 'H5', 'H', 5),
(570, 7, 'H6', 'H', 6),
(571, 7, 'H7', 'H', 7),
(572, 7, 'H8', 'H', 8),
(573, 7, 'H9', 'H', 9),
(574, 7, 'H10', 'H', 10);

-- --------------------------------------------------------

--
-- Struktur dari tabel `kursi_terpesan`
--

CREATE TABLE `kursi_terpesan` (
  `id` int(11) NOT NULL,
  `jadwal_id` int(11) DEFAULT NULL,
  `kursi_id` int(11) DEFAULT NULL,
  `pemesanan_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Dumping data untuk tabel `kursi_terpesan`
--

INSERT INTO `kursi_terpesan` (`id`, `jadwal_id`, `kursi_id`, `pemesanan_id`) VALUES
(32, 28, 154, 19),
(33, 28, 170, 19),
(34, 28, 187, 20),
(35, 28, 195, 20),
(40, 29, 20, 23),
(41, 29, 36, 23),
(42, 30, 152, 24),
(43, 29, 44, 25),
(44, 29, 60, 25),
(48, 27, 369, 28),
(49, 30, 144, 29),
(52, 32, 155, 31),
(53, 32, 179, 31),
(54, 40, 268, 32),
(55, 40, 304, 32),
(56, 40, 282, 33),
(57, 40, 294, 33),
(58, 38, 18, 34),
(59, 38, 50, 34),
(60, 42, 153, 35),
(61, 42, 177, 35),
(64, 39, 276, 37),
(65, 39, 294, 37),
(66, 39, 278, 38),
(67, 39, 284, 38),
(68, 39, 274, 39),
(69, 39, 286, 39),
(70, 39, 281, 40),
(71, 39, 287, 40),
(72, 39, 282, 41),
(73, 39, 288, 41);

-- --------------------------------------------------------

--
-- Struktur dari tabel `pemesanan`
--

CREATE TABLE `pemesanan` (
  `id` int(11) NOT NULL,
  `kode_booking` varchar(20) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `jadwal_id` int(11) DEFAULT NULL,
  `total_harga` decimal(10,2) DEFAULT NULL,
  `status` enum('pending','confirmed','cancelled') DEFAULT 'confirmed',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Dumping data untuk tabel `pemesanan`
--

INSERT INTO `pemesanan` (`id`, `kode_booking`, `user_id`, `jadwal_id`, `total_harga`, `status`, `created_at`) VALUES
(10, 'CMXD291B3AB', 8, 22, 20000.00, 'confirmed', '2026-05-09 15:30:28'),
(19, 'CMX0618014B', 3, 28, 20000.00, 'confirmed', '2026-05-11 07:46:48'),
(20, 'CMX7ADDB03C', 3, 28, 20000.00, 'confirmed', '2026-05-11 09:32:14'),
(21, 'CMXF6AD6341', 3, 29, 20000.00, 'confirmed', '2026-05-11 09:57:25'),
(22, 'CMX979F15CE', 3, 29, 20000.00, 'confirmed', '2026-05-11 10:02:23'),
(23, 'CMXE759DB9B', 3, 29, 20000.00, 'confirmed', '2026-05-11 10:05:03'),
(24, 'CMXDD24D9D8', 3, 30, 30000.00, 'confirmed', '2026-05-11 10:19:04'),
(25, 'CMX1DF87F39', 9, 29, 20000.00, 'confirmed', '2026-05-11 10:31:25'),
(28, 'CMXC46EE3AD', 10, 27, 30000.00, 'confirmed', '2026-05-11 11:18:17'),
(29, 'CMX31E0660B', 10, 30, 30000.00, 'confirmed', '2026-05-11 11:19:38'),
(30, 'CMXACFC355C', 11, 30, 60000.00, 'pending', '2026-05-11 14:18:45'),
(31, 'CMX214C5840', 11, 32, 60000.00, 'confirmed', '2026-05-11 21:35:05'),
(32, 'CMXD7D569DC', 3, 40, 60000.00, 'confirmed', '2026-05-12 01:20:47'),
(33, 'CMXC2135367', 3, 40, 60000.00, 'confirmed', '2026-05-12 02:30:38'),
(34, 'CMXB6435EAA', 12, 38, 80000.00, 'confirmed', '2026-05-12 03:34:01'),
(35, 'CMX8E961C3B', 12, 42, 20000.00, 'confirmed', '2026-05-12 03:37:14'),
(37, 'CMX33D5B1A5', 3, 39, 30000.00, 'confirmed', '2026-05-18 23:37:42'),
(38, 'CMX324D127C', 3, 39, 30000.00, 'confirmed', '2026-05-18 23:47:52'),
(39, 'CMXA26A54DE', 3, 39, 30000.00, 'confirmed', '2026-05-19 04:19:37'),
(40, 'CMXA24ACD88', 3, 39, 30000.00, 'confirmed', '2026-05-19 04:25:48'),
(41, 'CMX2313F5C9', 3, 39, 30000.00, 'confirmed', '2026-05-19 04:26:20');

-- --------------------------------------------------------

--
-- Struktur dari tabel `studio`
--

CREATE TABLE `studio` (
  `id` int(11) NOT NULL,
  `nama` varchar(50) NOT NULL,
  `kapasitas` int(11) DEFAULT 100
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Dumping data untuk tabel `studio`
--

INSERT INTO `studio` (`id`, `nama`, `kapasitas`) VALUES
(1, 'Studio 1', 80),
(2, 'Studio 2', 80),
(3, 'Studio 3', 80),
(5, 'Studio 4', 80),
(7, 'Studio 5', 80);

-- --------------------------------------------------------

--
-- Struktur dari tabel `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `nama` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `no_hp` varchar(20) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Dumping data untuk tabel `users`
--

INSERT INTO `users` (`id`, `nama`, `email`, `password`, `no_hp`, `created_at`) VALUES
(3, 'pais', 'paise@gmail.com', '$2y$10$g0vBpWpIXxvmRdpewgqNW.7B6J1QUtqtTrN6YXGWO902V3uUHzk6e', '098765434', '2026-05-06 03:47:21'),
(4, 'palis', 'palis@gmail.com', '$2y$10$yHXKbSu1wI/XlN58lNXFQ.enq.6NDkyps3n2IJv4M9jeTCQ4mQ8cm', '098765456', '2026-05-06 04:17:50'),
(5, 'pangis', 'pangis@gmail.com', '$2y$10$XjnIF43xXNIL.PK0elP94OTSbHTChNDAy0bNQOsvkvg5lTtxvhlh6', '098765678', '2026-05-07 07:43:14'),
(6, 'elele', 'elele@gmail.com', '$2y$10$3RN7Uriwa3fxM2A3gv6WKOh8Gj3Z0cqt7jaTaN6Sq/pdfQgj1OHry', '0183713739', '2026-05-08 00:20:36'),
(7, 'Bilal', 'bilal@gmail.com', '$2y$10$Pm4BRHZxxHwchqkRvmKlGuKqw5asbQWWmjsNHylikliMSjSkvJBRS', '', '2026-05-08 03:48:31'),
(8, 'anap', 'nanapphinopje@gmail.com', '$2y$10$kVvkpWSJD.5c4tS2iKFWGekHzHHvSzh5kd2jW6DVceykcDAk69N.G', '087710789222', '2026-05-09 15:27:04'),
(9, 'gaisan', 'ambon@gmail.com', '$2y$10$i0ZOeXBtjBUd9oG.DP4R..dDLOQBcRdel6zwDl6SMbfFgHLixXQlK', '09876543', '2026-05-11 10:21:27'),
(10, 'amay', 'amay@gmail.com', '$2y$10$e2sG/wJY/3.fcTmhMCcYFewFygaR8sw/HhahtIDWFMCxWD1wZveie', '98765678', '2026-05-11 11:16:58'),
(11, 'asmi', 'asmi@gmail.com', '$2y$10$IELrdW.hG98yP2q7PxFjDeccFuxHmeZc7CnCaamhtuL02tPZiq9Zq', '098765456', '2026-05-11 14:14:07'),
(12, 'ikbal', 'ikbal@gmail.com', '$2y$10$fsDvKhV3/816rEmUO4VtC.rpEl9tsZGvRCwXgaKOJHyBIkfsuUp3e', '876567876', '2026-05-12 03:33:04');

--
-- Indexes for dumped tables
--

--
-- Indeks untuk tabel `admin`
--
ALTER TABLE `admin`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- Indeks untuk tabel `detail_pemesanan`
--
ALTER TABLE `detail_pemesanan`
  ADD PRIMARY KEY (`id`),
  ADD KEY `pemesanan_id` (`pemesanan_id`),
  ADD KEY `kursi_id` (`kursi_id`);

--
-- Indeks untuk tabel `films`
--
ALTER TABLE `films`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `jadwal`
--
ALTER TABLE `jadwal`
  ADD PRIMARY KEY (`id`),
  ADD KEY `film_id` (`film_id`),
  ADD KEY `studio_id` (`studio_id`);

--
-- Indeks untuk tabel `kursi`
--
ALTER TABLE `kursi`
  ADD PRIMARY KEY (`id`),
  ADD KEY `studio_id` (`studio_id`);

--
-- Indeks untuk tabel `kursi_terpesan`
--
ALTER TABLE `kursi_terpesan`
  ADD PRIMARY KEY (`id`),
  ADD KEY `jadwal_id` (`jadwal_id`),
  ADD KEY `kursi_id` (`kursi_id`),
  ADD KEY `pemesanan_id` (`pemesanan_id`);

--
-- Indeks untuk tabel `pemesanan`
--
ALTER TABLE `pemesanan`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `kode_booking` (`kode_booking`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `jadwal_id` (`jadwal_id`);

--
-- Indeks untuk tabel `studio`
--
ALTER TABLE `studio`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT untuk tabel yang dibuang
--

--
-- AUTO_INCREMENT untuk tabel `admin`
--
ALTER TABLE `admin`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT untuk tabel `detail_pemesanan`
--
ALTER TABLE `detail_pemesanan`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=74;

--
-- AUTO_INCREMENT untuk tabel `films`
--
ALTER TABLE `films`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- AUTO_INCREMENT untuk tabel `jadwal`
--
ALTER TABLE `jadwal`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=43;

--
-- AUTO_INCREMENT untuk tabel `kursi`
--
ALTER TABLE `kursi`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=575;

--
-- AUTO_INCREMENT untuk tabel `kursi_terpesan`
--
ALTER TABLE `kursi_terpesan`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=74;

--
-- AUTO_INCREMENT untuk tabel `pemesanan`
--
ALTER TABLE `pemesanan`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=42;

--
-- AUTO_INCREMENT untuk tabel `studio`
--
ALTER TABLE `studio`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT untuk tabel `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- Ketidakleluasaan untuk tabel pelimpahan (Dumped Tables)
--

--
-- Ketidakleluasaan untuk tabel `detail_pemesanan`
--
ALTER TABLE `detail_pemesanan`
  ADD CONSTRAINT `detail_pemesanan_ibfk_1` FOREIGN KEY (`pemesanan_id`) REFERENCES `pemesanan` (`id`),
  ADD CONSTRAINT `detail_pemesanan_ibfk_2` FOREIGN KEY (`kursi_id`) REFERENCES `kursi` (`id`);

--
-- Ketidakleluasaan untuk tabel `jadwal`
--
ALTER TABLE `jadwal`
  ADD CONSTRAINT `jadwal_ibfk_1` FOREIGN KEY (`film_id`) REFERENCES `films` (`id`),
  ADD CONSTRAINT `jadwal_ibfk_2` FOREIGN KEY (`studio_id`) REFERENCES `studio` (`id`);

--
-- Ketidakleluasaan untuk tabel `kursi`
--
ALTER TABLE `kursi`
  ADD CONSTRAINT `kursi_ibfk_1` FOREIGN KEY (`studio_id`) REFERENCES `studio` (`id`);

--
-- Ketidakleluasaan untuk tabel `kursi_terpesan`
--
ALTER TABLE `kursi_terpesan`
  ADD CONSTRAINT `kursi_terpesan_ibfk_1` FOREIGN KEY (`jadwal_id`) REFERENCES `jadwal` (`id`),
  ADD CONSTRAINT `kursi_terpesan_ibfk_2` FOREIGN KEY (`kursi_id`) REFERENCES `kursi` (`id`),
  ADD CONSTRAINT `kursi_terpesan_ibfk_3` FOREIGN KEY (`pemesanan_id`) REFERENCES `pemesanan` (`id`);

--
-- Ketidakleluasaan untuk tabel `pemesanan`
--
ALTER TABLE `pemesanan`
  ADD CONSTRAINT `pemesanan_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `pemesanan_ibfk_2` FOREIGN KEY (`jadwal_id`) REFERENCES `jadwal` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
