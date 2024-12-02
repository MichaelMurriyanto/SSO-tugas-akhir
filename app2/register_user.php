<?php
$host = 'localhost'; // Konfigurasi koneksi ke database
$username = 'root';
$password = '';
$database_bpjs = 'bpjs';

$sub = $_POST['sub'] ?? null; // Mendapatkan nilai 'sub' dari data POST, jika tidak ada nilainya null
if ($sub) {
    $conn = new mysqli($host, $username, $password, $database_bpjs); // Membuat koneksi ke database
    if ($conn->connect_error) {
        die("Koneksi gagal: " . $conn->connect_error); // Menghentikan eksekusi jika koneksi gagal
    }
    // Query untuk mendapatkan data pengguna berdasarkan 'sub'
    $query = "SELECT nik, no_kk, kelas_bpjs, faskes, No_bpjs FROM users WHERE sub = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $sub); // Mengikat parameter 'sub' ke query
    $stmt->execute();
    $result = $stmt->get_result();
    $user_data = $result->fetch_assoc();
    if ($user_data) { // Mengecek apakah data pengguna ditemukan
        // Mengecek apakah data kelas BPJS, faskes, dan nomor BPJS sudah ada
        if (!empty($user_data['kelas_bpjs']) && !empty($user_data['faskes']) && !empty($user_data['No_bpjs'])) {
            echo json_encode(["success" => false, "message" => "Registrasi sudah pernah dilakukan."]); // Jika registrasi sudah pernah dilakukan, mengembalikan pesan gagal
        } else {
            // Mengecek apakah data `nik` dan `no_kk` ada di database
            if (!empty($user_data['nik']) && !empty($user_data['no_kk'])) {
                $kelas_bpjs = str_pad(rand(1, 3), 2, "0", STR_PAD_LEFT);
                $faskes = str_pad(rand(1, 3), 2, "0", STR_PAD_LEFT);
                $no_bpjs = substr($user_data['nik'], 0, 3) . substr($user_data['no_kk'], 0, 3) . $kelas_bpjs . $faskes . $user_data['sub'];
                $update_query = "UPDATE users SET kelas_bpjs = ?, faskes = ?, No_bpjs = ? WHERE sub = ?"; // Membuat nomor BPJS dari kombinasi nik, no_kk, kelas_bpjs, faskes, dan sub
                $stmt_update = $conn->prepare($update_query);  // Query untuk memperbarui data registrasi pengguna
                $stmt_update->bind_param("ssss", $kelas_bpjs, $faskes, $no_bpjs, $sub);
                $stmt_update->execute();
                echo json_encode(["success" => true, "message" => "Registrasi berhasil.", "kelas_bpjs" => $kelas_bpjs, "faskes" => $faskes, "No_bpjs" => $no_bpjs]);
            } else {                $update_query = "UPDATE users SET kelas_bpjs = NULL, faskes = NULL, No_bpjs = NULL WHERE sub = ?";
                $stmt_update = $conn->prepare($update_query); // Jika `nik` atau `no_kk` kosong, reset data registrasi
                $stmt_update->bind_param("s", $sub); // Mengikat parameter untuk query
                $stmt_update->execute();
                echo json_encode(["success" => false, "message" => "Registrasi gagal: `nik` atau `no_kk` tidak ada."]);
            }
        }
    } else {
        echo json_encode(["success" => false, "message" => "Pengguna tidak ditemukan."]);// Jika data pengguna tidak ditemukan di database
    }
    $stmt->close();
    $conn->close();
} else {
    echo json_encode(["success" => false, "message" => "Sub tidak valid."]);  // Jika 'sub' tidak valid atau tidak diberikan
}
?>
