<?php
// Koneksi ke server database
$host = "localhost";
$user = "root";
$password = "";

// Koneksi ke MySQL server tanpa database untuk menghapus dan membuat ulang database
$conn = new mysqli($host, $user, $password);

// Cek koneksi
if ($conn->connect_error) {
    die("Koneksi gagal: " . $conn->connect_error);
}

// Nama database yang akan dihapus dan dibuat ulang
$database = "gudang_barang3";

// Fungsi untuk menghapus database dan membuat ulang
function resetDatabase($conn, $database) {
    // Hapus database jika sudah ada
    $sqlDropDatabase = "DROP DATABASE IF EXISTS $database";
    if ($conn->query($sqlDropDatabase) === TRUE) {
        echo "Database $database telah dihapus.<br>";
    } else {
        echo "Error menghapus database: " . $conn->error . "<br>";
    }

    // Buat ulang database
    $sqlCreateDatabase = "CREATE DATABASE $database";
    if ($conn->query($sqlCreateDatabase) === TRUE) {
        echo "Database $database berhasil dibuat ulang.<br>";
    } else {
        echo "Error membuat database: " . $conn->error . "<br>";
    }

    // Pilih database yang baru saja dibuat
    $conn->select_db($database);

    // Buat tabel ulang
    $tables = [
        'admin' => "CREATE TABLE `admin` (
            `admin_id` INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
            `nama_admin` VARCHAR(100) NOT NULL,
            `email` VARCHAR(100) NOT NULL UNIQUE,
            `no_telepon` VARCHAR(15) NOT NULL,
            `alamat` TEXT NOT NULL,
            `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
            `password` VARCHAR(100) NOT NULL
        )",
        
        'barang' => "CREATE TABLE `barang` (
            `barang_id` INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
            `admin_id` INT(11) NOT NULL,
            `nama_barang` VARCHAR(100) NOT NULL,
            `kategori` VARCHAR(100) NOT NULL,
            `jumlah_item` INT(11) NOT NULL,
            `waktu_masuk` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (`admin_id`) REFERENCES `admin`(`admin_id`)
        )",
        
        'registrasi_ulang' => "CREATE TABLE `registrasi_ulang` (
            `registrasi_id` INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
            `id_admin_baru` INT(11) DEFAULT NULL,
            `waktu_registrasi` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            `alasan_registrasi` TEXT NOT NULL,
            FOREIGN KEY (`id_admin_baru`) REFERENCES `admin`(`admin_id`)
        )",
        
        'transaksi_barang' => "CREATE TABLE `transaksi_barang` (
            `transaksi_id` INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
            `barang_id` INT(11) DEFAULT NULL,
            `admin_id` INT(11) NOT NULL,
            `tipe_transaksi` ENUM('masuk', 'keluar') NOT NULL,
            `waktu_transaksi` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (`barang_id`) REFERENCES `barang`(`barang_id`),
            FOREIGN KEY (`admin_id`) REFERENCES `admin`(`admin_id`)
        )"
    ];

    // Buat tabel ulang
    foreach ($tables as $table => $createQuery) {
        if ($conn->query($createQuery) === TRUE) {
            echo "Tabel $table berhasil dibuat ulang.<br>";
        } else {
            echo "Error membuat tabel $table: " . $conn->error . "<br>";
        }
    }
}

// Proses untuk menghapus dan membuat ulang database dan tabel
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    resetDatabase($conn, $database);
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Database</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container-admin">
        <h2>Hapus dan Buat Ulang Database</h2>
        <form method="POST" action="">
            <button type="submit">Hapus Database dan Buat Ulang</button>
        </form>
    </div>
</body>
</html>
