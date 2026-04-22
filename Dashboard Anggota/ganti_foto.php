<?php
// aktifkan error reporting untuk debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();

// SESUAIKAN PATH INI
require_once '../koneksi.php'; 

// Cek login
if (!isset($_SESSION['id'])) {
    echo '<script>alert("Sesi habis, silakan login kembali."); window.location.href="../Login/login.php";</script>';
    exit;
}

 $id_user = $_SESSION['id'];

// ========================================================
// PROSES 1: HAPUS FOTO (AJAX)
// ========================================================
if (isset($_POST['action']) && $_POST['action'] === 'delete_photo') {
    $foto_session = isset($_SESSION['foto']) ? $_SESSION['foto'] : '';
    
    if (!empty($foto_session)) {
    // Path diperbaiki ke ../uploads/foto_profil/
    $file_path = __DIR__ . "/../uploads/foto_profil/" . $foto_session;
    if (file_exists($file_path)) unlink($file_path);
        
        $query = "UPDATE users SET foto_profil = NULL WHERE id = $id_user";
        if (mysqli_query($koneksi, $query)) {
            unset($_SESSION['foto']);
            echo json_encode(['status' => 'success', 'message' => 'Foto profil berhasil dihapus.']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Gagal update database.']);
        }
    } else {
        echo json_encode(['status' => 'info', 'message' => 'Tidak ada foto untuk dihapus.']);
    }
    exit;
}

// ========================================================
// PROSES 2: UPLOAD & CROP (AJAX)
// ========================================================
if (isset($_POST['image_base64'])) {
    $image = $_POST['image_base64'];
    $image = str_replace('data:image/jpeg;base64,', '', $image);
    $image = str_replace('data:image/png;base64,', '', $image);
    $image = str_replace(' ', '+', $image);
    $data = base64_decode($image);

   
$new_file_name = "user_" . $id_user . ".jpg"; 
$upload_dir = __DIR__ . "/../uploads/foto_profil/";
$destination = $upload_dir . $new_file_name;

    if (!is_dir($upload_dir)) mkdir($upload_dir, 0777, true);

    if (file_put_contents($destination, $data)) {
        $query = "UPDATE users SET foto_profil = '$new_file_name' WHERE id = $id_user";
        if (mysqli_query($koneksi, $query)) {
            $_SESSION['foto'] = $new_file_name;
            echo json_encode(['status' => 'success', 'message' => 'Foto berhasil diperbarui!']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Gagal update database.']);
        }
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Gagal menyimpan file.']);
    }
    exit;
}

// ========================================================
// DATA VARIABEL
// ========================================================
 $nama_user = isset($_SESSION['nama']) ? htmlspecialchars($_SESSION['nama']) : 'User';
 $foto_session = isset($_SESSION['foto']) ? $_SESSION['foto'] : '';

 $has_custom_photo = false;
 $foto_display = "https://ui-avatars.com/api/?name=" . urlencode($nama_user) . "&background=d90429&color=fff&size=300";

if (!empty($foto_session)) {
    // Path diperbaiki ke ../uploads/foto_profil/
    $path_check = "../uploads/foto_profil/" . $foto_session;
    if (file_exists($path_check)) {
        $foto_display = $path_check . "?t=" . time();
        $has_custom_photo = true;
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ganti Foto Profil - PMR Millenium</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <!-- Cropper.js CSS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.13/cropper.min.css">
    
    <style>
        :root {
            --primary-color: #d90429;
            --primary-hover: #c92a2a;
            --bg-color: #f4f6f9;
            --card-bg: #ffffff;
            --text-main: #1e293b;
            --text-muted: #64748b;
            --border-color: #e2e8f0;
            --shadow-md: 0 10px 30px rgba(0,0,0,0.1);
            --radius: 20px;
        }

        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Segoe UI', sans-serif; }
        body { background: var(--bg-color); min-height: 100vh; display: flex; justify-content: center; align-items: center; padding: 20px; color: var(--text-main); }

        .card-container {
            background: var(--card-bg);
            width: 100%;
            max-width: 480px;
            border-radius: var(--radius);
            box-shadow: var(--shadow-md);
            overflow: hidden;
            border: 1px solid var(--border-color);
        }

        .card-header {
            background: var(--primary-color);
            padding: 25px;
            text-align: center;
            color: white;
        }
        .card-header h2 { font-size: 1.4rem; margin-bottom: 5px; font-weight: 700; }
        .card-header p { font-size: 0.9rem; opacity: 0.9; font-weight: 400; }

        .card-body { padding: 25px; }

        /* Area Crop Wrapper */
        .cropper-wrapper {
            width: 100%;
            height: 300px;
            background: #f8f9fa;
            border: 2px solid var(--border-color);
            border-radius: 12px;
            margin-bottom: 20px;
            position: relative;
            overflow: hidden;
            display: flex;
            justify-content: center;
            align-items: center;
        }

        /* Style untuk Placeholder (Jika belum ada foto) */
        .placeholder-state {
            text-align: center;
            color: var(--text-muted);
        }
        .placeholder-state i { font-size: 3.5rem; margin-bottom: 10px; opacity: 0.3; }
        .placeholder-state p { font-weight: 500; font-size: 0.95rem; opacity: 0.7; }

        /* PERBAIKAN: Style untuk Foto Saat Ini (Preview Asli) */
        .current-photo-display {
            max-width: 100%;  /* Maksimal lebar kotak */
            max-height: 100%; /* Maksimal tinggi kotak */
            width: auto;
            height: auto;
            object-fit: contain; /* Gambar ditampilkan utuh, tidak di-zoom/dipotong */
            background-color: #f1f5f9; /* Warna latar belakang sisa ruang */
            display: block;
        }

        /* Gambar untuk proses Cropper (Hidden by default) */
        #imageToCrop {
            display: block;
            max-width: 100%;
            opacity: 0;
        }

        /* Input File */
        .file-upload-input { display: none; }

        .upload-btn-select {
            display: block;
            width: 100%;
            padding: 12px;
            border: 2px solid var(--primary-color);
            background: #fff1f1;
            color: var(--primary-color);
            border-radius: 10px;
            cursor: pointer;
            font-weight: 600;
            text-align: center;
            transition: 0.2s;
            margin-bottom: 15px;
        }
        .upload-btn-select:hover { background: #ffe0e0; }

        .btn-group { display: flex; gap: 10px; margin-bottom: 15px; }

        .btn-save {
            flex: 2;
            padding: 14px;
            background: var(--primary-color);
            color: white;
            border: none;
            border-radius: 10px;
            font-weight: 600;
            cursor: pointer;
            transition: 0.2s;
        }
        .btn-save:hover { background: var(--primary-hover); }
        .btn-save:disabled { background: #ccc; cursor: not-allowed; }

        .btn-delete {
            flex: 1;
            padding: 14px;
            background: white;
            color: #ef4444;
            border: 1px solid #fecaca;
            border-radius: 10px;
            font-weight: 600;
            cursor: pointer;
            transition: 0.2s;
        }
        .btn-delete:hover { background: #fee2e2; border-color: #ef4444; }

        .btn-back {
            display: block;
            text-align: center;
            color: var(--text-muted);
            text-decoration: none;
            font-size: 0.9rem;
            font-weight: 500;
        }
        .btn-back:hover { color: var(--primary-color); }

        /* Alert Popup */
        #alertBox {
            position: fixed; top: 20px; right: 20px;
            padding: 15px 25px; border-radius: 8px;
            background: white; color: var(--text-main);
            box-shadow: 0 5px 20px rgba(0,0,0,0.15);
            display: none; z-index: 9999; font-weight: 500;
        }
        .alert-success { border-left: 5px solid #10b981; }
        .alert-error { border-left: 5px solid var(--primary-color); }

        /* Cropper Customization */
        .cropper-view-box { border-radius: 50%; outline: 2px solid var(--primary-color); }
        .cropper-face { background-color: rgba(217, 4, 41, 0.1); }
        .cropper-modal { background: rgba(0,0,0,0.6); }
    </style>
</head>
<body>

    <div id="alertBox"></div>

    <div class="card-container">
        <div class="card-header">
            <h2><i class="fas fa-camera-retro"></i> Ganti Foto Profil</h2>
            <p>Pilih foto baru untuk memotong area wajah.</p>
        </div>

        <div class="card-body">
            <div class="cropper-wrapper" id="wrapperCropper">
                
                <!-- KONDISI 1: JIKA SUDAH ADA FOTO (PREVIEW ASLI) -->
                <?php if ($has_custom_photo): ?>
                    <img src="<?= $foto_display ?>" class="current-photo-display" id="currentPhoto">
                
                <!-- KONDISI 2: JIKA BELUM ADA FOTO (PLACEHOLDER) -->
                <?php else: ?>
                    <div class="placeholder-state" id="placeholder">
                        <i class="fas fa-user-circle"></i>
                        <p>Belum ada foto profil</p>
                    </div>
                <?php endif; ?>

                <!-- Gambar ini khusus untuk dijadikan target Cropper.js saat user pilih file baru -->
                <img id="imageToCrop" src="" style="display: none;">
            </div>

            <form id="uploadForm">
                <input type="file" id="inputImage" class="file-upload-input" accept="image/jpeg, image/png, image/jpg">
                
                <label for="inputImage" class="upload-btn-select">
                    <i class="fas fa-folder-open"></i> Pilih Foto Baru
                </label>

                <div class="btn-group">
                    <button type="button" class="btn-save" id="btnSave" disabled>
                        <i class="fas fa-save"></i> Simpan
                    </button>
                    
                    <?php if ($has_custom_photo): ?>
                    <button type="button" class="btn-delete" id="btnDelete">
                        <i class="fas fa-trash"></i>
                    </button>
                    <?php endif; ?>
                </div>
            </form>

            <a href="anggota.php" class="btn-back"><i class="fas fa-arrow-left"></i> Kembali ke Dashboard</a>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.13/cropper.min.js"></script>

    <script>
        $(document).ready(function() {
            const $image = $('#imageToCrop');
            const $input = $('#inputImage');
            const $btnSave = $('#btnSave');
            const $btnDelete = $('#btnDelete');
            const $currentPhoto = $('#currentPhoto');
            const $placeholder = $('#placeholder');
            const $alertBox = $('#alertBox');
            let cropper;

            // 1. Saat user memilih file
            $input.on('change', function(e) {
                const files = e.target.files;
                const file = files[0];

                if (file) {
                    if (!file.type.match('image.*')) {
                        showAlert('Format file tidak didukung!', 'error');
                        return;
                    }

                    const reader = new FileReader();
                    reader.onload = function(event) {
                        // 1. Sembunyikan elemen lama
                        $currentPhoto.hide();
                        $placeholder.hide();

                        // 2. Tampilkan tag img untuk Cropper
                        $image.attr('src', event.target.result).show();

                        // 3. Hancurkan cropper lama jika ada
                        if (cropper) cropper.destroy();

                        // 4. Inisialisasi Cropper Baru
                        cropper = new Cropper($image[0], {
                            aspectRatio: 1, 
                            viewMode: 1,
                            dragMode: 'move',
                            autoCropArea: 0.8,
                            responsive: true
                        });

                        // 5. Aktifkan tombol simpan
                        $btnSave.prop('disabled', false);
                    };
                    reader.readAsDataURL(file);
                }
            });

            // 2. Saat tombol simpan ditekan
            $btnSave.on('click', function() {
                if (!cropper) return;

                const $btn = $(this);
                $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Menyimpan...');

                const canvas = cropper.getCroppedCanvas({ width: 400, height: 400 });
                const imageData = canvas.toDataURL('image/jpeg', 0.9);

                $.ajax({
                    url: '', type: 'POST',
                    data: { image_base64: imageData },
                    dataType: 'json',
                    success: function(res) {
                        if (res.status === 'success') {
                            showAlert(res.message, 'success');
                            setTimeout(() => location.reload(), 1200);
                        } else {
                            showAlert(res.message, 'error');
                            $btn.prop('disabled', false).html('<i class="fas fa-save"></i> Simpan');
                        }
                    },
                    error: function() {
                        showAlert('Error sistem', 'error');
                        $btn.prop('disabled', false).html('<i class="fas fa-save"></i> Simpan');
                    }
                });
            });

            // 3. Saat tombol hapus ditekan
            <?php if ($has_custom_photo): ?>
            $btnDelete.on('click', function() {
                if (confirm('Yakin hapus foto profil?')) {
                    const $btn = $(this);
                    $btn.html('<i class="fas fa-spinner fa-spin"></i>');
                    $.post('', { action: 'delete_photo' }, function(res) {
                        if (res.status === 'success') {
                            showAlert(res.message, 'success');
                            setTimeout(() => location.reload(), 1200);
                        } else {
                            showAlert(res.message, 'error');
                            $btn.html('<i class="fas fa-trash"></i>');
                        }
                    }, 'json');
                }
            });
            <?php endif; ?>

            function showAlert(msg, type) {
                $alertBox.html(msg).removeClass('alert-success alert-error').addClass('alert-' + type).fadeIn();
                setTimeout(() => $alertBox.fadeOut(), 3000);
            }
        });
    </script>
</body>
</html>