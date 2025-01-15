<?php
session_start();

if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}

$host = "localhost";
$user = "root";
$password = "";
$database = "gudang_barang3";

$conn = new mysqli($host, $user, $password, $database);

if ($conn->connect_error) {
    die("Koneksi gagal: " . $conn->connect_error);
}

$sql = "
    SELECT 
        b.barang_id, 
        b.nama_barang, 
        b.kategori, 
        b.jumlah_item,
        b.waktu_masuk,
        a.nama_admin AS nama_admin_masuk
    FROM barang b
    JOIN admin a ON b.admin_id = a.admin_id
    WHERE b.jumlah_item > 0
";
$result = $conn->query($sql);

$conn->close();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Barang</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container-barang">
        <h2>Data Barang</h2>
        <table border="1" cellpadding="10" cellspacing="0">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nama Barang</th>
                    <th>Kategori</th>
                    <th>Jumlah Item</th>
                    <th>Waktu Masuk</th>
                    <th>Admin Masuk</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($result->num_rows > 0): ?>
                    <?php while ($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td><?= $row['barang_id'] ?></td>
                            <td><?= htmlspecialchars($row['nama_barang']) ?></td>
                            <td><?= htmlspecialchars($row['kategori']) ?></td>
                            <td><?= $row['jumlah_item'] ?></td>
                            <td><?= $row['waktu_masuk'] ?></td>
                            <td><?= htmlspecialchars($row['nama_admin_masuk']) ?></td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="6">Tidak ada data barang.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
        <br>
        <button onclick="window.location.href='tambah_barang.php'" style="background-color: #0275d8; color: white; padding: 10px; border: none; border-radius: 5px; cursor: pointer;">
            Tambah Barang
        </button>
        <button onclick="window.location.href='barang_keluar.php'" style="background-color: #f0ad4e; color: white; padding: 10px; border: none; border-radius: 5px; cursor: pointer;">
            Barang Keluar
        </button>
        <button onclick="window.location.href='logout.php'" style="background-color: #d9534f; color: white; padding: 10px; border: none; border-radius: 5px; cursor: pointer;">
            Logout
        </button>
    </div>
</body>
</html>