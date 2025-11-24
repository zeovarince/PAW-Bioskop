<?php
include '../koneksi.php';

function safe($row, $keys, $default = '') {
    if (!is_array($keys)) $keys = [$keys];
    foreach ($keys as $key) {
        if (isset($row[$key]) && $row[$key] !== null) return $row[$key];
    }
    return $default;
}

function poster_src($filename) {
    if (empty($filename)) return 'https://via.placeholder.com/300x450?text=No+Poster';
    $p1 = '../assets/images/' . $filename;
    if (file_exists($p1)) return $p1;
    return 'https://via.placeholder.com/300x450?text=No+Poster';
}

$sql = "SELECT * FROM movies ORDER BY id_movie DESC";
$res = mysqli_query($conn, $sql);
if ($res === false) die("Query Error: " . mysqli_error($conn));

$today = date('Y-m-d');
$now_showing = [];
$coming_soon = [];

while ($row = mysqli_fetch_assoc($res)) {
    $release_date = safe($row, ['release_date','Release_date','rilis'], '');
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

<nav class="bg-cinemaBlack border-b border-gray-800 py-4">
    <div class="max-w-7xl mx-auto px-6 flex justify-between items-center">
        <div class="flex items-center gap-3">
            <img src="../logo.png" class="h-10" alt="Logo">
            <h1 class="text-2xl font-bold text-cinemaRed uppercase">ONIC <span class="text-white">CINEMA</span></h1>
        </div>
        <div class="hidden md:block">
            <div class="ml-10 flex items-baseline space-x-4">
                <a href="index.php" class="text-gray-300 hover:text-white px-3 py-2 rounded-md text-sm font-medium transition">Home</a>
                <a href="movies.php" class="bg-cinemaRed text-white px-3 py-2 rounded-md text-sm font-medium transition">Movies</a>
                <a href="schedule.php" class="text-gray-300 hover:text-cinemaGold px-3 py-2 rounded-md text-sm font-medium transition">Schedule</a>
                <a href="contact.php" class="text-gray-300 hover:text-cinemaGold px-3 py-2 rounded-md text-sm font-medium transition">Contact</a>
            </div>
        </div>
    </div>
</nav>

<div class="max-w-7xl mx-auto px-6 py-10">

<h2 class="text-3xl font-bold mb-2 flex items-center gap-2">
    <i class="ph ph-film-strip text-cinemaRed"></i> Movies
</h2>

<!-- NOW SHOWING -->
<div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
<?php if (count($now_showing) > 0): ?>
    <?php foreach ($now_showing as $movie): ?>
    <?php
        $id = safe($movie, ['id_movie','Id_movie','id','movie_id'], '');
        $judul = htmlspecialchars(safe($movie, 'judul', '-'));
        $poster = safe($movie, 'poster', '');
        $duration = (int) safe($movie, 'duration', 0);
        $poster_url = poster_src($poster);
    ?>
    <div class="movie-card bg-white rounded-xl shadow-sm hover:shadow-xl transition duration-300 overflow-hidden border border-gray-100 group">
        <div class="relative">
            <?php if ($id !== ''): ?>
            <a href="buy_ticket.php?id=<?php echo urlencode($id); ?>">
                <img src="<?php echo $poster_url; ?>" class="w-full h-64 object-cover group-hover:scale-105 transition duration-500">
            </a>
            <?php else: ?>
                <img src="<?php echo $poster_url; ?>" class="w-full h-64 object-cover">
            <?php endif; ?>
        </div>

        <div class="p-4">
            <h4 class="font-bold text-gray-800 text-lg truncate"><?php echo $judul; ?></h4>

            <?php if ($id !== ''): ?>
            <a href="buy_ticket.php?id=<?php echo urlencode($id); ?>" 
               class="mt-4 block w-full text-center bg-cinemaRed hover:bg-red-700 text-white py-2 rounded-lg text-sm font-semibold transition">
                Buy Ticket
            </a>
            <?php else: ?>
            <button class="mt-4 w-full bg-gray-400 text-white py-2 rounded-lg text-sm font-semibold" disabled>
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
    <i class="ph ph-hourglass-medium text-cinemaGold"></i> Coming Soon
</h2>
<p class="text-gray-500 mb-8">Film yang akan tayang — belum bisa dibeli sekarang.</p>

<div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
<?php if (count($coming_soon) > 0): ?>
    <?php foreach ($coming_soon as $movie): ?>
    <?php
        $judul = htmlspecialchars(safe($movie,'judul','-'));
        $poster = safe($movie,'poster','');
        $description = htmlspecialchars(safe($movie,'description','Belum ada deskripsi.'));
        $release_date = safe($movie,'release_date','');
        $poster_url = poster_src($poster);
        $release_date_fmt = $release_date ? date('d M Y', strtotime($release_date)) : '-';
    ?>
    <div class="movie-card coming-card bg-white rounded-xl shadow-sm overflow-hidden border border-gray-200">

        <div class="relative">
            <img src="<?php echo $poster_url; ?>" class="w-full h-64 object-cover">
            <div class="absolute top-2 left-2 bg-cinemaRed text-white text-xs px-2 py-1 rounded">
                COMING SOON
            </div>
        </div>

        <div class="p-4">
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
