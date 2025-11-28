<?php
session_start();
require_once "koneksi.php"; // Pastikan path ke file koneksi.php benar
$db = new database();

// Fungsi format tanggal sesuai bahasa
function formatTanggal($tanggal, $bahasa = 'id') {
    $formatter = new IntlDateFormatter(
        $bahasa,
        IntlDateFormatter::LONG,
        IntlDateFormatter::NONE,
        'Asia/Jakarta',
        IntlDateFormatter::GREGORIAN
    );
    return $formatter->format(new DateTime($tanggal));
}

// PROSES HAPUS ANTRIAN
if (isset($_GET['hapus'])) {
    $id_antrian = $_GET['hapus'];
    $antrian_data = $db->get_antrian_by_id($id_antrian);

    if ($db->hapus_data_antrian($id_antrian)) {
        $username = $_SESSION['username'] ?? 'unknown user';
        $entitas = 'Hapus';
        $jenis = 'Antrian';
        $deskripsi = "Antrian '{$antrian_data['nomor_antrian']}' berhasil dihapus oleh $username.";
        $waktu = date('Y-m-d H:i:s');
        $db->tambah_aktivitas($entitas, $jenis, $deskripsi, $waktu);

        $_SESSION['notif_status'] = 'success';
        $_SESSION['notif_message'] = 'Data antrian berhasil dihapus.';
    } else {
        $_SESSION['notif_status'] = 'error';
        $_SESSION['notif_message'] = 'Gagal menghapus data antrian.';
    }
    header("Location: dataantrian.php");
    exit();
}

// PROSES EDIT ANTRIAN
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_antrian'])) {
    $id_antrian = $_POST['id_antrian'] ?? '';
    $nomor_antrian = $_POST['nomor_antrian'] ?? '';
    $id_pasien = $_POST['id_pasien'] ?? '';
    $kode_dokter = $_POST['kode_dokter'] ?? '';
    $status = $_POST['status'] ?? '';
    
    // Validasi data
    if (empty($id_antrian) || empty($nomor_antrian) || empty($id_pasien)) {
        $_SESSION['notif_status'] = 'error';
        $_SESSION['notif_message'] = 'Field nomor antrian dan pasien wajib diisi!';
        header("Location: dataantrian.php");
        exit();
    }
    
    // Update data antrian
    if ($db->update_data_antrian($id_antrian, $nomor_antrian, $id_pasien, $kode_dokter, $status)) {
        // Log aktivitas
        $username_session = $_SESSION['username'] ?? 'unknown user';
        $entitas = 'Edit';
        $jenis = 'Antrian';
        $deskripsi = "Antrian '$nomor_antrian' berhasil diupdate oleh $username_session.";
        $waktu = date('Y-m-d H:i:s');
        $db->tambah_aktivitas($entitas, $jenis, $deskripsi, $waktu);
        
        $_SESSION['notif_status'] = 'success';
        $_SESSION['notif_message'] = 'Data antrian berhasil diupdate.';
    } else {
        $_SESSION['notif_status'] = 'error';
        $_SESSION['notif_message'] = 'Gagal mengupdate data antrian.';
    }
    
    header("Location: dataantrian.php");
    exit();
}

// PROSES TAMBAH ANTRIAN
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['tambah_antrian'])) {
    $nomor_antrian = $_POST['nomor_antrian'] ?? '';
    $id_pasien = $_POST['id_pasien'] ?? '';
    $kode_dokter = $_POST['kode_dokter'] ?? '';
    $status = $_POST['status'] ?? 'Menunggu';
    
    // Validasi data
    if (empty($nomor_antrian) || empty($id_pasien)) {
        $_SESSION['notif_status'] = 'error';
        $_SESSION['notif_message'] = 'Field nomor antrian dan pasien wajib diisi!';
        header("Location: dataantrian.php");
        exit();
    }
    
    // Tambah data antrian
    if ($db->tambah_data_antrian($nomor_antrian, $id_pasien, $kode_dokter, $status)) {
        // Log aktivitas
        $username_session = $_SESSION['username'] ?? 'unknown user';
        $entitas = 'Tambah';
        $jenis = 'Antrian';
        $deskripsi = "Antrian '$nomor_antrian' berhasil ditambahkan oleh $username_session.";
        $waktu = date('Y-m-d H:i:s');
        $db->tambah_aktivitas($entitas, $jenis, $deskripsi, $waktu);
        
        $_SESSION['notif_status'] = 'success';
        $_SESSION['notif_message'] = 'Data antrian berhasil ditambahkan.';
    } else {
        $_SESSION['notif_status'] = 'error';
        $_SESSION['notif_message'] = 'Gagal menambahkan data antrian.';
    }
    
    header("Location: dataantrian.php");
    exit();
}

// Konfigurasi pagination, search, dan sorting
$entries_per_page = isset($_GET['entries']) ? (int)$_GET['entries'] : 10;
$current_page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$search_query = isset($_GET['search']) ? trim($_GET['search']) : '';
$sort_order = isset($_GET['sort']) && $_GET['sort'] === 'desc' ? 'desc' : 'asc';

// Ambil semua data antrian
$all_antrian = $db->tampil_data_antrian();

// Ambil data pasien dan dokter untuk dropdown
$all_pasien = $db->tampil_data_pasien();
$all_dokter = $db->tampil_data_dokter(); // Asumsi ada method untuk mengambil data dokter

// Filter data berdasarkan search query
if (!empty($search_query)) {
    $filtered_antrian = [];
    foreach ($all_antrian as $antrian) {
        // Cari di semua kolom yang relevan
        if (stripos($antrian['id_antrian'] ?? '', $search_query) !== false ||
            stripos($antrian['nomor_antrian'] ?? '', $search_query) !== false ||
            stripos($antrian['id_pasien'] ?? '', $search_query) !== false ||
            stripos($antrian['kode_dokter'] ?? '', $search_query) !== false ||
            stripos($antrian['status'] ?? '', $search_query) !== false ||
            stripos($antrian['waktu_daftar'] ?? '', $search_query) !== false) {
            $filtered_antrian[] = $antrian;
        }
    }
    $all_antrian = $filtered_antrian;
}

// Urutkan data berdasarkan ID Antrian
if ($sort_order === 'desc') {
    // Urutkan dari ID terbesar ke terkecil (terakhir ke terawal)
    usort($all_antrian, function($a, $b) {
        return ($b['id_antrian'] ?? 0) - ($a['id_antrian'] ?? 0);
    });
} else {
    // Urutkan dari ID terkecil ke terbesar (terawal ke terakhir) - default
    usort($all_antrian, function($a, $b) {
        return ($a['id_antrian'] ?? 0) - ($b['id_antrian'] ?? 0);
    });
}

// Hitung total data
$total_entries = count($all_antrian);

// Hitung total halaman
$total_pages = ceil($total_entries / $entries_per_page);

// Pastikan current page valid
if ($current_page < 1) $current_page = 1;
if ($current_page > $total_pages && $total_pages > 0) $current_page = $total_pages;

// Hitung offset
$offset = ($current_page - 1) * $entries_per_page;

// Ambil data untuk halaman saat ini
$data_antrian = array_slice($all_antrian, $offset, $entries_per_page);

// Hitung nomor urut yang benar berdasarkan sorting
if ($sort_order === 'desc') {
    // Untuk descending: nomor urut dari total_entries ke bawah
    $start_number = $total_entries - $offset;
} else {
    // Untuk ascending: nomor urut dari 1 ke atas (default)
    $start_number = $offset + 1;
}

// Tampilkan notifikasi jika ada
$notif_status = $_SESSION['notif_status'] ?? null;
$notif_message = $_SESSION['notif_message'] ?? null;
unset($_SESSION['notif_status'], $_SESSION['notif_message']);

// Cek apakah data antrian kosong untuk memicu modal
$is_data_empty = empty($data_antrian);
?>

<!DOCTYPE html>
<html lang="en">
  <!-- [Head] start -->

  <head>
    <title>Data Antrian - EyeThica Klinik</title>
    <!-- [Meta] -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0, minimal-ui">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="description"
      content="Able Pro is a trending dashboard template built with the Bootstrap 5 design framework. It is available in multiple technologies, including Bootstrap, React, Vue, CodeIgniter, Angular, .NET, and more.">
    <meta name="keywords"
      content="Bootstrap admin template, Dashboard UI Kit, Dashboard Template, Backend Panel, react dashboard, angular dashboard">
    <meta name="author" content="Phoenixcoded">

    <!-- [Favicon] icon -->
    <link rel="icon" href="../assets/images/faviconeyethica.png" type="image/x-icon"> <!-- [Font] Family -->
<link rel="stylesheet" href="../assets/fonts/inter/inter.css" id="main-font-link" />
<!-- [Tabler Icons] https://tablericons.com -->
<link rel="stylesheet" href="../assets/fonts/tabler-icons.min.css" >
<!-- [Feather Icons] https://feathericons.com -->
<link rel="stylesheet" href="../assets/fonts/feather.css" >
<!-- [Font Awesome Icons] https://fontawesome.com/icons -->
<link rel="stylesheet" href="../assets/fonts/fontawesome.css" >
<!-- [Material Icons] https://fonts.google.com/icons -->
<link rel="stylesheet" href="../assets/fonts/material.css" >
<!-- [Template CSS Files] -->
<link rel="stylesheet" href="../assets/css/style.css" id="main-style-link" >
<link rel="stylesheet" href="../assets/css/style-preset.css" >

<style>
/* Memusatkan modal secara vertikal */
.modal-dialog {
    display: flex;
    align-items: center;
    min-height: calc(100% - 1rem);
}

@media (min-width: 576px) {
    .modal-dialog {
        min-height: calc(100% - 3.5rem);
    }
}

/* Optional: Tambahkan animasi yang lebih smooth */
.modal.fade .modal-dialog {
    transform: translate(0, -50px);
    transition: transform 0.3s ease-out;
}

.modal.show .modal-dialog {
    transform: none;
}

/* Memastikan modal konten memiliki margin otomatis */
.modal-content {
    margin: auto;
}

/* Styling untuk modal hapus */
.modal-backdrop {
    background-color: rgba(0, 0, 0, 0.5);
}

.modal {
    backdrop-filter: blur(2px);
}

.modal-content {
    border: none;
    border-radius: 12px;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
}

.modal-header {
    border-bottom: 1px solid #dee2e6;
    padding: 1rem 1.5rem;
}

.modal-body {
    padding: 1.5rem;
}

.modal-footer {
    border-top: 1px solid #dee2e6;
    padding: 1rem 1.5rem;
}

/* Animasi modal */
.modal.fade .modal-dialog {
    transform: translateY(-50px);
    transition: transform 0.3s ease-out, opacity 0.3s ease-out;
}

.modal.show .modal-dialog {
    transform: translateY(0);
}

/* Tombol hapus dan edit */
.btn-hapus, .btn-edit {
    transition: all 0.3s ease;
}

.btn-hapus:hover {
    transform: scale(1.05);
    box-shadow: 0 4px 8px rgba(220, 53, 69, 0.3);
}

.btn-edit:hover {
    transform: scale(1.05);
    box-shadow: 0 4px 8px rgba(255, 193, 7, 0.3);
}

/* Loading spinner */
.spinner-border-sm {
    width: 1rem;
    height: 1rem;
}

/* Badge status */
.badge-menunggu {
    background-color: #ffc107;
    color: #000;
}

.badge-dipanggil {
    background-color: #17a2b8;
    color: #fff;
}

.badge-dilayani {
    background-color: #1174ff;
    color: #fff;
}

.badge-selesai {
    background-color: #28a745;
    color: #fff;
}

.badge-batal {
    background-color: #dc3545;
    color: #fff;
}

/* Styling untuk nomor antrian */
.nomor-antrian {
    font-weight: bold;
    font-size: 1.1em;
    color: #2c3e50;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    padding: 5px 10px;
    border-radius: 20px;
    display: inline-block;
    min-width: 60px;
    text-align: center;
}

/* Styling untuk field read-only */
.form-control[readonly] {
    background-color: #f8f9fa;
    border-color: #e9ecef;
    color: #6c757d;
    cursor: not-allowed;
}

.form-control[readonly]:focus {
    border-color: #e9ecef;
    box-shadow: none;
}
</style>
  </head>
  <!-- [Head] end -->
  <!-- [Body] Start -->

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
                  <li class="breadcrumb-item" aria-current="page">Data Antrian</li>
                </ul>
              </div>
              <div class="col-md-12">
                <div class="page-header-title">
                  <h2 class="mb-0">Data Antrian</h2>
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

            <div class="d-flex justify-content-start mb-4">
                <!-- Tombol Tambah Antrian dengan Modal -->
                <button type="button" class="btn btn-dark me-2" data-bs-toggle="modal" data-bs-target="#tambahAntrianModal">
                    <i class="fas fa-plus me-1"></i> Tambah Antrian
                </button>
            </div>

            <div class="card shadow-sm">
                <div class="card-body">
                    <!-- Show Entries dan Search -->
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <div class="d-flex align-items-center">
                                <label class="me-2 mb-0">Show</label>
                                <select class="form-select form-select-sm w-auto" id="entriesPerPage" onchange="changeEntries()">
                                    <option value="5" <?= $entries_per_page == 5 ? 'selected' : '' ?>>5</option>
                                    <option value="10" <?= $entries_per_page == 10 ? 'selected' : '' ?>>10</option>
                                    <option value="25" <?= $entries_per_page == 25 ? 'selected' : '' ?>>25</option>
                                    <option value="50" <?= $entries_per_page == 50 ? 'selected' : '' ?>>50</option>
                                    <option value="100" <?= $entries_per_page == 100 ? 'selected' : '' ?>>100</option>
                                </select>
                                <label class="ms-2 mb-0">entries</label>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <form method="GET" action="" class="d-flex justify-content-end">
                                <div class="input-group input-group-sm" style="width: 300px;">
                                    <input type="text" 
                                           class="form-control" 
                                           name="search" 
                                           placeholder="Cari data antrian..." 
                                           value="<?= htmlspecialchars($search_query) ?>"
                                           aria-label="Search">
                                    <input type="hidden" name="entries" value="<?= $entries_per_page ?>">
                                    <input type="hidden" name="sort" value="<?= $sort_order ?>">
                                    <button class="btn btn-outline-secondary" type="submit">
                                        <i class="fas fa-search"></i>
                                    </button>
                                    <?php if (!empty($search_query)): ?>
                                    <a href="dataantrian.php?entries=<?= $entries_per_page ?>&sort=<?= $sort_order ?>" class="btn btn-outline-danger" type="button">
                                        <i class="fas fa-times"></i>
                                    </a>
                                    <?php endif; ?>
                                </div>
                            </form>
                        </div>
                    </div>

                    <?php if (!empty($search_query)): ?>
                    <div class="alert alert-info alert-dismissible fade show mb-3" role="alert">
                        <i class="fas fa-info-circle me-2"></i>
                        Menampilkan hasil pencarian untuk: <strong>"<?= htmlspecialchars($search_query) ?>"</strong>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                    <?php endif; ?>

                    <div class="table-responsive">
                        <table id="antrianTable" class="table table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th>
                                        <a href="<?= getSortUrl($sort_order) ?>" class="text-decoration-none text-dark">
                                            No 
                                            <?php if ($sort_order === 'asc'): ?>
                                                <i class="fas fa-sort-up ms-1"></i>
                                            <?php else: ?>
                                                <i class="fas fa-sort-down ms-1"></i>
                                            <?php endif; ?>
                                        </a>
                                    </th>
                                    <th>ID</th>
                                    <th>Nomor Antrian</th>
                                    <th>ID Pasien</th>
                                    <th>Kode Dokter</th>
                                    <th>Status</th>
                                    <th>Waktu Daftar</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                if (!empty($data_antrian) && is_array($data_antrian)) {
                                    foreach ($data_antrian as $antrian) {
                                        $id_antrian = htmlspecialchars($antrian['id_antrian'] ?? '', ENT_QUOTES, 'UTF-8');
                                        $nomor_antrian = htmlspecialchars($antrian['nomor_antrian'] ?? '', ENT_QUOTES, 'UTF-8');
                                        $id_pasien = htmlspecialchars($antrian['id_pasien'] ?? '', ENT_QUOTES, 'UTF-8');
                                        $kode_dokter = htmlspecialchars($antrian['kode_dokter'] ?? '', ENT_QUOTES, 'UTF-8');
                                        $status = htmlspecialchars($antrian['status'] ?? '', ENT_QUOTES, 'UTF-8');
                                        $waktu_daftar = htmlspecialchars($antrian['waktu_daftar'] ?? '', ENT_QUOTES, 'UTF-8');
                                        
                                        // Format waktu daftar
                                        $waktu_daftar_formatted = !empty($waktu_daftar) ? date('d-m-Y H:i', strtotime($waktu_daftar)) : '-';
                                        
                                        // Tentukan class badge berdasarkan status
                                        $badge_class = 'badge-secondary';
                                        switch ($status) {
                                            case 'Menunggu':
                                                $badge_class = 'badge-menunggu';
                                                break;
                                            case 'Dipanggil':
                                                $badge_class = 'badge-dipanggil';
                                                break;
                                            case 'Dilayani':
                                                $badge_class = 'badge-dilayani';
                                                break;
                                            case 'Selesai':
                                                $badge_class = 'badge-selesai';
                                                break;
                                            case 'Batal':
                                                $badge_class = 'badge-batal';
                                                break;
                                        }
                                ?>
                                    <tr>
                                        <td><?= $start_number ?></td>
                                        <td><?= $id_antrian ?></td>
                                        <td>
                                            <span class="nomor-antrian"><?= $nomor_antrian ?></span>
                                        </td>
                                        <td><?= $id_pasien ?></td>
                                        <td><?= $kode_dokter ?></td>
                                        <td>
                                            <span class="badge <?= $badge_class ?>"><?= $status ?></span>
                                        </td>
                                        <td><?= $waktu_daftar_formatted ?></td>
                                        <td>
                                            <div class="btn-group" role="group">
                                                <button type="button"
                                                        class="btn btn-warning btn-sm btn-edit"
                                                        data-id="<?= $id_antrian ?>"
                                                        data-nomor="<?= $nomor_antrian ?>"
                                                        data-pasien="<?= $id_pasien ?>"
                                                        data-dokter="<?= $kode_dokter ?>"
                                                        data-status="<?= $status ?>"
                                                        data-waktu_daftar="<?= $waktu_daftar ?>"
                                                        title="Edit Antrian">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                                <button type="button"
                                                        class="btn btn-danger btn-sm btn-hapus"
                                                        data-id="<?= $id_antrian ?>"
                                                        data-nomor="<?= $nomor_antrian ?>"
                                                        title="Hapus Antrian">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                <?php
                                        // Update nomor urut berdasarkan sorting
                                        if ($sort_order === 'desc') {
                                            $start_number--; // Untuk descending: turun
                                        } else {
                                            $start_number++; // Untuk ascending: naik
                                        }
                                    }
                                } else {
                                    echo '<tr><td colspan="8" class="text-center text-muted">';
                                    if (!empty($search_query)) {
                                        echo 'Tidak ada data antrian yang sesuai dengan pencarian "' . htmlspecialchars($search_query) . '"';
                                    } else {
                                        echo 'Tidak ada data antrian ditemukan.';
                                    }
                                    echo '</td></tr>';
                                }
                                ?>
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination - SELALU TAMPIL JIKA ADA DATA -->
                    <?php if ($total_entries > 0): ?>
                    <div class="row mt-3">
                        <div class="col-md-6">
                            <p class="text-muted mb-0">
                                Menampilkan <?= $total_entries > 0 ? ($offset + 1) : 0 ?> 
                                sampai <?= min($offset + $entries_per_page, $total_entries) ?> 
                                dari <?= $total_entries ?> entri
                                <?php if (!empty($search_query)): ?>
                                <span class="text-info">(hasil pencarian)</span>
                                <?php endif; ?>
                                <?php if ($sort_order === 'desc'): ?>
                                <span class="text-warning">(diurutkan dari terbaru)</span>
                                <?php else: ?>
                                <span class="text-warning">(diurutkan dari terlama)</span>
                                <?php endif; ?>
                            </p>
                        </div>
                        <div class="col-md-6">
                            <?php if ($total_pages > 1): ?>
                            <nav aria-label="Page navigation">
                                <ul class="pagination justify-content-end mb-0">
                                    <!-- Previous Page -->
                                    <li class="page-item <?= $current_page <= 1 ? 'disabled' : '' ?>">
                                        <a class="page-link" href="<?= $current_page > 1 ? getPaginationUrl($current_page - 1, $entries_per_page, $search_query, $sort_order) : '#' ?>">
                                            Sebelumnya
                                        </a>
                                    </li>
                                    
                                    <!-- Page Numbers dengan format: Sebelumnya | 1 | 2 3 4 5... 11 Selanjutnya -->
                                    <?php
                                    // Selalu tampilkan halaman 1
                                    echo '<li class="page-item ' . ($current_page == 1 ? 'active' : '') . '">';
                                    echo '<a class="page-link" href="' . getPaginationUrl(1, $entries_per_page, $search_query, $sort_order) . '">1</a>';
                                    echo '</li>';
                                    
                                    // Tentukan range halaman yang akan ditampilkan
                                    $start = 2;
                                    $end = min(5, $total_pages - 1);
                                    
                                    // Jika current page > 3, adjust the range
                                    if ($current_page > 3) {
                                        $start = $current_page - 1;
                                        $end = min($current_page + 2, $total_pages - 1);
                                    }
                                    
                                    // Tampilkan ellipsis jika ada gap
                                    if ($start > 2) {
                                        echo '<li class="page-item disabled"><span class="page-link">...</span></li>';
                                    }
                                    
                                    // Tampilkan halaman-halaman
                                    for ($i = $start; $i <= $end; $i++) {
                                        if ($i < $total_pages) {
                                            echo '<li class="page-item ' . ($i == $current_page ? 'active' : '') . '">';
                                            echo '<a class="page-link" href="' . getPaginationUrl($i, $entries_per_page, $search_query, $sort_order) . '">' . $i . '</a>';
                                            echo '</li>';
                                        }
                                    }
                                    
                                    // Tampilkan ellipsis sebelum halaman terakhir jika perlu
                                    if ($end < $total_pages - 1) {
                                        echo '<li class="page-item disabled"><span class="page-link">...</span></li>';
                                    }
                                    
                                    // Tampilkan halaman terakhir jika lebih dari 1 halaman
                                    if ($total_pages > 1) {
                                        echo '<li class="page-item ' . ($current_page == $total_pages ? 'active' : '') . '">';
                                        echo '<a class="page-link" href="' . getPaginationUrl($total_pages, $entries_per_page, $search_query, $sort_order) . '">' . $total_pages . '</a>';
                                        echo '</li>';
                                    }
                                    ?>
                                    
                                    <!-- Next Page -->
                                    <li class="page-item <?= $current_page >= $total_pages ? 'disabled' : '' ?>">
                                        <a class="page-link" href="<?= $current_page < $total_pages ? getPaginationUrl($current_page + 1, $entries_per_page, $search_query, $sort_order) : '#' ?>">
                                            Selanjutnya
                                        </a>
                                    </li>
                                </ul>
                            </nav>
                            <?php else: ?>
                            <!-- Tampilkan pagination sederhana jika hanya 1 halaman -->
                            <nav aria-label="Page navigation">
                                <ul class="pagination justify-content-end mb-0">
                                    <li class="page-item disabled">
                                        <a class="page-link" href="#">Sebelumnya</a>
                                    </li>
                                    <li class="page-item active">
                                        <a class="page-link" href="#">1</a>
                                    </li>
                                    <li class="page-item disabled">
                                        <a class="page-link" href="#">Selanjutnya</a>
                                    </li>
                                </ul>
                            </nav>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <!-- [ Main Content ] end -->
      </div>
    </div>
    <!-- [ Main Content ] end -->

    <!-- Modal Tambah Antrian -->
    <div class="modal fade" id="tambahAntrianModal" tabindex="-1" aria-labelledby="tambahAntrianModalLabel" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="tambahAntrianModalLabel">
                        <i class="fas fa-plus-circle me-2"></i>Tambah Antrian Baru
                    </h5>
                </div>
                <form method="POST" action="dataantrian.php" id="tambahAntrianForm">
                    <input type="hidden" name="tambah_antrian" value="1">
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="nomor_antrian" class="form-label">Nomor Antrian <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="nomor_antrian" name="nomor_antrian" required 
                                           placeholder="Masukkan nomor antrian">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="id_pasien" class="form-label">Pasien <span class="text-danger">*</span></label>
                                    <select class="form-select" id="id_pasien" name="id_pasien" required>
                                        <option value="">Pilih Pasien</option>
                                        <?php foreach ($all_pasien as $pasien): ?>
                                            <option value="<?= htmlspecialchars($pasien['id_pasien']) ?>">
                                                <?= htmlspecialchars($pasien['nama_pasien']) ?> (<?= htmlspecialchars($pasien['id_pasien']) ?>)
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="kode_dokter" class="form-label">Dokter</label>
                                    <select class="form-select" id="kode_dokter" name="kode_dokter">
                                        <option value="">Pilih Dokter</option>
                                        <?php if (!empty($all_dokter)): ?>
                                            <?php foreach ($all_dokter as $dokter): ?>
                                                <option value="<?= htmlspecialchars($dokter['kode_dokter'] ?? '') ?>">
                                                    <?= htmlspecialchars($dokter['nama_dokter'] ?? '') ?> (<?= htmlspecialchars($dokter['kode_dokter'] ?? '') ?>)
                                                </option>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <option value="">Data dokter tidak tersedia</option>
                                        <?php endif; ?>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="status" class="form-label">Status</label>
                                    <select class="form-select" id="status" name="status">
                                        <option value="Menunggu" selected>Menunggu</option>
                                        <option value="Dipanggil">Dipanggil</option>
                                        <option value="Dilayani">Dilayani</option>
                                        <option value="Selesai">Selesai</option>
                                        <option value="Batal">Batal</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                            <i class="fas fa-times me-1"></i>Batal
                        </button>
                        <button type="submit" class="btn btn-primary" id="btnTambahAntrian">
                            <i class="fas fa-save me-1"></i>Simpan Antrian
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Edit Antrian -->
    <div class="modal fade" id="editAntrianModal" tabindex="-1" aria-labelledby="editAntrianModalLabel" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editAntrianModalLabel">
                        <i class="fas fa-edit me-2"></i>Edit Data Antrian
                    </h5>
                </div>
                <form method="POST" action="dataantrian.php" id="editAntrianForm">
                    <input type="hidden" name="edit_antrian" value="1">
                    <input type="hidden" id="edit_id_antrian" name="id_antrian">
                    
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="edit_nomor_antrian" class="form-label">Nomor Antrian <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="edit_nomor_antrian" name="nomor_antrian" required 
                                           placeholder="Masukkan nomor antrian">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="edit_id_pasien" class="form-label">Pasien <span class="text-danger">*</span></label>
                                    <select class="form-select" id="edit_id_pasien" name="id_pasien" required>
                                        <option value="">Pilih Pasien</option>
                                        <?php foreach ($all_pasien as $pasien): ?>
                                            <option value="<?= htmlspecialchars($pasien['id_pasien']) ?>">
                                                <?= htmlspecialchars($pasien['nama_pasien']) ?> (<?= htmlspecialchars($pasien['id_pasien']) ?>)
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="edit_kode_dokter" class="form-label">Dokter</label>
                                    <select class="form-select" id="edit_kode_dokter" name="kode_dokter">
                                        <option value="">Pilih Dokter</option>
                                        <?php if (!empty($all_dokter)): ?>
                                            <?php foreach ($all_dokter as $dokter): ?>
                                                <option value="<?= htmlspecialchars($dokter['kode_dokter'] ?? '') ?>">
                                                    <?= htmlspecialchars($dokter['nama_dokter'] ?? '') ?> (<?= htmlspecialchars($dokter['kode_dokter'] ?? '') ?>)
                                                </option>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <option value="">Data dokter tidak tersedia</option>
                                        <?php endif; ?>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="edit_status" class="form-label">Status</label>
                                    <select class="form-select" id="edit_status" name="status">
                                        <option value="Menunggu">Menunggu</option>
                                        <option value="Dipanggil">Dipanggil</option>
                                        <option value="Dilayani">Dilayani</option>
                                        <option value="Selesai">Selesai</option>
                                        <option value="Batal">Batal</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-12">
                                <div class="mb-3">
                                    <label for="edit_waktu_daftar" class="form-label">Waktu Daftar</label>
                                    <input type="text" class="form-control" id="edit_waktu_daftar" name="waktu_daftar" 
                                           readonly placeholder="Waktu daftar otomatis">
                                    <div class="form-text">Waktu daftar tidak dapat diubah.</div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                            <i class="fas fa-times me-1"></i>Batal
                        </button>
                        <button type="submit" class="btn btn-primary" id="btnUpdateAntrian">
                            <i class="fas fa-save me-1"></i>Update Antrian
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Hapus Antrian -->
    <div class="modal fade" id="hapusModal" tabindex="-1" aria-labelledby="hapusModalLabel" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="hapusModalLabel">
                        <i class="fas fa-exclamation-triangle text-danger me-2"></i>Konfirmasi Hapus
                    </h5>
                </div>
                <div class="modal-body">
                    <div class="text-center mb-3">
                        <i class="fas fa-trash-alt text-danger fa-3x mb-3"></i>
                    </div>
                    <p class="text-center">Apakah Anda yakin ingin menghapus antrian:</p>
                    <h5 class="text-center text-danger" id="nomorAntrianHapus"></h5>
                    <p class="text-center text-muted mt-3">
                        <small>Data yang dihapus tidak dapat dikembalikan.</small>
                    </p>
                </div>
                <div class="modal-footer justify-content-center">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times me-1"></i>Batal
                    </button>
                    <a href="#" id="hapusButton" class="btn btn-danger">
                        <i class="fas fa-trash me-1"></i>Ya, Hapus
                    </a>
                </div>
            </div>
        </div>
    </div>

    <footer class="pc-footer">
      <div class="footer-wrapper container-fluid">
        <div class="row">
          <div class="col my-1">
            <p class="m-0"
              >Copyright © 2025 Eyethica Klinik. All rights reserved.</p
            >
          </div>
          <div class="col-auto my-1">
            <ul class="list-inline footer-link mb-0">
              <li class="list-inline-item"><a href="../index.html">Home</a></li>
            </ul>
          </div>
        </div>
      </div>
    </footer> 
    
    <!-- [Page Specific JS] start -->
    <script src="../assets/js/plugins/apexcharts.min.js"></script>
    <script src="../assets/js/pages/dashboard-default.js"></script>
    <!-- [Page Specific JS] end -->
    <!-- Required Js -->
    <script src="../assets/js/plugins/popper.min.js"></script>
    <script src="../assets/js/plugins/simplebar.min.js"></script>
    <script src="../assets/js/plugins/bootstrap.min.js"></script>
    <script src="../assets/js/fonts/custom-font.js"></script>
    <script src="../assets/js/script.js"></script>
    <script src="../assets/js/theme.js"></script>
    <script src="../assets/js/plugins/feather.min.js"></script>
     <!-- Buy Now Link Script -->
    <script defer src="https://fomo.codedthemes.com/pixel/CDkpF1sQ8Tt5wpMZgqRvKpQiUhpWE3bc"></script>

    <script>
    // Function untuk mengubah jumlah entri per halaman
    function changeEntries() {
        const entries = document.getElementById('entriesPerPage').value;
        const search = '<?= $search_query ?>';
        const sort = '<?= $sort_order ?>';
        let url = 'dataantrian.php?entries=' + entries + '&page=1&sort=' + sort;
        
        if (search) {
            url += '&search=' + encodeURIComponent(search);
        }
        
        window.location.href = url;
    }

    // Function untuk clear search
    function clearSearch() {
        const entries = document.getElementById('entriesPerPage').value;
        const sort = '<?= $sort_order ?>';
        window.location.href = 'dataantrian.php?entries=' + entries + '&sort=' + sort;
    }

    // Function untuk menutup modal tambah antrian
    function closeTambahAntrianModal() {
        const modal = bootstrap.Modal.getInstance(document.getElementById('tambahAntrianModal'));
        modal.hide();
    }

    // Function untuk menutup modal edit antrian
    function closeEditAntrianModal() {
        const modal = bootstrap.Modal.getInstance(document.getElementById('editAntrianModal'));
        modal.hide();
    }

    // Function untuk menutup modal hapus antrian
    function closeHapusModal() {
        const modal = bootstrap.Modal.getInstance(document.getElementById('hapusModal'));
        modal.hide();
    }

    // Function untuk menampilkan modal hapus
    function showHapusModal(id, nomor) {
        document.getElementById('nomorAntrianHapus').textContent = 'Nomor ' + nomor;
        document.getElementById('hapusButton').href = 'dataantrian.php?hapus=' + id;
        
        // Tampilkan modal
        const hapusModal = new bootstrap.Modal(document.getElementById('hapusModal'));
        hapusModal.show();
    }

    // Function untuk menampilkan modal edit
    function showEditModal(id, nomor, pasien, dokter, status, waktu_daftar) {
        // Isi form dengan data yang ada
        document.getElementById('edit_id_antrian').value = id;
        document.getElementById('edit_nomor_antrian').value = nomor;
        document.getElementById('edit_id_pasien').value = pasien;
        document.getElementById('edit_kode_dokter').value = dokter;
        document.getElementById('edit_status').value = status;
        
        // Format dan tampilkan waktu daftar (read-only)
        if (waktu_daftar) {
            const date = new Date(waktu_daftar);
            const formattedDate = date.toLocaleString('id-ID', {
                day: '2-digit',
                month: '2-digit',
                year: 'numeric',
                hour: '2-digit',
                minute: '2-digit'
            });
            document.getElementById('edit_waktu_daftar').value = formattedDate;
        } else {
            document.getElementById('edit_waktu_daftar').value = '-';
        }
        
        // Tampilkan modal
        const editModal = new bootstrap.Modal(document.getElementById('editAntrianModal'));
        editModal.show();
    }

    // Function untuk handle submit form
    function handleFormSubmit(e, buttonId) {
        e.preventDefault();
        
        const submitButton = document.getElementById(buttonId);
        const originalText = submitButton.innerHTML;
        
        // Tampilkan loading
        submitButton.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status"></span>Memproses...';
        submitButton.disabled = true;
        
        // Submit form
        setTimeout(() => {
            e.target.submit();
        }, 500);
    }

    // Setup modal dengan event delegation
    document.addEventListener('DOMContentLoaded', function() {
        // Event delegation untuk tombol hapus
        document.addEventListener('click', function(e) {
            if (e.target.closest('.btn-hapus')) {
                e.preventDefault();
                const button = e.target.closest('.btn-hapus');
                const id = button.getAttribute('data-id');
                const nomor = button.getAttribute('data-nomor');
                showHapusModal(id, nomor);
            }
        });

        // Event delegation untuk tombol edit
        document.addEventListener('click', function(e) {
            if (e.target.closest('.btn-edit')) {
                e.preventDefault();
                const button = e.target.closest('.btn-edit');
                const id = button.getAttribute('data-id');
                const nomor = button.getAttribute('data-nomor');
                const pasien = button.getAttribute('data-pasien');
                const dokter = button.getAttribute('data-dokter');
                const status = button.getAttribute('data-status');
                const waktu_daftar = button.getAttribute('data-waktu_daftar');
                showEditModal(id, nomor, pasien, dokter, status, waktu_daftar);
            }
        });

        // Event listener untuk form tambah
        const tambahForm = document.getElementById('tambahAntrianForm');
        if (tambahForm) {
            tambahForm.addEventListener('submit', function(e) {
                handleFormSubmit(e, 'btnTambahAntrian');
            });
        }

        // Event listener untuk form edit
        const editForm = document.getElementById('editAntrianForm');
        if (editForm) {
            editForm.addEventListener('submit', function(e) {
                handleFormSubmit(e, 'btnUpdateAntrian');
            });
        }

        // Auto focus pada input search
        const searchInput = document.querySelector('input[name="search"]');
        if (searchInput && '<?= $search_query ?>') {
            searchInput.focus();
            searchInput.setSelectionRange(searchInput.value.length, searchInput.value.length);
        }

        // Reset form modal ketika ditutup
        const tambahAntrianModal = document.getElementById('tambahAntrianModal');
        if (tambahAntrianModal) {
            tambahAntrianModal.addEventListener('hidden.bs.modal', function () {
                document.getElementById('tambahAntrianForm').reset();
                const submitButton = document.getElementById('btnTambahAntrian');
                submitButton.innerHTML = '<i class="fas fa-save me-1"></i>Simpan Antrian';
                submitButton.disabled = false;
            });
        }

        const editAntrianModal = document.getElementById('editAntrianModal');
        if (editAntrianModal) {
            editAntrianModal.addEventListener('hidden.bs.modal', function () {
                const submitButton = document.getElementById('btnUpdateAntrian');
                submitButton.innerHTML = '<i class="fas fa-save me-1"></i>Update Antrian';
                submitButton.disabled = false;
            });
        }

        // Event listener untuk tombol close manual
        document.querySelector('#tambahAntrianModal .btn-close')?.addEventListener('click', closeTambahAntrianModal);
        document.querySelector('#tambahAntrianModal .btn-secondary')?.addEventListener('click', closeTambahAntrianModal);
        
        document.querySelector('#editAntrianModal .btn-close')?.addEventListener('click', closeEditAntrianModal);
        document.querySelector('#editAntrianModal .btn-secondary')?.addEventListener('click', closeEditAntrianModal);
        
        document.querySelector('#hapusModal .btn-close')?.addEventListener('click', closeHapusModal);
        document.querySelector('#hapusModal .btn-secondary')?.addEventListener('click', closeHapusModal);
    });
    </script>
    
    <script>change_box_container('false');</script>
    <script>layout_caption_change('true');</script>
    <script>layout_rtl_change('false');</script>
    <script>preset_change("preset-1");</script>
    
  </body>
  <!-- [Body] end -->
</html>

<?php
// Fungsi untuk membuat URL pagination
function getPaginationUrl($page, $entries, $search = '', $sort = 'asc') {
    $url = 'dataantrian.php?';
    $params = [];
    
    if ($page > 1) {
        $params[] = 'page=' . $page;
    }
    
    if ($entries != 10) {
        $params[] = 'entries=' . $entries;
    }
    
    if (!empty($search)) {
        $params[] = 'search=' . urlencode($search);
    }
    
    if ($sort != 'asc') {
        $params[] = 'sort=' . $sort;
    }
    
    return $url . implode('&', $params);
}

// Fungsi untuk membuat URL sorting
function getSortUrl($current_sort) {
    $url = 'dataantrian.php?';
    $params = [];
    
    $entries = isset($_GET['entries']) ? (int)$_GET['entries'] : 10;
    $search = isset($_GET['search']) ? trim($_GET['search']) : '';
    
    if ($entries != 10) {
        $params[] = 'entries=' . $entries;
    }
    
    if (!empty($search)) {
        $params[] = 'search=' . urlencode($search);
    }
    
    // Toggle sort order
    $new_sort = $current_sort === 'asc' ? 'desc' : 'asc';
    $params[] = 'sort=' . $new_sort;
    
    return $url . implode('&', $params);
}