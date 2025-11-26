<?php
include "../koneksi.php";

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

function col($arr, $key, $default = '') {
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
        'enabled' => ($i == 0) // hanya hari ini yang aktif
    ];
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title><?php echo $judul; ?> - Buy Ticket</title>
<link rel="icon" href="../../logo.png">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">

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
</style>
</head>
<body>

<div class="container my-4">

    <div class="movie-card">
        <img src="<?php echo $poster_src; ?>" class="poster-img">
        <div class="movie-details">
            <h3 class="fw-bold mb-1"><?php echo $judul; ?></h3>
            <p><strong>Rating:</strong> <?php echo $rating; ?> ⭐ (<?php echo $age_rating; ?>)</p>
            <p><strong>Genre:</strong> <?php echo $genre; ?></p>
            <p><strong>Duration:</strong> <?php echo $duration; ?> Min</p>
            <p><strong>Director:</strong> <?php echo $director; ?></p>
            <p><strong>Release Date:</strong> <?php echo $release_date; ?></p>
            <p class="mt-2 fw-semibold mb-1">Sinopsis</p>
            <div><?php echo $description; ?></div>
        </div>
    </div>

    <div class="ticket-card mt-3 p-3 bg-white rounded">
        <h5 class="fw-bold">Buy Ticket</h5>

        <label class="form-label fw-semibold">Pilih Studio</label>
        <select class="form-select mb-2" id="studio">
            <option value="1" data-price="35000">Regular 2D - Rp 35.000</option>
            <option value="2" data-price="55000">Dolby Atmos - Rp 55.000</option>
            <option value="3" data-price="75000">IMAX - Rp 75.000</option>
        </select>

        <label class="form-label fw-semibold">Jadwal</label>
        <div id="tanggalList" class="mb-2">
            <?php foreach ($tanggalList as $t): ?>
                <button class="btn btn-outline-dark me-2 mb-2 tanggal-btn 
                    <?php echo $t['enabled'] ? '' : 'disabled-date'; ?>" 
                    data-date="<?php echo $t['value']; ?>"
                    <?php echo $t['enabled'] ? '' : 'disabled'; ?>>
                    <?php echo $t['label']; ?>
                </button>
            <?php endforeach; ?>
        </div>

        <label class="form-label fw-semibold">Jam Tayang</label>
        <div id="showtimes" class="mb-2">
            <button class="btn btn-outline-dark me-2 mb-2 showtime-btn" data-time="12:30">12:30</button>
            <button class="btn btn-outline-dark me-2 mb-2 showtime-btn" data-time="14:45">14:45</button>
            <button class="btn btn-outline-dark me-2 mb-2 showtime-btn" data-time="17:10">17:10</button>
            <button class="btn btn-outline-dark me-2 mb-2 showtime-btn" data-time="19:20">19:20</button>
            <button class="btn btn-outline-dark me-2 mb-2 showtime-btn" data-time="21:00">21:00</button>
        </div>

        <label class="form-label fw-semibold">Jumlah Tiket</label>
        <input id="qty" type="number" min="1" value="1" class="form-control mb-2">

        <button id="buyNowBtn" class="btn btn-danger w-100">Buy Ticket</button>
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
        <a href="#" id="confirmProceed" class="btn btn-danger">Pilih Kursi</a>
        <button class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
      </div>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
// pilih tanggal
document.querySelectorAll('.tanggal-btn').forEach(btn => {
    btn.addEventListener('click', () => {
        document.querySelectorAll('.tanggal-btn').forEach(b => b.classList.remove('active-btn'));
        btn.classList.add('active-btn');
    });
});
// pilih jam
document.querySelectorAll('.showtime-btn').forEach(btn => {
    btn.addEventListener('click', () => {
        document.querySelectorAll('.showtime-btn').forEach(b => b.classList.remove('active-btn'));
        btn.classList.add('active-btn');
    });
});

// BUY TICKET
document.getElementById('buyNowBtn').addEventListener('click', () => {
    const studioSel = document.getElementById('studio');
    const studio = studioSel.value;
    const price = studioSel.options[studioSel.selectedIndex].dataset.price;

    const tanggalBtn = document.querySelector('.tanggal-btn.active-btn');
    if (!tanggalBtn) { alert("Pilih jadwal terlebih dahulu."); return; }
    const tanggal = tanggalBtn.dataset.date;

    const jamBtn = document.querySelector('.showtime-btn.active-btn');
    if (!jamBtn) { alert("Pilih jam tayang terlebih dahulu."); return; }
    const time = jamBtn.dataset.time;

    const qty = parseInt(document.getElementById('qty').value) || 1;
    const total = parseInt(price) * qty;

    document.getElementById('confirmText').innerText =
        `Film: <?php echo addslashes($judul); ?>\nTanggal: ${tanggal}\nJam: ${time}\nStudio: ${studio}\nJumlah: ${qty}\nTotal: Rp ${total.toLocaleString('id-ID')}`;

    const params = new URLSearchParams({
        id: '<?php echo $id_safe; ?>',
        studio: studio,        // ← ini yang benar
        time: time,
        date: tanggal,
        qty: qty,
        price: price
    });


    // MASUK KE STUDIOS.PHP
    document.getElementById('confirmProceed').href = 'pilih_kursi.php?' + params.toString();

    new bootstrap.Modal(document.getElementById('confirmModal')).show();
});
</script>

</body>
</html>
