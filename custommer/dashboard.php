<?php
session_start();
if (!isset($_SESSION['login']) || $_SESSION['role'] != '2') {
    header("Location: ../login.php");
    exit;
}
include "../koneksi.php";

$user_id = $_SESSION['user_id'];

$query_history = "
    SELECT 
        b.Id_booking, b.code_booking, b.tanggal_booking, b.total_harga, b.status_booking,
        m.judul, m.poster, m.Id_movie,
        s.nama_studio,
        j.Waktu_tayang,
        (SELECT COUNT(Id_reviews) FROM reviews WHERE Id_user = b.Id_user AND Id_movie = m.Id_movie) as review_status,
        GROUP_CONCAT(db.no_kursi ORDER BY db.no_kursi ASC) as kursi_list
    FROM booking b
    JOIN jadwal j ON b.Id_jadwal = j.Id_jadwal
    JOIN movies m ON j.Id_movie = m.Id_movie
    JOIN studios s ON j.Id_studio = s.Id_studio
    LEFT JOIN detail_booking db ON b.Id_booking = db.Id_booking
    WHERE b.Id_user = '".mysqli_real_escape_string($conn, $user_id)."'
    GROUP BY b.Id_booking
    ORDER BY b.tanggal_booking DESC
";
$result_history = mysqli_query($conn, $query_history);

?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Onic Cinema</title>
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
<body class="bg-gray-100 font-sans text-gray-800">

    <!-- Navigasi Customer -->
<nav class="bg-cinemaBlack border-b border-gray-800 py-4">
    <div class="max-w-7xl mx-auto px-6 flex justify-between items-center">
        <div class="flex items-center gap-4">
            <img src="../logo.png" alt="Onic Logo" class="h-11 w-auto object-contain drop-shadow-lg">
            <a href="index.php">
                <h1 class="text-3xl font-bold text-cinemaGold tracking-widest uppercase" style="text-shadow: 0px 0px 7px;">
                    ONIC <span class="text-white">CINEMA</span>
                </h1>
            </a>
        </div>
        <div class="hidden md:flex space-x-8">
            <a href="index.php" class="text-gray-300 hover:text-cinemaGold px-3 py-2 rounded-md text-sm font-medium transition">
                Home
            </a>

            <a href="movies.php" class="text-gray-300 hover:text-cinemaGold px-3 py-2 rounded-md text-sm font-medium transition">
                Movies
            </a>
            <a href="dashboard.php" class="bg-cinemaGold text-black px-3 py-2 rounded-md text-sm font-bold transition shadow-lg shadow-yellow-500/10">
                Dashboard
            </a>

            <a href="contact.php" class="text-gray-300 hover:text-cinemaGold px-3 py-2 rounded-md text-sm font-medium transition">
                Contact
            </a>
        </div>
        <div class="flex items-center gap-4">
            <div class="text-right hidden sm:block">
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
    
    <div class="max-w-7xl mx-auto px-6 py-10">
        <h2 class="text-3xl font-bold text-gray-800 mb-2 flex items-center gap-2">
            <i class="ph ph-user-circle text-cinemaRed"></i> Hi, <?= $_SESSION['username'] ?>!
        </h2>
        <p class="text-gray-500 mb-8">Riwayat dan status pemesanan tiket Anda.</p>

        <div class="bg-white shadow-xl rounded-xl border border-gray-200">
            <div class="p-6 border-b bg-gray-50 rounded-t-xl">
                <h3 class="text-xl font-bold text-gray-800 flex items-center gap-2">
                    <i class="ph ph-receipt text-cinemaGold"></i> Riwayat Pemesanan
                </h3>
            </div>
            
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-100">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Kode</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Film & Jadwal</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Kursi</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Total Harga</th>
                            <th class="px-6 py-3 text-center text-xs font-semibold text-gray-500 uppercase tracking-wider">Status</th>
                            <th class="px-6 py-3 text-center text-xs font-semibold text-gray-500 uppercase tracking-wider">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php if (mysqli_num_rows($result_history) > 0): ?>
                            <?php while($row = mysqli_fetch_assoc($result_history)): 
                                $is_confirmed = $row['status_booking'] == '1';
                                // Logika $is_future dihapus agar bisa review segera setelah Confirmed
                                $has_reviewed = $row['review_status'] > 0;
                            ?>
                            <tr class="hover:bg-gray-50 transition">
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-bold text-cinemaRed">
                                    <?= htmlspecialchars($row['code_booking']) ?>
                                </td>
                                <td class="px-6 py-4 text-sm">
                                    <p class="font-bold text-gray-900"><?= htmlspecialchars($row['judul']) ?></p>
                                    <p class="text-xs text-gray-500 flex items-center gap-1"><i class="ph ph-calendar-blank"></i> <?= date('d M Y, H:i', strtotime($row['Waktu_tayang'])) ?> di Studio <?= htmlspecialchars($row['nama_studio']) ?></p>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-600 font-medium">
                                    <?= htmlspecialchars($row['kursi_list']) ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-bold text-green-600">
                                    Rp <?= number_format($row['total_harga'], 0, ',', '.') ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-center">
                                    <?php 
                                    $status = $row['status_booking'];
                                    if ($status == '1') {
                                        echo '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-green-100 text-green-700"><i class="ph ph-check-circle-fill mr-1"></i> CONFIRMED</span>';
                                    } elseif ($status == '2') {
                                        echo '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-yellow-100 text-yellow-700"><i class="ph ph-clock-fill mr-1"></i> PENDING</span>';
                                    } else {
                                        echo '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-red-100 text-red-700"><i class="ph ph-x-circle-fill mr-1"></i> CANCELED</span>';
                                    }
                                    ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-center text-sm font-medium">
                                    <?php if ($status == '2'): ?>
                                        <a href="payment_confirm.php?id_booking=<?= $row['Id_booking'] ?>" class="bg-cinemaRed hover:bg-red-700 text-white px-3 py-1.5 rounded-lg font-bold text-xs transition shadow-md">
                                            Lanjut Bayar
                                        </a>
                                    <?php elseif ($is_confirmed): ?>
                                        <?php if (!$has_reviewed): ?>
                                            <!-- Link ke halaman review jika tiket sudah CONFIRMED dan BELUM di-review -->
                                            <a href="review_form.php?id_movie=<?= $row['Id_movie'] ?>" class="bg-cinemaGold text-black px-3 py-1.5 rounded-lg font-bold text-xs hover:bg-yellow-600 transition shadow">
                                                Beri Review
                                            </a>
                                        <?php else: ?>
                                            <span class="text-gray-500 text-xs px-3 py-1.5">Reviewed</span>
                                        <?php endif; ?>
                                    <?php else: ?>
                                        <span class="text-gray-400 text-xs px-3 py-1.5">-</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="6" class="px-6 py-10 text-center text-gray-500">
                                    <i class="ph ph-ticket-slash text-5xl text-gray-300 mb-3 block"></i>
                                    Anda belum melakukan pemesanan tiket.
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