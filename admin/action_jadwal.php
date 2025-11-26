<?php
session_start();
if (!isset($_SESSION['login']) || $_SESSION['role'] != '1') {
    header("Location: ../login.php");
    exit;
}

include "../koneksi.php";
include "../function.php"; 

$action = $_REQUEST['action'] ?? null;

// GABUNGKAN TANGGAL DAN JAM SEBELUM DIPROSES
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['tanggal']) && isset($_POST['jam'])) {
    $_POST['Waktu_tayang'] = $_POST['tanggal'] . ' ' . $_POST['jam'] . ':00';
}

if ($action == 'create' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $result = createSchedule($_POST);

    if ($result === true) {
        $_SESSION['message'] = "Sukses: Jadwal baru berhasil ditambahkan.";
        header("Location: schedule.php");
        exit;
    } else {
        // Pecah kembali tanggal dan jam untuk form jika error
        if(isset($_POST['Waktu_tayang'])) {
            $ts = strtotime($_POST['Waktu_tayang']);
            $_POST['tanggal'] = date('Y-m-d', $ts);
            $_POST['jam'] = date('H:i', $ts);
        }
        
        $_SESSION['form_data'] = $_POST;
        $_SESSION['form_errors'] = $result;
        $_SESSION['message'] = "Gagal: Terdapat kesalahan saat menambah jadwal.";
        header("Location: manage_jadwal.php");
        exit;
    }

} elseif ($action == 'update' && $_SERVER['REQUEST_METHOD'] === 'POST') {
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
        if(isset($_POST['Waktu_tayang'])) {
            $ts = strtotime($_POST['Waktu_tayang']);
            $_POST['tanggal'] = date('Y-m-d', $ts);
            $_POST['jam'] = date('H:i', $ts);
        }

        $_SESSION['form_data'] = $_POST;
        $_SESSION['form_errors'] = $result;
        $_SESSION['message'] = "Gagal: Terdapat kesalahan saat memperbarui jadwal.";
        header("Location: manage_jadwal.php?id=" . $id_jadwal); 
        exit;
    }

} elseif ($action == 'delete' && $_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['id'])) {
    $id_jadwal = $_GET['id'];
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
    $_SESSION['message'] = "Gagal: Aksi tidak valid.";
    header("Location: schedule.php");
    exit;
}
?>