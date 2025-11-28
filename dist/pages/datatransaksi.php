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

// PROSES HAPUS TRANSAKSI
if (isset($_GET['hapus'])) {
    $id_transaksi = $_GET['hapus'];
    $transaksi_data = $db->get_transaksi_by_id($id_transaksi);

    if ($db->hapus_data_transaksi($id_transaksi)) {
        $username = $_SESSION['username'] ?? 'unknown user';
        $entitas = 'Hapus';
        $jenis = 'Transaksi';
        $deskripsi = "Transaksi ID '{$transaksi_data['id_transaksi']}' berhasil dihapus oleh $username.";
        $waktu = date('Y-m-d H:i:s');
        $db->tambah_aktivitas($entitas, $jenis, $deskripsi, $waktu);

        $_SESSION['notif_status'] = 'success';
        $_SESSION['notif_message'] = 'Data transaksi berhasil dihapus.';
    } else {
        $_SESSION['notif_status'] = 'error';
        $_SESSION['notif_message'] = 'Gagal menghapus data transaksi.';
    }
    header("Location: datatransaksi.php");
    exit();
}

// PROSES EDIT TRANSAKSI
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_transaksi'])) {
    $id_transaksi = $_POST['id_transaksi'] ?? '';
    $id_rekam = $_POST['id_rekam'] ?? '';
    $id_kontrol = $_POST['id_kontrol'] ?? '';
    $id_pasien = $_POST['id_pasien'] ?? '';
    $kode_staff = $_POST['kode_staff'] ?? '';
    $tanggal_transaksi = $_POST['tanggal_transaksi'] ?? '';
    $metode_pembayaran = $_POST['metode_pembayaran'] ?? '';
    $total_biaya = $_POST['total_biaya'] ?? '';
    $status_pembayaran = $_POST['status_pembayaran'] ?? 'Belum bayar';
    
    // Validasi data
    if (empty($id_transaksi) || empty($id_pasien) || empty($total_biaya)) {
        $_SESSION['notif_status'] = 'error';
        $_SESSION['notif_message'] = 'Field pasien dan total biaya wajib diisi!';
        header("Location: datatransaksi.php");
        exit();
    }
    
    // Update data transaksi
    if ($db->update_data_transaksi($id_transaksi, $id_rekam, $id_kontrol, $id_pasien, $kode_staff, $tanggal_transaksi, $metode_pembayaran, $total_biaya, $status_pembayaran)) {
        // Log aktivitas
        $username_session = $_SESSION['username'] ?? 'unknown user';
        $entitas = 'Edit';
        $jenis = 'Transaksi';
        $deskripsi = "Transaksi ID '$id_transaksi' berhasil diupdate oleh $username_session.";
        $waktu = date('Y-m-d H:i:s');
        $db->tambah_aktivitas($entitas, $jenis, $deskripsi, $waktu);
        
        $_SESSION['notif_status'] = 'success';
        $_SESSION['notif_message'] = 'Data transaksi berhasil diupdate.';
    } else {
        $_SESSION['notif_status'] = 'error';
        $_SESSION['notif_message'] = 'Gagal mengupdate data transaksi.';
    }
    
    header("Location: datatransaksi.php");
    exit();
}

// PROSES TAMBAH TRANSAKSI
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['tambah_transaksi'])) {
    $id_rekam = $_POST['id_rekam'] ?? '';
    $id_kontrol = $_POST['id_kontrol'] ?? '';
    $id_pasien = $_POST['id_pasien'] ?? '';
    $kode_staff = $_POST['kode_staff'] ?? '';
    $tanggal_transaksi = $_POST['tanggal_transaksi'] ?? date('Y-m-d');
    $metode_pembayaran = $_POST['metode_pembayaran'] ?? '';
    $total_biaya = $_POST['total_biaya'] ?? '';
    $status_pembayaran = $_POST['status_pembayaran'] ?? 'Belum bayar';
    
    // Validasi data
    if (empty($id_pasien) || empty($total_biaya)) {
        $_SESSION['notif_status'] = 'error';
        $_SESSION['notif_message'] = 'Field pasien dan total biaya wajib diisi!';
        header("Location: datatransaksi.php");
        exit();
    }
    
    // Tambah data transaksi
    if ($db->tambah_data_transaksi($id_rekam, $id_kontrol, $id_pasien, $kode_staff, $tanggal_transaksi, $metode_pembayaran, $total_biaya, $status_pembayaran)) {
        // Log aktivitas
        $username_session = $_SESSION['username'] ?? 'unknown user';
        $entitas = 'Tambah';
        $jenis = 'Transaksi';
        $deskripsi = "Transaksi baru berhasil ditambahkan oleh $username_session.";
        $waktu = date('Y-m-d H:i:s');
        $db->tambah_aktivitas($entitas, $jenis, $deskripsi, $waktu);
        
        $_SESSION['notif_status'] = 'success';
        $_SESSION['notif_message'] = 'Data transaksi berhasil ditambahkan.';
    } else {
        $_SESSION['notif_status'] = 'error';
        $_SESSION['notif_message'] = 'Gagal menambahkan data transaksi.';
    }
    
    header("Location: datatransaksi.php");
    exit();
}

// Konfigurasi pagination, search, dan sorting
$entries_per_page = isset($_GET['entries']) ? (int)$_GET['entries'] : 10;
$current_page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$search_query = isset($_GET['search']) ? trim($_GET['search']) : '';
$sort_order = isset($_GET['sort']) && $_GET['sort'] === 'desc' ? 'desc' : 'asc';

// Ambil semua data transaksi
$all_transaksi = $db->tampil_data_transaksi();

// Ambil data pasien, staff, rekam medis, dan kontrol untuk dropdown
$all_pasien = $db->tampil_data_pasien();
$all_staff = $db->tampil_data_staff();
$all_rekam_medis = $db->tampil_data_rekam_medis();
$all_kontrol = $db->tampil_data_kontrol();

// Filter data berdasarkan search query
if (!empty($search_query)) {
    $filtered_transaksi = [];
    foreach ($all_transaksi as $transaksi) {
        // Cari di semua kolom yang relevan
        if (stripos($transaksi['id_transaksi'] ?? '', $search_query) !== false ||
            stripos($transaksi['id_rekam'] ?? '', $search_query) !== false ||
            stripos($transaksi['id_kontrol'] ?? '', $search_query) !== false ||
            stripos($transaksi['id_pasien'] ?? '', $search_query) !== false ||
            stripos($transaksi['kode_staff'] ?? '', $search_query) !== false ||
            stripos($transaksi['tanggal_transaksi'] ?? '', $search_query) !== false ||
            stripos($transaksi['metode_pembayaran'] ?? '', $search_query) !== false ||
            stripos($transaksi['total_biaya'] ?? '', $search_query) !== false ||
            stripos($transaksi['status_pembayaran'] ?? '', $search_query) !== false) {
            $filtered_transaksi[] = $transaksi;
        }
    }
    $all_transaksi = $filtered_transaksi;
}

// Urutkan data berdasarkan ID Transaksi
if ($sort_order === 'desc') {
    // Urutkan dari ID terbesar ke terkecil (terakhir ke terawal)
    usort($all_transaksi, function($a, $b) {
        return ($b['id_transaksi'] ?? 0) - ($a['id_transaksi'] ?? 0);
    });
} else {
    // Urutkan dari ID terkecil ke terbesar (terawal ke terakhir) - default
    usort($all_transaksi, function($a, $b) {
        return ($a['id_transaksi'] ?? 0) - ($b['id_transaksi'] ?? 0);
    });
}

// Hitung total data
$total_entries = count($all_transaksi);

// Hitung total halaman
$total_pages = ceil($total_entries / $entries_per_page);

// Pastikan current page valid
if ($current_page < 1) $current_page = 1;
if ($current_page > $total_pages && $total_pages > 0) $current_page = $total_pages;

// Hitung offset
$offset = ($current_page - 1) * $entries_per_page;

// Ambil data untuk halaman saat ini
$data_transaksi = array_slice($all_transaksi, $offset, $entries_per_page);

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

// Cek apakah data transaksi kosong untuk memicu modal
$is_data_empty = empty($data_transaksi);
?>

<!DOCTYPE html>
<html lang="en">
  <!-- [Head] start -->

  <head>
    <title>Data Transaksi - EyeThica Klinik</title>
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
    cursor: pointer;
}

.btn-hapus:hover {
    transform: scale(1.05);
    box-shadow: 0 4px 8px rgba(220, 53, 69, 0.3);
}

.btn-edit:hover {
    transform: scale(1.05);
    box-shadow: 0 4px 8px rgba(255, 193, 7, 0.3);
}

/* Pastikan tombol group dapat diklik */
.btn-group .btn {
    pointer-events: auto;
    position: relative;
    z-index: 1;
}

/* Loading spinner */
.spinner-border-sm {
    width: 1rem;
    height: 1rem;
}

/* Badge status pembayaran */
.badge-lunas {
    background-color: #28a745;
    color: #fff;
}

.badge-belum-dibayar {
    background-color: #ffc107;
    color: #000;
}

/* Badge metode pembayaran */
.badge-tunai {
    background-color: #17a2b8;
    color: #fff;
}

.badge-transfer {
    background-color: #6610f2;
    color: #fff;
}

.badge-qris {
    background-color: #e83e8c;
    color: #fff;
}

.badge-debit {
    background-color: #fd7e14;
    color: #fff;
}

/* Styling untuk total biaya */
.total-biaya {
    font-weight: bold;
    font-size: 1.1em;
    color: #28a745;
}

/* Styling untuk table */
.table th {
    border-top: none;
    font-weight: 600;
}

/* Responsive table */
@media (max-width: 768px) {
    .table-responsive {
        font-size: 0.875rem;
    }
    
    .btn-group .btn {
        padding: 0.25rem 0.5rem;
        font-size: 0.75rem;
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
                  <li class="breadcrumb-item" aria-current="page">Data Transaksi</li>
                </ul>
              </div>
              <div class="col-md-12">
                <div class="page-header-title">
                  <h2 class="mb-0">Data Transaksi</h2>
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
                <!-- Tombol Tambah Transaksi dengan Modal -->
                <button type="button" class="btn btn-dark me-2" data-bs-toggle="modal" data-bs-target="#tambahTransaksiModal">
                    <i class="fas fa-plus me-1"></i> Tambah Transaksi
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
                                           placeholder="Cari data transaksi..." 
                                           value="<?= htmlspecialchars($search_query) ?>"
                                           aria-label="Search">
                                    <input type="hidden" name="entries" value="<?= $entries_per_page ?>">
                                    <input type="hidden" name="sort" value="<?= $sort_order ?>">
                                    <button class="btn btn-outline-secondary" type="submit">
                                        <i class="fas fa-search"></i>
                                    </button>
                                    <?php if (!empty($search_query)): ?>
                                    <a href="datatransaksi.php?entries=<?= $entries_per_page ?>&sort=<?= $sort_order ?>" class="btn btn-outline-danger" type="button">
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
                        <table id="transaksiTable" class="table table-hover">
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
                                    <th>ID Kontrol</th>
                                    <th>ID Pasien</th>
                                    <th>Kode Staff</th>
                                    <th>Tanggal Transaksi</th>
                                    <th>Metode Bayar</th>
                                    <th>Total Biaya</th>
                                    <th>Status</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                if (!empty($data_transaksi) && is_array($data_transaksi)) {
                                    foreach ($data_transaksi as $transaksi) {
                                        $id_transaksi = htmlspecialchars($transaksi['id_transaksi'] ?? '', ENT_QUOTES, 'UTF-8');
                                        $id_rekam = htmlspecialchars($transaksi['id_rekam'] ?? '', ENT_QUOTES, 'UTF-8');
                                        $id_kontrol = htmlspecialchars($transaksi['id_kontrol'] ?? '', ENT_QUOTES, 'UTF-8');
                                        $id_pasien = htmlspecialchars($transaksi['id_pasien'] ?? '', ENT_QUOTES, 'UTF-8');
                                        $kode_staff = htmlspecialchars($transaksi['kode_staff'] ?? '', ENT_QUOTES, 'UTF-8');
                                        $tanggal_transaksi = htmlspecialchars($transaksi['tanggal_transaksi'] ?? '', ENT_QUOTES, 'UTF-8');
                                        $metode_pembayaran = htmlspecialchars($transaksi['metode_pembayaran'] ?? '', ENT_QUOTES, 'UTF-8');
                                        $total_biaya = htmlspecialchars($transaksi['total_biaya'] ?? '', ENT_QUOTES, 'UTF-8');
                                        $status_pembayaran = htmlspecialchars($transaksi['status_pembayaran'] ?? '', ENT_QUOTES, 'UTF-8');
                                        
                                        // Format tanggal transaksi
                                        $tanggal_transaksi_formatted = !empty($tanggal_transaksi) ? date('d-m-Y', strtotime($tanggal_transaksi)) : '-';
                                        
                                        // Format total biaya
                                        $total_biaya_formatted = !empty($total_biaya) ? 'Rp ' . number_format($total_biaya, 0, ',', '.') : '-';
                                        
                                        // Tentukan class badge berdasarkan status pembayaran
                                        $status_badge_class = 'badge-secondary';
                                        switch ($status_pembayaran) {
                                            case 'Lunas':
                                                $status_badge_class = 'badge-lunas';
                                                break;
                                            case 'Belum bayar':
                                                $status_badge_class = 'badge-belum-dibayar';
                                                break;
                                        }
                                        
                                        // Tentukan class badge berdasarkan metode pembayaran
                                        $metode_badge_class = 'badge-secondary';
                                        switch ($metode_pembayaran) {
                                            case 'Tunai':
                                                $metode_badge_class = 'badge-tunai';
                                                break;
                                            case 'Transfer':
                                                $metode_badge_class = 'badge-transfer';
                                                break;
                                            case 'QRIS':
                                                $metode_badge_class = 'badge-qris';
                                                break;
                                            case 'Debit':
                                                $metode_badge_class = 'badge-debit';
                                                break;
                                        }
                                ?>
                                    <tr>
                                        <td><?= $start_number ?></td>
                                        <td><?= $id_transaksi ?></td>
                                        <td><?= $id_rekam ?: '-' ?></td>
                                        <td><?= $id_kontrol ?: '-' ?></td>
                                        <td><?= $id_pasien ?></td>
                                        <td><?= $kode_staff ?: '-' ?></td>
                                        <td><?= $tanggal_transaksi_formatted ?></td>
                                        <td>
                                            <span class="badge <?= $metode_badge_class ?>"><?= $metode_pembayaran ?: '-' ?></span>
                                        </td>
                                        <td class="total-biaya"><?= $total_biaya_formatted ?></td>
                                        <td>
                                            <span class="badge <?= $status_badge_class ?>"><?= $status_pembayaran ?></span>
                                        </td>
                                        <td>
                                            <div class="btn-group" role="group">
                                                <button type="button"
                                                        class="btn btn-warning btn-sm btn-edit"
                                                        data-id="<?= $id_transaksi ?>"
                                                        data-rekam="<?= $id_rekam ?>"
                                                        data-kontrol="<?= $id_kontrol ?>"
                                                        data-pasien="<?= $id_pasien ?>"
                                                        data-staff="<?= $kode_staff ?>"
                                                        data-tanggal_transaksi="<?= $tanggal_transaksi ?>"
                                                        data-metode_pembayaran="<?= $metode_pembayaran ?>"
                                                        data-total_biaya="<?= $total_biaya ?>"
                                                        data-status_pembayaran="<?= $status_pembayaran ?>"
                                                        title="Edit Transaksi">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                                <button type="button"
                                                        class="btn btn-danger btn-sm btn-hapus"
                                                        data-id="<?= $id_transaksi ?>"
                                                        title="Hapus Transaksi">
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
                                    echo '<tr><td colspan="11" class="text-center text-muted">';
                                    if (!empty($search_query)) {
                                        echo 'Tidak ada data transaksi yang sesuai dengan pencarian "' . htmlspecialchars($search_query) . '"';
                                    } else {
                                        echo 'Tidak ada data transaksi ditemukan.';
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

    <!-- Modal Tambah Transaksi -->
    <div class="modal fade" id="tambahTransaksiModal" tabindex="-1" aria-labelledby="tambahTransaksiModalLabel" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="tambahTransaksiModalLabel">
                        <i class="fas fa-receipt me-2"></i>Tambah Transaksi Baru
                    </h5>
                </div>
                <form method="POST" action="datatransaksi.php" id="tambahTransaksiForm">
                    <input type="hidden" name="tambah_transaksi" value="1">
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
                                    <label for="id_kontrol" class="form-label">ID Kontrol</label>
                                    <select class="form-select" id="id_kontrol" name="id_kontrol">
                                        <option value="">Pilih Kontrol</option>
                                        <?php foreach ($all_kontrol as $kontrol): ?>
                                            <option value="<?= htmlspecialchars($kontrol['id_kontrol']) ?>">
                                                K-<?= htmlspecialchars($kontrol['id_kontrol']) ?> (<?= htmlspecialchars($kontrol['id_pasien']) ?>)
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="row">
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
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="kode_staff" class="form-label">Staff</label>
                                    <select class="form-select" id="kode_staff" name="kode_staff">
                                        <option value="">Pilih Staff</option>
                                        <?php foreach ($all_staff as $staff): ?>
                                            <option value="<?= htmlspecialchars($staff['kode_staff'] ?? '') ?>">
                                                <?= htmlspecialchars($staff['nama_staff'] ?? '') ?> (<?= htmlspecialchars($staff['kode_staff'] ?? '') ?>)
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="tanggal_transaksi" class="form-label">Tanggal Transaksi</label>
                                    <input type="date" class="form-control" id="tanggal_transaksi" name="tanggal_transaksi" 
                                           value="<?= date('Y-m-d') ?>">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="total_biaya" class="form-label">Total Biaya (Rp) <span class="text-danger">*</span></label>
                                    <input type="number" class="form-control" id="total_biaya" name="total_biaya" 
                                           placeholder="Masukkan total biaya" min="0" step="1000" required>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="metode_pembayaran" class="form-label">Metode Pembayaran</label>
                                    <select class="form-select" id="metode_pembayaran" name="metode_pembayaran">
                                        <option value="">Pilih Metode</option>
                                        <option value="Tunai">Tunai</option>
                                        <option value="Transfer">Transfer Bank</option>
                                        <option value="QRIS">QRIS</option>
                                        <option value="Debit">Kartu Debit</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="status_pembayaran" class="form-label">Status Pembayaran</label>
                                    <select class="form-select" id="status_pembayaran" name="status_pembayaran">
                                        <option value="Belum bayar" selected>Belum bayar</option>
                                        <option value="Lunas">Lunas</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                            <i class="fas fa-times me-1"></i>Batal
                        </button>
                        <button type="submit" class="btn btn-primary" id="btnTambahTransaksi">
                            <i class="fas fa-save me-1"></i>Simpan Transaksi
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Edit Transaksi -->
    <div class="modal fade" id="editTransaksiModal" tabindex="-1" aria-labelledby="editTransaksiModalLabel" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editTransaksiModalLabel">
                        <i class="fas fa-edit me-2"></i>Edit Data Transaksi
                    </h5>
                </div>
                <form method="POST" action="datatransaksi.php" id="editTransaksiForm">
                    <input type="hidden" name="edit_transaksi" value="1">
                    <input type="hidden" id="edit_id_transaksi" name="id_transaksi">
                    
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
                                    <label for="edit_id_kontrol" class="form-label">ID Kontrol</label>
                                    <select class="form-select" id="edit_id_kontrol" name="id_kontrol">
                                        <option value="">Pilih Kontrol</option>
                                        <?php foreach ($all_kontrol as $kontrol): ?>
                                            <option value="<?= htmlspecialchars($kontrol['id_kontrol']) ?>">
                                                K-<?= htmlspecialchars($kontrol['id_kontrol']) ?> (<?= htmlspecialchars($kontrol['id_pasien']) ?>)
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="row">
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
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="edit_kode_staff" class="form-label">Staff</label>
                                    <select class="form-select" id="edit_kode_staff" name="kode_staff">
                                        <option value="">Pilih Staff</option>
                                        <?php foreach ($all_staff as $staff): ?>
                                            <option value="<?= htmlspecialchars($staff['kode_staff'] ?? '') ?>">
                                                <?= htmlspecialchars($staff['nama_staff'] ?? '') ?> (<?= htmlspecialchars($staff['kode_staff'] ?? '') ?>)
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="edit_tanggal_transaksi" class="form-label">Tanggal Transaksi</label>
                                    <input type="date" class="form-control" id="edit_tanggal_transaksi" name="tanggal_transaksi">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="edit_total_biaya" class="form-label">Total Biaya (Rp) <span class="text-danger">*</span></label>
                                    <input type="number" class="form-control" id="edit_total_biaya" name="total_biaya" 
                                           placeholder="Masukkan total biaya" min="0" step="1000" required>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="edit_metode_pembayaran" class="form-label">Metode Pembayaran</label>
                                    <select class="form-select" id="edit_metode_pembayaran" name="metode_pembayaran">
                                        <option value="">Pilih Metode</option>
                                        <option value="Tunai">Tunai</option>
                                        <option value="Transfer">Transfer Bank</option>
                                        <option value="QRIS">QRIS</option>
                                        <option value="Debit">Kartu Debit</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="edit_status_pembayaran" class="form-label">Status Pembayaran</label>
                                    <select class="form-select" id="edit_status_pembayaran" name="status_pembayaran">
                                        <option value="Belum bayar">Belum bayar</option>
                                        <option value="Lunas">Lunas</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                            <i class="fas fa-times me-1"></i>Batal
                        </button>
                        <button type="submit" class="btn btn-primary" id="btnUpdateTransaksi">
                            <i class="fas fa-save me-1"></i>Update Transaksi
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Hapus Transaksi -->
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
                    <p class="text-center">Apakah Anda yakin ingin menghapus transaksi:</p>
                    <h5 class="text-center text-danger" id="idTransaksiHapus"></h5>
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
        let url = 'datatransaksi.php?entries=' + entries + '&page=1&sort=' + sort;
        
        if (search) {
            url += '&search=' + encodeURIComponent(search);
        }
        
        window.location.href = url;
    }

    // Function untuk clear search
    function clearSearch() {
        const entries = document.getElementById('entriesPerPage').value;
        const sort = '<?= $sort_order ?>';
        window.location.href = 'datatransaksi.php?entries=' + entries + '&sort=' + sort;
    }

    // Function untuk menampilkan modal hapus
    function showHapusModal(id) {
        document.getElementById('idTransaksiHapus').textContent = 'ID ' + id;
        document.getElementById('hapusButton').href = 'datatransaksi.php?hapus=' + id;
        
        // Tampilkan modal dengan membuat instance baru
        const hapusModal = new bootstrap.Modal(document.getElementById('hapusModal'));
        hapusModal.show();
    }

    // Function untuk menampilkan modal edit
    function showEditModal(id, rekam, kontrol, pasien, staff, tanggal_transaksi, metode_pembayaran, total_biaya, status_pembayaran) {
        console.log('Data yang diterima:', {id, rekam, kontrol, pasien, staff, tanggal_transaksi, metode_pembayaran, total_biaya, status_pembayaran});
        
        // Isi form dengan data yang ada
        document.getElementById('edit_id_transaksi').value = id;
        document.getElementById('edit_id_rekam').value = rekam || '';
        document.getElementById('edit_id_kontrol').value = kontrol || '';
        document.getElementById('edit_id_pasien').value = pasien || '';
        document.getElementById('edit_kode_staff').value = staff || '';
        document.getElementById('edit_tanggal_transaksi').value = tanggal_transaksi || '';
        document.getElementById('edit_metode_pembayaran').value = metode_pembayaran || '';
        document.getElementById('edit_total_biaya').value = total_biaya || '';
        document.getElementById('edit_status_pembayaran').value = status_pembayaran || 'Belum bayar';
        
        // Tampilkan modal
        const editModal = new bootstrap.Modal(document.getElementById('editTransaksiModal'));
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

    // Setup modal dengan event delegation yang lebih robust
    document.addEventListener('DOMContentLoaded', function() {
        console.log('DOM loaded, setting up event listeners...');

        // Event delegation untuk tombol hapus
        document.addEventListener('click', function(e) {
            if (e.target.closest('.btn-hapus')) {
                e.preventDefault();
                const button = e.target.closest('.btn-hapus');
                const id = button.getAttribute('data-id');
                console.log('Tombol hapus diklik:', id);
                showHapusModal(id);
            }
        });

        // Event delegation untuk tombol edit - VERSI PERBAIKAN
        document.addEventListener('click', function(e) {
            const editButton = e.target.closest('.btn-edit');
            if (editButton) {
                e.preventDefault();
                e.stopPropagation();
                
                console.log('Tombol edit diklik');
                
                // Ambil semua data atribut
                const id = editButton.getAttribute('data-id');
                const rekam = editButton.getAttribute('data-rekam');
                const kontrol = editButton.getAttribute('data-kontrol');
                const pasien = editButton.getAttribute('data-pasien');
                const staff = editButton.getAttribute('data-staff');
                const tanggal_transaksi = editButton.getAttribute('data-tanggal_transaksi');
                const metode_pembayaran = editButton.getAttribute('data-metode_pembayaran');
                const total_biaya = editButton.getAttribute('data-total_biaya');
                const status_pembayaran = editButton.getAttribute('data-status_pembayaran');
                
                console.log('Data atribut:', {
                    id, rekam, kontrol, pasien, staff, 
                    tanggal_transaksi, metode_pembayaran, total_biaya, status_pembayaran
                });
                
                showEditModal(id, rekam, kontrol, pasien, staff, tanggal_transaksi, metode_pembayaran, total_biaya, status_pembayaran);
            }
        });

        // Event listener untuk form tambah
        const tambahForm = document.getElementById('tambahTransaksiForm');
        if (tambahForm) {
            tambahForm.addEventListener('submit', function(e) {
                handleFormSubmit(e, 'btnTambahTransaksi');
            });
        }

        // Event listener untuk form edit
        const editForm = document.getElementById('editTransaksiForm');
        if (editForm) {
            editForm.addEventListener('submit', function(e) {
                handleFormSubmit(e, 'btnUpdateTransaksi');
            });
        }

        // Debug: Cek apakah tombol edit ada di DOM
        const editButtons = document.querySelectorAll('.btn-edit');
        console.log('Jumlah tombol edit ditemukan:', editButtons.length);
        
        editButtons.forEach((btn, index) => {
            console.log(`Tombol edit ${index + 1}:`, btn);
            console.log(`Data ID: ${btn.getAttribute('data-id')}`);
        });

        // Auto focus pada input search
        const searchInput = document.querySelector('input[name="search"]');
        if (searchInput && '<?= $search_query ?>') {
            searchInput.focus();
            searchInput.setSelectionRange(searchInput.value.length, searchInput.value.length);
        }

        // Reset form modal ketika ditutup
        const tambahTransaksiModal = document.getElementById('tambahTransaksiModal');
        if (tambahTransaksiModal) {
            tambahTransaksiModal.addEventListener('hidden.bs.modal', function () {
                document.getElementById('tambahTransaksiForm').reset();
                document.getElementById('tanggal_transaksi').value = '<?= date('Y-m-d') ?>';
                const submitButton = document.getElementById('btnTambahTransaksi');
                if (submitButton) {
                    submitButton.innerHTML = '<i class="fas fa-save me-1"></i>Simpan Transaksi';
                    submitButton.disabled = false;
                }
            });
        }

        const editTransaksiModal = document.getElementById('editTransaksiModal');
        if (editTransaksiModal) {
            editTransaksiModal.addEventListener('hidden.bs.modal', function () {
                const submitButton = document.getElementById('btnUpdateTransaksi');
                if (submitButton) {
                    submitButton.innerHTML = '<i class="fas fa-save me-1"></i>Update Transaksi';
                    submitButton.disabled = false;
                }
            });
        }
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
    $url = 'datatransaksi.php?';
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
    $url = 'datatransaksi.php?';
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