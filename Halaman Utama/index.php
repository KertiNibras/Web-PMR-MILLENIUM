<?php
session_start();
include '../koneksi.php'; // Pastikan path ke koneksi benar

// Cek status login
$is_logged_in = isset($_SESSION['nama']);
$role = isset($_SESSION['role']) ? $_SESSION['role'] : '';
$nama_user = $is_logged_in ? htmlspecialchars($_SESSION['nama']) : 'Guest';

// Tentukan link dashboard berdasarkan role
$dashboard_link = '../Login/login.php';
if ($is_logged_in) {
  if ($role == 'pengurus') {
    $dashboard_link = '../Dashboard Anggota/anggota.php';
  } else {
    $dashboard_link = '../Dashboard Anggota/anggota.php';
  }
}

// LOGIC: Ambil gambar hero dari database
$hero_query = mysqli_query($koneksi, "SELECT file_name FROM hero_background ORDER BY id DESC LIMIT 1");
$hero_data = mysqli_fetch_assoc($hero_query);
$hero_image = '../Gambar/background.png'; // Default fallback

if ($hero_data && !empty($hero_data['file_name'])) {
  if (file_exists('../Gambar/' . $hero_data['file_name'])) {
    $hero_image = '../Gambar/' . htmlspecialchars($hero_data['file_name']);
  }
}
?>
<!DOCTYPE html>
<html lang="id">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>PMR Millenium - SMKN 1 Cibinong</title>

  <!-- Libraries CSS -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link rel="stylesheet" href="https://unpkg.com/aos@next/dist/aos.css" />
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@9/swiper-bundle.min.css" />

  <link rel="icon" href="../Gambar/logpmi.png" type="image/png">

  <style>
    @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap');

    :root {
      --primary: #c1121f;
      --primary-dark: #9b0d18;
      --secondary: #f5f5f5;
      --text-dark: #333333;
      --text-light: #777777;
      --white: #ffffff;
      --shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
    }

    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
    }

    html {
      scroll-behavior: smooth;
    }

    section {
      scroll-margin-top: 80px;
    }

    body {
      font-family: 'Poppins', sans-serif;
      background-color: #fafafa;
      color: var(--text-dark);
      overflow-x: hidden;
    }

    /* --- NAVBAR --- */
    header {
      background: var(--white);
      box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
      position: fixed;
      width: 100%;
      z-index: 1000;
      top: 0;
    }

    .navbar {
      max-width: 1300px;
      margin: 0 auto;
      display: flex;
      justify-content: space-between;
      align-items: center;
      padding: 15px 20px;
    }

    .logo {
      display: flex;
      align-items: center;
      gap: 10px;
      font-weight: 700;
      color: var(--text-dark);
      font-size: 18px;
    }

    .logo img {
      height: 45px;
    }

    .nav-links {
      list-style: none;
      display: flex;
      align-items: center;
      gap: 25px;
    }

    .nav-links li a {
      text-decoration: none;
      color: var(--text-dark);
      font-weight: 500;
      font-size: 14px;
      display: flex;
      align-items: center;
      gap: 6px;
      transition: color 0.3s;
    }

    .nav-links li a:hover {
      color: var(--primary);
    }

    .btn-header {
      background-color: var(--primary);
      color: white !important;
      text-decoration: none;
      padding: 10px 20px;
      border-radius: 8px;
      font-weight: 600;
      font-size: 14px;
      transition: background-color 0.3s;
      display: inline-flex;
      align-items: center;
      gap: 8px;
    }

    .btn-header:hover {
      background-color: var(--primary-dark);
    }

    /* --- HERO SECTION --- */
    .hero {
      min-height: 100vh;
      background: linear-gradient(rgba(0, 0, 0, 0.6), rgba(0, 0, 0, 0.6));
      background-position: center;
      background-size: cover;
      background-repeat: no-repeat;
      display: flex;
      align-items: center;
      padding: 0 10%;
      color: var(--white);
      position: relative;
    }

    .hero-content {
      max-width: 700px;
      z-index: 2;
    }

    .hero-title {
      font-size: 52px;
      font-weight: 700;
      line-height: 1.2;
      margin-bottom: 20px;
    }

    .hero-title span {
      color: var(--primary);
      background: white;
      padding: 0 10px;
      border-radius: 4px;
      display: inline-block;
    }

    .hero-content p {
      font-size: 18px;
      margin-bottom: 30px;
      opacity: 0.9;
      line-height: 1.6;
    }

    .btn-secondary {
      background-color: transparent;
      color: var(--white);
      border: 2px solid var(--white);
      padding: 12px 30px;
      border-radius: 8px;
      font-weight: 600;
      text-decoration: none;
      transition: all 0.3s;
      display: inline-block;
    }

    .btn-secondary:hover {
      background: var(--white);
      color: var(--primary);
    }

    /* --- GENERAL SECTION --- */
    .section {
      padding: 80px 5%;
    }

    .section-title {
      text-align: center;
      font-size: 32px;
      font-weight: 700;
      margin-bottom: 10px;
      color: var(--text-dark);
    }

    .section-subtitle {
      text-align: center;
      color: var(--text-light);
      margin-bottom: 50px;
      font-size: 16px;
    }

    .container {
      max-width: 1200px;
      margin: 0 auto;
    }

    /* --- VISI MISI --- */
    .visi-misi-section {
      background: var(--white);
    }

    .card-container {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
      gap: 30px;
    }

    .card-visi-misi {
      background: var(--white);
      border-radius: 15px;
      padding: 30px;
      box-shadow: var(--shadow);
      border-top: 4px solid var(--primary);
      transition: transform 0.3s;
    }

    .card-visi-misi:hover {
      transform: translateY(-5px);
    }
    .top-staff-grid {
  display: flex;
  grid-template-columns: repeat(5, 1fr);
  gap: 20px;
  max-width: 1200px;
  margin: 0 auto 30px;
  justify-content: center;
  flex-wrap: wrap;
}
.top-staff-grid .card-pengurus {
  width: 200px;
}


    .icon-title {
      display: flex;
      align-items: center;
      gap: 15px;
      margin-bottom: 20px;
    }

    .icon-circle {
      background-color: rgba(193, 18, 31, 0.1);
      color: var(--primary);
      border-radius: 50%;
      width: 50px;
      height: 50px;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 20px;
    }

    .card-visi-misi h3 {
      font-size: 20px;
      margin: 0;
      color: var(--text-dark);
    }

    .card-visi-misi p,
    .card-visi-misi ul {
      color: var(--text-light);
      font-size: 15px;
      line-height: 1.8;
    }

    .card-visi-misi ul {
      padding-left: 20px;
    }

    /* --- PENGURUS --- */
    .pengurus-section {
      background-color: #f0f2f5;
    }

    .leader-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
      gap: 30px;
      max-width: 800px;
      margin: 0 auto 50px;
    }

    .staff-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
      gap: 25px;
    }

    .card-pengurus {
      background: var(--white);
      border-radius: 15px;
      padding: 20px;
      text-align: center;
      box-shadow: var(--shadow);
      transition: transform 0.3s;
    }

    .card-pengurus:hover {
      transform: translateY(-10px);
    }

    .card-pengurus img {
      width: 120px;
      height: 120px;
      object-fit: cover;
      border-radius: 50%;
      margin: 20px auto 15px;
      border: 5px solid var(--secondary);
      cursor: pointer;
      transition: border-color 0.3s;
    }

    .card-pengurus img:hover {
      border-color: var(--primary);
    }

    .card-pengurus h3 {
      font-size: 18px;
      margin-bottom: 5px;
      color: var(--text-dark);
    }

    .card-pengurus .jabatan {
      color: var(--primary);
      font-weight: 600;
      font-size: 14px;
      margin-bottom: 5px;
      display: block;
    }

    .card-pengurus .kelas {
      display: inline-flex;
      align-items: center;
      gap: 5px;
      font-size: 12px;
      color: var(--text-light);
      background: #f5f5f5;
      padding: 4px 10px;
      border-radius: 20px;
    }

    .card-pengurus .kelas img {
      width: 16px;
      height: 16px;
      border: none;
      border-radius: 0;
      margin: 0;
    }

    /* --- SWIPER CAROUSEL --- */
    .swiper {
      width: 100%;
      padding: 20px 0 50px;
    }

    .swiper-slide {
      background: #fff;
      border-radius: 15px;
      overflow: hidden;
      box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
      height: auto;
      border: 1px solid #eee;
    }

    .swiper-slide img {
      width: 100%;
      height: 250px;
      object-fit: cover;
      display: block;
      cursor: pointer;
      transition: transform 0.3s;
    }

    .swiper-slide img:hover {
      transform: scale(1.02);
    }

    .swiper-slide-content {
      padding: 20px;
    }

    .swiper-slide-content h3 {
      font-size: 18px;
      margin-bottom: 8px;
      color: var(--text-dark);
    }

    .swiper-slide-content p {
      font-size: 14px;
      color: var(--text-light);
      line-height: 1.6;
    }

    .swiper-pagination-bullet {
      background-color: var(--primary);
      opacity: 0.3;
    }

    .swiper-pagination-bullet-active {
      opacity: 1;
    }

    /* --- MODAL STYLING --- */
    .modal {
      display: none;
      position: fixed;
      z-index: 9999;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      background: rgba(0, 0, 0, 0.85);
      justify-content: center;
      align-items: center;
      backdrop-filter: blur(5px);
    }

    .modal-content-box {
      background: var(--white);
      border-radius: 12px;
      max-width: 600px;
      width: 90%;
      max-height: 90vh;
      overflow: hidden;
      display: flex;
      flex-direction: column;
      box-shadow: 0 20px 40px rgba(0, 0, 0, 0.3);
      animation: popUp 0.3s ease-out;
    }

    @keyframes popUp {
      from {
        transform: scale(0.8);
        opacity: 0;
      }

      to {
        transform: scale(1);
        opacity: 1;
      }
    }

    .modal-image-container {
      width: 100%;
      max-height: 400px;
      overflow: hidden;
      background: #f5f5f5;
    }

    .modal-image-container img {
      width: 100%;
      height: auto;
      object-fit: cover;
      display: block;
    }

    .modal-text-container {
      padding: 25px;
      text-align: left;
    }

    .modal-text-container h3 {
      margin-bottom: 10px;
      color: var(--text-dark);
      font-size: 20px;
    }

    .modal-text-container p {
      color: var(--text-light);
      font-size: 14px;
      line-height: 1.6;
      margin: 0;
    }

    .modal .close-btn {
      position: fixed;
      top: 20px;
      right: 25px;
      color: white;
      font-size: 35px;
      font-weight: bold;
      cursor: pointer;
      transition: 0.3s;
      z-index: 10;
      text-shadow: 0 2px 4px rgba(0, 0, 0, 0.5);
    }

    .modal .close-btn:hover {
      color: var(--primary);
    }

    /* --- FOOTER --- */
    .footer {
      background: #222;
      color: #aaa;
      text-align: center;
      padding: 40px 20px;
      font-size: 14px;
    }

    .footer .social-icons {
      margin-bottom: 15px;
    }

    .footer .social-icons a {
      margin: 0 10px;
      display: inline-block;
    }

    .footer .social-icons img {
      width: 24px;
      height: 24px;
      filter: brightness(0) invert(1);
      opacity: 0.7;
      transition: 0.3s;
    }

    .footer .social-icons a:hover img {
      opacity: 1;
      transform: scale(1.1);
    }

    /* --- MOBILE MENU --- */
    .hamburger {
      display: none;
      font-size: 24px;
      background: none;
      border: none;
      cursor: pointer;
    }

    .menu-overlay {
      position: fixed;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      background: rgba(0, 0, 0, 0.5);
      z-index: 1001;
      opacity: 0;
      visibility: hidden;
      transition: 0.3s;
    }

    .menu-overlay.active {
      opacity: 1;
      visibility: visible;
    }

    .mobile-menu {
      position: fixed;
      top: 0;
      right: -100%;
      width: 280px;
      height: 100%;
      background: white;
      z-index: 1002;
      padding: 60px 20px;
      transition: right 0.4s;
      display: flex;
      flex-direction: column;
      gap: 20px;
    }

    .mobile-menu.active {
      right: 0;
    }

    .mobile-menu a {
      text-decoration: none;
      color: var(--text-dark);
      font-size: 16px;
      display: flex;
      align-items: center;
      gap: 10px;
      padding: 10px 0;
      border-bottom: 1px solid #eee;
    }

    .mobile-menu a:hover {
      color: var(--primary);
    }

    .mobile-close {
      position: absolute;
      top: 15px;
      right: 15px;
      font-size: 24px;
      background: none;
      border: none;
      cursor: pointer;
    }

    /* --- RESPONSIVE --- */
    @media (max-width: 768px) {
      .hamburger {
        display: block;
      }

      .nav-links {
        display: none;
      }

      .hero {
        padding: 0 20px;
        text-align: center;
      }

      .hero-title {
        font-size: 32px;
      }

      .hero-content p {
        font-size: 16px;
      }

      .leader-grid,
      .staff-grid {
        grid-template-columns: 1fr;
        padding: 0 10px;
      }

      .modal-content-box {
        width: 95%;
        max-height: 85vh;
      }

      .modal-text-container {
        padding: 15px;
      }
    }
  </style>
</head>

<body>

  <!-- NAVBAR -->
  <header>
    <nav class="navbar">
      <div class="logo">
        <img src="../Gambar/logpmi.png" alt="Logo PMR">
        <span>PMR MILLENIUM</span>
      </div>
      <ul class="nav-links">
        <li><a href="#beranda"><i class="fas fa-home"></i> Beranda</a></li>
        <li><a href="#visi-misi"><i class="fas fa-bullseye"></i> Tentang</a></li>
        <li><a href="#pengurus"><i class="fas fa-users"></i> Pengurus</a></li>
        <li><a href="#kegiatan"><i class="fas fa-images"></i> Kegiatan</a></li>
        <li><a href="https://uks-smartcare.smkn1cibinong.sch.id/" target="_blank"><i class="fas fa-heartbeat"></i> UKS</a></li>

        <?php if ($is_logged_in): ?>
          <li><a href="<?= $dashboard_link ?>" class="btn-header"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
        <?php else: ?>
          <li><a href="../Login/login.php" class="btn-header"><i class="fas fa-sign-in-alt"></i> Login</a></li>
        <?php endif; ?>
      </ul>
      <button class="hamburger" id="hamburger-btn"><i class="fas fa-bars"></i></button>
    </nav>
  </header>

  <div class="menu-overlay" id="menu-overlay"></div>
  <div class="mobile-menu" id="mobile-menu">
    <button class="mobile-close" id="close-btn"><i class="fas fa-times"></i></button>
    <a href="#beranda"><i class="fas fa-home"></i> Beranda</a>
    <a href="#visi-misi"><i class="fas fa-bullseye"></i> Tentang</a>
    <a href="#pengurus"><i class="fas fa-users"></i> Pengurus</a>
    <a href="#kegiatan"><i class="fas fa-images"></i> Kegiatan</a>
    <a href="https://uks-smartcare.smkn1cibinong.sch.id/" target="_blank"><i class="fas fa-heartbeat"></i> UKS</a>

    <?php if ($is_logged_in): ?>
      <a href="<?= $dashboard_link ?>" class="btn-header" style="justify-content: center; margin-top: 20px; color: white !important;"><i class="fas fa-tachometer-alt"></i> Dashboard</a>
    <?php else: ?>
      <a href="../Login/login.php" class="btn-header" style="justify-content: center; margin-top: 20px; color: white !important;"><i class="fas fa-sign-in-alt"></i> Login</a>
    <?php endif; ?>
  </div>

  <!-- HERO -->
  <section class="hero" id="beranda" style="background-image: linear-gradient(rgba(0,0,0,0.6), rgba(0,0,0,0.6)), url('<?= $hero_image ?>');">
    <div class="hero-content">
      <h1 class="hero-title" data-aos="fade-right">Bersama Membangun <span>Generasi Peduli</span></h1>
      <p data-aos="fade-right" data-aos-delay="100">
        PMR Millenium - Wadah pembinaan generasi muda yang tangguh, mandiri, dan memiliki jiwa kemanusiaan yang tinggi.
      </p>
      <a href="../Daftar/register.php" class="btn-secondary" data-aos="fade-right" data-aos-delay="200">
        Bergabung Sekarang <i class="fas fa-arrow-right" style="margin-left:8px"></i>
      </a>
    </div>
  </section>

  <!-- VISI MISI -->
  <section class="section visi-misi-section" id="visi-misi">
    <div class="container">
      <h2 class="section-title" data-aos="fade-up">Tentang PMR</h2>
      <p class="section-subtitle" data-aos="fade-up">Memahami tujuan dan arah organisasi kita</p>

      <?php
      $q_tentang = mysqli_query($koneksi, "SELECT * FROM tentang_pmr LIMIT 1");
      $tentang = mysqli_fetch_assoc($q_tentang);
      $misi_arr = isset($tentang['misi']) ? explode("\n", $tentang['misi']) : [];
      $proker_arr = isset($tentang['program_kerja']) ? explode("\n", $tentang['program_kerja']) : [];
      ?>

      <div class="card-container">
        <div class="card-visi-misi" data-aos="fade-up">
          <div class="icon-title">
            <div class="icon-circle"><i class="fas fa-bullseye"></i></div>
            <h3>Visi</h3>
          </div>
          <p><?= isset($tentang['visi']) ? htmlspecialchars($tentang['visi']) : 'Visi belum diatur.' ?></p>
        </div>

        <div class="card-visi-misi" data-aos="fade-up" data-aos-delay="100">
          <div class="icon-title">
            <div class="icon-circle"><i class="fas fa-tasks"></i></div>
            <h3>Program Kerja</h3>
          </div>
          <ul>
            <?php foreach ($proker_arr as $item): if (trim($item)): ?>
                <li><?= htmlspecialchars(trim($item)) ?></li>
            <?php endif;
            endforeach; ?>
          </ul>
        </div>

        <div class="card-visi-misi" data-aos="fade-up" data-aos-delay="200">
          <div class="icon-title">
            <div class="icon-circle"><i class="fas fa-user-friends"></i></div>
            <h3>Misi</h3>
          </div>
          <ul>
            <?php foreach ($misi_arr as $item): if (trim($item)): ?>
                <li><?= htmlspecialchars(trim($item)) ?></li>
            <?php endif;
            endforeach; ?>
          </ul>
        </div>
      </div>
    </div>
  </section>

  <!-- PENGURUS -->
  <section class="section pengurus-section" id="pengurus">
    <h2 class="section-title" data-aos="fade-down">Struktur Organisasi</h2>
    <p class="section-subtitle" data-aos="fade-up">Kepengurusan PMR Millenium Tahun 2026</p>

    <div class="container">
      <?php
      $q_all = mysqli_query($koneksi, "SELECT * FROM pengurus ORDER BY urutan ASC");
      $all_data = [];
      while ($row = mysqli_fetch_assoc($q_all)) {
        $all_data[] = $row;
      }

      $leaders = array_slice($all_data, 0, 2);
      $top_staff = array_slice($all_data, 2, 4);
      $staff = array_slice($all_data, 6);
      ?>

      <!-- Level 1: Pimpinan Utama -->
      <?php if (count($leaders) > 0): ?>
        <div class="leader-grid">
          <?php foreach ($leaders as $p): ?>
            <div class="card-pengurus" data-aos="zoom-in">
              <img src="../Gambar/<?= htmlspecialchars($p['foto']) ?>" alt="<?= htmlspecialchars($p['nama']) ?>"
                onclick="openModalDetail(this.src, '<?= htmlspecialchars(addslashes($p['nama'])) ?>', 'Jabatan: <?= htmlspecialchars(addslashes($p['jabatan'])) ?> - Kelas: <?= htmlspecialchars(addslashes($p['kelas'])) ?>')">
              <h3><?= htmlspecialchars($p['nama']) ?></h3>
              <span class="jabatan"><?= htmlspecialchars($p['jabatan']) ?></span>
              <div class="kelas">
                <span><?= htmlspecialchars($p['kelas']) ?></span>
                <img src="../Gambar/<?= htmlspecialchars($p['logo_kelas']) ?>" alt="Logo">
              </div>
            </div>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>

      <!-- Level 2: Pejabat Tinggi -->
      <?php if (count($top_staff) > 0): ?>
       <div class="top-staff-grid">
          <?php foreach ($top_staff as $p): ?>
            <div class="card-pengurus" data-aos="fade-up">
              <img src="../Gambar/<?= htmlspecialchars($p['foto']) ?>" alt="<?= htmlspecialchars($p['nama']) ?>"
                onclick="openModalDetail(this.src, '<?= htmlspecialchars(addslashes($p['nama'])) ?>', 'Jabatan: <?= htmlspecialchars(addslashes($p['jabatan'])) ?> - Kelas: <?= htmlspecialchars(addslashes($p['kelas'])) ?>')">
              <h3><?= htmlspecialchars($p['nama']) ?></h3>
              <span class="jabatan"><?= htmlspecialchars($p['jabatan']) ?></span>
              <div class="kelas">
                <?= htmlspecialchars($p['kelas']) ?>
                <img src="../Gambar/<?= htmlspecialchars($p['logo_kelas']) ?>" alt="Logo">
              </div>
            </div>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>

      <!-- Level 3: Anggota/Sie -->
      <?php if (count($staff) > 0): ?>
        <div class="staff-grid">
          <?php foreach ($staff as $p): ?>
            <div class="card-pengurus" data-aos="fade-up">
              <img src="../Gambar/<?= htmlspecialchars($p['foto']) ?>" alt="<?= htmlspecialchars($p['nama']) ?>"
                onclick="openModalDetail(this.src, '<?= htmlspecialchars(addslashes($p['nama'])) ?>', 'Jabatan: <?= htmlspecialchars(addslashes($p['jabatan'])) ?> - Kelas: <?= htmlspecialchars(addslashes($p['kelas'])) ?>')">
              <h3><?= htmlspecialchars($p['nama']) ?></h3>
              <span class="jabatan"><?= htmlspecialchars($p['jabatan']) ?></span>
              <div class="kelas">
                <?= htmlspecialchars($p['kelas']) ?>
                <img src="../Gambar/<?= htmlspecialchars($p['logo_kelas']) ?>" alt="Logo">
              </div>
            </div>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
    </div>
  </section>

  <!-- KEGIATAN SECTION -->
  <section class="section carousel-section" id="kegiatan" style="background: #fff;">
    <div class="container">
      <h2 class="section-title" data-aos="fade-down">Dokumentasi Kegiatan</h2>
      <p class="section-subtitle" data-aos="fade-up">Momen penting dan aktivitas rutin PMR Millenium</p>

      <div class="swiper kegiatanSwiper" data-aos="fade-up">
        <div class="swiper-wrapper">
          <?php
          $keg = mysqli_query($koneksi, "SELECT * FROM kegiatan ORDER BY id DESC");
          while ($k = mysqli_fetch_assoc($keg)):
          ?>
            <div class="swiper-slide">
              <img src="../Gambar/<?= htmlspecialchars($k['gambar']) ?>" alt="<?= htmlspecialchars($k['judul']) ?>"
                onclick="openModalDetail(this.src, '<?= htmlspecialchars(addslashes($k['judul'])) ?>', '<?= htmlspecialchars(addslashes($k['deskripsi'])) ?>')">
              <div class="swiper-slide-content">
                <h3><?= htmlspecialchars($k['judul']) ?></h3>
                <p><?= htmlspecialchars($k['deskripsi']) ?></p>
              </div>
            </div>
          <?php endwhile; ?>
        </div>
        <div class="swiper-pagination"></div>
      </div>
    </div>
  </section>

  <!-- LOMBA SECTION -->
  <section class="section" style="background-color: #f0f2f5;" id="lomba">
    <div class="container">
      <h2 class="section-title" data-aos="fade-down">Prestasi & Lomba</h2>
      <p class="section-subtitle" data-aos="fade-up">Pencapaian membanggakan PMR Millenium</p>

      <div class="swiper lombaSwiper" data-aos="fade-up">
        <div class="swiper-wrapper">
          <?php
          $lom = mysqli_query($koneksi, "SELECT * FROM lomba ORDER BY id DESC");
          while ($l = mysqli_fetch_assoc($lom)):
          ?>
            <div class="swiper-slide">
              <img src="../Gambar/<?= htmlspecialchars($l['gambar']) ?>" alt="<?= htmlspecialchars($l['judul']) ?>"
                onclick="openModalDetail(this.src, '<?= htmlspecialchars(addslashes($l['judul'])) ?>', '<?= htmlspecialchars(addslashes($l['deskripsi'])) ?>')">
              <div class="swiper-slide-content">
                <h3><?= htmlspecialchars($l['judul']) ?></h3>
                <p><?= htmlspecialchars($l['deskripsi']) ?></p>
              </div>
            </div>
          <?php endwhile; ?>
        </div>
        <div class="swiper-pagination"></div>
      </div>
    </div>
  </section>

  <!-- MODAL PREVIEW -->
  <div id="fullPreview" class="modal">
    <span class="close-btn" onclick="closeModal()">&times;</span>
    <div class="modal-content-box">
      <div class="modal-image-container">
        <img id="modal-img" src="" alt="Preview">
      </div>
      <div class="modal-text-container">
        <h3 id="modal-title">Judul Disini</h3>
        <p id="modal-desc">Deskripsi disini...</p>
      </div>
    </div>
  </div>

  <!-- FOOTER -->
  <footer class="footer">
    <div class="social-icons">
      <a href="https://www.instagram.com/pmrmillenium" target="_blank"><img src="https://cdn.jsdelivr.net/gh/simple-icons/simple-icons/icons/instagram.svg" alt="IG"></a>
      <a href="#"><img src="https://cdn.jsdelivr.net/gh/simple-icons/simple-icons/icons/youtube.svg" alt="YT"></a>
      <a href="#"><img src="https://cdn.jsdelivr.net/gh/simple-icons/simple-icons/icons/facebook.svg" alt="FB"></a>
    </div>
    <p>© 2026 PMR Millenium SMKN 1 Cibinong. All Rights Reserved.</p>
  </footer>

  <!-- Scripts -->
  <script src="https://unpkg.com/aos@next/dist/aos.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/swiper@9/swiper-bundle.min.js"></script>

  <script>
    // Init AOS
    AOS.init({
      once: true,
      duration: 800
    });

    // Mobile Menu
    const hamburger = document.getElementById('hamburger-btn');
    const mobileMenu = document.getElementById('mobile-menu');
    const overlay = document.getElementById('menu-overlay');
    const closeBtn = document.getElementById('close-btn');

    hamburger.addEventListener('click', () => {
      mobileMenu.classList.add('active');
      overlay.classList.add('active');
    });

    function closeMenu() {
      mobileMenu.classList.remove('active');
      overlay.classList.remove('active');
    }
    overlay.addEventListener('click', closeMenu);
    closeBtn.addEventListener('click', closeMenu);
    document.querySelectorAll('.mobile-menu a').forEach(link => {
      link.addEventListener('click', closeMenu);
    });

    // Modal Functions
    function openModalDetail(src, title, desc) {
      document.getElementById('modal-img').src = src;
      document.getElementById('modal-title').innerText = title;
      document.getElementById('modal-desc').innerText = desc;
      document.getElementById('fullPreview').style.display = 'flex';
      document.body.style.overflow = 'hidden';
    }

    function closeModal() {
      document.getElementById('fullPreview').style.display = 'none';
      document.body.style.overflow = 'auto';
    }

    document.getElementById('fullPreview').addEventListener('click', (e) => {
      if (e.target.closest('.modal-content-box') === null) {
        closeModal();
      }
    });

    // Swiper Init
    var swiperConfig = {
      slidesPerView: 1,
      spaceBetween: 20,
      loop: true,
      grabCursor: true,
      pagination: {
        el: ".swiper-pagination",
        clickable: true
      },
      breakpoints: {
        640: {
          slidesPerView: 2
        },
        1024: {
          slidesPerView: 3
        }
      }
    };
    var swiperKegiatan = new Swiper(".kegiatanSwiper", swiperConfig);
    var swiperLomba = new Swiper(".lombaSwiper", swiperConfig);
  </script>
</body>

</html>