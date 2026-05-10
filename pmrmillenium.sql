-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: May 10, 2026 at 11:36 AM
-- Server version: 8.0.30
-- PHP Version: 8.1.10

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `pmrmillenium`
--

-- --------------------------------------------------------

--
-- Table structure for table `absensi`
--

CREATE TABLE `absensi` (
  `id` int NOT NULL,
  `user_id` int NOT NULL,
  `tanggal` date NOT NULL,
  `jam` time NOT NULL,
  `foto` varchar(255) DEFAULT NULL,
  `status` enum('hadir','izin','sakit','alpha') DEFAULT 'hadir',
  `keterangan` text,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `absensi`
--

INSERT INTO `absensi` (`id`, `user_id`, `tanggal`, `jam`, `foto`, `status`, `keterangan`, `created_at`) VALUES
(2, 35, '2026-05-09', '16:54:17', 'absen_35_1778320457.png', 'hadir', '-', '2026-05-09 09:54:17'),
(3, 38, '2026-05-10', '15:21:42', 'absen_38_1778401302.png', 'hadir', '-', '2026-05-10 08:21:42');

-- --------------------------------------------------------

--
-- Table structure for table `form_questions`
--

CREATE TABLE `form_questions` (
  `id` int NOT NULL,
  `question_text` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `question_type` enum('text','textarea','select','radio','file') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT 'text',
  `options` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `is_required` tinyint(1) DEFAULT '1',
  `ordering` int DEFAULT '0',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `form_questions`
--

INSERT INTO `form_questions` (`id`, `question_text`, `question_type`, `options`, `is_required`, `ordering`, `created_at`) VALUES
(10, 'Nama Lengkap', 'text', '[]', 1, 1, '2026-02-23 13:21:02'),
(12, 'Kelas & Jurusan', 'select', '[\"XI RPL 1\",\"XI RPL 2\",\"X RPL 1\",\"X RPL 2\"]', 1, 2, '2026-02-23 13:21:48'),
(18, 'Foto Diri *21mm x 28mm*', 'file', '[]', 0, 4, '2026-02-27 03:49:16'),
(19, 'Alasan', 'text', '[]', 1, 5, '2026-04-17 01:03:03'),
(20, 'No HP', 'text', '[]', 1, 3, '2026-05-08 06:16:56');

-- --------------------------------------------------------

--
-- Table structure for table `hero_background`
--

CREATE TABLE `hero_background` (
  `id` int NOT NULL,
  `file_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `uploaded_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `urutan` int NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `hero_background`
--

INSERT INTO `hero_background` (`id`, `file_name`, `uploaded_at`, `urutan`) VALUES
(6, '69e762f119361_background2.png', '2026-04-21 11:43:45', 0),
(7, '69e7630cc26f7_background3.png', '2026-04-21 11:44:12', 0),
(8, '69e7632ea539d_background.png', '2026-04-21 11:44:46', 0);

-- --------------------------------------------------------

--
-- Table structure for table `konten1`
--

CREATE TABLE `konten1` (
  `id` int NOT NULL,
  `judul` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `deskripsi` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `gambar` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `konten1`
--

INSERT INTO `konten1` (`id`, `judul`, `deskripsi`, `gambar`) VALUES
(9, 'Pelantikan 27', 'Materi 2', '69fab4af05154_WhatsApp Image 2026-02-04 at 11.16.40 AM.jpeg');

-- --------------------------------------------------------

--
-- Table structure for table `konten2`
--

CREATE TABLE `konten2` (
  `id` int NOT NULL,
  `judul` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `deskripsi` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `gambar` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `konten2`
--

INSERT INTO `konten2` (`id`, `judul`, `deskripsi`, `gambar`) VALUES
(6, 'Lomba SMAN 9 Bogor', 'LCT Putri', '69fab53ed0dac_kemala.jpeg');

-- --------------------------------------------------------

--
-- Table structure for table `pendaftaran`
--

CREATE TABLE `pendaftaran` (
  `id` int NOT NULL,
  `nama_lengkap` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `kelas` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `jurusan` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `no_whatsapp` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `answers` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin,
  `status` enum('pending','diterima','ditolak') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'pending',
  `generated_username` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `generated_password` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `card_sent` tinyint(1) NOT NULL DEFAULT '0',
  `submission_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `pendaftaran`
--

INSERT INTO `pendaftaran` (`id`, `nama_lengkap`, `kelas`, `jurusan`, `no_whatsapp`, `answers`, `status`, `generated_username`, `generated_password`, `card_sent`, `submission_date`) VALUES
(42, 'Bama Kerti', 'XI RPL 1', '-', '081280274480', '{\"Nama Lengkap\":\"Bama Kerti\",\"Kelas & Jurusan\":\"XI RPL 1\",\"No HP\":\"081280274480\",\"Alasan\":\"pengen\",\"Foto Diri *21mm x 28mm*\":\"question_file\\/file_1778401061_878.png\"}', 'diterima', 'bamakerti', '5912c391', 0, '2026-05-10 08:17:41');

-- --------------------------------------------------------

--
-- Table structure for table `pengaturan_absensi`
--

CREATE TABLE `pengaturan_absensi` (
  `id` int NOT NULL,
  `tanggal` date NOT NULL,
  `waktu_mulai` time NOT NULL,
  `waktu_selesai` time NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `pengaturan_absensi`
--

INSERT INTO `pengaturan_absensi` (`id`, `tanggal`, `waktu_mulai`, `waktu_selesai`) VALUES
(8, '2026-05-10', '15:20:00', '18:00:00');

-- --------------------------------------------------------

--
-- Table structure for table `pengurus`
--

CREATE TABLE `pengurus` (
  `id` int NOT NULL,
  `nama` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `jabatan` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `kelas` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `logo_kelas` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT 'rpl.png',
  `foto` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT 'default.jpg',
  `urutan` int DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `pengurus`
--

INSERT INTO `pengurus` (`id`, `nama`, `jabatan`, `kelas`, `logo_kelas`, `foto`, `urutan`) VALUES
(7, 'Kemala Putri Oktaviani', 'Ketua PMR', 'XI RPL 1', 'rpl.png', '69e17a18a2d60.jpeg', 1),
(8, 'Muhammad Alif Alghifari', 'Wakil Ketua', 'X RPL 1', 'rpl.png', '69f151abd4359.jpeg', 2),
(9, 'Mochamad Naufal Hanif', 'Sekretaris 1', 'XI RPL 1', 'rpl.png', '69f151cba6ace.jpeg', 3),
(10, 'Aurelia Zahra', 'Sekretaris 2', 'X DKV 3', 'dkv.png', '69f1521492db3.jpeg', 4),
(11, 'Sharhana Hajarani', 'Bendahara 1', 'XI DPIB 1', 'dpib.png', '69f15224a982f.jpeg', 5),
(12, 'Anindia Rahma Alliya', 'Bendahara 2', ' X RPL 1', 'rpl.png', '69f1532500d35.jpeg', 6);

-- --------------------------------------------------------

--
-- Table structure for table `perpustakaan`
--

CREATE TABLE `perpustakaan` (
  `id` int NOT NULL,
  `judul` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `deskripsi` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `kategori` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `file_pdf` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `perpustakaan`
--

INSERT INTO `perpustakaan` (`id`, `judul`, `deskripsi`, `kategori`, `file_pdf`, `created_at`) VALUES
(16, 'Cara Self Healing', 'Cara Self Healing tanpa bantuan orang lain jadi anda bisa revive diri sendiri.', 'P3K', '1778321875_Mengenal_Gerakan.pdf', '2026-05-09 10:17:55'),
(17, 'Panduan Fasilitator PMR', ' Panduan Fasilitator PMR - Kepemimpinan', 'Kepalangmerahan', '1778321986_Buku_PMI___Panduan_Fasilitator_PMR___Kepemimpinan.pdf', '2026-05-09 10:19:46');

-- --------------------------------------------------------

--
-- Table structure for table `settings`
--

CREATE TABLE `settings` (
  `setting_key` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `setting_value` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `settings`
--

INSERT INTO `settings` (`setting_key`, `setting_value`) VALUES
('footer_copyright', '© 2026 PMR Millenium SMKN 1 Cibinong. All Rights Reserved.'),
('hero_delay', '5000'),
('hero_effect', 'slide');

-- --------------------------------------------------------

--
-- Table structure for table `social_links`
--

CREATE TABLE `social_links` (
  `id` int NOT NULL,
  `platform` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `url` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `icon_url` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `urutan` int DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tentang_pmr`
--

CREATE TABLE `tentang_pmr` (
  `id` int NOT NULL,
  `visi` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `misi` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `program_kerja` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tentang_pmr`
--

INSERT INTO `tentang_pmr` (`id`, `visi`, `misi`, `program_kerja`) VALUES
(1, 'Mewujudkan ekstrakurikuler PMR sebagai organisasi yang peduli terhadap sesama, menciptakan persahabatan erat, dan harmonis antara anggota PMR Millenium.', 'Konten mingguan edukasi kesehatan di Instagram.\r\nMenyelenggarakan \"Semangat Juang Remaja\".\r\nVariasi latihan rutin (Tandu Estafet, dll).\r\nSosialisasi kesehatan di lingkungan sekolah.', 'Menjadi organisasi yang solid dan inovatif.\r\nMenumbuhkan kepedulian sosial & empati.\r\nMenjadi contoh teladan bagi masyarakat.');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int NOT NULL,
  `username` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `nama` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `kelas` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `password` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `foto_profil` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT 'default.jpg',
  `role` enum('anggota','pengurus') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `first_login` tinyint(1) DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `nama`, `kelas`, `password`, `foto_profil`, `role`, `first_login`) VALUES
(2, 'pengurus', 'Naufal Hanif', NULL, '$2y$10$Cn8jB90ByPm3DsMBdyZfUuvdSSEHteMSg/IaWKUhCvv4jNc39Rgaq', NULL, 'pengurus', 0),
(38, 'bamakerti', 'Bama Kerti', 'XI RPL 1', '$2y$10$laPoNnSAPKiGR0pQzfZSvONuHPpw7IVEJTYKlhrrlDoiZOIr/6DH2', 'question_file/file_1778401061_878.png', 'anggota', 0);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `absensi`
--
ALTER TABLE `absensi`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `form_questions`
--
ALTER TABLE `form_questions`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `hero_background`
--
ALTER TABLE `hero_background`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `konten1`
--
ALTER TABLE `konten1`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `konten2`
--
ALTER TABLE `konten2`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `pendaftaran`
--
ALTER TABLE `pendaftaran`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `pengaturan_absensi`
--
ALTER TABLE `pengaturan_absensi`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `pengurus`
--
ALTER TABLE `pengurus`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `perpustakaan`
--
ALTER TABLE `perpustakaan`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `settings`
--
ALTER TABLE `settings`
  ADD PRIMARY KEY (`setting_key`);

--
-- Indexes for table `tentang_pmr`
--
ALTER TABLE `tentang_pmr`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `absensi`
--
ALTER TABLE `absensi`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `form_questions`
--
ALTER TABLE `form_questions`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT for table `hero_background`
--
ALTER TABLE `hero_background`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `konten1`
--
ALTER TABLE `konten1`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `konten2`
--
ALTER TABLE `konten2`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `pendaftaran`
--
ALTER TABLE `pendaftaran`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=43;

--
-- AUTO_INCREMENT for table `pengaturan_absensi`
--
ALTER TABLE `pengaturan_absensi`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `pengurus`
--
ALTER TABLE `pengurus`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `perpustakaan`
--
ALTER TABLE `perpustakaan`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT for table `tentang_pmr`
--
ALTER TABLE `tentang_pmr`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=39;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
