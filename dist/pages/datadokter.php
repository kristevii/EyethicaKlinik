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

// PROSES HAPUS DOKTER
if (isset($_GET['hapus'])) {
    $id_user = $_GET['hapus'];
    $dokter_data = $db->get_dokter_by_id($id_user);
    $foto_to_delete = $dokter_data['foto_dokter'] ?? null;

    if ($db->hapus_data_dokter($id_user)) {
        if ($foto_to_delete && file_exists('imagedokter/' . $foto_to_delete)) {
            unlink('imagedokter/' . $foto_to_delete);
        }

        $username = $_SESSION['username'] ?? 'unknown user';
        $entitas = 'Hapus';
        $jenis = 'Dokter';
        $deskripsi = "Dokter '{$dokter_data['nama_dokter']}' berhasil dihapus oleh $username.";
        $waktu = date('Y-m-d H:i:s');
        $db->tambah_aktivitas($entitas, $jenis, $deskripsi, $waktu);

        $_SESSION['notif_status'] = 'success';
        $_SESSION['notif_message'] = 'Data dokter berhasil dihapus.';
    } else {
        $_SESSION['notif_status'] = 'error';
        $_SESSION['notif_message'] = 'Gagal menghapus data dokter.';
    }
    header("Location: datadokter.php");
    exit();
}

// PROSES EDIT DOKTER
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_dokter'])) {
    $id_user = $_POST['id_user'] ?? '';
    $kode_dokter = $_POST['kode_dokter'] ?? '';
    $spesialisasi_dokter = $_POST['spesialisasi_dokter'] ?? '';
    $nama_dokter = $_POST['nama_dokter'] ?? '';
    $tanggal_lahir_dokter = $_POST['tanggal_lahir_dokter'] ?? '';
    $jenis_kelamin_dokter = $_POST['jenis_kelamin_dokter'] ?? '';
    $alamat_dokter = $_POST['alamat_dokter'] ?? '';
    $email_dokter = $_POST['email_dokter'] ?? '';
    $telepon_dokter = $_POST['telepon_dokter'] ?? '';
    $ruang = $_POST['ruang'] ?? '';
    
    // Validasi data
    if (empty($id_user) || empty($kode_dokter) || empty($nama_dokter)) {
        $_SESSION['notif_status'] = 'error';
        $_SESSION['notif_message'] = 'Field kode dokter dan nama dokter wajib diisi!';
        header("Location: datadokter.php");
        exit();
    }
    
    // Handle upload foto
    $foto_dokter = null;
    if (isset($_FILES['foto_dokter']) && $_FILES['foto_dokter']['error'] === UPLOAD_ERR_OK) {
        $foto_name = $_FILES['foto_dokter']['name'];
        $foto_tmp = $_FILES['foto_dokter']['tmp_name'];
        $foto_size = $_FILES['foto_dokter']['size'];
        $foto_ext = strtolower(pathinfo($foto_name, PATHINFO_EXTENSION));
        
        $allowed_ext = ['jpg', 'jpeg', 'png', 'gif'];
        if (in_array($foto_ext, $allowed_ext)) {
            $new_foto_name = uniqid('dokter_', true) . '.' . $foto_ext;
            $upload_path = 'imagedokter/' . $new_foto_name;
            
            if (move_uploaded_file($foto_tmp, $upload_path)) {
                $foto_dokter = $new_foto_name;
                
                // Hapus foto lama jika ada
                $old_data = $db->get_dokter_by_id($id_user);
                if ($old_data && $old_data['foto_dokter'] && file_exists('imagedokter/' . $old_data['foto_dokter'])) {
                    unlink('imagedokter/' . $old_data['foto_dokter']);
                }
            }
        }
    }
    
    // Update data dokter
    if ($db->update_data_dokter($id_user, $kode_dokter, $spesialisasi_dokter, $foto_dokter, $nama_dokter, $tanggal_lahir_dokter, $jenis_kelamin_dokter, $alamat_dokter, $email_dokter, $telepon_dokter, $ruang)) {
        // Log aktivitas
        $username_session = $_SESSION['username'] ?? 'unknown user';
        $entitas = 'Edit';
        $jenis = 'Dokter';
        $deskripsi = "Dokter '$nama_dokter' berhasil diupdate oleh $username_session.";
        $waktu = date('Y-m-d H:i:s');
        $db->tambah_aktivitas($entitas, $jenis, $deskripsi, $waktu);
        
        $_SESSION['notif_status'] = 'success';
        $_SESSION['notif_message'] = 'Data dokter berhasil diupdate.';
    } else {
        $_SESSION['notif_status'] = 'error';
        $_SESSION['notif_message'] = 'Gagal mengupdate data dokter.';
    }
    
    header("Location: datadokter.php");
    exit();
}

// PROSES TAMBAH DOKTER
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['tambah_dokter'])) {
    $kode_dokter = $_POST['kode_dokter'] ?? '';
    $spesialisasi_dokter = $_POST['spesialisasi_dokter'] ?? '';
    $nama_dokter = $_POST['nama_dokter'] ?? '';
    $tanggal_lahir_dokter = $_POST['tanggal_lahir_dokter'] ?? '';
    $jenis_kelamin_dokter = $_POST['jenis_kelamin_dokter'] ?? '';
    $alamat_dokter = $_POST['alamat_dokter'] ?? '';
    $email_dokter = $_POST['email_dokter'] ?? '';
    $telepon_dokter = $_POST['telepon_dokter'] ?? '';
    $ruang = $_POST['ruang'] ?? '';
    
    // Validasi data
    if (empty($kode_dokter) || empty($nama_dokter)) {
        $_SESSION['notif_status'] = 'error';
        $_SESSION['notif_message'] = 'Field kode dokter dan nama dokter wajib diisi!';
        header("Location: datadokter.php");
        exit();
    }
    
    // Handle upload foto
    $foto_dokter = null;
    if (isset($_FILES['foto_dokter']) && $_FILES['foto_dokter']['error'] === UPLOAD_ERR_OK) {
        $foto_name = $_FILES['foto_dokter']['name'];
        $foto_tmp = $_FILES['foto_dokter']['tmp_name'];
        $foto_size = $_FILES['foto_dokter']['size'];
        $foto_ext = strtolower(pathinfo($foto_name, PATHINFO_EXTENSION));
        
        $allowed_ext = ['jpg', 'jpeg', 'png', 'gif'];
        if (in_array($foto_ext, $allowed_ext)) {
            $new_foto_name = uniqid('dokter_', true) . '.' . $foto_ext;
            $upload_path = 'imagedokter/' . $new_foto_name;
            
            if (move_uploaded_file($foto_tmp, $upload_path)) {
                $foto_dokter = $new_foto_name;
            }
        }
    }
    
    // Tambah data dokter
    if ($db->tambah_data_dokter($kode_dokter, $spesialisasi_dokter, $foto_dokter, $nama_dokter, $tanggal_lahir_dokter, $jenis_kelamin_dokter, $alamat_dokter, $email_dokter, $telepon_dokter, $ruang)) {
        // Log aktivitas
        $username_session = $_SESSION['username'] ?? 'unknown user';
        $entitas = 'Tambah';
        $jenis = 'Dokter';
        $deskripsi = "Dokter '$nama_dokter' berhasil ditambahkan oleh $username_session.";
        $waktu = date('Y-m-d H:i:s');
        $db->tambah_aktivitas($entitas, $jenis, $deskripsi, $waktu);
        
        $_SESSION['notif_status'] = 'success';
        $_SESSION['notif_message'] = 'Data dokter berhasil ditambahkan.';
    } else {
        $_SESSION['notif_status'] = 'error';
        $_SESSION['notif_message'] = 'Gagal menambahkan data dokter.';
    }
    
    header("Location: datadokter.php");
    exit();
}

// Konfigurasi pagination, search, dan sorting
$entries_per_page = isset($_GET['entries']) ? (int)$_GET['entries'] : 10;
$current_page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$search_query = isset($_GET['search']) ? trim($_GET['search']) : '';
$sort_order = isset($_GET['sort']) && $_GET['sort'] === 'desc' ? 'desc' : 'asc';

// Ambil semua data dokter
$all_dokter = $db->tampil_data_dokter();

// Filter data berdasarkan search query
if (!empty($search_query)) {
    $filtered_dokter = [];
    foreach ($all_dokter as $dokter) {
        // Cari di semua kolom yang relevan
        if (stripos($dokter['id_user'] ?? '', $search_query) !== false ||
            stripos($dokter['kode_dokter'] ?? '', $search_query) !== false ||
            stripos($dokter['spesialisasi_dokter'] ?? '', $search_query) !== false ||
            stripos($dokter['nama_dokter'] ?? '', $search_query) !== false ||
            stripos($dokter['tanggal_lahir_dokter'] ?? '', $search_query) !== false ||
            stripos($dokter['jenis_kelamin_dokter'] ?? '', $search_query) !== false ||
            stripos($dokter['alamat_dokter'] ?? '', $search_query) !== false ||
            stripos($dokter['email_dokter'] ?? '', $search_query) !== false ||
            stripos($dokter['telepon_dokter'] ?? '', $search_query) !== false ||
            stripos($dokter['ruang'] ?? '', $search_query) !== false) {
            $filtered_dokter[] = $dokter;
        }
    }
    $all_dokter = $filtered_dokter;
}

// Urutkan data berdasarkan ID User
if ($sort_order === 'desc') {
    // Urutkan dari ID terbesar ke terkecil (terakhir ke terawal)
    usort($all_dokter, function($a, $b) {
        return ($b['id_user'] ?? 0) - ($a['id_user'] ?? 0);
    });
} else {
    // Urutkan dari ID terkecil ke terbesar (terawal ke terakhir) - default
    usort($all_dokter, function($a, $b) {
        return ($a['id_user'] ?? 0) - ($b['id_user'] ?? 0);
    });
}

// Hitung total data
$total_entries = count($all_dokter);

// Hitung total halaman
$total_pages = ceil($total_entries / $entries_per_page);

// Pastikan current page valid
if ($current_page < 1) $current_page = 1;
if ($current_page > $total_pages && $total_pages > 0) $current_page = $total_pages;

// Hitung offset
$offset = ($current_page - 1) * $entries_per_page;

// Ambil data untuk halaman saat ini
$data_dokter = array_slice($all_dokter, $offset, $entries_per_page);

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

// Cek apakah data dokter kosong untuk memicu modal
$is_data_empty = empty($data_dokter);
?>

<!DOCTYPE html>
<html lang="en">
  <!-- [Head] start -->

  <head>
    <title>Data Dokter - EyeThica Klinik</title>
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

/* Form text untuk foto */
.form-text {
    font-size: 0.875rem;
    color: #6c757d;
}

/* Preview foto */
.foto-preview {
    max-width: 150px;
    max-height: 150px;
    border-radius: 8px;
    border: 2px solid #dee2e6;
    margin-top: 10px;
}

/* Styling untuk foto di tabel */
.foto-dokter {
    width: 50px;
    height: 50px;
    object-fit: cover;
    border-radius: 50%;
    border: 2px solid #dee2e6;
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
                  <li class="breadcrumb-item" aria-current="page">Data Dokter</li>
                </ul>
              </div>
              <div class="col-md-12">
                <div class="page-header-title">
                  <h2 class="mb-0">Data Dokter</h2>
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
                <!-- Tombol Tambah Dokter dengan Modal -->
                <button type="button" class="btn btn-dark me-2" data-bs-toggle="modal" data-bs-target="#tambahDokterModal">
                    <i class="fas fa-plus me-1"></i> Tambah Dokter
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
                                           placeholder="Cari data dokter..." 
                                           value="<?= htmlspecialchars($search_query) ?>"
                                           aria-label="Search">
                                    <input type="hidden" name="entries" value="<?= $entries_per_page ?>">
                                    <input type="hidden" name="sort" value="<?= $sort_order ?>">
                                    <button class="btn btn-outline-secondary" type="submit">
                                        <i class="fas fa-search"></i>
                                    </button>
                                    <?php if (!empty($search_query)): ?>
                                    <a href="datadokter.php?entries=<?= $entries_per_page ?>&sort=<?= $sort_order ?>" class="btn btn-outline-danger" type="button">
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
                        <table id="dokterTable" class="table table-hover">
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
                                    <th>Foto</th>
                                    <th>Kode Dokter</th>
                                    <th>Nama Dokter</th>
                                    <th>Spesialisasi</th>
                                    <th>Tanggal Lahir</th>
                                    <th>Jenis Kelamin</th>
                                    <th>Email</th>
                                    <th>Telepon</th>
                                    <th>Ruang</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                if (!empty($data_dokter) && is_array($data_dokter)) {
                                    foreach ($data_dokter as $dokter) {
                                        $id_user = htmlspecialchars($dokter['id_user'] ?? '', ENT_QUOTES, 'UTF-8');
                                        $kode_dokter = htmlspecialchars($dokter['kode_dokter'] ?? '', ENT_QUOTES, 'UTF-8');
                                        $spesialisasi_dokter = htmlspecialchars($dokter['spesialisasi_dokter'] ?? '', ENT_QUOTES, 'UTF-8');
                                        $foto_dokter = htmlspecialchars($dokter['foto_dokter'] ?? '', ENT_QUOTES, 'UTF-8');
                                        $nama_dokter = htmlspecialchars($dokter['nama_dokter'] ?? '', ENT_QUOTES, 'UTF-8');
                                        $tanggal_lahir_dokter = htmlspecialchars($dokter['tanggal_lahir_dokter'] ?? '', ENT_QUOTES, 'UTF-8');
                                        $jenis_kelamin_dokter = htmlspecialchars($dokter['jenis_kelamin_dokter'] ?? '', ENT_QUOTES, 'UTF-8');
                                        $alamat_dokter = htmlspecialchars($dokter['alamat_dokter'] ?? '', ENT_QUOTES, 'UTF-8');
                                        $email_dokter = htmlspecialchars($dokter['email_dokter'] ?? '', ENT_QUOTES, 'UTF-8');
                                        $telepon_dokter = htmlspecialchars($dokter['telepon_dokter'] ?? '', ENT_QUOTES, 'UTF-8');
                                        $ruang = htmlspecialchars($dokter['ruang'] ?? '', ENT_QUOTES, 'UTF-8');
                                        
                                        // Format tanggal lahir
                                        $tanggal_lahir_formatted = !empty($tanggal_lahir_dokter) ? date('d-m-Y', strtotime($tanggal_lahir_dokter)) : '-';
                                ?>
                                    <tr>
                                        <td><?= $start_number ?></td>
                                        <td><?= $id_user ?></td>
                                        <td>
                                            <?php if (!empty($foto_dokter)): ?>
                                                <img src="imagedokter/<?= $foto_dokter ?>" alt="Foto Dokter" class="foto-dokter">
                                            <?php else: ?>
                                                <div class="foto-dokter bg-light d-flex align-items-center justify-content-center">
                                                    <i class="fas fa-user-md text-muted"></i>
                                                </div>
                                            <?php endif; ?>
                                        </td>
                                        <td><?= $kode_dokter ?></td>
                                        <td><?= $nama_dokter ?></td>
                                        <td><?= $spesialisasi_dokter ?></td>
                                        <td><?= $tanggal_lahir_formatted ?></td>
                                        <td><?= $jenis_kelamin_dokter ?></td>
                                        <td><?= $email_dokter ?></td>
                                        <td><?= $telepon_dokter ?></td>
                                        <td><?= $ruang ?></td>
                                        <td>
                                            <div class="btn-group" role="group">
                                                <button type="button"
                                                        class="btn btn-warning btn-sm btn-edit"
                                                        data-id="<?= $id_user ?>"
                                                        data-kode="<?= $kode_dokter ?>"
                                                        data-spesialisasi="<?= $spesialisasi_dokter ?>"
                                                        data-foto="<?= $foto_dokter ?>"
                                                        data-nama="<?= $nama_dokter ?>"
                                                        data-tanggal_lahir="<?= $tanggal_lahir_dokter ?>"
                                                        data-jenis_kelamin="<?= $jenis_kelamin_dokter ?>"
                                                        data-alamat="<?= $alamat_dokter ?>"
                                                        data-email="<?= $email_dokter ?>"
                                                        data-telepon="<?= $telepon_dokter ?>"
                                                        data-ruang="<?= $ruang ?>"
                                                        title="Edit Dokter">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                                <button type="button"
                                                        class="btn btn-danger btn-sm btn-hapus"
                                                        data-id="<?= $id_user ?>"
                                                        data-nama="<?= $nama_dokter ?>"
                                                        title="Hapus Dokter">
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
                                        echo 'Tidak ada data dokter yang sesuai dengan pencarian "' . htmlspecialchars($search_query) . '"';
                                    } else {
                                        echo 'Tidak ada data dokter ditemukan.';
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

    <!-- Modal Tambah Dokter -->
    <div class="modal fade" id="tambahDokterModal" tabindex="-1" aria-labelledby="tambahDokterModalLabel" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="tambahDokterModalLabel">
                        <i class="fas fa-user-plus me-2"></i>Tambah Dokter Baru
                    </h5>
                </div>
                <form method="POST" action="datadokter.php" id="tambahDokterForm" enctype="multipart/form-data">
                    <input type="hidden" name="tambah_dokter" value="1">
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="kode_dokter" class="form-label">Kode Dokter <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="kode_dokter" name="kode_dokter" required 
                                           placeholder="Masukkan kode dokter">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="nama_dokter" class="form-label">Nama Dokter <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="nama_dokter" name="nama_dokter" required 
                                           placeholder="Masukkan nama dokter">
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="spesialisasi_dokter" class="form-label">Spesialisasi</label>
                                    <input type="text" class="form-control" id="spesialisasi_dokter" name="spesialisasi_dokter" 
                                           placeholder="Masukkan spesialisasi">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="foto_dokter" class="form-label">Foto Dokter</label>
                                    <input type="file" class="form-control" id="foto_dokter" name="foto_dokter" 
                                           accept="image/*">
                                    <div class="form-text">Format: JPG, PNG, GIF. Maksimal 2MB.</div>
                                    <div id="fotoPreviewTambah" class="foto-preview"></div>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="tanggal_lahir_dokter" class="form-label">Tanggal Lahir</label>
                                    <input type="date" class="form-control" id="tanggal_lahir_dokter" name="tanggal_lahir_dokter">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="jenis_kelamin_dokter" class="form-label">Jenis Kelamin</label>
                                    <select class="form-select" id="jenis_kelamin_dokter" name="jenis_kelamin_dokter">
                                        <option value="">Pilih Jenis Kelamin</option>
                                        <option value="Laki-laki">Laki-laki</option>
                                        <option value="Perempuan">Perempuan</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="email_dokter" class="form-label">Email</label>
                                    <input type="email" class="form-control" id="email_dokter" name="email_dokter" 
                                           placeholder="Masukkan email">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="telepon_dokter" class="form-label">Telepon</label>
                                    <input type="text" class="form-control" id="telepon_dokter" name="telepon_dokter" 
                                           placeholder="Masukkan nomor telepon">
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="alamat_dokter" class="form-label">Alamat</label>
                                    <textarea class="form-control" id="alamat_dokter" name="alamat_dokter" 
                                              placeholder="Masukkan alamat" rows="3"></textarea>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="ruang" class="form-label">Ruang</label>
                                    <input type="text" class="form-control" id="ruang" name="ruang" 
                                           placeholder="Masukkan ruang">
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                            <i class="fas fa-times me-1"></i>Batal
                        </button>
                        <button type="submit" class="btn btn-primary" id="btnTambahDokter">
                            <i class="fas fa-save me-1"></i>Simpan Dokter
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Edit Dokter -->
    <div class="modal fade" id="editDokterModal" tabindex="-1" aria-labelledby="editDokterModalLabel" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editDokterModalLabel">
                        <i class="fas fa-edit me-2"></i>Edit Data Dokter
                    </h5>
                </div>
                <form method="POST" action="datadokter.php" id="editDokterForm" enctype="multipart/form-data">
                    <input type="hidden" name="edit_dokter" value="1">
                    <input type="hidden" id="edit_id_user" name="id_user">
                    
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="edit_kode_dokter" class="form-label">Kode Dokter <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="edit_kode_dokter" name="kode_dokter" required 
                                           placeholder="Masukkan kode dokter">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="edit_nama_dokter" class="form-label">Nama Dokter <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="edit_nama_dokter" name="nama_dokter" required 
                                           placeholder="Masukkan nama dokter">
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="edit_spesialisasi_dokter" class="form-label">Spesialisasi</label>
                                    <input type="text" class="form-control" id="edit_spesialisasi_dokter" name="spesialisasi_dokter" 
                                           placeholder="Masukkan spesialisasi">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="edit_foto_dokter" class="form-label">Foto Dokter</label>
                                    <input type="file" class="form-control" id="edit_foto_dokter" name="foto_dokter" 
                                           accept="image/*">
                                    <div class="form-text">Kosongkan jika tidak ingin mengubah foto.</div>
                                    <div id="fotoPreviewEdit" class="foto-preview"></div>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="edit_tanggal_lahir_dokter" class="form-label">Tanggal Lahir</label>
                                    <input type="date" class="form-control" id="edit_tanggal_lahir_dokter" name="tanggal_lahir_dokter">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="edit_jenis_kelamin_dokter" class="form-label">Jenis Kelamin</label>
                                    <select class="form-select" id="edit_jenis_kelamin_dokter" name="jenis_kelamin_dokter">
                                        <option value="">Pilih Jenis Kelamin</option>
                                        <option value="Laki-laki">Laki-laki</option>
                                        <option value="Perempuan">Perempuan</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="edit_email_dokter" class="form-label">Email</label>
                                    <input type="email" class="form-control" id="edit_email_dokter" name="email_dokter" 
                                           placeholder="Masukkan email">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="edit_telepon_dokter" class="form-label">Telepon</label>
                                    <input type="text" class="form-control" id="edit_telepon_dokter" name="telepon_dokter" 
                                           placeholder="Masukkan nomor telepon">
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="edit_alamat_dokter" class="form-label">Alamat</label>
                                    <textarea class="form-control" id="edit_alamat_dokter" name="alamat_dokter" 
                                              placeholder="Masukkan alamat" rows="3"></textarea>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="edit_ruang" class="form-label">Ruang</label>
                                    <input type="text" class="form-control" id="edit_ruang" name="ruang" 
                                           placeholder="Masukkan ruang">
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                            <i class="fas fa-times me-1"></i>Batal
                        </button>
                        <button type="submit" class="btn btn-primary" id="btnUpdateDokter">
                            <i class="fas fa-save me-1"></i>Update Dokter
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Hapus Dokter -->
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
                    <p class="text-center">Apakah Anda yakin ingin menghapus dokter:</p>
                    <h5 class="text-center text-danger" id="namaDokterHapus"></h5>
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
        let url = 'datadokter.php?entries=' + entries + '&page=1&sort=' + sort;
        
        if (search) {
            url += '&search=' + encodeURIComponent(search);
        }
        
        window.location.href = url;
    }

    // Function untuk clear search
    function clearSearch() {
        const entries = document.getElementById('entriesPerPage').value;
        const sort = '<?= $sort_order ?>';
        window.location.href = 'datadokter.php?entries=' + entries + '&sort=' + sort;
    }

    // Function untuk preview foto
    function previewFoto(input, previewId) {
        const preview = document.getElementById(previewId);
        const file = input.files[0];
        
        if (file) {
            const reader = new FileReader();
            
            reader.onload = function(e) {
                preview.innerHTML = `<img src="${e.target.result}" class="foto-preview" alt="Preview Foto">`;
            }
            
            reader.readAsDataURL(file);
        } else {
            preview.innerHTML = '';
        }
    }

    // Function untuk menutup modal tambah dokter
    function closeTambahDokterModal() {
        const modal = bootstrap.Modal.getInstance(document.getElementById('tambahDokterModal'));
        modal.hide();
    }

    // Function untuk menutup modal edit dokter
    function closeEditDokterModal() {
        const modal = bootstrap.Modal.getInstance(document.getElementById('editDokterModal'));
        modal.hide();
    }

    // Function untuk menutup modal hapus dokter
    function closeHapusModal() {
        const modal = bootstrap.Modal.getInstance(document.getElementById('hapusModal'));
        modal.hide();
    }

    // Function untuk menampilkan modal hapus
    function showHapusModal(id, nama) {
        document.getElementById('namaDokterHapus').textContent = nama;
        document.getElementById('hapusButton').href = 'datadokter.php?hapus=' + id;
        
        // Tampilkan modal
        const hapusModal = new bootstrap.Modal(document.getElementById('hapusModal'));
        hapusModal.show();
    }

    // Function untuk menampilkan modal edit
    function showEditModal(id, kode, spesialisasi, foto, nama, tanggal_lahir, jenis_kelamin, alamat, email, telepon, ruang) {
        // Isi form dengan data yang ada
        document.getElementById('edit_id_user').value = id;
        document.getElementById('edit_kode_dokter').value = kode;
        document.getElementById('edit_spesialisasi_dokter').value = spesialisasi;
        document.getElementById('edit_nama_dokter').value = nama;
        document.getElementById('edit_tanggal_lahir_dokter').value = tanggal_lahir;
        document.getElementById('edit_jenis_kelamin_dokter').value = jenis_kelamin;
        document.getElementById('edit_alamat_dokter').value = alamat;
        document.getElementById('edit_email_dokter').value = email;
        document.getElementById('edit_telepon_dokter').value = telepon;
        document.getElementById('edit_ruang').value = ruang;
        
        // Tampilkan preview foto jika ada
        const fotoPreview = document.getElementById('fotoPreviewEdit');
        if (foto) {
            fotoPreview.innerHTML = `<img src="imagedokter/${foto}" class="foto-preview" alt="Foto Dokter">`;
        } else {
            fotoPreview.innerHTML = '';
        }
        
        // Tampilkan modal
        const editModal = new bootstrap.Modal(document.getElementById('editDokterModal'));
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
                const nama = button.getAttribute('data-nama');
                showHapusModal(id, nama);
            }
        });

        // Event delegation untuk tombol edit
        document.addEventListener('click', function(e) {
            if (e.target.closest('.btn-edit')) {
                e.preventDefault();
                const button = e.target.closest('.btn-edit');
                const id = button.getAttribute('data-id');
                const kode = button.getAttribute('data-kode');
                const spesialisasi = button.getAttribute('data-spesialisasi');
                const foto = button.getAttribute('data-foto');
                const nama = button.getAttribute('data-nama');
                const tanggal_lahir = button.getAttribute('data-tanggal_lahir');
                const jenis_kelamin = button.getAttribute('data-jenis_kelamin');
                const alamat = button.getAttribute('data-alamat');
                const email = button.getAttribute('data-email');
                const telepon = button.getAttribute('data-telepon');
                const ruang = button.getAttribute('data-ruang');
                showEditModal(id, kode, spesialisasi, foto, nama, tanggal_lahir, jenis_kelamin, alamat, email, telepon, ruang);
            }
        });

        // Event listener untuk form tambah
        const tambahForm = document.getElementById('tambahDokterForm');
        if (tambahForm) {
            tambahForm.addEventListener('submit', function(e) {
                handleFormSubmit(e, 'btnTambahDokter');
            });
        }

        // Event listener untuk form edit
        const editForm = document.getElementById('editDokterForm');
        if (editForm) {
            editForm.addEventListener('submit', function(e) {
                handleFormSubmit(e, 'btnUpdateDokter');
            });
        }

        // Event listener untuk preview foto tambah
        const fotoTambah = document.getElementById('foto_dokter');
        if (fotoTambah) {
            fotoTambah.addEventListener('change', function() {
                previewFoto(this, 'fotoPreviewTambah');
            });
        }

        // Event listener untuk preview foto edit
        const fotoEdit = document.getElementById('edit_foto_dokter');
        if (fotoEdit) {
            fotoEdit.addEventListener('change', function() {
                previewFoto(this, 'fotoPreviewEdit');
            });
        }

        // Auto focus pada input search
        const searchInput = document.querySelector('input[name="search"]');
        if (searchInput && '<?= $search_query ?>') {
            searchInput.focus();
            searchInput.setSelectionRange(searchInput.value.length, searchInput.value.length);
        }

        // Reset form modal ketika ditutup
        const tambahDokterModal = document.getElementById('tambahDokterModal');
        if (tambahDokterModal) {
            tambahDokterModal.addEventListener('hidden.bs.modal', function () {
                document.getElementById('tambahDokterForm').reset();
                document.getElementById('fotoPreviewTambah').innerHTML = '';
                const submitButton = document.getElementById('btnTambahDokter');
                submitButton.innerHTML = '<i class="fas fa-save me-1"></i>Simpan Dokter';
                submitButton.disabled = false;
            });
        }

        const editDokterModal = document.getElementById('editDokterModal');
        if (editDokterModal) {
            editDokterModal.addEventListener('hidden.bs.modal', function () {
                const submitButton = document.getElementById('btnUpdateDokter');
                submitButton.innerHTML = '<i class="fas fa-save me-1"></i>Update Dokter';
                submitButton.disabled = false;
            });
        }

        // Event listener untuk tombol close manual
        document.querySelector('#tambahDokterModal .btn-close')?.addEventListener('click', closeTambahDokterModal);
        document.querySelector('#tambahDokterModal .btn-secondary')?.addEventListener('click', closeTambahDokterModal);
        
        document.querySelector('#editDokterModal .btn-close')?.addEventListener('click', closeEditDokterModal);
        document.querySelector('#editDokterModal .btn-secondary')?.addEventListener('click', closeEditDokterModal);
        
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
    $url = 'datadokter.php?';
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
    $url = 'datadokter.php?';
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