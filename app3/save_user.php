<?php
// Database configurations
$host = 'localhost';
$db_user = 'root';
$db_password = '';
$db_kependudukan = 'kependudukan';
$db_rumahsakit = 'rumahsakit'; // Ubah bank menjadi rumahsakit
$db_bpjs = 'bpjs';

// Retrieve the incoming JSON data
$requestData = json_decode(file_get_contents("php://input"), true);
$sub = $requestData['sub'] ?? null;

if ($sub) {
    try {
        // Connect to the databases
        $pdoKependudukan = new PDO("mysql:host=$host;dbname=$db_kependudukan", $db_user, $db_password);
        $pdoRumahSakit = new PDO("mysql:host=$host;dbname=$db_rumahsakit", $db_user, $db_password); // Ubah koneksi ke rumahsakit
        $pdoBpjs = new PDO("mysql:host=$host;dbname=$db_bpjs", $db_user, $db_password);
        $pdoKependudukan->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $pdoRumahSakit->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $pdoBpjs->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

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
            $userRumahSakitData = array_merge($userData, $bpjsData);

            // Check if the sub already exists in the users table in rumahsakit database
            $stmtCheck = $pdoRumahSakit->prepare("SELECT COUNT(*) FROM users WHERE sub = :sub"); // Ganti tabel ke users
            $stmtCheck->execute([':sub' => $sub]);
            $exists = $stmtCheck->fetchColumn() > 0;

            if ($exists) {
                // Update existing record
                $stmtUpdate = $pdoRumahSakit->prepare("
                    UPDATE users SET 
                        name = :name, preferred_username = :preferred_username, given_name = :given_name, 
                        family_name = :family_name, email = :email, email_verified = :email_verified, 
                        nik = :nik, no_kk = :no_kk, nama = :nama, alamat = :alamat, no_hp = :no_hp,
                        kelas_bpjs = :kelas_bpjs, faskes = :faskes, no_bpjs = :no_bpjs
                    WHERE sub = :sub
                ");
                $stmtUpdate->execute([
                    ':sub' => $userRumahSakitData['sub'],
                    ':name' => $userRumahSakitData['name'],
                    ':preferred_username' => $userRumahSakitData['preferred_username'],
                    ':given_name' => $userRumahSakitData['given_name'],
                    ':family_name' => $userRumahSakitData['family_name'],
                    ':email' => $userRumahSakitData['email'],
                    ':email_verified' => $userRumahSakitData['email_verified'],
                    ':nik' => $userRumahSakitData['nik'],
                    ':no_kk' => $userRumahSakitData['no_kk'],
                    ':nama' => $userRumahSakitData['nama'],
                    ':alamat' => $userRumahSakitData['alamat'],
                    ':no_hp' => $userRumahSakitData['no_hp'],
                    ':kelas_bpjs' => $userRumahSakitData['kelas_bpjs'],
                    ':faskes' => $userRumahSakitData['faskes'],
                    ':no_bpjs' => $userRumahSakitData['no_bpjs']
                ]);
            } else {
                // Insert new record
                $stmtInsert = $pdoRumahSakit->prepare("
                    INSERT INTO users (sub, name, preferred_username, given_name, family_name, email, email_verified, nik, no_kk, nama, alamat, no_hp, kelas_bpjs, faskes, no_bpjs)
                    VALUES (:sub, :name, :preferred_username, :given_name, :family_name, :email, :email_verified, :nik, :no_kk, :nama, :alamat, :no_hp, :kelas_bpjs, :faskes, :no_bpjs)
                ");
                $stmtInsert->execute([
                    ':sub' => $userRumahSakitData['sub'],
                    ':name' => $userRumahSakitData['name'],
                    ':preferred_username' => $userRumahSakitData['preferred_username'],
                    ':given_name' => $userRumahSakitData['given_name'],
                    ':family_name' => $userRumahSakitData['family_name'],
                    ':email' => $userRumahSakitData['email'],
                    ':email_verified' => $userRumahSakitData['email_verified'],
                    ':nik' => $userRumahSakitData['nik'],
                    ':no_kk' => $userRumahSakitData['no_kk'],
                    ':nama' => $userRumahSakitData['nama'],
                    ':alamat' => $userRumahSakitData['alamat'],
                    ':no_hp' => $userRumahSakitData['no_hp'],
                    ':kelas_bpjs' => $userRumahSakitData['kelas_bpjs'],
                    ':faskes' => $userRumahSakitData['faskes'],
                    ':no_bpjs' => $userRumahSakitData['no_bpjs']
                ]);
            }

            // Retrieve updated data from users table in rumahsakit database
            $stmtRumahSakit = $pdoRumahSakit->prepare("SELECT * FROM users WHERE sub = :sub"); // Ganti tabel ke users
            $stmtRumahSakit->execute([':sub' => $sub]);
            $userRumahSakitData = $stmtRumahSakit->fetch(PDO::FETCH_ASSOC);

            echo json_encode(["status" => "success", "data" => $userRumahSakitData]);
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
