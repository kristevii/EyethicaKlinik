<?php
session_start();
include "koneksi.php";
$db = new database();

// Jika sudah login, redirect ke dashboard
if (isset($_SESSION['username']) && isset($_SESSION['role'])) {
    // Redirect sesuai role
    if ($_SESSION['role'] == 'Staff') {
        header("Location: ../index.php");
    } elseif ($_SESSION['role'] == 'Dokter') {
        header("Location: ../index.php");
    } 
    exit;
}

// Proses login jika ada data POST
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['username'])) {
    $username = $_POST['username'];
    $password = $_POST['password'];
    
    // Lakukan validasi login
    $users = $db->login($username, $password);
    
    if ($users) {
        $_SESSION['id_user'] = $users['id_user'];
        $_SESSION['nama'] = $users['nama'];
        $_SESSION['username'] = $users['username'];
        $_SESSION['role'] = $users['role'];
        
        // Redirect sesuai role
        if ($users['role'] == 'Staff') {
            header("Location: admin/index.php");
        } elseif ($users['role'] == 'Dokter') {
            header("Location: guru/index.php");
        }
        exit;
    } else {
        $error = "Login gagal! Username atau password salah.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <link
      rel="apple-touch-icon"
      sizes="76x76"
      href="../assets2/img/apple-icon.png" />
    <link rel="icon" type="image/png" href="../assets2/img/favicon.png" />
    <title>Login</title>
    <!-- Fonts and icons -->
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

    <!-- Nepcha Analytics (nepcha.com) -->
    <!-- Nepcha is a easy-to-use web analytics. No cookies and fully compliant with GDPR, CCPA and PECR. -->
    <script
      defer
      data-site="YOUR_DOMAIN_HERE"
      src="https://api.nepcha.com/js/nepcha-analytics.js"></script>
  </head>

  <body
    class="m-0 font-sans antialiased font-normal bg-white text-start text-base leading-default text-slate-500">
    
    <main class="mt-0 transition-all duration-200 ease-soft-in-out">
      <section>
        
        <div
          class="relative flex items-center p-0 overflow-hidden bg-center bg-cover min-h-75-screen">
          
          <div class="container z-10">  
            <div class="flex flex-wrap mt-0 -mx-3">
              <div
                class="flex flex-col w-full max-w-full px-3 mx-auto md:flex-0 shrink-0 md:w-6/12 lg:w-5/12 xl:w-4/12">
                <div
                  class="relative flex flex-col min-w-0 mt-32 break-words bg-transparent border-0 shadow-none rounded-2xl bg-clip-border">
                  <div
                    class="p-6 pb-0 mb-0 bg-transparent border-b-0 rounded-t-2xl">
                    <h3
                      class="relative z-10 font-bold text-transparent bg-gradient-to-tl from-blue-600 to-cyan-400 bg-clip-text">
                      Selamat Datang kembali
                    </h3>
                    <p class="mb-0">Masukkan username dan password untuk masuk</p>
                  </div>
                  <div class="flex-auto p-6">
                    <form role="form">
                      <label class="mb-2 ml-1 font-bold text-xs text-slate-700"
                        >Username</label
                      >
                      <div class="mb-4">
                        <input
                          type="text"
                          class="focus:shadow-soft-primary-outline text-sm leading-5.6 ease-soft block w-full appearance-none rounded-lg border border-solid border-gray-300 bg-white bg-clip-padding px-3 py-2 font-normal text-gray-700 transition-all focus:border-fuchsia-300 focus:outline-none focus:transition-shadow"
                          placeholder="Username"
                          aria-label="Username"
                          aria-describedby="username-addon" />
                      </div>
                      <label class="mb-2 ml-1 font-bold text-xs text-slate-700"
                        >Password</label
                      >
                      <div class="mb-4">
                        <input
                          type="password"
                          class="focus:shadow-soft-primary-outline text-sm leading-5.6 ease-soft block w-full appearance-none rounded-lg border border-solid border-gray-300 bg-white bg-clip-padding px-3 py-2 font-normal text-gray-700 transition-all focus:border-fuchsia-300 focus:outline-none focus:transition-shadow"
                          placeholder="Password"
                          aria-label="Password"
                          aria-describedby="password-addon" />
                      </div>
                      <div class="text-center">
                        <button
                          type="button"
                          class="inline-block w-full px-6 py-3 mt-6 mb-0 font-bold text-center text-white uppercase align-middle transition-all bg-transparent border-0 rounded-lg cursor-pointer shadow-soft-md bg-x-25 bg-150 leading-pro text-xs ease-soft-in tracking-tight-soft bg-gradient-to-tl from-blue-600 to-cyan-400 hover:scale-102 hover:shadow-soft-xs active:opacity-85">
                          Sign in
                        </button>
                      </div>
                    </form>
                  </div>
                </div>
              </div>
              <div class="w-full max-w-full px-3 lg:flex-0 shrink-0 md:w-6/12">
                <div
                  class="absolute top-0 hidden w-3/5 h-full -mr-32 overflow-hidden -skew-x-10 -right-40 rounded-bl-xl md:block">
                  <div
                    class="absolute inset-x-0 top-0 z-0 h-full -ml-16 bg-cover skew-x-10"
                    style="
                      background-image: url('../assets2/img/curved-images/curved6.jpg');
                    "></div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </section>
    </main>
  </body>
  <!-- plugin for scrollbar  -->
  <script src="../assets2/js/plugins/perfect-scrollbar.min.js" async></script>
  <!-- main script file  -->
  <script
    src="../assets2/js/soft-ui-dashboard-tailwind.js?v=1.0.5"
    async></script>
</html>
