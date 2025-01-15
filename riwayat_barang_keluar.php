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

$jumlah_item = isset($_SESSION['jumlah_item']) ? $_SESSION['jumlah_item'] : null;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['hapus_semua'])) {
    $conn->begin_transaction();

    try {
        $barang_ids_sql = "SELECT barang_id FROM barang WHERE jumlah_item = 0";
        $barang_ids_result = $conn->query($barang_ids_sql);

        if ($barang_ids_result->num_rows > 0) {
            while ($row = $barang_ids_result->fetch_assoc()) {
                $barang_id = $row['barang_id'];

                $delete_transaksi_sql = "DELETE FROM transaksi_barang WHERE barang_id = ?";
                $stmt = $conn->prepare($delete_transaksi_sql);
                $stmt->bind_param("i", $barang_id);
                if (!$stmt->execute()) {
                    throw new Exception("Gagal menghapus transaksi untuk barang_id $barang_id: " . $stmt->error);
                }

                $delete_barang_sql = "DELETE FROM barang WHERE barang_id = ?";
                $stmt = $conn->prepare($delete_barang_sql);
                $stmt->bind_param("i", $barang_id);
                if (!$stmt->execute()) {
                    throw new Exception("Gagal menghapus barang untuk barang_id $barang_id: " . $stmt->error);
                }
            }
        }

        $conn->commit();
        echo "<script>alert('Semua data riwayat berhasil dihapus!');</script>";
    } catch (Exception $e) {
        $conn->rollback();
        echo "<script>alert('Gagal menghapus: " . $e->getMessage() . "');</script>";
    }
}

$sql = "
    SELECT 
        tb.transaksi_id, 
        b.nama_barang, 
        b.kategori, 
        tb.waktu_transaksi, 
        a.nama_admin
    FROM 
        transaksi_barang tb
    JOIN 
        admin a 
    ON 
        tb.admin_id = a.admin_id
    JOIN 
        barang b
    ON 
        tb.barang_id = b.barang_id
    WHERE 
        tb.tipe_transaksi = 'keluar'
    ORDER BY 
        tb.waktu_transaksi DESC
";

$result = $conn->query($sql);

$conn->close();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Riwayat Barang Keluar</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container-riwayat">
        <h2>Riwayat Barang Keluar</h2>
        <form method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus semua riwayat barang keluar?')">
            <button type="submit" name="hapus_semua" style="background-color: #d9534f; color: white; padding: 10px; border: none; border-radius: 5px; cursor: pointer;">
                Hapus Semua Riwayat
            </button>
        </form>
        <table border="1" cellpadding="10" cellspacing="0">
            <thead>
                <tr>
                    <th>ID Transaksi</th>
                    <th>Nama Barang</th>
                    <th>Kategori</th>
                    <th>Jumlah Item</th>
                    <th>Waktu Transaksi</th>
                    <th>Nama Admin</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($result->num_rows > 0): ?>
                    <?php while ($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td><?= $row['transaksi_id'] ?></td>
                            <td><?= htmlspecialchars($row['nama_barang']) ?></td>
                            <td><?= htmlspecialchars($row['kategori']) ?></td>
                            <td><?= $jumlah_item !== null ? $jumlah_item : 'N/A' ?></td>
                            <td><?= $row['waktu_transaksi'] ?></td>
                            <td><?= htmlspecialchars($row['nama_admin']) ?></td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="6">Tidak ada riwayat barang keluar.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
        <br>
        <button onclick="window.location.href='barang_keluar.php'" style="background-color: #0275d8; color: white; padding: 10px; border: none; border-radius: 5px; cursor: pointer;">
            Kembali
        </button>
    </div>
</body>
</html>
