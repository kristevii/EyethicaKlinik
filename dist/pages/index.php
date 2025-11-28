<?php
session_start();

include "koneksi.php";
$db = new database();

function formatRupiah($angka) {
    return 'Rp ' . number_format($angka, 0, ',', '.');
}

$jumlahdata_users = $db->jumlahdata_users();
$jumlahdata_dokter = $db->jumlahdata_dokter();
$jumlahdata_staff = $db->jumlahdata_staff();
$jumlahdata_pasien = $db->jumlahdata_pasien();
$jumlahdata_antrian = $db->jumlahdata_antrian();
$jumlahdata_rekam = $db->jumlahdata_rekam();
$jumlahdata_kontrol = $db->jumlahdata_kontrol();
$jumlahdata_transaksi = $db->jumlahdata_transaksi();
$total_pendapatan_sukses = $db->jumlahtransaksisukses();
$total_pendapatan_terjeda = $db->jumlahtransaksiterjeda();
?>

<!DOCTYPE html>
<html lang="en">
  <!-- [Head] start -->

  <head>
    <title>Dashboard - EyeThica Klinik</title>
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

<script src="../assets/js/plugins/apexcharts.min.js"></script>
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
                  <li class="breadcrumb-item" aria-current="page">Home</li>
                </ul>
              </div>
              <div class="col-md-12">
                <div class="page-header-title">
                  <h2 class="mb-0">Home</h2>
                </div>
              </div>
            </div>
          </div>
        </div>
        <!-- [ breadcrumb ] end -->
        <!-- [ Main Content ] start -->
        <div class="row">
          <div class="col-md-6 col-xxl-3">
            <div class="card">
              <div class="card-body">
                <div class="d-flex align-items-center">
                  <div class="flex-shrink-0">
                    <div class="avtar avtar-s bg-light-primary">
                      <i class="ti ti-users" style="font-size: 25px;"></i>
                    </div>
                  </div>
                  <div class="flex-grow-1 ms-3">
                    <h6 class="mb-0">Data Users</h6>
                  </div>
                </div>
                <div class="bg-body p-3 mt-3 rounded">
                  <div class="mt-3 row align-items-center">
                    <div class="col-7">
                      <div id="all-earnings-graph"></div>
                    </div>
                    <div class="col-5">
                      <h5 class="mb-1"><?php echo $jumlahdata_users; ?></h5>
                      <p class="text-primary mb-0"><i class="ti ti-arrow-up-right"></i> 30.6%</p>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
          <div class="col-md-6 col-xxl-3">
            <div class="card">
              <div class="card-body">
                <div class="d-flex align-items-center">
                  <div class="flex-shrink-0">
                    <div class="avtar avtar-s bg-light-warning">
                      <i class="ti ti-users" style="font-size: 25px;"></i>
                    </div>
                  </div>
                  <div class="flex-grow-1 ms-3">
                    <h6 class="mb-0">Data Dokter</h6>
                  </div>
                </div>
                <div class="bg-body p-3 mt-3 rounded">
                  <div class="mt-3 row align-items-center">
                    <div class="col-7">
                      <div id="page-views-graph"></div>
                    </div>
                    <div class="col-5">
                      <h5 class="mb-1"><?php echo $jumlahdata_dokter; ?></h5>
                      <p class="text-warning mb-0"><i class="ti ti-arrow-up-right"></i> 30.6%</p>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
          <div class="col-md-6 col-xxl-3">
            <div class="card">
              <div class="card-body">
                <div class="d-flex align-items-center">
                  <div class="flex-shrink-0">
                    <div class="avtar avtar-s bg-light-success">
                      <i class="ti ti-users" style="font-size: 25px;"></i>
                    </div>
                  </div>
                  <div class="flex-grow-1 ms-3">
                    <h6 class="mb-0">Data Staff</h6>
                  </div>
                </div>
                <div class="bg-body p-3 mt-3 rounded">
                  <div class="mt-3 row align-items-center">
                    <div class="col-7">
                      <div id="total-task-graph"></div>
                    </div>
                    <div class="col-5">
                      <h5 class="mb-1"><?php echo $jumlahdata_staff; ?></h5>
                      <p class="text-success mb-0"><i class="ti ti-arrow-up-right"></i> New</p>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
          <div class="col-md-6 col-xxl-3">
            <div class="card">
              <div class="card-body">
                <div class="d-flex align-items-center">
                  <div class="flex-shrink-0">
                    <div class="avtar avtar-s bg-light-warning">
                      <i class="ti ti-users" style="font-size: 25px;"></i>
                    </div>
                  </div>
                  <div class="flex-grow-1 ms-3">
                    <h6 class="mb-0">Data Pasien</h6>
                  </div>
                </div>
                <div class="bg-body p-3 mt-3 rounded">
                  <div class="mt-3 row align-items-center">
                    <div class="col-7">
                      <div id="download-graph"></div>
                    </div>
                    <div class="col-5">
                      <h5 class="mb-1"><?php echo $jumlahdata_pasien; ?></h5>
                      <p class="text-danger mb-0"><i class="ti ti-arrow-up-right"></i> 30.6%</p>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
          <div class="col-md-6 col-xxl-3">
            <div class="card">
              <div class="card-body">
                <div class="d-flex align-items-center">
                  <div class="flex-shrink-0">
                    <div class="avtar avtar-s bg-light-primary">
                      <i class="ti ti-report" style="font-size: 25px;"></i>
                    </div>
                  </div>
                  <div class="flex-grow-1 ms-3">
                    <h6 class="mb-0">Data Antrian</h6>
                  </div>
                </div>
                <div class="bg-body p-3 mt-3 rounded">
                  <div class="mt-3 row align-items-center">
                    <div class="col-7">
                      <div id="all-earnings-graph"></div>
                    </div>
                    <div class="col-5">
                      <h5 class="mb-1"><?php echo $jumlahdata_antrian; ?></h5>
                      <p class="text-primary mb-0"><i class="ti ti-arrow-up-right"></i> 30.6%</p>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
          <div class="col-md-6 col-xxl-3">
            <div class="card">
              <div class="card-body">
                <div class="d-flex align-items-center">
                  <div class="flex-shrink-0">
                    <div class="avtar avtar-s bg-light-warning">
                      <i class="ti ti-report-medical" style="font-size: 25px;"></i>
                    </div>
                  </div>
                  <div class="flex-grow-1 ms-3">
                    <h6 class="mb-0">Data Rekam Medis</h6>
                  </div>
                </div>
                <div class="bg-body p-3 mt-3 rounded">
                  <div class="mt-3 row align-items-center">
                    <div class="col-7">
                      <div id="all-earnings-graph"></div>
                    </div>
                    <div class="col-5">
                      <h5 class="mb-1"><?php echo $jumlahdata_rekam; ?></h5>
                      <p class="text-primary mb-0"><i class="ti ti-arrow-up-right"></i> 30.6%</p>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
          <div class="col-md-6 col-xxl-3">
            <div class="card">
              <div class="card-body">
                <div class="d-flex align-items-center">
                  <div class="flex-shrink-0">
                    <div class="avtar avtar-s bg-light-warning">
                      <i class="ti ti-report-medical" style="font-size: 25px;"></i>
                    </div>
                  </div>
                  <div class="flex-grow-1 ms-3">
                    <h6 class="mb-0">Data Kontrol</h6>
                  </div>
                </div>
                <div class="bg-body p-3 mt-3 rounded">
                  <div class="mt-3 row align-items-center">
                    <div class="col-7">
                      <div id="page-views-graph"></div>
                    </div>
                    <div class="col-5">
                      <h5 class="mb-1"><?php echo $jumlahdata_kontrol; ?></h5>
                      <p class="text-warning mb-0"><i class="ti ti-arrow-up-right"></i> 30.6%</p>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
          <div class="col-md-6 col-xxl-3">
            <div class="card">
              <div class="card-body">
                <div class="d-flex align-items-center">
                  <div class="flex-shrink-0">
                    <div class="avtar avtar-s bg-light-success">
                      <i class="ti ti-report-money" style="font-size: 25px;"></i>
                    </div>
                  </div>
                  <div class="flex-grow-1 ms-3">
                    <h6 class="mb-0">Data Transaksi</h6>
                  </div>
                </div>
                <div class="bg-body p-3 mt-3 rounded">
                  <div class="mt-3 row align-items-center">
                    <div class="col-7">
                      <div id="page-views-graph"></div>
                    </div>
                    <div class="col-5">
                      <h5 class="mb-1"><?php echo $jumlahdata_transaksi; ?></h5>
                      <p class="text-warning mb-0"><i class="ti ti-arrow-up-right"></i> 30.6%</p>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
          <div class="col-lg-9">
            <div class="card">
              <div class="card-header">
                <h5 class="mb-0">Monthly Revenue</h5>
              </div>
              <div class="card-body">
                <h5 class="text-end my-2">5.44% <span class="badge bg-success">+2.6%</span> </h5>
                <div id="customer-rate-graph"></div>
              </div>
            </div>
          </div>
          <div class="col-lg-3">
            <div class="card">
              <div class="card-header">
                <h5 class="mb-0">Project - Able Pro</h5>
              </div>
              <div class="card-body">
                <div class="mb-4">
                  <p class="mb-2">Release v1.2.0<span class="float-end">70%</span></p>
                  <div class="progress progress-primary" style="height: 8px">
                    <div class="progress-bar" style="width: 70%"></div>
                  </div>
                </div>
                <div class="d-grid gap-2">
                  <a href="#" class="btn btn-link-secondary">
                    <div class="d-flex align-items-center">
                      <div class="flex-shrink-0">
                        <span class="p-1 d-block bg-warning rounded-circle">
                          <span class="visually-hidden">New alerts</span>
                        </span>
                      </div>
                      <div class="flex-grow-1 mx-2">
                        <p class="mb-0 d-grid text-start">
                          <span class="text-truncate w-100">Horizontal Layout</span>
                        </p>
                      </div>
                      <div class="badge bg-light-secondary f-12"><i class="ti ti-paperclip text-sm"></i> 2</div>
                    </div>
                  </a>
                  <a href="#" class="btn btn-link-secondary">
                    <div class="d-flex align-items-center">
                      <div class="flex-shrink-0">
                        <span class="p-1 d-block bg-warning rounded-circle">
                          <span class="visually-hidden">New alerts</span>
                        </span>
                      </div>
                      <div class="flex-grow-1 mx-2">
                        <p class="mb-0 d-grid text-start">
                          <span class="text-truncate w-100">Invoice Generator</span>
                        </p>
                      </div>
                    </div>
                  </a>
                  <a href="#" class="btn btn-link-secondary">
                    <div class="d-flex align-items-center">
                      <div class="flex-shrink-0">
                        <span class="p-1 d-block bg-warning rounded-circle">
                          <span class="visually-hidden">New alerts</span>
                        </span>
                      </div>
                      <div class="flex-grow-1 mx-2">
                        <p class="mb-0 d-grid text-start">
                          <span class="text-truncate w-100">Package Upgrades</span>
                        </p>
                      </div>
                    </div>
                  </a>
                  <a href="#" class="btn btn-link-secondary">
                    <div class="d-flex align-items-center">
                      <div class="flex-shrink-0">
                        <span class="p-1 d-block bg-success rounded-circle">
                          <span class="visually-hidden">New alerts</span>
                        </span>
                      </div>
                      <div class="flex-grow-1 mx-2">
                        <p class="mb-0 d-grid text-start">
                          <span class="text-truncate w-100">Figma Auto Layout</span>
                        </p>
                      </div>
                    </div>
                  </a>
                </div>
                <div class="d-grid mt-3">
                  <button class="btn btn-primary d-flex align-items-center justify-content-center"
                    ><i class="ti ti-plus"></i> Add task</button
                  >
                </div>
              </div>
            </div>
          </div>
          <div class="col-lg-7">
            <div class="card">
              <div class="card-body">
                <div class="d-flex align-items-center justify-content-between">
                  <h5 class="mb-0">Project overview</h5>
                  <div class="dropdown">
                    <a
                      class="avtar avtar-s btn-link-secondary dropdown-toggle arrow-none"
                      href="#"
                      data-bs-toggle="dropdown"
                      aria-haspopup="true"
                      aria-expanded="false"
                    >
                      <i class="ti ti-dots f-18"></i>
                    </a>
                    <div class="dropdown-menu dropdown-menu-end">
                      <a class="dropdown-item" href="#">Today</a>
                      <a class="dropdown-item" href="#">Weekly</a>
                      <a class="dropdown-item" href="#">Monthly</a>
                    </div>
                  </div>
                </div>
                <div class="row align-items-center justify-content-center">
                  <div class="col-md-6 col-xl-4">
                    <div class="mt-3 row align-items-center">
                      <div class="col-6">
                        <p class="text-muted mb-1">Total Tasks</p>
                        <h5 class="mb-0">34,686</h5>
                      </div>
                      <div class="col-6">
                        <div id="total-tasks-graph"></div>
                      </div>
                    </div>
                  </div>
                  <div class="col-md-6 col-xl-4">
                    <div class="mt-3 row align-items-center">
                      <div class="col-6">
                        <p class="text-muted mb-1">Pending Tasks</p>
                        <h5 class="mb-0">3,786</h5>
                      </div>
                      <div class="col-6">
                        <div id="pending-tasks-graph"></div>
                      </div>
                    </div>
                  </div>
                  <div class="col-md-6 col-xl-4">
                    <div class="mt-3 d-grid">
                      <button class="btn btn-primary d-flex align-items-center justify-content-center"
                        ><i class="ti ti-plus"></i> Add project</button
                      >
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
          <div class="col-lg-5">
            <div class="card">
              <div class="card-body">
                <div class="d-flex align-items-center">
                  <div class="flex-shrink-0">
                    <div class="avtar avtar-s bg-light-primary">
                      <i class="ti ti-at f-20"></i>
                    </div>
                  </div>
                  <div class="flex-grow-1 ms-3">
                    <h6 class="mb-0">Able pro</h6>
                    <small class="text-muted">@ableprodevelop</small>
                  </div>
                  <div class="dropdown">
                    <a
                      class="avtar avtar-s btn-link-secondary dropdown-toggle arrow-none"
                      href="#"
                      data-bs-toggle="dropdown"
                      aria-haspopup="true"
                      aria-expanded="false"
                    >
                      <i class="ti ti-dots-vertical f-18"></i>
                    </a>
                    <div class="dropdown-menu dropdown-menu-end">
                      <a class="dropdown-item" href="#">Today</a>
                      <a class="dropdown-item" href="#">Weekly</a>
                      <a class="dropdown-item" href="#">Monthly</a>
                    </div>
                  </div>
                </div>
                <div class="d-flex align-items-center justify-content-between mt-4">
                  <div class="user-group able-user-group">
                    <img src="../assets/images/user/avatar-1.jpg" alt="user-image" class="avtar" />
                    <img src="../assets/images/user/avatar-3.jpg" alt="user-image" class="avtar" />
                    <img src="../assets/images/user/avatar-4.jpg" alt="user-image" class="avtar" />
                    <img src="../assets/images/user/avatar-5.jpg" alt="user-image" class="avtar" />
                    <span class="avtar bg-light-primary text-primary text-sm">+2</span>
                  </div>
                  <a href="#" class="avtar avtar-s btn btn-primary rounded-circle">
                    <i class="ti ti-plus f-20"></i>
                  </a>
                </div>
              </div>
            </div>
          </div>
          <div class="col-md-6">
            <div class="card">
              <div class="card-body border-bottom pb-0">
                <div class="d-flex align-items-center justify-content-between">
                  <h5 class="mb-0">Transaksi</h5>
                </div>
                <ul class="nav nav-tabs analytics-tab" id="myTab" role="tablist">
                  <li class="nav-item" role="presentation">
                    <button
                      class="nav-link active"
                      id="analytics-tab-1"
                      data-bs-toggle="tab"
                      data-bs-target="#analytics-tab-1-pane"
                      type="button"
                      role="tab"
                      aria-controls="analytics-tab-1-pane"
                      aria-selected="true"
                      >Semua Transaksi</button
                    >
                  </li>
                  <li class="nav-item" role="presentation">
                    <button
                      class="nav-link"
                      id="analytics-tab-2"
                      data-bs-toggle="tab"
                      data-bs-target="#analytics-tab-2-pane"
                      type="button"
                      role="tab"
                      aria-controls="analytics-tab-2-pane"
                      aria-selected="false"
                      >Sukses</button
                    >
                  </li>
                  <li class="nav-item" role="presentation">
                    <button
                      class="nav-link"
                      id="analytics-tab-3"
                      data-bs-toggle="tab"
                      data-bs-target="#analytics-tab-3-pane"
                      type="button"
                      role="tab"
                      aria-controls="analytics-tab-3-pane"
                      aria-selected="false"
                      >Terjeda</button
                    >
                  </li>
                </ul>
              </div>
              <div class="tab-content" id="myTabContent">
                <!-- Tab Semua Transaksi -->
                <div
                  class="tab-pane fade show active"
                  id="analytics-tab-1-pane"
                  role="tabpanel"
                  aria-labelledby="analytics-tab-1"
                  tabindex="0"
                >
                  <ul class="list-group list-group-flush">
                    <?php
                    $transaksi_semua = $db->tampil_5_transaksi_terakhir();
                    if (empty($transaksi_semua)): ?>
                      <li class="list-group-item text-center text-muted">
                        Tidak ada data transaksi
                      </li>
                    <?php else: ?>
                      <?php foreach ($transaksi_semua as $transaksi): ?>
                        <li class="list-group-item">
                          <div class="d-flex align-items-center">
                            <div class="flex-shrink-0">
                              <div class="avtar avtar-s border"> 
                                <?php echo strtoupper(substr($transaksi['nama_pasien'], 0, 2)); ?>
                              </div>
                            </div>
                            <div class="flex-grow-1 ms-3">
                              <div class="row g-1">
                                <div class="col-6">
                                  <h6 class="mb-0"><?php echo htmlspecialchars($transaksi['nama_pasien']); ?></h6>
                                  <p class="text-muted mb-0">
                                    <small><?php echo date('d M Y', strtotime($transaksi['tanggal_transaksi'])); ?></small>
                                  </p>
                                </div>
                                <div class="col-6 text-end">
                                  <h6 class="mb-1">Rp <?php echo number_format($transaksi['total_biaya'], 0, ',', '.'); ?></h6>
                                  <p class="text-success mb-0">
                                    <i class="ti ti-arrow-up-right"></i> 
                                    <?php echo htmlspecialchars($transaksi['metode_pembayaran']); ?>
                                  </p>
                                </div>
                              </div>
                            </div>
                          </div>
                        </li>
                      <?php endforeach; ?>
                    <?php endif; ?>
                  </ul>
                </div>

                <!-- Tab Sukses (Lunas) -->
                <div class="tab-pane fade" id="analytics-tab-2-pane" role="tabpanel" aria-labelledby="analytics-tab-2" tabindex="0">
                  <ul class="list-group list-group-flush">
                    <?php
                    $transaksi_lunas = $db->tampil_5_transaksi_terakhir_lunas();
                    if (empty($transaksi_lunas)): ?>
                      <li class="list-group-item text-center text-muted">
                        Tidak ada transaksi sukses
                      </li>
                    <?php else: ?>
                      <?php foreach ($transaksi_lunas as $transaksi): ?>
                        <li class="list-group-item">
                          <div class="d-flex align-items-center">
                            <div class="flex-shrink-0">
                              <div class="avtar avtar-s border"> 
                                <?php echo strtoupper(substr($transaksi['nama_pasien'], 0, 2)); ?>
                              </div>
                            </div>
                            <div class="flex-grow-1 ms-3">
                              <div class="row g-1">
                                <div class="col-6">
                                  <h6 class="mb-0"><?php echo htmlspecialchars($transaksi['nama_pasien']); ?></h6>
                                  <p class="text-muted mb-0">
                                    <small><?php echo date('d M Y', strtotime($transaksi['tanggal_transaksi'])); ?></small>
                                  </p>
                                </div>
                                <div class="col-6 text-end">
                                  <h6 class="mb-1">Rp <?php echo number_format($transaksi['total_biaya'], 0, ',', '.'); ?></h6>
                                  <p class="text-success mb-0">
                                    <i class="ti ti-arrow-up-right"></i> 
                                    <?php echo htmlspecialchars($transaksi['metode_pembayaran']); ?>
                                  </p>
                                </div>
                              </div>
                            </div>
                          </div>
                        </li>
                      <?php endforeach; ?>
                    <?php endif; ?>
                  </ul>
                </div>

                <!-- Tab Terjeda (Belum Bayar) -->
                <div class="tab-pane fade" id="analytics-tab-3-pane" role="tabpanel" aria-labelledby="analytics-tab-3" tabindex="0">
                  <ul class="list-group list-group-flush">
                    <?php
                    $transaksi_belum_bayar = $db->tampil_5_transaksi_terakhir_belum_bayar();
                    if (empty($transaksi_belum_bayar)): ?>
                      <li class="list-group-item text-center text-muted">
                        Tidak ada transaksi terjeda
                      </li>
                    <?php else: ?>
                      <?php foreach ($transaksi_belum_bayar as $transaksi): ?>
                        <li class="list-group-item">
                          <div class="d-flex align-items-center">
                            <div class="flex-shrink-0">
                              <div class="avtar avtar-s border"> 
                                <?php echo strtoupper(substr($transaksi['nama_pasien'], 0, 2)); ?>
                              </div>
                            </div>
                            <div class="flex-grow-1 ms-3">
                              <div class="row g-1">
                                <div class="col-6">
                                  <h6 class="mb-0"><?php echo htmlspecialchars($transaksi['nama_pasien']); ?></h6>
                                  <p class="text-muted mb-0">
                                    <small><?php echo date('d M Y', strtotime($transaksi['tanggal_transaksi'])); ?></small>
                                  </p>
                                </div>
                                <div class="col-6 text-end">
                                  <h6 class="mb-1">Rp <?php echo number_format($transaksi['total_biaya'], 0, ',', '.'); ?></h6>
                                  <p class="text-danger mb-0">
                                    <i class="ti ti-arrow-down-left"></i> 
                                    <?php echo htmlspecialchars($transaksi['metode_pembayaran']); ?>
                                  </p>
                                </div>
                              </div>
                            </div>
                          </div>
                        </li>
                      <?php endforeach; ?>
                    <?php endif; ?>
                  </ul>
                </div>
              </div>
              <div class="card-footer">
                <div class="row g-2">
                  <div class="col-md-6">
                    <div class="d-grid">
                      <a href="datatransaksi.php" class="btn btn-outline-secondary d-grid">
                        <span class="text-truncate w-100">View all Transaction History</span>
                      </a>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
          <div class="col-md-6">
            <div class="card">
              <div class="card-body">
                <div class="d-flex align-items-center justify-content-between">
                  <h5 class="mb-0">Total Pendapatan Bulan ini</h5>
                </div>
                <div id="income-distribution-chart" style="min-height: 400px;"></div>
                <div class="row g-3 mt-3">
                  <div class="col-sm-6">
                    <div class="bg-body p-3 rounded">
                      <div class="d-flex align-items-center mb-2">
                        <div class="flex-shrink-0">
                          <span class="p-1 d-block bg-primary rounded-circle">
                            <span class="visually-hidden">New alerts</span>
                          </span>
                        </div>
                        <div class="flex-grow-1 ms-2">
                          <p class="mb-0">Pendapatan Transaksi Sukses</p>
                        </div>
                      </div>
                      <h6 class="mb-0"
                        ><?php echo formatRupiah($total_pendapatan_sukses); ?> <small class="text-muted"><i class="ti ti-chevrons-up"></i> +$763,43</small></h6
                      >
                    </div>
                  </div>
                  <div class="col-sm-6">
                    <div class="bg-body p-3 rounded">
                      <div class="d-flex align-items-center mb-2">
                        <div class="flex-shrink-0">
                          <span class="p-1 d-block bg-danger rounded-circle">
                            <span class="visually-hidden">New alerts</span>
                          </span>
                        </div>
                        <div class="flex-grow-1 ms-2">
                          <p class="mb-0">Pendapatan Transaksi Terjeda</p>
                        </div>
                      </div>
                      <h6 class="mb-0"
                        ><?php echo formatRupiah($total_pendapatan_terjeda); ?> <small class="text-muted"><i class="ti ti-chevrons-up"></i> +$763,43</small></h6
                      >
                    </div>
                  </div>
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

    
    
    
    
    
    
    
    
    
    <script>change_box_container('false');</script>
    
    
    <script>layout_caption_change('true');</script>
    
    
    
    
    <script>layout_rtl_change('false');</script>
    
    
    <script>preset_change("preset-1");</script>
    
    <script>
document.addEventListener('DOMContentLoaded', function() {
    // Data dari PHP
    var pendapatanSukses = <?php echo $total_pendapatan_sukses; ?>;
    var pendapatanTerjeda = <?php echo $total_pendapatan_terjeda; ?>;
    var totalPendapatan = pendapatanSukses + pendapatanTerjeda;
    
    // Hitung persentase
    var persenSukses = totalPendapatan > 0 ? (pendapatanSukses / totalPendapatan * 100) : 0;
    var persenTerjeda = totalPendapatan > 0 ? (pendapatanTerjeda / totalPendapatan * 100) : 0;
    
    // Format angka untuk tooltip
    var formatter = new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 0
    });

    // Options untuk chart
    var options = {
        series: [persenSukses, persenTerjeda],
        chart: {
            type: 'donut',
            height: 400
        },
        colors: ['#4680ff', '#dc2626'], // Hijau untuk sukses, orange untuk terjeda
        labels: ['Transaksi Sukses', 'Transaksi Terjeda'],
        dataLabels: {
            enabled: true,
            formatter: function (val) {
                return val.toFixed(1) + "%";
            },
            dropShadow: {
                enabled: false
            }
        },
        legend: {
            position: 'bottom',
            horizontalAlign: 'center'
        },
        plotOptions: {
            pie: {
                donut: {
                    size: '65%',
                    labels: {
                        show: true,
                        name: {
                            show: true,
                            fontSize: '14px',
                            fontWeight: 600,
                            color: '#6B7280'
                        },
                        value: {
                            show: true,
                            fontSize: '16px',
                            fontWeight: 700,
                            color: '#111827',
                            formatter: function (val) {
                                return formatter.format(totalPendapatan);
                            }
                        }
                    }
                }
            }
        },
        tooltip: {
            y: {
                formatter: function(value, { seriesIndex }) {
                    var amount = seriesIndex === 0 ? pendapatanSukses : pendapatanTerjeda;
                    return formatter.format(amount) + ' (' + value.toFixed(1) + '%)';
                }
            }
        },
        responsive: [{
            breakpoint: 480,
            options: {
                chart: {
                    height: 180
                },
                legend: {
                    position: 'bottom'
                }
            }
        }]
    };

    // Render chart
    var chart = new ApexCharts(document.querySelector("#income-distribution-chart"), options);
    chart.render();
});
</script>

  </body>
  <!-- [Body] end -->
</html>
