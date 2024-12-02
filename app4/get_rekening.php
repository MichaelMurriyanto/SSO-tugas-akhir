<?php
// Database configurations
$host = 'localhost';
$db_user = 'root';
$db_password = '';
$db_bank = 'bank';

// Retrieve the incoming JSON data
$requestData = json_decode(file_get_contents("php://input"), true);
$sub = $requestData['sub'] ?? null;
if ($sub) {
    try {
        // Connect to the database
        $pdoBank = new PDO("mysql:host=$host;dbname=$db_bank", $db_user, $db_password);
        $pdoBank->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // Query the rekeningBank table based on sub
        $stmt = $pdoBank->prepare("SELECT rumah_sakit, alamat_RS, rekening, QRIS FROM rekeningBank WHERE sub = :sub");
        $stmt->execute([':sub' => $sub]); // Menjalankan query dengan parameter 'sub'
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if ($result) {
            echo json_encode(["status" => "success", "data" => $result]);
        } else {
            echo json_encode(["status" => "success", "data" => []]);
        }
    } catch (PDOException $e) {
        echo json_encode(["status" => "error", "message" => $e->getMessage()]);
    }
} else {
    echo json_encode(["status" => "error", "message" => "Invalid sub"]);
}
?>
