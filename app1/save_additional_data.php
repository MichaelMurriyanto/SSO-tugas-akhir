<?php
$host = 'localhost';
$username = 'root'; 
$password = '';     
$dbname = 'kependudukan';
$conn = new mysqli($host, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Koneksi gagal: " . $conn->connect_error);// Periksa koneksi
}
$sub = $conn->real_escape_string($_POST['sub']); // Ambil data dari POST request
$nik = $conn->real_escape_string($_POST['nik']);
$no_kk = isset($_POST['no_kk']) && trim($_POST['no_kk']) !== '' ? $conn->real_escape_string($_POST['no_kk']) : 'belum di registrasi';
$nama = isset($_POST['nama']) && trim($_POST['nama']) !== '' ? $conn->real_escape_string($_POST['nama']) : 'belum di registrasi';
$alamat = isset($_POST['alamat']) && trim($_POST['alamat']) !== '' ? $conn->real_escape_string($_POST['alamat']) : 'belum di registrasi';
$no_hp = isset($_POST['no_hp']) && trim($_POST['no_hp']) !== '' && $_POST['no_hp'] != '0' ? $conn->real_escape_string($_POST['no_hp']) : 'belum di registrasi';
$sql_update = "UPDATE users SET 
               nik = '$nik', 
               no_kk = '$no_kk', 
               nama = '$nama', 
               alamat = '$alamat', 
               no_hp = '$no_hp' 
               WHERE sub = '$sub'"; // Update data di database
if ($conn->query($sql_update) === TRUE) {
    echo json_encode(["status" => "success"]);
} else {
    echo json_encode(["status" => "error", "message" => $conn->error]);
}
$conn->close();
?>
