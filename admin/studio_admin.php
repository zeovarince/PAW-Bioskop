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
        $baris_nama = range('A', chr(ord('A') + $total_baris - 1));
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
<script src="https://cdn.tailwindcss.com"></script>
<script src="https://unpkg.com/@phosphor-icons/web"></script>
<style>
    .seat { width:50px;height:50px;margin:4px;display:inline-flex;align-items:center;justify-content:center;border-radius:8px;font-weight:bold;cursor:pointer; }
    .reguler { background-color:#111;color:white; }
    .sweetbox { background-color:pink;color:black;width:110px; }
    .reserved { background-color:orange;color:white; }
    .sold { background-color:red;color:white; }
    .lorong { display:inline-block;width:50px; }
    .header { height:80px; background-color:#1f1f1f; display:flex; align-items:center; justify-content:space-between; padding:0 2rem; color:white; font-weight:bold; position:fixed; top:0; width:100%; z-index:50; }
    .header-left { display:flex; align-items:center; gap:2rem; }
    .header-left img { height:50px; }
    .nav-link { margin-right:1rem; padding:0.5rem 1rem; border-radius:0.5rem; transition:all 0.2s; }
    .nav-link:hover { background-color:#E50914; color:white; }
    .header-right { display:flex; align-items:center; gap:1rem; }
</style>
</head>
<body class="bg-gray-50">

<!-- Header -->
<header class="header">
    <div class="header-left">
        <img src="../logo.png" alt="Logo">
        <a href="index.php" class="nav-link">Dashboard</a>
        <a href="movies.php" class="nav-link">Movies</a>
        <a href="studio_admin.php" class="nav-link bg-cinemaGold text-black">Studio</a>
        <a href="schedule.php" class="nav-link">Schedules</a>
        <a href="validation.php" class="nav-link">Validation</a>
        <a href="report.php" class="nav-link">Report</a>
        <button onclick="alert('Tampilkan semua kursi');" class="nav-link bg-green-500 text-white hover:bg-green-600">Lihat Kursi</button>
    </div>
    <div class="header-right">
        <span><?= isset($_SESSION['username']) ? $_SESSION['username'] : 'Admin' ?></span>
        <a href="../logout.php" class="text-white hover:text-red-500"><i class="ph ph-sign-out text-2xl"></i></a>
    </div>
</header>

<!-- Main Content -->
<main class="pt-[100px] px-8">
    <h2 class="text-3xl font-bold mb-6">Admin Studio</h2>

    <div class="mb-4">
        <strong>Pilih Studio:</strong>
        <?php foreach ($studios as $s): ?>
            <a href="?studio=<?= $s['Id_studio'] ?>" class="inline-block bg-cinemaGold text-black px-3 py-1 rounded-md mb-1 mr-2">
                <?= $s['nama_studio'] ?>
            </a>
        <?php endforeach; ?>
    </div>

    <?php if (!$id_studio): ?>
        <div class="bg-red-100 text-red-700 p-4 rounded-lg">Studio tidak dipilih!</div>
    <?php else: ?>
        <h4 class="text-xl font-semibold mb-4">Layout Studio: <?= $studio['nama_studio'] ?></h4>
        <div class="bg-white p-6 rounded-xl shadow-xl">

            <?php
            if ($baris_nama[0] == 'A') {
                echo "<div class='text-center mb-3'>";
                for ($i = 1; $i <= 18; $i += 2) {
                    $s1 = "A$i"; $s2 = "A" . ($i+1);
                    $label = "$s1|$s2";
                    echo "<div class='seat sweetbox inline-block' data-seat='$label'>$label</div>";
                }
                echo "</div>";
            }

            $kursi_kiri = 4; $kursi_tengah = 15;
            for ($r = 1; $r < $total_baris; $r++) {
                echo "<div class='mb-2 text-center'>";
                for ($i = 1; $i <= $kursi_kiri; $i++) {
                    $seat_id = $baris_nama[$r].$i;
                    echo "<div class='seat reguler inline-block' data-seat='$seat_id'>$seat_id</div>";
                }
                echo "<span class='lorong'></span>";
                for ($i = $kursi_kiri+1; $i <= $kursi_kiri+$kursi_tengah; $i++) {
                    $seat_id = $baris_nama[$r].$i;
                    echo "<div class='seat reguler inline-block' data-seat='$seat_id'>$seat_id</div>";
                }
                echo "</div>";
            }
            ?>
        </div>
    <?php endif; ?>
</main>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
$(document).ready(function(){
    $('.seat').click(function(){
        let seatId = $(this).data('seat');
        let status = prompt("Ubah status kursi "+seatId+" menjadi: (reguler/sweetbox/reserved/sold/empty)");
        if(!status) return;
        if(status==='empty') status='reguler';
        let seatDiv = $(this);
        $.post('update_seat.php',{seat:seatId,status:status},function(resp){
            if(resp==='success'){
                seatDiv.removeClass('reguler sweetbox reserved sold').addClass(status);
                alert('Status kursi '+seatId+' berhasil diupdate menjadi '+status);
            }else{
                alert('Gagal update seat: '+resp);
            }
        });
    });
});
</script>

</body>
</html>
