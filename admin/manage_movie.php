<?php
session_start();
if (!isset($_SESSION['login']) || $_SESSION['role'] != '1') {
    header("Location: ../login.php");
    exit;
}
include "../koneksi.php";
include "../function.php"; // Memuat fungsi CRUD

$is_edit = false;
$movie_data = [];
$form_errors = [];

// Cek apakah ini mode EDIT
if (isset($_GET['id'])) {
    $id_movie = $_GET['id'];
    $movie_data = getMovies($id_movie); // Ambil data film yang akan di edit
    
    if ($movie_data) {
        $is_edit = true;
    } else {
        // Jika ID tidak valid
        $_SESSION['message'] = "Gagal: Film dengan ID tersebut tidak ditemukan.";
        header("Location: movies.php");
        exit;
    }
}

// Ambil data dari SESSION jika ada error saat submit (agar input tidak hilang)
if (isset($_SESSION['form_data'])) {
    $movie_data = array_merge($movie_data, $_SESSION['form_data']);
    unset($_SESSION['form_data']);
}
if (isset($_SESSION['form_errors'])) {
    $form_errors = $_SESSION['form_errors'];
    unset($_SESSION['form_errors']);
}

$page_title = $is_edit ? "Edit Film: " . htmlspecialchars($movie_data['judul']) : "Tambah Film Baru";
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $page_title ?> - Onic Cinema Admin</title>
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
    <script src="https://unpkg.com/@phosphor-icons/web"></script>
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
                        <a href="movies.php" class="bg-cinemaGold text-black px-3 py-2 text-l rounded-full text-sm font-medium transition">Movies</a>
                        <a href="studio_admin.php" class="text-gray-300 hover:text-cinemaRed px-3 py-2 rounded-md text-sm font-medium transition">Studio</a>
                        <a href="schedule.php" class="text-gray-300 hover:text-cinemaRed px-3 py-2 rounded-md text-sm font-medium transition">Schedules</a>
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
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">

            <!-- Judul Halaman -->
            <div class="mb-8 flex justify-between items-center border-b pb-4">
                <h2 class="text-3xl font-extrabold text-cinemaBlack flex items-center gap-2">
                    <i class="ph ph-film-slate text-cinemaRed"></i> <?= $page_title ?>
                </h2>
                <a href="movies.php" class="text-gray-500 hover:text-cinemaRed transition flex items-center gap-1">
                    <i class="ph ph-arrow-left"></i> Kembali ke Daftar
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

                <form action="action_movie.php" method="POST" enctype="multipart/form-data">
                    
                    <!-- Hidden input untuk mode edit -->
                    <?php if ($is_edit): ?>
                        <input type="hidden" name="action" value="update">
                        <input type="hidden" name="id_movie" value="<?= $movie_data['Id_movie'] ?>">
                        <input type="hidden" name="old_poster" value="<?= $movie_data['poster'] ?>">
                    <?php else: ?>
                        <input type="hidden" name="action" value="create">
                    <?php endif; ?>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">

                        <!-- Kolom Poster (Kiri) -->
                        <div class="md:col-span-1">
                            <label for="poster" class="block text-sm font-semibold text-gray-700 mb-2">Poster Film</label>
                            
                            <!-- Tampilkan Poster Saat Edit atau Placeholder saat Create -->
                            <div class="mb-4">
                                <?php 
                                $poster_src = $is_edit && !empty($movie_data['poster']) 
                                    ? '../assets/images/' . htmlspecialchars($movie_data['poster'])
                                    : 'https://placehold.co/200x300/e50914/ffffff?text=NO+IMAGE';
                                ?>
                                <img id="posterPreview" src="<?= $poster_src ?>" 
                                     alt="Poster Preview" 
                                     class="w-full max-w-[200px] h-auto object-cover rounded-lg shadow-lg border border-gray-200 mx-auto">
                            </div>
                            
                            <input type="file" id="poster" name="poster" class="block w-full text-sm text-gray-500 
                                file:mr-4 file:py-2 file:px-4 
                                file:rounded-full file:border-0 
                                file:text-sm file:font-semibold
                                file:bg-cinemaRed file:text-white
                                hover:file:bg-red-700 cursor-pointer"
                                accept="image/jpeg, image/png, image/jpg"
                            >
                            <?php if (isset($form_errors['poster'])): ?>
                                <p class="form-error"><?= $form_errors['poster'] ?></p>
                            <?php endif; ?>
                            <p class="text-xs text-gray-400 mt-2">Max. 2MB. Kosongkan jika tidak ingin mengubah saat edit.</p>
                        </div>

                        <!-- Kolom Data Film (Kanan) -->
                        <div class="md:col-span-2 space-y-4">
                            
                            <!-- Judul Film -->
                            <div>
                                <label for="judul" class="block text-sm font-semibold text-gray-700 mb-1">Judul Film <span class="text-cinemaRed">*</span></label>
                                <input type="text" id="judul" name="judul" class="form-input" placeholder="Masukkan Judul Film" 
                                    value="<?= htmlspecialchars($movie_data['judul'] ?? '') ?>" required>
                                <?php if (isset($form_errors['judul'])): ?>
                                    <p class="form-error"><?= $form_errors['judul'] ?></p>
                                <?php endif; ?>
                            </div>

                            <!-- Deskripsi -->
                            <div>
                                <label for="description" class="block text-sm font-semibold text-gray-700 mb-1">Deskripsi/Sinopsis <span class="text-cinemaRed">*</span></label>
                                <textarea id="description" name="description" rows="4" class="form-input" placeholder="Tulis sinopsis singkat film" required><?= htmlspecialchars($movie_data['description'] ?? '') ?></textarea>
                                <?php if (isset($form_errors['description'])): ?>
                                    <p class="form-error"><?= $form_errors['description'] ?></p>
                                <?php endif; ?>
                            </div>

                            <!-- Durasi dan Tanggal Rilis -->
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label for="duration" class="block text-sm font-semibold text-gray-700 mb-1">Durasi (Menit) <span class="text-cinemaRed">*</span></label>
                                    <input type="number" id="duration" name="duration" class="form-input" placeholder="Contoh: 120" min="1" 
                                        value="<?= htmlspecialchars($movie_data['duration'] ?? '') ?>" required>
                                    <?php if (isset($form_errors['duration'])): ?>
                                        <p class="form-error"><?= $form_errors['duration'] ?></p>
                                    <?php endif; ?>
                                </div>
                                
                                <div>
                                    <label for="release_date" class="block text-sm font-semibold text-gray-700 mb-1">Tanggal Rilis <span class="text-cinemaRed">*</span></label>
                                    <input type="date" id="release_date" name="release_date" class="form-input" 
                                        value="<?= htmlspecialchars($movie_data['release_date'] ?? date('Y-m-d')) ?>" required>
                                    <?php if (isset($form_errors['release_date'])): ?>
                                        <p class="form-error"><?= $form_errors['release_date'] ?></p>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Tombol Submit -->
                    <div class="mt-8 pt-6 border-t flex justify-end">
                        <button type="submit" class="bg-cinemaRed hover:bg-red-700 text-white px-8 py-3 rounded-lg font-bold shadow-lg transition transform hover:-translate-y-0.5">
                            <i class="ph ph-floppy-disk mr-1"></i> <?= $is_edit ? 'Simpan Perubahan' : 'Tambah Film' ?>
                        </button>
                    </div>
                </form>
            </div>

        </div>
    </div>

    <script>
        // Fungsi untuk menampilkan preview gambar
        document.getElementById('poster').addEventListener('change', function(event) {
            const [file] = event.target.files;
            if (file) {
                document.getElementById('posterPreview').src = URL.createObjectURL(file);
            }
        });
    </script>
</body>
</html>