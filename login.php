<?php
session_start();

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
    $email = $_POST['email'];
    $password = $_POST['password'];

    $sql = "SELECT admin_id, nama_admin FROM admin WHERE email = ? AND password = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $email, $password);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $_SESSION['admin_id'] = $row['admin_id'];
        $_SESSION['nama_admin'] = $row['nama_admin'];
        header("Location: barang.php");
        exit();
    } else {
        $message = "<div class='alert error'>Email atau password salah. Coba lagi.</div>";
    }

    $stmt->close();
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Halaman Login</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container-login">
        <h2>Login Admin</h2>
        <?= $message ?>
        <form method="POST" action="">
            <label for="email">Email:</label>
            <input type="email" id="email" name="email" required>

            <label for="password">Password:</label>
            <input type="password" id="password" name="password" required>

            <button type="submit">Login</button>
        </form>
        <br>
        <p>Belum punya akun atau lupa password? 
            <a href="form_admin.php" style="color: #0275d8; text-decoration: none;">Klik di sini untuk mendaftar</a>
        </p>
    </div>
</body>
</html>