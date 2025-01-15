<?php
session_start();

if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}

$admin_id = $_SESSION['admin_id'];
$nama_admin = $_SESSION['nama_admin'];
$message = "";

$host = "localhost";
$user = "root";
$password = "";
$database = "gudang_barang3";

$conn = new mysqli($host, $user, $password, $database);

if ($conn->connect_error) {
    die("Koneksi gagal: " . $conn->connect_error);
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $nama_barang = trim($_POST['nama_barang']);
    $kategori = trim($_POST['kategori']);
    $jumlah_item = intval(trim($_POST['jumlah_item']));

    if (empty($nama_barang) || empty($kategori) || $jumlah_item <= 0) {
        $message = "<div class='alert error'>Semua field wajib diisi dan jumlah item harus lebih dari 0!</div>";
    } else {
        $conn->begin_transaction();

        try {
            $sql_cek_barang = "SELECT barang_id, jumlah_item FROM barang WHERE nama_barang = ? AND kategori = ? AND admin_id = ?";
            $stmt_cek_barang = $conn->prepare($sql_cek_barang);
            $stmt_cek_barang->bind_param("ssi", $nama_barang, $kategori, $admin_id);
            $stmt_cek_barang->execute();
            $result = $stmt_cek_barang->get_result();

            if ($result->num_rows > 0) {
                $row = $result->fetch_assoc();
                $barang_id = $row['barang_id'];
                $jumlah_item_baru = $row['jumlah_item'] + $jumlah_item;

                $sql_update = "UPDATE barang SET jumlah_item = ? WHERE barang_id = ?";
                $stmt_update = $conn->prepare($sql_update);
                $stmt_update->bind_param("ii", $jumlah_item_baru, $barang_id);
                $stmt_update->execute();
            } else {
                $sql_barang = "INSERT INTO barang (admin_id, nama_barang, kategori, jumlah_item, waktu_masuk) VALUES (?, ?, ?, ?, NOW())";
                $stmt_barang = $conn->prepare($sql_barang);
                $stmt_barang->bind_param("issi", $admin_id, $nama_barang, $kategori, $jumlah_item);
                $stmt_barang->execute();
                $barang_id = $conn->insert_id;
                $stmt_barang->close();
            }

            $sql_transaksi = "INSERT INTO transaksi_barang (barang_id, admin_id, tipe_transaksi, waktu_transaksi) VALUES (?, ?, 'masuk', NOW())";
            $stmt_transaksi = $conn->prepare($sql_transaksi);
            $stmt_transaksi->bind_param("ii", $barang_id, $admin_id);
            $stmt_transaksi->execute();
            $stmt_transaksi->close();

            $conn->commit();
            $message = "<div class='alert success'>Barang berhasil ditambahkan atau diperbarui, dan transaksi dicatat!</div>";
        } catch (Exception $e) {
            $conn->rollback();
            $message = "<div class='alert error'>Terjadi kesalahan: " . $e->getMessage() . "</div>";
        }

        $stmt_cek_barang->close();
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Form Tambah Barang</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container-tambah">
        <h2>Form Tambah Barang</h2>
        <p>Admin: <?= htmlspecialchars($nama_admin) ?> (ID: <?= $admin_id ?>)</p>
        <?= $message ?>
        <form method="POST" action="">
            <label for="nama_barang">Nama Barang:</label>
            <input type="text" id="nama_barang" name="nama_barang" required>

            <label for="kategori">Kategori:</label>
            <input type="text" id="kategori" name="kategori" required>

            <label for="jumlah_item">Jumlah Stok:</label>
            <input type="number" id="jumlah_item" name="jumlah_item" required>

            <button type="submit">Simpan Barang</button>
        </form>
        <br>
        <button onclick="window.location.href='barang.php'" style="background-color: #5cb85c; color: white; padding: 10px; border: none; border-radius: 5px; cursor: pointer;">
            Lihat Data Barang
        </button>
        <button onclick="window.location.href='logout.php'" style="background-color: #d9534f; color: white; padding: 10px; border: none; border-radius: 5px; cursor: pointer;">
            Logout
        </button>
    </div>
</body>
</html>