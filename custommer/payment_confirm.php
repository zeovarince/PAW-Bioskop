<?php
session_start();
if (!isset($_SESSION['login'])) {
    header("Location: login.php"); // Mengganti ke ../login.php jika login.php ada di root
    exit;
}

include "../koneksi.php";

// Ambil ID booking dari URL
$id = $_GET['id_booking'] ?? null;

if (!$id) {
    // Jika tidak ada ID, redirect ke dashboard customer
    header("Location: dashboard.php");
    exit;
}

// Ambil data booking
$booking = mysqli_query($conn, "
    SELECT 
        b.*, 
        m.judul, 
        s.nama_studio,
        j.Waktu_tayang,
        GROUP_CONCAT(db.no_kursi ORDER BY db.no_kursi ASC) as kursi_list
    FROM booking b
    JOIN jadwal j ON b.Id_jadwal = j.Id_jadwal
    JOIN movies m ON j.Id_movie = m.Id_movie
    JOIN studios s ON j.Id_studio = s.Id_studio
    JOIN detail_booking db ON b.Id_booking = db.Id_booking
    WHERE b.Id_booking = '".mysqli_real_escape_string($conn, $id)."' AND b.Id_user = '".mysqli_real_escape_string($conn, $_SESSION['user_id'])."'
    GROUP BY b.Id_booking
");
$data = mysqli_fetch_assoc($booking);


// Jika booking tidak ditemukan atau bukan milik user ini
if (!$data) {
    echo "Booking tidak ditemukan atau Anda tidak memiliki akses!";
    exit;
}

// Generate kode pembayaran (LOGIC INI SUDAH DIKONEKSIKAN KE TABEL BOOKING DI PEMBAYARAN.PHP)
$code_booking = $data['code_booking'];
$total_harga = $data['total_harga'];
$status = $data['status_booking'];
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Pembayaran - Onic Cinema</title>
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
</head>

<body class="bg-gray-100 text-gray-800 font-sans min-h-screen">

    <!-- Navigasi Customer (Sama seperti index.php customer) -->
    <nav class="bg-cinemaBlack border-b border-gray-800 py-4">
        <div class="max-w-7xl mx-auto px-6 flex justify-between items-center">
            <div class="flex items-center gap-4">
                <img src="../logo.png" alt="Onic Logo" class="h-11 w-auto object-contain drop-shadow-lg">
                <h1 class="text-3xl font-bold text-cinemaGold tracking-widest uppercase" style="text-shadow: 0px 0px 7px;">
                    ONIC <span class="text-white">CINEMA</span>
                </h1>
            </div>
            <div class="flex items-center gap-4">
                 <a href="dashboard.php" class="text-gray-300 hover:text-cinemaGold px-3 py-2 rounded-md text-sm font-medium transition">Dashboard</a>
                 <div class="text-right">
                    <p class="text-sm font-bold text-white">Halo, <?= $_SESSION['username'] ?></p>
                    <p class="text-xs text-cinemaGold">Member</p>
                </div>
                <a href="../logout.php" class="bg-gray-800 hover:bg-cinemaRed text-white p-2 rounded-full transition" title="Logout">
                    <i class="ph ph-sign-out text-xl"></i>
                </a>
            </div>
        </div>
    </nav>
    <!-- End Navigasi -->

    <div class="p-8 pt-12">
        <div class="max-w-xl mx-auto bg-white p-8 rounded-xl shadow-2xl border-t-4 border-cinemaRed">

            <div class="flex justify-between items-start mb-6 border-b border-gray-200 pb-4">
                 <h2 class="text-3xl font-bold text-gray-800 flex items-center">
                    <i class="ph ph-receipt-text mr-3 text-cinemaRed"></i> Pembayaran Tiket
                </h2>
                <div class="text-right">
                    <p class="text-gray-500 text-sm">Total Tagihan:</p>
                    <p class="text-2xl font-extrabold text-green-600">Rp <?= number_format($total_harga, 0, ',', '.') ?></p>
                </div>
            </div>

            <div class="mb-6">
                <p class="mb-2 text-gray-500 text-sm font-semibold uppercase">Kode Pembayaran Anda:</p>
                <div class="p-4 bg-gray-100 rounded-lg text-2xl font-mono font-extrabold text-cinemaRed text-center tracking-widest border-2 border-dashed border-gray-300">
                    <?= $code_booking ?>
                </div>
            </div>

            <!-- Detail Booking -->
            <div class="bg-gray-50 p-4 rounded-lg mb-6 border">
                <p class="text-gray-700 font-bold mb-2 flex items-center"><i class="ph ph-film-strip mr-2 text-cinemaRed"></i> Detail Pesanan</p>
                <div class="grid grid-cols-2 gap-2 text-sm text-gray-600">
                    <p>Film:</p><p class="font-semibold text-gray-800"><?= htmlspecialchars($data['judul']) ?></p>
                    <p>Studio:</p><p class="font-semibold"><?= htmlspecialchars($data['nama_studio']) ?></p>
                    <p>Waktu Tayang:</p>
                    <p class="font-semibold">
                        <?php 
                            $waktu_tayang = $data['Waktu_tayang'] ?? null;
                            echo $waktu_tayang ? date('d M Y, H:i', strtotime($waktu_tayang)) : 'N/A';
                        ?> WIB
                    </p>
                    <p>Kursi:</p><p class="font-semibold text-cinemaRed"><?= htmlspecialchars($data['kursi_list']) ?></p>
                    <p>Status:</p>
                    <p class="font-semibold text-yellow-600">
                        PENDING (Menunggu Pembayaran)
                    </p>
                </div>
            </div>

            <!-- Instruksi -->
            <div class="p-4 bg-red-100/50 rounded-lg mb-6 border border-red-300">
                <p class="text-gray-700 font-semibold mb-2 flex items-center">
                    <i class="ph ph-hand-tap-fill mr-2 text-cinemaRed"></i> Instruksi Pembayaran:
                </p>
                <p class="text-gray-600 text-sm">
                    Silakan melakukan pembayaran secara **tunai** di loket bioskop. Tunjukkan **Kode Pembayaran** Anda di atas kepada admin kami untuk proses verifikasi dan aktivasi tiket. Booking Anda akan otomatis batal jika tidak dibayar dalam 1 jam.
                </p>
            </div>
            
            <a href="dashboard.php" class="bg-cinemaGold hover:bg-yellow-600 text-cinemaBlack px-4 py-3 rounded-lg w-full font-extrabold text-lg text-center inline-block transition duration-200 shadow-md hover:shadow-lg">
                 Kembali ke Dashboard Saya
            </a>
            
            <p class="text-center text-xs text-gray-500 mt-4">ID Booking Internal: <?= $data['Id_booking'] ?></p>

        </div>
    </div>
</body>
</html>