<?php
session_start();
if (!isset($_SESSION['login']) || $_SESSION['role'] != '1') {
    header("Location: ../login.php");
    exit;
}
include "../koneksi.php";
$q_income = mysqli_query($conn, "SELECT SUM(total_harga) as total FROM booking WHERE status_booking = '1'");
$total_income = mysqli_fetch_assoc($q_income)['total'] ?? 0;

$q_tickets = mysqli_query($conn, "SELECT COUNT(*) as total 
                                  FROM detail_booking db 
                                  JOIN booking b ON db.Id_booking = b.Id_booking 
                                  WHERE b.status_booking = '1'");
$total_tickets = mysqli_fetch_assoc($q_tickets)['total'] ?? 0;
$total_movies = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM movies"))['total'];
$total_studios = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM studios"))['total'];

$chart_labels = [];
$chart_data = [];

for ($i = 6; $i >= 0; $i--) {
    $date = date('Y-m-d', strtotime("-$i days"));
    $label = date('D', strtotime($date));
    
    $q_chart = mysqli_query($conn, "SELECT SUM(total_harga) as total FROM booking WHERE status_booking = '1' AND DATE(tanggal_booking) = '$date'");
    $data_row = mysqli_fetch_assoc($q_chart);
    
    $chart_labels[] = $label;
    $chart_data[] = $data_row['total'] ?? 0;
}
$query_transaksi = "SELECT 
                        b.Id_booking,
                        b.code_booking,
                        b.tanggal_booking,
                        b.total_harga,
                        b.status_booking,
                        u.username,
                        m.judul,
                        s.nama_studio,
                        j.harga as harga_satuan,
                        (SELECT COUNT(*) FROM detail_booking db WHERE db.Id_booking = b.Id_booking) as jumlah_tiket
                    FROM booking b
                    JOIN users u ON b.Id_user = u.Id_user
                    JOIN jadwal j ON b.Id_jadwal = j.Id_jadwal
                    JOIN movies m ON j.Id_movie = m.Id_movie
                    JOIN studios s ON j.Id_studio = s.Id_studio
                    ORDER BY b.tanggal_booking DESC 
                    LIMIT 10";
$result_transaksi = mysqli_query($conn, $query_transaksi);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Analytics Report - Onic Cinema</title>
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
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    
    <style>
        .glass-nav {
            background: rgba(20, 20, 20, 0.9);
            backdrop-filter: blur(10px);
        }
    </style>
</head>
    <nav class="glass-nav fixed w-full z-50 bg-cinemaBlack">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-20">
                
                <div class="flex items-center gap-4">
                    <img src="../logo.png" 
                         alt="Onic Logo" class="h-11 w-auto object-contain drop-shadow-lg">
                    
                    <h1 class="text-3xl font-bold text-cinemaGold tracking-widest uppercase" style="text-shadow: 0px 0px 7px;">
                        ONIC <span class="text-white">ADMINISTRATOR</span>
                    </h1>
                </div>

                <div class="hidden md:block">
                    <div class="ml-10 flex items-baseline space-x-4">
                        <a href="index.php" class="text-white hover:text-cinemaRed px-3 py-2 text-l rounded-md text-sm font-medium transition">Dashboard</a>
                        <a href="movies.php" class="text-white hover:text-cinemaRed px-3 py-2 rounded-md text-sm font-medium transition">Movies</a>
                        <a href="studio_admin.php" class="text-gray-300 hover:text-cinemaRed px-3 py-2 rounded-md text-sm font-medium transition">Studio</a>
                        <a href="schedule.php" class="text-white hover:text-cinemaRed px-3 py-2 rounded-md text-sm font-medium transition">Schedules</a>
                        <a href="validation.php" class="text-white hover:text-cinemaRed px-3 py-2 rounded-md text-sm font-medium transition">
                            Validation
                        </a>
                        <a href="report.php" class="bg-cinemaGold text-black-300 px-3 py-2 rounded-full text-sm font-medium transition">Report</a>
                        <a href="reviews.php" class="text-gray-300 hover:text-cinemaRed px-3 py-2 rounded-md text-sm font-medium transition">Reviews</a>

                    </div>
                </div>

                <div class="flex items-center gap-4">
                    <div class="hidden md:flex flex-col items-end">
                        <span class="text-white text-sm font-bold"><?= isset($_SESSION['username']) ? $_SESSION['username'] : 'Admin' ?></span>
                        <span class="text-gray-400 text-xs">Administrator</span>
                    </div>
                    <a href="../logout.php" class="text-gray-400 hover:text-white transition" title="Logout">
                        <i class="ph ph-sign-out text-2xl"></i>
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <div class="pt-28 pb-12 px-4 sm:px-6 lg:px-8 max-w-7xl mx-auto">
        <div class="flex justify-between items-end mb-8">
            <div>
                <h2 class="text-3xl font-bold text-gray-900">Laporan & Analitik</h2>
                <p class="text-gray-500 mt-1">Ringkasan performa penjualan tiket bioskop secara real-time.</p>
            </div>
            <button onclick="window.print()" class="bg-white border border-gray-300 text-gray-700 px-4 py-2 rounded-lg text-sm font-bold hover:bg-gray-50 transition flex items-center gap-2 shadow-sm">
                <i class="ph ph-printer text-lg"></i> Cetak Laporan
            </button>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
            <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100 flex flex-col justify-between hover:shadow-md transition">
                <div class="flex justify-between items-start mb-4">
                    <div>
                        <p class="text-gray-500 text-sm font-medium">Total Pendapatan</p>
                        <h3 class="text-2xl font-bold text-gray-900 mt-1">Rp <?= number_format($total_income/1000000, 1) ?>M</h3>
                    </div>
                    <div class="p-3 bg-green-100 text-green-600 rounded-lg">
                        <i class="ph ph-currency-dollar text-2xl"></i>
                    </div>
                </div>
                <p class="text-green-500 text-xs font-bold flex items-center gap-1">
                    <i class="ph ph-trend-up"></i> Updated Just Now
                </p>
            </div>
            <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100 flex flex-col justify-between hover:shadow-md transition">
                <div class="flex justify-between items-start mb-4">
                    <div>
                        <p class="text-gray-500 text-sm font-medium">Tiket Terjual</p>
                        <h3 class="text-2xl font-bold text-gray-900 mt-1"><?= number_format($total_tickets) ?></h3>
                    </div>
                    <div class="p-3 bg-blue-100 text-blue-600 rounded-lg">
                        <i class="ph ph-ticket text-2xl"></i>
                    </div>
                </div>
                <p class="text-blue-500 text-xs font-bold flex items-center gap-1">
                    <i class="ph ph-receipt"></i> Lembar Tiket
                </p>
            </div>
            <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100 flex flex-col justify-between hover:shadow-md transition">
                <div class="flex justify-between items-start mb-4">
                    <div>
                        <p class="text-gray-500 text-sm font-medium">Film Tayang</p>
                        <h3 class="text-2xl font-bold text-gray-900 mt-1"><?= $total_movies ?></h3>
                    </div>
                    <div class="p-3 bg-purple-100 text-purple-600 rounded-lg">
                        <i class="ph ph-film-strip text-2xl"></i>
                    </div>
                </div>
                <p class="text-purple-500 text-xs font-bold flex items-center gap-1">
                    <i class="ph ph-film-reel"></i> Judul Aktif
                </p>
            </div>
            <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100 flex flex-col justify-between hover:shadow-md transition">
                <div class="flex justify-between items-start mb-4">
                    <div>
                        <p class="text-gray-500 text-sm font-medium">Studio Aktif</p>
                        <h3 class="text-2xl font-bold text-gray-900 mt-1"><?= $total_studios ?></h3>
                    </div>
                    <div class="p-3 bg-red-100 text-cinemaRed rounded-lg">
                        <i class="ph ph-map-pin text-2xl"></i>
                    </div>
                </div>
                <p class="text-cinemaRed text-xs font-bold flex items-center gap-1">
                    <i class="ph ph-armchair"></i> Ruangan
                </p>
            </div>
        </div>
        <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100 mb-8">
            <div class="flex justify-between items-center mb-6">
                <h3 class="text-lg font-bold text-gray-900">Analitik Penjualan (7 Hari Terakhir)</h3>
                <button class="text-gray-400 hover:text-gray-600"><i class="ph ph-dots-three text-2xl"></i></button>
            </div>
            <div class="w-full h-80">
                <canvas id="salesChart"></canvas>
            </div>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="p-6 border-b border-gray-100 flex justify-between items-center bg-white">
                <h3 class="text-lg font-bold text-gray-900">Transaksi Terbaru</h3>
                <div class="flex gap-2">
                    <a href="export_csv.php" class="px-3 py-1.5 text-sm bg-cinemaBlack text-white rounded-lg hover:bg-gray-800 font-medium shadow-md">
                        Export CSV
                    </a>
                </div>
            </div>
            
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-gray-50 text-gray-500 text-xs uppercase tracking-wider border-b border-gray-100">
                            <th class="px-6 py-4 font-semibold">Order ID</th>
                            <th class="px-6 py-4 font-semibold">Film & Studio</th>
                            <th class="px-6 py-4 font-semibold">Pelanggan</th>
                            <th class="px-6 py-4 font-semibold text-center">Status</th>
                            <th class="px-6 py-4 font-semibold text-center">Jml Tiket</th>
                            <th class="px-6 py-4 font-semibold text-right">Harga Satuan</th>
                            <th class="px-6 py-4 font-semibold text-right">Total</th>
                            <th class="px-6 py-4 font-semibold text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 text-sm">
                        <?php while($row = mysqli_fetch_assoc($result_transaksi)): ?>
                        <tr class="hover:bg-gray-50 transition">
                            <td class="px-6 py-4 font-bold text-cinemaRed">
                                #<?= $row['code_booking'] ?: 'ORD-'.$row['Id_booking'] ?>
                            </td>
                            <td class="px-6 py-4">
                                <p class="font-bold text-gray-900 text-base"><?= $row['judul'] ?></p>
                                <p class="text-xs text-gray-500 flex items-center gap-1 mt-0.5">
                                    <i class="ph ph-monitor-play"></i> <?= $row['nama_studio'] ?>
                                </p>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 rounded-full bg-gradient-to-br from-indigo-500 to-purple-600 text-white flex items-center justify-center font-bold text-xs shadow-sm">
                                        <?= substr($row['username'], 0, 1) ?>
                                    </div>
                                    <div>
                                        <p class="font-medium text-gray-900"><?= $row['username'] ?></p>
                                        <p class="text-xs text-gray-400"><?= date('d M, H:i', strtotime($row['tanggal_booking'])) ?></p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 text-center">
                                <?php if($row['status_booking'] == '1'): ?>
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-bold bg-green-100 text-green-700 border border-green-200">
                                        <i class="ph ph-check-circle-fill"></i> Success
                                    </span>
                                <?php elseif($row['status_booking'] == '2'): ?>
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-bold bg-yellow-100 text-yellow-700 border border-yellow-200">
                                        <i class="ph ph-clock-fill"></i> Pending
                                    </span>
                                <?php else: ?>
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-bold bg-red-100 text-red-700 border border-red-200">
                                        <i class="ph ph-x-circle-fill"></i> Cancel
                                    </span>
                                <?php endif; ?>
                            </td>
                            <td class="px-6 py-4 text-center font-bold text-gray-700">
                                <?= $row['jumlah_tiket'] ?>
                            </td>
                            <td class="px-6 py-4 text-right text-gray-500">
                                Rp <?= number_format($row['harga_satuan'], 0, ',', '.') ?>
                            </td>
                            <td class="px-6 py-4 text-right font-bold text-gray-900 text-base">
                                Rp <?= number_format($row['total_harga'], 0, ',', '.') ?>
                            </td>
                            <td class="px-6 py-4 text-center">
                                <?php if($row['status_booking'] == '2'): ?>
                                    <!-- Tombol Proses untuk Validation -->
                                    <a href="validation.php" class="inline-block bg-cinemaRed hover:bg-red-700 text-white text-xs font-bold px-3 py-1.5 rounded shadow transition">
                                        Proses
                                    </a>
                                <?php else: ?>
                                    <button class="text-gray-400 hover:text-cinemaBlack transition"><i class="ph ph-dots-three-vertical font-bold text-lg"></i></button>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>

    </div>
    <script>
        const ctx = document.getElementById('salesChart');
        const labels = <?= json_encode($chart_labels) ?>;
        const data = <?= json_encode($chart_data) ?>;

        new Chart(ctx, {
            type: 'line',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Pendapatan (Rp)',
                    data: data,
                    borderColor: '#E50914',
                    backgroundColor: (context) => {
                        const ctx = context.chart.ctx;
                        const gradient = ctx.createLinearGradient(0, 0, 0, 400);
                        gradient.addColorStop(0, 'rgba(229, 9, 20, 0.2)');
                        gradient.addColorStop(1, 'rgba(229, 9, 20, 0)');
                        return gradient;
                    },
                    borderWidth: 3,
                    pointBackgroundColor: '#FFFFFF',
                    pointBorderColor: '#E50914',
                    pointBorderWidth: 2,
                    pointRadius: 5,
                    pointHoverRadius: 7,
                    tension: 0.4,
                    fill: true
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        backgroundColor: '#141414',
                        titleFont: { family: 'sans-serif', size: 13 },
                        bodyFont: { family: 'sans-serif', size: 14, weight: 'bold' },
                        padding: 12,
                        callbacks: {
                            label: function(context) {
                                return 'Rp ' + context.parsed.y.toLocaleString('id-ID');
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: { borderDash: [4, 4], color: '#f3f4f6' },
                        ticks: {
                            font: { family: 'sans-serif', size: 11 },
                            callback: function(value) { return 'Rp ' + value / 1000 + 'k'; } 
                        }
                    },
                    x: {
                        grid: { display: false },
                        ticks: { font: { family: 'sans-serif', size: 12 } }
                    }
                }
            }
        });
    </script>
</body>
</html>