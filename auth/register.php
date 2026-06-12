<?php
require_once '../config/db.php';

if (isset($_POST['register'])) {
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT); // Enkripsi password

    $query = "INSERT INTO users (username, password) VALUES ('$username', '$password')";
    if (mysqli_query($conn, $query)) {
        $_SESSION['notif'] = "Registrasi berhasil! Silakan login.";
        header("Location: login.php");
        exit;
    } else {
        $error = "Username sudah digunakan!";
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <title>Register</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <div class="auth-container">
        <h2>Register</h2>
        <?php if(isset($error)) echo "<p class='error'>$error</p>"; ?>
        <form method="POST">
            <div class="input-group">
                <input type="text" name="username" placeholder="Username" required>
            </div>
            <div class="input-group password-group">
                <input type="password" name="password" placeholder="Password" required>
                <i class="fa-solid fa-eye toggle-password"></i>
            </div>
            <button type="submit" name="register">Daftar</button>
        </form>
        <p>Sudah punya akun? <a href="login.php">Login di sini</a></p>
    </div>
    <script src="../assets/js/script.js"></script>
</body>
</html>