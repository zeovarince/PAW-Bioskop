<?php
session_start();
if (!isset($_SESSION['login']) || $_SESSION['role'] != '1') {
    header("Location: ../login.php");
    exit;
}

// Dummy seat (tanpa database) - Nanti bisa diganti database
$bookedSeats = [];

// Baris kursi
$baris_nama = ["","B","C","D","E","F","G","H","I","J"];

// Studio
$id_studio = $_GET['studio'] ?? '';
$namaStudio = [
    1 => "REGULAR 2D",
    2 => "DOLBY ATMOS",
    3 => "IMAX"
];

// Hitung Total Pending (Untuk Badge Navbar)
$total_pending = 0; 
if (file_exists("../koneksi.php")) {
    include "../koneksi.php";
    $pending = mysqli_query($conn, "SELECT COUNT(*) as total FROM booking WHERE status_booking = '2'");
    if ($pending) {
        $total_pending = mysqli_fetch_assoc($pending)['total'];
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
<!-- Phosphor Icons -->
<script src="https://unpkg.com/@phosphor-icons/web"></script>

<style>
    body { margin: 0; padding: 0; }

    /* kursi */
    .seat { 
        width:50px;height:50px;margin:4px;display:inline-flex;
        align-items:center;justify-content:center;border-radius:8px;
        font-weight:bold;cursor:pointer; 
    }
    .reguler { background:#222; color:white; }
    .sweetbox { background:#ff69b4; color:black; width:110px; }
    .booked { background:red !important; color:white !important; cursor:not-allowed; }
    .lorong { display:inline-block;width:50px; }

    /* Header glass */
    .glass-nav {
        background: rgba(20, 20, 20, 0.9);
        backdrop-filter: blur(10px);
    }

    /* Glow animasi */
    @keyframes glow {
        0% { text-shadow: 0 0 8px rgba(255,255,255,0.7); }
        50% { text-shadow: 0 0 16px rgba(255,255,255,1); }
        100% { text-shadow: 0 0 8px rgba(255,255,255,0.7); }
    }
    .glow {
        animation: glow 2.2s infinite ease-in-out;
    }

    /* tombol studio */
    .studio-btn {
        padding:1.4rem; border-radius:1rem; background:#FFD700;
        font-weight:bold; text-align:center; transition:0.2s; font-size:1.2rem;
        display: flex; align-items: center; justify-content: center; gap: 12px;
        color: black;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
    }
    .studio-btn:hover { background:#e6c300; transform:scale(1.02); }

    /* LAYAR BIOSKOP */
    .screen {
        width: 90%;
        height: 50px;
        margin: 0 auto 40px auto;
        background: linear-gradient(to bottom, #ffffff, #d9d9d9);
        border-radius: 0 0 50px 50px;
        box-shadow: 0 10px 30px rgba(255,255,255,0.5);
        text-align: center;
        font-weight: bold;
        font-size: 14px;
        padding-top: 10px;
        color: #999;
        letter-spacing: 5px;
        transform: perspective(500px) rotateX(-5deg);
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
                </div>
            </div>

            <!-- Menu Kanan (Profile) -->
            <div class="flex items-center gap-4">
                <div class="hidden md:flex flex-col items-end">
                    <span class="text-white text-sm font-bold"><?= isset($_SESSION['username']) ? $_SESSION['username'] : 'Admin' ?></span>
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
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-10">
        <a href="studio_admin.php?studio=1" class="studio-btn <?= $id_studio == 1 ? 'ring-4 ring-cinemaRed' : '' ?>">
            <i class="ph ph-film-strip text-2xl"></i> 
            <span>REGULAR 2D</span>
        </a>
        <a href="studio_admin.php?studio=2" class="studio-btn <?= $id_studio == 2 ? 'ring-4 ring-cinemaRed' : '' ?>">
            <i class="ph ph-speaker-high text-2xl"></i> 
            <span>DOLBY ATMOS</span>
        </a>
        <a href="studio_admin.php?studio=3" class="studio-btn <?= $id_studio == 3 ? 'ring-4 ring-cinemaRed' : '' ?>">
            <i class="ph ph-monitor-play text-2xl"></i> 
            <span>IMAX</span>
        </a>
    </div>

    <?php if ($id_studio): ?>
        <div class="bg-white p-8 rounded-2xl shadow-xl border border-gray-200">
            
            <div class="flex justify-between items-center mb-8 pb-4 border-b border-gray-100">
                <h2 class="text-2xl font-bold text-gray-800">
                    Layout: <span class="text-cinemaRed"><?= $namaStudio[$id_studio] ?></span>
                </h2>
                <div class="flex gap-4 text-sm">
                    <span class="flex items-center gap-1"><div class="w-4 h-4 bg-[#222] rounded"></div> Reguler</span>
                    <span class="flex items-center gap-1"><div class="w-4 h-4 bg-[#ff69b4] rounded"></div> Sweetbox</span>
                </div>
            </div>

            <div class="screen">
                LAYAR BIOSKOP
            </div>
            <div class="flex flex-col items-center">
                <div class="mb-6 flex gap-2 justify-center">
                <?php
                for ($i = 1; $i <= 18; $i += 2) {
                    $s1 = "A$i";
                    $s2 = "A" . ($i + 1);
                    $label = "$s1|$s2";
                    echo "<div class='seat sweetbox' title='Sweetbox $label'>$label</div>";
                }
                ?>
                </div>
                <?php
                $kursi_kiri = 4;
                for ($r = 1; $r <= 9; $r++) {
                    echo "<div class='mb-2 flex justify-center'>";
                    for ($i = 1; $i <= $kursi_kiri; $i++) {
                        $sid = $baris_nama[$r] . $i;
                        echo "<div class='seat reguler'>$sid</div>";
                    }
                    echo "<span class='lorong'></span>";
                    for ($i = 5; $i <= 19; $i++) {
                        $sid = $baris_nama[$r] . $i;
                        echo "<div class='seat reguler'>$sid</div>";
                    }

                    echo "</div>";
                }
                ?>
            </div>

            <div class="mt-8 text-center">
                <p class="text-gray-400 text-sm italic">* Tampilan ini adalah preview layout kursi di sisi user.</p>
            </div>

        </div>
    <?php else: ?>
        <!-- State Belum Pilih Studio -->
        <div class="bg-white rounded-2xl shadow-sm border-2 border-dashed border-gray-300 p-12 text-center">
            <i class="ph ph-armchair text-6xl text-gray-300 mb-4"></i>
            <h3 class="text-xl font-bold text-gray-400">Pilih Studio Terlebih Dahulu</h3>
            <p class="text-gray-500 mt-2">Klik salah satu tombol studio di atas untuk melihat layout kursi.</p>
        </div>
    <?php endif; ?>

</div>

</body>
</html>