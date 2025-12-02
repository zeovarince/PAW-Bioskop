<?php
session_start();
include '../koneksi.php';
include '../function.php'; // Memuat fungsi getAverageRating()

// Cek status login
$is_logged_in = isset($_SESSION['login']);

function safe($row, $keys, $default = '')
{
    if (!is_array($keys)) $keys = [$keys];
    foreach ($keys as $key) {
        if (isset($row[$key]) && $row[$key] !== null) return $row[$key];
    }
    return $default;
}

function poster_src($filename)
{
    if (empty($filename)) return 'https://via.placeholder.com/300x450?text=No+Poster';
    $p1 = '../assets/images/' . $filename;
    if (file_exists($p1)) return $p1;
    return 'https://via.placeholder.com/300x450?text=No+Poster';
}

// 1. Ambil semua data film
$sql = "SELECT * FROM movies ORDER BY release_date DESC";
$res = mysqli_query($conn, $sql);
if ($res === false) die("Query Error: " . mysqli_error($conn));

$today = date('Y-m-d');
$now_showing = [];
$coming_soon = [];

while ($row = mysqli_fetch_assoc($res)) {
    // Ambil data rating (asumsi getAverageRating tersedia di function.php)
    $rating_data = getAverageRating($row['Id_movie']);
    $row['avg_rating'] = $rating_data['average'];
    $row['review_count'] = $rating_data['count'];

    $release_date = safe($row, ['release_date', 'Release_date', 'rilis'], '');
    if ($release_date !== '' && $release_date > $today) {
        $coming_soon[] = $row;
    } else {
        $now_showing[] = $row;
    }
}
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Onic Cinema - Movies</title>
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
</head>

<body class="bg-gray-100 text-gray-800">

    <!--navbar user -->
    <nav class="bg-cinemaBlack border-b border-gray-800 py-4 sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-6 flex justify-between items-center">
            
            <!-- Logo -->
            <div class="flex items-center gap-4">
                <img src="../logo.png" alt="Onic Logo" class="h-11 w-auto object-contain drop-shadow-lg" onerror="this.style.display='none'">
                
                <h1 class="text-3xl font-bold text-cinemaGold tracking-widest uppercase" style="text-shadow: 0px 0px 7px;">
                    ONIC <span class="text-white">CINEMA</span>
                </h1>
            </div>

            <!-- Menu Tengah (LENGKAP) -->
            <div class="hidden md:flex space-x-8">
                <a href="index.php" class="text-gray-300 hover:text-cinemaGold px-3 py-2 rounded-md text-sm font-medium transition">Home</a>
                <a href="movies.php" class="bg-cinemaGold text-black px-3 py-2 rounded-md text-sm font-medium transition shadow-lg shadow-yellow-500/20">Movies</a>
                <?php if ($is_logged_in): ?>
                    <a href="dashboard.php" class="text-gray-300 hover:text-cinemaGold px-3 py-2 rounded-md text-sm font-medium transition">Dashboard</a>
                <?php endif; ?>                
                <a href="contact.php" class="text-gray-300 hover:text-cinemaGold px-3 py-2 rounded-md text-sm font-medium transition">Contact</a>
            </div>

            <!-- Menu Kanan (User Profile / Login) -->
            <div class="flex items-center gap-4">
                <?php if ($is_logged_in): ?>
                    <div class="text-right hidden sm:block">
                        <p class="text-sm font-bold text-white">Halo, <?= $_SESSION['username'] ?></p>
                        <p class="text-xs text-cinemaGold">Member</p>
                    </div>
                    <a href="../logout.php" class="bg-gray-800 hover:bg-cinemaRed text-white p-2 rounded-full transition" title="Logout">
                        <i class="ph ph-sign-out text-xl"></i>
                    </a>
                <?php else: ?>
                    <a href="../login.php" class="bg-cinemaRed hover:bg-red-700 text-white px-5 py-2 rounded-full font-bold text-sm transition shadow-lg shadow-red-900/20">
                        Masuk / Daftar
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </nav>

    <div class="max-w-7xl mx-auto px-6 py-10">

        <h2 class="text-3xl font-bold mb-2 flex items-center gap-2">
            <i class="ph ph-film-strip text-cinemaRed"></i> Sedang Tayang
        </h2>
        <p class="text-gray-500 mb-8">Pilih film favorit Anda untuk melihat jadwal dan membeli tiket.</p>

        <!-- NOW SHOWING -->
        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
            <?php if (count($now_showing) > 0): ?>
                <?php foreach ($now_showing as $movie): ?>
                    <?php
                    $id = safe($movie, ['id_movie', 'Id_movie', 'id', 'movie_id'], '');
                    $judul = htmlspecialchars(safe($movie, 'judul', '-'));
                    $poster = safe($movie, 'poster', '');
                    $poster_url = poster_src($poster);
                    $avg_rating = $movie['avg_rating'];
                    $review_count = $movie['review_count'];
                    ?>
                    <div class="movie-card bg-white rounded-xl shadow-md hover:shadow-xl transition duration-300 overflow-hidden border border-gray-200 group flex flex-col">
  
                        <!-- Poster dan Rating -->
                        <div class="relative">
                            <img src="<?php echo $poster_url; ?>" class="w-full aspect-[2/3] object-cover group-hover:scale-105 transition duration-500">
                            <!-- Rating Display -->
                            <div class="absolute top-2 right-2 bg-cinemaBlack/80 text-cinemaGold text-xs font-bold px-2 py-1 rounded flex items-center gap-1 shadow-md">
                                <i class="ph ph-star-fill text-sm"></i> 
                                <span><?= $avg_rating ?></span>
                                <span class="text-gray-400 font-medium ml-1"> (<?= $review_count ?>)</span>
                            </div>

                            <!-- Overlay Beli Tiket -->
                            <div class="absolute inset-0 bg-black/50 opacity-0 group-hover:opacity-100 transition duration-300 flex flex-col justify-center items-center">
                                <?php if ($id !== ''): ?>
                                    <a href="buy_ticket.php?id=<?php echo urlencode($id); ?>"
                                        class="bg-cinemaRed hover:bg-red-700 text-white px-6 py-2 rounded-full font-bold text-sm flex items-center gap-2 transition transform translate-y-4 group-hover:translate-y-0 shadow-lg">
                                        <i class="ph ph-ticket text-lg"></i> Beli Tiket
                                    </a>
                                <?php endif; ?>
                            </div>
                        </div>

                        <!-- Info Film -->
                        <div class="p-4">
                            <h4 class="font-bold text-gray-800 text-lg truncate flex-1"><?php echo $judul; ?></h4>
                            <div class="flex justify-between items-center text-xs text-gray-500 mt-2">
                                <span>Durasi: <?= safe($movie, 'duration', 0) ?> Menit</span>
                                <span class="border border-gray-400 px-1 rounded"><?= safe($movie, 'age_rating', 'SU') ?></span>
                            </div>

                            <!-- Link Login/Beli Tiket -->
                            <?php if ($id !== ''): ?>
                                <?php if ($is_logged_in): ?>
                                    <a href="buy_ticket.php?id=<?php echo urlencode($id); ?>"
                                        class="mt-4 block w-full text-center bg-cinemaRed hover:bg-red-700 text-white py-2 rounded-lg text-sm font-semibold transition">
                                        Beli Tiket
                                    </a>
                                <?php else: ?>
                                    <!-- Arahkan ke Login jika belum login -->
                                    <a href="../login.php"
                                        class="mt-4 block w-full text-center bg-cinemaGold hover:bg-yellow-600 text-black py-2 rounded-lg text-sm font-semibold transition">
                                        Login untuk Beli
                                    </a>
                                <?php endif; ?>
                            <?php else: ?>
                                <button class="mt-4 w-full bg-gray-400 text-white py-2 rounded-lg text-sm font-semibold cursor-not-allowed" disabled>
                                    Tidak tersedia
                                </button>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>

            <?php else: ?>
                <div class="col-span-4 text-center py-10 bg-white rounded-xl border border-dashed border-gray-300">
                    <i class="ph ph-film-slash text-4xl text-gray-300 mb-3"></i>
                    <p class="text-gray-500">Belum ada film tersedia.</p>
                </div>
            <?php endif; ?>
        </div>


        <!-- COMING SOON -->
        <h2 class="text-3xl font-bold mt-12 mb-2 flex items-center gap-2">
            <i class="ph ph-hourglass-medium text-cinemaGold"></i> Segera Tayang
        </h2>
        <p class="text-gray-500 mb-8">Film yang akan tayang — belum bisa dibeli sekarang.</p>

        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
            <?php if (count($coming_soon) > 0): ?>
                <?php foreach ($coming_soon as $movie): ?>
                    <?php
                    $judul = htmlspecialchars(safe($movie, 'judul', '-'));
                    $poster = safe($movie, 'poster', '');
                    $description = htmlspecialchars(safe($movie, 'description', 'Belum ada deskripsi.'));
                    $release_date = safe($movie, 'release_date', '');
                    $poster_url = poster_src($poster);
                    $release_date_fmt = $release_date ? date('d M Y', strtotime($release_date)) : '-';
                    ?>
                    <div class="movie-card coming-card bg-white rounded-xl shadow-sm overflow-hidden border border-gray-200 flex flex-col">

                        <div class="relative">
                            <img src="<?php echo $poster_url; ?>" class="w-full aspect-[2/3] object-cover">
                            <div class="absolute top-2 left-2 bg-cinemaRed text-white text-xs px-2 py-1 rounded">
                                COMING SOON
                            </div>
                        </div>

                        <div class="p-4 flex flex-col flex-1 justify-between">
                            <h4 class="font-bold text-gray-800 text-lg"><?php echo $judul; ?></h4>

                            <p class="text-gray-600 text-sm mt-3 line-clamp-4">
                                <?php echo $description; ?>
                            </p>

                            <div class="mt-3 text-sm text-gray-500 flex items-center gap-2">
                                <i class="ph ph-calendar"></i>
                                Release Date: <?php echo $release_date_fmt; ?>
                            </div>

                            <button class="mt-4 w-full bg-gray-400 text-white py-2 rounded-lg text-sm font-semibold cursor-not-allowed" disabled>
                                Not Available
                            </button>
                        </div>

                    </div>
                <?php endforeach; ?>

            <?php else: ?>
                <div class="col-span-4 text-center py-10 bg-white rounded-xl border border-dashed border-gray-300">
                    <i class="ph ph-hourglass text-4xl text-gray-300 mb-3"></i>
                    <p class="text-gray-500">Tidak ada film yang akan datang.</p>
                </div>
            <?php endif; ?>
        </div>

    </div>
</body>

</html>