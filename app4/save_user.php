<?php
// Database configurations
$host = 'localhost';
$db_user = 'root';
$db_password = '';
$db_kependudukan = 'kependudukan';
$db_bank = 'bank';
$db_bpjs = 'bpjs';

// Retrieve the incoming JSON data
$requestData = json_decode(file_get_contents("php://input"), true);
$sub = $requestData['sub'] ?? null;

if ($sub) {
    try {
        // Connect to the databases
        $pdoKependudukan = new PDO("mysql:host=$host;dbname=$db_kependudukan", $db_user, $db_password);
        $pdoBank = new PDO("mysql:host=$host;dbname=$db_bank", $db_user, $db_password);
        $pdoBpjs = new PDO("mysql:host=$host;dbname=$db_bpjs", $db_user, $db_password);
        $pdoKependudukan->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION); // Mengatur mode error PDO untuk database 'kependudukan'
        $pdoBank->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION); // Mengatur mode error PDO untuk database 'bank'
        $pdoBpjs->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION); // Mengatur mode error PDO untuk database 'bpjs'


        // Check if the user exists in the kependudukan database
        $stmt = $pdoKependudukan->prepare("SELECT sub, name, preferred_username, given_name, family_name, email, email_verified, nik, no_kk, nama, alamat, no_hp FROM users WHERE sub = :sub");
        $stmt->execute([':sub' => $sub]);
        $userData = $stmt->fetch(PDO::FETCH_ASSOC);

        // Fetch additional data from bpjs database
        $bpjsData = [];
        $stmtBpjs = $pdoBpjs->prepare("SELECT kelas_bpjs, faskes, no_bpjs FROM users WHERE sub = :sub");
        $stmtBpjs->execute([':sub' => $sub]);
        $bpjsData = $stmtBpjs->fetch(PDO::FETCH_ASSOC);

        if ($userData) {
            // Merge bpjs data with kependudukan data
            $nasabahData = array_merge($userData, $bpjsData);

            // Check if the sub already exists in the nasabah table
            $stmtCheck = $pdoBank->prepare("SELECT COUNT(*) FROM nasabah WHERE sub = :sub");
            $stmtCheck->execute([':sub' => $sub]);
            $exists = $stmtCheck->fetchColumn() > 0;

            if ($exists) {
                // Update existing record
                $stmtUpdate = $pdoBank->prepare("
                    UPDATE nasabah SET 
                        name = :name, preferred_username = :preferred_username, given_name = :given_name, 
                        family_name = :family_name, email = :email, email_verified = :email_verified, 
                        nik = :nik, no_kk = :no_kk, nama = :nama, alamat = :alamat, no_hp = :no_hp,
                        kelas_bpjs = :kelas_bpjs, faskes = :faskes, no_bpjs = :no_bpjs
                    WHERE sub = :sub
                ");
                $stmtUpdate->execute([
                    ':sub' => $nasabahData['sub'],
                    ':name' => $nasabahData['name'],
                    ':preferred_username' => $nasabahData['preferred_username'],
                    ':given_name' => $nasabahData['given_name'],
                    ':family_name' => $nasabahData['family_name'],
                    ':email' => $nasabahData['email'],
                    ':email_verified' => $nasabahData['email_verified'],
                    ':nik' => $nasabahData['nik'],
                    ':no_kk' => $nasabahData['no_kk'],
                    ':nama' => $nasabahData['nama'],
                    ':alamat' => $nasabahData['alamat'],
                    ':no_hp' => $nasabahData['no_hp'],
                    ':kelas_bpjs' => $nasabahData['kelas_bpjs'],
                    ':faskes' => $nasabahData['faskes'],
                    ':no_bpjs' => $nasabahData['no_bpjs']
                ]);
            } else {
                // Insert new record
                $stmtInsert = $pdoBank->prepare("
                    INSERT INTO nasabah (sub, name, preferred_username, given_name, family_name, email, email_verified, nik, no_kk, nama, alamat, no_hp, kelas_bpjs, faskes, no_bpjs)
                    VALUES (:sub, :name, :preferred_username, :given_name, :family_name, :email, :email_verified, :nik, :no_kk, :nama, :alamat, :no_hp, :kelas_bpjs, :faskes, :no_bpjs)
                ");
                $stmtInsert->execute([
                    ':sub' => $nasabahData['sub'],
                    ':name' => $nasabahData['name'],
                    ':preferred_username' => $nasabahData['preferred_username'],
                    ':given_name' => $nasabahData['given_name'],
                    ':family_name' => $nasabahData['family_name'],
                    ':email' => $nasabahData['email'],
                    ':email_verified' => $nasabahData['email_verified'],
                    ':nik' => $nasabahData['nik'],
                    ':no_kk' => $nasabahData['no_kk'],
                    ':nama' => $nasabahData['nama'],
                    ':alamat' => $nasabahData['alamat'],
                    ':no_hp' => $nasabahData['no_hp'],
                    ':kelas_bpjs' => $nasabahData['kelas_bpjs'],
                    ':faskes' => $nasabahData['faskes'],
                    ':no_bpjs' => $nasabahData['no_bpjs']
                ]);
            }

            // Retrieve updated data from nasabah table
            $stmtNasabah = $pdoBank->prepare("SELECT * FROM nasabah WHERE sub = :sub");
            $stmtNasabah->execute([':sub' => $sub]);
            $nasabahData = $stmtNasabah->fetch(PDO::FETCH_ASSOC);

            echo json_encode(["status" => "success", "data" => $nasabahData]);
        } else {
            echo json_encode(["status" => "error", "message" => "User not found in kependudukan database."]);
        }
    } catch (PDOException $e) {
        echo json_encode(["status" => "error", "message" => $e->getMessage()]);
    }
} else {
    echo json_encode(["status" => "error", "message" => "Invalid data: sub is missing."]);
}
?>
