<?php
session_start();
require_once __DIR__ . '/../koneksi.php';

// 1. Cek apakah user sudah login
if (!isset($_SESSION['nama'])) {
  echo '<script type="text/javascript">';
  echo 'window.location.href = "../Login/login.php";';
  echo '</script>';
  exit;
}

// 2. AMBIL ROLE & DATA USER
$role = isset($_SESSION['role']) ? $_SESSION['role'] : 'anggota';
$user_id = isset($_SESSION['id']) ? $_SESSION['id'] : 0;
$nama_user = htmlspecialchars($_SESSION['nama']);

$foto_session = isset($_SESSION['foto']) ? $_SESSION['foto'] : '';
$foto_profil = 'https://ui-avatars.com/api/?name=' . urlencode($nama_user) . '&background=d90429&color=fff';

if (!empty($foto_session)) {
  $path_foto = "../uploads/foto_profil/" . $foto_session;
  if (file_exists($path_foto)) {
    $foto_profil = $path_foto . "?t=" . time();
  }
}

// ========================================================
// CEK FIRST LOGIN DARI SESSION (Dikirim dari login.php)
// ========================================================
$is_first_login = false;
if (isset($_SESSION['first_login']) && $_SESSION['first_login'] == 1) {
  $is_first_login = true;
}

// ========================================================
// 3. LOGIKA STATISTIK REALTIME
// ========================================================
$stat_total_anggota = 0;
$stat_total_hadir_bulan_ini = 0;
$stat_total_buku = 0;
$stat_pendaftaran_baru = 0;
$stat_hadir_saya = 0;
$stat_total_pertemuan = 0;
$stat_persentase = 0;
$stat_status = '-';

try {
  if ($role == 'pengurus') {
    $q_anggota = mysqli_query($koneksi, "SELECT COUNT(*) as total FROM users WHERE role='anggota'");
    if ($q_anggota) {
      $d_anggota = mysqli_fetch_assoc($q_anggota);
      $stat_total_anggota = $d_anggota['total'] ?? 0;
    }

    $bulan_ini = date('m');
    $tahun_ini = date('Y');
    $q_hadir = mysqli_query($koneksi, "SELECT COUNT(*) as total FROM absensi WHERE MONTH(tanggal)='$bulan_ini' AND YEAR(tanggal)='$tahun_ini'");
    if ($q_hadir) {
      $d_hadir = mysqli_fetch_assoc($q_hadir);
      $stat_total_hadir_bulan_ini = $d_hadir['total'] ?? 0;
    }

    $q_buku = mysqli_query($koneksi, "SELECT COUNT(*) as total FROM perpustakaan");
    if ($q_buku) {
      $d_buku = mysqli_fetch_assoc($q_buku);
      $stat_total_buku = $d_buku['total'] ?? 0;
    }

    // PERBAIKAN: Cek dari tabel 'pendaftaran' yang statusnya PENDING
    $q_daftar = mysqli_query($koneksi, "SELECT COUNT(*) as total FROM pendaftaran WHERE status='PENDING'");
    if ($q_daftar) {
      $d_daftar = mysqli_fetch_assoc($q_daftar);
      $stat_pendaftaran_baru = $d_daftar['total'] ?? 0;
    }
  } else {
    $q_hadir_saya = mysqli_query($koneksi, "SELECT COUNT(*) as total FROM absensi WHERE user_id='$user_id'");
    if ($q_hadir_saya) {
      $d_hadir_saya = mysqli_fetch_assoc($q_hadir_saya);
      $stat_hadir_saya = $d_hadir_saya['total'] ?? 0;
    }

    $q_pertemuan = mysqli_query($koneksi, "SELECT COUNT(DISTINCT tanggal) as total FROM absensi");
    if ($q_pertemuan) {
      $d_pertemuan = mysqli_fetch_assoc($q_pertemuan);
      $stat_total_pertemuan = $d_pertemuan['total'] ?? 0;
    }

    $stat_persentase = ($stat_total_pertemuan > 0) ? round(($stat_hadir_saya / $stat_total_pertemuan) * 100) : 0;
    $stat_status = ($stat_persentase >= 80) ? 'Baik' : 'Perlu Ditingkatkan';
  }
} catch (Exception $e) {
  // error_log($e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="id">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Dashboard - PMR Millenium</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
  <link rel="icon" href="../Gambar/logpmi.png" type="image/png">
  <style>
    :root {
      --primary-color: #d90429;
      --primary-hover: #c92a2a;
      --bg-color: #f8f9fa;
      --card-bg: #ffffff;
      --text-color: #1e293b;
      --text-muted: #64748b;
      --border-color: #e2e8f0;
      --shadow-sm: 0 1px 3px rgba(0, 0, 0, 0.05);
      --shadow-md: 0 4px 6px rgba(0, 0, 0, 0.05);
      --shadow-lg: 0 10px 25px rgba(0, 0, 0, 0.15);
      --radius: 12px;
      --header-height: 70px;
      --sidebar-width: 250px;
      --stat-blue: #3b82f6;
      --stat-green: #10b981;
      --stat-orange: #f59e0b;
      --stat-purple: #8b5cf6;
    }

    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
    }

    body {
      font-family: 'Inter', 'Segoe UI', sans-serif;
      background-color: var(--bg-color);
      color: var(--text-color);
      line-height: 1.6;
    }

    a {
      text-decoration: none;
      color: inherit;
    }

    ul {
      list-style: none;
    }

    header {
      background: #fff;
      box-shadow: var(--shadow-sm);
      position: fixed;
      width: 100%;
      top: 0;
      z-index: 1000;
      height: var(--header-height);
    }

    .navbar {
      display: flex;
      justify-content: space-between;
      align-items: center;
      height: 100%;
      padding: 0 20px;
      max-width: 100%;
    }

    .nav-left {
      flex: 1;
      display: flex;
      justify-content: flex-start;
      align-items: center;
    }

    .logo {
      display: flex;
      align-items: center;
      gap: 10px;
      font-weight: 700;
      font-size: 18px;
      color: #000;
    }

    .logo img {
      height: 40px;
    }

    .nav-center {
      flex: 1;
      display: flex;
      justify-content: center;
      align-items: center;
    }

    .nav-right {
      flex: 1;
      display: flex;
      justify-content: flex-end;
      align-items: center;
      gap: 15px;
      position: relative;
    }

    .profile-btn {
      display: flex;
      align-items: center;
      gap: 10px;
      cursor: pointer;
      padding: 5px 10px;
      border-radius: 50px;
      transition: background 0.2s;
    }

    .profile-btn:hover {
      background-color: #f1f5f9;
    }

    .profile-greeting {
      display: flex;
      flex-direction: column;
      align-items: flex-end;
      line-height: 1.2;
    }

    .profile-greeting small {
      color: var(--text-muted);
      font-size: 0.75rem;
      font-weight: 500;
      text-transform: capitalize;
    }

    .profile-greeting span {
      font-weight: 600;
      font-size: 0.9rem;
      color: var(--text-color);
    }

    .profile-img {
      width: 40px;
      height: 40px;
      border-radius: 50%;
      object-fit: cover;
      border: 2px solid var(--primary-color);
      flex-shrink: 0;
    }

    .profile-dropdown {
      position: absolute;
      top: 100%;
      right: 0;
      margin-top: 10px;
      background: #fff;
      border-radius: 8px;
      box-shadow: var(--shadow-lg);
      width: 220px;
      z-index: 1001;
      opacity: 0;
      visibility: hidden;
      transform: translateY(-10px);
      transition: all 0.2s ease;
      border: 1px solid var(--border-color);
      overflow: hidden;
    }

    .profile-dropdown.active {
      opacity: 1;
      visibility: visible;
      transform: translateY(0);
    }

    .dropdown-header {
      padding: 15px;
      background: #f8f9fa;
      border-bottom: 1px solid var(--border-color);
    }

    .dropdown-header p {
      font-weight: 600;
      color: var(--text-color);
      font-size: 0.9rem;
    }

    .dropdown-header small {
      color: var(--text-muted);
      font-size: 0.75rem;
    }

    .profile-dropdown ul li a {
      display: flex;
      align-items: center;
      gap: 10px;
      padding: 12px 15px;
      color: var(--text-color);
      font-size: 0.9rem;
      transition: 0.2s;
    }

    .profile-dropdown ul li a:hover {
      background-color: #fff1f1;
      color: var(--primary-color);
    }

    .profile-dropdown ul li a i {
      width: 20px;
      text-align: center;
    }

    .menu-toggle {
      display: none;
      background: none;
      border: none;
      font-size: 24px;
      cursor: pointer;
      color: var(--primary-color);
      z-index: 1001;
    }

    .dashboard-container {
      display: flex;
      min-height: 100vh;
      padding-top: var(--header-height);
    }

    .sidebar {
      width: var(--sidebar-width);
      background: #fff;
      border-right: 1px solid var(--border-color);
      position: sticky;
      top: var(--header-height);
      height: calc(100vh - var(--header-height));
      overflow-y: auto;
      z-index: 900;
      flex-shrink: 0;
    }

    .sidebar li {
      padding: 14px 25px;
      cursor: pointer;
      color: var(--text-color);
      font-weight: 500;
      display: flex;
      align-items: center;
      gap: 12px;
      border-left: 4px solid transparent;
      transition: all 0.2s;
    }

    .sidebar li:hover,
    .sidebar li.active {
      background-color: #fff1f1;
      color: var(--primary-color);
      border-left-color: var(--primary-color);
    }

    .sidebar a {
      display: flex;
      align-items: center;
      gap: 10px;
      width: 100%;
    }

    .main-content {
      flex: 1;
      padding: 30px;
      width: 100%;
    }

    .dashboard-welcome {
      margin-bottom: 30px;
    }

    .dashboard-welcome h1 {
      font-size: 1.75rem;
      color: var(--primary-color);
      margin-bottom: 5px;
    }

    .stats-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
      gap: 20px;
      margin-bottom: 35px;
    }

    .stat-card {
      background: var(--card-bg);
      padding: 20px;
      border-radius: var(--radius);
      box-shadow: var(--shadow-sm);
      border: 1px solid var(--border-color);
      display: flex;
      align-items: center;
      gap: 20px;
      transition: transform 0.2s;
    }

    .stat-card:hover {
      transform: translateY(-3px);
    }

    /* HIGHLIGHT UNTUK PENDAFTARAN BARU */
    .stat-card.highlight-new {
      animation: pulseGlow 2s infinite;
      border-color: var(--stat-purple);
    }

    @keyframes pulseGlow {
      0% {
        box-shadow: 0 0 0 0 rgba(139, 92, 246, 0.4);
      }

      50% {
        box-shadow: 0 0 15px 5px rgba(139, 92, 246, 0.2);
      }

      100% {
        box-shadow: 0 0 0 0 rgba(139, 92, 246, 0.4);
      }
    }

    @keyframes shake {
      0% {
        transform: rotate(0deg);
      }

      25% {
        transform: rotate(-10deg);
      }

      50% {
        transform: rotate(10deg);
      }

      75% {
        transform: rotate(-10deg);
      }

      100% {
        transform: rotate(0deg);
      }
    }

    .stat-card.highlight-new .stat-icon {
      animation: shake 1.5s ease-in-out infinite;
    }

    .notif-badge {
      position: absolute;
      top: -8px;
      right: -8px;
      background: #d90429;
      color: white;
      font-size: 0.75rem;
      font-weight: 700;
      width: 22px;
      height: 22px;
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
      border: 2px solid #fff;
      box-shadow: 0 2px 5px rgba(0, 0, 0, 0.2);
    }

    .stat-icon-wrapper {
      position: relative;
      display: flex;
      align-items: center;
      justify-content: center;
    }

    /* END HIGHLIGHT */

    .stat-icon {
      width: 56px;
      height: 56px;
      border-radius: 12px;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 1.5rem;
      color: #fff;
      flex-shrink: 0;
    }

    .stat-info h2 {
      font-size: 1.5rem;
      margin-bottom: 2px;
      color: var(--text-color);
    }

    .stat-info p {
      font-size: 0.85rem;
      color: var(--text-muted);
      font-weight: 500;
    }

    .bg-red {
      background-color: var(--primary-color);
    }

    .bg-blue {
      background-color: var(--stat-blue);
    }

    .bg-green {
      background-color: var(--stat-green);
    }

    .bg-orange {
      background-color: var(--stat-orange);
    }

    .bg-purple {
      background-color: var(--stat-purple);
    }

    .cards {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
      gap: 25px;
    }

    .card {
      background: var(--card-bg);
      border-radius: var(--radius);
      padding: 25px;
      box-shadow: var(--shadow-sm);
      border: 1px solid var(--border-color);
      transition: all 0.3s ease;
      display: flex;
      flex-direction: column;
      align-items: flex-start;
      text-align: left;
    }

    .card:hover {
      transform: translateY(-5px);
      box-shadow: var(--shadow-md);
      border-color: rgba(217, 4, 41, 0.3);
    }

    .card-icon-wrapper {
      width: 48px;
      height: 48px;
      background-color: #ffebee;
      border-radius: 10px;
      display: flex;
      align-items: center;
      justify-content: center;
      color: var(--primary-color);
      font-size: 1.4rem;
      margin-bottom: 20px;
    }

    .card h3 {
      font-size: 1.15rem;
      margin-bottom: 8px;
      color: var(--text-color);
    }

    .card p {
      font-size: 0.9rem;
      color: var(--text-muted);
      margin-bottom: 20px;
      flex-grow: 1;
    }

    .card-btn {
      background-color: var(--primary-color);
      color: white;
      padding: 10px 20px;
      border-radius: 8px;
      font-weight: 600;
      font-size: 0.9rem;
      transition: 0.3s;
      border: none;
      cursor: pointer;
    }

    .card-btn:hover {
      background-color: var(--primary-hover);
    }

    .notification {
      position: fixed;
      top: 85px;
      right: 20px;
      background: white;
      border-left: 5px solid #10b981;
      color: #333;
      padding: 15px 20px;
      border-radius: 4px;
      box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
      z-index: 1100;
      display: none;
      align-items: center;
      gap: 12px;
      animation: slideIn 0.4s ease forwards;
      min-width: 280px;
    }

    .notification i {
      color: #10b981;
      font-size: 1.2rem;
    }

    @keyframes slideIn {
      from {
        opacity: 0;
        transform: translateX(50px);
      }

      to {
        opacity: 1;
        transform: translateX(0);
      }
    }

    @keyframes fadeOut {
      to {
        opacity: 0;
        transform: translateX(50px);
      }
    }

    /* Modal Logout */
    .modal-overlay {
      position: fixed;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      background: rgba(0, 0, 0, 0.5);
      backdrop-filter: blur(3px);
      z-index: 2000;
      display: none;
      align-items: center;
      justify-content: center;
      opacity: 0;
      transition: opacity 0.3s ease;
    }

    .modal-overlay.active {
      display: flex;
      opacity: 1;
    }

    .modal-box {
      background: #fff;
      padding: 40px 30px;
      border-radius: var(--radius);
      width: 90%;
      max-width: 400px;
      text-align: center;
      box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2);
      border: 1px solid var(--border-color);
      transform: scale(0.9);
      transition: transform 0.3s ease;
    }

    .modal-overlay.active .modal-box {
      transform: scale(1);
    }

    .modal-icon {
      width: 80px;
      height: 80px;
      background: #fee2e2;
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
      margin: 0 auto 20px;
      color: var(--primary-color);
      font-size: 2rem;
    }

    .modal-box h3 {
      margin-bottom: 10px;
      font-size: 1.5rem;
      color: var(--text-color);
      font-weight: 700;
    }

    .modal-box p {
      color: var(--text-muted);
      margin-bottom: 30px;
      font-size: 0.95rem;
      line-height: 1.5;
    }

    .modal-actions {
      display: flex;
      gap: 15px;
      justify-content: center;
      flex-direction: column;
    }

    @media (min-width: 400px) {
      .modal-actions {
        flex-direction: row;
      }
    }

    .btn-modal {
      padding: 13px;
      border-radius: 10px;
      font-weight: 600;
      cursor: pointer;
      border: none;
      transition: all 0.2s ease;
      font-size: 1rem;
      box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    }

    .btn-cancel {
      background-color: #f1f5f9;
      color: var(--text-muted);
    }

    .btn-cancel:hover {
      background-color: #e2e8f0;
      color: var(--text-color);
    }

    .btn-logout {
      background-color: var(--primary-color);
      color: white;
    }

    .btn-logout:hover {
      background-color: var(--primary-hover);
      transform: translateY(-2px);
    }

    /* ============ WELCOME SCREEN STYLES ============ */
    .welcome-overlay {
      position: fixed;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      background: rgba(0, 0, 0, 0.7);
      backdrop-filter: blur(8px);
      z-index: 5000;
      display: none;
      align-items: center;
      justify-content: center;
      opacity: 0;
      transition: opacity 0.4s ease;
    }

    .welcome-overlay.active {
      display: flex;
      opacity: 1;
    }

    .welcome-container {
      background: #fff;
      border-radius: 20px;
      width: 92%;
      max-width: 520px;
      overflow: hidden;
      box-shadow: 0 25px 60px rgba(0, 0, 0, 0.3);
      transform: scale(0.85) translateY(30px);
      transition: transform 0.5s cubic-bezier(0.34, 1.56, 0.64, 1);
      position: relative;
    }

    .welcome-overlay.active .welcome-container {
      transform: scale(1) translateY(0);
    }

    .welcome-header {
      background: linear-gradient(135deg, #d90429 0%, #a3001b 50%, #780000 100%);
      padding: 40px 30px 35px;
      text-align: center;
      position: relative;
      overflow: hidden;
    }

    .welcome-header::before {
      content: '';
      position: absolute;
      top: -50%;
      left: -50%;
      width: 200%;
      height: 200%;
      background: radial-gradient(circle, rgba(255, 255, 255, 0.08) 0%, transparent 60%);
      animation: welcomePulse 4s ease-in-out infinite;
    }

    @keyframes welcomePulse {

      0%,
      100% {
        transform: scale(1);
        opacity: 0.5;
      }

      50% {
        transform: scale(1.1);
        opacity: 1;
      }
    }

    .welcome-header .welcome-logo {
      width: 72px;
      height: 72px;
      background: rgba(255, 255, 255, 0.2);
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
      margin: 0 auto 18px;
      position: relative;
      z-index: 1;
      border: 3px solid rgba(255, 255, 255, 0.3);
    }

    .welcome-header .welcome-logo i {
      font-size: 2rem;
      color: #fff;
    }

    .welcome-header h2 {
      color: #fff;
      font-size: 1.5rem;
      font-weight: 800;
      position: relative;
      z-index: 1;
      margin-bottom: 4px;
    }

    .welcome-header p {
      color: rgba(255, 255, 255, 0.85);
      font-size: 0.95rem;
      position: relative;
      z-index: 1;
    }

    .welcome-body {
      padding: 30px 30px 25px;
      min-height: 220px;
      position: relative;
    }

    .welcome-step {
      display: none;
      text-align: center;
      animation: welcomeStepIn 0.45s ease forwards;
    }

    .welcome-step.active {
      display: block;
    }

    @keyframes welcomeStepIn {
      from {
        opacity: 0;
        transform: translateX(30px);
      }

      to {
        opacity: 1;
        transform: translateX(0);
      }
    }

    .welcome-step .step-icon {
      width: 64px;
      height: 64px;
      border-radius: 16px;
      display: flex;
      align-items: center;
      justify-content: center;
      margin: 0 auto 18px;
      font-size: 1.6rem;
    }

    .welcome-step .step-icon.icon-red {
      background: #fee2e2;
      color: #d90429;
    }

    .welcome-step .step-icon.icon-blue {
      background: #dbeafe;
      color: #3b82f6;
    }

    .welcome-step .step-icon.icon-green {
      background: #d1fae5;
      color: #10b981;
    }

    .welcome-step .step-icon.icon-orange {
      background: #ffedd5;
      color: #f59e0b;
    }

    .welcome-step .step-icon.icon-purple {
      background: #ede9fe;
      color: #8b5cf6;
    }

    .welcome-step h3 {
      font-size: 1.2rem;
      font-weight: 700;
      color: var(--text-color);
      margin-bottom: 10px;
    }

    .welcome-step p {
      font-size: 0.9rem;
      color: var(--text-muted);
      line-height: 1.7;
      max-width: 380px;
      margin: 0 auto 15px;
    }

    /* Tombol khusus di dalam step */
    .step-action-btn {
      display: inline-block;
      margin-top: 5px;
      padding: 8px 20px;
      border-radius: 8px;
      background-color: var(--primary-color);
      color: white;
      font-size: 0.85rem;
      font-weight: 600;
      text-decoration: none;
      transition: all 0.2s;
    }

    .step-action-btn:hover {
      background-color: var(--primary-hover);
      transform: translateY(-2px);
    }

    .welcome-progress {
      display: flex;
      justify-content: center;
      gap: 8px;
      padding: 0 30px 20px;
    }

    .welcome-dot {
      width: 10px;
      height: 10px;
      border-radius: 50%;
      background: #e2e8f0;
      transition: all 0.3s ease;
    }

    .welcome-dot.active {
      background: var(--primary-color);
      width: 28px;
      border-radius: 5px;
    }

    .welcome-footer {
      padding: 0 30px 30px;
      display: flex;
      justify-content: space-between;
      align-items: center;
      gap: 12px;
    }

    .welcome-btn {
      padding: 12px 28px;
      border-radius: 10px;
      font-weight: 600;
      font-size: 0.9rem;
      cursor: pointer;
      border: none;
      transition: all 0.25s ease;
    }

    .welcome-btn-skip {
      background: transparent;
      color: var(--text-muted);
      padding: 12px 16px;
    }

    .welcome-btn-skip:hover {
      color: var(--text-color);
      background: #f1f5f9;
      border-radius: 10px;
    }

    .welcome-btn-next {
      background: var(--primary-color);
      color: #fff;
      flex: 1;
      max-width: 200px;
    }

    .welcome-btn-next:hover {
      background: var(--primary-hover);
      transform: translateY(-2px);
      box-shadow: 0 4px 12px rgba(217, 4, 41, 0.4);
    }

    .welcome-btn-start {
      background: linear-gradient(135deg, #d90429, #a3001b);
      color: #fff;
      flex: 1;
      max-width: 200px;
    }

    .welcome-btn-start:hover {
      transform: translateY(-2px);
      box-shadow: 0 6px 20px rgba(217, 4, 41, 0.5);
    }

    .confetti-piece {
      position: absolute;
      width: 8px;
      height: 8px;
      border-radius: 2px;
      opacity: 0;
      z-index: 1;
    }

    @keyframes confettiFall {
      0% {
        opacity: 1;
        transform: translateY(0) rotate(0deg) scale(1);
      }

      100% {
        opacity: 0;
        transform: translateY(200px) rotate(720deg) scale(0.3);
      }
    }

    /* RESPONSIVE */
    @media (max-width: 992px) {
      .main-content {
        width: 100%;
        padding: 20px;
      }

      .sidebar {
        position: fixed;
        top: var(--header-height);
        left: auto;
        right: -260px;
        box-shadow: -2px 0 10px rgba(0, 0, 0, 0.1);
        border-right: none;
        border-left: 1px solid var(--border-color);
        transition: right 0.3s ease;
      }

      .sidebar.active {
        right: 0;
      }

      .menu-toggle {
        display: block;
      }

      .logo span {
        display: none;
      }

      .profile-greeting {
        display: none;
      }

      .welcome-container {
        max-width: 95%;
      }

      .welcome-header {
        padding: 30px 20px 25px;
      }

      .welcome-body {
        padding: 25px 20px 20px;
        min-height: 200px;
      }

      .welcome-footer {
        padding: 0 20px 25px;
      }
    }

    /* STYLE NOTIFIKASI ABSENSI */
    .absen-notification {
      position: fixed;
      top: 85px;
      right: -450px;
      /* Disembunyikan di luar layar kanan */
      width: 380px;
      background: linear-gradient(135deg, #d90429, #ef4444);
      color: white;
      padding: 20px;
      border-radius: 12px;
      box-shadow: 0 10px 30px rgba(217, 4, 41, 0.4);
      z-index: 1500;
      display: flex;
      align-items: center;
      gap: 15px;
      transition: right 0.5s cubic-bezier(0.68, -0.55, 0.265, 1.55);
      /* Efek bounce */
      animation: pulse-box 2s infinite;
    }

    .absen-notification.show {
      right: 20px;
      /* Muncul ke dalam layar */
    }

    .absen-notif-icon {
      font-size: 2rem;
      flex-shrink: 0;
    }

    .absen-notif-content h4 {
      font-size: 1.1rem;
      margin-bottom: 5px;
    }

    .absen-notif-content p {
      font-size: 0.85rem;
      opacity: 0.9;
      line-height: 1.4;
    }

    .absen-notif-close {
      position: absolute;
      top: 5px;
      right: 10px;
      background: none;
      border: none;
      color: white;
      font-size: 1.5rem;
      cursor: pointer;
      opacity: 0.7;
    }

    .absen-notif-close:hover {
      opacity: 1;
    }

    /* Banner untuk di dashboard (statik) */
    .dashboard-absen-banner {
      background: #fff1f1;
      border: 2px solid #fecaca;
      border-radius: var(--radius);
      padding: 20px;
      margin-bottom: 25px;
      display: flex;
      align-items: center;
      gap: 15px;
      animation: fadeIn 0.5s ease;
      /* PERBAIKAN MOBILE: Biar item turun otomatis kalai sempit */
      flex-wrap: wrap;
    }

    .dashboard-absen-banner i {
      font-size: 2.5rem;
      color: var(--primary-color);
      /* PERBAIKAN MOBILE: Ikon gak nyesain layar */
      flex-shrink: 0;
    }

    .dashboard-absen-banner .banner-text {
      /* PERBAIKAN MOBILE: Biar teks nge-fill sisa ruang ikon */
      flex: 1;
      min-width: 150px;
    }

    .dashboard-absen-banner .banner-text h3 {
      color: var(--primary-color);
      font-size: 1.1rem;
      margin-bottom: 5px;
    }

    .dashboard-absen-banner .banner-text p {
      color: var(--text-muted);
      font-size: 0.9rem;
    }

    .dashboard-absen-banner .btn {
      /* PERBAIKAN MOBILE: Biar tombol bisa pindah ke bawah dan gak nyeplak */
      margin-left: auto;
      flex-shrink: 0;
      text-align: center;
    }

    @keyframes pulse-box {
      0% {
        box-shadow: 0 10px 30px rgba(217, 4, 41, 0.4);
      }

      50% {
        box-shadow: 0 10px 40px rgba(217, 4, 41, 0.7);
      }

      100% {
        box-shadow: 0 10px 30px rgba(217, 4, 41, 0.4);
      }
    }

    @media (max-width: 992px) {
      .absen-notification {
        width: calc(100% - 40px);
        left: 20px;
        right: -100%;
      }

      .absen-notification.show {
        right: 0;
      }

      /* PERBAIKAN MOBILE: Susun ulang banner biar mirip card */
      .dashboard-absen-banner {
        flex-direction: column;
        align-items: flex-start;
        /* Semua item rata kiri */
        text-align: left;
        gap: 10px;
      }

      .dashboard-absen-banner i {
        font-size: 2rem;
        /* Perkecil ikon dikit */
      }

      .dashboard-absen-banner .btn {
        margin-left: 0;
        /* Tombol rata kiri */
        width: 100%;
        /* Tombol full width biar gampang ditekan */
        justify-content: center;
      }
    }
  </style>
</head>

<body>
  <!-- HEADER -->
  <header>
    <nav class="navbar">
      <div class="nav-left">
        <div class="logo">
          <img src="../Gambar/logpmi.png" alt="Logo PMR">
          <span>PMR MILLENIUM</span>
        </div>
      </div>
      <div class="nav-center"></div>
      <div class="nav-right">
        <div class="profile-btn" id="profileBtn">
          <div class="profile-greeting">
            <small><?= ucfirst($role) ?></small>
            <span>Halo, <?= $nama_user ?></span>
          </div>
          <img src="<?= $foto_profil ?>" alt="Foto Profil" class="profile-img">
        </div>
        <div class="profile-dropdown" id="profileDropdown">
          <div class="dropdown-header">
            <p><?= $nama_user ?></p>
            <small><?= ucfirst($role) ?></small>
          </div>
          <ul>
            <li><a href="ganti_foto.php"><i class="fa-solid fa-camera"></i> Ganti Foto Profil</a></li>
            <li><a href="ganti_nama.php"><i class="fa-solid fa-user-pen"></i> Ganti Nama</a></li>
            <li><a href="ganti_password.php"><i class="fa-solid fa-key"></i> Ganti Password</a></li>
          </ul>
        </div>
        <button class="menu-toggle" aria-label="Menu"><i class="fa-solid fa-bars"></i></button>
      </div>
    </nav>
  </header>

  <!-- NOTIFIKASI LOGIN -->
  <?php if (isset($_SESSION['login_success'])): ?>
    <div id="loginNotification" class="notification">
      <i class="fas fa-check-circle"></i>
      <div>
        <div style="font-weight: 600;">Login Berhasil</div>
        <div style="font-size: 0.85rem; color: #666;">Selamat datang, <b><?= $nama_user ?></b>!</div>
      </div>
    </div>
    <?php unset($_SESSION['login_success']); ?>
  <?php endif; ?>

  <!-- ============================================ -->
  <!-- WELCOME SCREEN (HANYA UNTUK FIRST LOGIN)     -->
  <!-- ============================================ -->
  <?php if ($is_first_login): ?>
    <div class="welcome-overlay" id="welcomeOverlay">
      <div class="welcome-container" id="welcomeContainer">
        <div class="welcome-header">
          <div class="welcome-logo"><i class="fa-solid fa-hand-holding-heart"></i></div>
          <h2>Selamat Datang, <?= $nama_user ?>! 🎉</h2>
          <p>Kamu baru saja bergabung di PMR Millenium</p>
        </div>
        <div class="welcome-body">
          <!-- Step 1 -->
          <div class="welcome-step active" data-step="1">
            <div class="step-icon icon-red"><i class="fa-solid fa-hand-wave"></i></div>
            <h3>Halo, Anggota Baru! 👋</h3>
            <p>Selamat bergabung di keluarga besar <b>PMR Millenium</b>! Kami sangat senang menyambutmu. Yuk, kenali fitur-fitur yang bisa kamu gunakan.</p>
          </div>
          <!-- Step 2 -->
          <div class="welcome-step" data-step="2">
            <div class="step-icon icon-blue"><i class="fa-solid fa-calendar-check"></i></div>
            <h3>Absensi Kegiatan 📋</h3>
            <p>Catat kehadiranmu setiap pertemuan PMR. Jaga keaktifanmu agar persentase kehadiran tetap <b>80% atau lebih</b> untuk status "Baik"!</p>
          </div>
          <!-- Step 3 -->
          <div class="welcome-step" data-step="3">
            <div class="step-icon icon-green"><i class="fa-solid fa-book-open"></i></div>
            <h3>Materi 📚</h3>
            <p>Akses berbagai materi pelatihan, panduan P3K, dan referensi penting lainnya kapan saja melalui fitur <b>Materi</b>.</p>
          </div>
          <!-- Step 4 (REKOMENDASI GANTI PASSWORD) -->
          <div class="welcome-step" data-step="4">
            <div class="step-icon icon-purple"><i class="fa-solid fa-shield-halved"></i></div>
            <h3>Keamanan Akun 🔒</h3>
            <p>Untuk keamanan akunmu, sangat <b>direkomendasikan</b> untuk segera mengganti password bawaan kamu dengan password yang kuat dan mudah diingat.</p>
            <a href="ganti_password.php" class="step-action-btn"><i class="fa-solid fa-key" style="margin-right:5px;"></i> Ganti Password Sekarang</a>
          </div>
          <!-- Step 5 -->
          <div class="welcome-step" data-step="5">
            <div class="step-icon icon-orange"><i class="fa-solid fa-rocket"></i></div>
            <h3>Siap Memulai? 🚀</h3>
            <p>Semua fitur sudah bisa kamu akses dari sidebar di sebelah kanan. Jangan ragu untuk eksplorasi dan berkontribusi di PMR!</p>
          </div>
        </div>
        <div class="welcome-progress">
          <div class="welcome-dot active" data-dot="1"></div>
          <div class="welcome-dot" data-dot="2"></div>
          <div class="welcome-dot" data-dot="3"></div>
          <div class="welcome-dot" data-dot="4"></div>
          <div class="welcome-dot" data-dot="5"></div>
        </div>
        <div class="welcome-footer">
          <button class="welcome-btn welcome-btn-skip" id="welcomeSkip">Lewati</button>
          <button class="welcome-btn welcome-btn-next" id="welcomeNext">Lanjut <i class="fa-solid fa-arrow-right" style="margin-left:6px;font-size:0.8rem;"></i></button>
          <button class="welcome-btn welcome-btn-start" id="welcomeStart" style="display:none;">Mulai! <i class="fa-solid fa-rocket" style="margin-left:6px;font-size:0.8rem;"></i></button>
        </div>
      </div>
    </div>
  <?php endif; ?>

  <!-- MODAL LOGOUT -->
  <div class="modal-overlay" id="logoutModal">
    <div class="modal-box">
      <div class="modal-icon"><i class="fa-solid fa-right-from-bracket"></i></div>
      <h3>Konfirmasi Keluar</h3>
      <p>Apakah Anda yakin ingin keluar dari akun?</p>
      <div class="modal-actions">
        <button class="btn-modal btn-cancel" onclick="closeLogoutModal()">Batal</button>
        <button class="btn-modal btn-logout" onclick="proceedLogout()">Ya, Keluar</button>
      </div>
    </div>
  </div>

  <div class="dashboard-container">
    <!-- SIDEBAR -->
    <aside class="sidebar">
      <ul>
        <li class="active"><a href="anggota.php"><i class="fa-solid fa-house"></i> Dashboard</a></li>
        <?php if ($role == 'pengurus'): ?>
          <li><a href="kelolaabsen.php"><i class="fa-solid fa-calendar-check"></i> Kelola Absensi</a></li>
          <li><a href="kelolaperpus.php"><i class="fa-solid fa-book"></i> Kelola Materi</a></li>
          <li><a href="kelola_pendaftaran.php"><i class="fa-solid fa-users"></i> Kelola Pendaftaran</a></li>
          <li><a href="kelola_beranda.php"><i class="fa-solid fa-pen-to-square"></i> Edit Halaman Utama</a></li>
        <?php else: ?>
          <li><a href="absensi.php"><i class="fa-solid fa-calendar-check"></i>Absensi</a></li>
          <li><a href="perpus.php"><i class="fa-solid fa-book"></i> Materi</a></li>
        <?php endif; ?>
        <li style="margin-top: 20px; border-top: 1px solid #eee;">
          <a href="javascript:void(0)" onclick="openLogoutModal()"><i class="fa-solid fa-right-from-bracket"></i> Log Out</a>
        </li>
        <li>
          <a href="../Halaman Utama/index.php"><i class="fa-solid fa-globe"></i>Halaman Utama</a>
        </li>
      </ul>
    </aside>

    <!-- KONTEN UTAMA -->
    <main class="main-content">
      <!-- BANNER STATIS DI DASHBOARD -->
      <div id="dashboardBanner" class="dashboard-absen-banner" style="display: none;">
        <i class="fas fa-exclamation-circle"></i>
        <div class="banner-text">
          <h3>Absensi Sedang Dibuka!</h3>
          <p id="bannerJam">Segera lakukan absensi sebelum waktu berakhir.</p>
        </div>
        <a href="absensi.php" class="btn btn-primary"><i class="fas fa-camera"></i> Absen Sekarang</a>
      </div>
      <!-- NOTIFIKASI ABSENSI SLIDE-IN -->
      <div id="absenNotif" class="absen-notification" style="display: none;">
        <div class="absen-notif-icon">
          <i class="fas fa-bell fa-shake"></i>
        </div>
        <div class="absen-notif-content">
          <h4>Waktunya Absensi!</h4>
          <p id="absenNotifText">Jadwal absensi sedang dibuka. Jangan lupa absen ya!</p>
        </div>
        <button class="absen-notif-close" onclick="tutupNotif()">&times;</button>
      </div>
      <div class="dashboard-welcome">
        <h1>Dashboard <?php echo ucfirst($role); ?></h1>
        <p>Halo, <b><?= $nama_user ?></b>! Selamat datang di portal.</p>
      </div>

      <div class="stats-grid">
        <?php if ($role == 'pengurus'): ?>
          <div class="stat-card">
            <div class="stat-icon bg-blue"><i class="fa-solid fa-users"></i></div>
            <div class="stat-info">
              <h2><?= $stat_total_anggota ?></h2>
              <p>Total Anggota</p>
            </div>
          </div>
          <div class="stat-card">
            <div class="stat-icon bg-green"><i class="fa-solid fa-calendar-check"></i></div>
            <div class="stat-info">
              <h2><?= $stat_total_hadir_bulan_ini ?></h2>
              <p>Kehadiran Bulan Ini</p>
            </div>
          </div>
          <div class="stat-card">
            <div class="stat-icon bg-orange"><i class="fa-solid fa-book"></i></div>
            <div class="stat-info">
              <h2><?= $stat_total_buku ?></h2>
              <p>Total Materi</p>
            </div>
          </div>

          <!-- KARTU PENDAFTARAN BARU DENGAN HIGHLIGHT -->
          <div class="stat-card <?php if ($stat_pendaftaran_baru > 0) echo 'highlight-new'; ?>">
            <div class="stat-icon bg-purple stat-icon-wrapper">
              <i class="fa-solid fa-user-plus"></i>
              <?php if ($stat_pendaftaran_baru > 0): ?>
                <span class="notif-badge"><?= $stat_pendaftaran_baru ?></span>
              <?php endif; ?>
            </div>
            <div class="stat-info">
              <h2><?= $stat_pendaftaran_baru ?></h2>
              <p>Pendaftaran Baru</p>
              <?php if ($stat_pendaftaran_baru > 0): ?>
                <small style="color: var(--stat-purple); font-weight: 700; font-size: 0.75rem;">
                  ⚠️ Perlu ditinjau!
                </small>
              <?php endif; ?>
            </div>
          </div>

        <?php else: ?>
          <div class="stat-card">
            <div class="stat-icon bg-green"><i class="fa-solid fa-check-circle"></i></div>
            <div class="stat-info">
              <h2><?= $stat_hadir_saya ?></h2>
              <p>Total Kehadiran Saya</p>
            </div>
          </div>
          <div class="stat-card">
            <div class="stat-icon bg-blue"><i class="fa-solid fa-percent"></i></div>
            <div class="stat-info">
              <h2><?= $stat_persentase ?>%</h2>
              <p>Persentase Kehadiran</p>
            </div>
          </div>
          <div class="stat-card">
            <div class="stat-icon bg-orange"><i class="fa-solid fa-star"></i></div>
            <div class="stat-info">
              <h2><?= $stat_status ?></h2>
              <p>Status Keaktifan</p>
            </div>
          </div>
        <?php endif; ?>
      </div>

      <div class="cards">
        <?php if ($role == 'pengurus'): ?>
          <div class="card">
            <div class="card-icon-wrapper"><i class="fa-solid fa-pen-to-square"></i></div>
            <h3>Lihat Absensi</h3>
            <p>Melihat data absensi anggota</p>
            <button class="card-btn" onclick="window.location.href='kelolaabsen.php'">Lihat Data</button>
          </div>
          <div class="card">
            <div class="card-icon-wrapper"><i class="fa-solid fa-book"></i></div>
            <h3>Kelola Perpustakaan</h3>
            <p>Tambah atau hapus buku digital dan materi.</p>
            <button class="card-btn" onclick="window.location.href='kelolaperpus.php'">Kelola</button>
          </div>
          <div class="card">
            <div class="card-icon-wrapper"><i class="fa-solid fa-users"></i></div>
            <h3>Kelola Pendaftaran</h3>
            <p>Mengelola data pendaftaran anggota baru.</p>
            <button class="card-btn" onclick="window.location.href='kelola_pendaftaran.php'">Kelola</button>
          </div>
        <?php else: ?>
          <div class="card">
            <div class="card-icon-wrapper"><i class="fa-solid fa-calendar-check"></i></div>
            <h3>Rekap Absensi</h3>
            <p>Lihat riwayat kehadiran kamu.</p>
            <button class="card-btn" onclick="window.location.href='absensi.php'">Lihat Data</button>
          </div>
          <div class="card">
            <div class="card-icon-wrapper"><i class="fa-solid fa-book-open"></i></div>
            <h3>Perpustakaan Digital</h3>
            <p>Akses buku panduan P3K dan materi pelatihan.</p>
            <button class="card-btn" onclick="window.location.href='perpus.php'">Buka Perpus</button>
          </div>
        <?php endif; ?>
      </div>
    </main>
  </div>

  <!-- JAVASCRIPT -->
  <script>
    document.addEventListener('DOMContentLoaded', () => {
      const menuToggle = document.querySelector('.menu-toggle');
      const sidebar = document.querySelector('.sidebar');
      const profileBtn = document.getElementById('profileBtn');
      const profileDropdown = document.getElementById('profileDropdown');

      if (menuToggle) {
        menuToggle.addEventListener('click', (e) => {
          e.stopPropagation();
          sidebar.classList.toggle('active');
          profileDropdown.classList.remove('active');
        });
      }

      profileBtn.addEventListener('click', (e) => {
        e.stopPropagation();
        profileDropdown.classList.toggle('active');
        sidebar.classList.remove('active');
      });

      document.addEventListener('click', (e) => {
        if (window.innerWidth <= 992) {
          if (!sidebar.contains(e.target) && !menuToggle.contains(e.target)) {
            sidebar.classList.remove('active');
          }
        }
        if (!profileBtn.contains(e.target) && !profileDropdown.contains(e.target)) {
          profileDropdown.classList.remove('active');
        }
      });

      const notification = document.getElementById('loginNotification');
      if (notification) {
        notification.style.display = 'flex';
        setTimeout(() => {
          notification.style.animation = 'fadeOut 0.5s ease forwards';
          setTimeout(() => {
            notification.style.display = 'none';
          }, 500);
        }, 4000);
      }

      // ============================================
      // WELCOME SCREEN LOGIC
      // ============================================
      const welcomeOverlay = document.getElementById('welcomeOverlay');
      if (welcomeOverlay) {
        let currentStep = 1;
        const totalSteps = 5;

        setTimeout(() => {
          welcomeOverlay.classList.add('active');
          launchConfetti();
        }, 600);

        const btnNext = document.getElementById('welcomeNext');
        const btnStart = document.getElementById('welcomeStart');
        const btnSkip = document.getElementById('welcomeSkip');

        function goToStep(step) {
          document.querySelectorAll('.welcome-step').forEach(s => s.classList.remove('active'));
          const targetStep = document.querySelector(`.welcome-step[data-step="${step}"]`);
          if (targetStep) targetStep.classList.add('active');

          document.querySelectorAll('.welcome-dot').forEach(d => d.classList.remove('active'));
          const targetDot = document.querySelector(`.welcome-dot[data-dot="${step}"]`);
          if (targetDot) targetDot.classList.add('active');

          currentStep = step;

          if (currentStep === totalSteps) {
            btnNext.style.display = 'none';
            btnStart.style.display = 'block';
          } else {
            btnNext.style.display = 'block';
            btnStart.style.display = 'none';
          }
        }

        btnNext.addEventListener('click', () => {
          if (currentStep < totalSteps) goToStep(currentStep + 1);
        });

        btnStart.addEventListener('click', () => closeWelcome());
        btnSkip.addEventListener('click', () => closeWelcome());

        welcomeOverlay.addEventListener('click', (e) => {
          if (e.target === welcomeOverlay) closeWelcome();
        });

        function closeWelcome() {
          welcomeOverlay.classList.remove('active');
          fetch('update_first_login.php', {
              method: 'POST',
              headers: {
                'Content-Type': 'application/x-www-form-urlencoded'
              }
            })
            .then(res => res.json()).then(data => {}).catch(err => console.log('Error:', err));

          setTimeout(() => {
            if (welcomeOverlay.parentNode) welcomeOverlay.parentNode.removeChild(welcomeOverlay);
          }, 500);
        }

        function launchConfetti() {
          const container = document.querySelector('.welcome-header');
          if (!container) return;
          const colors = ['#ff6b6b', '#ffd93d', '#6bcb77', '#4d96ff', '#ff6eb4', '#fff'];
          for (let i = 0; i < 30; i++) {
            const piece = document.createElement('div');
            piece.classList.add('confetti-piece');
            piece.style.backgroundColor = colors[Math.floor(Math.random() * colors.length)];
            piece.style.left = Math.random() * 100 + '%';
            piece.style.top = Math.random() * 30 + '%';
            piece.style.width = (Math.random() * 8 + 4) + 'px';
            piece.style.height = (Math.random() * 8 + 4) + 'px';
            piece.style.borderRadius = Math.random() > 0.5 ? '50%' : '2px';
            piece.style.animation = `confettiFall ${Math.random() * 2 + 1.5}s ease-out ${Math.random() * 0.8}s forwards`;
            container.appendChild(piece);
            setTimeout(() => {
              if (piece.parentNode) piece.parentNode.removeChild(piece);
            }, 4000);
          }
        }
      }
    });

    function openLogoutModal() {
      document.getElementById('logoutModal').classList.add('active');
    }

    function closeLogoutModal() {
      document.getElementById('logoutModal').classList.remove('active');
    }

    function proceedLogout() {
      window.location.href = "../logout.php";
    }
    document.getElementById('logoutModal').addEventListener('click', function(e) {
      if (e.target === this) closeLogoutModal();
    });
    // --- LOGIC NOTIFIKASI ABSENSI ---
    let notifSudahMuncul = false; // Biar gak muncul terus menerus

    // Ambil role dari PHP ke JavaScript
    const userRole = '<?= isset($_SESSION["role"]) ? $_SESSION["role"] : "anggota"; ?>';

    async function cekNotifAbsen() {
      // ✅ FIX: Jangan tampilkan notif sama sekali kalau role-nya Pengurus
      if (userRole === 'pengurus') return;

      try {
        const res = await fetch('get_status_absen.php');
        const data = await res.json();

        const notifSlide = document.getElementById('absenNotif');
        const bannerStatik = document.getElementById('dashboardBanner');

        // Kalau absensi dibuka DAN user belum absen
        if (data.is_open && !data.sudah_absen) {

          // 1. Tampilkan Banner Statik di Dashboard
          bannerStatik.style.display = 'flex';
          document.getElementById('bannerJam').innerHTML = `<strong>Waktu:</strong> ${data.jam_mulai} - ${data.jam_selesai} WIB`;

          // 2. Tampilkan Notifikasi Slide-in (hanya sekali saat pertama kali load)
          if (!notifSudahMuncul) {
            document.getElementById('absenNotifText').innerText = `Jadwal absensi dibuka (${data.jam_mulai} - ${data.jam_selesai}). Jangan lupa absen!`;

            // Delay 1 detik setelah halaman load baru muncul notif (biar efeknya kerasa)
            setTimeout(() => {
              notifSlide.style.display = 'flex';
              setTimeout(() => notifSlide.classList.add('show'), 100);
            }, 1000);

            // Auto-hide notif slide setelah 8 detik
            setTimeout(() => {
              tutupNotif();
            }, 8000);

            notifSudahMuncul = true;
          }

        } else {
          // Sembunyikan jika sudah absen atau jadwal ditutup
          bannerStatik.style.display = 'none';
          tutupNotif();
        }
      } catch (err) {
        console.error("Gagal cek notif absen:", err);
      }
    }

    function tutupNotif() {
      const notifSlide = document.getElementById('absenNotif');
      notifSlide.classList.remove('show');
      // Tunggu animasi selesai baru dihilangkan
      setTimeout(() => {
        if (!notifSlide.classList.contains('show')) {
          notifSlide.style.display = 'none';
        }
      }, 500);
    }

    // Panggil saat halaman pertama kali dibuka
    cekNotifAbsen();
    // Cek ulang setiap 30 detik (jaga-jaga kalau pengurus baru buka jadwal)
    setInterval(cekNotifAbsen, 30000);

    function tutupNotif() {
      const notifSlide = document.getElementById('absenNotif');
      notifSlide.classList.remove('show');
      // Tunggu animasi selesai baru dihilangkan
      setTimeout(() => {
        if (!notifSlide.classList.contains('show')) {
          notifSlide.style.display = 'none';
        }
      }, 500);
    }

    // Panggil saat halaman pertama kali dibuka
    cekNotifAbsen();
    // Cek ulang setiap 30 detik (jaga-jaga kalau pengurus baru buka jadwal)
    setInterval(cekNotifAbsen, 30000);
  </script>
</body>

</html>