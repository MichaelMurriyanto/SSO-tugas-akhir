<?php
// Koneksi ke database
$host = 'localhost';
$username = 'root'; // sesuaikan dengan konfigurasi XAMPP Anda
$password = '';     // sesuaikan dengan konfigurasi XAMPP Anda
$dbname = 'kependudukan';

$conn = new mysqli($host, $username, $password, $dbname);

// Periksa koneksi
if ($conn->connect_error) {
    die("Koneksi gagal: " . $conn->connect_error);
}

// Ambil data JSON dari POST request
$data = json_decode(file_get_contents('php://input'), true);

$sub = $conn->real_escape_string($data['sub']);
$name = $conn->real_escape_string($data['name']);
$preferred_username = $conn->real_escape_string($data['preferred_username']);
$given_name = $conn->real_escape_string($data['given_name']);
$family_name = $conn->real_escape_string($data['family_name']);
$email = $conn->real_escape_string($data['email']);
$email_verified = $data['email_verified'] ? 1 : 0;

// Periksa apakah data sudah ada di database
$sql_check = "SELECT * FROM users WHERE sub = '$sub'";
$result = $conn->query($sql_check);

if ($result->num_rows > 0) {
    // Data sudah ada, ambil dan tampilkan
    $user_data = $result->fetch_assoc();
    
    // Tampilkan data dengan penyesuaian jika ada kolom kosong
    $user_data['nik'] = $user_data['nik'] ?? 'belum di registrasi';
    $user_data['no_kk'] = $user_data['no_kk'] ?? 'belum di registrasi';
    $user_data['nama'] = $user_data['nama'] ?? 'belum di registrasi';
    $user_data['alamat'] = $user_data['alamat'] ?? 'belum di registrasi';
    $user_data['no_hp'] = $user_data['no_hp'] ?? 'belum di registrasi';
    
    echo json_encode($user_data);
} else {
    // Data tidak ada, simpan ke database
    $sql_insert = "INSERT INTO users (sub, name, preferred_username, given_name, family_name, email, email_verified) 
                   VALUES ('$sub', '$name', '$preferred_username', '$given_name', '$family_name', '$email', $email_verified)";
    
    if ($conn->query($sql_insert) === TRUE) {
        // Ambil data yang baru saja disimpan untuk ditampilkan
        $sql_get_new = "SELECT * FROM users WHERE sub = '$sub'";
        $result_new = $conn->query($sql_get_new);
        if ($result_new->num_rows > 0) {
            $user_data = $result_new->fetch_assoc();
            $user_data['nik'] = 'belum di registrasi';
            $user_data['no_kk'] = 'belum di registrasi';
            $user_data['nama'] = 'belum di registrasi';
            $user_data['alamat'] = 'belum di registrasi';
            $user_data['no_hp'] = 'belum di registrasi';
            
            echo json_encode($user_data);
        }
    } else {
        echo json_encode(["status" => "error", "message" => $conn->error]);
    }
}

// Tutup koneksi
$conn->close();
?>
