<?php
session_start();
if (!isset($_SESSION['login']) || $_SESSION['role'] != '1') {
    header("Location: ../login.php");
    exit;
}
include "../koneksi.php";

$film = mysqli_query($conn, "SELECT COUNT(*) as total FROM movies");
$total_film = mysqli_fetch_assoc($film)['total'];

$pending = mysqli_query($conn, "SELECT COUNT(*) as total FROM booking WHERE status_booking = '2'");
$total_pending = mysqli_fetch_assoc($pending)['total'];

$today = date('Y-m-d');
$pendapatan = mysqli_query($conn, "SELECT SUM(total_harga) as total FROM booking WHERE status_booking = '1' AND DATE(tanggal_booking) = '$today'");
$data_pendapatan = mysqli_fetch_assoc($pendapatan);
$harian = $data_pendapatan['total'] ?? 0;

$tampil = mysqli_query($conn, "SELECT * FROM movies ORDER BY Id_movie DESC LIMIT 4");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Onic Cinema</title>
    <link rel="icon" href="logo.png">
    
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
    </style>
</head>
<body class="bg-gray-100 font-sans text-gray-800">

    <nav class="glass-nav fixed w-full z-50 border-b border-gray-800">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-20">
                
                <div class="flex items-center gap-4">
                    <img src="../logo.png" 
                         alt="Onic Logo" class="h-11 w-auto object-contain drop-shadow-lg">
                    
                    <h1 class="text-3xl font-bold text-cinemaGold tracking-widest uppercase" style="text-shadow: 0px 0px 7px;">
                        ONIC <span class="text-white">ADMINISTRATOR</span>
                    </h1>
                </div>

                <div class="hidden md:block">
                    <div class="ml-10 flex items-baseline space-x-4">
                        <a href="index.php" class="bg-cinemaGold text-black px-3 py-2 text-l rounded-full text-sm font-medium transition">Dashboard</a>
                        <a href="movies.php" class="text-gray-300 hover:text-cinemaRed px-3 py-2 rounded-md text-sm font-medium transition">Movies</a>
                        <a href="schedule.php" class="text-gray-300 hover:text-cinemaRed px-3 py-2 rounded-md text-sm font-medium transition">Schedules</a>
                        <a href="validation.php" class="text-gray-300 hover:text-cinemaRed px-3 py-2 rounded-md text-sm font-medium transition">
                            Validation
                            <?php if($total_pending > 0): ?>
                                <span class="ml-1 bg-red-600 text-white text-xs px-1.5 rounded-full"><?= $total_pending ?></span>
                            <?php endif; ?>
                        </a>
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

    <div class="pt-20 pb-12">
        
        <div class="bg-cinemaBlack text-white relative overflow-hidden">
            <div class="absolute inset-0 pattern-grid pointer-events-none"></div>
            <div class="absolute top-0 right-0 -mr-20 -mt-20 w-96 h-96 bg-cinemaRed rounded-full mix-blend-screen filter blur-3xl opacity-20"></div>
            <div class="absolute bottom-0 left-0 -ml-20 -mb-20 w-80 h-80 bg-cinemaGold rounded-full mix-blend-screen filter blur-3xl opacity-10"></div>
            <div class="max-w-7xl mx-auto px-4 py-16 sm:px-6 lg:px-8 relative z-10">
                <div class="flex flex-col md:flex-row justify-between items-center">
                    <div class="mb-7 md:mb-10">
                        <p class="text-cinemaGold font-semibold uppercase tracking-wider mb-2">KUASA ADMIN</p>
                        <h2 class="text-4xl md:text-5xl font-bold mb-4">Hello Admin !</h2>
                        <p class="text-gray-400 max-w-xl text-lg">
                            Cek laporan pembelian dan kelola jadwal tayang film
                        </p>
                        <div class="mt-6 flex gap-3">
                            <a href="validation.php" class="bg-cinemaRed hover:bg-red-700 text-white px-6 py-3 rounded-lg font-bold shadow-lg transition transform hover:-translate-y-1">
                                Cek Pesanan
                            </a>
                            <a href="manage_movies.php" class="bg-gray-800 hover:bg-gray-700 text-white px-6 py-3 rounded-lg font-bold border border-gray-700 transition">
                                Tambah Film
                            </a>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 gap-4 w-full md:w-auto">
                        <div class="bg-white/10 backdrop-blur-md border border-white/20 p-4 rounded-xl flex items-center min-w-[250px]">
                            <div class="p-3 bg-green-500/20 text-green-400 rounded-lg mr-4">
                                <i class="ph ph-currency-dollar text-2xl"></i>
                            </div>
                            <div>
                                <p class="text-gray-300 text-xs uppercase">Pendapatan Hari Ini</p>
                                <h3 class="text-xl font-bold text-white">Rp <?= number_format($harian, 0, ',', '.') ?></h3>
                            </div>
                        </div>
                        <div class="bg-white/10 backdrop-blur-md border border-white/20 p-4 rounded-xl flex items-center min-w-[250px]">
                            <div class="p-3 bg-orange-500/20 text-orange-400 rounded-lg mr-4">
                                <i class="ph ph-hourglass text-2xl"></i>
                            </div>
                            <div>
                                <p class="text-gray-300 text-xs uppercase">Menunggu Konfirmasi</p>
                                <h3 class="text-xl font-bold text-white"><?= $total_pending ?> Pesanan</h3>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            </div>
            </div>
        </div>

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-12">
            
            <div class="flex justify-between items-center mb-6 border-b border-gray-300 pb-4">
                <div>
                    <h3 class="text-2xl font-bold text-gray-900 flex items-center gap-2">
                        <i class="ph ph-film-strip text-cinemaRed"></i> Sedang Tayang
                    </h3>
                    <p class="text-gray-500 text-sm mt-1">Daftar film terbaru yang sedang aktif.</p>
                </div>
                <a href="movies.php" class="text-cinemaRed font-bold hover:text-red-800 flex items-center gap-1 transition">
                    Lihat Semua Film <i class="ph ph-arrow-right"></i>
                </a>
            </div>

            <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
                
                <?php if(mysqli_num_rows($tampil) > 0): ?>
                    <?php while($movie = mysqli_fetch_assoc($tampil)): ?>
                        <div class="bg-white rounded-xl shadow-sm hover:shadow-xl transition duration-300 overflow-hidden border border-gray-100 group">
                            <div class="relative">
                                <img src="../assets/images/<?= $movie['poster'] ?>" 
                                     alt="<?= $movie['judul'] ?>" 
                                     class="w-full h-64 object-cover group-hover:scale-105 transition duration-500">
                                <div class="absolute top-2 right-2 bg-cinemaBlack text-cinemaGold text-xs font-bold px-2 py-1 rounded shadow-md">
                                    <?= $movie['duration'] ?> Menit
                                </div>
                            </div>
                            
                            <div class="p-4">
                                <h4 class="font-bold text-gray-800 text-lg truncate" title="<?= $movie['judul'] ?>">
                                    <?= $movie['judul'] ?>
                                </h4>
                                <div class="flex justify-between items-center mt-2 text-sm text-gray-500">
                                    <span><i class="ph ph-calendar-blank"></i> <?= date('d M Y', strtotime($movie['release_date'])) ?></span>
                                </div>
                                
                                <a href="edit_movie.php?id=<?= $movie['Id_movie'] ?>" class="mt-4 block w-full text-center bg-gray-100 hover:bg-cinemaRed hover:text-white text-gray-600 py-2 rounded-lg text-sm font-semibold transition">
                                    Edit Info
                                </a>
                            </div>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <div class="col-span-4 text-center py-10 bg-white rounded-xl border border-dashed border-gray-300">
                        <i class="ph ph-film-slash text-4xl text-gray-300 mb-3"></i>
                        <p class="text-gray-500">Belum ada data film.</p>
                        <a href="manage_movies.php" class="text-cinemaRed font-bold text-sm mt-2 inline-block">Tambah Film Sekarang</a>
                    </div>
                <?php endif; ?>

            </div>
        </div>

    </div>

</body>
</html>