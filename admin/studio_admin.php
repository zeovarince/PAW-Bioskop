<?php
session_start();
if (!isset($_SESSION['login']) || $_SESSION['role'] != '1') {
    header("Location: ../login.php");
    exit;
}

include "../koneksi.php";
include "../function.php"; 

$id_studio = $_GET['studio'] ?? '';
$studio_data = [];
$studios = getStudios(); // Menggunakan fungsi dari function.php
$total_pending = 0; 

// Hitung Total Pending (Untuk Badge Navbar)
$q_badge = mysqli_query($conn, "SELECT COUNT(*) as total FROM booking WHERE status_booking = '2'");
if ($q_badge) {
    $total_pending = mysqli_fetch_assoc($q_badge)['total'];
}

if ($id_studio) {
    $studio_data = getStudios($id_studio);
    
    if ($studio_data) {
        $total_baris = (int)$studio_data['total_baris'];
        $total_kursi_per_baris = (int)$studio_data['total_kursi_per_baris'];
        $baris_nama = range('A', chr(ord('A') + $total_baris - 1));
        
        // DUMMY: Dalam aplikasi nyata, kursi yang SOLD/RESERVED akan diambil dari tabel TRANSAKSI.
        // Di sini kita biarkan kosong agar admin bisa simulasikan status kursinya.
        $bookedSeats = []; 
    } else {
        $id_studio = '';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Admin Studio - Onic Cinema</title>

<link rel="icon" href="../logo.png">
<script src="https://cdn.tailwindcss.com"></script>
<!-- Phosphor Icons -->
<script src="https://unpkg.com/@phosphor-icons/web"></script>

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
    body { margin: 0; padding: 0; }
    /* Style Kursi */
    .seat { 
        width: 32px; height: 32px; margin: 3px; 
        display: inline-flex; align-items: center; justify-content: center; 
        border-radius: 6px; font-size: 0.75rem; font-weight: bold; 
        cursor: pointer; transition: transform 0.1s, box-shadow 0.1s;
    }
    .seat:hover { transform: scale(1.1); box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.2); }
    .reguler { background-color: #374151; color: white; }
    .reserved { background: #f59e0b; color: white; }
    .sold { background: #dc2626; color: white; cursor: not-allowed; opacity: 0.8; }
    .lorong { display: inline-block; width: 30px; }
    
    /* Navigasi */
    .glass-nav {
        background: rgba(20, 20, 20, 0.9);
        backdrop-filter: blur(10px);
    }
    
    /* LAYAR BIOSKOP */
    .screen {
        width: 90%;
        max-width: 600px;
        height: 30px;
        margin: 0 auto 40px auto;
        background: #374151;
        border-radius: 0 0 15px 15px;
        box-shadow: 0 10px 20px rgba(0,0,0,0.3);
        text-align: center;
        font-weight: bold;
        font-size: 14px;
        padding-top: 5px;
        color: #9ca3af;
        letter-spacing: 2px;
        transform: perspective(300px) rotateX(-5deg);
    }
</style>
</head>
<body class="bg-gray-100 font-sans text-gray-800">


<!-- ========================= NAVBAR ADMIN ========================= -->
<nav class="glass-nav fixed w-full z-50 bg-cinemaBlack border-b border-gray-800">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between h-20">
            
            <!-- Logo -->
            <div class="flex items-center gap-4">
                <img src="../logo.png" alt="Onic Logo" class="h-11 w-auto object-contain drop-shadow-lg">
                
                <h1 class="text-3xl font-bold text-cinemaGold tracking-widest uppercase" style="text-shadow: 0px 0px 7px;">
                    ONIC <span class="text-white">ADMINISTRATOR</span>
                </h1>
            </div>

            <!-- Menu Tengah -->
            <div class="hidden md:block">
                <div class="ml-10 flex items-baseline space-x-4">
                    
                    <a href="index.php" class="text-gray-300 hover:text-cinemaRed px-3 py-2 rounded-md text-sm font-medium transition">Dashboard</a>
                    <a href="movies.php" class="text-gray-300 hover:text-cinemaRed px-3 py-2 rounded-md text-sm font-medium transition">Movies</a>
                    <a href="studio_admin.php" class="bg-cinemaGold text-black px-3 py-2 rounded-full text-sm font-bold transition shadow-lg">Studio</a>
                    <a href="schedule.php" class="text-gray-300 hover:text-cinemaRed px-3 py-2 rounded-md text-sm font-medium transition">Schedules</a>
                    <a href="validation.php" class="text-gray-300 hover:text-cinemaRed px-3 py-2 rounded-md text-sm font-medium transition flex items-center gap-1">
                        Validation
                        <?php if($total_pending > 0): ?>
                            <span class="ml-1 bg-red-600 text-white text-xs px-1.5 py-0.5 rounded-full animate-pulse"><?= $total_pending ?></span>
                        <?php endif; ?>
                    </a>
                    <a href="report.php" class="text-gray-300 hover:text-cinemaRed px-3 py-2 rounded-md text-sm font-medium transition">Report</a>
                    <a href="reviews.php" class="text-gray-300 hover:text-cinemaRed px-3 py-2 rounded-md text-sm font-medium transition">Reviews</a>
                </div>
            </div>

            <!-- Menu Kanan (Profile) -->
            <div class="flex items-center gap-4">
                <div class="hidden md:flex flex-col items-end">
                    <span class="text-white text-sm font-bold"><?= $_SESSION['username'] ?? 'Admin' ?></span>
                    <span class="text-gray-400 text-xs">Administrator</span>
                </div>
                <a href="../logout.php" onclick="return confirm('Yakin ingin logout?')" class="text-gray-400 hover:text-white transition" title="Logout">
                    <i class="ph ph-sign-out text-2xl"></i>
                </a>
            </div>
        </div>
    </div>
</nav>


<!-- ========================= CONTENT ========================= -->
<div class="pt-28 pb-12 px-4 sm:px-6 lg:px-8 max-w-7xl mx-auto">

    <div class="flex justify-between items-center mb-8">
        <div>
            <h1 class="text-3xl font-bold text-gray-900 flex items-center gap-2">
                <i class="ph ph-armchair text-cinemaRed"></i> Kelola Layout Studio
            </h1>
            <p class="text-gray-500 mt-1">Lihat dan atur konfigurasi kursi untuk setiap studio.</p>
        </div>
        <a href="manage_studio.php" class="text-sm font-bold text-blue-600 hover:text-blue-800 transition flex items-center gap-1">
            <i class="ph ph-pencil-simple"></i> Edit Konfigurasi Studio
        </a>
    </div>

    <!-- GRID PILIH STUDIO (Dinamic Fetch) -->
    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-6 mb-10">
        <?php if (!empty($studios)): ?>
            <?php foreach ($studios as $s): ?>
                 <a href="studio_admin.php?studio=<?= $s['Id_studio'] ?>" class="studio-btn bg-cinemaGold text-black px-4 py-3 rounded-xl font-bold transition shadow-md <?= $id_studio == $s['Id_studio'] ? 'ring-4 ring-cinemaRed ring-offset-2' : '' ?>">
                    <i class="ph ph-monitor-play text-xl"></i> 
                    <span><?= $s['nama_studio'] ?> (<?= $s['capacity'] ?> Kursi)</span>
                </a>
            <?php endforeach; ?>
        <?php else: ?>
             <div class="col-span-3 bg-yellow-100 p-4 rounded-lg text-yellow-700">
                <i class="ph ph-warning-circle mr-2"></i> Belum ada studio terdaftar. Silakan tambahkan di halaman Kelola Studio.
            </div>
        <?php endif; ?>
    </div>

    <?php if ($id_studio && !empty($studio_data)): ?>
        <div class="bg-white p-8 rounded-2xl shadow-xl border border-gray-200">
            
            <div class="flex justify-between items-center mb-8 pb-4 border-b border-gray-100">
                <h2 class="text-2xl font-bold text-gray-800">
                    Layout: <span class="text-cinemaRed"><?= $studio_data['nama_studio'] ?></span>
                </h2>
                <p class="text-gray-500 text-sm"><?= $total_baris ?> Baris x <?= $total_kursi_per_baris ?> Kursi</p>
            </div>

            <div class="screen">
                LAYAR BIOSKOP
            </div>
            
            <!-- Denah Kursi Dinamis -->
            <div class="flex flex-col items-center overflow-x-auto">
                <div class="inline-block"> <!-- Memastikan container memiliki lebar yang sesuai -->
                    <?php
                    $kursi_kiri = 4; // Asumsi 4 kursi di kiri lorong
                    
                    for ($r = 0; $r < $total_baris; $r++) {
                        $baris_label = $baris_nama[$r];
                        
                        echo "<div class='mb-2 flex items-center justify-center'>";
                        echo "<span class='text-gray-400 font-bold mr-2 w-4 text-right'>{$baris_label}</span>"; // Label Baris

                        // Kelompok KIRI
                        for ($i = 1; $i <= $kursi_kiri; $i++) {
                            $seat_id = $baris_label.$i;
                            // Default class 'reguler'. Interaksi di JS untuk simulasi status.
                            echo "<div class='seat reguler inline-block' data-seat='$seat_id' onclick='handleSeatClick(\"$seat_id\")'>$seat_id</div>";
                        }

                        // Lorong
                        echo "<span class='lorong'></span>";

                        // Kelompok KANAN/TENGAH
                        for ($i = $kursi_kiri + 1; $i <= $total_kursi_per_baris; $i++) {
                            $seat_id = $baris_label.$i;
                            echo "<div class='seat reguler inline-block' data-seat='$seat_id' onclick='handleSeatClick(\"$seat_id\")'>$seat_id</div>";
                        }
                        echo "</div>";
                    }
                    ?>
                </div>
            </div>

            <div class="mt-8 text-center">
                <p class="text-gray-400 text-sm italic">* Klik kursi untuk simulasi perubahan status (Reguler → Reserved → Sold).</p>
            </div>

        </div>
    <?php else: ?>
        <!-- State Belum Pilih Studio -->
        <div class="bg-white rounded-2xl shadow-sm border-2 border-dashed border-gray-300 p-12 text-center">
            <i class="ph ph-armchair text-6xl text-gray-300 mb-4"></i>
            <h3 class="text-xl font-bold text-gray-500">Pilih Studio Terlebih Dahulu</h3>
            <p class="text-gray-500 mt-2">Klik salah satu tombol studio di atas untuk melihat layout kursi.</p>
        </div>
    <?php endif; ?>

</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
// Fungsi simulasi klik kursi (diperbaiki agar lebih sederhana: Reguler -> Reserved -> Sold -> Reguler)
function handleSeatClick(seatId) {
    const seatDiv = $(`[data-seat="${seatId}"]`);
    
    // Dapatkan status saat ini
    let currentClass = 'reguler';
    if (seatDiv.hasClass('reserved')) {
        currentClass = 'reserved';
    } else if (seatDiv.hasClass('sold')) {
        currentClass = 'sold';
    }

    let newStatus = 'reguler';
    if (currentClass === 'reguler') {
        newStatus = 'reserved';
    } else if (currentClass === 'reserved') {
        newStatus = 'sold';
    }
    
    // Perbarui visual
    seatDiv.removeClass('reguler reserved sold').addClass(newStatus);
    
    console.log(`Simulasi: Kursi ${seatId} diubah menjadi status: ${newStatus}`);

    // Catatan: Komunikasi ke update_seat.php (untuk menyimpan status) dihilangkan karena file tersebut belum ada.
}
</script>

</body>
</html>