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

// PROSES HAPUS KONTROL
if (isset($_GET['hapus'])) {
    $id_kontrol = $_GET['hapus'];
    $kontrol_data = $db->get_kontrol_by_id($id_kontrol);

    if ($db->hapus_data_kontrol($id_kontrol)) {
        $username = $_SESSION['username'] ?? 'unknown user';
        $entitas = 'Hapus';
        $jenis = 'Kontrol';
        $deskripsi = "Kontrol ID '{$kontrol_data['id_kontrol']}' berhasil dihapus oleh $username.";
        $waktu = date('Y-m-d H:i:s');
        $db->tambah_aktivitas($entitas, $jenis, $deskripsi, $waktu);

        $_SESSION['notif_status'] = 'success';
        $_SESSION['notif_message'] = 'Data kontrol berhasil dihapus.';
    } else {
        $_SESSION['notif_status'] = 'error';
        $_SESSION['notif_message'] = 'Gagal menghapus data kontrol.';
    }
    header("Location: datakontrol.php");
    exit();
}

// PROSES EDIT KONTROL
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_kontrol'])) {
    $id_kontrol = $_POST['id_kontrol'] ?? '';
    $id_rekam = $_POST['id_rekam'] ?? '';
    $id_pasien = $_POST['id_pasien'] ?? '';
    $kode_dokter = $_POST['kode_dokter'] ?? '';
    $tanggal_kontrol = $_POST['tanggal_kontrol'] ?? '';
    $keluhan = $_POST['keluhan'] ?? '';
    $catatan = $_POST['catatan'] ?? '';
    $biaya = $_POST['biaya'] ?? '';
    $status_kontrol = $_POST['status_kontrol'] ?? 'Terjadwal';
    
    // Validasi data
    if (empty($id_kontrol) || empty($id_pasien) || empty($kode_dokter)) {
        $_SESSION['notif_status'] = 'error';
        $_SESSION['notif_message'] = 'Field pasien dan dokter wajib diisi!';
        header("Location: datakontrol.php");
        exit();
    }
    
    // Update data kontrol
    if ($db->update_data_kontrol($id_kontrol, $id_rekam, $id_pasien, $kode_dokter, $tanggal_kontrol, $keluhan, $catatan, $biaya, $status_kontrol)) {
        // Log aktivitas
        $username_session = $_SESSION['username'] ?? 'unknown user';
        $entitas = 'Edit';
        $jenis = 'Kontrol';
        $deskripsi = "Kontrol ID '$id_kontrol' berhasil diupdate oleh $username_session.";
        $waktu = date('Y-m-d H:i:s');
        $db->tambah_aktivitas($entitas, $jenis, $deskripsi, $waktu);
        
        $_SESSION['notif_status'] = 'success';
        $_SESSION['notif_message'] = 'Data kontrol berhasil diupdate.';
    } else {
        $_SESSION['notif_status'] = 'error';
        $_SESSION['notif_message'] = 'Gagal mengupdate data kontrol.';
    }
    
    header("Location: datakontrol.php");
    exit();
}

// PROSES TAMBAH KONTROL
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['tambah_kontrol'])) {
    $id_rekam = $_POST['id_rekam'] ?? '';
    $id_pasien = $_POST['id_pasien'] ?? '';
    $kode_dokter = $_POST['kode_dokter'] ?? '';
    $tanggal_kontrol = $_POST['tanggal_kontrol'] ?? date('Y-m-d');
    $keluhan = $_POST['keluhan'] ?? '';
    $catatan = $_POST['catatan'] ?? '';
    $biaya = $_POST['biaya'] ?? '';
    $status_kontrol = $_POST['status_kontrol'] ?? 'Terjadwal';
    
    // Validasi data
    if (empty($id_pasien) || empty($kode_dokter)) {
        $_SESSION['notif_status'] = 'error';
        $_SESSION['notif_message'] = 'Field pasien dan dokter wajib diisi!';
        header("Location: datakontrol.php");
        exit();
    }
    
    // Tambah data kontrol
    if ($db->tambah_data_kontrol($id_rekam, $id_pasien, $kode_dokter, $tanggal_kontrol, $keluhan, $catatan, $biaya, $status_kontrol)) {
        // Log aktivitas
        $username_session = $_SESSION['username'] ?? 'unknown user';
        $entitas = 'Tambah';
        $jenis = 'Kontrol';
        $deskripsi = "Kontrol baru berhasil ditambahkan oleh $username_session.";
        $waktu = date('Y-m-d H:i:s');
        $db->tambah_aktivitas($entitas, $jenis, $deskripsi, $waktu);
        
        $_SESSION['notif_status'] = 'success';
        $_SESSION['notif_message'] = 'Data kontrol berhasil ditambahkan.';
    } else {
        $_SESSION['notif_status'] = 'error';
        $_SESSION['notif_message'] = 'Gagal menambahkan data kontrol.';
    }
    
    header("Location: datakontrol.php");
    exit();
}

// Konfigurasi pagination, search, dan sorting
$entries_per_page = isset($_GET['entries']) ? (int)$_GET['entries'] : 10;
$current_page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$search_query = isset($_GET['search']) ? trim($_GET['search']) : '';
$sort_order = isset($_GET['sort']) && $_GET['sort'] === 'desc' ? 'desc' : 'asc';

// Ambil semua data kontrol
$all_kontrol = $db->tampil_data_kontrol();

// Ambil data pasien, dokter, dan rekam medis untuk dropdown
$all_pasien = $db->tampil_data_pasien();
$all_dokter = $db->tampil_data_dokter();
$all_rekam_medis = $db->tampil_data_rekam_medis();

// Filter data berdasarkan search query
if (!empty($search_query)) {
    $filtered_kontrol = [];
    foreach ($all_kontrol as $kontrol) {
        // Cari di semua kolom yang relevan
        if (stripos($kontrol['id_kontrol'] ?? '', $search_query) !== false ||
            stripos($kontrol['id_rekam'] ?? '', $search_query) !== false ||
            stripos($kontrol['id_pasien'] ?? '', $search_query) !== false ||
            stripos($kontrol['kode_dokter'] ?? '', $search_query) !== false ||
            stripos($kontrol['tanggal_kontrol'] ?? '', $search_query) !== false ||
            stripos($kontrol['keluhan'] ?? '', $search_query) !== false ||
            stripos($kontrol['catatan'] ?? '', $search_query) !== false ||
            stripos($kontrol['biaya'] ?? '', $search_query) !== false ||
            stripos($kontrol['status_kontrol'] ?? '', $search_query) !== false ||
            stripos($kontrol['create_at'] ?? '', $search_query) !== false) {
            $filtered_kontrol[] = $kontrol;
        }
    }
    $all_kontrol = $filtered_kontrol;
}

// Urutkan data berdasarkan ID Kontrol
if ($sort_order === 'desc') {
    // Urutkan dari ID terbesar ke terkecil (terakhir ke terawal)
    usort($all_kontrol, function($a, $b) {
        return ($b['id_kontrol'] ?? 0) - ($a['id_kontrol'] ?? 0);
    });
} else {
    // Urutkan dari ID terkecil ke terbesar (terawal ke terakhir) - default
    usort($all_kontrol, function($a, $b) {
        return ($a['id_kontrol'] ?? 0) - ($b['id_kontrol'] ?? 0);
    });
}

// Hitung total data
$total_entries = count($all_kontrol);

// Hitung total halaman
$total_pages = ceil($total_entries / $entries_per_page);

// Pastikan current page valid
if ($current_page < 1) $current_page = 1;
if ($current_page > $total_pages && $total_pages > 0) $current_page = $total_pages;

// Hitung offset
$offset = ($current_page - 1) * $entries_per_page;

// Ambil data untuk halaman saat ini
$data_kontrol = array_slice($all_kontrol, $offset, $entries_per_page);

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

// Cek apakah data kontrol kosong untuk memicu modal
$is_data_empty = empty($data_kontrol);
?>

<!DOCTYPE html>
<html lang="en">
  <!-- [Head] start -->

  <head>
    <title>Data Kontrol - EyeThica Klinik</title>
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

/* Badge status kontrol */
.badge-terjadwal {
    background-color: #17a2b8;
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

.badge-tunda {
    background-color: #ffc107;
    color: #000;
}

/* Styling untuk text truncated */
.text-truncate-2 {
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

/* Styling untuk biaya */
.biaya {
    font-weight: bold;
    color: #28a745;
}

/* Styling untuk table */
.table th {
    border-top: none;
    font-weight: 600;
}

/* Styling untuk create_at */
.create-at {
    font-size: 0.875rem;
    color: #6c757d;
}

/* Responsive table */
@media (max-width: 768px) {
    .table-responsive {
        font-size: 0.875rem;
    }
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
                  <li class="breadcrumb-item" aria-current="page">Data Kontrol</li>
                </ul>
              </div>
              <div class="col-md-12">
                <div class="page-header-title">
                  <h2 class="mb-0">Data Kontrol</h2>
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
                <!-- Tombol Tambah Kontrol dengan Modal -->
                <button type="button" class="btn btn-dark me-2" data-bs-toggle="modal" data-bs-target="#tambahKontrolModal">
                    <i class="fas fa-plus me-1"></i> Tambah Kontrol
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
                                           placeholder="Cari data kontrol..." 
                                           value="<?= htmlspecialchars($search_query) ?>"
                                           aria-label="Search">
                                    <input type="hidden" name="entries" value="<?= $entries_per_page ?>">
                                    <input type="hidden" name="sort" value="<?= $sort_order ?>">
                                    <button class="btn btn-outline-secondary" type="submit">
                                        <i class="fas fa-search"></i>
                                    </button>
                                    <?php if (!empty($search_query)): ?>
                                    <a href="datakontrol.php?entries=<?= $entries_per_page ?>&sort=<?= $sort_order ?>" class="btn btn-outline-danger" type="button">
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
                        <table id="kontrolTable" class="table table-hover">
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
                                    <th>ID Rekam</th>
                                    <th>ID Pasien</th>
                                    <th>Kode Dokter</th>
                                    <th>Tanggal Kontrol</th>
                                    <th>Keluhan</th>
                                    <th>Catatan</th>
                                    <th>Biaya</th>
                                    <th>Status</th>
                                    <th>Dibuat</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                if (!empty($data_kontrol) && is_array($data_kontrol)) {
                                    foreach ($data_kontrol as $kontrol) {
                                        $id_kontrol = htmlspecialchars($kontrol['id_kontrol'] ?? '', ENT_QUOTES, 'UTF-8');
                                        $id_rekam = htmlspecialchars($kontrol['id_rekam'] ?? '', ENT_QUOTES, 'UTF-8');
                                        $id_pasien = htmlspecialchars($kontrol['id_pasien'] ?? '', ENT_QUOTES, 'UTF-8');
                                        $kode_dokter = htmlspecialchars($kontrol['kode_dokter'] ?? '', ENT_QUOTES, 'UTF-8');
                                        $tanggal_kontrol = htmlspecialchars($kontrol['tanggal_kontrol'] ?? '', ENT_QUOTES, 'UTF-8');
                                        $keluhan = htmlspecialchars($kontrol['keluhan'] ?? '', ENT_QUOTES, 'UTF-8');
                                        $catatan = htmlspecialchars($kontrol['catatan'] ?? '', ENT_QUOTES, 'UTF-8');
                                        $biaya = htmlspecialchars($kontrol['biaya'] ?? '', ENT_QUOTES, 'UTF-8');
                                        $status_kontrol = htmlspecialchars($kontrol['status_kontrol'] ?? '', ENT_QUOTES, 'UTF-8');
                                        $create_at = htmlspecialchars($kontrol['create_at'] ?? '', ENT_QUOTES, 'UTF-8');
                                        
                                        // Format tanggal
                                        $tanggal_kontrol_formatted = !empty($tanggal_kontrol) ? date('d-m-Y', strtotime($tanggal_kontrol)) : '-';
                                        $create_at_formatted = !empty($create_at) ? date('d-m-Y H:i', strtotime($create_at)) : '-';
                                        
                                        // Format biaya
                                        $biaya_formatted = !empty($biaya) ? 'Rp ' . number_format($biaya, 0, ',', '.') : '-';
                                        
                                        // Tentukan class badge berdasarkan status kontrol
                                        $badge_class = 'badge-secondary';
                                        switch ($status_kontrol) {
                                            case 'Terjadwal':
                                                $badge_class = 'badge-terjadwal';
                                                break;
                                            case 'Selesai':
                                                $badge_class = 'badge-selesai';
                                                break;
                                            case 'Batal':
                                                $badge_class = 'badge-batal';
                                                break;
                                            case 'Tunda':
                                                $badge_class = 'badge-tunda';
                                                break;
                                        }
                                ?>
                                    <tr>
                                        <td><?= $start_number ?></td>
                                        <td><?= $id_kontrol ?></td>
                                        <td><?= $id_rekam ?></td>
                                        <td><?= $id_pasien ?></td>
                                        <td><?= $kode_dokter ?></td>
                                        <td><?= $tanggal_kontrol_formatted ?></td>
                                        <td>
                                            <div class="text-truncate-2" title="<?= $keluhan ?>">
                                                <?= $keluhan ?>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="text-truncate-2" title="<?= $catatan ?>">
                                                <?= $catatan ?>
                                            </div>
                                        </td>
                                        <td class="biaya"><?= $biaya_formatted ?></td>
                                        <td>
                                            <span class="badge <?= $badge_class ?>"><?= $status_kontrol ?></span>
                                        </td>
                                        <td class="create-at"><?= $create_at_formatted ?></td>
                                        <td>
                                            <div class="btn-group" role="group">
                                                <button type="button"
                                                        class="btn btn-warning btn-sm btn-edit"
                                                        data-id="<?= $id_kontrol ?>"
                                                        data-rekam="<?= $id_rekam ?>"
                                                        data-pasien="<?= $id_pasien ?>"
                                                        data-dokter="<?= $kode_dokter ?>"
                                                        data-tanggal_kontrol="<?= $tanggal_kontrol ?>"
                                                        data-keluhan="<?= $keluhan ?>"
                                                        data-catatan="<?= $catatan ?>"
                                                        data-biaya="<?= $biaya ?>"
                                                        data-status_kontrol="<?= $status_kontrol ?>"
                                                        title="Edit Kontrol">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                                <button type="button"
                                                        class="btn btn-danger btn-sm btn-hapus"
                                                        data-id="<?= $id_kontrol ?>"
                                                        title="Hapus Kontrol">
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
                                    echo '<tr><td colspan="12" class="text-center text-muted">';
                                    if (!empty($search_query)) {
                                        echo 'Tidak ada data kontrol yang sesuai dengan pencarian "' . htmlspecialchars($search_query) . '"';
                                    } else {
                                        echo 'Tidak ada data kontrol ditemukan.';
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

    <!-- Modal Tambah Kontrol -->
    <div class="modal fade" id="tambahKontrolModal" tabindex="-1" aria-labelledby="tambahKontrolModalLabel" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="tambahKontrolModalLabel">
                        <i class="fas fa-calendar-plus me-2"></i>Tambah Kontrol Baru
                    </h5>
                </div>
                <form method="POST" action="datakontrol.php" id="tambahKontrolForm">
                    <input type="hidden" name="tambah_kontrol" value="1">
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="id_rekam" class="form-label">ID Rekam Medis</label>
                                    <select class="form-select" id="id_rekam" name="id_rekam">
                                        <option value="">Pilih Rekam Medis</option>
                                        <?php foreach ($all_rekam_medis as $rekam): ?>
                                            <option value="<?= htmlspecialchars($rekam['id_rekam']) ?>">
                                                RM-<?= htmlspecialchars($rekam['id_rekam']) ?> (<?= htmlspecialchars($rekam['id_pasien']) ?>)
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
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
                                    <label for="kode_dokter" class="form-label">Dokter <span class="text-danger">*</span></label>
                                    <select class="form-select" id="kode_dokter" name="kode_dokter" required>
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
                                    <label for="tanggal_kontrol" class="form-label">Tanggal Kontrol</label>
                                    <input type="date" class="form-control" id="tanggal_kontrol" name="tanggal_kontrol" 
                                           value="<?= date('Y-m-d') ?>">
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="biaya" class="form-label">Biaya (Rp)</label>
                                    <input type="number" class="form-control" id="biaya" name="biaya" 
                                           placeholder="Masukkan biaya" min="0" step="1000">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="status_kontrol" class="form-label">Status Kontrol</label>
                                    <select class="form-select" id="status_kontrol" name="status_kontrol">
                                        <option value="Terjadwal" selected>Terjadwal</option>
                                        <option value="Selesai">Selesai</option>
                                        <option value="Batal">Batal</option>
                                        <option value="Tunda">Tunda</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-12">
                                <div class="mb-3">
                                    <label for="keluhan" class="form-label">Keluhan</label>
                                    <textarea class="form-control" id="keluhan" name="keluhan" 
                                              placeholder="Masukkan keluhan pasien" rows="3"></textarea>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-12">
                                <div class="mb-3">
                                    <label for="catatan" class="form-label">Catatan Tambahan</label>
                                    <textarea class="form-control" id="catatan" name="catatan" 
                                              placeholder="Masukkan catatan tambahan" rows="3"></textarea>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                            <i class="fas fa-times me-1"></i>Batal
                        </button>
                        <button type="submit" class="btn btn-primary" id="btnTambahKontrol">
                            <i class="fas fa-save me-1"></i>Simpan Kontrol
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Edit Kontrol -->
    <div class="modal fade" id="editKontrolModal" tabindex="-1" aria-labelledby="editKontrolModalLabel" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editKontrolModalLabel">
                        <i class="fas fa-edit me-2"></i>Edit Data Kontrol
                    </h5>
                </div>
                <form method="POST" action="datakontrol.php" id="editKontrolForm">
                    <input type="hidden" name="edit_kontrol" value="1">
                    <input type="hidden" id="edit_id_kontrol" name="id_kontrol">
                    
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="edit_id_rekam" class="form-label">ID Rekam Medis</label>
                                    <select class="form-select" id="edit_id_rekam" name="id_rekam">
                                        <option value="">Pilih Rekam Medis</option>
                                        <?php foreach ($all_rekam_medis as $rekam): ?>
                                            <option value="<?= htmlspecialchars($rekam['id_rekam']) ?>">
                                                RM-<?= htmlspecialchars($rekam['id_rekam']) ?> (<?= htmlspecialchars($rekam['id_pasien']) ?>)
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
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
                                    <label for="edit_kode_dokter" class="form-label">Dokter <span class="text-danger">*</span></label>
                                    <select class="form-select" id="edit_kode_dokter" name="kode_dokter" required>
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
                                    <label for="edit_tanggal_kontrol" class="form-label">Tanggal Kontrol</label>
                                    <input type="date" class="form-control" id="edit_tanggal_kontrol" name="tanggal_kontrol">
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="edit_biaya" class="form-label">Biaya (Rp)</label>
                                    <input type="number" class="form-control" id="edit_biaya" name="biaya" 
                                           placeholder="Masukkan biaya" min="0" step="1000">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="edit_status_kontrol" class="form-label">Status Kontrol</label>
                                    <select class="form-select" id="edit_status_kontrol" name="status_kontrol">
                                        <option value="Terjadwal">Terjadwal</option>
                                        <option value="Selesai">Selesai</option>
                                        <option value="Batal">Batal</option>
                                        <option value="Tunda">Tunda</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-12">
                                <div class="mb-3">
                                    <label for="edit_keluhan" class="form-label">Keluhan</label>
                                    <textarea class="form-control" id="edit_keluhan" name="keluhan" 
                                              placeholder="Masukkan keluhan pasien" rows="3"></textarea>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-12">
                                <div class="mb-3">
                                    <label for="edit_catatan" class="form-label">Catatan Tambahan</label>
                                    <textarea class="form-control" id="edit_catatan" name="catatan" 
                                              placeholder="Masukkan catatan tambahan" rows="3"></textarea>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                            <i class="fas fa-times me-1"></i>Batal
                        </button>
                        <button type="submit" class="btn btn-primary" id="btnUpdateKontrol">
                            <i class="fas fa-save me-1"></i>Update Kontrol
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Hapus Kontrol -->
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
                    <p class="text-center">Apakah Anda yakin ingin menghapus kontrol:</p>
                    <h5 class="text-center text-danger" id="idKontrolHapus"></h5>
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
        let url = 'datakontrol.php?entries=' + entries + '&page=1&sort=' + sort;
        
        if (search) {
            url += '&search=' + encodeURIComponent(search);
        }
        
        window.location.href = url;
    }

    // Function untuk clear search
    function clearSearch() {
        const entries = document.getElementById('entriesPerPage').value;
        const sort = '<?= $sort_order ?>';
        window.location.href = 'datakontrol.php?entries=' + entries + '&sort=' + sort;
    }

    // Function untuk menutup modal tambah kontrol
    function closeTambahKontrolModal() {
        const modal = bootstrap.Modal.getInstance(document.getElementById('tambahKontrolModal'));
        modal.hide();
    }

    // Function untuk menutup modal edit kontrol
    function closeEditKontrolModal() {
        const modal = bootstrap.Modal.getInstance(document.getElementById('editKontrolModal'));
        modal.hide();
    }

    // Function untuk menutup modal hapus kontrol
    function closeHapusModal() {
        const modal = bootstrap.Modal.getInstance(document.getElementById('hapusModal'));
        modal.hide();
    }

    // Function untuk menampilkan modal hapus
    function showHapusModal(id) {
        document.getElementById('idKontrolHapus').textContent = 'ID ' + id;
        document.getElementById('hapusButton').href = 'datakontrol.php?hapus=' + id;
        
        // Tampilkan modal dengan membuat instance baru
        const hapusModal = new bootstrap.Modal(document.getElementById('hapusModal'));
        hapusModal.show();
    }

    // Function untuk menampilkan modal edit
    function showEditModal(id, rekam, pasien, dokter, tanggal_kontrol, keluhan, catatan, biaya, status_kontrol) {
        // Isi form dengan data yang ada
        document.getElementById('edit_id_kontrol').value = id;
        document.getElementById('edit_id_rekam').value = rekam;
        document.getElementById('edit_id_pasien').value = pasien;
        document.getElementById('edit_kode_dokter').value = dokter;
        document.getElementById('edit_tanggal_kontrol').value = tanggal_kontrol;
        document.getElementById('edit_keluhan').value = keluhan;
        document.getElementById('edit_catatan').value = catatan;
        document.getElementById('edit_biaya').value = biaya;
        document.getElementById('edit_status_kontrol').value = status_kontrol;
        
        // Tampilkan modal dengan membuat instance baru
        const editModal = new bootstrap.Modal(document.getElementById('editKontrolModal'));
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
                showHapusModal(id);
            }
        });

        // Event delegation untuk tombol edit
        document.addEventListener('click', function(e) {
            if (e.target.closest('.btn-edit')) {
                e.preventDefault();
                const button = e.target.closest('.btn-edit');
                const id = button.getAttribute('data-id');
                const rekam = button.getAttribute('data-rekam');
                const pasien = button.getAttribute('data-pasien');
                const dokter = button.getAttribute('data-dokter');
                const tanggal_kontrol = button.getAttribute('data-tanggal_kontrol');
                const keluhan = button.getAttribute('data-keluhan');
                const catatan = button.getAttribute('data-catatan');
                const biaya = button.getAttribute('data-biaya');
                const status_kontrol = button.getAttribute('data-status_kontrol');
                showEditModal(id, rekam, pasien, dokter, tanggal_kontrol, keluhan, catatan, biaya, status_kontrol);
            }
        });

        // Event listener untuk form tambah
        const tambahForm = document.getElementById('tambahKontrolForm');
        if (tambahForm) {
            tambahForm.addEventListener('submit', function(e) {
                handleFormSubmit(e, 'btnTambahKontrol');
            });
        }

        // Event listener untuk form edit
        const editForm = document.getElementById('editKontrolForm');
        if (editForm) {
            editForm.addEventListener('submit', function(e) {
                handleFormSubmit(e, 'btnUpdateKontrol');
            });
        }

        // Auto focus pada input search
        const searchInput = document.querySelector('input[name="search"]');
        if (searchInput && '<?= $search_query ?>') {
            searchInput.focus();
            searchInput.setSelectionRange(searchInput.value.length, searchInput.value.length);
        }

        // Reset form modal ketika ditutup
        const tambahKontrolModal = document.getElementById('tambahKontrolModal');
        if (tambahKontrolModal) {
            tambahKontrolModal.addEventListener('hidden.bs.modal', function () {
                document.getElementById('tambahKontrolForm').reset();
                document.getElementById('tanggal_kontrol').value = '<?= date('Y-m-d') ?>';
                const submitButton = document.getElementById('btnTambahKontrol');
                submitButton.innerHTML = '<i class="fas fa-save me-1"></i>Simpan Kontrol';
                submitButton.disabled = false;
            });
        }

        const editKontrolModal = document.getElementById('editKontrolModal');
        if (editKontrolModal) {
            editKontrolModal.addEventListener('hidden.bs.modal', function () {
                const submitButton = document.getElementById('btnUpdateKontrol');
                submitButton.innerHTML = '<i class="fas fa-save me-1"></i>Update Kontrol';
                submitButton.disabled = false;
            });
        }

        // Event listener untuk tombol close manual
        document.querySelectorAll('#tambahKontrolModal .btn-close, #tambahKontrolModal .btn-secondary').forEach(btn => {
            btn.addEventListener('click', closeTambahKontrolModal);
        });
        
        document.querySelectorAll('#editKontrolModal .btn-close, #editKontrolModal .btn-secondary').forEach(btn => {
            btn.addEventListener('click', closeEditKontrolModal);
        });
        
        document.querySelectorAll('#hapusModal .btn-close, #hapusModal .btn-secondary').forEach(btn => {
            btn.addEventListener('click', closeHapusModal);
        });
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
    $url = 'datakontrol.php?';
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
    $url = 'datakontrol.php?';
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
?>