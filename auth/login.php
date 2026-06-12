<?php
require_once '../config/db.php';

// Redirect jika sudah login
if (isset($_SESSION['user_id'])) {
    header("Location: ../index.php");
    exit;
}

if (isset($_POST['login'])) {
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $password = $_POST['password'];

    $result = mysqli_query($conn, "SELECT * FROM users WHERE username = '$username'");
    $user = mysqli_fetch_assoc($result);

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['notif'] = "Selamat datang, " . $user['username'] . "!";
        header("Location: ../index.php");
        exit;
    } else {
        $error = "Username atau password salah!";
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <title>Login</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <div class="auth-container">
        <h2>Login</h2>
        <?php if(isset($_SESSION['notif'])) { echo "<p class='success'>".$_SESSION['notif']."</p>"; unset($_SESSION['notif']); } ?>
        <?php if(isset($error)) echo "<p class='error'>$error</p>"; ?>
        
        <form method="POST">
            <div class="input-group">
                <input type="text" name="username" placeholder="Username" required>
            </div>
            <div class="input-group password-group">
                <input type="password" name="password" placeholder="Password" required>
                <i class="fa-solid fa-eye toggle-password"></i>
            </div>
            <button type="submit" name="login">Login</button>
        </form>
        <p>Belum punya akun? <a href="register.php">Daftar sekarang</a></p>
    </div>
    <script src="../assets/js/script.js"></script>
</body>
</html>