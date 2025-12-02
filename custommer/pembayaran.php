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

// Validasi Data Awal
if (!$id_jadwal || empty($seats_str) || $qty <= 0 || $price <= 0) {
    die("Error: Data pemesanan tidak lengkap. Pastikan Anda memilih kursi. <a href='index.php'>Kembali</a>");
}

$seats = array_map('trim', explode(',', $seats_str));
$total_harga = $price * count($seats);

// Validasi tambahan: Pastikan jumlah kursi yang dipilih sesuai dengan qty
if (count($seats) !== $qty) {
    die("Error: Jumlah kursi (" . count($seats) . ") tidak sesuai dengan tiket yang dibeli ($qty). <a href='javascript:history.back()'>Kembali</a>");
}

// Cek apakah kursi sudah terisi (prevent double booking)
$seats_safe_check = implode("','", $seats); // Format: 'A1','A2'
$cek_transaksi = mysqli_query($conn, "SELECT seat FROM transaksi WHERE id_jadwal='".esc($id_jadwal)."' AND seat IN ('$seats_safe_check')");

if (mysqli_num_rows($cek_transaksi) > 0) {
    echo "<script>
            alert('Maaf, beberapa kursi yang Anda pilih baru saja dibooking orang lain.');
            window.history.back();
          </script>";
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
        throw new Exception("Gagal membuat booking utama: " . mysqli_error($conn));
    }
    $id_booking = mysqli_insert_id($conn);

    // B. Masukkan ke tabel DETAIL_BOOKING
    foreach ($seats as $seat) {
        $q_detail = "INSERT INTO detail_booking (Id_booking, no_kursi) VALUES ('$id_booking', '".esc($seat)."')";
        if (!mysqli_query($conn, $q_detail)) {
            throw new Exception("Gagal menyimpan detail kursi ($seat): " . mysqli_error($conn));
        }
    }

    // C. Masukkan ke tabel TRANSAKSI (Lock Kursi)
    // Table transaksi ini sepertinya digunakan untuk pengecekan cepat ketersediaan kursi
    $seats_str_safe = esc(implode(',', $seats)); 
    
    $q_transaksi = "INSERT INTO transaksi (id_jadwal, seat, nama_customer, kursi) 
                    VALUES ('".esc($id_jadwal)."', '$seats_str_safe', '".esc($username)."', '$seats_str_safe')"; 

    if (!mysqli_query($conn, $q_transaksi)) {
        throw new Exception("Gagal menyimpan data transaksi (Lock Kursi): " . mysqli_error($conn));
    }

    // 4. COMMIT & REDIRECT
    mysqli_commit($conn);
    
    // Redirect ke halaman konfirmasi pembayaran
    header("Location: payment_confirm.php?id_booking=" . $id_booking);
    exit;

} catch (Exception $e) {
    // 5. ROLLBACK & ERROR HANDLING
    mysqli_rollback($conn);
    
    // Tampilkan error langsung agar mudah didebug, jangan redirect ke index
    echo "<div style='font-family:sans-serif; padding:20px; text-align:center;'>";
    echo "<h2 style='color:red;'>Transaksi Gagal</h2>";
    echo "<p>Terjadi kesalahan sistem: <strong>" . $e->getMessage() . "</strong></p>";
    echo "<br><a href='index.php' style='background:#ccc; padding:10px; text-decoration:none; color:black; border-radius:5px;'>Kembali ke Home</a>";
    echo "</div>";
    exit;
}
?>