// Menunggu seluruh elemen DOM selesai dimuat
document.addEventListener("DOMContentLoaded", function() {
    
    // 1. Menghilangkan Notifikasi Toast Otomatis setelah 3 detik
    const notifBox = document.getElementById("notifBox");
    if (notifBox) {
        setTimeout(function() {
            notifBox.style.opacity = "0";
            setTimeout(() => notifBox.remove(), 500); // Hapus elemen setelah transisi memudar
        }, 3000);
    }

    // 2. Toggle Password Visibility (Mata Password)
    const togglePasswordBtns = document.querySelectorAll('.toggle-password');
    togglePasswordBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            // Mengambil input password di dalam container input-group yang sama
            const passwordInput = this.parentElement.querySelector('input');
            
            if (passwordInput.getAttribute('type') === 'password') {
                passwordInput.setAttribute('type', 'text');
                this.classList.remove('fa-eye');
                this.classList.add('fa-eye-slash');
            } else {
                passwordInput.setAttribute('type', 'password');
                this.classList.remove('fa-eye-slash');
                this.classList.add('fa-eye');
            }
        });
    });

    // 3. Web Push Notification untuk Pengingat Deadline Tugas
    // Meminta izin notifikasi browser jika belum ditentukan
    if (Notification.permission !== "granted" && Notification.permission !== "denied") {
        Notification.requestPermission().then(permission => {
            if (permission === "granted") {
                console.log("Izin notifikasi diberikan.");
            }
        });
    }

    function checkDeadlines() {
        if (Notification.permission !== "granted") return;

        const todoCards = document.querySelectorAll('.todo-card');
        // Load daftar tugas yang sudah dinotifikasi dari LocalStorage agar tidak spamming
        let notifiedTasks = JSON.parse(localStorage.getItem('notifiedTasks') || '[]');
        const now = new Date();

        todoCards.forEach(card => {
            const taskId = card.getAttribute('data-id');
            const taskName = card.getAttribute('data-task');
            const deadlineStr = card.getAttribute('data-deadline');
            const status = card.getAttribute('data-status');

            // Cek jika tugas memiliki batas waktu dan belum selesai ('belum' atau 'sedang_dikerjakan')
            if (deadlineStr && (status === 'belum' || status === 'sedang_dikerjakan')) {
                // Ganti spasi dengan 'T' agar format MySQL (YYYY-MM-DD HH:MM:SS) kompatibel lintas browser
                const deadlineDate = new Date(deadlineStr.replace(' ', 'T'));
                
                // Hitung selisih waktu dalam milidetik
                const diffMs = deadlineDate - now;
                const diffHours = diffMs / (1000 * 60 * 60);

                // Kirim notifikasi jika deadline tersisa kurang dari 2 jam (dan belum terlewati)
                if (diffHours > 0 && diffHours <= 2) {
                    // Pastikan belum pernah diberi notifikasi untuk tugas ini
                    if (!notifiedTasks.includes(taskId)) {
                        const minutesLeft = Math.round(diffHours * 60);
                        
                        // Kirim notifikasi sistem (di luar browser/desktop)
                        new Notification("🚨 Deadline Tugas Hampir Tiba!", {
                            body: `Tugas "${taskName}" harus diselesaikan dalam waktu kurang dari ${minutesLeft} menit lagi!`,
                            icon: 'https://cdn-icons-png.flaticon.com/512/1792/1792931.png'
                        });

                        // Masukkan ID tugas ke LocalStorage agar tidak terus menerus muncul setiap menit
                        notifiedTasks.push(taskId);
                        localStorage.setItem('notifiedTasks', JSON.stringify(notifiedTasks));
                    }
                }
            }
        });
    }

    // Jalankan pengecekan secara langsung saat halaman pertama kali dimuat
    checkDeadlines();

    // Jalankan pengecekan secara berkala setiap 1 menit (60000 ms)
    setInterval(checkDeadlines, 60000);
});