<?php
class database {
    var $host = "localhost";
    var $username = "root";
    var $password = "";
    var $db = "poliklinik";
    public $koneksi; // Deklarasikan properti koneksi

    function __construct(){
        // Cek koneksi ke MySQL server
        $this->koneksi = mysqli_connect($this->host, $this->username, $this->password);

        if (mysqli_connect_errno()) {
            die("Koneksi database GAGAL: " . mysqli_connect_error());
        }

        // Cek pemilihan database
        $cekdb = mysqli_select_db($this->koneksi, $this->db);
        if (!$cekdb) {
            die("Database '{$this->db}' tidak ditemukan atau gagal dipilih.");
        }
    }

    // Metode untuk login dengan password plain text
    function login($username, $password) {
        // Lindungi input dari SQL Injection
        $username = mysqli_real_escape_string($this->koneksi, $username);
        $password = mysqli_real_escape_string($this->koneksi, $password);

        $query = "SELECT * FROM users WHERE username = '$username' AND password = '$password'";
        $result = mysqli_query($this->koneksi, $query); // Gunakan $this->koneksi

        if (!$result) {
            error_log("Login Query Error: " . mysqli_error($this->koneksi) . " Query: " . $query);
            return false;
        }

        if (mysqli_num_rows($result) == 1) {
            return mysqli_fetch_assoc($result); // Login berhasil, kembalikan data user
        }
        return false; // User tidak ditemukan atau password salah
    }

    // --- Function Jumlah Data di Dashboard ---
    function jumlahdata_users(){
        $data = mysqli_query($this->koneksi, "SELECT COUNT(*) as total from users");
        if (!$data) {
            error_log("Query error jumlahdata_users: " . mysqli_error($this->koneksi));
            return 0;
        }
        $hasil = mysqli_fetch_assoc($data);
        return $hasil['total'];
    }
    function jumlahdata_dokter(){
        $data = mysqli_query($this->koneksi, "SELECT COUNT(*) as total from data_dokter");
        if (!$data) {
            error_log("Query error jumlahdata_dokter: " . mysqli_error($this->koneksi));
            return 0;
        }
        $hasil = mysqli_fetch_assoc($data);
        return $hasil['total'];
    }
    function jumlahdata_staff(){
        $data = mysqli_query($this->koneksi, "SELECT COUNT(*) as total from data_staff");
        if (!$data) {
            error_log("Query error jumlahdata_staff: " . mysqli_error($this->koneksi));
            return 0;
        }
        $hasil = mysqli_fetch_assoc($data);
        return $hasil['total'];
    }
    function jumlahdata_pasien(){
        $data = mysqli_query($this->koneksi, "SELECT COUNT(*) as total from data_pasien");
        if (!$data) {
            error_log("Query error jumlahdata_pasien: " . mysqli_error($this->koneksi));
            return 0;
        }
        $hasil = mysqli_fetch_assoc($data);
        return $hasil['total'];
    }
    function jumlahdata_antrian(){
        $data = mysqli_query($this->koneksi, "SELECT COUNT(*) as total from data_antrian");
        if (!$data) {
            error_log("Query error jumlahdata_antrian: " . mysqli_error($this->koneksi));
            return 0;
        }
        $hasil = mysqli_fetch_assoc($data);
        return $hasil['total'];
    }
    function jumlahdata_rekam(){
        $data = mysqli_query($this->koneksi, "SELECT COUNT(*) as total from data_rekam_medis");
        if (!$data) {
            error_log("Query error jumlahdata_rekam: " . mysqli_error($this->koneksi));
            return 0;
        }
        $hasil = mysqli_fetch_assoc($data);
        return $hasil['total'];
    }
    function jumlahdata_kontrol(){
        $data = mysqli_query($this->koneksi, "SELECT COUNT(*) as total from data_kontrol");
        if (!$data) {
            error_log("Query error jumlahdata_kontrol: " . mysqli_error($this->koneksi));
            return 0;
        }
        $hasil = mysqli_fetch_assoc($data);
        return $hasil['total'];
    }
    function jumlahdata_transaksi(){
        $data = mysqli_query($this->koneksi, "SELECT COUNT(*) as total from data_transaksi");
        if (!$data) {
            error_log("Query error jumlahdata_transaksi: " . mysqli_error($this->koneksi));
            return 0;
        }
        $hasil = mysqli_fetch_assoc($data);
        return $hasil['total'];
    }

    // --- Function Tampil Data ---
    function tampil_data_users(){
        $hasil = [];
        $data = mysqli_query($this->koneksi, "select id_user, role, nama, username, password from users");
        if (!$data) {
            error_log("Query error tampil_data_users: " . mysqli_error($this->koneksi));
            return [];
        }
        while ($row = mysqli_fetch_array($data)){
            $hasil[] = $row;
        }
        return $hasil;
    }

    function tampil_data_dokter(){
        $hasil = [];
        $data = mysqli_query($this->koneksi, "select id_user, kode_dokter, spesialisasi_dokter, foto_dokter, nama_dokter, tanggal_lahir_dokter, jenis_kelamin_dokter, alamat_dokter, email_dokter, telepon_dokter, ruang from data_dokter");
        if (!$data) {
            error_log("Query error tampil_data_dokter: " . mysqli_error($this->koneksi));
            return [];
        }
        while ($row = mysqli_fetch_array($data)){
            $hasil[] = $row;
        }
        return $hasil;
    }

    function tampil_data_staff(){
        $hasil = [];
        // Menambahkan `id_bisnis` ke dalam query
        $data = mysqli_query($this->koneksi, "select id_user, kode_staff, jabatan_staff, foto_staff, nama_staff, jenis_kelamin_staff, tanggal_lahir_staff, alamat_staff, email_staff, telepon_staff from data_staff");
        if (!$data) {
            error_log("Query error tampil_data_staff: " . mysqli_error($this->koneksi));
            return [];
        }
        while ($row = mysqli_fetch_assoc($data)){ // Mengubah ke mysqli_fetch_assoc untuk konsistensi
            $hasil[] = $row;
        }
        return $hasil;
    }

    function tampil_data_pasien(){
        $hasil = [];
        $data = mysqli_query($this->koneksi, "select id_pasien, nama_pasien, jenis_kelamin_pasien, tgl_lahir_pasien, alamat_pasien, telepon_pasien, tanggal_registrasi_pasien from data_pasien");
        if (!$data) {
            error_log("Query error tampil_data_pasien: " . mysqli_error($this->koneksi));
            return [];
        }
        while ($row = mysqli_fetch_assoc($data)){
            $hasil[] = $row;
        }
        return $hasil;
    }

    function tampil_data_antrian(){
        $hasil = [];
        $data = mysqli_query($this->koneksi, "select id_antrian, nomor_antrian, id_pasien, kode_dokter, status, waktu_daftar from data_antrian");
        if (!$data) {
            error_log("Query error tampil_data_antrian: " . mysqli_error($this->koneksi));
            return [];
        }
        while ($row = mysqli_fetch_assoc($data)){
            $hasil[] = $row;
        }
        return $hasil;
    }

    function tampil_data_rekam_medis(){
        $hasil = [];
        $data = mysqli_query($this->koneksi, "select id_rekam, id_pasien, kode_dokter, tanggal_periksa, diagnosa, resep_obat, catatan, biaya, butuh_kontrol from data_rekam_medis");
        if (!$data) {
            error_log("Query error tampil_data_rekam_medis: " . mysqli_error($this->koneksi));
            return [];
        }
        while ($row = mysqli_fetch_assoc($data)){
            $hasil[] = $row;
        }
        return $hasil;
    }

    function tampil_data_kontrol(){
        $hasil = [];
        $data = mysqli_query($this->koneksi, "select id_kontrol, id_rekam, id_pasien, kode_dokter, tanggal_kontrol, keluhan, catatan, biaya, status_kontrol, create_at from data_kontrol");
        if (!$data) {
            error_log("Query error tampil_data_kontrol: " . mysqli_error($this->koneksi));
            return [];
        }
        while ($row = mysqli_fetch_assoc($data)){
            $hasil[] = $row;
        }
        return $hasil;
    }

    function tampil_data_jadwal_dokter(){
        $hasil = [];
        $data = mysqli_query($this->koneksi, "select id_jadwal, kode_dokter, hari, jam_mulai, jam_selesai, status from data_jadwal_dokter");
        if (!$data) {
            error_log("Query error tampil_data_jadwal_dokter: " . mysqli_error($this->koneksi));
            return [];
        }
        while ($row = mysqli_fetch_assoc($data)){
            $hasil[] = $row;
        }
        return $hasil;
    }
    
    function tampil_data_transaksi(){
        $hasil = [];
        $data = mysqli_query($this->koneksi, "select id_transaksi, id_rekam, id_kontrol, id_pasien, kode_staff, tanggal_transaksi, metode_pembayaran, total_biaya, status_pembayaran from data_transaksi");
        if (!$data) {
            error_log("Query error tampil_data_transaksi: " . mysqli_error($this->koneksi));
            return [];
        }
        while ($row = mysqli_fetch_assoc($data)){
            $hasil[] = $row;
        }
        return $hasil;
    }

    function tampil_5_transaksi_terakhir_lunas() {
        $hasil = [];
        $query = "SELECT 
                    dt.id_pasien,
                    p.nama_pasien,
                    dt.tanggal_transaksi,
                    dt.total_biaya,
                    dt.metode_pembayaran
                FROM data_transaksi dt
                LEFT JOIN data_pasien p ON dt.id_pasien = p.id_pasien
                WHERE dt.status_pembayaran = 'lunas' 
                ORDER BY dt.id_transaksi DESC 
                LIMIT 5";
        
        $data = mysqli_query($this->koneksi, $query);
        if (!$data) {
            error_log("Query error tampil_5_transaksi_terakhir_lunas: " . mysqli_error($this->koneksi));
            return [];
        }
        while ($row = mysqli_fetch_assoc($data)){
            $hasil[] = $row;
        }
        return $hasil;
    }

    function tampil_5_transaksi_terakhir_belum_bayar() {
        $hasil = [];
        $query = "SELECT 
                    dt.id_pasien,
                    p.nama_pasien,
                    dt.tanggal_transaksi,
                    dt.total_biaya,
                    dt.metode_pembayaran
                FROM data_transaksi dt
                LEFT JOIN data_pasien p ON dt.id_pasien = p.id_pasien
                WHERE dt.status_pembayaran = 'belum bayar' 
                ORDER BY dt.id_transaksi DESC 
                LIMIT 5";
        
        $data = mysqli_query($this->koneksi, $query);
        if (!$data) {
            error_log("Query error tampil_5_transaksi_terakhir_belum_bayar: " . mysqli_error($this->koneksi));
            return [];
        }
        while ($row = mysqli_fetch_assoc($data)){
            $hasil[] = $row;
        }
        return $hasil;
    }

    function tampil_5_transaksi_terakhir() {
        $hasil = [];
        $query = "SELECT 
                    dt.id_pasien,
                    p.nama_pasien,
                    dt.tanggal_transaksi,
                    dt.total_biaya,
                    dt.metode_pembayaran
                FROM data_transaksi dt
                LEFT JOIN data_pasien p ON dt.id_pasien = p.id_pasien
                ORDER BY dt.id_transaksi DESC 
                LIMIT 5";
        
        $data = mysqli_query($this->koneksi, $query);
        if (!$data) {
            error_log("Query error tampil_5_transaksi_terakhir: " . mysqli_error($this->koneksi));
            return [];
        }
        while ($row = mysqli_fetch_assoc($data)){
            $hasil[] = $row;
        }
        return $hasil;
    }

    function jumlahtransaksisukses() {
        $query = "SELECT 
                    SUM(dt.total_biaya) as total_pendapatan
                FROM data_transaksi dt
                WHERE dt.status_pembayaran = 'Lunas'";
        
        $data = mysqli_query($this->koneksi, $query);
        if (!$data) {
            error_log("Query error jumlahtransaksisukses: " . mysqli_error($this->koneksi));
            return 0;
        }
        
        $row = mysqli_fetch_assoc($data);
        return $row['total_pendapatan'] ? $row['total_pendapatan'] : 0;
    }

    function jumlahtransaksiterjeda() {
        $query = "SELECT 
                    SUM(dt.total_biaya) as total_pendapatan
                FROM data_transaksi dt
                WHERE dt.status_pembayaran = 'Belum bayar'";
        
        $data = mysqli_query($this->koneksi, $query);
        if (!$data) {
            error_log("Query error jumlahtransaksiterjeda: " . mysqli_error($this->koneksi));
            return 0;
        }
        
        $row = mysqli_fetch_assoc($data);
        return $row['total_pendapatan'] ? $row['total_pendapatan'] : 0;
    }
    
    function tampil_5_aktivitas_profile_terakhir($id_user){
        $hasil = [];
        
        // Lindungi dari SQL Injection
        $id_user = mysqli_real_escape_string($this->koneksi, $id_user);
        
        $query = "SELECT 
                    ap.id_user, 
                    u.nama as nama_user,
                    ap.jenis,   
                    ap.entitas, 
                    ap.keterangan, 
                    ap.waktu 
                FROM aktivitas_profile ap
                LEFT JOIN users u ON ap.id_user = u.id_user
                WHERE ap.id_user = '$id_user'
                ORDER BY ap.waktu DESC 
                LIMIT 5";
        
        $data = mysqli_query($this->koneksi, $query);
        if (!$data) {
            error_log("Query error tampil_5_aktivitas_profile_terakhir: " . mysqli_error($this->koneksi));
            return [];
        }
        while ($row = mysqli_fetch_assoc($data)){
            $hasil[] = $row;
        }
        return $hasil;
    }

    function tampil_5_aktivitas_user_terakhir(){
        $hasil = [];
        $query = "SELECT 
                    au.id_user, 
                    u.nama as nama_user,
                    au.jenis, 
                    au.entitas, 
                    au.keterangan, 
                    au.waktu 
                FROM aktivitas_user au
                LEFT JOIN users u ON au.id_user = u.id_user
                ORDER BY au.waktu DESC 
                LIMIT 5";
        
        $data = mysqli_query($this->koneksi, $query);
        if (!$data) {
            error_log("Query error tampil_5_aktivitas_user_terakhir: " . mysqli_error($this->koneksi));
            return [];
        }
        while ($row = mysqli_fetch_assoc($data)){
            $hasil[] = $row;
        }
        return $hasil;
    }

    // TAMBAH DATA
    function tambah_data_mitra($nama, $nomortelepon, $provinsi, $kabupaten, $kodepos, $alamat, $tgl_bergabung, $status, $status_en) {
        // Lindungi dari SQL Injection
        $nama = mysqli_real_escape_string($this->koneksi, $nama);
        $provinsi = mysqli_real_escape_string($this->koneksi, $provinsi);
        $kabupaten = mysqli_real_escape_string($this->koneksi, $kabupaten);
        $kodepos = mysqli_real_escape_string($this->koneksi, $kodepos);
        $alamat = mysqli_real_escape_string($this->koneksi, $alamat);
        $nomortelepon = mysqli_real_escape_string($this->koneksi, $nomortelepon);
        $tgl_bergabung = mysqli_real_escape_string($this->koneksi, $tgl_bergabung);
        $status = mysqli_real_escape_string($this->koneksi, $status);
        $status_en = mysqli_real_escape_string($this->koneksi, $status_en);

        $sql = "INSERT INTO mitra(nama, provinsi, kabupaten, kodepos, alamat, nomortelepon, tgl_bergabung, status, status_en) VALUES ('$nama', '$provinsi', '$kabupaten', '$kodepos', '$alamat', '$nomortelepon', '$tgl_bergabung', '$status', '$status_en')";
        $result = mysqli_query($this->koneksi, $sql);

        if ($result) {
            return true;
        } else {
            error_log("Error tambah_data_mitra: " . mysqli_error($this->koneksi) . " Query: " . $sql);
            return false;
        }
    }


    // EDIT DATA
    function edit_data_galeri($id_galeri, $kategori, $kategori_en, $gambar, $tanggal) {
        // Lindungi dari SQL Injection
        $id_galeri = mysqli_real_escape_string($this->koneksi, $id_galeri);
        $kategori = mysqli_real_escape_string($this->koneksi, $kategori);
        $kategori_en = mysqli_real_escape_string($this->koneksi, $kategori_en);
        $gambar = mysqli_real_escape_string($this->koneksi, $gambar);
        $tanggal = mysqli_real_escape_string($this->koneksi, $tanggal);

        $sql = "UPDATE galeri SET kategori = '$kategori', kategori_en = '$kategori_en',  gambar = '$gambar', tanggal= '$tanggal'  WHERE id_galeri = '$id_galeri'";
        $result = mysqli_query($this->koneksi, $sql);

        if ($result) {
            return true;
        } else {
            error_log("Error edit_data_galeri: " . mysqli_error($this->koneksi) . " Query: " . $sql);
            return false;
        }
    }


    // HAPUS DATA
    function hapus_data_mitra($id_mitra) {
        // Lindungi dari SQL Injection
        $id_mitra = mysqli_real_escape_string($this->koneksi, $id_mitra);

        $sql = "DELETE FROM mitra WHERE id_mitra = '$id_mitra'";
        $result = mysqli_query($this->koneksi, $sql);

        if ($result) {
            return true;
        } else {
            error_log("Error hapus_data_mitra: " . mysqli_error($this->koneksi) . " Query: " . $sql);
            return false;
        }
    }


    //edit data berita start
    function get_berita_by_id($id_berita) {
        $id = mysqli_real_escape_string($this->koneksi, $id_berita);
        $query = "SELECT * FROM berita WHERE id_berita = '$id' LIMIT 1";
        $result = mysqli_query($this->koneksi, $query);
        if ($result && mysqli_num_rows($result) > 0) {
            return mysqli_fetch_assoc($result);
        }
        return false;
    }

    function get_recent_activities($limit = 5) {
        $activities = [];
        $limit = (int)$limit;
        $query = "SELECT * FROM aktivitas ORDER BY waktu DESC LIMIT $limit";
        $result = mysqli_query($this->koneksi, $query);
        
        if ($result) {
            while ($row = mysqli_fetch_assoc($result)) {
                $activities[] = [
                    'waktu' => $row['waktu'],
                    'pengguna' => $row['pengguna'],
                    'aktivitas' => $row['aktivitas'],
                    'detail' => $row['detail']
                ];
            }
        } else {
            error_log("Error in get_recent_activities: " . mysqli_error($this->koneksi));
            // Return sample data if table doesn't exist
            $activities = [
                [
                    'waktu' => date('Y-m-d H:i:s'),
                    'pengguna' => 'Admin',
                    'aktivitas' => 'Login',
                    'detail' => 'Admin logged in'
                ]
            ];
        }
        
        return $activities;
    }

    function get_user_by_id($id_user) {
        $query = "SELECT * FROM users WHERE id_user = ?";
        $stmt = mysqli_prepare($this->koneksi, $query);
        if ($stmt) {
            mysqli_stmt_bind_param($stmt, "i", $id_user);
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);
            
            if (mysqli_num_rows($result) > 0) {
                return mysqli_fetch_assoc($result);
            }
        }
        return null;
    }

    function tambah_aktivitas_user($jenis, $entitas, $keterangan = '') {
        $jenis = mysqli_real_escape_string($this->koneksi, $jenis);
        $entitas = mysqli_real_escape_string($this->koneksi, $entitas);
        $keterangan = mysqli_real_escape_string($this->koneksi, $keterangan);

        $sql = "INSERT INTO aktivitas_user (jenis, entitas, keterangan) 
                VALUES ('$jenis', '$entitas', '$keterangan')";

        $result = mysqli_query($this->koneksi, $sql);

        if (!$result) {
            error_log("Error tambah_aktivitas_user: " . mysqli_error($this->koneksi));
            return false;
        }
        return true;
    }

    function tambah_aktivitasprofile($jenis, $entitas, $keterangan = '') {
        $id_user = $_SESSION['id_user'];
        $jenis = mysqli_real_escape_string($this->koneksi, $jenis);
        $entitas = mysqli_real_escape_string($this->koneksi, $entitas);
        $keterangan = mysqli_real_escape_string($this->koneksi, $keterangan);

        $sql = "INSERT INTO aktivitas_profile (id_user, jenis, entitas, keterangan) 
                VALUES ('$id_user', '$jenis', '$entitas', '$keterangan')";

        $result = mysqli_query($this->koneksi, $sql);

        if (!$result) {
            error_log("Error tambah_aktivitasprofile: " . mysqli_error($this->koneksi));
            return false;
        }
        return true;
    }


}    
?>