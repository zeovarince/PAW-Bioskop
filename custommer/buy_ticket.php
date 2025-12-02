<?php
ob_start();
session_start();

include "../koneksi.php";
include "../function.php"; 

// 1. STATUS LOGIN
$is_logged_in = isset($_SESSION['login']);
$user_id = $_SESSION['user_id'] ?? null;

// 2. VALIDASI ID FILM
$id = '';
if (isset($_GET['id']) && !empty($_GET['id'])) {
    $id = $_GET['id'];
} elseif (isset($_POST['movie_id']) && !empty($_POST['movie_id'])) {
    $id = $_POST['movie_id'];
} else {
    header("Location: movies.php");
    exit;
}

$id_safe = mysqli_real_escape_string($conn, $id);

// --- 3. LOGIKA PROSES BOOKING (Validasi Jadwal) ---
if (isset($_GET['action']) && $_GET['action'] == 'validate') {
    if (!$is_logged_in) {
        header("Location: ../login.php");
        exit;
    }
    $post_movie_id = $_POST['movie_id'];
    $post_studio_id = $_POST['studio_id'];
    $post_date = $_POST['date']; 
    $post_time = $_POST['time']; 
    $post_qty = $_POST['qty'];
    $datetime_search = "$post_date $post_time"; 

    $query_cari_jadwal = "SELECT Id_jadwal FROM jadwal WHERE Id_movie = '$post_movie_id' AND Id_studio = '$post_studio_id' AND Waktu_tayang LIKE '$datetime_search%' LIMIT 1";
    $cek_jadwal = mysqli_query($conn, $query_cari_jadwal);

    if (mysqli_num_rows($cek_jadwal) > 0) {
        $data_jadwal = mysqli_fetch_assoc($cek_jadwal);
        $id_jadwal_fix = $data_jadwal['Id_jadwal'];
        header("Location: select_seat.php?id_jadwal=$id_jadwal_fix&qty=$post_qty");
        exit;
    } else {
        echo "<script>alert('Maaf, jadwal tayang tidak tersedia.'); window.history.back();</script>";
        exit;
    }
}

// --- 4. AMBIL DATA FILM ---
$query = mysqli_query($conn, "SELECT * FROM movies WHERE Id_movie = '{$id_safe}'");
if (!$query || mysqli_num_rows($query) == 0) {
    echo "<script>alert('Film tidak ditemukan!'); window.location='movies.php';</script>";
    exit;
}
$movie = mysqli_fetch_assoc($query);

// Helper
function col($arr, $key, $default = '') { return isset($arr[$key]) ? $arr[$key] : $default; }

$judul = htmlspecialchars(col($movie, 'judul'));
$poster_file = col($movie, 'poster');
$rating = htmlspecialchars(col($movie, 'rating', '13+'));
$age_rating = htmlspecialchars(col($movie, 'age_rating', '13+'));
$genre = htmlspecialchars(col($movie, 'genre'));
$duration = (int) col($movie, 'duration');
$director = htmlspecialchars(col($movie, 'director'));
$release_date_raw = col($movie, 'release_date', '');
$release_date = $release_date_raw ? date("d M Y", strtotime($release_date_raw)) : "-";
$description = htmlspecialchars(col($movie, 'description'));

$poster_src = '../assets/images/' . $poster_file;
if (empty($poster_file) || !file_exists($poster_src)) {
    $poster_src = 'https://placehold.co/400x600?text=No+Poster';
}

// --- AMBIL DATA RATING & REVIEW ---
// Hitung Rata-rata Rating
$q_avg = mysqli_query($conn, "SELECT AVG(rating) as avg_rat, COUNT(*) as total FROM reviews WHERE Id_movie = '$id_safe'");
$d_avg = mysqli_fetch_assoc($q_avg);
$avg_rating_db = number_format($d_avg['avg_rat'], 1); 
$review_count_db = $d_avg['total'];

// Ambil Daftar Komentar Orang Lain (Read Only)
$q_reviews_list = mysqli_query($conn, "SELECT r.*, u.username FROM reviews r JOIN users u ON r.Id_user = u.Id_user WHERE r.Id_movie = '$id_safe' ORDER BY r.created_at DESC LIMIT 10");

// Data untuk Form Booking
$studios_db = [];
if (function_exists('getStudios')) {
    $studios_db = getStudios();
} else {
    $q_std = mysqli_query($conn, "SELECT * FROM studios");
    while($row = mysqli_fetch_assoc($q_std)) $studios_db[] = $row;
}

$tanggalList = [];
for ($i = 0; $i < 7; $i++) {
    $ts = strtotime("+$i day");
    $tanggalList[] = ['label' => date("d M • D", $ts), 'value' => date("Y-m-d", $ts), 'enabled' => true ];
}
$showtimes = ['12:30', '14:45', '17:10', '19:20', '21:00'];
?>

<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title><?php echo $judul; ?> - Buy Ticket</title>
<link rel="icon" href="../logo.png">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
<script src="https://cdn.tailwindcss.com"></script>
<script src="https://unpkg.com/@phosphor-icons/web"></script>
<style>
    body { background: #f5f5f5; font-family: 'Inter', sans-serif; }
    .movie-card { display: flex; flex-wrap: wrap; gap: 20px; background: #fff; border-radius: 12px; padding: 20px; box-shadow: 0 4px 12px rgba(0,0,0,0.05); }
    .poster-img { width: 200px; border-radius: 8px; object-fit: cover; box-shadow: 0 4px 8px rgba(0,0,0,0.2); }
    .movie-details { flex: 1 1 300px; }
    
    .active-btn { background:#E50914 !important; color:white !important; border-color:#E50914 !important; }
    .rating-pill {
        background: #fff8e1; color: #f59e0b; border-radius: 99px; padding: 4px 12px; 
        font-size: 0.85rem; font-weight: bold; border: 1px solid #fcd34d; display: inline-flex; align-items: center; gap: 4px;
    }
</style>
</head>
<body>

<div class="container my-5" style="max-width: 900px;">
    <a href="movies.php" class="btn btn-outline-secondary btn-sm mb-4 fw-bold">← Kembali ke Movies</a>

    <!-- CARD FILM -->
    <div class="movie-card">
        <img src="<?php echo $poster_src; ?>" class="poster-img" onerror="this.src='https://placehold.co/400x600?text=No+Poster'">
        <div class="movie-details">
            <h1 class="fs-2 fw-bold text-dark mb-2"><?php echo $judul; ?></h1>
            
            <!-- TAMPILAN RATING (Hanya Menampilkan) -->
            <div class="mb-3">
                <span class="rating-pill" title="Rating rata-rata dari pengguna">
                    ⭐ <?php echo $avg_rating_db; ?>/5 (<?php echo $review_count_db; ?> Ulasan)
                </span>
                <span class="badge bg-secondary ms-2"><?php echo $age_rating; ?></span>
            </div>

            <div class="row text-sm text-muted mb-3">
                <div class="col-6 mb-1"><strong>Genre:</strong><br><?php echo $genre; ?></div>
                <div class="col-6 mb-1"><strong>Durasi:</strong><br><?php echo $duration; ?> Menit</div>
                <div class="col-6 mb-1"><strong>Sutradara:</strong><br><?php echo $director; ?></div>
                <div class="col-6 mb-1"><strong>Rilis:</strong><br><?php echo $release_date; ?></div>
            </div>
            
            <p class="fw-bold mb-1">Sinopsis</p>
            <p class="text-muted small" style="line-height: 1.6;"><?php echo $description; ?></p>
        </div>
    </div>

    <!-- CARD PEMESANAN -->
    <div class="bg-white rounded-xl shadow-sm p-4 mt-4 border border-gray-100 mb-5">
        <h5 class="fw-bold border-bottom pb-3 mb-3">Pilih Jadwal & Tiket</h5>
        
        <?php if (!$is_logged_in): ?>
            <div class="text-center py-5">
                <p class="text-muted mb-3">Silakan login untuk melanjutkan pemesanan.</p>
                <a href="../login.php" class="btn btn-danger fw-bold px-5 py-2 rounded-pill">Masuk / Daftar</a>
            </div>
        <?php else: ?>
            <form id="bookingForm" action="buy_ticket.php?action=validate&id=<?= $id_safe ?>" method="POST">
                <input type="hidden" name="movie_id" value="<?= $id_safe ?>">
                
                <!-- PILIH STUDIO -->
                <div class="mb-4">
                    <label class="form-label fw-bold small text-uppercase text-muted">Pilih Studio</label>
                    <select class="form-select" id="studio" name="studio_id" required>
                        <option value="" disabled selected>-- Pilih Studio --</option>
                        <?php foreach ($studios_db as $s): ?>
                            <option value="<?= $s['Id_studio'] ?>" data-name="<?= htmlspecialchars($s['nama_studio']) ?>">
                                <?= htmlspecialchars($s['nama_studio']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- PILIH TANGGAL -->
                <div class="mb-4">
                    <label class="form-label fw-bold small text-uppercase text-muted">Pilih Tanggal</label>
                    <div class="d-flex flex-wrap gap-2" id="tanggalList">
                        <?php foreach ($tanggalList as $index => $t): ?>
                            <button type="button" class="btn btn-outline-dark btn-sm rounded-pill tanggal-btn <?php echo $index === 0 ? 'active-btn' : ''; ?>" data-date="<?php echo $t['value']; ?>"><?php echo $t['label']; ?></button>
                        <?php endforeach; ?>
                        <input type="hidden" name="date" id="selected_date" value="<?= $tanggalList[0]['value'] ?>">
                    </div>
                </div>

                <!-- PILIH JAM -->
                <div class="mb-4">
                    <label class="form-label fw-bold small text-uppercase text-muted">Pilih Jam</label>
                    <div class="d-flex flex-wrap gap-2" id="showtimes">
                        <?php foreach ($showtimes as $index => $time): ?>
                            <button type="button" class="btn btn-outline-dark btn-sm rounded-pill showtime-btn <?php echo $index === 0 ? 'active-btn' : ''; ?>" data-time="<?php echo $time; ?>"><?php echo $time; ?></button>
                        <?php endforeach; ?>
                        <input type="hidden" name="time" id="selected_time" value="<?= $showtimes[0] ?>">
                    </div>
                </div>

                <!-- JUMLAH TIKET -->
                <div class="mb-4">
                    <label class="form-label fw-bold small text-uppercase text-muted">Jumlah Tiket</label>
                    <input id="qty" name="qty" type="number" min="1" max="10" value="1" class="form-control w-25" required>
                </div>

                <button type="submit" id="buyNowBtn" class="btn btn-danger w-100 py-3 fw-bold rounded-3 shadow-sm hover:shadow-lg transition">
                    Lanjut Pilih Kursi <i class="ph ph-arrow-right ms-1"></i>
                </button>
            </form>
        <?php endif; ?>
    </div>

    <!-- LIST ULASAN PENONTON (READ ONLY) -->
    <?php if (mysqli_num_rows($q_reviews_list) > 0): ?>
    <div class="bg-white rounded-xl shadow-sm p-5 border border-gray-100">
        <h5 class="fw-bold mb-4 flex items-center gap-2 border-bottom pb-3">
            <i class="ph ph-chat-text text-primary"></i> Apa Kata Penonton?
        </h5>

        <div class="d-flex flex-column gap-3">
            <?php while ($rev = mysqli_fetch_assoc($q_reviews_list)): ?>
                <div class="d-flex gap-3">
                    <!-- Avatar -->
                    <div class="flex-shrink-0">
                        <div class="w-10 h-10 rounded-circle bg-light d-flex align-items-center justify-content-center fw-bold text-secondary border">
                            <?= substr($rev['username'], 0, 1) ?>
                        </div>
                    </div>
                    <!-- Isi Review -->
                    <div>
                        <div class="d-flex align-items-center gap-2 mb-1">
                            <span class="fw-bold text-dark" style="font-size: 0.9rem;"><?= htmlspecialchars($rev['username']) ?></span>
                            <span class="text-warning" style="font-size: 0.8rem;">
                                <?php for($i=0; $i<$rev['rating']; $i++) echo '★'; ?>
                            </span>
                            <span class="text-muted ms-auto small" style="font-size: 0.75rem;">
                                <?= date('d M Y', strtotime($rev['created_at'])) ?>
                            </span>
                        </div>
                        <p class="text-secondary mb-0 small" style="line-height: 1.4;">
                            <?= htmlspecialchars($rev['komentar']) ?>
                        </p>
                    </div>
                </div>
                <hr class="my-2 text-muted opacity-25">
            <?php endwhile; ?>
        </div>
    </div>
    <?php else: ?>
        <div class="bg-white rounded-xl shadow-sm p-5 border border-gray-100 text-center py-5">
            <i class="ph ph-chat-teardrop-dots text-4xl text-gray-300 mb-2"></i>
            <p class="text-muted fw-bold">Belum ada ulasan.</p>
            <p class="small text-secondary">Jadilah orang pertama yang menonton film ini!</p>
        </div>
    <?php endif; ?>
    <!-- END LIST ULASAN -->

</div>

<!-- Modal Konfirmasi Booking -->
<div class="modal fade" id="confirmModal" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header border-0">
        <h5 class="modal-title fw-bold">Konfirmasi Pesanan</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <div class="alert alert-light border">
            <p id="confirmText" class="mb-0" style="white-space: pre-line;"></p>
        </div>
        <p class="small text-muted mt-2">*Pastikan jadwal yang Anda pilih sudah benar.</p>
      </div>
      <div class="modal-footer border-0">
        <button type="button" class="btn btn-light text-muted" data-bs-dismiss="modal">Batal</button>
        <button id="confirmProceed" type="button" class="btn btn-danger fw-bold px-4">Ya, Lanjut</button>
      </div>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
    const bookingForm = document.getElementById('bookingForm');
    const studioSelect = document.getElementById('studio');
    
    function setupSelection(selector, inputId) {
        const buttons = document.querySelectorAll(selector);
        const input = document.getElementById(inputId);
        buttons.forEach(btn => {
            btn.addEventListener('click', () => {
                buttons.forEach(b => b.classList.remove('active-btn'));
                btn.classList.add('active-btn');
                input.value = btn.dataset.date || btn.dataset.time;
            });
        });
    }
    setupSelection('.tanggal-btn', 'selected_date');
    setupSelection('.showtime-btn', 'selected_time');

    if (bookingForm) {
        bookingForm.addEventListener('submit', function(e) {
            e.preventDefault(); 
            const studioName = studioSelect.options[studioSelect.selectedIndex]?.dataset.name || 'N/A';
            const selectedDate = document.getElementById('selected_date').value;
            const selectedTime = document.getElementById('selected_time').value;
            const qty = document.getElementById('qty').value;
            
            if (!selectedDate || !selectedTime || !studioSelect.value || qty < 1) {
                alert("Mohon lengkapi pilihan tanggal, jam, studio, dan jumlah tiket.");
                return;
            }

            const tanggalBtn = document.querySelector('.tanggal-btn.active-btn');
            const tanggalText = tanggalBtn ? tanggalBtn.innerText : selectedDate;

            document.getElementById('confirmText').innerText = `Film: <?php echo addslashes($judul); ?>\nStudio: ${studioName}\nTanggal: ${tanggalText}\nJam: ${selectedTime}\nJumlah Tiket: ${qty}`;
            const confirmModal = new bootstrap.Modal(document.getElementById('confirmModal'));
            confirmModal.show();
        });
    }

    document.getElementById('confirmProceed')?.addEventListener('click', function() {
        bookingForm.submit(); 
    });
</script>

</body>
</html>