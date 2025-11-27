<?php
session_start();
if (!isset($_SESSION['login']) || $_SESSION['role'] != '1') {
    header("Location: ../login.php");
    exit;
}
include "../koneksi.php";
include "../function.php"; 

$is_edit = false;
$schedule_data = [];
$form_errors = [];

$movies = getMovies();
$studios = getStudios();

// Jam Tayang Standar (Sesuai buy_ticket.php)
$allowed_times = ['12:30', '14:45', '17:10', '19:20', '21:00'];

if (isset($_GET['id'])) {
    $id_jadwal = $_GET['id'];
    $schedule_data = getSchedules($id_jadwal); 
    
    if ($schedule_data) {
        $is_edit = true;
        // Pisahkan DateTime menjadi Date dan Time
        $db_time = strtotime($schedule_data['Waktu_tayang']);
        $schedule_data['tanggal'] = date('Y-m-d', $db_time);
        $schedule_data['jam'] = date('H:i', $db_time);
    } else {
        $_SESSION['message'] = "Gagal: Jadwal tidak ditemukan.";
        header("Location: schedule.php");
        exit;
    }
}

if (isset($_SESSION['form_data'])) {
    $schedule_data = array_merge($schedule_data, $_SESSION['form_data']);
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
                        cinemaRed: '#E50914',
                        cinemaGold: '#FFD700',
                    }
                }
            }
        }
    </script>
    <style>
        .glass-nav { background: rgba(20, 20, 20, 0.9); backdrop-filter: blur(10px); }
        .form-input { @apply w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-cinemaRed focus:border-cinemaRed transition duration-200; }
        .form-error { @apply text-red-500 text-sm mt-1; }
    </style>
</head>
<body class="bg-gray-50 font-sans text-gray-800">

    <nav class="glass-nav fixed w-full z-50 bg-cinemaBlack">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-20">
                <div class="flex items-center gap-4">
                    <img src="../logo.png" alt="Onic Logo" class="h-11 w-auto object-contain drop-shadow-lg">
                    <h1 class="text-3xl font-bold text-cinemaGold tracking-widest uppercase">ONIC <span class="text-white">ADMINISTRATOR</span></h1>
                </div>
                <div class="hidden md:block">
                    <div class="ml-10 flex items-baseline space-x-4">
                        <a href="index.php" class="text-gray-300 hover:text-cinemaRed px-3 py-2 rounded-md text-sm font-medium transition">Dashboard</a>
                        <a href="movies.php" class="text-gray-300 hover:text-cinemaRed px-3 py-2 rounded-md text-sm font-medium transition">Movies</a>
                        <a href="studio_admin.php" class="text-gray-300 hover:text-cinemaRed px-3 py-2 rounded-md text-sm font-medium transition">Studio</a>
                        <a href="schedule.php" class="bg-cinemaGold text-black px-3 py-2 rounded-full text-sm font-medium transition">Schedules</a>
                        <a href="validation.php" class="text-gray-300 hover:text-cinemaRed px-3 py-2 rounded-md text-sm font-medium transition">Validation</a>
                        <a href="report.php" class="text-gray-300 hover:text-cinemaRed px-3 py-2 rounded-md text-sm font-medium transition">Report</a>
                    </div>
                </div>
                <div class="flex items-center gap-4">
                    <div class="hidden md:flex flex-col items-end">
                        <span class="text-white text-sm font-bold"><?= isset($_SESSION['username']) ? $_SESSION['username'] : 'Admin' ?></span>
                    </div>
                    <a href="../logout.php" class="text-gray-400 hover:text-white transition"><i class="ph ph-sign-out text-2xl"></i></a>
                </div>
            </div>
        </div>
    </nav>

    <div class="pt-24 pb-12 min-h-screen">
        <div class="max-w-xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="mb-8 flex justify-between items-center border-b pb-4">
                <h2 class="text-3xl font-extrabold text-cinemaBlack flex items-center gap-2">
                    <i class="ph ph-calendar-check text-cinemaRed"></i> <?= $page_title ?>
                </h2>
                <a href="schedule.php" class="text-gray-500 hover:text-cinemaRed transition flex items-center gap-1"><i class="ph ph-arrow-left"></i> Kembali</a>
            </div>

            <div class="bg-white shadow-xl rounded-xl p-6 md:p-8">
                <?php if (isset($form_errors['general'])): ?>
                    <div class="p-4 mb-4 text-sm text-red-700 bg-red-100 rounded-lg"><?= $form_errors['general']; ?></div>
                <?php endif; ?>
                <?php if (isset($form_errors['Waktu_tayang'])): ?>
                    <div class="p-4 mb-4 text-sm text-red-700 bg-red-100 rounded-lg"><?= $form_errors['Waktu_tayang']; ?></div>
                <?php endif; ?>

                <form action="action_jadwal.php" method="POST">
                    <?php if ($is_edit): ?>
                        <input type="hidden" name="action" value="update">
                        <input type="hidden" name="id_jadwal" value="<?= $schedule_data['Id_jadwal'] ?>">
                    <?php else: ?>
                        <input type="hidden" name="action" value="create">
                    <?php endif; ?>

                    <div class="mb-4">
                        <label class="block text-sm font-semibold text-gray-700 mb-1">Film <span class="text-cinemaRed">*</span></label>
                        <select name="Id_movie" class="form-input" required>
                            <option value="">-- Pilih Film --</option>
                            <?php foreach ($movies as $movie): ?>
                                <option value="<?= $movie['Id_movie'] ?>" <?= ($schedule_data['Id_movie'] ?? '') == $movie['Id_movie'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($movie['judul']) ?> (<?= $movie['duration'] ?> Min)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="mb-4">
                        <label class="block text-sm font-semibold text-gray-700 mb-1">Studio <span class="text-cinemaRed">*</span></label>
                        <select name="Id_studio" class="form-input" required>
                            <option value="">-- Pilih Studio --</option>
                            <?php foreach ($studios as $studio): ?>
                                <option value="<?= $studio['Id_studio'] ?>" <?= ($schedule_data['Id_studio'] ?? '') == $studio['Id_studio'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($studio['nama_studio']) ?> (Cap: <?= $studio['capacity'] ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- INPUT TANGGAL & JAM (DIPISAH) -->
                    <div class="grid grid-cols-2 gap-4">
                        <div class="mb-4">
                            <label class="block text-sm font-semibold text-gray-700 mb-1">Tanggal <span class="text-cinemaRed">*</span></label>
                            <input type="date" name="tanggal" class="form-input" 
                                value="<?= htmlspecialchars($schedule_data['tanggal'] ?? date('Y-m-d')) ?>" required>
                        </div>
                        
                        <div class="mb-4">
                            <label class="block text-sm font-semibold text-gray-700 mb-1">Jam Tayang <span class="text-cinemaRed">*</span></label>
                            <select name="jam" class="form-input" required>
                                <option value="">-- Pilih Jam --</option>
                                <?php foreach ($allowed_times as $time): ?>
                                    <option value="<?= $time ?>" <?= ($schedule_data['jam'] ?? '') == $time ? 'selected' : '' ?>>
                                        <?= $time ?> WIB
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="block text-sm font-semibold text-gray-700 mb-1">Harga Tiket (Rp) <span class="text-cinemaRed">*</span></label>
                        <input type="number" name="harga" class="form-input" placeholder="Contoh: 35000" min="1000" step="500"
                            value="<?= htmlspecialchars($schedule_data['harga'] ?? '') ?>" required>
                        <?php if (isset($form_errors['harga'])): ?>
                            <p class="form-error"><?= $form_errors['harga'] ?></p>
                        <?php endif; ?>
                    </div>

                    <div class="mt-8 pt-6 border-t flex justify-end">
                        <button type="submit" class="bg-cinemaRed hover:bg-red-700 text-white px-8 py-3 rounded-lg font-bold shadow-lg transition transform hover:-translate-y-0.5">
                            <i class="ph ph-floppy-disk mr-1"></i> <?= $is_edit ? 'Simpan Perubahan' : 'Tambah Jadwal' ?>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</body>
</html>