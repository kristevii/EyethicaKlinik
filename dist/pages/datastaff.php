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

// PROSES HAPUS STAFF
if (isset($_GET['hapus'])) {
    $id_user = $_GET['hapus'];
    $staff_data = $db->get_staff_by_id($id_user);
    $foto_to_delete = $staff_data['foto_staff'] ?? null;

    if ($db->hapus_data_staff($id_user)) {
        if ($foto_to_delete && file_exists('imagestaff/' . $foto_to_delete)) {
            unlink('imagestaff/' . $foto_to_delete);
        }

        $username = $_SESSION['username'] ?? 'unknown user';
        $entitas = 'Hapus';
        $jenis = 'Staff';
        $deskripsi = "Staff '{$staff_data['nama_staff']}' berhasil dihapus oleh $username.";
        $waktu = date('Y-m-d H:i:s');
        $db->tambah_aktivitas($entitas, $jenis, $deskripsi, $waktu);

        $_SESSION['notif_status'] = 'success';
        $_SESSION['notif_message'] = 'Data staff berhasil dihapus.';
    } else {
        $_SESSION['notif_status'] = 'error';
        $_SESSION['notif_message'] = 'Gagal menghapus data staff.';
    }
    header("Location: datastaff.php");
    exit();
}

// PROSES EDIT STAFF
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_staff'])) {
    $id_user = $_POST['id_user'] ?? '';
    $kode_staff = $_POST['kode_staff'] ?? '';
    $jabatan_staff = $_POST['jabatan_staff'] ?? '';
    $nama_staff = $_POST['nama_staff'] ?? '';
    $jenis_kelamin_staff = $_POST['jenis_kelamin_staff'] ?? '';
    $tanggal_lahir_staff = $_POST['tanggal_lahir_staff'] ?? '';
    $alamat_staff = $_POST['alamat_staff'] ?? '';
    $email_staff = $_POST['email_staff'] ?? '';
    $telepon_staff = $_POST['telepon_staff'] ?? '';
    
    // Validasi data
    if (empty($id_user) || empty($kode_staff) || empty($nama_staff)) {
        $_SESSION['notif_status'] = 'error';
        $_SESSION['notif_message'] = 'Field kode staff dan nama staff wajib diisi!';
        header("Location: datastaff.php");
        exit();
    }
    
    // Handle upload foto
    $foto_staff = null;
    if (isset($_FILES['foto_staff']) && $_FILES['foto_staff']['error'] === UPLOAD_ERR_OK) {
        $foto_name = $_FILES['foto_staff']['name'];
        $foto_tmp = $_FILES['foto_staff']['tmp_name'];
        $foto_size = $_FILES['foto_staff']['size'];
        $foto_ext = strtolower(pathinfo($foto_name, PATHINFO_EXTENSION));
        
        $allowed_ext = ['jpg', 'jpeg', 'png', 'gif'];
        if (in_array($foto_ext, $allowed_ext)) {
            $new_foto_name = uniqid('staff_', true) . '.' . $foto_ext;
            $upload_path = 'imagestaff/' . $new_foto_name;
            
            if (move_uploaded_file($foto_tmp, $upload_path)) {
                $foto_staff = $new_foto_name;
                
                // Hapus foto lama jika ada
                $old_data = $db->get_staff_by_id($id_user);
                if ($old_data && $old_data['foto_staff'] && file_exists('imagestaff/' . $old_data['foto_staff'])) {
                    unlink('imagestaff/' . $old_data['foto_staff']);
                }
            }
        }
    }
    
    // Update data staff
    if ($db->update_data_staff($id_user, $kode_staff, $jabatan_staff, $foto_staff, $nama_staff, $jenis_kelamin_staff, $tanggal_lahir_staff, $alamat_staff, $email_staff, $telepon_staff)) {
        // Log aktivitas
        $username_session = $_SESSION['username'] ?? 'unknown user';
        $entitas = 'Edit';
        $jenis = 'Staff';
        $deskripsi = "Staff '$nama_staff' berhasil diupdate oleh $username_session.";
        $waktu = date('Y-m-d H:i:s');
        $db->tambah_aktivitas($entitas, $jenis, $deskripsi, $waktu);
        
        $_SESSION['notif_status'] = 'success';
        $_SESSION['notif_message'] = 'Data staff berhasil diupdate.';
    } else {
        $_SESSION['notif_status'] = 'error';
        $_SESSION['notif_message'] = 'Gagal mengupdate data staff.';
    }
    
    header("Location: datastaff.php");
    exit();
}

// PROSES TAMBAH STAFF
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['tambah_staff'])) {
    $kode_staff = $_POST['kode_staff'] ?? '';
    $jabatan_staff = $_POST['jabatan_staff'] ?? '';
    $nama_staff = $_POST['nama_staff'] ?? '';
    $jenis_kelamin_staff = $_POST['jenis_kelamin_staff'] ?? '';
    $tanggal_lahir_staff = $_POST['tanggal_lahir_staff'] ?? '';
    $alamat_staff = $_POST['alamat_staff'] ?? '';
    $email_staff = $_POST['email_staff'] ?? '';
    $telepon_staff = $_POST['telepon_staff'] ?? '';
    
    // Validasi data
    if (empty($kode_staff) || empty($nama_staff)) {
        $_SESSION['notif_status'] = 'error';
        $_SESSION['notif_message'] = 'Field kode staff dan nama staff wajib diisi!';
        header("Location: datastaff.php");
        exit();
    }
    
    // Handle upload foto
    $foto_staff = null;
    if (isset($_FILES['foto_staff']) && $_FILES['foto_staff']['error'] === UPLOAD_ERR_OK) {
        $foto_name = $_FILES['foto_staff']['name'];
        $foto_tmp = $_FILES['foto_staff']['tmp_name'];
        $foto_size = $_FILES['foto_staff']['size'];
        $foto_ext = strtolower(pathinfo($foto_name, PATHINFO_EXTENSION));
        
        $allowed_ext = ['jpg', 'jpeg', 'png', 'gif'];
        if (in_array($foto_ext, $allowed_ext)) {
            $new_foto_name = uniqid('staff_', true) . '.' . $foto_ext;
            $upload_path = 'imagestaff/' . $new_foto_name;
            
            if (move_uploaded_file($foto_tmp, $upload_path)) {
                $foto_staff = $new_foto_name;
            }
        }
    }
    
    // Tambah data staff
    if ($db->tambah_data_staff($kode_staff, $jabatan_staff, $foto_staff, $nama_staff, $jenis_kelamin_staff, $tanggal_lahir_staff, $alamat_staff, $email_staff, $telepon_staff)) {
        // Log aktivitas
        $username_session = $_SESSION['username'] ?? 'unknown user';
        $entitas = 'Tambah';
        $jenis = 'Staff';
        $deskripsi = "Staff '$nama_staff' berhasil ditambahkan oleh $username_session.";
        $waktu = date('Y-m-d H:i:s');
        $db->tambah_aktivitas($entitas, $jenis, $deskripsi, $waktu);
        
        $_SESSION['notif_status'] = 'success';
        $_SESSION['notif_message'] = 'Data staff berhasil ditambahkan.';
    } else {
        $_SESSION['notif_status'] = 'error';
        $_SESSION['notif_message'] = 'Gagal menambahkan data staff.';
    }
    
    header("Location: datastaff.php");
    exit();
}

// Konfigurasi pagination, search, dan sorting
$entries_per_page = isset($_GET['entries']) ? (int)$_GET['entries'] : 10;
$current_page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$search_query = isset($_GET['search']) ? trim($_GET['search']) : '';
$sort_order = isset($_GET['sort']) && $_GET['sort'] === 'desc' ? 'desc' : 'asc';

// Ambil semua data staff
$all_staff = $db->tampil_data_staff();

// Filter data berdasarkan search query
if (!empty($search_query)) {
    $filtered_staff = [];
    foreach ($all_staff as $staff) {
        // Cari di semua kolom yang relevan
        if (stripos($staff['id_user'] ?? '', $search_query) !== false ||
            stripos($staff['kode_staff'] ?? '', $search_query) !== false ||
            stripos($staff['jabatan_staff'] ?? '', $search_query) !== false ||
            stripos($staff['nama_staff'] ?? '', $search_query) !== false ||
            stripos($staff['jenis_kelamin_staff'] ?? '', $search_query) !== false ||
            stripos($staff['tanggal_lahir_staff'] ?? '', $search_query) !== false ||
            stripos($staff['alamat_staff'] ?? '', $search_query) !== false ||
            stripos($staff['email_staff'] ?? '', $search_query) !== false ||
            stripos($staff['telepon_staff'] ?? '', $search_query) !== false) {
            $filtered_staff[] = $staff;
        }
    }
    $all_staff = $filtered_staff;
}

// Urutkan data berdasarkan ID User
if ($sort_order === 'desc') {
    // Urutkan dari ID terbesar ke terkecil (terakhir ke terawal)
    usort($all_staff, function($a, $b) {
        return ($b['id_user'] ?? 0) - ($a['id_user'] ?? 0);
    });
} else {
    // Urutkan dari ID terkecil ke terbesar (terawal ke terakhir) - default
    usort($all_staff, function($a, $b) {
        return ($a['id_user'] ?? 0) - ($b['id_user'] ?? 0);
    });
}

// Hitung total data
$total_entries = count($all_staff);

// Hitung total halaman
$total_pages = ceil($total_entries / $entries_per_page);

// Pastikan current page valid
if ($current_page < 1) $current_page = 1;
if ($current_page > $total_pages && $total_pages > 0) $current_page = $total_pages;

// Hitung offset
$offset = ($current_page - 1) * $entries_per_page;

// Ambil data untuk halaman saat ini
$data_staff = array_slice($all_staff, $offset, $entries_per_page);

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

// Cek apakah data staff kosong untuk memicu modal
$is_data_empty = empty($data_staff);
?>

<!DOCTYPE html>
<html lang="en">
  <!-- [Head] start -->

  <head>
    <title>Data Staff - EyeThica Klinik</title>
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
.foto-staff {
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
                  <li class="breadcrumb-item" aria-current="page">Data Staff</li>
                </ul>
              </div>
              <div class="col-md-12">
                <div class="page-header-title">
                  <h2 class="mb-0">Data Staff</h2>
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
                <!-- Tombol Tambah Staff dengan Modal -->
                <button type="button" class="btn btn-dark me-2" data-bs-toggle="modal" data-bs-target="#tambahStaffModal">
                    <i class="fas fa-plus me-1"></i> Tambah Staff
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
                                           placeholder="Cari data staff..." 
                                           value="<?= htmlspecialchars($search_query) ?>"
                                           aria-label="Search">
                                    <input type="hidden" name="entries" value="<?= $entries_per_page ?>">
                                    <input type="hidden" name="sort" value="<?= $sort_order ?>">
                                    <button class="btn btn-outline-secondary" type="submit">
                                        <i class="fas fa-search"></i>
                                    </button>
                                    <?php if (!empty($search_query)): ?>
                                    <a href="datastaff.php?entries=<?= $entries_per_page ?>&sort=<?= $sort_order ?>" class="btn btn-outline-danger" type="button">
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
                        <table id="staffTable" class="table table-hover">
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
                                    <th>Kode Staff</th>
                                    <th>Nama Staff</th>
                                    <th>Jabatan</th>
                                    <th>Jenis Kelamin</th>
                                    <th>Tanggal Lahir</th>
                                    <th>Email</th>
                                    <th>Telepon</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                if (!empty($data_staff) && is_array($data_staff)) {
                                    foreach ($data_staff as $staff) {
                                        $id_user = htmlspecialchars($staff['id_user'] ?? '', ENT_QUOTES, 'UTF-8');
                                        $kode_staff = htmlspecialchars($staff['kode_staff'] ?? '', ENT_QUOTES, 'UTF-8');
                                        $jabatan_staff = htmlspecialchars($staff['jabatan_staff'] ?? '', ENT_QUOTES, 'UTF-8');
                                        $foto_staff = htmlspecialchars($staff['foto_staff'] ?? '', ENT_QUOTES, 'UTF-8');
                                        $nama_staff = htmlspecialchars($staff['nama_staff'] ?? '', ENT_QUOTES, 'UTF-8');
                                        $jenis_kelamin_staff = htmlspecialchars($staff['jenis_kelamin_staff'] ?? '', ENT_QUOTES, 'UTF-8');
                                        $tanggal_lahir_staff = htmlspecialchars($staff['tanggal_lahir_staff'] ?? '', ENT_QUOTES, 'UTF-8');
                                        $alamat_staff = htmlspecialchars($staff['alamat_staff'] ?? '', ENT_QUOTES, 'UTF-8');
                                        $email_staff = htmlspecialchars($staff['email_staff'] ?? '', ENT_QUOTES, 'UTF-8');
                                        $telepon_staff = htmlspecialchars($staff['telepon_staff'] ?? '', ENT_QUOTES, 'UTF-8');
                                        
                                        // Format tanggal lahir
                                        $tanggal_lahir_formatted = !empty($tanggal_lahir_staff) ? date('d-m-Y', strtotime($tanggal_lahir_staff)) : '-';
                                ?>
                                    <tr>
                                        <td><?= $start_number ?></td>
                                        <td><?= $id_user ?></td>
                                        <td>
                                            <?php if (!empty($foto_staff)): ?>
                                                <img src="imagestaff/<?= $foto_staff ?>" alt="Foto Staff" class="foto-staff">
                                            <?php else: ?>
                                                <div class="foto-staff bg-light d-flex align-items-center justify-content-center">
                                                    <i class="fas fa-user text-muted"></i>
                                                </div>
                                            <?php endif; ?>
                                        </td>
                                        <td><?= $kode_staff ?></td>
                                        <td><?= $nama_staff ?></td>
                                        <td><?= $jabatan_staff ?></td>
                                        <td><?= $jenis_kelamin_staff ?></td>
                                        <td><?= $tanggal_lahir_formatted ?></td>
                                        <td><?= $email_staff ?></td>
                                        <td><?= $telepon_staff ?></td>
                                        <td>
                                            <div class="btn-group" role="group">
                                                <button type="button"
                                                        class="btn btn-warning btn-sm btn-edit"
                                                        data-id="<?= $id_user ?>"
                                                        data-kode="<?= $kode_staff ?>"
                                                        data-jabatan="<?= $jabatan_staff ?>"
                                                        data-foto="<?= $foto_staff ?>"
                                                        data-nama="<?= $nama_staff ?>"
                                                        data-jenis_kelamin="<?= $jenis_kelamin_staff ?>"
                                                        data-tanggal_lahir="<?= $tanggal_lahir_staff ?>"
                                                        data-alamat="<?= $alamat_staff ?>"
                                                        data-email="<?= $email_staff ?>"
                                                        data-telepon="<?= $telepon_staff ?>"
                                                        title="Edit Staff">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                                <button type="button"
                                                        class="btn btn-danger btn-sm btn-hapus"
                                                        data-id="<?= $id_user ?>"
                                                        data-nama="<?= $nama_staff ?>"
                                                        title="Hapus Staff">
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
                                        echo 'Tidak ada data staff yang sesuai dengan pencarian "' . htmlspecialchars($search_query) . '"';
                                    } else {
                                        echo 'Tidak ada data staff ditemukan.';
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

    <!-- Modal Tambah Staff -->
    <div class="modal fade" id="tambahStaffModal" tabindex="-1" aria-labelledby="tambahStaffModalLabel" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="tambahStaffModalLabel">
                        <i class="fas fa-user-plus me-2"></i>Tambah Staff Baru
                    </h5>
                </div>
                <form method="POST" action="datastaff.php" id="tambahStaffForm" enctype="multipart/form-data">
                    <input type="hidden" name="tambah_staff" value="1">
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="kode_staff" class="form-label">Kode Staff <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="kode_staff" name="kode_staff" required 
                                           placeholder="Masukkan kode staff">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="nama_staff" class="form-label">Nama Staff <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="nama_staff" name="nama_staff" required 
                                           placeholder="Masukkan nama staff">
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="jabatan_staff" class="form-label">Jabatan</label>
                                    <input type="text" class="form-control" id="jabatan_staff" name="jabatan_staff" 
                                           placeholder="Masukkan jabatan">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="foto_staff" class="form-label">Foto Staff</label>
                                    <input type="file" class="form-control" id="foto_staff" name="foto_staff" 
                                           accept="image/*">
                                    <div class="form-text">Format: JPG, PNG, GIF. Maksimal 2MB.</div>
                                    <div id="fotoPreviewTambah" class="foto-preview"></div>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="tanggal_lahir_staff" class="form-label">Tanggal Lahir</label>
                                    <input type="date" class="form-control" id="tanggal_lahir_staff" name="tanggal_lahir_staff">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="jenis_kelamin_staff" class="form-label">Jenis Kelamin</label>
                                    <select class="form-select" id="jenis_kelamin_staff" name="jenis_kelamin_staff">
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
                                    <label for="email_staff" class="form-label">Email</label>
                                    <input type="email" class="form-control" id="email_staff" name="email_staff" 
                                           placeholder="Masukkan email">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="telepon_staff" class="form-label">Telepon</label>
                                    <input type="text" class="form-control" id="telepon_staff" name="telepon_staff" 
                                           placeholder="Masukkan nomor telepon">
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-12">
                                <div class="mb-3">
                                    <label for="alamat_staff" class="form-label">Alamat</label>
                                    <textarea class="form-control" id="alamat_staff" name="alamat_staff" 
                                              placeholder="Masukkan alamat" rows="3"></textarea>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                            <i class="fas fa-times me-1"></i>Batal
                        </button>
                        <button type="submit" class="btn btn-primary" id="btnTambahStaff">
                            <i class="fas fa-save me-1"></i>Simpan Staff
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Edit Staff -->
    <div class="modal fade" id="editStaffModal" tabindex="-1" aria-labelledby="editStaffModalLabel" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editStaffModalLabel">
                        <i class="fas fa-edit me-2"></i>Edit Data Staff
                    </h5>
                </div>
                <form method="POST" action="datastaff.php" id="editStaffForm" enctype="multipart/form-data">
                    <input type="hidden" name="edit_staff" value="1">
                    <input type="hidden" id="edit_id_user" name="id_user">
                    
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="edit_kode_staff" class="form-label">Kode Staff <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="edit_kode_staff" name="kode_staff" required 
                                           placeholder="Masukkan kode staff">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="edit_nama_staff" class="form-label">Nama Staff <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="edit_nama_staff" name="nama_staff" required 
                                           placeholder="Masukkan nama staff">
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="edit_jabatan_staff" class="form-label">Jabatan</label>
                                    <input type="text" class="form-control" id="edit_jabatan_staff" name="jabatan_staff" 
                                           placeholder="Masukkan jabatan">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="edit_foto_staff" class="form-label">Foto Staff</label>
                                    <input type="file" class="form-control" id="edit_foto_staff" name="foto_staff" 
                                           accept="image/*">
                                    <div class="form-text">Kosongkan jika tidak ingin mengubah foto.</div>
                                    <div id="fotoPreviewEdit" class="foto-preview"></div>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="edit_tanggal_lahir_staff" class="form-label">Tanggal Lahir</label>
                                    <input type="date" class="form-control" id="edit_tanggal_lahir_staff" name="tanggal_lahir_staff">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="edit_jenis_kelamin_staff" class="form-label">Jenis Kelamin</label>
                                    <select class="form-select" id="edit_jenis_kelamin_staff" name="jenis_kelamin_staff">
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
                                    <label for="edit_email_staff" class="form-label">Email</label>
                                    <input type="email" class="form-control" id="edit_email_staff" name="email_staff" 
                                           placeholder="Masukkan email">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="edit_telepon_staff" class="form-label">Telepon</label>
                                    <input type="text" class="form-control" id="edit_telepon_staff" name="telepon_staff" 
                                           placeholder="Masukkan nomor telepon">
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-12">
                                <div class="mb-3">
                                    <label for="edit_alamat_staff" class="form-label">Alamat</label>
                                    <textarea class="form-control" id="edit_alamat_staff" name="alamat_staff" 
                                              placeholder="Masukkan alamat" rows="3"></textarea>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                            <i class="fas fa-times me-1"></i>Batal
                        </button>
                        <button type="submit" class="btn btn-primary" id="btnUpdateStaff">
                            <i class="fas fa-save me-1"></i>Update Staff
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Hapus Staff -->
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
                    <p class="text-center">Apakah Anda yakin ingin menghapus staff:</p>
                    <h5 class="text-center text-danger" id="namaStaffHapus"></h5>
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
        let url = 'datastaff.php?entries=' + entries + '&page=1&sort=' + sort;
        
        if (search) {
            url += '&search=' + encodeURIComponent(search);
        }
        
        window.location.href = url;
    }

    // Function untuk clear search
    function clearSearch() {
        const entries = document.getElementById('entriesPerPage').value;
        const sort = '<?= $sort_order ?>';
        window.location.href = 'datastaff.php?entries=' + entries + '&sort=' + sort;
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

    // Function untuk menutup modal tambah staff
    function closeTambahStaffModal() {
        const modal = bootstrap.Modal.getInstance(document.getElementById('tambahStaffModal'));
        modal.hide();
    }

    // Function untuk menutup modal edit staff
    function closeEditStaffModal() {
        const modal = bootstrap.Modal.getInstance(document.getElementById('editStaffModal'));
        modal.hide();
    }

    // Function untuk menutup modal hapus staff
    function closeHapusModal() {
        const modal = bootstrap.Modal.getInstance(document.getElementById('hapusModal'));
        modal.hide();
    }

    // Function untuk menampilkan modal hapus - PERBAIKAN
    function showHapusModal(id, nama) {
        document.getElementById('namaStaffHapus').textContent = nama;
        document.getElementById('hapusButton').href = 'datastaff.php?hapus=' + id;
        
        // Tampilkan modal dengan membuat instance baru
        const hapusModal = new bootstrap.Modal(document.getElementById('hapusModal'));
        hapusModal.show();
    }

    // Function untuk menampilkan modal edit - PERBAIKAN
    function showEditModal(id, kode, jabatan, foto, nama, jenis_kelamin, tanggal_lahir, alamat, email, telepon) {
        // Isi form dengan data yang ada
        document.getElementById('edit_id_user').value = id;
        document.getElementById('edit_kode_staff').value = kode;
        document.getElementById('edit_jabatan_staff').value = jabatan;
        document.getElementById('edit_nama_staff').value = nama;
        document.getElementById('edit_jenis_kelamin_staff').value = jenis_kelamin;
        document.getElementById('edit_tanggal_lahir_staff').value = tanggal_lahir;
        document.getElementById('edit_alamat_staff').value = alamat;
        document.getElementById('edit_email_staff').value = email;
        document.getElementById('edit_telepon_staff').value = telepon;
        
        // Tampilkan preview foto jika ada
        const fotoPreview = document.getElementById('fotoPreviewEdit');
        if (foto) {
            fotoPreview.innerHTML = `<img src="imagestaff/${foto}" class="foto-preview" alt="Foto Staff">`;
        } else {
            fotoPreview.innerHTML = '';
        }
        
        // Tampilkan modal dengan membuat instance baru
        const editModal = new bootstrap.Modal(document.getElementById('editStaffModal'));
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

    // Setup modal dengan event delegation - PERBAIKAN
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
                const jabatan = button.getAttribute('data-jabatan');
                const foto = button.getAttribute('data-foto');
                const nama = button.getAttribute('data-nama');
                const jenis_kelamin = button.getAttribute('data-jenis_kelamin');
                const tanggal_lahir = button.getAttribute('data-tanggal_lahir');
                const alamat = button.getAttribute('data-alamat');
                const email = button.getAttribute('data-email');
                const telepon = button.getAttribute('data-telepon');
                showEditModal(id, kode, jabatan, foto, nama, jenis_kelamin, tanggal_lahir, alamat, email, telepon);
            }
        });

        // Event listener untuk form tambah
        const tambahForm = document.getElementById('tambahStaffForm');
        if (tambahForm) {
            tambahForm.addEventListener('submit', function(e) {
                handleFormSubmit(e, 'btnTambahStaff');
            });
        }

        // Event listener untuk form edit
        const editForm = document.getElementById('editStaffForm');
        if (editForm) {
            editForm.addEventListener('submit', function(e) {
                handleFormSubmit(e, 'btnUpdateStaff');
            });
        }

        // Event listener untuk preview foto tambah
        const fotoTambah = document.getElementById('foto_staff');
        if (fotoTambah) {
            fotoTambah.addEventListener('change', function() {
                previewFoto(this, 'fotoPreviewTambah');
            });
        }

        // Event listener untuk preview foto edit
        const fotoEdit = document.getElementById('edit_foto_staff');
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
        const tambahStaffModal = document.getElementById('tambahStaffModal');
        if (tambahStaffModal) {
            tambahStaffModal.addEventListener('hidden.bs.modal', function () {
                document.getElementById('tambahStaffForm').reset();
                document.getElementById('fotoPreviewTambah').innerHTML = '';
                const submitButton = document.getElementById('btnTambahStaff');
                submitButton.innerHTML = '<i class="fas fa-save me-1"></i>Simpan Staff';
                submitButton.disabled = false;
            });
        }

        const editStaffModal = document.getElementById('editStaffModal');
        if (editStaffModal) {
            editStaffModal.addEventListener('hidden.bs.modal', function () {
                const submitButton = document.getElementById('btnUpdateStaff');
                submitButton.innerHTML = '<i class="fas fa-save me-1"></i>Update Staff';
                submitButton.disabled = false;
            });
        }

        // Event listener untuk tombol close manual - PERBAIKAN
        document.querySelectorAll('#tambahStaffModal .btn-close, #tambahStaffModal .btn-secondary').forEach(btn => {
            btn.addEventListener('click', closeTambahStaffModal);
        });
        
        document.querySelectorAll('#editStaffModal .btn-close, #editStaffModal .btn-secondary').forEach(btn => {
            btn.addEventListener('click', closeEditStaffModal);
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
    $url = 'datastaff.php?';
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
    $url = 'datastaff.php?';
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