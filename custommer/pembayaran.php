<?php
session_start();
include "../koneksi.php";
include "../function.php"; // Memuat fungsi esc() dan lainnya

// 1. Cek User Login
if (!isset($_SESSION['login']) || $_SESSION['role'] != '2') {
    header("Location: ../login.php");
    exit;
}
$user_id = $_SESSION['user_id'];
$username = $_SESSION['username'];

// 2. Ambil Data POST dari pilih_kursi.php
$id_jadwal = $_POST['id_jadwal'] ?? null;
$seats_str = $_POST['seat'] ?? '';
$qty = (int)($_POST['qty'] ?? 0);
$price = (float)($_POST['price'] ?? 0);

if (!$id_jadwal || empty($seats_str) || $qty <= 0 || $price <= 0) {
    $_SESSION['booking_error'] = "Data pemesanan tidak lengkap.";
    header("Location: index.php"); // Kembali ke halaman utama jika data kurang
    exit;
}

$seats = array_map('trim', explode(',', $seats_str));
$total_harga = $price * count($seats);

// Validasi tambahan: Pastikan jumlah kursi yang dipilih sesuai dengan qty
if (count($seats) !== $qty) {
    $_SESSION['booking_error'] = "Kesalahan validasi: Jumlah kursi tidak sesuai dengan kuantitas tiket.";
    header("Location: index.php"); 
    exit;
}

// Cek apakah kursi sudah terisi (prevent double booking)
$seats_safe = implode("','", $seats);
$cek_transaksi = mysqli_query($conn, "SELECT seat FROM transaksi WHERE id_jadwal='".esc($id_jadwal)."' AND seat IN ('$seats_safe')");

if (mysqli_num_rows($cek_transaksi) > 0) {
    $_SESSION['booking_error'] = "Beberapa kursi yang Anda pilih sudah terisi. Silakan pilih kursi lain.";
    header("Location: pilih_kursi.php?id=".esc($_POST['id'])."&studio=".esc($_POST['studio'])."&date=".esc($_POST['date'])."&time=".esc($_POST['time'])."&price=".esc($price)."&qty=".esc($qty));
    exit;
}

// 3. START TRANSACTION DATABASE
mysqli_begin_transaction($conn);
$success = true;
$id_booking = null;

try {
    // A. Masukkan ke tabel BOOKING (Status Pending = '2')
    $code_booking = "PAY-" . strtoupper(substr(md5(time() . rand()), 0, 8));

    $q_booking = "INSERT INTO booking (Id_user, Id_jadwal, total_harga, code_booking, status_booking) 
                  VALUES ('".esc($user_id)."', '".esc($id_jadwal)."', '".esc($total_harga)."', '$code_booking', '2')";
    
    if (!mysqli_query($conn, $q_booking)) {
        $success = false;
        throw new Exception("Gagal membuat booking utama.");
    }
    $id_booking = mysqli_insert_id($conn);

    // B. Masukkan ke tabel DETAIL_BOOKING
    foreach ($seats as $seat) {
        $q_detail = "INSERT INTO detail_booking (Id_booking, no_kursi) VALUES ('$id_booking', '".esc($seat)."')";
        if (!mysqli_query($conn, $q_detail)) {
            $success = false;
            throw new Exception("Gagal membuat detail booking.");
        }
    }

    // C. Masukkan ke tabel TRANSAKSI (Lock Kursi)
    $seats_str_safe = esc(implode(',', $seats)); // Simpan kembali sebagai string koma
    $q_transaksi = "INSERT INTO transaksi (id_jadwal, seat, nama_customer, kursi) 
                    VALUES ('".esc($id_jadwal)."', '$seats_str_safe', '".esc($username)."', '$seats_str_safe')"; 
                    // Note: Schema transaksi agak aneh, kita isi 'seat' dan 'kursi' dengan data kursi

    if (!mysqli_query($conn, $q_transaksi)) {
        $success = false;
        throw new Exception("Gagal mengunci kursi di transaksi.");
    }

    // 4. COMMIT & REDIRECT
    mysqli_commit($conn);
    
    // Redirect ke halaman konfirmasi pembayaran dengan ID Booking yang baru dibuat
    header("Location: payment_confirm.php?id_booking=" . $id_booking);
    exit;

} catch (Exception $e) {
    // 5. ROLLBACK & ERROR
    mysqli_rollback($conn);
    error_log("Booking failed: " . $e->getMessage());
    $_SESSION['booking_error'] = "Pemesanan gagal diproses: " . $e->getMessage();
    header("Location: index.php");
    exit;
}
?>