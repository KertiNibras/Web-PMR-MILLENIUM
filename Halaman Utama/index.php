<?php
session_start();
include '../koneksi.php'; // Pastikan path ke koneksi benar. Jika file ini ada di folder 'Halaman Utama', maka pathnya '../koneksi.php'

// Cek status login
 $is_logged_in = isset($_SESSION['nama']);
 $role = isset($_SESSION['role']) ? $_SESSION['role'] : '';
 $nama_user = $is_logged_in ? htmlspecialchars($_SESSION['nama']) : 'Guest';

// Tentukan link dashboard berdasarkan role
 $dashboard_link = '../Login/login.php'; // Default jika belum login
if ($is_logged_in) {
    if ($role == 'pengurus') {
        $dashboard_link = '../Dashboard Anggota/anggota.php'; // Atau halaman utama pengurus
    } else {
        $dashboard_link = '../Dashboard Anggota/anggota.php';
    }
}
?>
<!DOCTYPE html>
<html lang="id">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>PMR Millenium - SMKN 1 Cibinong</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link rel="stylesheet" href="https://unpkg.com/aos@next/dist/aos.css" />
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
      background: linear-gradient(rgba(0,0,0,0.6), rgba(0,0,0,0.6)), url('../Gambar/background.png') center/cover no-repeat;
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

    /* --- GENERAL SECTION STYLING --- */
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

    .card-visi-misi p, .card-visi-misi ul {
      color: var(--text-light);
      font-size: 15px;
      line-height: 1.8;
    }

    .card-visi-misi ul {
      padding-left: 20px;
    }

    /* --- PENGURUS SECTION --- */
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
      width: 180px;
      height: 180px;
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

    /* --- CAROUSEL SECTION --- */
    .carousel-section {
      background: var(--white);
    }

    .carousel-wrapper {
      position: relative;
      max-width: 1100px;
      margin: 0 auto;
      padding: 0 40px;
    }

    .carousel-container {
      display: flex;
      gap: 20px;
      transition: transform 0.5s ease-in-out;
    }

    .card-carousel {
      min-width: calc(33.333% - 14px);
      background: var(--white);
      border-radius: 12px;
      box-shadow: 0 5px 15px rgba(0,0,0,0.05);
      overflow: hidden;
      border: 1px solid #eee;
      transition: all 0.3s;
    }

    .card-carousel:hover {
      box-shadow: 0 15px 30px rgba(0,0,0,0.1);
    }

    .card-carousel img {
      width: 100%;
      height: 250px;
      object-fit: cover;
      cursor: pointer;
    }

    .card-carousel-content {
      padding: 20px;
    }

    .card-carousel h3 {
      font-size: 18px;
      color: var(--text-dark);
      margin-bottom: 8px;
    }

    .card-carousel p {
      font-size: 14px;
      color: var(--text-light);
      line-height: 1.6;
    }

    .carousel-btn {
      position: absolute;
      top: 50%;
      transform: translateY(-50%);
      width: 45px;
      height: 45px;
      background: var(--white);
      border: 1px solid #ddd;
      border-radius: 50%;
      cursor: pointer;
      z-index: 10;
      font-size: 18px;
      color: var(--text-dark);
      box-shadow: 0 4px 10px rgba(0,0,0,0.1);
      transition: 0.3s;
      display: flex;
      align-items: center;
      justify-content: center;
    }

    .carousel-btn:hover {
      background: var(--primary);
      color: white;
      border-color: var(--primary);
    }

    .prev-btn { left: 0; }
    .next-btn { right: 0; }

    /* --- MODAL --- */
    .modal {
      display: none;
      position: fixed;
      z-index: 9999;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      background: rgba(0, 0, 0, 0.9);
      justify-content: center;
      align-items: center;
      backdrop-filter: blur(5px);
    }

    .modal img {
      max-width: 90%;
      max-height: 90%;
      object-fit: contain;
      border-radius: 8px;
    }

    .modal .close-btn {
      position: absolute;
      top: 20px;
      right: 30px;
      color: white;
      font-size: 40px;
      font-weight: bold;
      cursor: pointer;
      transition: 0.3s;
    }

    .modal .close-btn:hover { color: var(--primary); }

    /* --- FOOTER --- */
    .footer {
      background: #222;
      color: #aaa;
      text-align: center;
      padding: 40px 20px;
      font-size: 14px;
    }

    .footer .social-icons { margin-bottom: 15px; }
    .footer .social-icons a { margin: 0 10px; display: inline-block; }
    .footer .social-icons img { width: 24px; height: 24px; filter: brightness(0) invert(1); opacity: 0.7; transition: 0.3s; }
    .footer .social-icons a:hover img { opacity: 1; transform: scale(1.1); }

    /* --- MOBILE MENU --- */
    .hamburger { display: none; font-size: 24px; background: none; border: none; cursor: pointer; }
    
    .menu-overlay { 
      position: fixed; top: 0; left: 0; width: 100%; height: 100%; 
      background: rgba(0,0,0,0.5); 
      z-index: 1001;
      opacity: 0; visibility: hidden; transition: 0.3s; 
    }
    .menu-overlay.active { opacity: 1; visibility: visible; }
    
    .mobile-menu {
      position: fixed; top: 0; right: -100%; width: 280px; height: 100%; background: white;
      z-index: 1002;
      padding: 60px 20px; transition: right 0.4s; display: flex; flex-direction: column; gap: 20px;
    }
    .mobile-menu.active { right: 0; }
    .mobile-menu a { text-decoration: none; color: var(--text-dark); font-size: 16px; display: flex; align-items: center; gap: 10px; padding: 10px 0; border-bottom: 1px solid #eee; }
    .mobile-menu a:hover { color: var(--primary); }
    .mobile-close { position: absolute; top: 15px; right: 15px; font-size: 24px; background: none; border: none; cursor: pointer; }

    /* --- RESPONSIVE --- */
    @media (max-width: 768px) {
      .hamburger { display: block; }
      .nav-links { display: none; }
      
      .hero { padding: 0 20px; text-align: center; }
      .hero-title { font-size: 32px; }
      .hero-content p { font-size: 16px; }

      .leader-grid, .staff-grid {
        grid-template-columns: 1fr;
        padding: 0 10px;
      }

      .carousel-wrapper { padding: 0; }
      .carousel-container {
        overflow-x: auto;
        scroll-snap-type: x mandatory;
        scroll-behavior: smooth;
        -webkit-overflow-scrolling: touch;
        padding: 0 20px;
        transform: none !important; 
      }

      .card-carousel {
        min-width: 85%;
        scroll-snap-align: center;
        flex-shrink: 0;
      }

      .carousel-btn { display: none; }
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
        
        <!-- LOGIKA TOMBOL LOGIN/DASHBOARD -->
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
    
    <!-- LOGIKA TOMBOL LOGIN/DASHBOARD DI MOBILE MENU -->
    <?php if ($is_logged_in): ?>
      <a href="<?= $dashboard_link ?>" class="btn-header" style="justify-content: center; margin-top: 20px; color: white !important;"><i class="fas fa-tachometer-alt"></i> Dashboard</a>
    <?php else: ?>
      <a href="../Login/login.php" class="btn-header" style="justify-content: center; margin-top: 20px; color: white !important;"><i class="fas fa-sign-in-alt"></i> Login</a>
    <?php endif; ?>
    
  </div>

  <!-- HERO -->
  <section class="hero" id="beranda">
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
      
      <div class="card-container">
        <div class="card-visi-misi" data-aos="fade-up">
          <div class="icon-title">
            <div class="icon-circle"><i class="fas fa-bullseye"></i></div>
            <h3>Visi</h3>
          </div>
          <p>Mewujudkan ekstrakurikuler PMR sebagai organisasi yang peduli terhadap sesama, menciptakan persahabatan erat, dan harmonis antara anggota PMR Millenium.</p>
        </div>

        <div class="card-visi-misi" data-aos="fade-up" data-aos-delay="100">
          <div class="icon-title">
            <div class="icon-circle"><i class="fas fa-tasks"></i></div>
            <h3>Program Kerja</h3>
          </div>
          <ul>
            <li>Konten mingguan edukasi kesehatan di Instagram.</li>
            <li>Menyelenggarakan "Semangat Juang Remaja".</li>
            <li>Variasi latihan rutin (Tandu Estafet, dll).</li>
            <li>Sosialisasi kesehatan di lingkungan sekolah.</li>
          </ul>
        </div>

        <div class="card-visi-misi" data-aos="fade-up" data-aos-delay="200">
          <div class="icon-title">
            <div class="icon-circle"><i class="fas fa-user-friends"></i></div>
            <h3>Misi</h3>
          </div>
          <ul>
            <li>Menjadi organisasi yang solid dan inovatif.</li>
            <li>Menumbuhkan kepedulian sosial & empati.</li>
            <li>Menjadi contoh teladan bagi masyarakat.</li>
          </ul>
        </div>
      </div>
    </div>
  </section>

  <!-- PENGURUS -->
  <section class="section pengurus-section" id="pengurus">
    <h2 class="section-title" data-aos="fade-down">Struktur Organisasi</h2>
    <p class="section-subtitle" data-aos="fade-up">Kepengurusan PMR Millenium Tahun 2026</p>

    <div class="leader-grid">
      <div class="card-pengurus" data-aos="zoom-in">
        <img src="../Gambar/kemala.jpeg" alt="Ketua PMR" onclick="openFull(this)">
        <h3>Kemala Putri Oktaviani</h3>
        <span class="jabatan">Ketua PMR</span>
        <div class="kelas">
          <span>XI RPL 1</span>
          <img src="../Gambar/rpl.png" alt="RPL">
        </div>
      </div>
      <div class="card-pengurus" data-aos="zoom-in" data-aos-delay="100">
        <img src="../Gambar/alif.jpg" alt="Wakil Ketua" onclick="openFull(this)">
        <h3>Muhammad Alif Alghifari</h3>
        <span class="jabatan">Wakil Ketua</span>
        <div class="kelas">
          <span>X RPL 1</span>
          <img src="../Gambar/rpl.png" alt="RPL">
        </div>
      </div>
    </div>

    <div class="container">
      <div class="staff-grid">
        <div class="card-pengurus" data-aos="fade-up">
          <img src="../Gambar/hanip.jpg" alt="Sekretaris 1" onclick="openFull(this)">
          <h3>Mochammad Naufal Hanif</h3>
          <span class="jabatan">Sekretaris 1</span>
          <div class="kelas">XI RPL 1 <img src="../Gambar/rpl.png" alt="Logo"></div>
        </div>
        <div class="card-pengurus" data-aos="fade-up" data-aos-delay="100">
          <img src="../Gambar/aurelia.jpg" alt="Sekretaris 2" onclick="openFull(this)">
          <h3>Aurelia Zahra</h3>
          <span class="jabatan">Sekretaris 2</span>
          <div class="kelas">X DKV 3 <img src="../Gambar/dkv.png" alt="Logo"></div>
        </div>
        <div class="card-pengurus" data-aos="fade-up" data-aos-delay="200">
          <img src="../Gambar/sharhana.jpg" alt="Bendahara 1" onclick="openFull(this)">
          <h3>Sharhana Hajarani</h3>
          <span class="jabatan">Bendahara 1</span>
          <div class="kelas">XI DPIB 1 <img src="../Gambar/dpib.png" alt="Logo"></div>
        </div>
        <div class="card-pengurus" data-aos="fade-up" data-aos-delay="300">
          <img src="../Gambar/anindia.jpg" alt="Bendahara 2" onclick="openFull(this)">
          <h3>Anindia Rahma Alliya</h3>
          <span class="jabatan">Bendahara 2</span>
          <div class="kelas">X RPL 1 <img src="../Gambar/rpl.png" alt="Logo"></div>
        </div>
      </div>
    </div>
  </section>

  <!-- KEGIATAN -->
  <section class="section carousel-section" id="kegiatan">
    <h2 class="section-title" data-aos="fade-down">Dokumentasi Kegiatan</h2>
    <p class="section-subtitle" data-aos="fade-up">Momen penting dan aktivitas rutin PMR Millenium</p>

    <div class="carousel-wrapper" data-aos="fade-up">
      <button class="carousel-btn prev-btn" onclick="prevSlide(0)"><i class="fas fa-chevron-left"></i></button>
      <div class="carousel-container" id="carousel-kegiatan">
        <div class="card-carousel">
          <img src="../Gambar/kegiatan1.jpg" alt="Kegiatan" onclick="openFull(this)">
          <div class="card-carousel-content">
            <h3>Pelatihan P3K</h3>
            <p>Simulasi penanganan pertolongan pertama pada korban kecelakaan bersama instruktur ahli.</p>
          </div>
        </div>
        <div class="card-carousel">
          <img src="../Gambar/kegiatan2.jpg" alt="Kegiatan" onclick="openFull(this)">
          <div class="card-carousel-content">
            <h3>Latihan Rutin</h3>
            <p>Peningkatan skill anggota dalam bidang kepalangmerahan dan kesiapsiagaan bencana.</p>
          </div>
        </div>
        <div class="card-carousel">
          <img src="../Gambar/kegiatan3.jpeg" alt="Kegiatan" onclick="openFull(this)">
          <div class="card-carousel-content">
            <h3>Sosialisasi Kesehatan</h3>
            <p>Berbagi ilmu tentang pola hidup sehat dan bersih kepada warga sekitar.</p>
          </div>
        </div>
      </div>
      <button class="carousel-btn next-btn" onclick="nextSlide(0)"><i class="fas fa-chevron-right"></i></button>
    </div>
  </section>

  <!-- LOMBA -->
  <section class="section" style="background-color: #f0f2f5;">
    <h2 class="section-title" data-aos="fade-down">Prestasi & Lomba</h2>
    <p class="section-subtitle" data-aos="fade-up">Pencapaian membanggakan PMR Millenium</p>

    <div class="carousel-wrapper" data-aos="fade-up">
      <button class="carousel-btn prev-btn" onclick="prevSlide(1)"><i class="fas fa-chevron-left"></i></button>
      <div class="carousel-container" id="carousel-lomba">
        <div class="card-carousel">
          <img src="../Gambar/kegiatan1.jpg" alt="Lomba" onclick="openFull(this)">
          <div class="card-carousel-content">
            <h3>Juara 1 P3K Tingkat Kabupaten</h3>
            <p>Meraih juara pertama dalam lomba pertolongan pertama tingkat Kabupaten Bogor.</p>
          </div>
        </div>
        <div class="card-carousel">
          <img src="../Gambar/kegiatan2.jpg" alt="Lomba" onclick="openFull(this)">
          <div class="card-carousel-content">
            <h3>Juara Harapan Wira Usaha</h3>
            <p>Inovasi produk kesehatan dan UKS yang mendapat pengakuan di tingkat provinsi.</p>
          </div>
        </div>
        <div class="card-carousel">
          <img src="../Gambar/kegiatan3.jpeg" alt="Lomba" onclick="openFull(this)">
          <div class="card-carousel-content">
            <h3>Best Performance</h3>
            <p>Penampilan terbaik dalam upacara dan parade PMR tingkat Jawa Barat.</p>
          </div>
        </div>
      </div>
      <button class="carousel-btn next-btn" onclick="nextSlide(1)"><i class="fas fa-chevron-right"></i></button>
    </div>
  </section>

  <!-- MODAL -->
  <div id="fullPreview" class="modal">
    <span class="close-btn" onclick="closeFull()">×</span>
    <img id="fullImg" alt="Preview">
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

  <script src="https://unpkg.com/aos@next/dist/aos.js"></script>
  <script>
    AOS.init({ once: true, duration: 800 });

    // Mobile Menu Functions
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

    // Image Modal
    function openFull(img) {
      document.getElementById('fullImg').src = img.src;
      document.getElementById('fullPreview').style.display = 'flex';
    }

    function closeFull() {
      document.getElementById('fullPreview').style.display = 'none';
    }

    document.getElementById('fullPreview').addEventListener('click', (e) => {
      if (e.target.tagName !== 'IMG') closeFull();
    });

    // Carousel Logic
    const carousels = document.querySelectorAll('.carousel-container');
    const counters = [0, 0]; 

    function getItemsPerView() {
      if (window.innerWidth <= 768) return 1;
      if (window.innerWidth <= 1024) return 2;
      return 3;
    }

    function updateCarousel(index) {
      if (window.innerWidth > 768) {
        const container = carousels[index];
        const items = container.children;
        if (items.length === 0) return;
        
        const itemWidth = items[0].offsetWidth + 20;
        const maxTranslate = (items.length - getItemsPerView()) * itemWidth;
        
        let translateX = counters[index] * itemWidth;
        if (translateX > maxTranslate) translateX = maxTranslate;
        
        container.style.transform = `translateX(-${translateX}px)`;
      }
    }

    function nextSlide(index) {
      if (window.innerWidth <= 768) return;
        
      const container = carousels[index];
      const totalItems = container.children.length;
      const maxIndex = totalItems - getItemsPerView();
      
      if (counters[index] < maxIndex) {
        counters[index]++;
        updateCarousel(index);
      } else {
        counters[index] = 0;
        updateCarousel(index);
      }
    }

    function prevSlide(index) {
       if (window.innerWidth <= 768) return;

      const container = carousels[index];
      const totalItems = container.children.length;
      
      if (counters[index] > 0) {
        counters[index]--;
        updateCarousel(index);
      } else {
        const maxIndex = totalItems - getItemsPerView();
        counters[index] = maxIndex;
        updateCarousel(index);
      }
    }

    setInterval(() => { if(window.innerWidth > 768) nextSlide(0); }, 5000);
    setInterval(() => { if(window.innerWidth > 768) nextSlide(1); }, 6000);

    window.addEventListener('resize', () => {
      if (window.innerWidth > 768) {
         updateCarousel(0);
         updateCarousel(1);
      } else {
         carousels[0].style.transform = 'none';
         carousels[1].style.transform = 'none';
         counters[0] = 0;
         counters[1] = 0;
      }
    });
  </script>
</body>

</html>