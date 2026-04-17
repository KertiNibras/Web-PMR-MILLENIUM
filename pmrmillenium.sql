-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Apr 17, 2026 at 03:11 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

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
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `kegiatan` varchar(100) NOT NULL,
  `tanggal` date NOT NULL,
  `jam` time NOT NULL,
  `foto` varchar(255) DEFAULT NULL,
  `status` enum('hadir','izin','sakit','alpha') DEFAULT 'hadir',
  `keterangan` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `absensi`
--

INSERT INTO `absensi` (`id`, `user_id`, `kegiatan`, `tanggal`, `jam`, `foto`, `status`, `keterangan`, `created_at`) VALUES
(27, 1, '', '2026-03-04', '13:39:14', 'absen_1_1772606354.png', 'hadir', '-', '2026-03-04 06:39:14');

-- --------------------------------------------------------

--
-- Table structure for table `form_questions`
--

CREATE TABLE `form_questions` (
  `id` int(11) NOT NULL,
  `question_text` varchar(255) NOT NULL,
  `question_type` enum('text','textarea','select','radio','file') DEFAULT 'text',
  `options` text DEFAULT NULL,
  `is_required` tinyint(1) DEFAULT 1,
  `ordering` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `form_questions`
--

INSERT INTO `form_questions` (`id`, `question_text`, `question_type`, `options`, `is_required`, `ordering`, `created_at`) VALUES
(10, 'Nama Lengkap', 'text', '[]', 1, 1, '2026-02-23 13:21:02'),
(12, 'Kelas & Jurusan', 'select', '[\"XI RPL 1\",\"XI RPL 2\",\"X RPL 1\",\"X RPL 2\"]', 1, 2, '2026-02-23 13:21:48'),
(18, 'Prestasi', 'file', '[]', 0, 4, '2026-02-27 03:49:16'),
(19, 'Alasan', 'text', '[]', 1, 4, '2026-04-17 01:03:03');

-- --------------------------------------------------------

--
-- Table structure for table `hero_background`
--

CREATE TABLE `hero_background` (
  `id` int(11) NOT NULL,
  `file_name` varchar(255) NOT NULL,
  `uploaded_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `hero_background`
--

INSERT INTO `hero_background` (`id`, `file_name`, `uploaded_at`) VALUES
(1, 'background.png', '2026-04-16 05:49:59'),
(2, 'Windows 7 cat user profile.jpg', '2026-04-16 05:53:16'),
(3, '69e1797ce3fc7_background.png', '2026-04-17 00:06:20');

-- --------------------------------------------------------

--
-- Table structure for table `kegiatan`
--

CREATE TABLE `kegiatan` (
  `id` int(11) NOT NULL,
  `judul` varchar(100) NOT NULL,
  `deskripsi` text NOT NULL,
  `gambar` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `lomba`
--

CREATE TABLE `lomba` (
  `id` int(11) NOT NULL,
  `judul` varchar(100) NOT NULL,
  `deskripsi` text NOT NULL,
  `gambar` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `pendaftaran`
--

CREATE TABLE `pendaftaran` (
  `id` int(11) NOT NULL,
  `nama_lengkap` varchar(100) NOT NULL,
  `kelas` varchar(20) NOT NULL,
  `jurusan` varchar(50) NOT NULL,
  `no_whatsapp` varchar(20) NOT NULL,
  `answers` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`answers`)),
  `submission_date` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `pengaturan_absensi`
--

CREATE TABLE `pengaturan_absensi` (
  `id` int(11) NOT NULL,
  `tanggal` date NOT NULL,
  `jam_mulai` time NOT NULL,
  `jam_selesai` time NOT NULL,
  `status` enum('aktif','tidak') NOT NULL DEFAULT 'tidak'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `pengaturan_absensi`
--

INSERT INTO `pengaturan_absensi` (`id`, `tanggal`, `jam_mulai`, `jam_selesai`, `status`) VALUES
(0, '2026-03-04', '13:28:00', '14:00:00', 'tidak');

-- --------------------------------------------------------

--
-- Table structure for table `pengurus`
--

CREATE TABLE `pengurus` (
  `id` int(11) NOT NULL,
  `nama` varchar(100) NOT NULL,
  `jabatan` varchar(50) NOT NULL,
  `kelas` varchar(20) NOT NULL,
  `logo_kelas` varchar(50) DEFAULT 'rpl.png',
  `foto` varchar(255) DEFAULT 'default.jpg',
  `urutan` int(11) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `pengurus`
--

INSERT INTO `pengurus` (`id`, `nama`, `jabatan`, `kelas`, `logo_kelas`, `foto`, `urutan`) VALUES
(7, 'Kemala Putri Oktaviani', 'Ketua PMR', 'XI RPL 1', 'rpl.png', '69e17a18a2d60.jpeg', 1),
(8, 'Muhammad Alif Alghifari', 'Wakil Ketua', 'X RPL 1', 'rpl.png', '69e17a78ec0ff.png', 2),
(9, 'Mochammad Naufal Hanif', 'Sekretaris 1', 'XI RPL 1', 'rpl.png', '69e17aabdb4f4.jpg', 3),
(10, 'Aurelia Zahra', 'Sekretaris 2', 'X DKV 3', 'dkv.png', '69e17ade6199f.png', 4),
(11, 'Sharhana Hajarani', 'Bendahara 1', 'XI DPIB 1', 'dpib.png', '69e17b6e50a20.png', 5),
(12, 'Anindia Rahma Alliya', 'Bendahara 2', ' X RPL 1', 'rpl.png', '69e17bb142340.png', 6);

-- --------------------------------------------------------

--
-- Table structure for table `perpustakaan`
--

CREATE TABLE `perpustakaan` (
  `id` int(11) NOT NULL,
  `judul` varchar(255) NOT NULL,
  `deskripsi` text DEFAULT NULL,
  `kategori` varchar(100) DEFAULT NULL,
  `file_pdf` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `perpustakaan`
--

INSERT INTO `perpustakaan` (`id`, `judul`, `deskripsi`, `kategori`, `file_pdf`, `created_at`) VALUES
(2, 'Cara Self Healing', 'Self Healing adalah ketika kamu sedang knock, kamu bisa menyembuhkan diri tanpa bantuan orang lain.', 'Kepalangmerahan', '1771747666_Laporan_Absensi.pdf', '2026-02-22 08:07:46');

-- --------------------------------------------------------

--
-- Table structure for table `tentang_pmr`
--

CREATE TABLE `tentang_pmr` (
  `id` int(11) NOT NULL,
  `visi` text NOT NULL,
  `misi` text NOT NULL,
  `program_kerja` text NOT NULL
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
  `id` int(11) NOT NULL,
  `username` varchar(30) NOT NULL,
  `nama` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `foto_profil` varchar(255) DEFAULT 'default.jpg',
  `role` enum('anggota','pengurus') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `nama`, `password`, `foto_profil`, `role`) VALUES
(1, '1024012342', 'Bama Kerti', 'anggota123', 'user_1.jpg', 'anggota'),
(2, 'pengurus', 'Naufal Hanif', 'pengurus123', 'user_2.jpg', 'pengurus'),
(3, '1024012321', 'Inka Dayungitas', 'anggota1234', 'default.jpg', 'anggota');

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
-- Indexes for table `kegiatan`
--
ALTER TABLE `kegiatan`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `lomba`
--
ALTER TABLE `lomba`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `pendaftaran`
--
ALTER TABLE `pendaftaran`
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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=28;

--
-- AUTO_INCREMENT for table `form_questions`
--
ALTER TABLE `form_questions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT for table `hero_background`
--
ALTER TABLE `hero_background`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `kegiatan`
--
ALTER TABLE `kegiatan`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `lomba`
--
ALTER TABLE `lomba`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `pendaftaran`
--
ALTER TABLE `pendaftaran`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT for table `pengurus`
--
ALTER TABLE `pengurus`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `perpustakaan`
--
ALTER TABLE `perpustakaan`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `tentang_pmr`
--
ALTER TABLE `tentang_pmr`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

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
