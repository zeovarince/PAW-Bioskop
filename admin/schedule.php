<?php
session_start();
if (!isset($_SESSION['login']) || $_SESSION['role'] != '1') {
    header("Location: ../login.php");
    exit;
}
include "../koneksi.php";
include "../function.php"; // Memuat fungsi CRUD Jadwal

// Ambil semua data jadwal
$schedules = getSchedules();
$schedule_count = count($schedules);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Jadwal - Onic Cinema Admin</title>
    <link rel="icon" href="../logo.png">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://unpkg.com/@phosphor-icons/web@2.1.1/src/css/icons.css">
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
<body class="bg-gray-50 font-sans text-gray-800">

    <!-- Navigasi Admin -->
    <nav class="glass-nav fixed w-full z-50 bg-cinemaBlack">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-20">
                <div class="flex items-center gap-4">
                    <img src="../logo.png" alt="Onic Logo" class="h-11 w-auto object-contain drop-shadow-lg">
                    <h1 class="text-3xl font-bold text-cinemaGold tracking-widest uppercase" style="text-shadow: 0px 0px 7px;">
                        ONIC <span class="text-white">ADMINISTRATOR</span>
                    </h1>
                </div>

                <div class="hidden md:block">
                    <div class="ml-10 flex items-baseline space-x-4">
                        <a href="index.php" class="text-gray-300 hover:text-cinemaRed px-3 py-2 rounded-md text-sm font-medium transition">Dashboard</a>
                        <a href="movies.php" class="text-gray-300 hover:text-cinemaRed px-3 py-2 rounded-md text-sm font-medium transition">Movies</a>
                        <a href="studio_admin.php" class="text-gray-300 hover:text-cinemaRed px-3 py-2 rounded-md text-sm font-medium transition">Studio</a>
                        <a href="schedule.php" class="bg-cinemaGold text-black px-3 py-2 text-l rounded-full text-sm font-medium transition">Schedules</a>
                        <a href="validation.php" class="text-gray-300 hover:text-cinemaRed px-3 py-2 rounded-md text-sm font-medium transition">Validation</a>
                        <a href="report.php" class="text-gray-300 hover:text-cinemaRed px-3 py-2 rounded-md text-sm font-medium transition">Report</a>
                    </div>
                </div>

                <div class="flex items-center gap-4">
                    <div class="hidden md:flex flex-col items-end">
                        <span class="text-white text-sm font-bold"><?= isset($_SESSION['username']) ? $_SESSION['username'] : 'Admin' ?></span>
                        <span class="text-gray-400 text-xs">Administrator</span>
                    </div>
                    <a href="../logout.php" class="text-gray-400 hover:text-white transition" title="Logout">
                        <i class="ph ph-sign-out text-2xl"></i>
                    </a>
                </div>
            </div>
        </div>
    </nav>
    <!-- End Navigasi -->

    <div class="pt-24 pb-12 min-h-screen">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

            <!-- Judul Halaman -->
            <div class="mb-8 flex justify-between items-center border-b pb-4">
                <div>
                    <h2 class="text-3xl font-extrabold text-cinemaBlack flex items-center gap-2">
                        <i class="ph ph-calendar-check text-cinemaRed"></i> Daftar Jadwal Tayang
                    </h2>
                    <p class="text-gray-500 mt-1">Total: <?= $schedule_count ?> Jadwal</p>
                </div>
                <div class="flex gap-4">
                    <a href="manage_studio.php" class="bg-gray-200 hover:bg-gray-300 text-gray-800 px-5 py-2.5 rounded-lg font-semibold shadow-md transition flex items-center gap-2 text-sm">
                        <i class="ph ph-projector-screen-chart"></i> Kelola Studio
                    </a>
                    <a href="manage_jadwal.php" class="bg-cinemaRed hover:bg-red-700 text-white px-5 py-2.5 rounded-lg font-semibold shadow-md transition transform hover:-translate-y-0.5 flex items-center gap-2">
                        <i class="ph ph-plus-circle"></i> Tambah Jadwal Baru
                    </a>
                </div>
            </div>

            <!-- Tampilkan Pesan Sukses/Error (jika ada) -->
            <?php if (isset($_SESSION['message'])): ?>
                <div id="alert" class="p-4 mb-4 text-sm rounded-lg <?= strpos($_SESSION['message'], 'Gagal') !== false ? 'bg-red-100 text-red-700' : 'bg-green-100 text-green-700' ?>" role="alert">
                    <?= $_SESSION['message']; ?>
                </div>
                <?php unset($_SESSION['message']); ?>
            <?php endif; ?>

            <!-- Tabel Daftar Jadwal -->
            <div class="bg-white shadow-xl rounded-xl overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-100">
                            <tr>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider w-1/12">ID</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider w-4/12">Film</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider w-2/12">Studio</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider w-3/12">Waktu Tayang</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider w-2/12">Harga</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider w-2/12">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <?php if ($schedule_count > 0): ?>
                                <?php foreach ($schedules as $schedule): 
                                    $is_past = strtotime($schedule['Waktu_tayang']) < time();
                                ?>
                                    <tr class="<?= $is_past ? 'bg-gray-50 text-gray-400' : 'text-gray-900' ?>">
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium"><?= $schedule['Id_jadwal'] ?></td>
                                        <td class="px-6 py-4 text-sm font-bold">
                                            <?= htmlspecialchars($schedule['Judul_Film']) ?>
                                            <?php if ($is_past): ?>
                                                <span class="ml-2 px-2 py-0.5 text-xs font-semibold bg-gray-200 text-gray-500 rounded-full">Selesai</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm"><?= htmlspecialchars($schedule['nama_studio']) ?></td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm">
                                            <p><?= date('d M Y', strtotime($schedule['Waktu_tayang'])) ?></p>
                                            <p class="font-semibold"><?= date('H:i', strtotime($schedule['Waktu_tayang'])) ?> WIB</p>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-bold <?= $is_past ? 'text-gray-400' : 'text-cinemaRed' ?>">
                                            Rp. <?= number_format($schedule['harga'], 0, ',', '.') ?>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium flex items-center gap-3">
                                            <?php if (!$is_past): ?>
                                            <!-- Tombol Edit -->
                                            <a href="manage_jadwal.php?id=<?= $schedule['Id_jadwal'] ?>" class="text-blue-600 hover:text-blue-900 transition flex items-center gap-1">
                                                <i class="ph ph-pencil-simple"></i> Edit
                                            </a>
                                            <!-- Tombol Hapus (Trigger Modal) -->
                                            <button onclick="openModal(<?= $schedule['Id_jadwal'] ?>)" class="text-red-600 hover:text-red-900 transition flex items-center gap-1">
                                                <i class="ph ph-trash"></i> Hapus
                                            </button>
                                            <?php else: ?>
                                                -
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="6" class="px-6 py-10 text-center text-gray-500">
                                        <i class="ph ph-calendar-x text-5xl text-gray-200 mb-3 block"></i>
                                        Tidak ada jadwal tayang yang terdaftar.
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
    <div id="deleteModal" class="modal fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center opacity-0 pointer-events-none z-[100]">
        <div class="bg-white rounded-lg shadow-2xl w-full max-w-sm p-6 transform translate-y-2 scale-95 transition-all duration-300">
            <div class="text-center">
                <i class="ph ph-warning-circle text-6xl text-red-500 mx-auto mb-4"></i>
                <h3 class="text-lg font-bold text-gray-900 mb-2">Konfirmasi Hapus</h3>
                <p class="text-sm text-gray-500 mb-6">Anda yakin ingin menghapus jadwal ini? Jadwal dengan booking aktif tidak dapat dihapus.</p>
                
                <div class="flex justify-center gap-4">
                    <button onclick="closeModal()" class="px-4 py-2 bg-gray-200 text-gray-800 rounded-lg hover:bg-gray-300 transition font-semibold">
                        Batal
                    </button>
                    <a href="#" id="deleteConfirmBtn" class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition font-semibold">
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
            
            // Set action URL pada tombol konfirmasi
            confirmBtn.href = `action_jadwal.php?action=delete&id=${scheduleId}`;
            
            // Tampilkan modal
            modal.classList.remove('opacity-0', 'pointer-events-none');
            modal.querySelector('div').classList.remove('translate-y-2', 'scale-95');
        }

        function closeModal() {
            const modal = document.getElementById('deleteModal');
            
            // Sembunyikan modal
            modal.classList.add('opacity-0', 'pointer-events-none');
            modal.querySelector('div').classList.add('translate-y-2', 'scale-95');
        }

        // Auto hide alert after 5 seconds
        setTimeout(() => {
            const alert = document.getElementById('alert');
            if (alert) {
                alert.style.opacity = '0';
                setTimeout(() => alert.remove(), 500);
            }
        }, 5000);
    </script>
</body>
</html>