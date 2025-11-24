<?php
session_start();
if (!isset($_SESSION['login']) || $_SESSION['role'] != '1') {
    header("Location: ../login.php");
    exit;
}
include "../koneksi.php";
include "../function.php"; // Memuat fungsi CRUD

$is_edit = false;
$schedule_data = [];
$form_errors = [];

// Ambil data Film dan Studio untuk dropdown
$movies = getMovies();
$studios = getStudios();

// Cek apakah ini mode EDIT
if (isset($_GET['id'])) {
    $id_jadwal = $_GET['id'];
    $schedule_data = getSchedules($id_jadwal); // Ambil data jadwal
    
    if ($schedule_data) {
        $is_edit = true;
        // Format waktu tayang untuk input datetime-local: YYYY-MM-DDTHH:MM
        $schedule_data['Waktu_tayang_formatted'] = date('Y-m-d\TH:i', strtotime($schedule_data['Waktu_tayang']));
    } else {
        $_SESSION['message'] = "Gagal: Jadwal dengan ID tersebut tidak ditemukan.";
        header("Location: schedule.php");
        exit;
    }
}

// Ambil data dari SESSION jika ada error saat submit (agar input tidak hilang)
if (isset($_SESSION['form_data'])) {
    $schedule_data = array_merge($schedule_data, $_SESSION['form_data']);
    // Jika ada data form, format ulang waktu tayangnya
    $schedule_data['Waktu_tayang_formatted'] = date('Y-m-d\TH:i', strtotime($schedule_data['Waktu_tayang']));
    unset($_SESSION['form_data']);
}
if (isset($_SESSION['form_errors'])) {
    $form_errors = $_SESSION['form_errors'];
    unset($_SESSION['form_errors']);
}

$page_title = $is_edit ? "Edit Jadwal: " . htmlspecialchars($schedule_data['Judul_Film']) : "Tambah Jadwal Baru";
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $page_title ?> - Onic Cinema Admin</title>
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
        .form-input {
            @apply w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-cinemaRed focus:border-cinemaRed transition duration-200;
        }
        .form-error {
             @apply text-red-500 text-sm mt-1;
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
        <div class="max-w-xl mx-auto px-4 sm:px-6 lg:px-8">

            <!-- Judul Halaman -->
            <div class="mb-8 flex justify-between items-center border-b pb-4">
                <h2 class="text-3xl font-extrabold text-cinemaBlack flex items-center gap-2">
                    <i class="ph ph-calendar-check text-cinemaRed"></i> <?= $page_title ?>
                </h2>
                <a href="schedule.php" class="text-gray-500 hover:text-cinemaRed transition flex items-center gap-1">
                    <i class="ph ph-arrow-left"></i> Kembali ke Daftar Jadwal
                </a>
            </div>

            <!-- Card Form -->
            <div class="bg-white shadow-xl rounded-xl p-6 md:p-8">
                
                <!-- Tampilkan Error General -->
                <?php if (isset($form_errors['general'])): ?>
                    <div class="p-4 mb-4 text-sm text-red-700 bg-red-100 rounded-lg" role="alert">
                        <?= $form_errors['general']; ?>
                    </div>
                <?php endif; ?>

                <form action="action_jadwal.php" method="POST">
                    
                    <!-- Hidden input untuk mode edit -->
                    <?php if ($is_edit): ?>
                        <input type="hidden" name="action" value="update">
                        <input type="hidden" name="id_jadwal" value="<?= $schedule_data['Id_jadwal'] ?>">
                    <?php else: ?>
                        <input type="hidden" name="action" value="create">
                    <?php endif; ?>

                    <!-- Pilih Film -->
                    <div class="mb-4">
                        <label for="Id_movie" class="block text-sm font-semibold text-gray-700 mb-1">Film yang Ditayangkan <span class="text-cinemaRed">*</span></label>
                        <select id="Id_movie" name="Id_movie" class="form-input" required>
                            <option value="">-- Pilih Film --</option>
                            <?php if (!empty($movies)): ?>
                                <?php foreach ($movies as $movie): ?>
                                    <option value="<?= $movie['Id_movie'] ?>" 
                                        <?= ($schedule_data['Id_movie'] ?? '') == $movie['Id_movie'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($movie['judul']) ?> (<?= $movie['duration'] ?> Menit)
                                    </option>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <option value="" disabled>Belum ada Film yang terdaftar</option>
                            <?php endif; ?>
                        </select>
                        <?php if (empty($movies)): ?>
                            <p class="text-sm text-red-500 mt-1">Belum ada Film! Silakan <a href="manage_movie.php" class="font-bold underline">Tambah Film</a> terlebih dahulu.</p>
                        <?php endif; ?>
                    </div>

                    <!-- Pilih Studio -->
                    <div class="mb-4">
                        <label for="Id_studio" class="block text-sm font-semibold text-gray-700 mb-1">Studio <span class="text-cinemaRed">*</span></label>
                        <select id="Id_studio" name="Id_studio" class="form-input" required>
                            <option value="">-- Pilih Studio --</option>
                            <?php if (!empty($studios)): ?>
                                <?php foreach ($studios as $studio): ?>
                                    <option value="<?= $studio['Id_studio'] ?>" 
                                        <?= ($schedule_data['Id_studio'] ?? '') == $studio['Id_studio'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($studio['nama_studio']) ?> (Kapasitas: <?= $studio['capacity'] ?> Kursi)
                                    </option>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <option value="" disabled>Belum ada Studio yang terdaftar</option>
                            <?php endif; ?>
                        </select>
                        <?php if (empty($studios)): ?>
                            <p class="text-sm text-red-500 mt-1">Belum ada Studio! Silakan <a href="manage_studio.php" class="font-bold underline">Tambah Studio</a> terlebih dahulu.</p>
                        <?php endif; ?>
                    </div>

                    <!-- Waktu Tayang dan Harga -->
                    <div class="grid grid-cols-2 gap-4">
                        <div class="mb-4">
                            <label for="Waktu_tayang" class="block text-sm font-semibold text-gray-700 mb-1">Waktu Tayang <span class="text-cinemaRed">*</span></label>
                            <!-- Input datetime-local menerima format YYYY-MM-DDTHH:MM -->
                            <input type="datetime-local" id="Waktu_tayang" name="Waktu_tayang" class="form-input" 
                                value="<?= htmlspecialchars($schedule_data['Waktu_tayang_formatted'] ?? '') ?>" required>
                            <?php if (isset($form_errors['Waktu_tayang'])): ?>
                                <p class="form-error"><?= $form_errors['Waktu_tayang'] ?></p>
                            <?php endif; ?>
                        </div>
                        
                        <div class="mb-4">
                            <label for="harga" class="block text-sm font-semibold text-gray-700 mb-1">Harga Tiket (Rp) <span class="text-cinemaRed">*</span></label>
                            <input type="number" id="harga" name="harga" class="form-input" placeholder="Contoh: 35000" min="1000" step="500"
                                value="<?= htmlspecialchars($schedule_data['harga'] ?? '') ?>" required>
                            <?php if (isset($form_errors['harga'])): ?>
                                <p class="form-error"><?= $form_errors['harga'] ?></p>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Tombol Submit -->
                    <div class="mt-8 pt-6 border-t flex justify-end">
                        <button type="submit" class="bg-cinemaRed hover:bg-red-700 text-white px-8 py-3 rounded-lg font-bold shadow-lg transition transform hover:-translate-y-0.5"
                            <?= empty($movies) || empty($studios) ? 'disabled' : '' ?>>
                            <i class="ph ph-floppy-disk mr-1"></i> <?= $is_edit ? 'Simpan Perubahan' : 'Tambah Jadwal' ?>
                        </button>
                    </div>
                </form>
            </div>

        </div>
    </div>

</body>
</html>