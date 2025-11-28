<?php
session_start();
require_once "koneksi.php";
$db = new database();

// Fungsi untuk mendapatkan semua ruang dokter
function getAllRuang($db) {
    $query = "SELECT DISTINCT ruang FROM data_dokter ORDER BY ruang";
    $result = $db->koneksi->query($query);
    $ruang_list = [];
    
    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $ruang_list[] = $row['ruang'];
        }
    }
    
    // Jika tidak ada ruang di database, gunakan default
    if (empty($ruang_list)) {
        $ruang_list = ['Ruang 1', 'Ruang 2', 'Ruang 3'];
    }
    
    return $ruang_list;
}

// Fungsi untuk mendapatkan panggilan terakhir per ruang dokter
function getPanggilanTerakhirPerRuang($db) {
    $all_ruang = getAllRuang($db);
    $panggilan_terakhir = [];
    
    // Inisialisasi semua ruang dengan nilai default
    foreach ($all_ruang as $ruang) {
        $panggilan_terakhir[$ruang] = [
            'nomor_antrian' => '-',
            'nama_pasien' => '-',
            'nama_dokter' => '-',
            'status' => '-'
        ];
    }
    
    // Ambil data panggilan terakhir yang ada
    $query = "SELECT da.kode_dokter, dd.nama_dokter, dd.ruang, da.nomor_antrian, da.id_pasien, dp.nama_pasien, da.status
              FROM data_antrian da
              LEFT JOIN data_dokter dd ON da.kode_dokter = dd.kode_dokter
              LEFT JOIN data_pasien dp ON da.id_pasien = dp.id_pasien
              WHERE da.status IN ('Dipanggil', 'Dilayani')
              ORDER BY da.id_antrian DESC";
    
    $result = $db->koneksi->query($query);
    
    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $ruang = $row['ruang'] ?? 'Umum';
            // Hanya simpan panggilan terakhir untuk setiap ruang jika belum ada
            if (isset($panggilan_terakhir[$ruang]) && $panggilan_terakhir[$ruang]['nomor_antrian'] === '-') {
                $panggilan_terakhir[$ruang] = [
                    'nomor_antrian' => $row['nomor_antrian'] ?? '-',
                    'nama_pasien' => $row['nama_pasien'] ?? '-',
                    'nama_dokter' => $row['nama_dokter'] ?? '-',
                    'status' => $row['status'] ?? '-'
                ];
            }
        }
    }
    
    return $panggilan_terakhir;
}

// Fungsi untuk mendapatkan antrian yang sedang dipanggil sekarang (hanya status 'Dipanggil')
function getAntrianSedangDipanggil($db) {
    $query = "SELECT da.id_antrian, da.nomor_antrian, da.id_pasien, dp.nama_pasien, 
                     da.kode_dokter, dd.nama_dokter, dd.ruang, da.status
              FROM data_antrian da
              LEFT JOIN data_dokter dd ON da.kode_dokter = dd.kode_dokter
              LEFT JOIN data_pasien dp ON da.id_pasien = dp.id_pasien
              WHERE da.status = 'Dipanggil'
              ORDER BY da.id_antrian ASC";
    
    $result = $db->koneksi->query($query);
    $antrian_dipanggil = [];
    
    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $antrian_dipanggil[] = $row;
        }
    }
    
    return $antrian_dipanggil;
}

// Ambil data untuk ditampilkan
$panggilan_terakhir = getPanggilanTerakhirPerRuang($db);
$antrian_dipanggil = getAntrianSedangDipanggil($db);

// Tampilkan notifikasi jika ada
$notif_status = $_SESSION['notif_status'] ?? null;
$notif_message = $_SESSION['notif_message'] ?? null;
unset($_SESSION['notif_status'], $_SESSION['notif_message']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Panggilan Antrian - EyeThica Klinik</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0, minimal-ui">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="description" content="Sistem Panggilan Antrian Klinik EyeThica">
    <meta name="keywords" content="klinik, antrian, panggilan, dokter">
    <meta name="author" content="EyeThica Klinik">

    <!-- [Favicon] icon -->
    <link rel="icon" href="../assets/images/faviconeyethica.png" type="image/x-icon">
    
    <!-- [Font] Family -->
    <link rel="stylesheet" href="../assets/fonts/inter/inter.css" id="main-font-link" />
    
    <!-- [Tabler Icons] -->
    <link rel="stylesheet" href="../assets/fonts/tabler-icons.min.css" >
    
    <!-- [Feather Icons] -->
    <link rel="stylesheet" href="../assets/fonts/feather.css" >
    
    <!-- [Font Awesome Icons] -->
    <link rel="stylesheet" href="../assets/fonts/fontawesome.css" >
    
    <!-- [Material Icons] -->
    <link rel="stylesheet" href="../assets/fonts/material.css" >
    
    <!-- [Template CSS Files] -->
    <link rel="stylesheet" href="../assets/css/style.css" id="main-style-link" >
    <link rel="stylesheet" href="../assets/css/style-preset.css" >

    <style>
    .card-panggilan {
        border-radius: 15px;
        box-shadow: 0 4px 20px rgba(0,0,0,0.1);
        border: none;
        margin-bottom: 20px;
        transition: transform 0.3s ease;
    }
    
    .card-panggilan:hover {
        transform: translateY(-5px);
    }
    
    .card-header-custom {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        border-radius: 15px 15px 0 0 !important;
        padding: 1.5rem;
        border-bottom: none;
    }
    
    .nomor-antrian-besar {
        font-size: 4rem;
        font-weight: bold;
        color: #2c3e50;
        text-align: center;
        margin: 20px 0;
        background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        text-shadow: 2px 2px 4px rgba(0,0,0,0.1);
    }
    
    .nomor-antrian-kosong {
        font-size: 4rem;
        font-weight: bold;
        color: #6c757d;
        text-align: center;
        margin: 20px 0;
        opacity: 0.5;
    }
    
    .info-pasien {
        background-color: #f8f9fa;
        border-radius: 10px;
        padding: 15px;
        margin: 10px 0;
    }
    
    .ruang-dokter {
        font-size: 1.2rem;
        font-weight: bold;
        color: #495057;
        background-color: #e9ecef;
        padding: 8px 15px;
        border-radius: 20px;
        display: inline-block;
        margin-bottom: 10px;
    }
    
    .status-badge {
        font-size: 0.9rem;
        padding: 8px 15px;
        border-radius: 20px;
    }
    
    .badge-dipanggil {
        background-color: #ffc107;
        color: #000;
    }
    
    .badge-dilayani {
        background-color: #17a2b8;
        color: #fff;
    }
    
    .badge-kosong {
        background-color: #6c757d;
        color: #fff;
    }
    
    .empty-state {
        text-align: center;
        padding: 40px 20px;
        color: #6c757d;
    }
    
    .empty-state i {
        font-size: 4rem;
        margin-bottom: 20px;
        color: #dee2e6;
    }
    
    .card-title {
        font-size: 1.5rem;
        font-weight: 600;
        margin-bottom: 0;
    }
    
    .highlight-card {
        border: 3px solid #ffc107;
        animation: pulse 2s infinite;
    }
    
    @keyframes pulse {
        0% { box-shadow: 0 0 0 0 rgba(255, 193, 7, 0.4); }
        70% { box-shadow: 0 0 0 10px rgba(255, 193, 7, 0); }
        100% { box-shadow: 0 0 0 0 rgba(255, 193, 7, 0); }
    }
    
    .refresh-btn {
        position: absolute;
        top: 20px;
        right: 20px;
        background: rgba(255,255,255,0.2);
        border: none;
        color: white;
        border-radius: 50%;
        width: 40px;
        height: 40px;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: all 0.3s ease;
    }
    
    .refresh-btn:hover {
        background: rgba(255,255,255,0.3);
        transform: rotate(180deg);
    }

    .antrian-aktif {
        background: linear-gradient(135deg, #fff3cd 0%, #ffeaa7 100%);
        border-left: 5px solid #ffc107;
        transition: all 0.3s ease;
    }

    .antrian-item {
        border-radius: 10px;
        margin-bottom: 15px;
        transition: all 0.3s ease;
    }

    .antrian-item:hover {
        transform: translateX(5px);
        box-shadow: 0 4px 15px rgba(0,0,0,0.1);
    }

    .ruang-card {
        transition: all 0.3s ease;
        height: 100%;
    }

    .ruang-card:hover {
        transform: scale(1.02);
    }

    .ruang-kosong {
        opacity: 0.7;
        background-color: #f8f9fa;
    }

    .antrian-count {
        font-size: 0.8rem;
        background: rgba(255,255,255,0.2);
        padding: 2px 8px;
        border-radius: 10px;
        margin-left: 10px;
    }

    .antrian-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
        gap: 20px;
        margin-top: 20px;
    }

    .antrian-list {
        max-height: 400px;
        overflow-y: auto;
        padding: 10px;
    }

    .antrian-list::-webkit-scrollbar {
        width: 6px;
    }

    .antrian-list::-webkit-scrollbar-track {
        background: #f1f1f1;
        border-radius: 10px;
    }

    .antrian-list::-webkit-scrollbar-thumb {
        background: #c1c1c1;
        border-radius: 10px;
    }

    .antrian-list::-webkit-scrollbar-thumb:hover {
        background: #a8a8a8;
    }

    .status-info {
        font-size: 0.85rem;
        margin-top: 5px;
    }

    .icon-status {
        margin-right: 5px;
    }
    </style>
</head>

<body data-pc-preset="preset-1" data-pc-sidebar-caption="true" data-pc-layout="vertical" data-pc-direction="ltr" data-pc-theme_contrast="" data-pc-theme="light">
    <!-- [ Pre-loader ] start -->
    <div class="loader-bg">
        <div class="loader-track">
            <div class="loader-fill"></div>
        </div>
    </div>
    <!-- [ Pre-loader ] End -->
    
    <?php include 'header.php'; ?>

    <!-- [ Main Content ] start -->
    <div class="pc-container">
        <div class="pc-content">
            <!-- [ breadcrumb ] start -->
            <div class="page-header">
                <div class="page-block">
                    <div class="row align-items-center">
                        <div class="col-md-12">
                            <ul class="breadcrumb">
                                <li class="breadcrumb-item"><a href="javascript: void(0)">Dashboard</a></li>
                                <li class="breadcrumb-item" aria-current="page">Panggilan Antrian</li>
                            </ul>
                        </div>
                        <div class="col-md-12">
                            <div class="page-header-title">
                                <h2 class="mb-0">Panggilan Antrian</h2>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- [ breadcrumb ] end -->
            
            <!-- [ Main Content ] start -->
            <div class="container-fluid">
                <?php if ($notif_message): ?>
                <div class="alert alert-<?= htmlspecialchars($notif_status) ?> alert-dismissible fade show" role="alert">
                    <i class="fas fa-<?= $notif_status === 'success' ? 'check-circle' : 'exclamation-triangle' ?> me-2"></i>
                    <?= htmlspecialchars($notif_message) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
                <?php endif; ?>

                <!-- Card 1: Panggilan Terakhir Setiap Ruang -->
                <div class="row">
                    <div class="col-12">
                        <div class="card card-panggilan">
                            <div class="card-header card-header-custom position-relative">
                                <h5 class="card-title mb-0">
                                    <i class="fas fa-door-open me-2"></i>Panggilan Terakhir per Ruang
                                </h5>
                                <button class="refresh-btn" onclick="location.reload()" title="Refresh Data">
                                    <i class="fas fa-sync-alt"></i>
                                </button>
                            </div>
                            <div class="card-body">
                                <div class="antrian-grid">
                                    <?php foreach ($panggilan_terakhir as $ruang => $data): ?>
                                        <div class="ruang-card card shadow-sm <?= $data['nomor_antrian'] === '-' ? 'ruang-kosong' : '' ?>">
                                            <div class="card-body text-center">
                                                <div class="ruang-dokter mb-3">
                                                    <i class="fas fa-door-closed me-2"></i>
                                                    <?= htmlspecialchars($ruang) ?>
                                                </div>
                                                <?php if ($data['nomor_antrian'] === '-'): ?>
                                                    <div class="nomor-antrian-kosong">
                                                        -
                                                    </div>
                                                    <div class="info-pasien">
                                                        <h6 class="mb-2 text-muted">Tidak ada panggilan</h6>
                                                        <p class="mb-1 text-muted small">
                                                            <i class="fas fa-user-md me-1"></i>
                                                            Menunggu antrian
                                                        </p>
                                                        <div class="status-info">
                                                            <span class="badge badge-kosong status-badge">
                                                                <i class="fas fa-clock icon-status"></i>Tidak Aktif
                                                            </span>
                                                        </div>
                                                    </div>
                                                <?php else: ?>
                                                    <div class="nomor-antrian-besar">
                                                        <?= htmlspecialchars($data['nomor_antrian']) ?>
                                                    </div>
                                                    <div class="info-pasien">
                                                        <h6 class="mb-2"><?= htmlspecialchars($data['nama_pasien']) ?></h6>
                                                        <p class="mb-1 text-muted small">
                                                            <i class="fas fa-user-md me-1"></i>
                                                            <?= htmlspecialchars($data['nama_dokter']) ?>
                                                        </p>
                                                        <div class="status-info">
                                                            <?php if ($data['status'] === 'Dipanggil'): ?>
                                                                <span class="badge badge-dipanggil status-badge">
                                                                    <i class="fas fa-bullhorn icon-status"></i>Sedang Dipanggil
                                                                </span>
                                                            <?php elseif ($data['status'] === 'Dilayani'): ?>
                                                                <span class="badge badge-dilayani status-badge">
                                                                    <i class="fas fa-user-md icon-status"></i>Sedang Dilayani
                                                                </span>
                                                            <?php else: ?>
                                                                <span class="badge badge-kosong status-badge">
                                                                    <i class="fas fa-info-circle icon-status"></i><?= htmlspecialchars($data['status']) ?>
                                                                </span>
                                                            <?php endif; ?>
                                                        </div>
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Card 2: Antrian yang Sedang Dipanggil (Hanya status 'Dipanggil') -->
                <div class="row">
                    <div class="col-12">
                        <div class="card card-panggilan <?= !empty($antrian_dipanggil) ? 'highlight-card' : '' ?>">
                            <div class="card-header card-header-custom">
                                <h5 class="card-title mb-0">
                                    <i class="fas fa-bullhorn me-2"></i>Antrian yang Sedang Dipanggil
                                    <span class="antrian-count"><?= count($antrian_dipanggil) ?> antrian</span>
                                </h5>
                            </div>
                            <div class="card-body">
                                <?php if (!empty($antrian_dipanggil)): ?>
                                    <div class="antrian-list">
                                        <?php foreach ($antrian_dipanggil as $antrian): ?>
                                            <div class="antrian-item card border-0 antrian-aktif">
                                                <div class="card-body">
                                                    <div class="row align-items-center">
                                                        <div class="col-md-2 text-center">
                                                            <div class="nomor-antrian-besar" style="font-size: 2.5rem;">
                                                                <?= htmlspecialchars($antrian['nomor_antrian']) ?>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-8">
                                                            <div class="info-pasien">
                                                                <div class="row">
                                                                    <div class="col-md-6">
                                                                        <h6 class="mb-2">
                                                                            <i class="fas fa-user-injured me-2 text-primary"></i>
                                                                            <?= htmlspecialchars($antrian['nama_pasien']) ?>
                                                                        </h6>
                                                                        <p class="mb-1 text-muted">
                                                                            <i class="fas fa-user-md me-1"></i>
                                                                            <?= htmlspecialchars($antrian['nama_dokter']) ?>
                                                                        </p>
                                                                    </div>
                                                                    <div class="col-md-6">
                                                                        <div class="d-flex flex-wrap gap-2">
                                                                            <span class="badge bg-primary status-badge">
                                                                                <i class="fas fa-door-open me-1"></i>
                                                                                <?= htmlspecialchars($antrian['ruang'] ?? 'Umum') ?>
                                                                            </span>
                                                                            <span class="badge badge-dipanggil status-badge">
                                                                                <i class="fas fa-bullhorn me-1"></i>
                                                                                Sedang Dipanggil
                                                                            </span>
                                                                        </div>
                                                                        <p class="text-success mb-0 mt-2 small">
                                                                            <i class="fas fa-info-circle me-1"></i>
                                                                            Silakan menuju ruang dokter
                                                                        </p>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-2 text-center">
                                                            <div class="panggil-indicator">
                                                                <i class="fas fa-volume-up fa-2x text-warning"></i>
                                                                <p class="small text-muted mt-2 mb-0">Sedang Dipanggil</p>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                <?php else: ?>
                                    <div class="empty-state">
                                        <i class="fas fa-bullhorn"></i>
                                        <h5>Tidak Ada Antrian yang Sedang Dipanggil</h5>
                                        <p class="text-muted">Semua antrian telah selesai atau belum ada yang dipanggil.</p>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- [ Main Content ] end -->
        </div>
    </div>
    <!-- [ Main Content ] end -->

    <footer class="pc-footer">
        <div class="footer-wrapper container-fluid">
            <div class="row">
                <div class="col my-1">
                    <p class="m-0">Copyright © 2025 Eyethica Klinik. All rights reserved.</p>
                </div>
                <div class="col-auto my-1">
                    <ul class="list-inline footer-link mb-0">
                        <li class="list-inline-item"><a href="../index.html">Home</a></li>
                    </ul>
                </div>
            </div>
        </div>
    </footer>

    <!-- Required Js -->
    <script src="../assets/js/plugins/popper.min.js"></script>
    <script src="../assets/js/plugins/simplebar.min.js"></script>
    <script src="../assets/js/plugins/bootstrap.min.js"></script>
    <script src="../assets/js/fonts/custom-font.js"></script>
    <script src="../assets/js/script.js"></script>
    <script src="../assets/js/theme.js"></script>
    <script src="../assets/js/plugins/feather.min.js"></script>

    <script>
    // Auto refresh setiap 30 detik
    setInterval(function() {
        location.reload();
    }, 30000);

    // Animasi untuk antrian yang sedang dipanggil
    document.addEventListener('DOMContentLoaded', function() {
        const antrianItems = document.querySelectorAll('.antrian-aktif');
        antrianItems.forEach((item, index) => {
            // Delay animasi untuk setiap item
            item.style.animationDelay = `${index * 0.2}s`;
        });
    });
    </script>
</body>
</html>