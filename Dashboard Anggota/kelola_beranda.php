<?php
session_start();
include '../koneksi.php';

// Cek Login & Role
if (!isset($_SESSION['nama'])) {
    header("Location: ../Login/login.php");
    exit;
}
if ($_SESSION['role'] != 'pengurus') {
    echo '<script>alert("AKSES DITOLAK!"); window.location.href="../Dashboard Anggota/anggota.php";</script>';
    exit;
}

// Ambil Data User untuk Header
$nama_user = htmlspecialchars($_SESSION['nama']);
$role = $_SESSION['role'];
$foto_session = isset($_SESSION['foto']) ? $_SESSION['foto'] : '';
$foto_profil = 'https://ui-avatars.com/api/?name=' . urlencode($nama_user) . '&background=d90429&color=fff'; // Default UI Avatar

// Pastikan path ke ../uploads/foto_profil/
if (!empty($foto_session)) {
    $path_foto = "../uploads/foto_profil/" . $foto_session;
    if (file_exists($path_foto)) {
        $foto_profil = $path_foto . "?t=" . time(); // Tambah timestamp supaya anti-cache
    }
}

// ================== BLOK PROSES LOGIC ==================

// 1. HERO BACKGROUND (Revisi: Multi Upload & Settings)
if (isset($_POST['tambah_banner'])) {
    $target_dir = "../Gambar/";
    $count = count($_FILES['gambar_hero']['name']);
    for ($i = 0; $i < $count; $i++) {
        if (!empty($_FILES['gambar_hero']['name'][$i])) {
            $file_name = uniqid() . '_' . basename($_FILES['gambar_hero']['name'][$i]);
            $tmp_name = $_FILES['gambar_hero']['tmp_name'][$i];
            if (move_uploaded_file($tmp_name, $target_dir . $file_name)) {
                mysqli_query($koneksi, "INSERT INTO hero_background (file_name) VALUES ('$file_name')");
            }
        }
    }
    echo "<script>alert('Banner baru berhasil ditambahkan!'); window.location.href='kelola_beranda.php?tab=hero';</script>";
}

// Hapus Banner
if (isset($_GET['hapus_banner'])) {
    $id = intval($_GET['hapus_banner']);
    $g = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT file_name FROM hero_background WHERE id=$id"));
    if ($g && file_exists("../Gambar/" . $g['file_name'])) unlink("../Gambar/" . $g['file_name']);
    mysqli_query($koneksi, "DELETE FROM hero_background WHERE id=$id");
    echo "<script>alert('Banner dihapus!'); window.location.href='kelola_beranda.php?tab=hero';</script>";
}

// Hapus Multiple Banner
if (isset($_POST['hapus_multiple_banner'])) {
    if (!empty($_POST['selected_ids'])) {
        foreach ($_POST['selected_ids'] as $id) {
            $id = intval($id);
            $g = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT file_name FROM hero_background WHERE id=$id"));
            if ($g && file_exists("../Gambar/" . $g['file_name'])) unlink("../Gambar/" . $g['file_name']);
            mysqli_query($koneksi, "DELETE FROM hero_background WHERE id=$id");
        }
        echo "<script>alert('Banner terpilih berhasil dihapus!'); window.location.href='kelola_beranda.php?tab=hero';</script>";
    }
}

// Update Urutan Banner
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_order_banner'])) {
    header('Content-Type: application/json');
    $order = json_decode($_POST['update_order_banner']);
    foreach ($order as $index => $id) {
        mysqli_query($koneksi, "UPDATE hero_background SET urutan = " . ($index + 1) . " WHERE id = " . intval($id));
    }
    echo json_encode(['status' => 'success']);
    exit;
}

// Update Pengaturan Animasi
if (isset($_POST['update_slider_settings'])) {
    $effect = mysqli_real_escape_string($koneksi, $_POST['hero_effect']);
    $delay = intval($_POST['hero_delay']);
    mysqli_query($koneksi, "UPDATE settings SET setting_value='$effect' WHERE setting_key='hero_effect'");
    mysqli_query($koneksi, "UPDATE settings SET setting_value='$delay' WHERE setting_key='hero_delay'");
    echo "<script>alert('Pengaturan animasi disimpan!'); window.location.href='kelola_beranda.php?tab=hero';</script>";
}

// 2. TENTANG PMR
if (isset($_POST['update_tentang'])) {
    $visi = mysqli_real_escape_string($koneksi, $_POST['visi']);
    $misi = mysqli_real_escape_string($koneksi, $_POST['misi']);
    $proker = mysqli_real_escape_string($koneksi, $_POST['program_kerja']);
    $check = mysqli_query($koneksi, "SELECT id FROM tentang_pmr LIMIT 1");
    if (mysqli_num_rows($check) > 0) {
        $row = mysqli_fetch_assoc($check);
        mysqli_query($koneksi, "UPDATE tentang_pmr SET visi='$visi', misi='$misi', program_kerja='$proker' WHERE id=" . $row['id']);
    } else {
        mysqli_query($koneksi, "INSERT INTO tentang_pmr (visi, misi, program_kerja) VALUES ('$visi', '$misi', '$proker')");
    }
    echo "<script>alert('Data Tentang PMR diperbarui!'); window.location.href='kelola_beranda.php?tab=tentang';</script>";
}

// 3. PENGURUS LOGIC
if (isset($_POST['tambah_pengurus'])) {
    $nama = mysqli_real_escape_string($koneksi, $_POST['nama']);
    $jabatan = mysqli_real_escape_string($koneksi, $_POST['jabatan']);
    $kelas = mysqli_real_escape_string($koneksi, $_POST['kelas']);
    $logo = mysqli_real_escape_string($koneksi, $_POST['logo_kelas']);
    $res = mysqli_query($koneksi, "SELECT MAX(urutan) as m FROM pengurus");
    $row = mysqli_fetch_assoc($res);
    $newOrder = ($row['m'] ?? 0) + 1;
    $foto = 'default.jpg';
    if (!empty($_FILES['foto_pengurus']['name'])) {
        $ext = pathinfo($_FILES['foto_pengurus']['name'], PATHINFO_EXTENSION);
        $foto = uniqid() . '.' . $ext;
        move_uploaded_file($_FILES['foto_pengurus']['tmp_name'], "../Gambar/" . $foto);
    }
    mysqli_query($koneksi, "INSERT INTO pengurus (nama, jabatan, kelas, logo_kelas, foto, urutan) VALUES ('$nama', '$jabatan', '$kelas', '$logo', '$foto', '$newOrder')");
    echo "<script>alert('Pengurus ditambahkan!'); window.location.href='kelola_beranda.php?tab=pengurus';</script>";
}
if (isset($_POST['edit_pengurus'])) {
    $id = intval($_POST['id_edit']);
    $nama = mysqli_real_escape_string($koneksi, $_POST['nama_edit']);
    $jabatan = mysqli_real_escape_string($koneksi, $_POST['jabatan_edit']);
    $kelas = mysqli_real_escape_string($koneksi, $_POST['kelas_edit']);
    $logo = mysqli_real_escape_string($koneksi, $_POST['logo_kelas_edit']);
    $query = "UPDATE pengurus SET nama='$nama', jabatan='$jabatan', kelas='$kelas', logo_kelas='$logo' WHERE id=$id";
    if (!empty($_FILES['foto_edit']['name'])) {
        $old = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT foto FROM pengurus WHERE id=$id"));
        if ($old && $old['foto'] != 'default.jpg') unlink("../Gambar/" . $old['foto']);
        $ext = pathinfo($_FILES['foto_edit']['name'], PATHINFO_EXTENSION);
        $foto_new = uniqid() . '.' . $ext;
        move_uploaded_file($_FILES['foto_edit']['tmp_name'], "../Gambar/" . $foto_new);
        $query = "UPDATE pengurus SET nama='$nama', jabatan='$jabatan', kelas='$kelas', logo_kelas='$logo', foto='$foto_new' WHERE id=$id";
    }
    mysqli_query($koneksi, $query);
    echo "<script>alert('Data pengurus diperbarui!'); window.location.href='kelola_beranda.php?tab=pengurus';</script>";
}
if (isset($_GET['hapus_pengurus'])) {
    $id = intval($_GET['hapus_pengurus']);
    $g = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT foto FROM pengurus WHERE id=$id"));
    if ($g && $g['foto'] != 'default.jpg') unlink("../Gambar/" . $g['foto']);
    mysqli_query($koneksi, "DELETE FROM pengurus WHERE id=$id");
    echo "<script>alert('Dihapus!'); window.location.href='kelola_beranda.php?tab=pengurus';</script>";
}
if (isset($_POST['hapus_multiple_pengurus'])) {
    if (!empty($_POST['selected_ids'])) {
        foreach ($_POST['selected_ids'] as $id) {
            $id = intval($id);
            $g = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT foto FROM pengurus WHERE id=$id"));
            if ($g && $g['foto'] != 'default.jpg') unlink("../Gambar/" . $g['foto']);
            mysqli_query($koneksi, "DELETE FROM pengurus WHERE id=$id");
        }
        echo "<script>alert('Data terpilih berhasil dihapus!'); window.location.href='kelola_beranda.php?tab=pengurus';</script>";
    }
}
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_order_pengurus'])) {
    header('Content-Type: application/json');
    $order = json_decode($_POST['update_order_pengurus']);
    foreach ($order as $index => $id) {
        mysqli_query($koneksi, "UPDATE pengurus SET urutan = " . ($index + 1) . " WHERE id = " . intval($id));
    }
    echo json_encode(['status' => 'success']);
    exit;
}

// 4. GALERI
if (isset($_POST['tambah_galeri'])) {
    $kategori = mysqli_real_escape_string($koneksi, $_POST['kategori']);
    $judul = mysqli_real_escape_string($koneksi, $_POST['judul']);
    $deskripsi = mysqli_real_escape_string($koneksi, $_POST['deskripsi']);
    $rename = uniqid() . '_' . basename($_FILES['gambar_galeri']['name']);
    if (move_uploaded_file($_FILES['gambar_galeri']['tmp_name'], "../Gambar/" . $rename)) {
        $table = ($kategori == 'kegiatan') ? 'kegiatan' : 'lomba';
        mysqli_query($koneksi, "INSERT INTO $table (judul, deskripsi, gambar) VALUES ('$judul', '$deskripsi', '$rename')");
        echo "<script>alert('Galeri ditambahkan!'); window.location.href='kelola_beranda.php?tab=galeri&sub=$kategori';</script>";
    }
}
// Hapus Single Galeri
if (isset($_GET['hapus_galeri'])) {
    $id = intval($_GET['hapus_galeri']);
    $jenis = $_GET['jenis'];
    $table = ($jenis == 'kegiatan') ? 'kegiatan' : 'lomba';
    $g = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT gambar FROM $table WHERE id=$id"));
    if ($g && file_exists("../Gambar/" . $g['gambar'])) unlink("../Gambar/" . $g['gambar']);
    mysqli_query($koneksi, "DELETE FROM $table WHERE id=$id");
    echo "<script>alert('Dihapus!'); window.location.href='kelola_beranda.php?tab=galeri&sub=$jenis';</script>";
}

// [BARU] Hapus Multiple Galeri
if (isset($_POST['hapus_multiple_galeri'])) {
    if (!empty($_POST['selected_ids'])) {
        $jenis = mysqli_real_escape_string($koneksi, $_POST['jenis_galeri']);
        $table = ($jenis == 'kegiatan') ? 'kegiatan' : 'lomba';

        foreach ($_POST['selected_ids'] as $id) {
            $id = intval($id);
            $g = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT gambar FROM $table WHERE id=$id"));
            if ($g && file_exists("../Gambar/" . $g['gambar'])) unlink("../Gambar/" . $g['gambar']);
            mysqli_query($koneksi, "DELETE FROM $table WHERE id=$id");
        }
        echo "<script>alert('Galeri terpilih berhasil dihapus!'); window.location.href='kelola_beranda.php?tab=galeri&sub=$jenis';</script>";
    }
}

// 5. FOOTER LOGIC
if (isset($_POST['update_copyright'])) {
    $text = mysqli_real_escape_string($koneksi, $_POST['copyright_text']);
    mysqli_query($koneksi, "UPDATE settings SET setting_value='$text' WHERE setting_key='footer_copyright'");
    echo "<script>alert('Copyright diperbarui!'); window.location.href='kelola_beranda.php?tab=footer';</script>";
}
if (isset($_POST['tambah_sosmed'])) {
    $platform = mysqli_real_escape_string($koneksi, $_POST['platform']);
    $url = mysqli_real_escape_string($koneksi, $_POST['url']);
    $icon_file = mysqli_real_escape_string($koneksi, $_POST['icon_file']);
    mysqli_query($koneksi, "INSERT INTO social_links (platform, url, icon_url) VALUES ('$platform', '$url', '$icon_file')");
    echo "<script>alert('Sosmed ditambahkan!'); window.location.href='kelola_beranda.php?tab=footer';</script>";
}
if (isset($_GET['hapus_sosmed'])) {
    mysqli_query($koneksi, "DELETE FROM social_links WHERE id=" . intval($_GET['hapus_sosmed']));
    echo "<script>alert('Sosmed dihapus!'); window.location.href='kelola_beranda.php?tab=footer';</script>";
}
if (isset($_POST['edit_sosmed'])) {
    $id = intval($_POST['id_sosmed']);
    $platform = mysqli_real_escape_string($koneksi, $_POST['platform']);
    $url = mysqli_real_escape_string($koneksi, $_POST['url']);
    $icon_file = mysqli_real_escape_string($koneksi, $_POST['icon_file']);
    mysqli_query($koneksi, "UPDATE social_links SET platform='$platform', url='$url', icon_url='$icon_file' WHERE id=$id");
    echo "<script>alert('Sosmed diperbarui!'); window.location.href='kelola_beranda.php?tab=footer';</script>";
}

// Ambil Data Default
$hero_now = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT file_name FROM hero_background ORDER BY id DESC LIMIT 1"));
$tentang_now = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT * FROM tentang_pmr LIMIT 1"));
$active_tab = isset($_GET['tab']) ? $_GET['tab'] : 'hero';
$sub_galeri = isset($_GET['sub']) ? $_GET['sub'] : 'kegiatan';
$icon_list = ['Instagram' => 'instagram.png', 'Youtube' => 'youtube.png', 'TikTok' => 'tiktok.png', 'Facebook' => 'facebook.png', 'Twitter' => 'twitter.png', 'Website' => 'web.png'];
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Halaman Utama | PMR Millenium</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link rel="icon" href="../Gambar/logpmi.png" type="image/png">
    <script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>
    <style>
        /* CSS (Sama persis seperti sebelumnya) */
        :root {
            --primary-color: #d90429;
            --primary-hover: #c92a2a;
            --bg-color: #f8f9fa;
            --card-bg: #ffffff;
            --text-color: #1e293b;
            --text-muted: #64748b;
            --border-color: #e2e8f0;
            --success-color: #10b981;
            --danger-color: #ef4444;
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
        }

        .nav-left,
        .nav-right {
            display: flex;
            align-items: center;
        }

        .nav-right {
            gap: 15px;
            position: relative;
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

        .profile-btn {
            cursor: pointer;
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

        .menu-toggle {
            display: none;
            background: none;
            border: none;
            font-size: 24px;
            cursor: pointer;
            color: var(--primary-color);
        }

        .modal-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            backdrop-filter: blur(4px);
            z-index: 9999;
            display: flex;
            align-items: center;
            justify-content: center;
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
            width: 90%;
            max-width: 500px;
            max-height: 90vh;
            overflow-y: auto;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
            transform: scale(0.9);
            transition: transform 0.3s ease;
        }

        .modal-overlay.active .modal-box {
            transform: scale(1);
        }

        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            border-bottom: 1px solid #eee;
            padding-bottom: 10px;
        }

        .modal-close {
            background: none;
            border: none;
            font-size: 1.5rem;
            cursor: pointer;
            color: var(--text-muted);
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
            overflow-x: hidden;
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

        .tabs {
            display: flex;
            gap: 5px;
            margin-bottom: 20px;
            border-bottom: 2px solid var(--border-color);
            flex-wrap: wrap;
        }

        .tab-btn {
            padding: 10px 20px;
            border: none;
            background: none;
            cursor: pointer;
            font-weight: 600;
            color: var(--text-muted);
            border-bottom: 3px solid transparent;
            margin-bottom: -2px;
            transition: 0.2s;
        }

        .tab-btn.active {
            color: var(--primary-color);
            border-bottom-color: var(--primary-color);
        }

        .content-card {
            background: white;
            padding: 25px;
            border-radius: var(--radius);
            box-shadow: var(--shadow-sm);
            border: 1px solid var(--border-color);
            margin-bottom: 25px;
        }

        .btn {
            padding: 10px 20px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: all 0.2s ease;
            font-size: 0.9rem;
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

        .btn-danger {
            background-color: var(--danger-color);
        }

        .btn-outline-danger {
            background: transparent;
            border: 1px solid var(--danger-color);
            color: var(--danger-color);
        }

        .btn-outline-secondary {
            background: transparent;
            border: 1px solid var(--text-muted);
            color: var(--text-muted);
        }

        .btn-sm {
            padding: 5px 10px;
            font-size: 12px;
        }

        .form-group {
            margin-bottom: 15px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            font-size: 0.9rem;
        }

        .form-control {
            width: 100%;
            padding: 10px 15px;
            border: 1px solid var(--border-color);
            border-radius: 8px;
            font-size: 0.95rem;
            outline: none;
            transition: 0.2s;
        }

        .form-control:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(217, 4, 41, 0.1);
        }

        .custom-file-upload {
            border: 2px dashed var(--border-color);
            border-radius: 10px;
            padding: 20px;
            text-align: center;
            cursor: pointer;
            transition: all 0.2s;
            background: #fafafa;
            position: relative;
            overflow: hidden;
        }

        .custom-file-upload:hover {
            border-color: var(--primary-color);
            background: #fff;
        }

        .custom-file-upload input[type="file"] {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            opacity: 0;
            cursor: pointer;
        }

        .upload-icon {
            font-size: 2rem;
            color: #cbd5e1;
            margin-bottom: 10px;
        }

        .upload-text {
            font-size: 0.9rem;
            color: var(--text-muted);
        }

        .file-selected {
            font-size: 0.85rem;
            color: var(--success-color);
            margin-top: 10px;
            font-weight: 600;
            word-break: break-all;
        }

        .split-row {
            display: flex;
            gap: 30px;
            flex-wrap: wrap;
        }

        .split-col {
            flex: 1;
            min-width: 300px;
        }

        .img-preview {
            width: 100%;
            height: 250px;
            object-fit: cover;
            border-radius: 8px;
            border: 1px solid #eee;
            background: #f8f8f8;
        }

        /* Banner, Pengurus & Galeri Styles */
        .sortable-grid {
            display: grid;
            grid-template-columns: repeat(5, 1fr);
            gap: 20px;
        }

        .pengurus-card,
        .banner-card {
            background: #fff;
            border: 1px solid var(--border-color);
            border-radius: 10px;
            padding: 15px;
            text-align: center;
            cursor: move;
            transition: 0.2s;
            position: relative;
        }

        .pengurus-card:hover,
        .banner-card:hover {
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
            border-color: var(--primary-color);
        }

        .sortable-chosen {
            border: 2px dashed var(--primary-color);
            background: #fff1f1;
            opacity: 0.8;
        }

        .card-checkbox {
            position: absolute;
            top: 10px;
            left: 10px;
            width: 18px;
            height: 18px;
            cursor: pointer;
            z-index: 10;
        }

        .drag-handle-icon {
            color: #ccc;
            font-size: 1.2rem;
            margin-bottom: 5px;
            display: block;
            cursor: move;
        }

        .pengurus-card img,
        .banner-card img {
            width: 70px;
            height: 70px;
            border-radius: 50%;
            object-fit: cover;
            margin: 0 auto 10px;
            border: 2px solid #eee;
            display: block;
        }

        .banner-card img {
            width: 100%;
            height: 120px;
            border-radius: 6px;
            object-fit: cover;
        }

        .pengurus-card h6 {
            font-size: 0.95rem;
            margin: 5px 0;
            color: var(--text-color);
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .pengurus-card small {
            color: var(--primary-color);
            font-weight: 600;
            font-size: 0.8rem;
            display: block;
            margin-bottom: 10px;
        }

        .card-actions {
            display: flex;
            gap: 5px;
            justify-content: center;
            margin-top: 10px;
        }

        .toolbar-pengurus {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            flex-wrap: wrap;
            gap: 10px;
        }

        .select-all-wrapper {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 0.9rem;
        }

        /* Galeri Specific Styles */
        .galeri-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
            gap: 20px;
        }

        .galeri-item {
            position: relative;
            border-radius: 10px;
            overflow: hidden;
            border: 1px solid var(--border-color);
            background: #fff;
        }

        .galeri-item img {
            width: 100%;
            height: 150px;
            object-fit: cover;
        }

        .delete-link {
            position: absolute;
            top: 8px;
            right: 8px;
            background: rgba(255, 255, 255, 0.9);
            border-radius: 50%;
            width: 24px;
            height: 24px;
            text-align: center;
            line-height: 24px;
            color: var(--danger-color);
            opacity: 0;
            transition: 0.2s;
        }

        .galeri-item:hover .delete-link {
            opacity: 1;
        }

        /* Table Styles */
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }

        th,
        td {
            padding: 12px 15px;
            text-align: left;
            border-bottom: 1px solid var(--border-color);
            font-size: 0.9rem;
        }

        th {
            background-color: #f8f9fa;
            font-weight: 600;
        }

        .icon-preview {
            width: 24px;
            height: 24px;
            vertical-align: middle;
            margin-right: 8px;
            object-fit: contain;
        }

        @media (max-width: 1400px) {
            .sortable-grid {
                grid-template-columns: repeat(4, 1fr);
            }
        }

        @media (max-width: 1100px) {
            .sortable-grid {
                grid-template-columns: repeat(3, 1fr);
            }
        }

        @media (max-width: 992px) {
            .sidebar {
                position: fixed;
                top: var(--header-height);
                right: -260px;
                left: auto;
                box-shadow: -2px 0 10px rgba(0, 0, 0, 0.1);
                transition: right 0.3s;
                border-right: none;
            }

            .sidebar.active {
                right: 0;
            }

            .menu-toggle {
                display: block;
            }

            .split-row {
                flex-direction: column;
            }

            .sortable-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        @media (max-width: 576px) {
            .sortable-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>

<body>
    <!-- HEADER & MODALS (Sama persis) -->
    <header>
        <nav class="navbar">
            <div class="nav-left">
                <div class="logo"><img src="../Gambar/logpmi.png" alt="Logo PMR"><span>PMR MILLENIUM</span></div>
            </div>
            <div class="nav-right">
                <div class="profile-btn" id="profileBtn"><img src="<?= $foto_profil ?>" alt="Foto Profil" class="profile-img"></div>
                <div class="profile-dropdown" id="profileDropdown">
                    <div class="dropdown-header">
                        <p><?= $nama_user ?></p><small><?= ucfirst($role) ?></small>
                    </div>
                    <ul>
                        <li><a href="ganti_foto.php"><i class="fa-solid fa-camera"></i> Ganti Foto Profil</a></li>
                        <li><a href="ganti_password.php"><i class="fa-solid fa-key"></i> Ganti Password</a></li>
                    </ul>
                </div>
                <button class="menu-toggle" aria-label="Menu"><i class="fa-solid fa-bars"></i></button>
            </div>
        </nav>
    </header>

    <div class="modal-overlay" id="logoutModal">
        <div class="modal-box">
            <div style="text-align: center;">
                <div style="width:60px;height:60px;background:#fee2e2;color:var(--primary-color);border-radius:50%;display:flex;align-items:center;justify-content:center;margin:0 auto 20px;font-size:24px;"><i class="fa-solid fa-right-from-bracket"></i></div>
                <h3>Konfirmasi Keluar</h3>
                <p>Apakah Anda yakin ingin keluar?</p>
                <div style="justify-content: center; margin-top: 20px; display:flex; gap:10px;"><button class="btn btn-outline-secondary" onclick="closeLogoutModal()">Batal</button><button class="btn btn-primary" onclick="proceedLogout()">Ya, Keluar</button></div>
            </div>
        </div>
    </div>
    <div class="modal-overlay" id="editModal">
        <div class="modal-box">
            <div class="modal-header">
                <h3>Edit Data Pengurus</h3><button class="modal-close" onclick="closeEditModal()">&times;</button>
            </div>
            <form method="POST" enctype="multipart/form-data"><input type="hidden" name="id_edit" id="edit_id">
                <div class="form-group"><label>Nama</label><input type="text" name="nama_edit" id="edit_nama" class="form-control" required></div>
                <div class="form-group"><label>Jabatan</label><input type="text" name="jabatan_edit" id="edit_jabatan" class="form-control" required></div>
                <div class="form-group"><label>Kelas</label><input type="text" name="kelas_edit" id="edit_kelas" class="form-control" required></div>
                <div class="form-group"><label>Logo</label><select name="logo_kelas_edit" id="edit_logo" class="form-control">
                        <option value="rpl.png">RPL</option>
                        <option value="dkv.png">DKV</option>
                        <option value="dpib.png">DPIB</option>
                    </select></div>
                <div class="form-group"><label>Foto</label>
                    <div class="custom-file-upload" style="padding: 15px;"><input type="file" name="foto_edit" id="edit_foto_input">
                        <div class="upload-text" id="edit_foto_label">Klik untuk ganti</div>
                    </div>
                </div>
                <div style="display:flex; gap:10px; justify-content:flex-end;"><button type="button" class="btn btn-outline-secondary" onclick="closeEditModal()">Batal</button><button type="submit" name="edit_pengurus" class="btn btn-primary">Simpan</button></div>
            </form>
        </div>
    </div>
    <div class="modal-overlay" id="editSosmedModal">
        <div class="modal-box">
            <div class="modal-header">
                <h3>Edit Media Sosial</h3><button class="modal-close" onclick="closeEditSosmedModal()">&times;</button>
            </div>
            <form method="POST"><input type="hidden" name="id_sosmed" id="edit_sosmed_id">
                <div class="form-group"><label>Platform</label><input type="text" name="platform" id="edit_sosmed_platform" class="form-control" required></div>
                <div class="form-group"><label>URL</label><input type="url" name="url" id="edit_sosmed_url" class="form-control" required></div>
                <div class="form-group"><label>Icon</label><select name="icon_file" id="edit_sosmed_icon" class="form-control"><?php foreach ($icon_list as $name => $file): ?><option value="<?= $file ?>"><?= $name ?></option><?php endforeach; ?></select></div>
                <div style="display:flex; gap:10px; justify-content:flex-end;"><button type="button" class="btn btn-outline-secondary" onclick="closeEditSosmedModal()">Batal</button><button type="submit" name="edit_sosmed" class="btn btn-primary">Update</button></div>
            </form>
        </div>
    </div>

    <div class="dashboard-container">
        <aside class="sidebar" id="sidebar">
            <ul>
                <li><a href="../Dashboard Anggota/anggota.php"><i class="fa-solid fa-house"></i> Dashboard</a></li>
                <li><a href="kelolaabsen.php"><i class="fa-solid fa-calendar-check"></i> Kelola Absensi</a></li>
                <li><a href="kelolaperpus.php"><i class="fa-solid fa-book"></i> Kelola Perpustakaan</a></li>
                <li><a href="kelola_pendaftaran.php"><i class="fa-solid fa-users"></i> Kelola Pendaftaran</a></li>
                <li class="active"><a href="kelola_beranda.php"><i class="fa-solid fa-pen-to-square"></i> Edit Halaman Utama</a></li>
                <li style="margin-top: 20px; border-top: 1px solid #eee;"><a href="javascript:void(0)" onclick="openLogoutModal()"><i class="fa-solid fa-right-from-bracket"></i> Log Out</a></li>
                <li><a href="../Halaman Utama/index.php"><i class="fa-solid fa-globe"></i>Halaman Utama</a></li>
            </ul>
        </aside>

        <main class="main-content">
            <div class="page-title">
                <h1>Pengaturan Halaman Utama</h1>
                <p>Kelola konten banner, pengurus, dan galeri.</p>
            </div>

            <div class="tabs">
                <button class="tab-btn <?= ($active_tab == 'hero') ? 'active' : '' ?>" onclick="switchTab('hero')"><i class="fas fa-image"></i> Banner</button>
                <button class="tab-btn <?= ($active_tab == 'tentang') ? 'active' : '' ?>" onclick="switchTab('tentang')"><i class="fas fa-info-circle"></i> Tentang</button>
                <button class="tab-btn <?= ($active_tab == 'pengurus') ? 'active' : '' ?>" onclick="switchTab('pengurus')"><i class="fas fa-users"></i> Pengurus</button>
                <button class="tab-btn <?= ($active_tab == 'galeri') ? 'active' : '' ?>" onclick="switchTab('galeri')"><i class="fas fa-images"></i> Galeri</button>
                <button class="tab-btn <?= ($active_tab == 'footer') ? 'active' : '' ?>" onclick="switchTab('footer')"><i class="fas fa-cog"></i> Footer</button>
            </div>

            <div id="content-area">
                <!-- ================== TAB HERO ================== -->
                <?php if ($active_tab == 'hero'): ?>
                    <div class="content-card">
                        <h3 style="margin-bottom: 15px;"><i class="fas fa-sliders-h"></i> Pengaturan Animasi Banner</h3>
                        <div class="split-row">
                            <div class="split-col">
                                <?php
                                $set_effect = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT setting_value FROM settings WHERE setting_key='hero_effect'"));
                                $set_delay = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT setting_value FROM settings WHERE setting_key='hero_delay'"));
                                ?>
                                <form method="POST">
                                    <div class="form-group">
                                        <label>Jenis Animasi</label>
                                        <select name="hero_effect" class="form-control">
                                            <option value="slide" <?= ($set_effect['setting_value'] == 'slide') ? 'selected' : '' ?>>Slide (Geser Biasa)</option>
                                            <option value="zoom" <?= ($set_effect['setting_value'] == 'zoom') ? 'selected' : '' ?>>Zoom In / Zoom Out</option>
                                        </select>
                                    </div>
                                    <div class="form-group">
                                        <label>Durasi Per Gambar (Milidetik)</label>
                                        <input type="number" name="hero_delay" class="form-control" value="<?= htmlspecialchars($set_delay['setting_value']) ?>">
                                    </div>
                                    <button type="submit" name="update_slider_settings" class="btn btn-primary"><i class="fas fa-save"></i> Simpan Pengaturan</button>
                                </form>
                            </div>
                        </div>
                    </div>

                    <div class="content-card">
                        <h3 style="margin-bottom: 20px;"><i class="fas fa-images"></i> Daftar Banner</h3>

                        <!-- Form Upload Multiple -->
                        <form method="POST" enctype="multipart/form-data" style="margin-bottom: 30px;">
                            <div class="custom-file-upload">
                                <input type="file" name="gambar_hero[]" id="hero_input" multiple accept="image/*" required>
                                <div class="upload-icon"><i class="fas fa-cloud-upload-alt"></i></div>
                                <div class="upload-text" id="hero_label">Klik untuk pilih banyak gambar</div>
                                <div class="file-selected" id="hero_selected" style="display: none;"></div>
                            </div>
                            <button type="submit" name="tambah_banner" class="btn btn-success" style="margin-top: 15px;"><i class="fas fa-plus"></i> Upload Banner</button>
                        </form>

                        <!-- Toolbar Multi Delete -->
                        <form method="POST" onsubmit="return confirm('Yakin hapus banner terpilih?')" id="form-multi-banner">
                            <div class="toolbar-pengurus">
                                <div class="select-all-wrapper">
                                    <input type="checkbox" id="select_all_banner" onclick="toggleSelectAllBanner(this)">
                                    <label for="select_all_banner">Pilih Semua</label>
                                </div>
                                <button type="submit" name="hapus_multiple_banner" class="btn btn-sm btn-danger" id="btn-multi-delete-banner" style="display:none;">
                                    <i class="fas fa-trash"></i> Hapus Terpilih (<span id="count-selected-banner">0</span>)
                                </button>
                            </div>

                            <!-- Grid Banner -->
                            <div id="sortable-banner" class="sortable-grid">
                                <?php
                                $q_banner = mysqli_query($koneksi, "SELECT * FROM hero_background ORDER BY urutan ASC");
                                if (mysqli_num_rows($q_banner) == 0) echo "<p style='color: #999; text-align: center; width: 100%; grid-column: 1/-1;'>Belum ada banner.</p>";
                                while ($b = mysqli_fetch_assoc($q_banner)):
                                ?>
                                    <div class="banner-card" data-id="<?= $b['id'] ?>">
                                        <input type="checkbox" name="selected_ids[]" value="<?= $b['id'] ?>" class="card-checkbox" onchange="updateMultiDeleteBanner()">
                                        <i class="fas fa-grip-vertical drag-handle-icon"></i>
                                        <img src="../Gambar/<?= htmlspecialchars($b['file_name']) ?>">
                                        <div class="card-actions">
                                            <a href="?tab=hero&hapus_banner=<?= $b['id'] ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Hapus?')"><i class="fas fa-trash"></i></a>
                                        </div>
                                    </div>
                                <?php endwhile; ?>
                            </div>
                        </form>
                    </div>

                    <!-- ================== TAB LAINNYA ================== -->
                <?php elseif ($active_tab == 'tentang'): ?>
                    <div class="content-card">
                        <h3 style="margin-bottom: 20px;"><i class="fas fa-bullseye"></i> Visi, Misi & Program Kerja</h3>
                        <form method="POST">
                            <div class="form-group"><label>Visi</label><textarea name="visi" class="form-control" rows="3" required><?= htmlspecialchars($tentang_now['visi'] ?? '') ?></textarea></div>
                            <div class="form-group"><label>Misi</label><textarea name="misi" class="form-control" rows="5" required><?= htmlspecialchars($tentang_now['misi'] ?? '') ?></textarea></div>
                            <div class="form-group"><label>Program Kerja</label><textarea name="program_kerja" class="form-control" rows="5" required><?= htmlspecialchars($tentang_now['program_kerja'] ?? '') ?></textarea></div><button type="submit" name="update_tentang" class="btn btn-primary"><i class="fas fa-save"></i> Simpan</button>
                        </form>
                    </div>
                <?php elseif ($active_tab == 'pengurus'): ?>
                    <div class="content-card" style="border-left: 4px solid var(--success-color);">
                        <h3 style="color: var(--success-color); margin-bottom: 15px;"><i class="fas fa-user-plus"></i> Tambah Pengurus</h3>
                        <form method="POST" enctype="multipart/form-data">
                            <div class="split-row" style="gap: 15px; align-items: flex-end;">
                                <div style="flex: 2;"><label>Nama</label><input type="text" name="nama" class="form-control" required></div>
                                <div style="flex: 2;"><label>Jabatan</label><input type="text" name="jabatan" class="form-control" required></div>
                                <div style="flex: 1;"><label>Kelas</label><input type="text" name="kelas" class="form-control" required></div>
                                <div style="flex: 1;"><label>Logo</label><select name="logo_kelas" class="form-control">
                                        <option value="rpl.png">RPL</option>
                                        <option value="dkv.png">DKV</option>
                                        <option value="dpib.png">DPIB</option>
                                    </select></div>
                                <div style="flex: 1;"><label>Foto</label>
                                    <div class="custom-file-upload" style="padding: 9px;"><input type="file" name="foto_pengurus" id="add_pengurus_foto"><span id="add_pengurus_label" style="font-size:0.8rem; color:var(--text-muted);">Pilih</span></div>
                                </div>
                                <div style="flex: 0; padding-bottom: 3px;"><button name="tambah_pengurus" class="btn btn-success"><i class="fas fa-plus"></i></button></div>
                            </div>
                        </form>
                    </div>
                    <div class="content-card">
                        <form method="POST" onsubmit="return confirm('Yakin hapus data terpilih?')" id="form-multi-delete">
                            <div class="toolbar-pengurus">
                                <div class="select-all-wrapper"><input type="checkbox" id="select_all" onclick="toggleSelectAll(this)"><label for="select_all">Pilih Semua</label></div><button type="submit" name="hapus_multiple_pengurus" class="btn btn-sm btn-danger" id="btn-multi-delete" style="display:none;"><i class="fas fa-trash"></i> Hapus Terpilih (<span id="count-selected">0</span>)</button>
                            </div>
                            <div id="sortable-pengurus" class="sortable-grid"><?php $q_p = mysqli_query($koneksi, "SELECT * FROM pengurus ORDER BY urutan ASC");
                                                                                if (mysqli_num_rows($q_p) == 0) echo "<p style='color: #999; text-align: center; width: 100%;'>Belum ada data.</p>";
                                                                                while ($p = mysqli_fetch_assoc($q_p)): ?><div class="pengurus-card" data-id="<?= $p['id'] ?>"><input type="checkbox" name="selected_ids[]" value="<?= $p['id'] ?>" class="card-checkbox" onchange="updateMultiDeleteButton()"><i class="fas fa-grip-vertical drag-handle-icon"></i><img src="../Gambar/<?= $p['foto'] ?>">
                                        <h6 title="<?= htmlspecialchars($p['nama']) ?>"><?= htmlspecialchars($p['nama']) ?></h6><small><?= htmlspecialchars($p['jabatan']) ?></small>
                                        <div class="card-actions"><button type="button" class="btn btn-sm btn-outline-secondary" onclick='openEditModal(<?= json_encode($p) ?>)'><i class="fas fa-edit"></i></button><a href="?tab=pengurus&hapus_pengurus=<?= $p['id'] ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Hapus?')"><i class="fas fa-trash"></i></a></div>
                                    </div><?php endwhile; ?></div>
                        </form>
                    </div>

                    <!-- ================== TAB GALERI (MODIFIED) ================== -->
                <?php elseif ($active_tab == 'galeri'): ?>
                    <div class="sub-tabs" style="margin-bottom: 20px; display: flex; gap: 10px;">
                        <a href="?tab=galeri&sub=kegiatan" class="btn btn-sm <?= ($sub_galeri == 'kegiatan') ? 'btn-primary' : 'btn-outline-secondary' ?>">Kegiatan</a>
                        <a href="?tab=galeri&sub=lomba" class="btn btn-sm <?= ($sub_galeri == 'lomba') ? 'btn-primary' : 'btn-outline-secondary' ?>">Lomba</a>
                    </div>

                    <div class="split-row">
                        <div style="flex: 3;">
                            <!-- Form untuk Multiple Delete -->
                            <form method="POST" onsubmit="return confirm('Yakin hapus galeri terpilih?')" id="form-multi-galeri">
                                <input type="hidden" name="jenis_galeri" value="<?= $sub_galeri ?>">

                                <!-- Toolbar Galeri -->
                                <div class="toolbar-pengurus" style="background: #fff; padding: 15px; border: 1px solid var(--border-color); border-radius: 10px; margin-bottom: 20px;">
                                    <div class="select-all-wrapper">
                                        <input type="checkbox" id="select_all_galeri" onclick="toggleSelectAllGaleri(this)">
                                        <label for="select_all_galeri">Pilih Semua</label>
                                    </div>
                                    <button type="submit" name="hapus_multiple_galeri" class="btn btn-sm btn-danger" id="btn-multi-delete-galeri" style="display:none;">
                                        <i class="fas fa-trash"></i> Hapus Terpilih (<span id="count-selected-galeri">0</span>)
                                    </button>
                                </div>

                                <!-- Grid Galeri -->
                                <div class="galeri-grid">
                                    <?php
                                    $table = ($sub_galeri == 'kegiatan') ? 'kegiatan' : 'lomba';
                                    $q_g = mysqli_query($koneksi, "SELECT * FROM $table ORDER BY id DESC");
                                    if (mysqli_num_rows($q_g) == 0) echo "<p style='text-align:center; width:100%; color:#999;'>Kosong</p>";
                                    while ($g = mysqli_fetch_assoc($q_g)):
                                    ?>
                                        <div class="galeri-item">
                                            <!-- Checkbox di pojok kiri atas -->
                                            <input type="checkbox" name="selected_ids[]" value="<?= $g['id'] ?>" class="card-checkbox" onchange="updateMultiDeleteGaleri()">

                                            <!-- Tombol Hapus Satuan di pojok kanan atas -->
                                            <a href="?tab=galeri&sub=<?= $sub_galeri ?>&hapus_galeri=<?= $g['id'] ?>&jenis=<?= $sub_galeri ?>" onclick="return confirm('Hapus?')" class="delete-link">
                                                <i class="fas fa-times"></i>
                                            </a>
                                            <img src="../Gambar/<?= $g['gambar'] ?>">
                                            <div style="padding: 12px;"><strong><?= htmlspecialchars($g['judul']) ?></strong></div>
                                        </div>
                                    <?php endwhile; ?>
                                </div>
                            </form>
                        </div>

                        <!-- Form Tambah Galeri -->
                        <div style="flex: 1; min-width: 280px;">
                            <div class="content-card">
                                <h5 style="margin-bottom: 15px;">Tambah <?= ucfirst($sub_galeri) ?></h5>
                                <form method="POST" enctype="multipart/form-data">
                                    <input type="hidden" name="kategori" value="<?= $sub_galeri ?>">
                                    <div class="form-group">
                                        <input type="text" name="judul" class="form-control" placeholder="Judul" required>
                                    </div>
                                    <div class="form-group">
                                        <textarea name="deskripsi" class="form-control" rows="2" placeholder="Deskripsi" required></textarea>
                                    </div>
                                    <div class="form-group">
                                        <div class="custom-file-upload" style="padding: 15px;">
                                            <input type="file" name="gambar_galeri" required id="galeri_input">
                                            <div id="galeri_label">Pilih Gambar</div>
                                        </div>
                                    </div>
                                    <button name="tambah_galeri" class="btn btn-primary w-100">Simpan</button>
                                </form>
                            </div>
                        </div>
                    </div>

                <?php elseif ($active_tab == 'footer'): ?>
                    <div class="split-row">
                        <div class="split-col">
                            <div class="content-card">
                                <h3 style="margin-bottom: 15px;"><i class="fas fa-share-alt"></i> Link Media Sosial</h3>
                                <table>
                                    <thead>
                                        <tr>
                                            <th>Icon</th>
                                            <th>Platform</th>
                                            <th>URL</th>
                                            <th>Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody><?php $sosmed = mysqli_query($koneksi, "SELECT * FROM social_links ORDER BY urutan ASC");
                                            if (mysqli_num_rows($sosmed) == 0) echo "<tr><td colspan='4' style='text-align:center;'>Kosong</td></tr>";
                                            while ($s = mysqli_fetch_assoc($sosmed)): ?><tr>
                                                <td><img src="../Gambar/<?= htmlspecialchars($s['icon_url']) ?>" style="width:24px; height:24px; object-fit:contain;"></td>
                                                <td><?= htmlspecialchars($s['platform']) ?></td>
                                                <td><a href="<?= $s['url'] ?>" target="_blank" style="color: var(--primary-color); font-size: 0.85rem;"><?= substr($s['url'], 0, 20) ?>...</a></td>
                                                <td><button onclick='openEditSosmedModal(<?= json_encode($s) ?>)' class="btn btn-sm btn-outline-secondary"><i class="fas fa-edit"></i></button> <a href="?tab=footer&hapus_sosmed=<?= $s['id'] ?>" onclick="return confirm('Hapus?')" class="btn btn-sm btn-outline-danger"><i class="fas fa-trash"></i></a></td>
                                            </tr><?php endwhile; ?></tbody>
                                </table>
                            </div>
                        </div>
                        <div class="split-col" style="min-width: 300px;">
                            <div class="content-card" style="border-left: 4px solid var(--primary-color);">
                                <h3 style="margin-bottom: 15px;"><i class="fas fa-plus"></i> Tambah Sosmed</h3>
                                <form method="POST">
                                    <div class="form-group"><label>Platform</label><input type="text" name="platform" class="form-control" required></div>
                                    <div class="form-group"><label>URL</label><input type="url" name="url" class="form-control" required></div>
                                    <div class="form-group"><label>Icon</label><select name="icon_file" class="form-control"><?php foreach ($icon_list as $name => $file): ?><option value="<?= $file ?>"><?= $name ?></option><?php endforeach; ?></select></div><button type="submit" name="tambah_sosmed" class="btn btn-primary w-100">Tambahkan</button>
                                </form>
                            </div>
                            <div class="content-card">
                                <h3 style="margin-bottom: 15px;"><i class="fas fa-copyright"></i> Copyright</h3><?php $cpy = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT setting_value FROM settings WHERE setting_key='footer_copyright'")); ?><form method="POST">
                                    <div class="form-group"><textarea name="copyright_text" class="form-control" rows="2" required><?= htmlspecialchars($cpy['setting_value'] ?? '') ?></textarea></div><button type="submit" name="update_copyright" class="btn btn-primary w-100">Update</button>
                                </form>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </main>
    </div>

    <script>
        // Interactions
        const menuToggle = document.querySelector('.menu-toggle');
        const sidebar = document.getElementById('sidebar');
        if (menuToggle) menuToggle.addEventListener('click', () => sidebar.classList.toggle('active'));
        const profileBtn = document.getElementById('profileBtn');
        const profileDropdown = document.getElementById('profileDropdown');
        if (profileBtn) profileBtn.addEventListener('click', (e) => {
            e.stopPropagation();
            profileDropdown.classList.toggle('active');
        });
        document.addEventListener('click', (e) => {
            if (profileDropdown && !profileBtn.contains(e.target)) profileDropdown.classList.remove('active');
        });

        // Modals
        function openLogoutModal() {
            document.getElementById('logoutModal').classList.add('active');
        }

        function closeLogoutModal() {
            document.getElementById('logoutModal').classList.remove('active');
        }

        function proceedLogout() {
            window.location.href = "../logout.php";
        }

        function openEditModal(data) {
            document.getElementById('edit_id').value = data.id;
            document.getElementById('edit_nama').value = data.nama;
            document.getElementById('edit_jabatan').value = data.jabatan;
            document.getElementById('edit_kelas').value = data.kelas;
            document.getElementById('edit_logo').value = data.logo_kelas;
            document.getElementById('editModal').classList.add('active');
        }

        function closeEditModal() {
            document.getElementById('editModal').classList.remove('active');
        }

        function openEditSosmedModal(data) {
            document.getElementById('edit_sosmed_id').value = data.id;
            document.getElementById('edit_sosmed_platform').value = data.platform;
            document.getElementById('edit_sosmed_url').value = data.url;
            document.getElementById('edit_sosmed_icon').value = data.icon_url;
            document.getElementById('editSosmedModal').classList.add('active');
        }

        function closeEditSosmedModal() {
            document.getElementById('editSosmedModal').classList.remove('active');
        }

        function switchTab(tabName) {
            window.location.href = 'kelola_beranda.php?tab=' + tabName;
        }

        // Sortable Logic
        function initSortable(id, fieldName) {
            const el = document.getElementById(id);
            if (el) {
                new Sortable(el, {
                    animation: 150,
                    handle: '.drag-handle-icon',
                    ghostClass: 'sortable-chosen',
                    onEnd: function(evt) {
                        const order = [];
                        document.querySelectorAll(`#${id} .banner-card, #${id} .pengurus-card`).forEach(item => order.push(item.getAttribute('data-id')));
                        const formData = new FormData();
                        formData.append('update_order_' + fieldName, JSON.stringify(order));
                        fetch('', {
                            method: 'POST',
                            body: formData
                        });
                    }
                });
            }
        }
        initSortable('sortable-banner', 'banner');
        initSortable('sortable-pengurus', 'pengurus');

        // Multi Delete Logic Banner
        function toggleSelectAllBanner(source) {
            document.querySelectorAll('.banner-card .card-checkbox').forEach(cb => cb.checked = source.checked);
            updateMultiDeleteBanner();
        }

        function updateMultiDeleteBanner() {
            const checked = document.querySelectorAll('.banner-card .card-checkbox:checked').length;
            const btn = document.getElementById('btn-multi-delete-banner');
            const countSpan = document.getElementById('count-selected-banner');
            if (checked > 0) {
                btn.style.display = 'inline-flex';
                countSpan.innerText = checked;
            } else {
                btn.style.display = 'none';
            }
        }

        // Multi Delete Logic Pengurus
        function toggleSelectAll(source) {
            document.querySelectorAll('.pengurus-card .card-checkbox').forEach(cb => cb.checked = source.checked);
            updateMultiDeleteButton();
        }

        function updateMultiDeleteButton() {
            const checked = document.querySelectorAll('.pengurus-card .card-checkbox:checked').length;
            const btn = document.getElementById('btn-multi-delete');
            const countSpan = document.getElementById('count-selected');
            if (checked > 0) {
                btn.style.display = 'inline-flex';
                countSpan.innerText = checked;
            } else {
                btn.style.display = 'none';
            }
        }

        // [BARU] Multi Delete Logic Galeri
        function toggleSelectAllGaleri(source) {
            document.querySelectorAll('.galeri-item .card-checkbox').forEach(cb => cb.checked = source.checked);
            updateMultiDeleteGaleri();
        }

        function updateMultiDeleteGaleri() {
            const checked = document.querySelectorAll('.galeri-item .card-checkbox:checked').length;
            const btn = document.getElementById('btn-multi-delete-galeri');
            const countSpan = document.getElementById('count-selected-galeri');
            if (checked > 0) {
                btn.style.display = 'inline-flex';
                countSpan.innerText = checked;
            } else {
                btn.style.display = 'none';
            }
        }

        // File Input Display - Supports Multiple
        function setupFileInput(inputId, labelId, selectedId = null) {
            const input = document.getElementById(inputId);
            const label = document.getElementById(labelId);
            const selected = selectedId ? document.getElementById(selectedId) : null;

            if (input) {
                input.addEventListener('change', function(e) {
                    if (this.files && this.files.length > 0) {
                        let names = [];
                        for (let i = 0; i < this.files.length; i++) {
                            names.push(this.files[i].name);
                        }
                        const text = names.join(', ');

                        if (selected) {
                            label.style.display = 'none';
                            selected.style.display = 'block';
                            selected.innerText = text;
                        } else {
                            label.innerText = text;
                        }
                    } else {
                        if (selected) {
                            label.style.display = 'block';
                            selected.style.display = 'none';
                            selected.innerText = '';
                        } else {
                            label.innerText = 'Pilih File';
                        }
                    }
                });
            }
        }
        setupFileInput('hero_input', 'hero_label', 'hero_selected');
        setupFileInput('edit_foto_input', 'edit_foto_label');
        setupFileInput('add_pengurus_foto', 'add_pengurus_label');
        setupFileInput('galeri_input', 'galeri_label');
    </script>
</body>

</html>