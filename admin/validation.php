<?php
session_start();
if (!isset($_SESSION['login']) || $_SESSION['role'] != '1') {
    header("Location: ../login.php");
    exit;
}
include "../koneksi.php";
include "../function.php"; 

$q_pending = "
    SELECT 
        b.Id_booking, b.code_booking, b.tanggal_booking, b.total_harga, b.status_booking,
        u.username,
        m.judul,
        s.nama_studio,
        j.Waktu_tayang,
        GROUP_CONCAT(db.no_kursi ORDER BY db.no_kursi ASC) as kursi_list
    FROM booking b
    JOIN users u ON b.Id_user = u.Id_user
    JOIN jadwal j ON b.Id_jadwal = j.Id_jadwal
    JOIN movies m ON j.Id_movie = m.Id_movie
    JOIN studios s ON j.Id_studio = s.Id_studio
    LEFT JOIN detail_booking db ON b.Id_booking = db.Id_booking
    WHERE b.status_booking = '2'
    GROUP BY b.Id_booking
    ORDER BY b.tanggal_booking ASC
";
$result_pending = mysqli_query($conn, $q_pending);

$message = $_SESSION['message'] ?? null;
unset($_SESSION['message']);

// Logika untuk Aksi Konfirmasi/Batal
if (isset($_GET['action']) && isset($_GET['id'])) {
    $action = $_GET['action'];
    $id_booking = mysqli_real_escape_string($conn, $_GET['id']);
    
    $booking = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM booking WHERE Id_booking = '$id_booking'"));

    if (!$booking) {
        $_SESSION['message'] = "Error: Booking tidak ditemukan.";
    } else {
        $status_baru = ($action == 'confirm') ? '1' : '3'; // 1=Confirmed, 3=Cancelled
        $pesan = ($action == 'confirm') ? 'dikonfirmasi' : 'dibatalkan';

        // UPDATE STATUS
        $q_update = "UPDATE booking SET status_booking = '$status_baru' WHERE Id_booking = '$id_booking'";
        
        if ($status_baru == '3') {
            // Hapus kursi dari tabel transaksi jika dibatalkan
            mysqli_query($conn, "DELETE FROM transaksi WHERE id_jadwal = '{$booking['Id_jadwal']}' AND kursi IN (SELECT no_kursi FROM detail_booking WHERE Id_booking = '{$booking['Id_booking']}')");
        }

        if (mysqli_query($conn, $q_update)) {
            $_SESSION['message'] = "Sukses: Booking {$booking['code_booking']} berhasil {$pesan}.";
        } else {
            $_SESSION['message'] = "Gagal memproses aksi: " . mysqli_error($conn);
        }
    }
    header("Location: validation.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Validasi Booking - Onic Cinema Admin</title>
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
        .modal { transition: opacity 0.3s ease-in-out; }
    </style>
</head>
<body class="bg-gray-50 font-sans text-gray-800">

    <!-- Navigasi Admin -->
    <nav class="glass-nav fixed w-full z-50 bg-cinemaBlack">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-20">
                <div class="flex items-center gap-4">
                    <img src="../logo.png" alt="Onic Logo" class="h-11 w-auto object-contain drop-shadow-lg">
                    <h1 class="text-3xl font-bold text-cinemaGold tracking-widest uppercase">
                        ONIC <span class="text-white">ADMINISTRATOR</span>
                    </h1>
                </div>

                <div class="hidden md:block">
                    <div class="ml-10 flex items-baseline space-x-4">
                        <a href="index.php" class="text-gray-300 hover:text-cinemaRed px-3 py-2 rounded-md text-sm font-medium transition">Dashboard</a>
                        <a href="movies.php" class="text-gray-300 hover:text-cinemaRed px-3 py-2 rounded-md text-sm font-medium transition">Movies</a>
                        <a href="schedule.php" class="text-gray-300 hover:text-cinemaRed px-3 py-2 rounded-md text-sm font-medium transition">Schedules</a>
                        <a href="validation.php" class="bg-cinemaGold text-black px-3 py-2 text-l rounded-full text-sm font-medium transition">Validation</a>
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
    <!-- End Navigasi -->

    <div class="pt-24 pb-12 min-h-screen">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

            <!-- Judul Halaman -->
            <div class="mb-8 flex justify-between items-center border-b pb-4">
                <div>
                    <h2 class="text-3xl font-extrabold text-cinemaBlack flex items-center gap-2">
                        <i class="ph ph-hand-coins text-cinemaRed"></i> Validasi Pembayaran
                    </h2>
                    <p class="text-gray-500 mt-1">Daftar pemesanan tiket yang menunggu konfirmasi pembayaran.</p>
                </div>
            </div>

            <!-- Tampilkan Pesan Sukses/Error (jika ada) -->
            <?php if ($message): ?>
                <div class="p-4 mb-4 text-sm rounded-lg <?= strpos($message, 'Gagal') !== false ? 'bg-red-100 text-red-700' : 'bg-green-100 text-green-700' ?>" role="alert">
                    <?= $message; ?>
                </div>
            <?php endif; ?>

            <!-- Tabel Daftar Pending Booking -->
            <div class="bg-white shadow-xl rounded-xl overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-100">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Kode Booking</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Pelanggan</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Film & Jadwal</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Kursi</th>
                                <th class="px-6 py-3 text-right text-xs font-semibold text-gray-500 uppercase tracking-wider">Total</th>
                                <th class="px-6 py-3 text-center text-xs font-semibold text-gray-500 uppercase tracking-wider">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <?php if (mysqli_num_rows($result_pending) > 0): ?>
                                <?php while ($row = mysqli_fetch_assoc($result_pending)): ?>
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-bold text-cinemaRed">
                                            <?= htmlspecialchars($row['code_booking']) ?>
                                        </td>
                                        <td class="px-6 py-4 text-sm">
                                            <p class="font-bold text-gray-900"><?= htmlspecialchars($row['username']) ?></p>
                                            <p class="text-xs text-gray-500"><?= date('d M Y, H:i', strtotime($row['tanggal_booking'])) ?></p>
                                        </td>
                                        <td class="px-6 py-4 text-sm">
                                            <p class="font-bold text-gray-900"><?= htmlspecialchars($row['judul']) ?></p>
                                            <p class="text-xs text-gray-500">@ <?= date('H:i', strtotime($row['Waktu_tayang'])) ?> di Studio <?= htmlspecialchars($row['nama_studio']) ?></p>
                                        </td>
                                        <td class="px-6 py-4 text-sm text-gray-600 font-medium">
                                            <?= htmlspecialchars($row['kursi_list']) ?>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-bold text-green-600 text-right">
                                            Rp <?= number_format($row['total_harga'], 0, ',', '.') ?>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-center text-sm font-medium">
                                            <a href="?action=confirm&id=<?= $row['Id_booking'] ?>" class="bg-green-500 hover:bg-green-600 text-white px-3 py-1.5 rounded-lg font-semibold text-xs shadow-md mr-2">
                                                <i class="ph ph-check-circle"></i> Konfirmasi
                                            </a>
                                            <a href="?action=cancel&id=<?= $row['Id_booking'] ?>" class="bg-red-500 hover:bg-red-600 text-white px-3 py-1.5 rounded-lg font-semibold text-xs shadow-md">
                                                <i class="ph ph-x-circle"></i> Batal
                                            </a>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="6" class="px-6 py-10 text-center text-gray-500">
                                        <i class="ph ph-clock-countdown text-5xl text-gray-300 mb-3 block"></i>
                                        Tidak ada pemesanan yang menunggu validasi.
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </div>
</body>
</html>