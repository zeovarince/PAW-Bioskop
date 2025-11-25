<?php
session_start();
include "../koneksi.php"; 
// Ambil Semua Data Film
$query = "SELECT * FROM movies ORDER BY release_date ASC";
$result = mysqli_query($conn, $query);

$now_showing = [];
$coming_soon = [];
$hero_movie = null; 
$today = date('Y-m-d');

while ($row = mysqli_fetch_assoc($result)) {
    // Tentukan path gambar
    $poster_path = "../assets/images/" . $row['poster'];
    if (empty($row['poster']) || !file_exists($poster_path)) {
        $row['poster_url'] = "https://placehold.co/300x450?text=No+Poster";
    } else {
        $row['poster_url'] = $poster_path;
    }

    // Pisahkan Now Showing vs Coming Soon
    if ($row['release_date'] > $today) {
        $coming_soon[] = $row;
    } else {
        $now_showing[] = $row;
    }
}

// Ambil 1 film terbaru dari Now Showing untuk jadi Hero Banner
if (!empty($now_showing)) {
    $hero_movie = $now_showing[0]; 
}

// --- LIMIT DATA UNTUK HOME (Maksimal 5 Film) ---
$now_showing_limit = array_slice($now_showing, 0, 5);
$coming_soon_limit = array_slice($coming_soon, 0, 5);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Oonic Cinema - Home</title>
    <link rel="icon" href="../logo.png">
    
    <!-- Tailwind & Icons -->
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
            background: rgba(20, 20, 20, 0.8);
            backdrop-filter: blur(10px);
        }
        .hero-overlay {
            background: linear-gradient(to top, #141414 10%, transparent 100%);
        }
        .hero-overlay-left {
            background: linear-gradient(to right, #141414 20%, transparent 100%);
        }
    </style>
</head>
<body class="bg-cinemaBlack font-sans text-gray-200">

    <!--navbar user -->
    <nav class="bg-cinemaBlack border-b border-gray-800 py-4">
        <div class="max-w-7xl mx-auto px-6 flex justify-between items-center">
            
            <div class="flex items-center gap-4">
                <img src="../logo.png" 
                        alt="Onic Logo" class="h-11 w-auto object-contain drop-shadow-lg">
                
                <h1 class="text-3xl font-bold text-cinemaGold tracking-widest uppercase" style="text-shadow: 0px 0px 7px;">
                    ONIC <span class="text-white">CINEMA</span>
                </h1>
            </div>

            <div class="hidden md:flex space-x-8">
                <a href="index.php" class="bg-cinemaGold text-black px-3 py-2 rounded-md text-sm font-medium transition">Home</a>
                <a href="movies.php" class=" text-gray-300 hover:text-cinemaGold px-3 py-2 rounded-md text-sm font-medium transition">Movies</a>
                <a href="schedule.php" class="text-gray-300 hover:text-cinemaGold px-3 py-2 rounded-md text-sm font-medium transition">Schedule</a>
                <a href="contact.php" class="text-gray-300 hover:text-cinemaGold px-3 py-2 rounded-md text-sm font-medium transition">Contact</a>
            </div>

            <div class="flex items-center gap-4">
                <?php if (isset($_SESSION['login'])): ?>
                    <div class="text-right hidden sm:block">
                        <p class="text-sm font-bold text-white">Halo, <?= $_SESSION['username'] ?></p>
                        <p class="text-xs text-cinemaGold">Member</p>
                    </div>
                    <a href="logout.php" class="bg-gray-800 hover:bg-cinemaRed text-white p-2 rounded-full transition" title="Logout">
                        <i class="ph ph-sign-out text-xl"></i>
                    </a>
                <?php else: ?>
                    <a href="login.php" class="bg-cinemaRed hover:bg-red-700 text-white px-5 py-2 rounded-full font-bold text-sm transition shadow-lg shadow-red-900/20">
                        Masuk / Daftar
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </nav>

    <!--hero banner -->
    <?php if ($hero_movie): ?>
    <header class="relative w-full h-[85vh] overflow-hidden">
        <div class="absolute inset-0 bg-cover bg-center bg-no-repeat transform scale-105 hover:scale-100 transition duration-[10s]"
             style="background-image: url('<?= $hero_movie['poster_url'] ?>');">
        </div>
        
        <div class="absolute inset-0 bg-black/40"></div> 
        <div class="absolute inset-0 hero-overlay"></div>
        <div class="absolute inset-0 hero-overlay-left"></div>

        <div class="relative z-10 h-full max-w-7xl mx-auto px-6 flex flex-col justify-center pt-20">
            <span class="inline-block bg-cinemaRed text-white text-xs font-bold px-3 py-1 rounded w-fit mb-4 uppercase tracking-wider">
                Sedang Tayang
            </span>
            
            <h1 class="text-5xl md:text-7xl font-extrabold text-white mb-4 max-w-3xl leading-tight drop-shadow-2xl">
                <?= $hero_movie['judul'] ?>
            </h1>
            
            <div class="flex items-center gap-4 text-gray-300 text-sm mb-6 font-medium">
                <span class="flex items-center gap-1"><i class="ph ph-clock text-cinemaGold"></i> <?= $hero_movie['duration'] ?> Menit</span>
                <span>•</span>
                <span><?= $hero_movie['genre'] ?? 'Movie' ?></span>
                <span>•</span>
                <span class="border border-gray-500 px-2 rounded text-xs"><?= $hero_movie['age_rating'] ?? 'SU' ?></span>
            </div>

            <p class="text-gray-300 max-w-xl text-lg mb-8 line-clamp-3 leading-relaxed drop-shadow-md">
                <?= $hero_movie['description'] ?>
            </p>

            <div class="flex gap-4">
                <a href="buy_ticket.php?id=<?= $hero_movie['Id_movie'] ?>" 
                   class="bg-cinemaRed hover:bg-red-700 text-white px-8 py-3.5 rounded-lg font-bold text-lg flex items-center gap-2 shadow-lg shadow-red-900/50 transition transform hover:-translate-y-1">
                    <i class="ph ph-ticket text-xl"></i> Beli Tiket
                </a>
                <!-- Tombol Detail Film SUDAH DIHAPUS DI SINI -->
            </div>
        </div>
    </header>
    <?php endif; ?>

    <!-- nampilin film -->
    <main class="max-w-7xl mx-auto px-6 py-16">

        <!-- SECTION: NOW SHOWING -->
        <div class="flex justify-between items-end mb-8">
            <div>
                <h2 class="text-3xl font-bold text-white flex items-center gap-2">
                    <i class="ph ph-fire text-cinemaRed"></i> Sedang Hangat
                </h2>
                <p class="text-gray-500 mt-1">Film pilihan yang sedang tayang di bioskop kami.</p>
            </div>
            <a href="movies.php" class="text-cinemaGold hover:text-yellow-300 font-bold text-sm flex items-center gap-1 transition">
                Lihat Semua <i class="ph ph-arrow-right"></i>
            </a>
        </div>

        <!-- Grid Film -->
        <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-5 gap-6 mb-20">
            <?php foreach ($now_showing_limit as $movie): ?>
                <div class="group relative bg-cinemaDark rounded-xl overflow-hidden shadow-lg hover:shadow-2xl hover:shadow-red-900/20 transition duration-300">
                    <div class="aspect-[2/3] overflow-hidden relative">
                        <img src="<?= $movie['poster_url'] ?>" alt="<?= $movie['judul'] ?>" 
                             class="w-full h-full object-cover group-hover:scale-110 transition duration-500">
                        <div class="absolute inset-0 bg-black/70 opacity-0 group-hover:opacity-100 transition duration-300 flex flex-col justify-center items-center gap-3">
                            <a href="buy_ticket.php?id=<?= $movie['Id_movie'] ?>" class="bg-cinemaRed text-white px-6 py-2 rounded-full font-bold text-sm transform translate-y-4 group-hover:translate-y-0 transition duration-300">
                                Beli Tiket
                            </a>
                        </div>
                        <div class="absolute top-2 left-2 bg-black/60 backdrop-blur-md text-yellow-400 text-xs font-bold px-2 py-1 rounded flex items-center gap-1">
                            <i class="ph ph-star-fill"></i> <?= $movie['rating'] ?? '0.0' ?>
                        </div>
                    </div>

                    <!-- Info Film -->
                    <div class="p-4">
                        <h3 class="text-white font-bold truncate mb-1 group-hover:text-cinemaRed transition" title="<?= $movie['judul'] ?>">
                            <?= $movie['judul'] ?>
                        </h3>
                        <div class="flex justify-between items-center text-xs text-gray-500">
                            <span><?= $movie['duration'] ?> Menit</span>
                            <span class="border border-gray-600 px-1 rounded"><?= $movie['age_rating'] ?? 'SU' ?></span>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>


        <!-- SECTION: COMING SOON -->
        <?php if (count($coming_soon_limit) > 0): ?>
        <div class="mb-10">
            <div class="flex justify-between items-center mb-8">
                <div class="flex items-center gap-3">
                    <i class="ph ph-hourglass-medium text-3xl text-cinemaGold"></i>
                    <h2 class="text-3xl font-bold text-white">Segera Tayang</h2>
                </div>
                <!-- Link ke movies.php -->
                <a href="movies.php" class="text-gray-500 hover:text-white font-bold text-xs flex items-center gap-1 transition">
                    Lihat Semua <i class="ph ph-arrow-right"></i>
                </a>
            </div>

            <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-5 gap-6">
                <?php foreach ($coming_soon_limit as $movie): ?>
                    <div class="bg-cinemaDark/50 rounded-xl overflow-hidden border border-gray-800 opacity-80 hover:opacity-100 transition">
                        <div class="aspect-[2/3] relative">
                            <img src="<?= $movie['poster_url'] ?>" class="w-full h-full object-cover grayscale hover:grayscale-0 transition duration-500">
                            <div class="absolute bottom-0 left-0 right-0 bg-gradient-to-t from-black p-3">
                                <p class="text-cinemaRed text-xs font-bold uppercase tracking-wider">Coming Soon</p>
                                <p class="text-white text-xs">Rilis: <?= date('d M Y', strtotime($movie['release_date'])) ?></p>
                            </div>
                        </div>
                        <div class="p-3">
                            <h4 class="text-gray-300 font-bold text-sm truncate"><?= $movie['judul'] ?></h4>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>

    </main>

    <footer class="bg-black border-t border-gray-900 pt-16 pb-8">
        <div class="max-w-7xl mx-auto px-6">
            <div class="flex flex-col md:flex-row justify-between items-center mb-10">
                <div class="flex items-center gap-3 mb-6 md:mb-0">
                    <img src="../logo.png" alt="Logo" class="h-12 opacity-80 grayscale hover:grayscale-0 transition" onerror="this.style.display='none'">
                    <div class="text-gray-400 text-sm">
                        <p class="font-bold text-white">SONIC CINEMA</p>
                        <p>Hiburan Tanpa Batas.</p>
                    </div>
                </div>
            </div>
            <div class="border-t border-gray-900 pt-8 text-center text-gray-600 text-sm">
                &copy; <?= date('Y') ?> Sonic Cinema. Developed for Web Programming Final Project.
            </div>
        </div>
    </footer>

</body>
</html>