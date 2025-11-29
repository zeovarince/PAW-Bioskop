<?php
session_start();
if (!isset($_SESSION['login']) || $_SESSION['role'] != '2') {
    header("Location: ../login.php");
    exit;
}
include "../koneksi.php";
include "../function.php"; 

// --- HELPER FUNCTION (Bisa dipindahkan ke function.php) ---
function poster_src($filename) {
    if (empty($filename)) return 'https://via.placeholder.com/300x450?text=No+Poster';
    $p1 = '../assets/images/' . $filename;
    // Cek keberadaan file (penting)
    if (file_exists($p1)) return $p1;
    return 'https://via.placeholder.co/300x450?text=No+Poster';
}
// --- END HELPER FUNCTION ---

$user_id = $_SESSION['user_id'];
$id_movie = $_GET['id_movie'] ?? null;
$form_errors = [];
$success_message = '';

if (!$id_movie) {
    header("Location: dashboard.php");
    exit;
}

$movie = getMovies($id_movie);
if (!$movie) {
    die("Film tidak ditemukan.");
}

// Cek apakah user sudah pernah review
$cek_review = mysqli_query($conn, "SELECT * FROM reviews WHERE Id_user='".esc($user_id)."' AND Id_movie='".esc($id_movie)."'");
$existing_review = mysqli_fetch_assoc($cek_review);

// Logika Submit Review
if (isset($_POST['submit_review'])) {
    if ($existing_review) {
        $form_errors['general'] = "Anda sudah memberikan review untuk film ini.";
    } else {
        $data_post = array_merge($_POST, ['Id_user' => $user_id, 'Id_movie' => $id_movie]);
        $result = saveReview($data_post);
        
        if ($result === true) {
            $success_message = "Terima kasih! Review Anda berhasil disimpan.";
            // Ambil ulang review yang baru disimpan untuk ditampilkan
            $cek_review = mysqli_query($conn, "SELECT * FROM reviews WHERE Id_user='".esc($user_id)."' AND Id_movie='".esc($id_movie)."'");
            $existing_review = mysqli_fetch_assoc($cek_review);
        } else {
            $form_errors = $result;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Review: <?= htmlspecialchars($movie['judul']) ?></title>
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
        .star-rating input { display: none; }
        .star-rating label { font-size: 2rem; color: #ccc; cursor: pointer; transition: color 0.2s; }
        .star-rating input:checked ~ label,
        .star-rating input:hover ~ label,
        .star-rating label:hover,
        .star-rating label:hover ~ label { color: #FFD700; }
        /* Mengatur urutan bintang dari kanan ke kiri untuk efek hover yang benar */
        .star-rating { display: flex; flex-direction: row-reverse; justify-content: center; }
    </style>
</head>
<body class="bg-gray-100 font-sans text-gray-800">

    <!-- Navigasi Customer -->
    <nav class="bg-cinemaBlack border-b border-gray-800 py-4">
        <div class="max-w-7xl mx-auto px-6 flex justify-between items-center">
            <h1 class="text-3xl font-bold text-cinemaGold tracking-widest uppercase">ONIC <span class="text-white">CINEMA</span></h1>
            <div class="flex items-center gap-4">
                 <a href="dashboard.php" class="text-gray-300 hover:text-cinemaGold px-3 py-2 rounded-md text-sm font-medium transition">Dashboard</a>
                 <a href="../logout.php" class="bg-gray-800 hover:bg-cinemaRed text-white p-2 rounded-full transition" title="Logout">
                    <i class="ph ph-sign-out text-xl"></i>
                </a>
            </div>
        </div>
    </nav>
    
    <div class="max-w-xl mx-auto px-6 py-10">
        <a href="dashboard.php" class="text-gray-500 hover:text-cinemaRed mb-4 inline-flex items-center gap-1"><i class="ph ph-arrow-left"></i> Kembali ke Dashboard</a>

        <div class="bg-white shadow-xl rounded-xl p-8 border-t-4 border-cinemaRed">
            <h2 class="text-2xl font-bold mb-6 text-gray-800 flex items-center gap-2">
                <i class="ph ph-star-half text-cinemaGold"></i> Beri Review untuk:
            </h2>
            <div class="flex items-center gap-4 border-b pb-4 mb-6">
                <img src="<?= poster_src($movie['poster']) ?>" alt="<?= $movie['judul'] ?>" class="w-16 h-24 object-cover rounded-md shadow-md">
                <h3 class="text-xl font-extrabold text-gray-900"><?= htmlspecialchars($movie['judul']) ?></h3>
            </div>
            
            <?php if ($success_message): ?>
                <div class="p-4 mb-4 text-sm text-green-700 bg-green-100 rounded-lg" role="alert">
                    <?= $success_message ?>
                </div>
            <?php endif; ?>

            <?php if (isset($form_errors['general'])): ?>
                <div class="p-4 mb-4 text-sm text-red-700 bg-red-100 rounded-lg" role="alert">
                    <?= $form_errors['general'] ?>
                </div>
            <?php endif; ?>

            <?php if ($existing_review): ?>
                <!-- Tampilan Review yang Sudah Ada -->
                <div class="text-center p-6 bg-gray-50 rounded-lg border border-gray-200">
                    <p class="text-sm font-semibold text-gray-600 mb-3">Anda sudah memberikan rating:</p>
                    <div class="star-rating justify-center mb-4">
                        <?php for ($i = 5; $i >= 1; $i--): ?>
                            <label class="text-4xl <?= $existing_review['rating'] >= $i ? 'text-cinemaGold' : 'text-gray-300' ?>">★</label>
                        <?php endfor; ?>
                    </div>
                    <p class="text-gray-700 font-medium italic">"<?= htmlspecialchars($existing_review['komentar']) ?>"</p>
                </div>

            <?php else: ?>
                <!-- Formulir Input Review -->
                <form action="review_form.php?id_movie=<?= $id_movie ?>" method="POST">
                    
                    <!-- Input Rating Bintang -->
                    <div class="mb-6">
                        <label class="block text-center text-lg font-semibold text-gray-700 mb-3">Berapa Rating Anda?</label>
                        <div class="star-rating">
                            <?php for ($i = 5; $i >= 1; $i--): ?>
                                <input type="radio" id="star<?= $i ?>" name="rating" value="<?= $i ?>" required>
                                <label for="star<?= $i ?>">★</label>
                            <?php endfor; ?>
                        </div>
                        <?php if (isset($form_errors['rating'])): ?>
                            <p class="text-center text-red-500 text-sm mt-2"><?= $form_errors['rating'] ?></p>
                        <?php endif; ?>
                    </div>

                    <!-- Input Komentar -->
                    <div class="mb-6">
                        <label for="komentar" class="block text-sm font-semibold text-gray-700 mb-1">Komentar Anda (Opsional)</label>
                        <textarea id="komentar" name="komentar" rows="4" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-cinemaRed focus:border-cinemaRed transition duration-200" placeholder="Tuliskan pendapat Anda tentang film ini..."></textarea>
                    </div>

                    <button type="submit" name="submit_review" class="bg-cinemaRed hover:bg-red-700 text-white px-8 py-3 rounded-lg font-bold w-full shadow-lg transition">
                        Kirim Review
                    </button>
                </form>
            <?php endif; ?>

        </div>
    </div>
</body>
</html>