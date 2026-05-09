<?php
date_default_timezone_set('Asia/Jakarta');
session_start();
require_once __DIR__ . '/../koneksi.php';

// ========================================================
// PENGATURAN LOKASI & RADIUS (HARDCODED)
// ========================================================
$config = [
  'nama_lokasi' => 'SMKN 1 Cibinong',
  'latitude'    => -6.4974677907960166,     
  'longitude'   => 106.89440596714574,
  'radius'      => 150
];
// ========================================================

if (!isset($_SESSION['nama'])) {
  echo '<script>alert("Silakan login terlebih dahulu!"); window.location.href = "../Login/login.php";</script>';
  exit;
}

if (!isset($_SESSION['id'])) {
  $nama_session = $_SESSION['nama'];
  $stmt = mysqli_prepare($koneksi, "SELECT id, role FROM users WHERE nama = ?");
  mysqli_stmt_bind_param($stmt, "s", $nama_session);
  mysqli_stmt_execute($stmt);
  $result_id = mysqli_stmt_get_result($stmt);
  if (mysqli_num_rows($result_id) > 0) {
    $data_user = mysqli_fetch_assoc($result_id);
    $_SESSION['id'] = $data_user['id'];
    $_SESSION['role'] = $data_user['role'];
  } else {
    echo '<script>alert("Data user tidak ditemukan!"); window.location.href = "../logout.php";</script>';
    exit;
  }
}

$id_user = $_SESSION['id'];
$role = isset($_SESSION['role']) ? $_SESSION['role'] : 'anggota';
$nama_user = htmlspecialchars($_SESSION['nama']);

$foto_session = isset($_SESSION['foto']) ? $_SESSION['foto'] : '';
$foto_profil = 'https://ui-avatars.com/api/?name=' . urlencode($nama_user) . '&background=d90429&color=fff';
if (!empty($foto_session) && file_exists("../uploads/foto_profil/" . $foto_session)) {
  $foto_profil = "../uploads/foto_profil/" . $foto_session;
}

$month = isset($_GET['m']) ? intval($_GET['m']) : date('m');
$year = isset($_GET['y']) ? intval($_GET['y']) : date('Y');

$query_absen = mysqli_query($koneksi, "SELECT tanggal, status FROM absensi WHERE user_id = '$id_user' AND MONTH(tanggal) = '$month' AND YEAR(tanggal) = '$year'");
$riwayat_absen = [];
// Ambil Tanggal Jadwal di Bulan Ini untuk Kalender Anggota
$jadwal_bulan_ini = [];
$sql_jcal = "SELECT tanggal FROM pengaturan_absensi WHERE MONTH(tanggal) = ? AND YEAR(tanggal) = ?";
$stmt_jcal = mysqli_prepare($koneksi, $sql_jcal);
mysqli_stmt_bind_param($stmt_jcal, "ii", $month, $year);
mysqli_stmt_execute($stmt_jcal);
$res_jcal = mysqli_stmt_get_result($stmt_jcal);
while ($rj = mysqli_fetch_assoc($res_jcal)) {
  $jadwal_bulan_ini[$rj['tanggal']] = true;
}
while ($row = mysqli_fetch_assoc($query_absen)) {
  if (strtolower($row['status']) == 'hadir') {
    $riwayat_absen[$row['tanggal']] = $row['status'];
  }
}
?>
<!DOCTYPE html>
<html lang="id">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Rekap Absensi | PMR Millenium</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
  <link rel="icon" href="../Gambar/logpmi.png" type="image/png">
  <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="" />
  <style>
    :root {
      --primary-color: #d90429;
      --primary-hover: #c92a2a;
      --bg-color: #f8f9fa;
      --text-color: #1e293b;
      --text-muted: #64748b;
      --border-color: #e2e8f0;
      --success-color: #10b981;
      --warning-color: #f59e0b;
      --cyan-color: #06b6d4;
      --shadow-sm: 0 1px 3px rgba(0, 0, 0, 0.05);
      --radius: 12px;
      --header-height: 70px;
      --sidebar-width: 250px;
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
      cursor: pointer;
      padding: 5px;
      border-radius: 50px;
    }

    .profile-btn:hover {
      background-color: #f1f5f9;
    }

    .profile-img {
      width: 40px;
      height: 40px;
      border-radius: 50%;
      object-fit: cover;
      border: 2px solid var(--primary-color);
    }

    .profile-dropdown {
      position: absolute;
      top: 100%;
      right: 0;
      margin-top: 10px;
      background: #fff;
      border-radius: 8px;
      box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15);
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
      font-size: 0.9rem;
      transition: 0.2s;
    }

    .profile-dropdown ul li a:hover {
      background-color: #fff1f1;
      color: var(--primary-color);
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

    .page-title h1 {
      font-size: 1.75rem;
      color: var(--primary-color);
      margin-bottom: 5px;
    }

    .page-title p {
      color: var(--text-muted);
      font-size: 0.9rem;
      margin-bottom: 25px;
    }

    .info-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
      gap: 15px;
      margin-bottom: 20px;
    }

    .info-card {
      background: white;
      padding: 20px;
      border-radius: var(--radius);
      box-shadow: var(--shadow-sm);
      border: 1px solid var(--border-color);
      display: flex;
      align-items: center;
      gap: 15px;
    }

    .info-icon {
      width: 50px;
      height: 50px;
      border-radius: 10px;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 1.3rem;
      color: white;
      flex-shrink: 0;
    }

    .info-content h3 {
      font-size: 0.85rem;
      color: var(--text-muted);
      margin-bottom: 4px;
    }

    .info-content p {
      font-size: 1.1rem;
      font-weight: 700;
      color: var(--text-color);
    }

    .bg-cyan {
      background-color: var(--cyan-color);
    }

    .bg-green {
      background-color: var(--success-color);
    }

    .map-container {
      height: 300px;
      width: 100%;
      border-radius: var(--radius);
      overflow: hidden;
      border: 1px solid var(--border-color);
      margin-bottom: 25px;
      position: relative;
      z-index: 1;
    }

    .user-marker-icon {
      display: flex;
      align-items: center;
      justify-content: center;
    }

    .user-marker-icon .dot {
      width: 16px;
      height: 16px;
      background: #3b82f6;
      border: 3px solid white;
      border-radius: 50%;
      box-shadow: 0 0 10px rgba(0, 0, 0, 0.3);
    }

    .status-box {
      background: white;
      padding: 25px;
      border-radius: var(--radius);
      box-shadow: var(--shadow-sm);
      margin-bottom: 25px;
      text-align: center;
      border: 1px solid var(--border-color);
      display: flex;
      flex-direction: column;
      align-items: center;
      gap: 15px;
    }

    .status-box.inactive {
      background: #fee2e2;
      border-color: #fecaca;
    }

    .status-box.active {
      background: #dcfce7;
      border-color: #bbf7d0;
    }

    .status-box.warning {
      background: #fffbeb;
      border-color: #fcd34d;
    }

    .status-icon {
      font-size: 3rem;
      margin-bottom: 10px;
    }

    .status-box.inactive .status-icon {
      color: var(--primary-color);
    }

    .status-box.active .status-icon {
      color: var(--success-color);
    }

    .status-box.warning .status-icon {
      color: var(--warning-color);
    }

    .status-title {
      font-size: 1.2rem;
      font-weight: 700;
      margin-bottom: 5px;
    }

    .status-time {
      font-size: 0.9rem;
      color: var(--text-muted);
    }

    .btn {
      padding: 12px 25px;
      border: none;
      border-radius: 8px;
      cursor: pointer;
      font-weight: 600;
      display: inline-flex;
      align-items: center;
      gap: 8px;
      transition: all 0.2s ease;
      font-size: 0.95rem;
      color: white;
    }

    .btn-primary {
      background-color: var(--primary-color);
    }

    .btn-primary:hover {
      background-color: var(--primary-hover);
    }

    .btn-success {
      background-color: var(--success-color);
    }

    .btn:disabled {
      background-color: #cbd5e1;
      cursor: not-allowed;
      transform: none;
      box-shadow: none;
    }

    .calendar-container {
      background: white;
      border-radius: var(--radius);
      box-shadow: var(--shadow-sm);
      padding: 20px;
      border: 1px solid var(--border-color);
    }

    .calendar-header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 20px;
      padding-bottom: 15px;
      border-bottom: 1px solid var(--border-color);
    }

    .calendar-header h2 {
      font-size: 1.2rem;
    }

    .calendar-nav {
      display: flex;
      gap: 10px;
    }

    .calendar-nav a {
      padding: 8px 15px;
      background: var(--bg-color);
      border-radius: 6px;
      font-weight: 600;
    }

    .calendar-grid {
      display: grid;
      grid-template-columns: repeat(7, 1fr);
      gap: 5px;
    }

    .calendar-day-name {
      text-align: center;
      font-weight: 600;
      color: var(--text-muted);
      font-size: 0.85rem;
      padding: 10px;
    }

    .calendar-day {
      border: 1px solid var(--border-color);
      border-radius: 8px;
      min-height: 80px;
      padding: 8px;
      position: relative;
      background: #fff;
      transition: 0.2s;
    }

    .calendar-day:hover {
      background: #f8fafc;
    }

    .calendar-day.empty {
      background: #f8f9fa;
      border-color: transparent;
    }

    .calendar-day.today {
      border-color: var(--primary-color);
      border-width: 2px;
    }

    .day-number {
      font-weight: 600;
      color: var(--text-muted);
      font-size: 0.9rem;
      margin-bottom: 5px;
    }

    .calendar-day.today .day-number {
      color: var(--primary-color);
    }

    .attendance-mark {
      display: flex;
      align-items: center;
      justify-content: center;
      height: calc(100% - 25px);
      font-size: 2rem;
    }

    .attendance-mark.hadir {
      color: var(--success-color);
    }

    .attendance-mark.hadir i {
      background: #dcfce7;
      padding: 10px;
      border-radius: 50%;
    }

    .attendance-mark.missed {
      color: #dc2626;
    }

    .attendance-mark.scheduled {
      color: var(--cyan-color);
    }

    .attendance-mark.scheduled i {
      background: #ecfeff;
      padding: 10px;
      border-radius: 50%;
    }

    .attendance-mark.missed i {
      background: #fee2e2;
      padding: 10px;
      border-radius: 50%;
    }

    /* MODAL CAMERA */
    .modal {
      display: none;
      position: fixed;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      background-color: rgba(0, 0, 0, 0.6);
      z-index: 2000;
      align-items: center;
      justify-content: center;
      padding: 20px;
    }

    .modal-content {
      background: white;
      border-radius: var(--radius);
      max-width: 500px;
      width: 100%;
      overflow: hidden;
      animation: fadeIn 0.3s ease;
    }

    .modal-header {
      padding: 15px 20px;
      background: var(--primary-color);
      color: white;
      display: flex;
      justify-content: space-between;
      align-items: center;
    }

    .close-modal {
      background: none;
      border: none;
      color: white;
      font-size: 1.5rem;
      cursor: pointer;
    }

    .modal-body {
      padding: 20px;
    }

    .camera-wrapper {
      width: 100%;
      background: #1a1a2e;
      border-radius: 12px;
      overflow: hidden;
      margin: 15px 0;
      position: relative;
      aspect-ratio: 4/3;
      display: flex;
      align-items: center;
      justify-content: center;
    }

    #video,
    #capturedImage,
    #overlay {
      position: absolute;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      object-fit: cover;
    }

    #video {
      transform: scaleX(-1);
      z-index: 1;
    }

    #overlay {
      z-index: 2;
      pointer-events: none;
    }

    #capturedImage {
      z-index: 3;
      display: none;
    }

    /* ==================== FACE DETECTION GRID OVERLAY ==================== */
    .face-detection-overlay {
      position: absolute;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      z-index: 5;
      pointer-events: none;
      display: flex;
      align-items: center;
      justify-content: center;
    }

    /* Oval Face Guide - Main Element */
    .face-oval-guide {
      position: absolute;
      width: 55%;
      height: 72%;
      border: 3px solid rgba(16, 185, 129, 0.7);
      border-radius: 50% / 60%;
      transition: all 0.3s ease;
      box-shadow:
        0 0 0 1000px rgba(16, 185, 129, 0.03),
        inset 0 0 30px rgba(16, 185, 129, 0.1);
    }

    .face-oval-guide.detected {
      border-color: rgba(16, 185, 129, 1);
      box-shadow:
        0 0 0 1000px rgba(16, 185, 129, 0.05),
        0 0 30px rgba(16, 185, 129, 0.4),
        inset 0 0 30px rgba(16, 185, 129, 0.2);
      animation: pulse-glow 2s infinite;
    }

    @keyframes pulse-glow {

      0%,
      100% {
        box-shadow: 0 0 0 1000px rgba(16, 185, 129, 0.05), 0 0 30px rgba(16, 185, 129, 0.4), inset 0 0 30px rgba(16, 185, 129, 0.2);
      }

      50% {
        box-shadow: 0 0 0 1000px rgba(16, 185, 129, 0.08), 0 0 50px rgba(16, 185, 129, 0.6), inset 0 0 40px rgba(16, 185, 129, 0.3);
      }
    }

    /* Corner Brackets - Outer Frame */
    .corner-bracket {
      position: absolute;
      width: 45px;
      height: 45px;
      z-index: 6;
      pointer-events: none;
      transition: all 0.3s ease;
    }

    .corner-bracket::before,
    .corner-bracket::after {
      content: '';
      position: absolute;
      background: rgba(16, 185, 129, 0.7);
      transition: all 0.3s ease;
    }

    .corner-bracket.tl {
      top: 12%;
      left: 18%;
    }

    .corner-bracket.tl::before {
      top: 0;
      left: 0;
      width: 100%;
      height: 3px;
    }

    .corner-bracket.tl::after {
      top: 0;
      left: 0;
      width: 3px;
      height: 100%;
    }

    .corner-bracket.tr {
      top: 12%;
      right: 18%;
    }

    .corner-bracket.tr::before {
      top: 0;
      right: 0;
      width: 100%;
      height: 3px;
    }

    .corner-bracket.tr::after {
      top: 0;
      right: 0;
      width: 3px;
      height: 100%;
    }

    .corner-bracket.bl {
      bottom: 18%;
      left: 18%;
    }

    .corner-bracket.bl::before {
      bottom: 0;
      left: 0;
      width: 100%;
      height: 3px;
    }

    .corner-bracket.bl::after {
      bottom: 0;
      left: 0;
      width: 3px;
      height: 100%;
    }

    .corner-bracket.br {
      bottom: 18%;
      right: 18%;
    }

    .corner-bracket.br::before {
      bottom: 0;
      right: 0;
      width: 100%;
      height: 3px;
    }

    .corner-bracket.br::after {
      bottom: 0;
      right: 0;
      width: 3px;
      height: 100%;
    }

    .corner-bracket.active::before,
    .corner-bracket.active::after {
      background: #10b981;
      box-shadow: 0 0 10px rgba(16, 185, 129, 0.8);
    }

    /* Crosshair Lines - Subtle Grid */
    .crosshair-line {
      position: absolute;
      background: linear-gradient(90deg, transparent, rgba(16, 185, 129, 0.2), transparent);
      z-index: 4;
      pointer-events: none;
    }

    .crosshair-line.horizontal {
      width: 80%;
      height: 1px;
      left: 10%;
      top: 50%;
    }

    .crosshair-line.vertical {
      width: 1px;
      height: 70%;
      left: 50%;
      top: 15%;
      background: linear-gradient(180deg, transparent, rgba(16, 185, 129, 0.2), transparent);
    }

    .switch-cam-btn {
      position: absolute;
      top: 15px;
      right: 15px;
      background: rgba(255, 255, 255, 0.2);
      border: 2px solid rgba(255, 255, 255, 0.7);
      border-radius: 50%;
      width: 40px;
      height: 40px;
      display: flex;
      align-items: center;
      justify-content: center;
      cursor: pointer;
      color: white;
      z-index: 10;
    }

    .face-status {
      text-align: center;
      margin-top: 10px;
      font-weight: 600;
      font-size: 0.95rem;
      min-height: 24px;
      transition: all 0.3s;
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 8px;
    }

    .face-status.found {
      color: var(--success-color);
    }

    .face-status.not-found {
      color: var(--warning-color);
    }

    /* Status Badge Style */
    .status-badge {
      display: inline-flex;
      align-items: center;
      gap: 6px;
      padding: 6px 14px;
      border-radius: 20px;
      font-size: 0.85rem;
      font-weight: 600;
      transition: all 0.3s;
    }

    .status-badge.detecting {
      background: rgba(107, 114, 128, 0.15);
      color: #6b7280;
    }

    .status-badge.found {
      background: rgba(16, 185, 129, 0.15);
      color: #059669;
      border: 1px solid rgba(16, 185, 129, 0.3);
    }

    .status-badge.not-found {
      background: rgba(245, 158, 11, 0.15);
      color: #d97706;
      border: 1px solid rgba(245, 158, 11, 0.3);
    }

    /* MODAL LOGOUT */
    .modal-overlay {
      position: fixed;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      background: rgba(0, 0, 0, 0.5);
      backdrop-filter: blur(4px);
      display: flex;
      align-items: center;
      justify-content: center;
      z-index: 9999;
      opacity: 0;
      visibility: hidden;
      transition: all 0.3s ease;
    }

    .modal-overlay.active {
      opacity: 1;
      visibility: visible;
    }

    .modal-box {
      background: white;
      padding: 30px;
      border-radius: 16px;
      text-align: center;
      width: 90%;
      max-width: 400px;
      box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
      transform: scale(0.9);
      transition: transform 0.3s ease;
      border: 1px solid var(--border-color);
    }

    .modal-overlay.active .modal-box {
      transform: scale(1);
    }

    .modal-icon {
      width: 60px;
      height: 60px;
      background: #fee2e2;
      color: var(--primary-color);
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
      margin: 0 auto 20px;
      font-size: 24px;
    }

    .modal-box h3 {
      margin-bottom: 10px;
      font-size: 1.25rem;
      color: var(--text-color);
    }

    .modal-box p {
      color: var(--text-muted);
      margin-bottom: 25px;
      font-size: 0.95rem;
    }

    .modal-actions {
      display: flex;
      gap: 10px;
      justify-content: center;
    }

    .btn-modal {
      padding: 12px 20px;
      border-radius: 10px;
      font-weight: 600;
      cursor: pointer;
      border: none;
      transition: all 0.2s ease;
      font-size: 0.95rem;
      flex: 1;
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

    @keyframes fadeIn {
      from {
        opacity: 0;
        transform: translateY(10px);
      }

      to {
        opacity: 1;
        transform: translateY(0);
      }
    }

    @media (max-width: 992px) {
      .sidebar {
        position: fixed;
        top: var(--header-height);
        left: auto;
        right: -260px;
        transition: right 0.3s ease;
        z-index: 999;
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

      .map-container {
        height: 250px;
      }

      /* Adjust for mobile */
      .face-oval-guide {
        width: 65%;
        height: 75%;
      }

      .corner-bracket {
        width: 35px;
        height: 35px;
      }

      .corner-bracket.tl,
      .corner-bracket.tr {
        top: 10%;
      }

      .corner-bracket.tl,
      .corner-bracket.bl {
        left: 14%;
      }

      .corner-bracket.tr,
      .corner-bracket.br {
        right: 14%;
      }

      .corner-bracket.bl,
      .corner-bracket.br {
        bottom: 22%;
      }
    }
  </style>
</head>

<body>
  <header>
    <nav class="navbar">
      <div class="nav-left">
        <div class="logo"><img src="../Gambar/logpmi.png" alt="Logo PMR"><span>PMR MILLENIUM</span></div>
      </div>
      <div class="nav-center"></div>
      <div class="nav-right">
        <div class="profile-btn" id="profileBtn"><img src="<?= $foto_profil ?>" alt="Foto Profil" class="profile-img"></div>
        <div class="profile-dropdown" id="profileDropdown">
          <div class="dropdown-header">
            <p><?= $nama_user ?></p><small><?= ucfirst($role) ?></small>
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
    <aside class="sidebar">
      <ul>
        <li><a href="anggota.php"><i class="fa-solid fa-house"></i> Dashboard</a></li>
        <li class="active"><a href="absensi.php"><i class="fa-solid fa-calendar-check"></i>Absensi</a></li>
        <li><a href="perpus.php"><i class="fa-solid fa-book"></i>Materi</a></li>
        <li style="margin-top: 20px; border-top: 1px solid #eee;">
          <a href="javascript:void(0)" onclick="openLogoutModal()"><i class="fa-solid fa-right-from-bracket"></i> Log Out</a>
        </li>
        <li><a href="../Halaman Utama/index.php"><i class="fa-solid fa-globe"></i>Halaman Utama</a></li>
      </ul>
    </aside>

    <main class="main-content">
      <div class="page-title">
        <h1>Absensi & Kehadiran</h1>
        <p>Lakukan absensi harianmu dan pantau riwayat kehadiran.</p>
      </div>

      <div class="info-grid">
        <div class="info-card">
          <div class="info-icon bg-cyan"><i class="fa-solid fa-location-crosshairs"></i></div>
          <div class="info-content">
            <h3>Akurasi GPS</h3>
            <p id="accuracy-display">Memuat...</p>
          </div>
        </div>
        <div class="info-card">
          <div class="info-icon bg-green"><i class="fa-solid fa-route"></i></div>
          <div class="info-content">
            <h3>Jarak ke <?= $config['nama_lokasi'] ?></h3>
            <p id="distance-display">Menghitung...</p>
          </div>
        </div>
      </div>

      <div id="map" class="map-container"></div>

      <section class="status-box" id="attendanceStatus">
        <div class="status-icon"><i class="fas fa-spinner fa-spin"></i></div>
        <div class="status-title">Memeriksa status absensi...</div>
      </section>

      <section class="calendar-container">
        <div class="calendar-header">
          <h2><?= date('F Y', strtotime("$year-$month-01")) ?></h2>
          <div class="calendar-nav">
            <?php
            $prev_month = $month - 1;
            $prev_year = $year;
            if ($prev_month == 0) {
              $prev_month = 12;
              $prev_year--;
            }
            $next_month = $month + 1;
            $next_year = $year;
            if ($next_month == 13) {
              $next_month = 1;
              $next_year++;
            }
            ?>
            <a href="?m=<?= $prev_month ?>&y=<?= $prev_year ?>"><i class="fas fa-chevron-left"></i></a>
            <a href="?m=<?= date('m') ?>&y=<?= date('Y') ?>">Hari Ini</a>
            <a href="?m=<?= $next_month ?>&y=<?= $next_year ?>"><i class="fas fa-chevron-right"></i></a>
          </div>
        </div>
        <div class="calendar-grid">
          <div class="calendar-day-name">Min</div>
          <div class="calendar-day-name">Sen</div>
          <div class="calendar-day-name">Sel</div>
          <div class="calendar-day-name">Rab</div>
          <div class="calendar-day-name">Kam</div>
          <div class="calendar-day-name">Jum</div>
          <div class="calendar-day-name">Sab</div>
          <?php
          $first_day = date('w', strtotime("$year-$month-01"));
          $days_in_month = cal_days_in_month(CAL_GREGORIAN, $month, $year);
          $today = date('Y-m-d');
          for ($i = 0; $i < $first_day; $i++) echo "<div class='calendar-day empty'></div>";
          for ($day = 1; $day <= $days_in_month; $day++) {
            $date_val = sprintf("%04d-%02d-%02d", $year, $month, $day);
            $is_today = ($date_val == $today) ? 'today' : '';

            // Cek apakah hari ini adalah jadwal dari database
            $is_scheduled = isset($jadwal_bulan_ini[$date_val]);
            // Cek apakah anggota sudah absen
            $has_attended = isset($riwayat_absen[$date_val]);

            echo "<div class='calendar-day $is_today'><div class='day-number'>$day</div>";

            if ($has_attended) {
              // Jika sudah hadir, tanda hijau
              echo "<div class='attendance-mark hadir' title='Hadir'><i class='fas fa-check-circle'></i></div>";
            } elseif ($is_scheduled && $date_val < $today) {
              // Jika jadwal dari database tapi sudah lewat dan tidak absen, tanda merah
              echo "<div class='attendance-mark missed' title='Tidak Hadir'><i class='fas fa-times-circle'></i></div>";
            } elseif ($is_scheduled && $date_val >= $today) {
              // Jika jadwal hari ini atau masa depan, tanda biru (icon kalender)
              echo "<div class='attendance-mark scheduled' title='Jadwal Latihan'><i class='fas fa-calendar-check'></i></div>";
            }

            echo "</div>";
          }
          ?>
        </div>
      </section>
    </main>
  </div>

  <!-- Modal Camera -->
  <div class="modal" id="cameraModal">
    <div class="modal-content">
      <div class="modal-header">
        <h3><i class="fa-solid fa-camera" style="margin-right:8px;"></i>Ambil Foto Absensi</h3>
        <button class="close-modal" id="btnCloseModal">&times;</button>
      </div>
      <div class="modal-body">
        <div class="camera-wrapper" id="cameraWrapper">
          <!-- Face Detection Overlay dengan Oval dan Corner Brackets -->
          <div class="face-detection-overlay" id="faceOverlay">
            <!-- Crosshair lines -->
            <div class="crosshair-line horizontal"></div>
            <div class="crosshair-line vertical"></div>

            <!-- Oval Face Guide -->
            <div class="face-oval-guide" id="faceOvalGuide"></div>

            <!-- Corner Brackets -->
            <div class="corner-bracket tl" id="cornerTL"></div>
            <div class="corner-bracket tr" id="cornerTR"></div>
            <div class="corner-bracket bl" id="cornerBL"></div>
            <div class="corner-bracket br" id="cornerBR"></div>
          </div>

          <button class="switch-cam-btn" id="btnSwitchCamera" title="Ganti Kamera"><i class="fa-solid fa-camera-rotate"></i></button>
          <video id="video" autoplay playsinline muted></video>
          <canvas id="overlay"></canvas>
          <canvas id="canvas" style="display:none;"></canvas>
          <img id="capturedImage" alt="Capture">
        </div>

        <!-- Status Badge -->
        <div id="faceStatus" class="face-status">
          <span class="status-badge detecting" id="statusBadge">
            <i class="fas fa-spinner fa-spin"></i> Memuat deteksi wajah...
          </span>
        </div>

        <div id="cameraControls" style="margin-top: 15px; display: flex; justify-content: center; gap: 10px; flex-wrap: wrap;">
          <button class="btn btn-primary" id="btnCapture" disabled><i class="fa-solid fa-camera"></i> Ambil Foto</button>
          <button class="btn btn-success" id="btnSubmit" style="display:none; width: 100%;"><i class="fa-solid fa-paper-plane"></i> Kirim Absensi</button>
        </div>
        <div id="successMessage" style="display:none; text-align:center;">
          <div style="color: var(--success-color); font-size: 3rem; margin: 20px 0;"><i class="fa-solid fa-check-circle"></i></div>
          <h4>Absensi Berhasil!</h4>
          <button class="btn btn-primary" style="margin-top: 15px;" onclick="location.reload()">Selesai</button>
        </div>
      </div>
    </div>
  </div>

  <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
  <script defer src="https://cdn.jsdelivr.net/npm/face-api.js@0.22.2/dist/face-api.min.js"></script>

  <script>
    // --- UI Scripts ---
    const menuToggle = document.querySelector('.menu-toggle');
    const sidebar = document.querySelector('.sidebar');
    const profileBtn = document.getElementById('profileBtn');
    const profileDropdown = document.getElementById('profileDropdown');

    menuToggle.addEventListener('click', (e) => {
      e.stopPropagation();
      sidebar.classList.toggle('active');
      profileDropdown.classList.remove('active');
    });

    profileBtn.addEventListener('click', (e) => {
      e.stopPropagation();
      profileDropdown.classList.toggle('active');
      sidebar.classList.remove('active');
    });

    document.addEventListener('click', (e) => {
      if (window.innerWidth <= 992) {
        if (!sidebar.contains(e.target) && !menuToggle.contains(e.target)) sidebar.classList.remove('active');
      }
      if (!profileBtn.contains(e.target) && !profileDropdown.contains(e.target)) profileDropdown.classList.remove('active');
    });

    const statusBox = document.getElementById('attendanceStatus');
    const accuracyDisplay = document.getElementById('accuracy-display');
    const distanceDisplay = document.getElementById('distance-display');

    // --- CONFIG ---
    const SCHOOL_LAT = <?= $config['latitude'] ?>;
    const SCHOOL_LNG = <?= $config['longitude'] ?>;
    const MAX_RADIUS = <?= $config['radius'] ?>;

    // --- MAP SETUP ---
    const map = L.map('map').setView([SCHOOL_LAT, SCHOOL_LNG], 17);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
      attribution: '&copy; OpenStreetMap contributors'
    }).addTo(map);

    const schoolIcon = L.divIcon({
      className: 'custom-icon',
      html: '<div style="background-color:#d90429; width:30px; height:30px; border-radius:50%; border:3px solid white; display:flex; align-items:center; justify-content:center; box-shadow:0 2px 5px rgba(0,0,0,0.3);"><i class="fas fa-school" style="color:white; font-size:14px;"></i></div>',
      iconSize: [30, 30],
      iconAnchor: [15, 15]
    });
    L.marker([SCHOOL_LAT, SCHOOL_LNG], {
      icon: schoolIcon
    }).addTo(map).bindPopup("<b>Lokasi Absensi</b><br>SMKN 1 Cibinong");

    const radiusCircle = L.circle([SCHOOL_LAT, SCHOOL_LNG], {
      color: '#10b981',
      fillColor: '#10b981',
      fillOpacity: 0.15,
      radius: MAX_RADIUS
    }).addTo(map);

    const userIcon = L.divIcon({
      className: 'user-marker-icon',
      html: '<div class="dot"></div>',
      iconSize: [16, 16],
      iconAnchor: [8, 8]
    });
    const userMarker = L.marker([0, 0], {
      icon: userIcon
    }).addTo(map);

    function calculateDistance(lat1, lon1, lat2, lon2) {
      const R = 6371e3;
      const φ1 = lat1 * Math.PI / 180,
        φ2 = lat2 * Math.PI / 180;
      const Δφ = (lat2 - lat1) * Math.PI / 180,
        Δλ = (lon2 - lon1) * Math.PI / 180;
      const a = Math.sin(Δφ / 2) * Math.sin(Δφ / 2) + Math.cos(φ1) * Math.cos(φ2) * Math.sin(Δλ / 2) * Math.sin(Δλ / 2);
      return R * 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));
    }

    let currentPosition = null;

    async function checkAttendanceStatus() {
      try {
        const res = await fetch('get_status_absen.php');
        const data = await res.json();

        if (!data.is_open) {
          renderStatusClosed(data.message);
          accuracyDisplay.innerText = "-";
          distanceDisplay.innerText = "-";
          return;
        }

        renderStatusLocating();

        if (!navigator.geolocation) {
          renderStatusError("Browser tidak mendukung GPS.");
          return;
        }

        navigator.geolocation.watchPosition(
          (position) => {
            currentPosition = position;
            const {
              latitude: userLat,
              longitude: userLng,
              accuracy
            } = position.coords;
            const distance = calculateDistance(userLat, userLng, SCHOOL_LAT, SCHOOL_LNG);

            accuracyDisplay.innerHTML = `± ${accuracy.toFixed(0)} meter`;
            distanceDisplay.innerHTML = `${distance.toFixed(0)} meter`;

            userMarker.setLatLng([userLat, userLng]);
            map.setView([userLat, userLng]);

            distance <= MAX_RADIUS ? renderStatusOpen(data) : renderStatusTooFar(distance);
          },
          (error) => {
            let msg = "Gagal mendapatkan lokasi.";
            if (error.code === 1) msg = "Izin lokasi ditolak.";
            renderStatusError(msg);
          }, {
            enableHighAccuracy: true,
            timeout: 10000,
            maximumAge: 0
          }
        );
      } catch (error) {
        console.error(error);
        renderStatusError("Gagal memuat data.");
      }
    }

    function renderStatusClosed(msg) {
      statusBox.className = 'status-box inactive';
      statusBox.innerHTML = `<div class="status-icon"><i class="fas fa-door-closed"></i></div><div class="status-title">${msg || "Absensi Belum Dibuka"}</div><div class="status-time">Silakan tunggu pengurus membuka absensi.</div>`;
    }

    function renderStatusLocating() {
      statusBox.className = 'status-box warning';
      statusBox.innerHTML = `<div class="status-icon"><i class="fas fa-spinner fa-spin"></i></div><div class="status-title">Mendeteksi Lokasi...</div><div class="status-time">Pastikan GPS aktif.</div>`;
    }

    function renderStatusError(msg) {
      statusBox.className = 'status-box inactive';
      statusBox.innerHTML = `<div class="status-icon"><i class="fas fa-exclamation-triangle"></i></div><div class="status-title">Error Lokasi</div><div class="status-time">${msg}</div>`;
    }

    function renderStatusTooFar(distance) {
      statusBox.className = 'status-box inactive';
      statusBox.innerHTML = `<div class="status-icon"><i class="fas fa-map-marker-alt"></i></div><div class="status-title">Di Luar Area</div><div class="status-time">Jarak Anda ${distance.toFixed(0)} meter. Radius maksimal ${MAX_RADIUS} meter.</div>`;
    }

    function renderStatusOpen(data) {
  // ✅ CEK JIKA SUDAH ABSEN
  if (data.sudah_absen) {
    renderStatusAlreadyAttended(data);
    return;
  }

  statusBox.className = 'status-box active';
  statusBox.innerHTML = `
    <div class="status-icon"><i class="fas fa-door-open"></i></div>
    <div class="status-title">Absensi Dibuka</div>
    <div class="status-time">Waktu: ${data.jam_mulai} - ${data.jam_selesai} WIB</div>
    <button class="btn btn-primary" id="btnOpenCamera"><i class="fa-solid fa-camera"></i> Absensi Sekarang</button>
  `;

  document.getElementById('btnOpenCamera').onclick = () => {
    document.getElementById('cameraModal').style.display = 'flex';
    resetModal();
    startCamera(useFrontCamera ? 'user' : 'environment');
    loadFaceModels();
  };
}

// ✅ FUNGSI BARU UNTUK YANG SUDAH ABSEN
function renderStatusAlreadyAttended(data) {
  statusBox.className = 'status-box active';
  statusBox.innerHTML = `
    <div class="status-icon" style="color: var(--success-color);"><i class="fas fa-check-circle"></i></div>
    <div class="status-title" style="color: var(--success-color);">Kamu Sudah Absen! ✅</div>
    <div class="status-time">Absensi hari ini sudah tercatat.<br>Waktu: ${data.jam_mulai} - ${data.jam_selesai} WIB</div>
  `;
}

    checkAttendanceStatus();

    // --- Camera Logic ---
    let currentImageData = null;
    let stream = null;
    let useFrontCamera = true;
    const modal = document.getElementById('cameraModal');
    const video = document.getElementById('video');
    const canvas = document.getElementById('canvas');
    const capturedImage = document.getElementById('capturedImage');
    const overlay = document.getElementById('overlay');
    const btnCapture = document.getElementById('btnCapture');
    const btnSwitch = document.getElementById('btnSwitchCamera');
    const btnSubmit = document.getElementById('btnSubmit');
    const faceStatusEl = document.getElementById('faceStatus');
    const statusBadge = document.getElementById('statusBadge');

    // Grid elements
    const faceOvalGuide = document.getElementById('faceOvalGuide');
    const cornerBrackets = [
      document.getElementById('cornerTL'),
      document.getElementById('cornerTR'),
      document.getElementById('cornerBL'),
      document.getElementById('cornerBR')
    ];
    const faceOverlay = document.getElementById('faceOverlay');

    let modelsLoaded = false;
    let faceDetectionInterval = null;

    function updateStatusBadge(type, text, icon) {
      statusBadge.className = `status-badge ${type}`;
      statusBadge.innerHTML = `<i class="${icon}"></i> ${text}`;
    }

    async function loadFaceModels() {
      if (modelsLoaded) return;
      updateStatusBadge('detecting', 'Memuat model AI...', 'fas fa-spinner fa-spin');
      try {
        await faceapi.nets.tinyFaceDetector.loadFromUri('https://cdn.jsdelivr.net/npm/@vladmandic/face-api@1.7.12/model/');
        await faceapi.nets.faceLandmark68Net.loadFromUri('https://cdn.jsdelivr.net/npm/@vladmandic/face-api@1.7.12/model/');
        modelsLoaded = true;
        updateStatusBadge('detecting', 'Arahkan wajah Anda ke kamera', 'fas fa-crosshairs');
        startFaceDetection();
      } catch (err) {
        console.error("Gagal memuat model:", err);
        updateStatusBadge('not-found', 'Gagal memuat AI', 'fas fa-exclamation-triangle');
      }
    }

    function startFaceDetection() {
      if (!modelsLoaded) return;
      if (faceDetectionInterval) clearInterval(faceDetectionInterval);

      faceDetectionInterval = setInterval(async () => {
        if (video.style.display === 'none') return;

        const detections = await faceapi.detectAllFaces(video, new faceapi.TinyFaceDetectorOptions()).withFaceLandmarks();

        if (detections.length > 0) {
          // Activate visual feedback
          faceOvalGuide.classList.add('detected');
          cornerBrackets.forEach(b => b.classList.add('active'));

          btnCapture.disabled = false;
          updateStatusBadge('found', 'Wajah terdeteksi! Silakan ambil foto.', 'fas fa-check-circle');
        } else {
          // Deactivate
          faceOvalGuide.classList.remove('detected');
          cornerBrackets.forEach(b => b.classList.remove('active'));

          btnCapture.disabled = true;
          updateStatusBadge('not-found', 'Wajah tidak terdeteksi', 'fas fa-exclamation-triangle');
        }
      }, 300);
    }

    async function startCamera(facingMode) {
      try {
        if (stream) stream.getTracks().forEach(track => track.stop());
        stream = await navigator.mediaDevices.getUserMedia({
          video: {
            facingMode: facingMode,
            width: {
              ideal: 1280
            },
            height: {
              ideal: 720
            }
          }
        });
        video.srcObject = stream;
        video.style.display = 'block';
        capturedImage.style.display = 'none';
        overlay.style.display = 'block';
        btnSwitch.style.display = 'flex';
        btnCapture.style.display = 'inline-flex';
        btnSubmit.style.display = 'none';
        video.style.transform = facingMode === 'user' ? 'scaleX(-1)' : 'scaleX(1)';

        // Show overlay
        faceOverlay.style.display = 'flex';

        if (modelsLoaded) startFaceDetection();
      } catch (err) {
        alert("Tidak dapat mengakses kamera.");
      }
    }

    document.getElementById('btnCloseModal').onclick = () => {
      modal.style.display = 'none';
      stopCamera();
    };

    window.onclick = (e) => {
      if (e.target == modal) {
        modal.style.display = 'none';
        stopCamera();
      }
    };

    function stopCamera() {
      if (stream) {
        stream.getTracks().forEach(track => track.stop());
        stream = null;
      }
      if (faceDetectionInterval) clearInterval(faceDetectionInterval);
      const ctx = overlay.getContext('2d');
      ctx.clearRect(0, 0, overlay.width, overlay.height);

      // Hide and reset overlay
      faceOverlay.style.display = 'none';
      faceOvalGuide.classList.remove('detected');
      cornerBrackets.forEach(b => b.classList.remove('active'));
    }

    btnSwitch.onclick = async () => {
      useFrontCamera = !useFrontCamera;
      await startCamera(useFrontCamera ? 'user' : 'environment');
    };

    btnCapture.onclick = () => {
      if (btnCapture.disabled) {
        alert("Wajah tidak terdeteksi!");
        return;
      }
      clearInterval(faceDetectionInterval);
      canvas.width = video.videoWidth;
      canvas.height = video.videoHeight;
      let ctx = canvas.getContext('2d');
      if (useFrontCamera) {
        ctx.translate(canvas.width, 0);
        ctx.scale(-1, 1);
      }
      ctx.drawImage(video, 0, 0, canvas.width, canvas.height);
      if (useFrontCamera) ctx.setTransform(1, 0, 0, 1, 0, 0);
      currentImageData = canvas.toDataURL('image/png');
      capturedImage.src = currentImageData;
      video.style.display = 'none';
      overlay.style.display = 'none';
      capturedImage.style.display = 'block';

      // Hide overlay when captured
      faceOverlay.style.display = 'none';

      stopCamera();
      btnSwitch.style.display = 'none';
      btnCapture.style.display = 'none';
      btnSubmit.style.display = 'inline-flex';
      updateStatusBadge('found', 'Foto siap dikirim', 'fas fa-image');
    };

    btnSubmit.onclick = () => {
      if (!currentImageData) {
        alert("Ambil foto dulu!");
        return;
      }
      btnSubmit.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Mengirim...';
      btnSubmit.disabled = true;

      fetch('proses_absensi.php', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json'
          },
          body: JSON.stringify({
            foto: currentImageData,
            lat: currentPosition?.coords.latitude,
            lng: currentPosition?.coords.longitude
          })
        })
        .then(res => res.json())
        .then(data => {
          if (data.status === 'success') {
            document.getElementById('cameraControls').style.display = 'none';
            document.getElementById('successMessage').style.display = 'block';
          } else {
            alert('Error: ' + data.message);
            btnSubmit.innerHTML = '<i class="fa-solid fa-paper-plane"></i> Kirim Absensi';
            btnSubmit.disabled = false;
          }
        })
        .catch(err => {
          console.error(err);
          alert('Gagal mengirim data.');
          btnSubmit.innerHTML = '<i class="fa-solid fa-paper-plane"></i> Kirim Absensi';
          btnSubmit.disabled = false;
        });
    };

    function resetModal() {
      video.style.display = 'block';
      capturedImage.style.display = 'none';
      overlay.style.display = 'block';
      document.getElementById('cameraControls').style.display = 'flex';
      document.getElementById('successMessage').style.display = 'none';
      btnCapture.style.display = 'inline-flex';
      btnSubmit.style.display = 'none';
      btnSubmit.innerHTML = '<i class="fa-solid fa-paper-plane"></i> Kirim Absensi';
      btnSubmit.disabled = false;
      btnCapture.disabled = true;
      updateStatusBadge('detecting', 'Memproses...', 'fas fa-spinner fa-spin');
      currentImageData = null;

      // Reset overlay
      faceOverlay.style.display = 'flex';
      faceOvalGuide.classList.remove('detected');
      cornerBrackets.forEach(b => b.classList.remove('active'));
    }

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
  </script>
</body>

</html>