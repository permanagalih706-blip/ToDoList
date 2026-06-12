<?php
require_once '../config/db.php';
if (isset($_POST['id'])) {
    $id = $_POST['id'];
    $status = $_POST['status'];
    $user_id = $_SESSION['user_id']; // Keamanan ekstra
    
    mysqli_query($conn, "UPDATE todos SET status='$status' WHERE id='$id' AND user_id='$user_id'");
    $_SESSION['notif'] = "Status tugas diperbarui!";
    header("Location: ../index.php");
}
?>