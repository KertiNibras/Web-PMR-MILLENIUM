-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Mar 04, 2026 at 04:00 AM
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
(5, 1, 'Absensi Harian', '2026-02-13', '08:18:29', 'absen_1770967109_1.png', 'sakit', 'Demam Wleee', '2026-02-13 07:18:29'),
(6, 1, 'Absensi Harian', '2026-02-13', '08:18:44', 'absen_1770967124_1.png', 'hadir', 'Hadir Rapatt', '2026-02-13 07:18:44'),
(7, 3, 'Absensi Harian', '2026-02-13', '08:22:56', 'absen_1770967376_3.png', 'izin', 'Izin Acara Keluarga', '2026-02-13 07:22:56'),
(16, 3, 'Absensi Harian', '2026-02-22', '12:27:48', 'absen_1771759668_3.png', 'hadir', '', '2026-02-22 11:27:48'),
(17, 3, 'Absensi Harian', '2026-02-23', '02:35:32', 'absen_1771810532_3.png', 'hadir', 'Hadir coy', '2026-02-23 01:35:32'),
(18, 3, 'Absensi Harian', '2026-02-23', '02:39:59', 'absen_1771810799_3.png', 'sakit', 'Mouse gw razer nig boss wlee', '2026-02-23 01:39:59'),
(19, 1, 'Absensi Harian', '2026-02-23', '02:58:33', 'absen_1771811913_1.png', 'izin', 'malas', '2026-02-23 01:58:33'),
(20, 1, 'Absensi Harian', '2026-02-23', '09:08:07', 'absen_1771812487_1.png', 'hadir', 'hadir waktu', '2026-02-23 02:08:07'),
(21, 3, 'Absensi Harian', '2026-02-23', '09:16:57', 'absen_1771813017_3.png', 'hadir', '', '2026-02-23 02:16:57'),
(22, 1, 'Absensi Harian', '2026-02-24', '09:09:49', 'absen_1771898989_1.png', 'hadir', 'hadir coy', '2026-02-24 02:09:49'),
(23, 1, 'Absensi Harian', '2026-02-24', '17:55:13', 'absen_1771930513_1.png', 'hadir', 'tes absen hadir debug 24 feb', '2026-02-24 10:55:13'),
(24, 3, 'Absensi Harian', '2026-02-24', '18:39:28', 'absen_1771933168_3.png', 'izin', 'Izin debug 24 feb', '2026-02-24 11:39:28');

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
(13, 'Alasan \"Opsional\"', 'text', '[]', 0, 4, '2026-02-23 14:48:48'),
(18, 'Prestasi', 'file', '[]', 0, 4, '2026-02-27 03:49:16');

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
-- Indexes for table `pendaftaran`
--
ALTER TABLE `pendaftaran`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `perpustakaan`
--
ALTER TABLE `perpustakaan`
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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=26;

--
-- AUTO_INCREMENT for table `form_questions`
--
ALTER TABLE `form_questions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT for table `pendaftaran`
--
ALTER TABLE `pendaftaran`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT for table `perpustakaan`
--
ALTER TABLE `perpustakaan`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

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
