<?php
session_start();
if (!isset($_SESSION['login']) || $_SESSION['role'] != '1') {
    header("Location: ../login.php");
    exit;
}

include "../koneksi.php";

$id_studio = $_GET['studio'] ?? '';
$studioListQuery = mysqli_query($conn, "SELECT * FROM studios ORDER BY Id_studio");
$studios = [];
while ($s = mysqli_fetch_assoc($studioListQuery)) {
    $studios[] = $s;
}

if ($id_studio) {
    $studioQuery = mysqli_query($conn, "SELECT * FROM studios WHERE Id_studio='$id_studio'");
    if ($studioQuery && mysqli_num_rows($studioQuery) > 0) {
        $studio = mysqli_fetch_assoc($studioQuery);
        $total_baris = (int)$studio['total_baris'];
        $total_kursi_per_baris = (int)$studio['total_kursi_per_baris'];
        
        // Batasi penamaan baris, misalnya sampai Z (26 baris)
        $baris_nama = range('A', chr(min(ord('A') + $total_baris - 1, ord('Z'))));
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
    .glass-nav {
        background: rgba(20, 20, 20, 0.9);
        backdrop-filter: blur(10px);
    }
    .seat { 
        width: 32px; 
        height: 32px; 
        margin: 3px; 
        display: inline-flex; 
        align-items: center; 
        justify-content: center; 
        border-radius: 6px; 
        font-size: 0.75rem; /* text-xs */
        font-weight: bold; 
        cursor: pointer; 
        transition: transform 0.1s, box-shadow 0.1s;
    }
    .seat:hover:not(.sold):not(.reserved) {
        transform: scale(1.1);
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.2);
    }

    /* Statuses */
    .reguler { background-color: #374151; color: white; } /* gray-700 */
    /* .sweetbox dihapus */
    .reserved { background-color: #f59e0b; color: white; } /* amber-500 */
    .sold { background-color: #dc2626; color: white; cursor: not-allowed; opacity: 0.8; } /* red-600 */
    .lorong { display: inline-block; width: 30px; }
    .screen {
        background: #141414;
        color: white;
        padding: 10px;
        border-radius: 8px;
        box-shadow: 0 0 15px rgba(0,0,0,0.5);
    }
</style>
</head>
<body class="bg-gray-100 font-sans">

<!-- Navigasi Admin -->
<nav class="glass-nav fixed w-full z-50 bg-cinemaBlack">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between h-20">
            <div class="flex items-center gap-4">
                <img src="../logo.png" alt="Onic Logo" class="h-11 w-auto object-contain drop-shadow-lg">
                <h1 class="text-3xl font-bold text-cinemaGold tracking-widest uppercase" style="text-shadow: 0px 0px 7px;">
                    ONIC <span class="text-white">ADMINISTRATOR</span>
                </h1>
            </div>

            <div class="hidden md:block">
                <div class="ml-10 flex items-baseline space-x-4">
                    <a href="index.php" class="text-gray-300 hover:text-cinemaRed px-3 py-2 rounded-md text-sm font-medium transition">Dashboard</a>
                    <a href="movies.php" class="text-gray-300 hover:text-cinemaRed px-3 py-2 rounded-md text-sm font-medium transition">Movies</a>
                    <a href="schedule.php" class="text-gray-300 hover:text-cinemaRed px-3 py-2 rounded-md text-sm font-medium transition">Schedules</a>
                    <a href="manage_studio.php" class="bg-cinemaGold text-black px-3 py-2 text-l rounded-full text-sm font-medium transition">Studios</a>
                    <a href="validation.php" class="text-gray-300 hover:text-cinemaRed px-3 py-2 rounded-md text-sm font-medium transition">Validation</a>
                    <a href="report.php" class="text-gray-300 hover:text-cinemaRed px-3 py-2 rounded-md text-sm font-medium transition">Report</a>
                </div>
            </div>

            <div class="flex items-center gap-4">
                <span class="text-white text-sm font-bold hidden md:block"><?= isset($_SESSION['username']) ? $_SESSION['username'] : 'Admin' ?></span>
                <a href="../logout.php" class="text-gray-400 hover:text-white transition" title="Logout">
                    <i class="ph ph-sign-out text-2xl"></i>
                </a>
            </div>
        </div>
    </div>
</nav>

<!-- Main Content -->
<main class="pt-24 pb-12 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <h2 class="text-3xl font-bold text-gray-800 mb-6 flex items-center gap-2">
        <i class="ph ph-projector-screen text-cinemaRed"></i> Visualisasi Denah Studio
    </h2>

    <div class="flex flex-wrap items-center gap-3 mb-6 bg-white p-4 rounded-xl shadow-md border">
        <strong class="text-sm text-gray-700">Pilih Studio:</strong>
        <?php foreach ($studios as $s): ?>
            <a href="?studio=<?= $s['Id_studio'] ?>" 
               class="px-3 py-1.5 rounded-full text-sm font-semibold transition 
               <?= ($id_studio == $s['Id_studio']) ? 'bg-cinemaRed text-white shadow-lg' : 'bg-gray-200 text-gray-700 hover:bg-gray-300' ?>">
                <?= $s['nama_studio'] ?> (<?= $s['capacity'] ?> Kursi)
            </a>
        <?php endforeach; ?>
        <a href="manage_studio.php" class="ml-auto text-sm font-medium text-gray-500 hover:text-cinemaRed">
            <i class="ph ph-wrench text-lg mr-1"></i> Kelola Studio
        </a>
    </div>

    <?php if (!$id_studio): ?>
        <div class="bg-yellow-100 text-yellow-700 p-4 rounded-lg border border-yellow-200 mt-8">
            <i class="ph ph-info-fill mr-2"></i> Silakan pilih salah satu studio di atas untuk melihat denah kursi.
        </div>
    <?php else: ?>
        <h4 class="text-xl font-semibold mb-6 mt-8">Denah Studio: <?= $studio['nama_studio'] ?></h4>
        
        <!-- Legenda Status Kursi -->
        <div class="flex flex-wrap gap-4 text-xs font-medium text-gray-600 mb-6 p-4 bg-white rounded-xl shadow-sm border">
            <span class="flex items-center gap-2">
                <div class="seat reguler"></div> Reguler
            </span>
            <span class="flex items-center gap-2">
                <div class="seat reserved"></div> Reserved (Admin Lock)
            </span>
            <span class="flex items-center gap-2">
                <div class="seat sold"></div> Sold (Terjual)
            </span>
        </div>

        <!-- Layout Kursi -->
        <div class="bg-white p-6 rounded-xl shadow-xl overflow-x-auto">
            <div class="text-center mb-6 py-2 screen w-full mx-auto max-w-sm">LAYAR BIOSKOP</div>
            
            <div class="flex flex-col items-center">
                <?php
                // Logika kursi - HANYA REGULER DENGAN LORONG
                
                $kursi_kiri = 4; // Asumsi 4 kursi di kiri
                $kursi_tengah_start = $kursi_kiri + 1;
                $kursi_tengah_end = $total_kursi_per_baris;
                
                for ($r = 0; $r < $total_baris; $r++) {
                    $baris_label = $baris_nama[$r];
                    
                    echo "<div class='mb-2 text-center flex items-center'>";
                    echo "<span class='text-gray-400 font-bold mr-2'>{$baris_label}</span>"; // Label Baris

                    // Kelompok KIRI
                    for ($i = 1; $i <= $kursi_kiri; $i++) {
                        $seat_id = $baris_label.$i;
                        // Default class 'reguler'. Status sebenarnya harusnya dicek ke DB.
                        echo "<div class='seat reguler inline-block' data-seat='$seat_id'>$seat_id</div>";
                    }

                    // Lorong
                    echo "<span class='lorong'></span>";

                    // Kelompok KANAN/TENGAH
                    for ($i = $kursi_tengah_start; $i <= $kursi_tengah_end; $i++) {
                        $seat_id = $baris_label.$i;
                        echo "<div class='seat reguler inline-block' data-seat='$seat_id'>$seat_id</div>";
                    }
                    echo "</div>";
                }
                ?>
            </div>
        </div>
    <?php endif; ?>
</main>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
// Fungsi jQuery tetap digunakan untuk interaksi prompt (walaupun alert/prompt tidak disarankan di production)
$(document).ready(function(){
    // Hanya tambahkan event listener jika ada studio yang dipilih
    if ('<?= $id_studio ?>' !== '') {
        $('.seat').click(function(){
            let seatDiv = $(this);
            let seatId = seatDiv.data('seat');
            
            // Dapatkan status saat ini untuk pre-fill prompt
            let currentStatus = 'reguler';
            if (seatDiv.hasClass('reserved')) currentStatus = 'reserved';
            else if (seatDiv.hasClass('sold')) currentStatus = 'sold';
            
            // Sweetbox dihapus, jadi opsi di sini hanya reguler, reserved, sold
            let newStatus = prompt(`Ubah status kursi ${seatId} di Studio <?= $studio['nama_studio'] ?> menjadi: (reguler, reserved, sold, empty)`, currentStatus);
            
            if(!newStatus || newStatus === currentStatus) return; 
            
            newStatus = newStatus.toLowerCase().trim();
            if(newStatus === 'empty') newStatus = 'reguler'; 
            
            // Simulasikan Sukses:
            // Hapus kelas sweetbox (jika ada, walau seharusnya tidak ada lagi)
            seatDiv.removeClass('reguler sweetbox reserved sold').addClass(newStatus);
            alert('Status kursi '+seatId+' berhasil diupdate menjadi '+newStatus + ' (Simulasi)');
            
            // KODE ASLI JIKA update_seat.php SUDAH DIBUAT (Sweetbox dihapus dari opsi)
            // $.post('update_seat.php',{
            //     studio_id: '<?= $id_studio ?>',
            //     seat: seatId,
            //     status: newStatus 
            // },function(resp){
            //     if(resp.trim() === 'success'){
            //         seatDiv.removeClass('reguler reserved sold').addClass(newStatus);
            //         alert('Status kursi '+seatId+' berhasil diupdate menjadi '+newStatus);
            //     }else{
            //         alert('Gagal update seat: '+resp);
            //     }
            // });
        });
    }
});
</script>

</body>
</html>