<?php
include "../koneksi.php";

// =======================
// GENERATE JADWAL OTOMATIS (LOGIC ASLI ANDA)
// =======================
$film_ids = range(1,7);
$studio_ids = [1 => 35000, 2 => 55000, 3 => 75000]; 
$jam_tayang = ['12:30','14:45','17:10','19:20','21:00'];
$days = 7;

for($d=0; $d<$days; $d++){
    $tanggal = date('Y-m-d', strtotime("+$d day"));
    foreach ($film_ids as $id_film) {
        foreach ($studio_ids as $id_studio => $harga) {
            foreach ($jam_tayang as $jam) {
                $waktu_tayang = "$tanggal $jam:00";
                $cek = mysqli_query($conn, "SELECT Id_jadwal FROM jadwal WHERE Id_movie='$id_film' AND Id_studio='$id_studio' AND Waktu_tayang='$waktu_tayang'");
                if(mysqli_num_rows($cek) == 0){
                    mysqli_query($conn, "INSERT INTO jadwal (Id_movie, Id_studio, Waktu_tayang, harga) VALUES ('$id_film','$id_studio','$waktu_tayang','$harga')");
                }
            }
        }
    }
}

// =======================
// HALAMAN PILIH KURSI
// =======================
$id_movie = $_GET['id'] ?? '';
$id_studio = $_GET['studio'] ?? '';
$tanggal = $_GET['date'] ?? '';
$jam = $_GET['time'] ?? '';
$qty = (int)($_GET['qty'] ?? 1);
$price = (float)($_GET['price'] ?? 0);

if (!$id_movie || !$id_studio || !$tanggal || !$jam || $price <= 0) {
    die("Data jadwal tidak lengkap atau harga tidak valid!");
}

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
    die("Jadwal tidak ditemukan di database!");
}
$id_jadwal = mysqli_fetch_assoc($q)['Id_jadwal'];

// Ambil kursi yang sudah dibooking dari tabel transaksi
$bookedQuery = mysqli_query($conn, "SELECT kursi FROM transaksi WHERE id_jadwal='$id_jadwal'");
$bookedSeats = [];
while ($b = mysqli_fetch_assoc($bookedQuery)) {
    // Karena kolom 'kursi' menyimpan multiple seats dipisahkan koma, kita pecah dan masukkan
    $seats = explode(',', $b['kursi']); 
    $bookedSeats = array_merge($bookedSeats, $seats);
}
$bookedSeats = array_map('trim', $bookedSeats);
$bookedSeats = array_filter($bookedSeats);

// Ambil detail studio untuk denah kursi
$studioDetail = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM studios WHERE Id_studio='$id_studio'"));
$total_baris = (int)($studioDetail['total_baris'] ?? 10); // Default 10
$total_kursi_per_baris = (int)($studioDetail['total_kursi_per_baris'] ?? 19); // Default 19

// Batasi penamaan baris A-Z
$baris_nama = range('A', chr(min(ord('A') + $total_baris - 1, ord('Z'))));
?>
<!DOCTYPE html>
<html>
<head>
    <title>Pilih Kursi - Onic Cinema</title>
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
        .seat {
            width: 38px;
            height: 38px;
            margin: 4px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 6px;
            font-size: 0.8rem;
            font-weight: bold;
            cursor: pointer;
            user-select: none;
            transition: transform 0.1s;
        }
        .seat:hover:not(.booked) {
            transform: scale(1.1);
        }
        .reguler { background-color: #374151; color: white; }
        .selected { background-color: #E50914 !important; color: white !important; transform: scale(1.05); }
        .booked { background-color: #9ca3af !important; color: #4b5563 !important; cursor: not-allowed; opacity: 0.7; }
        .lorong { display: inline-block; width: 35px; }
    </style>

    <script>
        let kursiTerpilih = [];
        const maxQty = <?= $qty ?>;
        const ticketPrice = <?= $price ?>;

        function pilihKursi(id) {
            let seat = document.getElementById(id);
            if (!seat || seat.classList.contains("booked")) return;

            if (seat.classList.contains("selected")) {
                // Hapus pilihan
                seat.classList.remove("selected");
                kursiTerpilih = kursiTerpilih.filter(k => k !== id);
            } else {
                // Tambah pilihan
                if (kursiTerpilih.length >= maxQty) {
                    alert(`Anda hanya dapat memilih maksimal ${maxQty} kursi.`);
                    return;
                }
                seat.classList.add("selected");
                kursiTerpilih.push(id);
            }

            document.getElementById("seat_terpilih").value = kursiTerpilih.join(",");
            document.getElementById("list_kursi").innerHTML = kursiTerpilih.join(", ");
            document.getElementById("total_harga").innerHTML =
                (kursiTerpilih.length * ticketPrice).toLocaleString("id-ID");
            
            // Aktifkan tombol bayar jika kursi sudah dipilih
            const bayarBtn = document.getElementById("lanjutBayar");
            if (kursiTerpilih.length > 0) {
                bayarBtn.disabled = false;
                bayarBtn.classList.remove('bg-gray-400', 'cursor-not-allowed', 'hover:bg-gray-400');
                bayarBtn.classList.add('bg-cinemaRed', 'hover:bg-red-700');
            } else {
                 bayarBtn.disabled = true;
                 bayarBtn.classList.add('bg-gray-400', 'cursor-not-allowed', 'hover:bg-gray-400');
                 bayarBtn.classList.remove('bg-cinemaRed', 'hover:bg-red-700');
            }
        }
    </script>
</head>
<body class="bg-gray-100 font-sans">
<div class="max-w-4xl mx-auto py-10 px-4">
    <h2 class="text-3xl font-bold text-gray-800 mb-2 flex items-center gap-2">
        <i class="ph ph-armchair text-cinemaRed"></i> Pilih Kursi Anda
    </h2>
    
    <div class="bg-white shadow-lg rounded-xl p-6 mb-6 border-l-4 border-cinemaGold">
        <p class="font-bold text-gray-800">Detail Pemesanan:</p>
        <div class="text-sm text-gray-600 mt-1 flex flex-wrap gap-x-4">
            <span>Jadwal ID: <span class="font-semibold text-cinemaRed"><?= $id_jadwal ?></span></span>
            <span>Studio: <span class="font-semibold"><?= $id_studio ?></span></span>
            <span>Waktu: <span class="font-semibold"><?= $tanggal ?> @ <?= $jam ?></span></span>
            <span>Tiket Max: <span class="font-semibold"><?= $qty ?></span></span>
        </div>
    </div>

    <!-- Legenda Kursi -->
    <div class="flex flex-wrap gap-4 text-xs font-medium text-gray-600 mb-6 p-4 bg-white rounded-xl shadow-sm border">
        <span class="flex items-center gap-2">
            <div class="seat reguler"></div> Reguler
        </span>
        <span class="flex items-center gap-2">
            <div class="seat selected" style="width:38px;"></div> Dipilih
        </span>
        <span class="flex items-center gap-2">
            <div class="seat booked"></div> Terisi
        </span>
    </div>

    <div class="bg-white rounded-xl shadow-xl p-6">
        <div class="w-full h-4 bg-gray-400 mx-auto rounded-full mb-8 shadow-inner"></div>
        <p class="text-center font-bold text-gray-700 mb-6">Layar Bioskop</p>
        
        <!-- Denah Kursi -->
        <div class="flex flex-col items-center overflow-x-auto pb-4">
            <?php
            // Asumsi denah kursi: Lorong di tengah.

            $kursi_kiri = 4; // 4 kursi di kiri lorong
            $kursi_tengah_start = $kursi_kiri + 1;
            $kursi_tengah_end = $total_kursi_per_baris;
            $total_kursi_tengah = $kursi_tengah_end - $kursi_kiri;

            for ($r = 0; $r < $total_baris; $r++) { 
                $baris_label = $baris_nama[$r];
                $kursi_loop_end = $total_kursi_per_baris;
                
                echo "<div class='mb-1.5 flex items-center'>";
                
                // Label Baris
                echo "<span class='text-gray-400 font-bold mr-2'>{$baris_label}</span>";

                // Kursi Reguler

                // Kelompok KIRI
                for ($i = 1; $i <= $kursi_kiri; $i++) {
                    $seat_id = $baris_label . $i;
                    $class = in_array($seat_id, $bookedSeats) ? "booked" : "reguler";
                    echo "<div class='seat $class' id='$seat_id' onclick='pilihKursi(\"$seat_id\")'>$seat_id</div>";
                }

                // Lorong
                echo "<span class='lorong'></span>";

                // Kelompok KANAN/TENGAH
                for ($i = $kursi_kiri + 1; $i <= $kursi_loop_end; $i++) {
                    $seat_id = $baris_label . $i;
                    $class = in_array($seat_id, $bookedSeats) ? "booked" : "reguler";
                    echo "<div class='seat $class' id='$seat_id' onclick='pilihKursi(\"$seat_id\")'>$seat_id</div>";
                }
                echo "</div>";
            }
            ?>
        </div>
        
    </div>

    <div class="bg-white shadow-lg rounded-xl p-4 mt-6">
        <h5 class="text-lg font-bold text-gray-800">Ringkasan Pilihan</h5>
        <div class="text-sm text-gray-600 mt-2">
            <p><i class="ph ph-armchair-fill mr-1 text-cinemaRed"></i> Kursi Dipilih: <span id="list_kursi" class="font-bold">-</span></p>
            <p class="mt-1 text-xl font-extrabold text-gray-900"><i class="ph ph-currency-dollar-simple text-cinemaGold mr-1"></i> Total: Rp <span id="total_harga">0</span></p>
        </div>
    </div>

    <form action="pembayaran.php" method="POST" class="mt-4">
        <input type="hidden" name="id_jadwal" value="<?= $id_jadwal ?>">
        <input type="hidden" name="seat" id="seat_terpilih">
        <input type="hidden" name="qty" value="<?= $qty ?>">
        <input type="hidden" name="price" value="<?= $price ?>">

        <button id="lanjutBayar" 
            class="w-full py-3 font-bold text-white rounded-lg shadow-lg transition duration-300
            bg-gray-400 cursor-not-allowed hover:bg-gray-400" disabled>
            Lanjut Pembayaran (Pilih Kursi Dulu)
        </button>
    </form>
</div>
</body>
</html>