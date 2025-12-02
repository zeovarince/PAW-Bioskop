<?php
session_start();
if (!isset($_SESSION['login']) || $_SESSION['role'] != '1') {
    header("Location: ../login.php");
    exit;
}
include "../koneksi.php";

// --- PROSES UPDATE STATUS (TERIMA / TOLAK) ---
if (isset($_POST['action']) && isset($_POST['id_booking'])) {
    $id = mysqli_real_escape_string($conn, $_POST['id_booking']);
    $action = $_POST['action'];
    
    // 1 = Success, 3 = Cancelled
    $status = ($action === 'accept') ? '1' : '3';
    
    $query_update = "UPDATE booking SET status_booking = '$status' WHERE Id_booking = '$id'";
    
    if (mysqli_query($conn, $query_update)) {
        $pesan = ($status == '1') ? "Pesanan berhasil divalidasi!" : "Pesanan telah dibatalkan.";
        echo "<script>alert('$pesan'); window.location='validation.php';</script>";
    } else {
        echo "<script>alert('Gagal mengubah status: " . mysqli_error($conn) . "');</script>";
    }
}

// --- AMBIL DATA PENDING ---
// Join tabel booking, users, jadwal, movies, dan studios
$query = "SELECT 
            b.Id_booking, 
            b.code_booking, 
            b.tanggal_booking, 
            b.total_harga,
            u.username, 
            u.email,
            m.judul, 
            s.nama_studio, 
            j.Waktu_tayang,
            (SELECT GROUP_CONCAT(no_kursi SEPARATOR ', ') FROM detail_booking db WHERE db.Id_booking = b.Id_booking) as kursi
          FROM booking b
          JOIN users u ON b.Id_user = u.Id_user
          JOIN jadwal j ON b.Id_jadwal = j.Id_jadwal
          JOIN movies m ON j.Id_movie = m.Id_movie
          JOIN studios s ON j.Id_studio = s.Id_studio
          WHERE b.status_booking = '2' 
          ORDER BY b.tanggal_booking ASC";

$result = mysqli_query($conn, $query);
$total_pending = mysqli_num_rows($result);

// Hitung Pending Global (untuk Badge Navbar)
$q_badge = mysqli_query($conn, "SELECT COUNT(*) as total FROM booking WHERE status_booking = '2'");
$badge_nav = mysqli_fetch_assoc($q_badge)['total'];
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Validasi Tiket - Onic Cinema</title>
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
    <script src="https://unpkg.com/@phosphor-icons/web"></script>
    
    <style>
        .glass-nav {
            background: rgba(20, 20, 20, 0.95);
            backdrop-filter: blur(10px);
        }
    </style>
</head>
<body class="bg-gray-50 font-sans text-gray-800">

    <!-- NAVBAR ADMIN (SAMA PERSIS DENGAN INDEX) -->
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
                        
                        <a href="studio_admin.php" class="text-gray-300 hover:text-cinemaRed px-3 py-2 rounded-md text-sm font-medium transition">Studio</a>
                        
                        <a href="schedule.php" class="text-gray-300 hover:text-cinemaRed px-3 py-2 rounded-md text-sm font-medium transition">Schedules</a>
                        
                        <!-- VALIDATION (Sedang Aktif) -->
                        <a href="validation.php" class="bg-cinemaGold text-black px-3 py-2 rounded-full text-sm font-bold transition shadow-lg flex items-center gap-1">
                            Validation
                            <?php if($badge_nav > 0): ?>
                                <span class="ml-1 bg-red-600 text-white text-xs px-1.5 py-0.5 rounded-full animate-pulse"><?= $badge_nav ?></span>
                            <?php endif; ?>
                        </a>
                        
                        <a href="report.php" class="text-gray-300 hover:text-cinemaRed px-3 py-2 rounded-md text-sm font-medium transition">Report</a>
                    
                        <a href="reviews.php" class="text-gray-300 hover:text-cinemaRed px-3 py-2 rounded-md text-sm font-medium transition">Reviews</a>

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

    <!-- CONTENT -->
    <div class="pt-28 pb-12 px-4 sm:px-6 lg:px-8 max-w-7xl mx-auto">
        
        <!-- HEADER HALAMAN -->
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-8">
            <div>
                <h2 class="text-3xl font-bold text-gray-900 flex items-center gap-2">
                    <i class="ph ph-ticket text-cinemaRed"></i> Validasi Tiket
                </h2>
                <p class="text-gray-500 mt-1">Konfirmasi pembayaran tunai dari pelanggan di loket.</p>
            </div>
            
            <div class="bg-white px-4 py-2 rounded-lg border shadow-sm text-sm text-gray-600">
                Menunggu Konfirmasi: <strong class="text-cinemaRed"><?= $total_pending ?> Transaksi</strong>
            </div>
        </div>

        <!-- TABEL VALIDASI -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
            
            <!-- Tab Header -->
            <div class="flex border-b border-gray-200 bg-gray-50">
                <button class="px-6 py-3 text-sm font-bold text-cinemaRed border-b-2 border-cinemaRed bg-white flex items-center gap-2">
                    <i class="ph ph-clock"></i> Menunggu
                </button>
                <a href="report.php" class="px-6 py-3 text-sm font-medium text-gray-500 hover:text-gray-700 transition flex items-center gap-2">
                    <i class="ph ph-check-circle"></i> Riwayat Transaksi
                </a>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-white text-gray-500 text-xs uppercase tracking-wider border-b border-gray-100">
                            <th class="px-6 py-4 font-semibold">Kode Booking</th>
                            <th class="px-6 py-4 font-semibold">Pelanggan</th>
                            <th class="px-6 py-4 font-semibold">Detail Film</th>
                            <th class="px-6 py-4 font-semibold text-center">Kursi</th>
                            <th class="px-6 py-4 font-semibold text-right">Total Bayar</th>
                            <th class="px-6 py-4 font-semibold text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 text-sm">
                        
                        <?php if($total_pending > 0): ?>
                            <?php while($row = mysqli_fetch_assoc($result)): ?>
                            <tr class="hover:bg-yellow-50/30 transition group">
                                <td class="px-6 py-4">
                                    <span class="font-mono font-bold text-lg text-gray-800">#<?= $row['code_booking'] ?></span>
                                    <p class="text-xs text-gray-400 mt-1"><?= date('d M Y, H:i', strtotime($row['tanggal_booking'])) ?></p>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-3">
                                        <div class="w-8 h-8 rounded-full bg-gradient-to-br from-blue-500 to-indigo-600 text-white flex items-center justify-center font-bold uppercase">
                                            <?= substr($row['username'], 0, 1) ?>
                                        </div>
                                        <div>
                                            <p class="font-bold text-gray-900"><?= $row['username'] ?></p>
                                            <p class="text-xs text-gray-500 truncate w-32"><?= $row['email'] ?></p>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <p class="font-bold text-gray-800"><?= $row['judul'] ?></p>
                                    <div class="flex items-center gap-2 mt-1">
                                        <span class="bg-gray-100 text-gray-600 text-[10px] px-2 py-0.5 rounded font-bold"><?= $row['nama_studio'] ?></span>
                                        <span class="text-xs text-gray-500"><?= date('H:i', strtotime($row['Waktu_tayang'])) ?> WIB</span>
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <p class="text-sm font-bold text-gray-800"><?= $row['kursi'] ?></p>
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <p class="font-bold text-lg text-cinemaRed">Rp <?= number_format($row['total_harga'], 0, ',', '.') ?></p>
                                    <span class="text-[10px] text-gray-400 bg-gray-100 px-2 py-0.5 rounded">Tunai / Transfer</span>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex justify-center gap-2">
                                        <!-- Form Terima -->
                                        <form method="POST" onsubmit="return confirm('Konfirmasi pembayaran ini valid?')">
                                            <input type="hidden" name="id_booking" value="<?= $row['Id_booking'] ?>">
                                            <input type="hidden" name="action" value="accept">
                                            <button type="submit" class="bg-green-500 hover:bg-green-600 text-white p-2 rounded-lg shadow-md transition transform active:scale-95" title="Terima (Lunas)">
                                                <i class="ph ph-check text-lg font-bold"></i>
                                            </button>
                                        </form>

                                        <!-- Form Tolak -->
                                        <form method="POST" onsubmit="return confirm('Yakin ingin membatalkan pesanan ini?')">
                                            <input type="hidden" name="id_booking" value="<?= $row['Id_booking'] ?>">
                                            <input type="hidden" name="action" value="reject">
                                            <button type="submit" class="bg-red-500 hover:bg-red-600 text-white p-2 rounded-lg shadow-md transition transform active:scale-95" title="Tolak / Batalkan">
                                                <i class="ph ph-x text-lg font-bold"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        
                        <?php else: ?>
                            <!-- State Kosong -->
                            <tr>
                                <td colspan="6" class="py-16 text-center">
                                    <div class="flex flex-col items-center justify-center text-gray-400">
                                        <div class="bg-gray-100 p-4 rounded-full mb-3">
                                            <i class="ph ph-check-circle text-4xl text-green-500"></i>
                                        </div>
                                        <p class="text-lg font-medium text-gray-600">Tidak ada antrean validasi.</p>
                                        <p class="text-sm">Semua pesanan telah diproses.</p>
                                    </div>
                                </td>
                            </tr>
                        <?php endif; ?>

                    </tbody>
                </table>
            </div>
        </div>

    </div>

</body>
</html>