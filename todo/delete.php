<?php
require_once '../config/db.php';
if (isset($_POST['id'])) {
    $id = $_POST['id'];
    $user_id = $_SESSION['user_id']; // Keamanan ekstra mencegah hapus data orang lain
    
    mysqli_query($conn, "DELETE FROM todos WHERE id='$id' AND user_id='$user_id'");
    $_SESSION['notif'] = "Tugas berhasil dihapus!";
    header("Location: ../index.php");
}
?>