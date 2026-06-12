<?php
require_once '../config/db.php';
if (isset($_POST['add'])) {
    $task = mysqli_real_escape_string($conn, $_POST['task']);
    $description = mysqli_real_escape_string($conn, $_POST['description'] ?? '');
    $deadline = !empty($_POST['deadline']) ? mysqli_real_escape_string($conn, $_POST['deadline']) : null;
    $user_id = $_SESSION['user_id'];
    
    if ($deadline) {
        $query = "INSERT INTO todos (user_id, task, description, deadline, status) VALUES ('$user_id', '$task', '$description', '$deadline', 'belum')";
    } else {
        $query = "INSERT INTO todos (user_id, task, description, deadline, status) VALUES ('$user_id', '$task', '$description', NULL, 'belum')";
    }
    
    mysqli_query($conn, $query);
    $_SESSION['notif'] = "Tugas berhasil ditambahkan!";
    header("Location: ../index.php");
}
?>