<?php
require_once 'config/db.php';

// Proteksi halaman, harus login
if (!isset($_SESSION['user_id'])) {
    header("Location: auth/login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$username = $_SESSION['username'] ?? 'User';

// Ambil semua tugas untuk user ini, urutkan berdasarkan deadline terdekat (jika ada), lalu created_at DESC
$result = mysqli_query($conn, "
    SELECT * FROM todos 
    WHERE user_id = '$user_id' 
    ORDER BY 
        CASE WHEN deadline IS NULL THEN 1 ELSE 0 END, 
        deadline ASC, 
        created_at DESC
");
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My To-Do List</title>
    <!-- FontAwesome CDN untuk Ikon -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <div class="container">
        <!-- Header Section -->
        <header>
            <div class="header-title">
                <h2><i class="fa-solid fa-list-check"></i> To-Do List</h2>
                <p>Halo, <strong><?= htmlspecialchars($username) ?></strong>! Kelola tugas harianmu di sini.</p>
            </div>
            <a href="auth/logout.php" class="btn-logout"><i class="fa-solid fa-right-from-bracket"></i> Keluar</a>
        </header>

        <!-- Notification Toast -->
        <?php if(isset($_SESSION['notif'])): ?>
            <div class="notif" id="notifBox">
                <i class="fa-solid fa-circle-info"></i>
                <span><?= $_SESSION['notif']; ?></span>
            </div>
            <?php unset($_SESSION['notif']); ?>
        <?php endif; ?>

        <!-- Form Tambah Tugas -->
        <section class="add-section">
            <h3><i class="fa-solid fa-plus"></i> Tambah Tugas Baru</h3>
            <form action="todo/add.php" method="POST" class="add-form">
                <div class="form-group">
                    <input type="text" name="task" placeholder="Apa yang ingin kamu kerjakan?" required>
                </div>
                <div class="form-group">
                    <textarea name="description" placeholder="Deskripsi tugas (opsional)" rows="2"></textarea>
                </div>
                <div class="form-row">
                    <div class="form-group half">
                        <label for="deadline_input"><i class="fa-solid fa-calendar-day"></i> Batas Waktu (Deadline)</label>
                        <input type="datetime-local" id="deadline_input" name="deadline">
                    </div>
                    <div class="form-group button-group">
                        <button type="submit" name="add" class="btn-add"><i class="fa-solid fa-plus"></i> Tambah</button>
                    </div>
                </div>
            </form>
        </section>

        <!-- Daftar Tugas -->
        <section class="list-section">
            <h3><i class="fa-solid fa-tasks"></i> Daftar Tugas Kamu</h3>
            
            <?php if (mysqli_num_rows($result) == 0): ?>
                <div class="empty-state">
                    <i class="fa-regular fa-clipboard"></i>
                    <p>Belum ada tugas. Silakan tambahkan tugas di atas!</p>
                </div>
            <?php else: ?>
                <div class="todo-grid">
                    <?php while($row = mysqli_fetch_assoc($result)): 
                        // Menentukan warna & label status
                        $status_class = '';
                        $status_label = '';
                        if ($row['status'] == 'belum') {
                            $status_class = 'status-belum';
                            $status_label = 'Belum';
                            $next_status = 'sedang_dikerjakan';
                            $next_icon = 'fa-play';
                            $next_title = 'Mulai Dikerjakan';
                        } elseif ($row['status'] == 'sedang_dikerjakan') {
                            $status_class = 'status-proses';
                            $status_label = 'Sedang Dikerjakan';
                            $next_status = 'sudah';
                            $next_icon = 'fa-check';
                            $next_title = 'Selesaikan';
                        } else {
                            $status_class = 'status-sudah';
                            $status_label = 'Sudah Selesai';
                            $next_status = 'belum';
                            $next_icon = 'fa-rotate-left';
                            $next_title = 'Kerjakan Ulang';
                        }

                        // Cek apakah deadline terlewati
                        $is_overdue = false;
                        if (!empty($row['deadline']) && $row['status'] != 'sudah') {
                            $is_overdue = (strtotime($row['deadline']) < time());
                        }
                    ?>
                        <div class="todo-card <?= $status_class ?> <?= $is_overdue ? 'overdue' : '' ?>" 
                             data-id="<?= $row['id'] ?>"
                             data-task="<?= htmlspecialchars($row['task']) ?>"
                             data-deadline="<?= !empty($row['deadline']) ? $row['deadline'] : '' ?>"
                             data-status="<?= $row['status'] ?>">
                            
                            <div class="card-header">
                                <span class="badge <?= $status_class ?>"><?= $status_label ?></span>
                                <?php if ($is_overdue): ?>
                                    <span class="badge badge-overdue"><i class="fa-solid fa-triangle-exclamation"></i> Terlambat</span>
                                <?php endif; ?>
                            </div>

                            <div class="card-body">
                                <h4 class="task-title"><?= htmlspecialchars($row['task']) ?></h4>
                                <?php if (!empty($row['description'])): ?>
                                    <p class="task-desc"><?= nl2br(htmlspecialchars($row['description'])) ?></p>
                                <?php endif; ?>
                            </div>

                            <div class="card-footer">
                                <div class="task-meta">
                                    <?php if (!empty($row['deadline'])): ?>
                                        <span class="meta-deadline" title="Deadline">
                                            <i class="fa-regular fa-clock"></i> 
                                            <?= date('d M Y H:i', strtotime($row['deadline'])) ?>
                                        </span>
                                    <?php else: ?>
                                        <span class="meta-deadline no-deadline">
                                            <i class="fa-regular fa-clock"></i> Tanpa Deadline
                                        </span>
                                    <?php endif; ?>
                                </div>

                                <div class="card-actions">
                                    <!-- Tombol Siklus Status Cepat -->
                                    <form action="todo/update.php" method="POST" class="inline-form">
                                        <input type="hidden" name="id" value="<?= $row['id'] ?>">
                                        <input type="hidden" name="status" value="<?= $next_status ?>">
                                        <button type="submit" class="action-btn btn-status" title="<?= $next_title ?>">
                                            <i class="fa-solid <?= $next_icon ?>"></i>
                                        </button>
                                    </form>

                                    <!-- Tombol Edit -->
                                    <a href="todo/edit.php?id=<?= $row['id'] ?>" class="action-btn btn-edit" title="Edit Tugas">
                                        <i class="fa-solid fa-pen-to-square"></i>
                                    </a>

                                    <!-- Tombol Hapus -->
                                    <form action="todo/delete.php" method="POST" class="inline-form">
                                        <input type="hidden" name="id" value="<?= $row['id'] ?>">
                                        <button type="submit" class="action-btn btn-delete" title="Hapus Tugas" onclick="return confirm('Hapus tugas ini?')">
                                            <i class="fa-solid fa-trash-can"></i>
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    <?php endwhile; ?>
                </div>
            <?php endif; ?>
        </section>
    </div>

    <!-- Custom Script -->
    <script src="assets/js/script.js"></script>
</body>
</html>