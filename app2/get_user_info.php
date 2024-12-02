<?php
$host = 'localhost';
$username = 'root';
$password = '';
$database_kependudukan = 'kependudukan';
$database_bpjs = 'bpjs';

$sub = $_POST['sub'] ?? null; // Mengambil nilai 'sub' dari data POST, jika tidak ada maka nilainya null
if ($sub) {
    $conn_bpjs = new mysqli($host, $username, $password, $database_bpjs); // Membuat koneksi ke database BPJS dan Kependudukan
    $conn_kependudukan = new mysqli($host, $username, $password, $database_kependudukan);

    if ($conn_bpjs->connect_error || $conn_kependudukan->connect_error) {
        echo json_encode(["success" => false, "message" => "Koneksi database gagal."]);
        exit();
    }
    $query = "SELECT * FROM users WHERE sub = ?"; // Query untuk mendapatkan data pengguna berdasarkan 'sub' dari database BPJS
    $stmt = $conn_bpjs->prepare($query);
    $stmt->bind_param("s", $sub); // Mengikat parameter 'sub' ke query
    $stmt->execute();
    $result = $stmt->get_result();
    $user_data_bpjs = $result->fetch_assoc(); // Mengambil data pengguna dari hasil query
    if ($user_data_bpjs) {
        // Jika data pengguna ditemukan di database BPJS
        $query_kependudukan = "SELECT nik, no_kk, nama, alamat, no_hp FROM users WHERE sub = ?";
        $stmt_kependudukan = $conn_kependudukan->prepare($query_kependudukan);
        $stmt_kependudukan->bind_param("s", $sub);
        $stmt_kependudukan->execute();
        $result_kependudukan = $stmt_kependudukan->get_result();
        $user_data_kependudukan = $result_kependudukan->fetch_assoc(); // Mengambil data dari database Kependudukan

        if ($user_data_kependudukan) {
            // Membandingkan data dari kedua database
            $needs_update = (
                $user_data_bpjs['nik'] !== $user_data_kependudukan['nik'] ||
                $user_data_bpjs['no_kk'] !== $user_data_kependudukan['no_kk'] ||
                $user_data_bpjs['nama'] !== $user_data_kependudukan['nama'] ||
                $user_data_bpjs['alamat'] !== $user_data_kependudukan['alamat'] ||
                $user_data_bpjs['no_hp'] !== $user_data_kependudukan['no_hp']
            );
            if ($needs_update) {
                // Jika ada perbedaan data, lakukan update di database BPJS
                $update_query = "UPDATE users SET nik = ?, no_kk = ?, nama = ?, alamat = ?, no_hp = ? WHERE sub = ?";
                $stmt_update = $conn_bpjs->prepare($update_query);
                $stmt_update->bind_param(
                    "ssssss",
                    $user_data_kependudukan['nik'],
                    $user_data_kependudukan['no_kk'],
                    $user_data_kependudukan['nama'],
                    $user_data_kependudukan['alamat'],
                    $user_data_kependudukan['no_hp'],
                    $sub
                );
                $stmt_update->execute();
            }
        }
        $user_data_full = array_merge($user_data_bpjs, $user_data_kependudukan); // Gabungkan data dari database BPJS dan Kependudukan

    } else {
        // Jika data tidak ditemukan di BPJS, coba cari di database Kependudukan
        $query_kependudukan = "SELECT * FROM users WHERE sub = ?";
        $stmt_kependudukan = $conn_kependudukan->prepare($query_kependudukan);
        $stmt_kependudukan->bind_param("s", $sub);
        $stmt_kependudukan->execute();
        $result_kependudukan = $stmt_kependudukan->get_result();
        $user_data_kependudukan = $result_kependudukan->fetch_assoc();
        // Jika data ditemukan di database Kependudukan, masukkan ke database BPJS
        if ($user_data_kependudukan) {
            $insert_query = "INSERT INTO users (sub, name, preferred_username, given_name, family_name, email, email_verified, nik, no_kk, nama, alamat, no_hp) 
                             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            $stmt_insert = $conn_bpjs->prepare($insert_query);
            $stmt_insert->bind_param(
                "ssssssissssi",
                $user_data_kependudukan['sub'],
                $user_data_kependudukan['name'],
                $user_data_kependudukan['preferred_username'],
                $user_data_kependudukan['given_name'],
                $user_data_kependudukan['family_name'],
                $user_data_kependudukan['email'],
                $user_data_kependudukan['email_verified'],
                $user_data_kependudukan['nik'],
                $user_data_kependudukan['no_kk'],
                $user_data_kependudukan['nama'],
                $user_data_kependudukan['alamat'],
                $user_data_kependudukan['no_hp']
            );
            $stmt_insert->execute();
            $user_data_full = $user_data_kependudukan;
        } else {
            // Jika data tidak ditemukan di kedua database
            $user_data_full = null;
        }
    }

    // Mengirimkan data pengguna dalam format JSON sebagai respon
    echo json_encode($user_data_full ? ["success" => true, "data" => $user_data_full] : ["success" => false, "message" => "User tidak ditemukan di database BPJS atau Kependudukan."]);
    // Menutup semua statement dan koneksi database
    $stmt->close();
    if (isset($stmt_kependudukan)) $stmt_kependudukan->close();
    if (isset($stmt_insert)) $stmt_insert->close();
    if (isset($stmt_update)) $stmt_update->close();
    $conn_bpjs->close();
    $conn_kependudukan->close();
} else {
    // Jika 'sub' tidak valid atau tidak diberikan
    echo json_encode(["success" => false, "message" => "Sub tidak valid."]);
}
?>
