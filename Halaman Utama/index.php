<?php
session_start();
include '../koneksi.php';

// Cek Login
 $is_logged_in = isset($_SESSION['nama']);
 $role = isset($_SESSION['role']) ? $_SESSION['role'] : '';
 $nama_user = $is_logged_in ? htmlspecialchars($_SESSION['nama']) : 'Guest';
 $dashboard_link = '../Login/login.php';
if ($is_logged_in) {
  $dashboard_link = ($role == 'pengurus') ? '../Dashboard Anggota/anggota.php' : '../Dashboard Anggota/anggota.php';
}

// LOGIC: Ambil Data
 $hero_query = mysqli_query($koneksi, "SELECT file_name FROM hero_background ORDER BY urutan ASC");
 $hero_images = [];
while ($row = mysqli_fetch_assoc($hero_query)) {
  if (!empty($row['file_name']) && file_exists('../Gambar/' . $row['file_name'])) {
    $hero_images[] = '../Gambar/' . htmlspecialchars($row['file_name']);
  }
}
if (empty($hero_images)) {
  $hero_images[] = '../Gambar/background.png';
}

// Ambil Pengaturan Animasi
 $set_effect = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT setting_value FROM settings WHERE setting_key='hero_effect'"));
 $set_delay = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT setting_value FROM settings WHERE setting_key='hero_delay'"));
 $hero_effect = $set_effect ? $set_effect['setting_value'] : 'slide';
 $hero_delay = $set_delay ? intval($set_delay['setting_value']) : 5000;

// Logic Footer
 $social_query = mysqli_query($koneksi, "SELECT * FROM social_links ORDER BY urutan ASC");
 $social_links = [];
while ($row = mysqli_fetch_assoc($social_query)) {
  if (!empty($row['icon_url'])) {
    $row['icon_src'] = (strpos($row['icon_url'], 'http') === 0) ? $row['icon_url'] : '../Gambar/' . $row['icon_url'];
  } else {
    $row['icon_src'] = '';
  }
  $social_links[] = $row;
}
 $setting_query = mysqli_query($koneksi, "SELECT setting_value FROM settings WHERE setting_key = 'footer_copyright'");
 $setting_data = mysqli_fetch_assoc($setting_query);
 $copyright_text = $setting_data ? $setting_data['setting_value'] : '© 2026 PMR Millenium';
?>
<!DOCTYPE html>
<html lang="id">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>PMR Millenium - SMKN 1 Cibinong</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link rel="stylesheet" href="https://unpkg.com/aos@next/dist/aos.css" />
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@9/swiper-bundle.min.css" />
  <link rel="icon" href="../Gambar/logpmi.png" type="image/png">
  <style>
    @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap');

    :root {
      --primary: #c1121f;
      --primary-dark: #9b0d18;
      --text-dark: #333333;
      --white: #ffffff;
    }

    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
    }

    html {
      scroll-behavior: smooth;
    }

    body {
      font-family: 'Poppins', sans-serif;
      background-color: #fafafa;
      color: var(--text-dark);
      overflow-x: hidden;
    }

    /* NAVBAR */
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
      color: var(--text_dark);
      font-weight: 500;
      font-size: 14px;
      transition: color 0.3s;
    }

    .nav-links li a:hover {
      color: var(--primary);
    }

    .btn-header {
      background-color: var(--primary);
      color: white !important;
      padding: 10px 20px;
      border-radius: 8px;
      font-weight: 600;
      font-size: 14px;
      display: inline-flex;
      align-items: center;
      gap: 8px;
    }

    /* HERO SWIPER */
    .hero {
      height: 100vh;
      position: relative;
      overflow: hidden;
      background: #000;
    }

    .heroSwiper {
      width: 100%;
      height: 100%;
      position: absolute;
      top: 0;
      left: 0;
      z-index: 1;
    }

    .heroSwiper .swiper-slide {
      background-size: cover;
      background-position: center;
      background-repeat: no-repeat;
    }

    .heroSwiper .swiper-pagination {
      display: none;
    }

    /* Hapus Titik */

    /* LOGIC ANIMASI ZOOM */
    .heroSwiper.zoom-mode .swiper-slide-active {
      animation: kenburns 10s ease-in-out infinite alternate;
    }

    @keyframes kenburns {
      0% {
        transform: scale(1);
      }

      100% {
        transform: scale(1.1);
      }
    }

    .hero-overlay {
      position: absolute;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      background: linear-gradient(rgba(0, 0, 0, 0.6), rgba(0, 0, 0, 0.6));
      z-index: 2;
    }

    /* [REVISED] TEKS RATA KIRI SEJAJAR NAVBAR */
    .hero-content {
      position: relative;
      z-index: 3;
      height: 100%;
      display: flex;
      flex-direction: column;
      justify-content: center;
      /* Posisi Tengah Vertikal */
      align-items: flex-start;
      /* [FIX] Posisi Kiri Horisontal */

      /* [FIX] Align dengan Navbar */
      max-width: 1300px;
      /* Samakan dengan max-width navbar */
      width: 100%;
      margin: 0 auto;
      /* Pusatkan container agar sejajar dengan header */
      padding: 0 20px;
      /* Samakan padding dengan navbar */

      color: var(--white);
    }

    .hero-title {
      font-size: 52px;
      font-weight: 700;
      line-height: 1.2;
      margin-bottom: 20px;
      text-align: left;
      /* Pastikan teks rata kiri */
      max-width: 700px;
      /* Batasi lebar agar tidak terlalu panjang */
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
      text-align: left;
      /* Pastikan teks rata kiri */
      max-width: 600px;
      /* Batasi lebar paragraf */
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

    /* SECTIONS */
    .section {
      padding: 80px 5%;
    }

    .section-title {
      text-align: center;
      font-size: 32px;
      font-weight: 700;
      margin-bottom: 10px;
    }

    .section-subtitle {
      text-align: center;
      color: #777;
      margin-bottom: 50px;
      font-size: 16px;
    }

    .container {
      max-width: 1200px;
      margin: 0 auto;
    }

    .card-container {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
      gap: 30px;
    }

    .card-visi-misi {
      background: white;
      border-radius: 15px;
      padding: 30px;
      box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
      border-top: 4px solid var(--primary);
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

    /* PENGURUS */
    .pengurus-section {
      background-color: #f0f2f5;
    }

    .staff-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
      gap: 25px;
      justify-items: center;
    }

    .card-pengurus {
      background: white;
      border-radius: 15px;
      padding: 20px;
      text-align: center;
      box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
      transition: transform 0.3s;
      width: 100%;
      max-width: 280px;
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
      border: 5px solid #f5f5f5;
      cursor: pointer;
    }

    .card-pengurus h3 {
      font-size: 18px;
      margin-bottom: 5px;
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
      color: #777;
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

    /* KEGIATAN SWIPER */
    .swiper.kegiatanSwiper {
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
      cursor: pointer;
    }

    .swiper-slide-content {
      padding: 20px;
    }

    .swiper-slide-content h3 {
      font-size: 18px;
      margin-bottom: 8px;
    }

    .swiper-slide-content p {
      font-size: 14px;
      color: #777;
      line-height: 1.6;
    }

    /* MODAL */
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
      background: white;
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
      max-height: 70vh;
      overflow: hidden;
      background: #f5f5f5;
      display: flex;
      justify-content: center;
      align-items: center;
    }

    .modal-image-container img {
      max-width: 100%;
      max-height: 70vh;
      object-fit: contain;
    }

    .modal-text-container {
      padding: 25px;
      text-align: left;
    }

    .modal-text-container h3 {
      margin-bottom: 10px;
      font-size: 20px;
    }

    .modal-text-container p {
      color: #777;
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
      z-index: 10;
    }

    /* FOOTER */
    .footer {
      background: #222;
      color: #aaa;
      text-align: center;
      padding: 40px 20px;
      font-size: 14px;
    }

    .footer .social-icons a {
      margin: 0 10px;
      display: inline-block;
    }

    .footer .social-icons img {
      width: 24px;
      height: 24px;
      opacity: 0.7;
      transition: 0.3s;
    }

    /* MOBILE */
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
      color: #333;
      font-size: 16px;
      padding: 10px 0;
      border-bottom: 1px solid #eee;
    }

    @media (max-width: 768px) {
      .hamburger {
        display: block;
      }

      .nav-links {
        display: none;
      }

      .hero {
        padding: 0;
      }

      .hero-content {
        align-items: center;
        /* Di mobile balik ke tengah agar cantik */
        padding: 0 20px;
        margin: 0;
      }

      .hero-title {
        font-size: 32px;
        text-align: center;
      }

      .hero-content p {
        text-align: center;
      }

      .btn-secondary {
        text-align: center;
      }
    }
  </style>
</head>

<body>

  <!-- NAVBAR -->
  <header>
    <nav class="navbar">
      <div class="logo"><img src="../Gambar/logpmi.png" alt="Logo"><span>PMR MILLENIUM</span></div>
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
    <button style="position:absolute; top:15px; right:15px; font-size:24px; background:none; border:none; cursor:pointer;" id="close-btn"><i class="fas fa-times"></i></button>
    <a href="#beranda"><i class="fas fa-home"></i> Beranda</a><a href="#visi-misi"><i class="fas fa-bullseye"></i> Tentang</a><a href="#pengurus"><i class="fas fa-users"></i> Pengurus</a><a href="#kegiatan"><i class="fas fa-images"></i> Kegiatan</a>
    <?php if ($is_logged_in): ?><a href="<?= $dashboard_link ?>" class="btn-header" style="text-align:center;"><i class="fas fa-tachometer-alt"></i> Dashboard</a><?php else: ?><a href="../Login/login.php" class="btn-header" style="text-align:center;"><i class="fas fa-sign-in-alt"></i> Login</a><?php endif; ?>
  </div>

  <!-- HERO -->
  <section class="hero" id="beranda">
    <div class="swiper heroSwiper">
      <div class="swiper-wrapper">
        <?php foreach ($hero_images as $img_src): ?>
          <div class="swiper-slide" style="background-image: url('<?= $img_src ?>');"></div>
        <?php endforeach; ?>
      </div>
    </div>
    <div class="hero-overlay"></div>
    <div class="hero-content">
      <h1 class="hero-title" data-aos="fade-right">Bersama Membangun <span>Generasi Peduli</span></h1>
      <p data-aos="fade-right" data-aos-delay="100">PMR Millenium - Wadah pembinaan generasi muda yang tangguh, mandiri, dan memiliki jiwa kemanusiaan yang tinggi.</p>
      <a href="../Daftar/register.php" class="btn-secondary" data-aos="fade-right" data-aos-delay="200">Bergabung Sekarang <i class="fas fa-arrow-right" style="margin-left:8px"></i></a>
    </div>
  </section>

  <!-- VISI MISI -->
  <section class="section" id="visi-misi">
    <div class="container">
      <h2 class="section-title" data-aos="fade-up">Tentang PMR</h2>
      <p class="section-subtitle" data-aos="fade-up">Memahami tujuan dan arah organisasi kita</p>
      <?php
      $q_tentang = mysqli_query($koneksi, "SELECT * FROM tentang_pmr LIMIT 1");
      $tentang = mysqli_fetch_assoc($q_tentang);
      ?>
      <div class="card-container">
        <div class="card-visi-misi" data-aos="fade-up">
          <div class="icon-title">
            <div class="icon-circle"><i class="fas fa-bullseye"></i></div>
            <h3>Visi</h3>
          </div>
          <p><?= isset($tentang['visi']) ? htmlspecialchars($tentang['visi']) : '-' ?></p>
        </div>
        <div class="card-visi-misi" data-aos="fade-up" data-aos-delay="100">
          <div class="icon-title">
            <div class="icon-circle"><i class="fas fa-tasks"></i></div>
            <h3>Program Kerja</h3>
          </div>
          <ul><?php $proker_arr = isset($tentang['program_kerja']) ? explode("\n", $tentang['program_kerja']) : [];
              foreach ($proker_arr as $item): if (trim($item)): ?><li><?= htmlspecialchars(trim($item)) ?></li><?php endif;
                                                                                                                                                                                                                  endforeach; ?></ul>
        </div>
        <div class="card-visi-misi" data-aos="fade-up" data-aos-delay="200">
          <div class="icon-title">
            <div class="icon-circle"><i class="fas fa-user-friends"></i></div>
            <h3>Misi</h3>
          </div>
          <ul><?php $misi_arr = isset($tentang['misi']) ? explode("\n", $tentang['misi']) : [];
              foreach ($misi_arr as $item): if (trim($item)): ?><li><?= htmlspecialchars(trim($item)) ?></li><?php endif;
                                                                                                                                                                                            endforeach; ?></ul>
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
      <?php if (count($leaders) > 0): ?>
        <!-- [FIX] Ukuran container diperkecil dari 800px menjadi 650px agar kartu lebih dempet -->
        <div class="staff-grid" style="max-width: 650px; margin: 0 auto 50px; gap: 20px;">
          <?php foreach ($leaders as $p): ?>
            <div class="card-pengurus" data-aos="zoom-in">
              <img src="../Gambar/<?= htmlspecialchars($p['foto']) ?>" onclick="openModalDetail(this.src, '<?= htmlspecialchars(addslashes($p['nama'])) ?>', '<?= htmlspecialchars(addslashes($p['jabatan'])) ?>')">
              <h3><?= htmlspecialchars($p['nama']) ?></h3>
              <span class="jabatan"><?= htmlspecialchars($p['jabatan']) ?></span>
              <div class="kelas"><span><?= htmlspecialchars($p['kelas']) ?></span><img src="../Gambar/<?= htmlspecialchars($p['logo_kelas']) ?>"></div>
            </div>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
      <?php if (count($top_staff) > 0): ?>
        <div class="staff-grid" style="margin-bottom: 30px;">
          <?php foreach ($top_staff as $p): ?>
            <div class="card-pengurus" data-aos="fade-up">
              <img src="../Gambar/<?= htmlspecialchars($p['foto']) ?>" onclick="openModalDetail(this.src, '<?= htmlspecialchars(addslashes($p['nama'])) ?>', '<?= htmlspecialchars(addslashes($p['jabatan'])) ?>')">
              <h3><?= htmlspecialchars($p['nama']) ?></h3><span class="jabatan"><?= htmlspecialchars($p['jabatan']) ?></span>
              <div class="kelas"><?= htmlspecialchars($p['kelas']) ?><img src="../Gambar/<?= htmlspecialchars($p['logo_kelas']) ?>"></div>
            </div>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
      <?php if (count($staff) > 0): ?>
        <div class="staff-grid">
          <?php foreach ($staff as $p): ?>
            <div class="card-pengurus" data-aos="fade-up">
              <img src="../Gambar/<?= htmlspecialchars($p['foto']) ?>" onclick="openModalDetail(this.src, '<?= htmlspecialchars(addslashes($p['nama'])) ?>', '<?= htmlspecialchars(addslashes($p['jabatan'])) ?>')">
              <h3><?= htmlspecialchars($p['nama']) ?></h3><span class="jabatan"><?= htmlspecialchars($p['jabatan']) ?></span>
              <div class="kelas"><?= htmlspecialchars($p['kelas']) ?><img src="../Gambar/<?= htmlspecialchars($p['logo_kelas']) ?>"></div>
            </div>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
    </div>
  </section>

  <!-- KEGIATAN -->
  <section class="section" id="kegiatan" style="background: #fff;">
    <div class="container">
      <h2 class="section-title" data-aos="fade-down">Dokumentasi Kegiatan</h2>
      <p class="section-subtitle" data-aos="fade-up">Momen penting dan aktivitas rutin PMR Millenium</p>
      <div class="swiper kegiatanSwiper" data-aos="fade-up">
        <div class="swiper-wrapper">
          <?php $keg = mysqli_query($koneksi, "SELECT * FROM kegiatan ORDER BY id DESC");
          while ($k = mysqli_fetch_assoc($keg)): ?>
            <div class="swiper-slide">
              <img src="../Gambar/<?= htmlspecialchars($k['gambar']) ?>" onclick="openModalDetail(this.src, '<?= htmlspecialchars(addslashes($k['judul'])) ?>', '<?= htmlspecialchars(addslashes($k['deskripsi'])) ?>')">
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

  <!-- LOMBA -->
  <section class="section" style="background-color: #f0f2f5;" id="lomba">
    <div class="container">
      <!-- <h2 class="section-title" data-aos="fade-down">Prestasi & Lomba</h2>
      <p class="section-subtitle" data-aos="fade-up">Pencapaian membanggakan PMR Millenium</p> -->
      <div class="swiper kegiatanSwiper" data-aos="fade-up">
        <div class="swiper-wrapper">
          <?php $lom = mysqli_query($koneksi, "SELECT * FROM lomba ORDER BY id DESC");
          while ($l = mysqli_fetch_assoc($lom)): ?>
            <div class="swiper-slide">
              <img src="../Gambar/<?= htmlspecialchars($l['gambar']) ?>" onclick="openModalDetail(this.src, '<?= htmlspecialchars(addslashes($l['judul'])) ?>', '<?= htmlspecialchars(addslashes($l['deskripsi'])) ?>')">
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

  <!-- MODAL -->
  <div id="fullPreview" class="modal"><span class="close-btn" onclick="closeModal()">&times;</span>
    <div class="modal-content-box">
      <div class="modal-image-container"><img id="modal-img" src=""></div>
      <div class="modal-text-container">
        <h3 id="modal-title"></h3>
        <p id="modal-desc"></p>
      </div>
    </div>
  </div>

  <!-- FOOTER -->
  <footer class="footer">
    <div class="social-icons">
      <?php if (count($social_links) > 0): ?><?php foreach ($social_links as $sosmed): ?><a href="<?= htmlspecialchars($sosmed['url']) ?>" target="_blank"><?php if (!empty($sosmed['icon_src'])): ?><img src="<?= $sosmed['icon_src'] ?>"><?php endif; ?></a><?php endforeach; ?><?php endif; ?>
    </div>
    <p><?= htmlspecialchars($copyright_text) ?></p>
  </footer>

  <!-- Scripts -->
  <script src="https://unpkg.com/aos@next/dist/aos.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/swiper@9/swiper-bundle.min.js"></script>
  <script>
    AOS.init({
      once: true,
      duration: 800
    });
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

    function openModalDetail(src, title, desc) {
      document.getElementById('modal-img').src = src;
      document.getElementById('modal-title').innerText = title;
      document.getElementById('modal-desc').innerText = desc;
      document.getElementById('fullPreview').style.display = 'flex';
    }

    function closeModal() {
      document.getElementById('fullPreview').style.display = 'none';
    }
    document.getElementById('fullPreview').addEventListener('click', (e) => {
      if (e.target.closest('.modal-content-box') === null) closeModal();
    });

    // HERO SWIPER
    var heroEffectMode = '<?= $hero_effect ?>';
    var heroSwiper = new Swiper(".heroSwiper", {
      spaceBetween: 0,
      centeredSlides: true,
      autoplay: {
        delay: <?= $hero_delay ?>,
        disableOnInteraction: false
      },
      effect: 'slide',
      grabCursor: true,
      loop: true,
      on: {
        init: function() {
          if (heroEffectMode === 'zoom') {
            this.el.classList.add('zoom-mode');
          }
        }
      }
    });

    var swiperKegiatan = new Swiper(".kegiatanSwiper", {
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
    });
  </script>
</body>

</html>