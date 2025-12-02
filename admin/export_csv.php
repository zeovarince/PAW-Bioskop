<?php
session_start();
if (!isset($_SESSION['login']) || $_SESSION['role'] != '1') {
    header("Location: ../login.php");
    exit;
}

include "../koneksi.php";

header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=Laporan_Transaksi_Onic_Cinema.csv');

$output = fopen("php://output", "w");

// Header kolom
fputcsv($output, ['Order ID', 'Pelanggan', 'Film', 'Studio', 'Jumlah Tiket', 'Harga Satuan', 'Total Harga', 'Status', 'Tanggal Booking']);

// Query data transaksi
$query = "SELECT 
            b.code_booking,
            u.username,
            m.judul,
            s.nama_studio,
            (SELECT COUNT(*) FROM detail_booking db WHERE db.Id_booking = b.Id_booking) as jumlah_tiket,
            j.harga as harga_satuan,
            b.total_harga,
            b.status_booking,
            b.tanggal_booking
          FROM booking b
          JOIN users u ON b.Id_user = u.Id_user
          JOIN jadwal j ON b.Id_jadwal = j.Id_jadwal
          JOIN movies m ON j.Id_movie = m.Id_movie
          JOIN studios s ON j.Id_studio = s.Id_studio
          ORDER BY b.tanggal_booking DESC";

$result = mysqli_query($conn, $query);

// Konversi status booking
function status($st) {
    if ($st == '1') return 'Success';
    if ($st == '2') return 'Pending';
    return 'Cancel';
}

// Loop data ke CSV
while ($row = mysqli_fetch_assoc($result)) {
    fputcsv($output, [
        $row['code_booking'],
        $row['username'],
        $row['judul'],
        $row['nama_studio'],
        $row['jumlah_tiket'],
        $row['harga_satuan'],
        $row['total_harga'],
        status($row['status_booking']),
        $row['tanggal_booking']
    ]);
}

fclose($output);
exit;
