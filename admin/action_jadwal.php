<?php
session_start();
// Pastikan user adalah admin
if (!isset($_SESSION['login']) || $_SESSION['role'] != '1') {
    header("Location: ../login.php");
    exit;
}

include "../koneksi.php";
include "../function.php"; // Memuat fungsi CRUD Jadwal

// Tentukan aksi berdasarkan input POST atau GET
$action = $_REQUEST['action'] ?? null;

if ($action == 'create' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    // Aksi: TAMBAH JADWAL BARU (CREATE)
    $result = createSchedule($_POST);

    if ($result === true) {
        $_SESSION['message'] = "Sukses: Jadwal baru berhasil ditambahkan.";
        header("Location: schedule.php");
        exit;
    } else {
        // Simpan data form dan error ke session untuk ditampilkan kembali
        $_SESSION['form_data'] = $_POST;
        $_SESSION['form_errors'] = $result;
        $_SESSION['message'] = "Gagal: Terdapat kesalahan saat menambah jadwal.";
        header("Location: manage_jadwal.php");
        exit;
    }

} elseif ($action == 'update' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    // Aksi: PERBARUI JADWAL (UPDATE)
    $id_jadwal = $_POST['id_jadwal'] ?? null;
    
    if (!$id_jadwal) {
        $_SESSION['message'] = "Gagal: ID Jadwal tidak ditemukan.";
        header("Location: schedule.php");
        exit;
    }

    $result = updateSchedule($id_jadwal, $_POST);

    if ($result === true) {
        $_SESSION['message'] = "Sukses: Jadwal berhasil diperbarui.";
        header("Location: schedule.php");
        exit;
    } else {
        // Simpan data form dan error ke session untuk ditampilkan kembali
        $_SESSION['form_data'] = $_POST;
        $_SESSION['form_errors'] = $result;
        $_SESSION['message'] = "Gagal: Terdapat kesalahan saat memperbarui jadwal.";
        header("Location: manage_jadwal.php?id=" . $id_jadwal); // Kembali ke halaman edit
        exit;
    }

} elseif ($action == 'delete' && $_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['id'])) {
    // Aksi: HAPUS JADWAL (DELETE)
    $id_jadwal = $_GET['id'];
    
    // Ambil data untuk pesan sukses/gagal
    $schedule = getSchedules($id_jadwal);
    $schedule_title = $schedule ? htmlspecialchars($schedule['Judul_Film']) . " di " . htmlspecialchars($schedule['nama_studio']) : "Jadwal";

    $result = deleteSchedule($id_jadwal);

    if ($result === true) {
        $_SESSION['message'] = "Sukses: Jadwal **" . $schedule_title . "** berhasil dihapus.";
    } else {
        $_SESSION['message'] = "Gagal: Jadwal **" . $schedule_title . "** tidak dapat dihapus. " . ($result['general'] ?? '');
    }

    header("Location: schedule.php");
    exit;

} else {
    // Jika tidak ada aksi yang valid
    $_SESSION['message'] = "Gagal: Aksi tidak valid atau metode request salah.";
    header("Location: schedule.php");
    exit;
}
?>