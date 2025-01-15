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

$message = "";

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['barang_id'])) {
    $barang_id = $_POST['barang_id'];
    $admin_id = $_SESSION['admin_id'];

    $conn->begin_transaction();

    try {
        $sql_select = "SELECT nama_barang, kategori, jumlah_item FROM barang WHERE barang_id = ?";
        $stmt_select = $conn->prepare($sql_select);
        $stmt_select->bind_param("i", $barang_id);
        $stmt_select->execute();
        $result = $stmt_select->get_result();

        if ($result->num_rows > 0) {
            $barang = $result->fetch_assoc();
            $jumlah_item = $barang['jumlah_item'];

            $sql_transaksi = "INSERT INTO transaksi_barang (barang_id, admin_id, tipe_transaksi, waktu_transaksi) VALUES (?, ?, 'keluar', NOW())";
            $stmt_transaksi = $conn->prepare($sql_transaksi);
            $stmt_transaksi->bind_param("ii", $barang_id, $admin_id);
            $stmt_transaksi->execute();

            $sql_update = "UPDATE barang SET jumlah_item = 0 WHERE barang_id = ?";
            $stmt_update = $conn->prepare($sql_update);
            $stmt_update->bind_param("i", $barang_id);
            $stmt_update->execute();

            $_SESSION['jumlah_item'] = $jumlah_item;

            $conn->commit();
            $message = "<div class='alert success'>Barang berhasil dikeluarkan dari gudang.</div>";

            header("Location: riwayat_barang_keluar.php");
            exit();
        } else {
            throw new Exception("Data barang tidak ditemukan!");
        }
    } catch (Exception $e) {
        $conn->rollback();
        $message = "<div class='alert error'>Terjadi kesalahan: " . $e->getMessage() . "</div>";
    }

    $stmt_select->close();
}

$sql = "SELECT barang_id, nama_barang, kategori, jumlah_item, waktu_masuk FROM barang WHERE jumlah_item > 0";
$result = $conn->query($sql);

$conn->close();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Barang Keluar</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container-barang-keluar">
        <h2>Pilih Barang untuk Dikeluarkan</h2>
        <?= $message ?>
        <table border="1" cellpadding="10" cellspacing="0">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nama Barang</th>
                    <th>Kategori</th>
                    <th>Jumlah Item</th>
                    <th>Waktu Masuk</th>
                    <th>Aksi</th>
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
                            <td>
                                <form method="POST" action="barang_keluar.php" style="display:inline;">
                                    <input type="hidden" name="barang_id" value="<?= $row['barang_id'] ?>">
                                    <button type="submit" style="background-color: #d9534f; color: white; padding: 10px; border: none; border-radius: 5px; cursor: pointer;">
                                        Keluarkan
                                    </button>
                                </form>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="6">Tidak ada barang dengan stok tersedia.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
        <br>
        <button onclick="window.location.href='barang.php'" style="background-color: #0275d8; color: white; padding: 10px; border: none; border-radius: 5px; cursor: pointer;">
            Kembali ke Data Barang
        </button>
        <button onclick="window.location.href='riwayat_barang_keluar.php'" style="background-color: #5cb85c; color: white; padding: 10px; border: none; border-radius: 5px; cursor: pointer;">
            Riwayat Transaksi
        </button>
    </div>
</body>
</html>
