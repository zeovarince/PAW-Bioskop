<?php
session_start();
if (!isset($_SESSION['login']) || $_SESSION['role'] != '1') {
    header("Location: ../login.php");
    exit;
}
include "../koneksi.php";
include "../function.php"; // Memuat fungsi CRUD Jadwal

// Ambil semua data jadwal
// Kita modifikasi query di sini untuk sorting yang lebih baik:
// 1. Jadwal yang BELUM lewat (Waktu_tayang >= NOW) ditaruh di atas (ASC)
// 2. Jadwal yang SUDAH lewat ditaruh di bawah (DESC)
$query_jadwal = "
    SELECT 
        j.Id_jadwal,
        j.Waktu_tayang, 
        j.harga,
        m.judul AS Judul_Film, 
        s.nama_studio,
        j.Id_movie,
        j.Id_studio
    FROM jadwal j
    JOIN movies m ON j.Id_movie = m.Id_movie
    JOIN studios s ON j.Id_studio = s.Id_studio
    ORDER BY 
        CASE WHEN j.Waktu_tayang >= NOW() THEN 0 ELSE 1 END ASC, 
        CASE WHEN j.Waktu_tayang >= NOW() THEN j.Waktu_tayang END ASC,
        CASE WHEN j.Waktu_tayang < NOW() THEN j.Waktu_tayang END DESC
";

$result_jadwal = mysqli_query($conn, $query_jadwal);
$schedules = [];
while ($row = mysqli_fetch_assoc($result_jadwal)) {
    $schedules[] = $row;
}

$schedule_count = count($schedules);

// Hitung Total Pending (Untuk Badge Navbar)
$total_pending = 0; 
if (file_exists("../koneksi.php")) { // Cek file koneksi jika perlu, tapi sudah diinclude diatas
    $pending = mysqli_query($conn, "SELECT COUNT(*) as total FROM booking WHERE status_booking = '2'");
    if ($pending) {
        $total_pending = mysqli_fetch_assoc($pending)['total'];
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Jadwal - Onic Cinema Admin</title>
    <link rel="icon" href="../logo.png">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        cinemaBlack: '#141414',
                        cinemaDark: '#1f1f1f',
                        cinemaRed: '#E50914',
                        cinemaGold: '#FFD700',
                    }
                }
            }
        }
    </script>
    <!-- Phosphor Icons -->
    <script src="https://unpkg.com/@phosphor-icons/web"></script>
    
    <style>
        .glass-nav {
            background: rgba(20, 20, 20, 0.9);
            backdrop-filter: blur(10px);
        }
        .modal {
            transition: opacity 0.3s ease-in-out;
        }
    </style>
</head>
<body class="bg-gray-100 font-sans text-gray-800">

    <!-- NAVBAR ADMIN (Updated Sesuai Index) -->
    <nav class="glass-nav fixed w-full z-50 bg-cinemaBlack border-b border-gray-800">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-20">
                
                <!-- Logo -->
                <div class="flex items-center gap-4">
                    <img src="../logo.png" alt="Onic Logo" class="h-11 w-auto object-contain drop-shadow-lg">
                    <h1 class="text-3xl font-bold text-cinemaGold tracking-widest uppercase" style="text-shadow: 0px 0px 7px;">
                        ONIC <span class="text-white">ADMINISTRATOR</span>
                    </h1>
                </div>

                <!-- Menu Tengah -->
                <div class="hidden md:block">
                    <div class="ml-10 flex items-baseline space-x-4">
                        <a href="index.php" class="text-gray-300 hover:text-cinemaRed px-3 py-2 rounded-md text-sm font-medium transition">Dashboard</a>
                        <a href="movies.php" class="text-gray-300 hover:text-cinemaRed px-3 py-2 rounded-md text-sm font-medium transition">Movies</a>
                        <a href="studio_admin.php" class="text-gray-300 hover:text-cinemaRed px-3 py-2 rounded-md text-sm font-medium transition">Studio</a>
                        
                        <!-- Menu Schedule Aktif -->
                        <a href="schedule.php" class="bg-cinemaGold text-black px-3 py-2 rounded-full text-sm font-bold transition shadow-lg">Schedules</a>
                        
                        <a href="validation.php" class="text-gray-300 hover:text-cinemaRed px-3 py-2 rounded-md text-sm font-medium transition flex items-center gap-1">
                            Validation
                            <?php if($total_pending > 0): ?>
                                <span class="ml-1 bg-red-600 text-white text-xs px-1.5 py-0.5 rounded-full animate-pulse"><?= $total_pending ?></span>
                            <?php endif; ?>
                        </a>
                        <a href="report.php" class="text-gray-300 hover:text-cinemaRed px-3 py-2 rounded-md text-sm font-medium transition">Report</a>
                    </div>
                </div>

                <!-- Menu Kanan (Profile) -->
                <div class="flex items-center gap-4">
                    <div class="hidden md:flex flex-col items-end">
                        <span class="text-white text-sm font-bold"><?= isset($_SESSION['username']) ? $_SESSION['username'] : 'Admin' ?></span>
                        <span class="text-gray-400 text-xs">Administrator</span>
                    </div>
                    <a href="../logout.php" onclick="return confirm('Yakin ingin logout?')" class="text-gray-400 hover:text-white transition" title="Logout">
                        <i class="ph ph-sign-out text-2xl"></i>
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <!-- CONTENT -->
    <div class="pt-24 pb-12 min-h-screen">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

            <!-- Judul Halaman -->
            <div class="mb-8 flex justify-between items-center border-b border-gray-200 pb-4">
                <div>
                    <h2 class="text-3xl font-extrabold text-gray-900 flex items-center gap-2">
                        <i class="ph ph-calendar-check text-cinemaRed"></i> Daftar Jadwal Tayang
                    </h2>
                    <p class="text-gray-500 mt-1">Total: <?= $schedule_count ?> Jadwal Terdaftar</p>
                </div>
                <div class="flex gap-3">
                    <a href="manage_studio.php" class="bg-white border border-gray-300 text-gray-700 hover:bg-gray-50 px-4 py-2 rounded-lg font-semibold shadow-sm transition flex items-center gap-2 text-sm">
                        <i class="ph ph-projector-screen-chart text-lg"></i> Kelola Studio
                    </a>
                    <a href="manage_jadwal.php" class="bg-cinemaRed hover:bg-red-700 text-white px-4 py-2 rounded-lg font-bold shadow-lg transition transform hover:-translate-y-0.5 flex items-center gap-2 text-sm">
                        <i class="ph ph-plus-circle text-lg"></i> Tambah Jadwal
                    </a>
                </div>
            </div>

            <!-- Pesan Notifikasi -->
            <?php if (isset($_SESSION['message'])): ?>
                <div id="alert" class="p-4 mb-6 text-sm rounded-lg shadow-sm flex items-center gap-2 <?= strpos($_SESSION['message'], 'Gagal') !== false ? 'bg-red-100 text-red-800 border border-red-200' : 'bg-green-100 text-green-800 border border-green-200' ?>" role="alert">
                    <i class="ph <?= strpos($_SESSION['message'], 'Gagal') !== false ? 'ph-warning-circle' : 'ph-check-circle' ?> text-xl"></i>
                    <?= $_SESSION['message']; ?>
                </div>
                <?php unset($_SESSION['message']); ?>
            <?php endif; ?>

            <!-- Tabel Jadwal -->
            <div class="bg-white shadow-xl rounded-xl overflow-hidden border border-gray-100">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <!-- Kolom ID Dihapus -->
                                <th scope="col" class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-wider w-1/3">Film</th>
                                <th scope="col" class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Studio</th>
                                <th scope="col" class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Waktu Tayang</th>
                                <th scope="col" class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Harga Tiket</th>
                                <th scope="col" class="px-6 py-4 text-center text-xs font-bold text-gray-500 uppercase tracking-wider w-1/6">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <?php if ($schedule_count > 0): ?>
                                <?php foreach ($schedules as $schedule): 
                                    $waktu_tayang = strtotime($schedule['Waktu_tayang']);
                                    $is_past = $waktu_tayang < time();
                                    $is_today = date('Y-m-d', $waktu_tayang) == date('Y-m-d');
                                ?>
                                    <tr class="hover:bg-gray-50 transition group <?= $is_past ? 'bg-gray-50 opacity-60' : '' ?>">
                                        
                                        <td class="px-6 py-4">
                                            <div class="flex items-center">
                                                <div>
                                                    <div class="text-sm font-bold text-gray-900 <?= $is_past ? 'text-gray-500' : '' ?>">
                                                        <?= htmlspecialchars($schedule['Judul_Film']) ?>
                                                    </div>
                                                    <?php if ($is_past): ?>
                                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-gray-200 text-gray-600 mt-1">
                                                            Selesai
                                                        </span>
                                                    <?php elseif ($is_today): ?>
                                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-green-100 text-green-800 mt-1 animate-pulse">
                                                            Hari Ini
                                                        </span>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        </td>

                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="text-sm text-gray-700 bg-gray-100 px-2 py-1 rounded border border-gray-200">
                                                <?= htmlspecialchars($schedule['nama_studio']) ?>
                                            </span>
                                        </td>

                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm text-gray-900 font-medium">
                                                <?= date('H:i', $waktu_tayang) ?> WIB
                                            </div>
                                            <div class="text-xs text-gray-500">
                                                <?= date('d M Y', $waktu_tayang) ?>
                                            </div>
                                        </td>

                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="text-sm font-bold <?= $is_past ? 'text-gray-500' : 'text-cinemaRed' ?>">
                                                Rp <?= number_format($schedule['harga'], 0, ',', '.') ?>
                                            </span>
                                        </td>

                                        <td class="px-6 py-4 whitespace-nowrap text-center text-sm font-medium">
                                            <div class="flex justify-center items-center gap-2 opacity-0 group-hover:opacity-100 transition-opacity duration-200 <?= $is_past ? 'opacity-50' : '' ?>">
                                                <?php if (!$is_past): ?>
                                                    <a href="manage_jadwal.php?id=<?= $schedule['Id_jadwal'] ?>" class="text-blue-600 hover:text-blue-800 bg-blue-50 hover:bg-blue-100 p-2 rounded-lg transition" title="Edit Jadwal">
                                                        <i class="ph ph-pencil-simple text-lg"></i>
                                                    </a>
                                                    <button onclick="openModal(<?= $schedule['Id_jadwal'] ?>)" class="text-red-600 hover:text-red-800 bg-red-50 hover:bg-red-100 p-2 rounded-lg transition" title="Hapus Jadwal">
                                                        <i class="ph ph-trash text-lg"></i>
                                                    </button>
                                                <?php else: ?>
                                                    <button disabled class="text-gray-400 cursor-not-allowed p-2" title="Jadwal Selesai">
                                                        <i class="ph ph-lock-key text-lg"></i>
                                                    </button>
                                                <?php endif; ?>
                                            </div>
                                        </td>

                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="5" class="px-6 py-16 text-center">
                                        <div class="flex flex-col items-center justify-center text-gray-400">
                                            <i class="ph ph-calendar-x text-5xl mb-3 text-gray-300"></i>
                                            <p class="text-lg font-medium text-gray-500">Belum ada jadwal tayang.</p>
                                            <p class="text-sm">Silakan tambah jadwal baru untuk memulai.</p>
                                        </div>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </div>

    <!-- Modal Konfirmasi Hapus -->
    <div id="deleteModal" class="modal fixed inset-0 bg-black/80 backdrop-blur-sm flex items-center justify-center opacity-0 pointer-events-none z-[100]">
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-sm p-6 transform translate-y-4 scale-95 transition-all duration-300">
            <div class="text-center">
                <div class="bg-red-100 p-3 rounded-full w-16 h-16 flex items-center justify-center mx-auto mb-4">
                    <i class="ph ph-trash text-3xl text-red-600"></i>
                </div>
                <h3 class="text-xl font-bold text-gray-900 mb-2">Hapus Jadwal?</h3>
                <p class="text-sm text-gray-500 mb-6">Tindakan ini tidak dapat dibatalkan. Pastikan tidak ada tiket yang sudah terjual untuk jadwal ini.</p>
                
                <div class="flex justify-center gap-3">
                    <button onclick="closeModal()" class="px-5 py-2.5 bg-gray-100 text-gray-700 rounded-xl hover:bg-gray-200 transition font-bold text-sm">
                        Batal
                    </button>
                    <a href="#" id="deleteConfirmBtn" class="px-5 py-2.5 bg-red-600 text-white rounded-xl hover:bg-red-700 transition font-bold text-sm shadow-lg shadow-red-500/30">
                        Ya, Hapus
                    </a>
                </div>
            </div>
        </div>
    </div>

    <script>
        function openModal(scheduleId) {
            const modal = document.getElementById('deleteModal');
            const confirmBtn = document.getElementById('deleteConfirmBtn');
            const modalContent = modal.querySelector('div');
            
            confirmBtn.href = `action_jadwal.php?action=delete&id=${scheduleId}`;
            
            modal.classList.remove('opacity-0', 'pointer-events-none');
            modalContent.classList.remove('translate-y-4', 'scale-95');
            modalContent.classList.add('translate-y-0', 'scale-100');
        }

        function closeModal() {
            const modal = document.getElementById('deleteModal');
            const modalContent = modal.querySelector('div');
            
            modal.classList.add('opacity-0', 'pointer-events-none');
            modalContent.classList.add('translate-y-4', 'scale-95');
            modalContent.classList.remove('translate-y-0', 'scale-100');
        }

        // Auto hide alert
        setTimeout(() => {
            const alert = document.getElementById('alert');
            if (alert) {
                alert.style.transition = 'opacity 0.5s ease';
                alert.style.opacity = '0';
                setTimeout(() => alert.remove(), 500);
            }
        }, 4000);
    </script>
</body>
</html>