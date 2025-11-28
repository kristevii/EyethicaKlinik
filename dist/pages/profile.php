<?php
session_start();

include "koneksi.php";
$db = new database();

// Ambil data user dari session
$username = $_SESSION['username'] ?? 'Juna Arka';
$role = $_SESSION['role'] ?? 'Staff Administrasi';

// Fungsi untuk mendapatkan data rekan kerja (staff dan dokter)
function getRekanKerja($db) {
    $rekan_kerja = [];
    
    // Ambil data staff dari tabel data_staff (tanpa filter status)
    $query_staff = "SELECT nama_staff as nama, jabatan_staff as jabatan, foto_staff as foto 
                   FROM data_staff 
                   ORDER BY nama_staff";
    $result_staff = $db->koneksi->query($query_staff);
    
    if ($result_staff && $result_staff->num_rows > 0) {
        while ($row = $result_staff->fetch_assoc()) {
            // Jika foto kosong, gunakan foto default
            if (empty($row['foto'])) {
                $row['foto'] = '../assets2/img/team-2.jpg';
            }
            $rekan_kerja[] = $row;
        }
    }
    
    // Ambil data dokter dari tabel data_dokter (tanpa filter status)
    $query_dokter = "SELECT nama_dokter as nama, spesialisasi_dokter as jabatan, foto_dokter as foto 
                    FROM data_dokter 
                    ORDER BY nama_dokter";
    $result_dokter = $db->koneksi->query($query_dokter);
    
    if ($result_dokter && $result_dokter->num_rows > 0) {
        while ($row = $result_dokter->fetch_assoc()) {
            // Jika foto kosong, gunakan foto default
            if (empty($row['foto'])) {
                $row['foto'] = '../assets2/img/team-1.jpg';
            }
            $rekan_kerja[] = $row;
        }
    }
    
    // Jika tidak ada data, gunakan data sample
    if (empty($rekan_kerja)) {
        $rekan_kerja = [
            [
                'nama' => 'Dr. Sarah Wijaya',
                'jabatan' => 'Dokter Umum',
                'foto' => '../assets2/img/team-1.jpg'
            ],
            [
                'nama' => 'Budi Santoso',
                'jabatan' => 'Staff Administrasi',
                'foto' => '../assets2/img/team-2.jpg'
            ],
            [
                'nama' => 'Dr. Andi Pratama',
                'jabatan' => 'Dokter Spesialis Mata',
                'foto' => '../assets2/img/team-3.jpg'
            ],
            [
                'nama' => 'Siti Rahayu',
                'jabatan' => 'Staff Receptionist',
                'foto' => '../assets2/img/team-4.jpg'
            ]
        ];
    }
    
    return $rekan_kerja;
}

// Fungsi untuk mendapatkan 5 aktivitas terakhir
function getAktivitasTerakhir($db) {
    $aktivitas = [];
    
    // Query untuk mengambil 5 aktivitas terakhir
    $query = "SELECT jenis, entitas, keterangan, waktu 
              FROM aktivitas_profile 
              ORDER BY waktu DESC 
              LIMIT 5";
    $result = $db->koneksi->query($query);
    
    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $aktivitas[] = $row;
        }
    } else {
        // Data sample jika tidak ada data
        $aktivitas = [
            [
                'jenis' => 'Login',
                'entitas' => 'User',
                'keterangan' => 'Admin user successfully logged in.',
                'waktu' => '2025-11-26 09:39:00'
            ],
            [
                'jenis' => 'Edit',
                'entitas' => 'Data User',
                'keterangan' => 'Pengguna Juna Arka mengubah data profile.',
                'waktu' => '2025-10-29 07:39:56'
            ],
            [
                'jenis' => 'Login',
                'entitas' => 'User',
                'keterangan' => 'Pengguna Alea Wibawa berhasil login.',
                'waktu' => '2025-10-29 07:39:53'
            ],
            [
                'jenis' => 'Logout',
                'entitas' => 'User',
                'keterangan' => 'Pengguna dr. Maria Lestari, Sp.M logout dari sistem.',
                'waktu' => '2025-10-29 07:39:54'
            ],
            [
                'jenis' => 'Update',
                'entitas' => 'Profile',
                'keterangan' => 'Pengguna mengubah foto profile.',
                'waktu' => '2025-10-28 15:20:10'
            ]
        ];
    }
    
    return $aktivitas;
}

$rekan_kerja = getRekanKerja($db);
$aktivitas_terakhir = getAktivitasTerakhir($db);
?>

<!DOCTYPE html>
<html lang="en">
  <head>
    <title>Profile - EyeThica Klinik</title>
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
    <link rel="icon" href="../assets/images/favicon.svg" type="image/x-icon"> 
    <!-- [Font] Family -->
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

    <!-- Tailwind CSS -->
    <link
      href="https://fonts.googleapis.com/css?family=Open+Sans:300,400,600,700"
      rel="stylesheet" />
    <!-- Font Awesome Icons -->
    <script
      src="https://kit.fontawesome.com/42d5adcbca.js"
      crossorigin="anonymous"></script>
    <!-- Nucleo Icons -->
    <link href="../assets2/css/nucleo-icons.css" rel="stylesheet" />
    <link href="../assets2/css/nucleo-svg.css" rel="stylesheet" />
    <!-- Main Styling -->
    <link
      href="../assets2/css/soft-ui-dashboard-tailwind.css?v=1.0.5"
      rel="stylesheet" />

    <style>
      /* Custom CSS untuk mengatasi konflik */
      .tailwind-section {
        position: relative;
        z-index: 1;
      }
      .pc-content {
        position: relative;
        z-index: 2;
      }
      .tailwind-content {
        background: transparent !important;
      }
      .badge-jabatan {
        font-size: 0.7rem;
        padding: 4px 8px;
        border-radius: 12px;
        font-weight: 600;
      }
      .badge-dokter {
        background-color: #e3f2fd;
        color: #1976d2;
        border: 1px solid #bbdefb;
      }
      .badge-staff {
        background-color: #f3e5f5;
        color: #7b1fa2;
        border: 1px solid #e1bee7;
      }
      .icon-dokter {
        color: #1976d2;
      }
      .icon-staff {
        color: #7b1fa2;
      }
      .aktivitas-item {
        transition: all 0.2s ease-in-out;
      }
      .aktivitas-item:hover {
        transform: translateX(5px);
      }
      .badge-login {
        background-color: #e8f5e8;
        color: #2e7d32;
        border: 1px solid #c8e6c9;
      }
      .badge-logout {
        background-color: #ffebee;
        color: #c62828;
        border: 1px solid #ffcdd2;
      }
      .badge-edit {
        background-color: #e3f2fd;
        color: #1565c0;
        border: 1px solid #bbdefb;
      }
      .badge-update {
        background-color: #f3e5f5;
        color: #7b1fa2;
        border: 1px solid #e1bee7;
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
                  <li class="breadcrumb-item" aria-current="page">Profile</li>
                </ul>
              </div>
              <div class="col-md-12">
                <div class="page-header-title">
                  <h2 class="mb-0">Profile</h2>
                </div>
              </div>
            </div>
          </div>
        </div>
        <!-- [ breadcrumb ] end -->

        <!-- Tailwind Content Section -->
        <div class="tailwind-section">
          <div class="w-full px-3 mx-auto">
            <div class="relative flex items-center p-0 mt-3 overflow-hidden bg-center bg-cover min-h-75 rounded-2xl"
              style="
                background-image: url('../assets2/img/curved-images/curved0.jpg');
                background-position-y: 50%;
              ">
              <span class="absolute inset-y-0 w-full h-full bg-center bg-cover bg-gradient-to-tl from-purple-700 to-pink-500 opacity-60"></span>
            </div>
            
            <div class="relative flex flex-col flex-auto min-w-0 p-4 mx-3 -mt-16 overflow-hidden break-words border-0 shadow-blur rounded-2xl bg-white/80 bg-clip-border backdrop-blur-2xl backdrop-saturate-200">
              <div class="flex flex-wrap -mx-3">
                <div class="flex-none w-auto max-w-full px-3">
                  <div class="text-base ease-soft-in-out h-18.5 w-18.5 relative inline-flex items-center justify-center rounded-xl text-white transition-all duration-200">
                    <img src="../assets2/img/bruce-mars.jpg" alt="profile_image" class="w-full shadow-soft-sm rounded-xl" />
                  </div>
                </div>
                <div class="flex-none w-auto max-w-full px-3 my-auto">
                  <div class="h-full">
                    <h5 class="mb-1"><?php echo htmlspecialchars($username); ?></h5>
                    <p class="mb-0 font-semibold leading-normal text-sm"><?php echo htmlspecialchars($role); ?></p>
                  </div>
                </div>
                <div class="w-full max-w-full px-3 mx-auto mt-4 sm:my-auto sm:mr-0 md:w-1/2 md:flex-none lg:w-4/12">
                  <div class="relative right-0">
                    <ul class="relative flex flex-wrap p-1 list-none bg-transparent rounded-xl" nav-pills role="tablist">
                      <li class="z-30 flex-auto text-center">
                        <a class="z-30 block w-full px-0 py-1 mb-0 transition-all border-0 rounded-lg ease-soft-in-out bg-inherit text-slate-700" nav-link active href="javascript:;" role="tab" aria-selected="true">
                          <i class="fas fa-user text-slate-700"></i>
                          <span class="ml-1">Profile</span>
                        </a>
                      </li>
                      <li class="z-30 flex-auto text-center">
                        <a class="z-30 block w-full px-0 py-1 mb-0 transition-all border-0 rounded-lg ease-soft-in-out bg-inherit text-slate-700" nav-link href="javascript:;" role="tab" aria-selected="false">
                          <i class="fas fa-cog text-slate-700"></i>
                          <span class="ml-1">Settings</span>
                        </a>
                      </li>
                      <li class="z-30 flex-auto text-center">
                        <a class="z-30 block w-full px-0 py-1 mb-0 transition-colors border-0 rounded-lg ease-soft-in-out bg-inherit text-slate-700" nav-link href="javascript:;" role="tab" aria-selected="false">
                          <i class="fas fa-bell text-slate-700"></i>
                          <span class="ml-1">Notifications</span>
                        </a>
                      </li>
                    </ul>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <div class="w-full p-3 mx-auto">
            <div class="flex flex-wrap -mx-3">
              <!-- Card: Aktivitas Profile -->
              <div class="w-full max-w-full px-3 mb-4 xl:w-4/12">
                <div class="relative flex flex-col h-full min-w-0 break-words bg-white border-0 shadow-soft-xl rounded-2xl bg-clip-border">
                  <div class="p-4 pb-0 mb-0 bg-white border-b-0 rounded-t-2xl">
                    <h6 class="mb-0">Aktivitas Profile</h6>
                    <p class="text-xs text-slate-500 mt-1">5 Aktivitas Terakhir</p>
                  </div>
                  <div class="flex-auto p-4">
                    <div class="space-y-3">
                      <?php foreach ($aktivitas_terakhir as $aktivitas): 
                        // Tentukan icon dan badge berdasarkan jenis aktivitas
                        $icon = '';
                        $color = '';
                        $badge_class = '';
                        switch ($aktivitas['jenis']) {
                          case 'Login':
                            $icon = 'fas fa-sign-in-alt';
                            $color = 'text-green-500';
                            $badge_class = 'badge-login';
                            break;
                          case 'Logout':
                            $icon = 'fas fa-sign-out-alt';
                            $color = 'text-red-500';
                            $badge_class = 'badge-logout';
                            break;
                          case 'Edit':
                            $icon = 'fas fa-edit';
                            $color = 'text-blue-500';
                            $badge_class = 'badge-edit';
                            break;
                          case 'Update':
                            $icon = 'fas fa-sync-alt';
                            $color = 'text-purple-500';
                            $badge_class = 'badge-update';
                            break;
                          case 'Create':
                            $icon = 'fas fa-plus';
                            $color = 'text-teal-500';
                            $badge_class = 'badge-update';
                            break;
                          case 'Delete':
                            $icon = 'fas fa-trash';
                            $color = 'text-red-500';
                            $badge_class = 'badge-logout';
                            break;
                          default:
                            $icon = 'fas fa-history';
                            $color = 'text-gray-500';
                            $badge_class = 'badge-edit';
                        }
                        
                        // Format waktu
                        $waktu = date('d M Y, H:i', strtotime($aktivitas['waktu']));
                      ?>
                        <div class="aktivitas-item flex items-start space-x-3 p-2 rounded-lg hover:bg-slate-50">
                          <div class="flex-shrink-0 mt-1">
                            <i class="<?php echo $icon . ' ' . $color; ?> text-sm"></i>
                          </div>
                          <div class="flex-1 min-w-0">
                            <div class="flex items-center mb-1">
                              <span class="badge-jabatan <?php echo $badge_class; ?> mr-2">
                                <?php echo htmlspecialchars($aktivitas['jenis']); ?>
                              </span>
                              <p class="text-xs text-slate-500">
                                <?php echo htmlspecialchars($aktivitas['entitas']); ?>
                              </p>
                            </div>
                            <p class="text-sm text-slate-700 mb-1">
                              <?php echo htmlspecialchars($aktivitas['keterangan']); ?>
                            </p>
                            <p class="text-xs text-slate-400">
                              <i class="far fa-clock mr-1"></i><?php echo $waktu; ?>
                            </p>
                          </div>
                        </div>
                        <?php if ($aktivitas !== end($aktivitas_terakhir)): ?>
                          <hr class="my-2 bg-gradient-to-r from-transparent via-slate-200 to-transparent" />
                        <?php endif; ?>
                      <?php endforeach; ?>
                    </div>
                    
                    <!-- Tombol Lihat Semua Aktivitas -->
                    <div class="mt-4 text-center">
                      <a href="javascript:;" class="inline-block px-4 py-2 text-xs font-bold leading-normal text-center text-white align-middle transition-all ease-in bg-slate-700 border-0 rounded-lg shadow-md cursor-pointer hover:-translate-y-px hover:shadow-xs active:opacity-85">
                        Lihat Semua Aktivitas
                      </a>
                    </div>
                  </div>
                </div>
              </div>

              <!-- Card: Profile Information -->
              <div class="w-full max-w-full px-3 mb-4 lg-max:mt-6 xl:w-4/12">
                <div class="relative flex flex-col h-full min-w-0 break-words bg-white border-0 shadow-soft-xl rounded-2xl bg-clip-border">
                  <div class="p-4 pb-0 mb-0 bg-white border-b-0 rounded-t-2xl">
                    <div class="flex flex-wrap -mx-3">
                      <div class="flex items-center w-full max-w-full px-3 shrink-0 md:w-8/12 md:flex-none">
                        <h6 class="mb-0">Informasi Profile</h6>
                      </div>
                      <div class="w-full max-w-full px-3 text-right shrink-0 md:w-4/12 md:flex-none">
                        <a href="javascript:;" data-target="tooltip_trigger" data-placement="top">
                          <i class="leading-normal fas fa-user-edit text-sm text-slate-400"></i>
                        </a>
                      </div>
                    </div>
                  </div>
                  <div class="flex-auto p-4">
                    <p class="leading-normal text-sm">
                      Hi, I'm <?php echo htmlspecialchars($username); ?>, <?php echo htmlspecialchars($role); ?> di EyeThica Klinik. Bertanggung jawab dalam mengelola sistem antrian dan administrasi pasien.
                    </p>
                    <hr class="h-px my-6 bg-transparent bg-gradient-to-r from-transparent via-white to-transparent" />
                    <ul class="flex flex-col pl-0 mb-0 rounded-lg">
                      <li class="relative block px-4 py-2 pt-0 pl-0 leading-normal bg-white border-0 rounded-t-lg text-sm text-inherit">
                        <strong class="text-slate-700">Nama Lengkap:</strong> &nbsp; <?php echo htmlspecialchars($username); ?>
                      </li>
                      <li class="relative block px-4 py-2 pl-0 leading-normal bg-white border-0 border-t-0 text-sm text-inherit">
                        <strong class="text-slate-700">Jabatan:</strong> &nbsp; <?php echo htmlspecialchars($role); ?>
                      </li>
                      <li class="relative block px-4 py-2 pl-0 leading-normal bg-white border-0 border-t-0 text-sm text-inherit">
                        <strong class="text-slate-700">Email:</strong> &nbsp; <?php echo htmlspecialchars(strtolower(str_replace(' ', '.', $username)) . '@eyethica.com'); ?>
                      </li>
                      <li class="relative block px-4 py-2 pl-0 leading-normal bg-white border-0 border-t-0 text-sm text-inherit">
                        <strong class="text-slate-700">Lokasi:</strong> &nbsp; EyeThica Klinik
                      </li>
                      <li class="relative block px-4 py-2 pb-0 pl-0 bg-white border-0 border-t-0 rounded-b-lg text-inherit">
                        <strong class="leading-normal text-sm text-slate-700">Bergabung:</strong> &nbsp;
                        <span class="text-sm text-slate-600">Januari 2024</span>
                      </li>
                    </ul>
                  </div>
                </div>
              </div>

              <!-- Card: Rekan Kerja -->
              <div class="w-full max-w-full px-3 mb-4 lg-max:mt-6 xl:w-4/12">
                <div class="relative flex flex-col h-full min-w-0 break-words bg-white border-0 shadow-soft-xl rounded-2xl bg-clip-border">
                  <div class="p-4 pb-0 mb-0 bg-white border-b-0 rounded-t-2xl">
                    <h6 class="mb-0">Rekan Kerja</h6>
                    <p class="text-xs text-slate-500 mt-1">Staff dan Dokter Klinik</p>
                  </div>
                  <div class="flex-auto p-4">
                    <ul class="flex flex-col pl-0 mb-0 rounded-lg">
                      <?php foreach ($rekan_kerja as $index => $rekan): 
                        $is_dokter = strpos($rekan['jabatan'], 'Dokter') !== false || strpos($rekan['jabatan'], 'Spesialis') !== false;
                        $badge_class = $is_dokter ? 'badge-dokter' : 'badge-staff';
                        $icon_class = $is_dokter ? 'icon-dokter' : 'icon-staff';
                      ?>
                        <li class="relative flex items-center px-0 py-3 mb-2 bg-white border-0 <?php echo $index === 0 ? 'rounded-t-lg' : ''; ?> text-inherit">
                          <div class="inline-flex items-center justify-center w-12 h-12 mr-4 text-white transition-all duration-200 text-base ease-soft-in-out rounded-xl">
                            <img src="<?php echo htmlspecialchars($rekan['foto']); ?>" alt="<?php echo htmlspecialchars($rekan['nama']); ?>" class="w-full shadow-soft-2xl rounded-xl" />
                          </div>
                          <div class="flex flex-col items-start justify-center flex-1">
                            <div class="flex items-center mb-1">
                              <i class="fas <?php echo $is_dokter ? 'fa-user-md' : 'fa-user-tie'; ?> text-xs <?php echo $icon_class; ?> mr-2"></i>
                              <h6 class="mb-0 leading-normal text-sm font-semibold"><?php echo htmlspecialchars($rekan['nama']); ?></h6>
                            </div>
                            <span class="badge-jabatan <?php echo $badge_class; ?>">
                              <?php echo htmlspecialchars($rekan['jabatan']); ?>
                            </span>
                          </div>
                          <a class="inline-block py-2 px-3 mb-0 ml-2 font-bold text-center uppercase align-middle transition-all bg-transparent border-0 rounded-lg shadow-none cursor-pointer leading-pro text-xs ease-soft-in hover:scale-102 hover:active:scale-102 active:opacity-85 text-slate-600 hover:text-slate-800 hover:shadow-none active:scale-100" href="javascript:;" title="Kirim Pesan">
                            <i class="fas fa-comment text-sm"></i>
                          </a>
                        </li>
                        <?php if ($index < count($rekan_kerja) - 1): ?>
                          <hr class="my-1 bg-transparent bg-gradient-to-r from-transparent via-slate-200 to-transparent" />
                        <?php endif; ?>
                      <?php endforeach; ?>
                    </ul>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
        <!-- End Tailwind Content Section -->

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

    <!-- Tailwind JS -->
    <script src="../assets2/js/plugins/perfect-scrollbar.min.js"></script>
    <script src="../assets2/js/soft-ui-dashboard-tailwind.js?v=1.0.5"></script>

    <script>
      // Pastikan konten terload dengan baik
      document.addEventListener('DOMContentLoaded', function() {
        console.log('Profile page loaded successfully');
        
        // Tambahkan efek hover pada item aktivitas
        const aktivitasItems = document.querySelectorAll('.aktivitas-item');
        aktivitasItems.forEach(item => {
          item.addEventListener('mouseenter', function() {
            this.style.transform = 'translateX(5px)';
          });
          item.addEventListener('mouseleave', function() {
            this.style.transform = 'translateX(0)';
          });
        });
      });
    </script>
  </body>
</html>