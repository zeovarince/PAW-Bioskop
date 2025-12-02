<?php
session_start();
if (!isset($_SESSION['login']) || $_SESSION['role'] != '1') {
    header("Location: ../login.php");
    exit;
}
include "../koneksi.php";
include "../function.php"; // Memuat fungsi esc() dan getMovies()

// Query untuk mengambil semua review dengan JOIN ke users dan movies
$query_reviews = "
    SELECT 
        r.rating, 
        r.komentar, 
        r.created_at,
        u.username,
        m.judul AS Judul_Film,
        m.Id_movie
    FROM reviews r
    JOIN users u ON r.Id_user = u.Id_user
    JOIN movies m ON r.Id_movie = m.Id_movie
    ORDER BY r.created_at DESC
";
$result_reviews = mysqli_query($conn, $query_reviews);
$review_count = mysqli_num_rows($result_reviews);
$movies_list = getMovies(); // Untuk filter dropdown
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manajemen Review - Onic Cinema Admin</title>
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
        .star-fill {
            color: #FFD700;
        }
    </style>
</head>
<body class="bg-gray-50 font-sans text-gray-800">

    <!-- Navigasi Admin -->
    <nav class="glass-nav fixed w-full z-50 bg-cinemaBlack border-b border-gray-800">
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
                        <a href="manage_studio.php" class="text-gray-300 hover:text-cinemaRed px-3 py-2 rounded-md text-sm font-medium transition">Studio</a>
                        <a href="schedule.php" class="text-gray-300 hover:text-cinemaRed px-3 py-2 rounded-md text-sm font-medium transition">Schedules</a>
                        <a href="validation.php" class="text-gray-300 hover:text-cinemaRed px-3 py-2 rounded-md text-sm font-medium transition">Validation</a>
                        <a href="report.php" class="text-gray-300 hover:text-cinemaRed px-3 py-2 rounded-md text-sm font-medium transition">Report</a>
                        <!-- Menu Review Aktif -->
                        <a href="reviews.php" class="bg-cinemaGold text-black px-3 py-2 rounded-full text-sm font-bold transition shadow-lg">Reviews</a>
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

    <!-- CONTENT -->
    <div class="pt-24 pb-12 min-h-screen">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

            <!-- Judul Halaman -->
            <div class="mb-8 flex justify-between items-center border-b border-gray-200 pb-4">
                <div>
                    <h2 class="text-3xl font-extrabold text-gray-900 flex items-center gap-2">
                        <i class="ph ph-star-half text-cinemaGold"></i> Review Pengguna
                    </h2>
                    <p class="text-gray-500 mt-1">Total: <?= $review_count ?> Ulasan dari Pengguna.</p>
                </div>
                <!-- Dropdown filter jika diperlukan (Dibiarkan sederhana dulu) -->
            </div>
            
            <!-- Tabel Daftar Reviews -->
            <div class="bg-white shadow-xl rounded-xl overflow-hidden border border-gray-100">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-wider w-1/4">Film</th>
                                <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-wider w-1/6">Rating</th>
                                <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-wider w-1/4">Komentar</th>
                                <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-wider w-1/6">Dari</th>
                                <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-wider w-1/6">Tanggal</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <?php if ($review_count > 0): ?>
                                <?php while ($review = mysqli_fetch_assoc($result_reviews)): ?>
                                    <tr>
                                        <td class="px-6 py-4 text-sm font-bold text-gray-900">
                                            <?= htmlspecialchars($review['Judul_Film']) ?>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm">
                                            <span class="flex items-center">
                                                <?php for ($i = 1; $i <= 5; $i++): ?>
                                                    <i class="ph ph-star-fill text-lg <?= $review['rating'] >= $i ? 'star-fill' : 'text-gray-300' ?>"></i>
                                                <?php endfor; ?>
                                                <span class="ml-2 font-bold text-gray-900"><?= $review['rating'] ?></span>
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 text-sm italic text-gray-600 max-w-xs truncate" title="<?= htmlspecialchars($review['komentar']) ?>">
                                            "<?= htmlspecialchars($review['komentar']) ?: '— Tidak ada komentar —' ?>"
                                        </td>
                                        <td class="px-6 py-4 text-sm font-medium text-gray-700">
                                            <?= htmlspecialchars($review['username']) ?>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-xs text-gray-500">
                                            <?= date('d M Y H:i', strtotime($review['created_at'])) ?>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="5" class="px-6 py-10 text-center text-gray-500">
                                        <i class="ph ph-speech-bubble-slashes text-5xl text-gray-300 mb-3 block"></i>
                                        Belum ada ulasan yang diberikan oleh pengguna.
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </div>
</body>
</html>