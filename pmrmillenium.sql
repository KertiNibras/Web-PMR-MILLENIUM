-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: May 08, 2026 at 01:35 PM
-- Server version: 8.0.30
-- PHP Version: 8.3.30

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
  `foto` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `status` enum('hadir','izin','sakit','alpha') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT 'hadir',
  `keterangan` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `absensi`
--

INSERT INTO `absensi` (`id`, `user_id`, `tanggal`, `jam`, `foto`, `status`, `keterangan`, `created_at`) VALUES
(27, 1, '2026-03-04', '13:39:14', 'absen_1_1772606354.png', 'hadir', '-', '2026-03-04 06:39:14'),
(29, 1, '2026-04-22', '08:11:14', 'absen_1_1776820274.png', 'hadir', '-', '2026-04-22 01:11:14');

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
(18, 'Foto Diri', 'file', '[]', 0, 4, '2026-02-27 03:49:16'),
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `pendaftaran`
--

INSERT INTO `pendaftaran` (`id`, `nama_lengkap`, `kelas`, `jurusan`, `no_whatsapp`, `answers`, `status`, `generated_username`, `generated_password`, `card_sent`, `submission_date`) VALUES
(8, 'Salatin nibras bama kerti', 'XI RPL 2', '-', '-', '{\"Nama Lengkap\":\"Salatin nibras bama kerti\",\"Kelas & Jurusan\":\"XI RPL 2\",\"No HP\":\"+628174707280\",\"Alasan\":\"Penasaran\",\"Foto Diri\":\"question_file\\/file_1778224464_856.jpg\"}', 'diterima', 'salatinnibrasbamakerti179', 'dcd7b277', 1, '2026-05-08 07:14:24');

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
(1, '2026-08-05', '17:45:00', '19:30:00'),
(2, '2026-03-05', '11:05:00', '11:10:00');

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
(15, 'Cara Self Healing', 'ada', 'P3K', '1777429808_Mengenal-Gerakan.pdf', '2026-04-29 02:29:43');

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
  `role` enum('anggota','pengurus') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `nama`, `kelas`, `password`, `foto_profil`, `role`) VALUES
(1, '1024012342', 'Bama Kerti', 'XI RPL 1', 'anggota123', 'user_1.jpg', 'anggota'),
(2, 'pengurus', 'Naufal Hanif', NULL, 'pengurus123', NULL, 'pengurus'),
(3, '1024012321', 'Inka Dayungitas', 'XI DKV 1', 'anggota1234', 'default.jpg', 'anggota'),
(4, 'ada', 'ada', 'XI RPL 1', '$2y$10$ERJnyWgYrckGlvyJdicYSOdKS7CqQo8g.NzSJh75gSIju7PUcONsS', '', 'anggota'),
(5, 'salatinnibrasbamakerti', 'Salatin nibras bama kerti', 'XI RPL 1', '$2y$10$x/pFWGrKFjVn0mIjbec.s.4tuIJMd3JmYc2FR0XxfS9iNyMdJNlw2', 'question_file/file_1778222096_769.jpg', 'anggota'),
(6, 'kertia', 'Kertia', 'X RPL 2', '$2y$10$NZsZoF6idV3.5DqTPVbRw.6XBwgVzAFUUHtxvi20wj1F3NFC2d.F6', '', 'anggota'),
(7, 'bamakerti', 'Bama Kerti', 'XI RPL 2', '$2y$10$QeXOUSfKM7eNUCz8Jb8Q/eKGuxB9yiSUzo3sJDhIsgts7lB5mYY9W', '', 'anggota'),
(8, 'salatinnibrasbamakerti503', 'Salatin nibras bama kerti', 'XI RPL 2', '$2y$10$Pz5TgXKgsTgTFmc9VU216.kBLnhE.oZD0yAopz4lsCK0vVkfPYqVe', '', 'anggota'),
(9, 'salatinnibrasbamakerti902', 'Salatin nibras bama kerti', 'XI RPL 1', '$2y$10$UwnOqfhvfXwPY9/hx1owsOQvwvx9GF6j5R8mPQ2H31Ze/r.Yr8X5u', 'question_file/file_1778223811_305.jpg', 'anggota'),
(10, 'salatinnibrasbamakerti829', 'Salatin nibras bama kerti', 'XI RPL 1', '$2y$10$13qccfAh3eojdZ55QPJAmu5FPeoOfw5W/.vqctRqi/QGoGJkMqwOi', 'question_file/file_1778224227_729.jpg', 'anggota'),
(11, 'salatinnibrasbamakerti776', 'Salatin nibras bama kerti', 'XI RPL 1', '$2y$10$Ot1tm/ywAO3hYNxOyzpY4ONjq4ZLloIawBrinff4yNVrcjDKAlpTu', 'question_file/file_1778224402_764.jpg', 'anggota'),
(12, 'salatinnibrasbamakerti179', 'Salatin nibras bama kerti', 'XI RPL 2', '$2y$10$h58fLuAEa/WtU7v7dbylFetghq7uX/U1QO5nn4rchFtvz1dH3HpmW', 'question_file/file_1778224464_856.jpg', 'anggota');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `absensi`
--
ALTER TABLE `absensi`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

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
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=30;

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
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `pengaturan_absensi`
--
ALTER TABLE `pengaturan_absensi`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `pengurus`
--
ALTER TABLE `pengurus`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `perpustakaan`
--
ALTER TABLE `perpustakaan`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `tentang_pmr`
--
ALTER TABLE `tentang_pmr`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `absensi`
--
ALTER TABLE `absensi`
  ADD CONSTRAINT `absensi_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
