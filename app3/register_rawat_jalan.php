<?php
// configurations
$host = 'localhost';
$db_user = 'root';
$db_password = '';
$db_name = 'rumahsakit';

try {
    // database connection
    $pdo = new PDO("mysql:host=$host;dbname=$db_name", $db_user, $db_password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Retrieve incoming JSON data
    $requestData = json_decode(file_get_contents("php://input"), true);
    $sub = $requestData['sub'] ?? null; // Mengambil nilai 'sub' dari data JSON, jika tidak ada, maka bernilai null

    if ($sub) { 
        $stmt = $pdo->prepare("SELECT nik, no_bpjs FROM users WHERE sub = :sub"); // Menyiapkan query untuk mengambil 'nik' dan 'no_bpjs' berdasarkan 'sub'
        $stmt->execute([':sub' => $sub]);
        $userData = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($userData) {
            // Generate no_rekam_medis and no_rawat_jalan
            $no_rekam_medis = substr($userData['nik'], 0, 4) . substr($userData['no_bpjs'], 0, 4);
            $no_rawat_jalan = substr($userData['nik'], 0, 2) . substr($userData['no_bpjs'], 0, 4);

            $stmtCheck = $pdo->prepare("SELECT COUNT(*) FROM rawat_jalan WHERE sub = :sub"); // Menyiapkan query untuk memeriksa apakah 'sub' sudah ada di tabel rawat_jalan
            $stmtCheck->execute([':sub' => $sub]);
            $exists = $stmtCheck->fetchColumn() > 0;

            if ($exists) { // Jika data dengan 'sub' sudah ada, lakukan pembaruan
                // Update record
                $stmtUpdate = $pdo->prepare("
                    UPDATE rawat_jalan SET 
                        no_rekam_medis = :no_rekam_medis, jenis_klinik = :jenis_klinik, dokter = :dokter, 
                        tanggal = :tanggal, waktu = :waktu, keluhan = :keluhan, no_rawat_jalan = :no_rawat_jalan
                    WHERE sub = :sub
                "); // Menyiapkan query untuk memperbarui data rawat_jalan
                $stmtUpdate->execute([
                    ':no_rekam_medis' => $no_rekam_medis,
                    ':jenis_klinik' => $requestData['jenis_klinik'],
                    ':dokter' => $requestData['dokter'],
                    ':tanggal' => $requestData['tanggal'],
                    ':waktu' => $requestData['waktu'],
                    ':keluhan' => $requestData['keluhan'],
                    ':no_rawat_jalan' => $no_rawat_jalan,
                    ':sub' => $sub
                ]); 
            } else {
                // Insert new record
                $stmtInsert = $pdo->prepare("
                    INSERT INTO rawat_jalan (sub, no_rekam_medis, jenis_klinik, dokter, tanggal, waktu, keluhan, no_rawat_jalan)
                    VALUES (:sub, :no_rekam_medis, :jenis_klinik, :dokter, :tanggal, :waktu, :keluhan, :no_rawat_jalan)
                "); // Menyiapkan query untuk memasukkan data baru ke tabel rawat_jalan
                $stmtInsert->execute([
                    ':sub' => $sub,
                    ':no_rekam_medis' => $no_rekam_medis,
                    ':jenis_klinik' => $requestData['jenis_klinik'],
                    ':dokter' => $requestData['dokter'],
                    ':tanggal' => $requestData['tanggal'],
                    ':waktu' => $requestData['waktu'],
                    ':keluhan' => $requestData['keluhan'],
                    ':no_rawat_jalan' => $no_rawat_jalan
                ]);
            }

            echo json_encode(["status" => "success", "message" => "Data rawat jalan berhasil disimpan atau diperbarui."]);
        } else {
            echo json_encode(["status" => "error", "message" => "User data not found."]);
        }
    } else {
        echo json_encode(["status" => "error", "message" => "Invalid data: sub is missing."]); // Jika parameter 'sub' tidak ada dalam data yang dikirim, mengembalikan respons error
    }
} catch (PDOException $e) {
    echo json_encode(["status" => "error", "message" => $e->getMessage()]);
}
?>
