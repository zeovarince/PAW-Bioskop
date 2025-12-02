<?php
session_start();
if (!isset($_SESSION['login']) || $_SESSION['role'] != '1') {
    header("Location: ../login.php");
    exit;
}

// Dummy seat (tanpa database)
$bookedSeats = [];

// Studio
$id_studio = $_GET['studio'] ?? '';
$namaStudio = [
    1 => "REGULAR 2D",
    2 => "DOLBY ATMOS",
    3 => "IMAX"
];
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

<style>
    body { margin: 0; padding: 0; }

    /* Header glass */
    .glass-nav {
        background: rgba(0, 0, 0, 1);
        backdrop-filter: blur(10px);
        box-shadow: 0 0 20px rgba(255, 215, 0, 0.1);
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
    }
    .studio-btn:hover { background:#e6c300; transform:scale(1.03); }

    /* Layar bioskop */
    .screen {
        width: 90%;
        height: 50px;
        margin: 0 auto 25px auto;
        background: linear-gradient(to bottom, #ffffff, #d9d9d9);
        border-radius: 0 0 30px 30px;
        box-shadow: 0 0 25px rgba(255,255,255,0.7);
        text-align: center;
        font-weight: bold;
        font-size: 20px;
        padding-top: 10px;
        color: #333;
        letter-spacing: 2px;
    }

    /* Kursi */
    .seat-container {
        width: 100%;
        overflow-x: auto;
    }

    .seat-grid {
        display: grid;
        grid-template-columns: repeat(70, auto);
        gap: 6px;
        justify-content: center;
        padding: 8px 0;
    }

    .seat-box {
        width: 42px;
        height: 42px;
        background: #2d3e50;
        border-radius: 6px;
        color: white;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 13px;
        font-weight: 600;
    }

    .seat-box.booked {
        background: #c0392b !important;
    }
</style>
</head>
<body class="bg-gray-100 font-sans text-gray-800">


<!-- ========================= HEADER ========================= -->
<nav class="glass-nav fixed top-0 left-0 w-full z-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between h-20">
            
            <!-- LOGO + TITLE -->
            <div class="flex items-center gap-4">
                <img src="../logo.png" class="h-12 w-auto object-contain">
                
                <h1 class="text-3xl font-bold tracking-widest uppercase">
                    <span class="text-cinemaGold glow">ONIC</span>
                    <span class="text-white glow">ADMINISTRATOR</span>
                </h1>
            </div>

            <!-- MENU -->
            <div class="hidden md:flex items-center space-x-6 text-[15px]">

                <a href="index.php"
                   class="px-4 py-2 rounded-full
                   <?= basename($_SERVER['PHP_SELF'])=='index.php' ? 'bg-cinemaGold text-black' : 'text-gray-300 hover:text-cinemaRed' ?>">
                   Dashboard
                </a>

                <a href="movies.php"
                   class="font-medium 
                   <?= basename($_SERVER['PHP_SELF'])=='movies.php' ? 'text-red-500' : 'text-gray-300 hover:text-red-500' ?>">
                   Movies
                </a>

                <a href="studio_admin.php"
                   class="px-4 py-2 rounded-full
                   <?= basename($_SERVER['PHP_SELF'])=='studio_admin.php' ? 'bg-cinemaGold text-black' : 'text-gray-300 hover:text-cinemaRed' ?>">
                   Studio
                </a>

                <a href="schedule.php"
                   class="text-gray-300 hover:text-cinemaRed">
                   Schedules
                </a>

                <a href="validation.php"
                   class="text-gray-300 hover:text-cinemaRed">
                   Validation
                </a>

                <a href="report.php"
                   class="text-gray-300 hover:text-cinemaRed">
                   Report
                </a>
                <a href="reviews.php" class="text-gray-300 hover:text-cinemaRed px-3 py-2 rounded-md text-sm font-medium transition">Reviews</a>

                <!-- ADMIN INFO -->
                <div class="flex items-center gap-3 pl-6 border-l border-gray-700">

                    <div class="leading-tight text-right">
                        <div class="font-bold text-white text-[15px]">
                            <?= $_SESSION['username'] ?? 'adminPAW' ?>
                        </div>
                        <div class="text-sm text-gray-400 -mt-1">
                            Administrator
                        </div>
                    </div>

                    <a href="../logout.php" 
                       class="text-gray-300 text-xl font-bold hover:text-cinemaRed transition">
                        ➜
                    </a>
                </div>

            </div>
        </div>
    </div>
</nav>



<!-- ========================= CONTENT ========================= -->
<div class="mt-28 p-6">

    <h1 class="text-3xl font-bold mb-6">Kelola Studio</h1>

    <!-- GRID PILIH STUDIO -->
    <div class="grid grid-cols-3 gap-6 mb-10">

        <a href="studio_admin.php?studio=1" class="studio-btn">REGULAR 2D</a>
        <a href="studio_admin.php?studio=2" class="studio-btn">DOLBY ATMOS</a>
        <a href="studio_admin.php?studio=3" class="studio-btn">IMAX</a>

    </div>

    <?php if ($id_studio): ?>
        <h2 class="text-2xl font-bold mb-4">
            Layout Studio: <?= $namaStudio[$id_studio] ?>
        </h2>

        <div class="card p-4 bg-white shadow-lg rounded-xl">

            <!-- ===================== LAYAR ===================== -->
            <div class="screen">Layar Bioskop</div>

            <!-- ===================== KURSI A–J (51–120) ===================== -->
            <div class="seat-container">
                <?php
                    $rows = range("A", "J");
                    foreach ($rows as $r):
                ?>
                    <div class="seat-grid">
                        <?php
                            for ($i = 51; $i <= 120; $i++):
                                $sid = $r . $i;
                                $cls = "seat-box";
                                if (in_array($sid, $bookedSeats)) $cls .= " booked";
                        ?>
                            <div class="<?= $cls ?>"><?= $sid ?></div>
                        <?php endfor; ?>
                    </div>
                <?php endforeach; ?>
            </div>

        </div>
    <?php endif; ?>

</div>

</body>
</html>
