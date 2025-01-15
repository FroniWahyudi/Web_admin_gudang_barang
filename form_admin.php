<?php
$host = "localhost";
$user = "root";
$password = "";
$database = "gudang_barang3";

$conn = new mysqli($host, $user, $password, $database);

if ($conn->connect_error) {
    die("Koneksi gagal: " . $conn->connect_error);
}

$message = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $nama_admin = trim($_POST['nama_admin']);
    $email = trim($_POST['email']);
    $no_telepon = trim($_POST['no_telepon']);
    $alamat = trim($_POST['alamat']);
    $alasan_registrasi = trim($_POST['alasan_registrasi']);
    $password = trim($_POST['password']);

    if (empty($nama_admin) || empty($email) || empty($no_telepon) || empty($alamat) || empty($alasan_registrasi) || empty($password)) {
        $message = "<div class='alert error'><span class='closebtn' onclick='this.parentElement.style.display=\"none\";'>&times;</span>Semua field wajib diisi!</div>";
    } else {
        $conn->begin_transaction();

        try {
            $sql_admin = "INSERT INTO admin (nama_admin, email, no_telepon, alamat, password) VALUES (?, ?, ?, ?, ?)";
            $stmt_admin = $conn->prepare($sql_admin);
            $stmt_admin->bind_param("sssss", $nama_admin, $email, $no_telepon, $alamat, $password);

            if ($stmt_admin->execute()) {
                $admin_id = $conn->insert_id;
                $sql_registrasi = "INSERT INTO registrasi_ulang (id_admin_baru, alasan_registrasi) VALUES (?, ?)";
                $stmt_registrasi = $conn->prepare($sql_registrasi);
                $stmt_registrasi->bind_param("is", $admin_id, $alasan_registrasi);

                if ($stmt_registrasi->execute()) {
                    $conn->commit();
                    $message = "<div class='alert success'><span class='closebtn' onclick='this.parentElement.style.display=\"none\";'>&times;</span>Data berhasil disimpan!</div>";
                } else {
                    throw new Exception("Gagal menyimpan ke tabel registrasi_ulang: " . $stmt_registrasi->error);
                }
            } else {
                throw new Exception("Gagal menyimpan ke tabel admin: " . $stmt_admin->error);
            }
        } catch (Exception $e) {
            $conn->rollback();
            $message = "<div class='alert error'><span class='closebtn' onclick='this.parentElement.style.display=\"none\";'>&times;</span>Terjadi kesalahan: " . $e->getMessage() . "</div>";
        }

        $stmt_admin->close();
        $stmt_registrasi->close();
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Form Admin</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container-admin">
        <h2>Form Tambah Admin</h2>
        <?= $message ?>
        <form method="POST" action="">
            <label for="nama_admin">Nama:</label>
            <input type="text" id="nama_admin" name="nama_admin" required>

            <label for="email">Email:</label>
            <input type="email" id="email" name="email" required>

            <label for="no_telepon">No Telepon:</label>
            <input type="text" id="no_telepon" name="no_telepon" required>

            <label for="alamat">Alamat:</label>
            <input type="text" id="alamat" name="alamat" required>

            <label for="alasan_registrasi">Alasan Registrasi:</label>
            <input type="text" id="alasan_registrasi" name="alasan_registrasi" required>

            <label for="password">Password:</label>
            <input type="password" id="password" name="password" required>

            <button type="submit">Simpan</button>
        </form>
        <br>
        <a href="login.php" class="btn-back">Kembali ke Login</a>
    </div>
</body>
</html>
