<?php
include "../koneksi.php";

// =======================
// GENERATE JADWAL OTOMATIS
// =======================

// Film 1-7
$film_ids = range(1,7);

// Studio 3 jenis: id => harga
$studio_ids = [
    1 => 35000, // Regular 2D
    2 => 55000, // Dolby Atmos
    3 => 75000  // IMAX
];

// Jam tayang
$jam_tayang = ['12:30','14:45','17:10','19:20','21:00'];

// Jumlah hari ke depan untuk generate jadwal (misal 7 hari)
$days = 7;

for($d=0; $d<$days; $d++){
    $tanggal = date('Y-m-d', strtotime("+$d day"));

    foreach ($film_ids as $id_film) {
        foreach ($studio_ids as $id_studio => $harga) {
            foreach ($jam_tayang as $jam) {
                $waktu_tayang = "$tanggal $jam:00";

                // Cek apakah jadwal sudah ada
                $cek = mysqli_query($conn, "SELECT Id_jadwal FROM jadwal 
                    WHERE Id_movie='$id_film' AND Id_studio='$id_studio' AND Waktu_tayang='$waktu_tayang'");

                if(mysqli_num_rows($cek) == 0){
                    // Insert jadwal baru
                    mysqli_query($conn, "INSERT INTO jadwal (Id_movie, Id_studio, Waktu_tayang, harga) 
                        VALUES ('$id_film','$id_studio','$waktu_tayang','$harga')");
                }
            }
        }
    }
}

// =======================
// HALAMAN PILIH KURSI
// =======================

// Ambil parameter dari URL
$id_movie = $_GET['id'] ?? '';
$id_studio = $_GET['studio'] ?? '';
$tanggal = $_GET['date'] ?? '';
$jam = $_GET['time'] ?? '';
$price = $_GET['price'] ?? 0;

if (!$id_movie || !$id_studio || !$tanggal || !$jam) {
    echo "Data jadwal tidak lengkap!";
    exit;
}

// Gabungkan waktu tayang
$waktu_tayang = "$tanggal $jam:00";

// Cari ID jadwal
$q = mysqli_query($conn, "
    SELECT Id_jadwal FROM jadwal 
    WHERE Id_movie='$id_movie'
    AND Id_studio='$id_studio'
    AND Waktu_tayang='$waktu_tayang'
    LIMIT 1
");

if (mysqli_num_rows($q) == 0) {
    echo "Jadwal tidak ditemukan!";
    exit;
}

$id_jadwal = mysqli_fetch_assoc($q)['Id_jadwal'];

// Ambil kursi yang sudah dibooking
$bookedQuery = mysqli_query($conn, "SELECT kursi FROM transaksi WHERE id_jadwal='$id_jadwal'");
$bookedSeats = [];
while ($b = mysqli_fetch_assoc($bookedQuery)) {
    $bookedSeats[] = $b['kursi'];
}

// Baris
$baris_nama = range('A', 'J');
?>
<!DOCTYPE html>
<html>
<head>
    <title>Pilih Kursi</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .seat {
            width: 50px;
            height: 50px;
            margin: 4px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 8px;
            font-weight: bold;
            cursor: pointer;
            user-select: none;
        }
        .reguler { background-color: #111; color: white; }
        .sweetbox { background-color: pink; color: black; width: 110px; }
        .none { background-color: white; border: 1px solid #ccc; }
        .selected { background-color: #0d6efd !important; color: white !important; }
        .booked { background-color: red !important; color: white !important; cursor: not-allowed; }
        .lorong { display: inline-block; width: 50px; }
    </style>
    <script>
        let kursiTerpilih = [];

        function pilihKursi(id) {
            let seat = document.getElementById(id);
            if (!seat || seat.classList.contains("booked")) return;

            if (seat.classList.contains("selected")) {
                seat.classList.remove("selected");
                kursiTerpilih = kursiTerpilih.filter(k => k !== id);
            } else {
                seat.classList.add("selected");
                kursiTerpilih.push(id);
            }

            document.getElementById("seat_terpilih").value = kursiTerpilih.join(",");
            document.getElementById("list_kursi").innerHTML = kursiTerpilih.join(", ");
            document.getElementById("total_harga").innerHTML =
                (kursiTerpilih.length * <?= $price ?>).toLocaleString("id-ID");
        }
    </script>
</head>
<body class="bg-light">
<div class="container mt-5">
    <h2 class="mb-3">Pilih Kursi</h2>
    <div class="alert alert-dark">
        <strong>Film ID:</strong> <?= $id_movie ?> |
        <strong>Studio:</strong> <?= $id_studio ?> |
        <strong>Tanggal:</strong> <?= $tanggal ?> |
        <strong>Jam:</strong> <?= $jam ?>
    </div>

    <div class="mb-4">
        <span class="badge bg-dark p-2">Reguler (Hitam)</span>
        <span class="badge p-2" style="background:pink;color:black;">Sweetbox (Pink)</span>
        <span class="badge bg-light text-dark p-2 border">Tidak Tersedia (Putih)</span>
        <span class="badge bg-primary p-2">Dipilih (Biru)</span>
    </div>

    <div class="card p-4">
        <!-- SWEETBOX: Baris A1-A18 (9 pasangan) -->
        <div class="text-center mb-3">
        <?php
        for ($i = 1; $i <= 18; $i += 2) {
            $s1 = "A$i";
            $s2 = "A" . ($i + 1);
            $label = "$s1|$s2"; // gabung 2 kursi

            $isBooked = in_array($s1, $bookedSeats) || in_array($s2, $bookedSeats);
            echo "<div class='seat sweetbox " . ($isBooked ? "booked" : "") . "' id='$label' " .
                 ($isBooked ? "" : "onclick='pilihKursi(\"$label\")'") . ">$label</div>";
        }
        ?>
        </div>

        <!-- KURSI REGULER B-J -->
        <?php
        $kursi_kiri = 4;
        $kursi_tengah = 15; // total 19 kursi per baris

        for ($r = 1; $r <= 9; $r++) { // B-J
            echo "<div class='mb-2 text-center'>";
            // kiri
            for ($i = 1; $i <= $kursi_kiri; $i++) {
                $seat_id = $baris_nama[$r] . $i;
                $class = in_array($seat_id, $bookedSeats) ? "booked" : "reguler";
                echo "<div class='seat $class' id='$seat_id' onclick='pilihKursi(\"$seat_id\")'>$seat_id</div>";
            }
            // lorong
            echo "<span class='lorong'></span>";
            // tengah
            for ($i = $kursi_kiri + 1; $i <= $kursi_kiri + $kursi_tengah; $i++) {
                $seat_id = $baris_nama[$r] . $i;
                $class = in_array($seat_id, $bookedSeats) ? "booked" : "reguler";
                echo "<div class='seat $class' id='$seat_id' onclick='pilihKursi(\"$seat_id\")'>$seat_id</div>";
            }
            echo "</div>";
        }
        ?>
    </div>

    <div class="card mt-4 p-3">
        <h5>Kursi Dipilih: <span id="list_kursi">-</span></h5>
        <h5>Total Harga: Rp <span id="total_harga">0</span></h5>
    </div>

    <form action="pembayaran.php" method="POST" class="mt-3">
        <input type="hidden" name="id_jadwal" value="<?= $id_jadwal ?>">
        <input type="hidden" name="seat" id="seat_terpilih">
        <button class="btn btn-primary w-100 py-2">Lanjut Pembayaran</button>
    </form>
</div>
</body>
</html>
