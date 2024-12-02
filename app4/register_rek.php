<?php
// Database configurations
$host = 'localhost';
$db_user = 'root';
$db_password = '';
$db_bank = 'bank';
$db_kependudukan = 'kependudukan';

// Retrieve the incoming JSON data
$requestData = json_decode(file_get_contents("php://input"), true);
$sub = $requestData['sub'] ?? null;
$rumah_sakit = $requestData['rumah_sakit'] ?? null;
$alamat_RS = $requestData['alamat_RS'] ?? null;

if ($sub && $rumah_sakit && $alamat_RS) {
    try {
        // Connect to the database
        $pdoBank = new PDO("mysql:host=$host;dbname=$db_bank", $db_user, $db_password);
        $pdoBank->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // Generate rekening and QRIS based on NIK
        $pdoKependudukan = new PDO("mysql:host=$host;dbname=$db_kependudukan", $db_user, $db_password);
        $stmtNik = $pdoKependudukan->prepare("SELECT nik FROM users WHERE sub = :sub");
        $stmtNik->execute([':sub' => $sub]);
        $nikData = $stmtNik->fetch(PDO::FETCH_ASSOC);
        
        if ($nikData && isset($nikData['nik'])) {
            $nik = $nikData['nik'];

            // Generate rekening number (4 digits of NIK + 12 random digits)
            $rekening = substr($nik, 0, 4) . str_pad(rand(0, 999999999999), 12, '0', STR_PAD_LEFT);

            // Generate QRIS (6 digits of rekening + 10 random digits)
            $qris = substr($rekening, 0, 6) . str_pad(rand(0, 9999999999), 10, '0', STR_PAD_LEFT);

            // Insert into rekeningBank table
            $stmtInsert = $pdoBank->prepare("
                INSERT INTO rekeningBank (sub, rumah_sakit, alamat_RS, rekening, QRIS)
                VALUES (:sub, :rumah_sakit, :alamat_RS, :rekening, :QRIS)
            ");
            $stmtInsert->execute([
                ':sub' => $sub,
                ':rumah_sakit' => $rumah_sakit,
                ':alamat_RS' => $alamat_RS,
                ':rekening' => $rekening,
                ':QRIS' => $qris
            ]);

            echo json_encode(["status" => "success", "message" => "Rekening registered successfully."]);
        } else {
            echo json_encode(["status" => "error", "message" => "NIK not found for the provided sub."]);
        }
    } catch (PDOException $e) {
        echo json_encode(["status" => "error", "message" => $e->getMessage()]);
    }
} else {
    echo json_encode(["status" => "error", "message" => "Invalid data: sub, rumah_sakit, or alamat_RS missing."]);
}
?>
