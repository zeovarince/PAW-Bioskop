<?php
session_start(); // Ditambahkan: Memastikan session dimulai
include "../koneksi.php";
include "../function.php"; // Ditambahkan: Memuat fungsi getStudios() dan getAverageRating()

// cek jika id film ada
if (!isset($_GET['id'])) {
    header("Location: movies.php");
    exit;
}

$id = $_GET['id'];
$id_safe = mysqli_real_escape_string($conn, $id);

// ambil data film
$query = mysqli_query($conn, "SELECT * FROM movies WHERE Id_movie = '{$id_safe}'");
if (!$query) die('Query error: ' . mysqli_error($conn));

$movie = mysqli_fetch_assoc($query);
if (!$movie) {
    echo "Film tidak ditemukan!";
    exit;
}

// Ambil Data Rating dari Database
$rating_data = getAverageRating($id_safe);
$avg_rating = $rating_data['average'];
$review_count = $rating_data['count'];

// Ambil Data Studio dari Database
$studios_db = getStudios();


function col($arr, $key, $default = '')
{
    return isset($arr[$key]) ? $arr[$key] : $default;
}

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
    $poster_src = 'https://via.placeholder.com/400x600?text=No+Poster';
}

// generate tanggal 7 hari, hanya hari ini aktif
$tanggalList = [];
for ($i = 0; $i < 7; $i++) {
    $ts = strtotime("+$i day");
    $tanggalList[] = [
        'label' => date("d M • l", $ts),
        'value' => date("Y-m-d", $ts),
        'enabled' => true 
    ];
}

// Jam Tayang yang Hardcoded (Sesuai permintaan non-AJAX)
$showtimes = ['12:30', '14:45', '17:10', '19:20', '21:00'];
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title><?php echo $judul; ?> - Buy Ticket</title>
<link rel="icon" href="../../logo.png">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
<!-- Tambahkan Tailwind CSS untuk Konsistensi Styling -->
<script src="https://cdn.tailwindcss.com"></script> 
<style>
body { background: #f5f5f5; }
.movie-card { display: flex; flex-wrap: wrap; gap: 15px; background: #fff; border-radius: 10px; padding: 15px; }
.poster-img { width: 180px; border-radius: 8px; object-fit: cover; }
.movie-details { flex: 1 1 300px; }

.disabled-date {
    background: #ddd !important;
    color: #888 !important;
    border-color: #ccc !important;
    cursor: not-allowed !important;
    pointer-events: none !important;
}

.active-btn { background:#b91c1c !important; color:white !important; border-color:#b91c1c !important; }
/* Style untuk rating */
.rating-pill {
    background: #fcfcfc;
    color: #FFD700;
    border-radius: 9999px;
    padding: 2px 8px;
    font-size: 0.8rem;
    font-weight: bold;
    border: 1px solid #ddd;
}
</style>
</head>
<body>

<div class="container my-4">
    <a href="movies.php" class="btn btn-sm btn-secondary mb-3">← Kembali ke Daftar Film</a>

    <div class="movie-card">
        <img src="<?php echo $poster_src; ?>" class="poster-img">
        <div class="movie-details">
            <h3 class="fw-bold mb-1"><?php echo $judul; ?></h3>
            
            <!-- TAMPILAN RATING BARU DARI DATABASE -->
            <p class="mb-1">
                <span class="rating-pill">
                    <?php echo $avg_rating; ?> ⭐ (<?php echo $review_count; ?> Reviews)
                </span>
                <span class="text-muted small ms-2">| Age: <?php echo $age_rating; ?></span>
            </p>
            <!-- END TAMPILAN RATING BARU -->

            <p class="mb-1 mt-3"><strong>Genre:</strong> <?php echo $genre; ?></p>
            <p class="mb-1"><strong>Duration:</strong> <?php echo $duration; ?> Min</p>
            <p class="mb-1"><strong>Director:</strong> <?php echo $director; ?></p>
            <p class="mb-1"><strong>Release Date:</strong> <?php echo $release_date; ?></p>
            <p class="mt-2 fw-semibold mb-1">Sinopsis</p>
            <div><?php echo $description; ?></div>
        </div>
    </div>

    <div class="ticket-card mt-3 p-3 bg-white rounded">
        <h5 class="fw-bold">Buy Ticket</h5>

        <!-- Form Validation -->
        <form id="bookingForm" action="buy_ticket.php?action=validate" method="POST">
            <input type="hidden" name="movie_id" value="<?= $id_safe ?>">

            <label class="form-label fw-semibold">Pilih Studio</label>
            <select class="form-select mb-2" id="studio" name="studio_id" required>
                <?php if (empty($studios_db)): ?>
                    <option value="" disabled selected>-- Tidak ada Studio Aktif --</option>
                <?php else: ?>
                    <?php foreach ($studios_db as $s): ?>
                        <option value="<?= $s['Id_studio'] ?>" data-name="<?= htmlspecialchars($s['nama_studio']) ?>">
                            <?= htmlspecialchars($s['nama_studio']) ?>
                        </option>
                    <?php endforeach; ?>
                <?php endif; ?>
            </select>


            <label class="form-label fw-semibold">Jadwal</label>
            <div id="tanggalList" class="mb-2">
                <?php foreach ($tanggalList as $index => $t): ?>
                    <button type="button" class="btn btn-outline-dark me-2 mb-2 tanggal-btn 
                    <?php echo $t['enabled'] ? '' : 'disabled-date'; ?>
                    <?php echo $index === 0 ? 'active-btn' : ''; ?>" 
                        data-date="<?php echo $t['value']; ?>"
                        <?php echo $t['enabled'] ? '' : 'disabled'; ?>>
                        <?php echo $t['label']; ?>
                    </button>
                <?php endforeach; ?>
                 <input type="hidden" name="date" id="selected_date" value="<?= $tanggalList[0]['value'] ?? '' ?>">
            </div>

            <label class="form-label fw-semibold">Jam Tayang</label>
            <div id="showtimes" class="mb-2 d-flex flex-wrap gap-2">
                <?php foreach ($showtimes as $time): ?>
                    <button type="button" class="btn btn-outline-dark me-2 mb-2 showtime-btn" data-time="<?php echo $time; ?>">
                        <?php echo $time; ?>
                    </button>
                <?php endforeach; ?>
                <input type="hidden" name="time" id="selected_time">
            </div>

            <label class="form-label fw-semibold">Jumlah Tiket</label>
            <input id="qty" name="qty" type="number" min="1" value="1" class="form-control mb-2" required>

            <button type="submit" id="buyNowBtn" class="btn btn-danger w-100 mt-3">Buy Ticket</button>
        </form>

    </div>

</div>

<!-- Modal -->
<div class="modal fade" id="confirmModal" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Konfirmasi Pesanan</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <p id="confirmText"></p>
      </div>
      <div class="modal-footer">
        <button id="confirmProceed" class="btn btn-danger">Pilih Kursi</button>
        <button class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
      </div>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
    const bookingForm = document.getElementById('bookingForm');
    const studioSelect = document.getElementById('studio');
    
    // 1. Event Listener: Ganti Tanggal
    document.querySelectorAll('.tanggal-btn').forEach(btn => {
        btn.addEventListener('click', () => {
            document.querySelectorAll('.tanggal-btn').forEach(b => b.classList.remove('active-btn'));
            document.querySelectorAll('.showtime-btn').forEach(b => b.classList.remove('active-btn')); 
            document.getElementById('selected_time').value = ''; 

            btn.classList.add('active-btn');
            document.getElementById('selected_date').value = btn.dataset.date;
        });
    });
    
    // 2. Event Listener: Ganti Jam
    document.querySelectorAll('.showtime-btn').forEach(btn => {
        btn.addEventListener('click', () => {
            document.querySelectorAll('.showtime-btn').forEach(b => b.classList.remove('active-btn'));
            btn.classList.add('active-btn');
            document.getElementById('selected_time').value = btn.dataset.time;
        });
    });
    
    // 3. Set initial active states on load
    document.addEventListener('DOMContentLoaded', () => {
        const firstDateBtn = document.querySelector('.tanggal-btn');
        if (firstDateBtn && !document.querySelector('.tanggal-btn.active-btn')) {
             firstDateBtn.classList.add('active-btn');
             document.getElementById('selected_date').value = firstDateBtn.dataset.date;
        }
    });

    // 4. Form Submission (Triggered by Buy Ticket button click)
    bookingForm.addEventListener('submit', function(e) {
        e.preventDefault(); 

        // Ambil data terpilih untuk ditampilkan di Modal
        const studioName = studioSelect.options[studioSelect.selectedIndex]?.dataset.name || 'N/A';
        
        const selectedDate = document.getElementById('selected_date').value;
        const selectedTime = document.getElementById('selected_time').value;
        const qty = document.getElementById('qty').value;
        
        // Validasi Dasar
        if (!selectedDate || !selectedTime || !studioSelect.value || qty < 1) {
            alert("Mohon lengkapi pilihan tanggal, jam, studio, dan jumlah tiket.");
            return;
        }

        // Tampilkan Modal Konfirmasi
        document.getElementById('confirmText').innerText =
            `Film: <?php echo addslashes($judul); ?>\nTanggal: ${tanggalBtn.innerText.trim()}\nJam: ${selectedTime}\nStudio: ${studioName}\nJumlah: ${qty}\n\nLanjutkan ke pemilihan kursi?`;

        const confirmModal = new bootstrap.Modal(document.getElementById('confirmModal'));
        confirmModal.show();
    });

    // 5. Konfirmasi Lanjut ke Pilih Kursi
    document.getElementById('confirmProceed').addEventListener('click', function() {
        // Setelah konfirmasi, set action form ke validate dan submit
        bookingForm.submit(); 
    });
</script>

</body>

</html>