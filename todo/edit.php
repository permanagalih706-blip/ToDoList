<?php
require_once '../config/db.php';

// Proteksi halaman, harus login
if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// Cek apakah parameter id tersedia
if (!isset($_GET['id']) || empty($_GET['id'])) {
    $_SESSION['notif'] = "Tugas tidak ditemukan!";
    header("Location: ../index.php");
    exit;
}

$id = mysqli_real_escape_string($conn, $_GET['id']);

// Ambil data tugas berdasarkan id dan user_id (agar aman)
$result = mysqli_query($conn, "SELECT * FROM todos WHERE id = '$id' AND user_id = '$user_id'");
$todo = mysqli_fetch_assoc($result);

if (!$todo) {
    $_SESSION['notif'] = "Tugas tidak ditemukan atau Anda tidak memiliki akses!";
    header("Location: ../index.php");
    exit;
}

// Proses update tugas
if (isset($_POST['update'])) {
    $task = mysqli_real_escape_string($conn, $_POST['task']);
    $description = mysqli_real_escape_string($conn, $_POST['description']);
    $deadline = !empty($_POST['deadline']) ? mysqli_real_escape_string($conn, $_POST['deadline']) : null;
    $status = mysqli_real_escape_string($conn, $_POST['status']);

    // Validasi status agar hanya berisi enum yang diperbolehkan
    if (!in_array($status, ['belum', 'sedang_dikerjakan', 'sudah'])) {
        $status = 'belum';
    }

    if ($deadline) {
        $query = "UPDATE todos SET task = '$task', description = '$description', deadline = '$deadline', status = '$status' WHERE id = '$id' AND user_id = '$user_id'";
    } else {
        $query = "UPDATE todos SET task = '$task', description = '$description', deadline = NULL, status = '$status' WHERE id = '$id' AND user_id = '$user_id'";
    }

    if (mysqli_query($conn, $query)) {
        $_SESSION['notif'] = "Tugas berhasil diperbarui!";
    } else {
        $_SESSION['notif'] = "Gagal memperbarui tugas!";
    }
    header("Location: ../index.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Tugas</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <div class="edit-container">
        <header class="edit-header">
            <h2><i class="fa-solid fa-pen-to-square"></i> Edit Tugas</h2>
            <a href="../index.php" class="btn-back"><i class="fa-solid fa-arrow-left"></i> Kembali</a>
        </header>

        <form method="POST" class="edit-form">
            <div class="form-group">
                <label for="task">Nama Tugas</label>
                <input type="text" id="task" name="task" value="<?= htmlspecialchars($todo['task']) ?>" required placeholder="Masukkan nama tugas...">
            </div>

            <div class="form-group">
                <label for="description">Deskripsi</label>
                <textarea id="description" name="description" placeholder="Masukkan deskripsi tugas..." rows="4"><?= htmlspecialchars($todo['description'] ?? '') ?></textarea>
            </div>

            <div class="form-group">
                <label for="deadline">Batas Waktu (Deadline)</label>
                <?php 
                // Format deadline dari MySQL datetime ke format datetime-local input (Y-m-d\TH:i)
                $formatted_deadline = "";
                if (!empty($todo['deadline'])) {
                    $formatted_deadline = date('Y-m-d\TH:i', strtotime($todo['deadline']));
                }
                ?>
                <input type="datetime-local" id="deadline" name="deadline" value="<?= $formatted_deadline ?>">
            </div>

            <div class="form-group">
                <label for="status">Status</label>
                <select id="status" name="status" required>
                    <option value="belum" <?= $todo['status'] == 'belum' ? 'selected' : '' ?>>Belum Dikerjakan</option>
                    <option value="sedang_dikerjakan" <?= $todo['status'] == 'sedang_dikerjakan' ? 'selected' : '' ?>>Sedang Dikerjakan</option>
                    <option value="sudah" <?= $todo['status'] == 'sudah' ? 'selected' : '' ?>>Sudah Selesai</option>
                </select>
            </div>

            <button type="submit" name="update" class="btn-save"><i class="fa-solid fa-floppy-disk"></i> Simpan Perubahan</button>
        </form>
    </div>
</body>
</html>
