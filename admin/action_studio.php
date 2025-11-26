<?php
session_start();
// Pastikan user adalah admin
if (!isset($_SESSION['login']) || $_SESSION['role'] != '1') {
    header("Location: ../login.php");
    exit;
}

include "../koneksi.php";
include "../function.php"; // Memuat fungsi CRUD Studio (getStudios, createStudio, updateStudio, deleteStudio)

// Tentukan aksi berdasarkan input POST atau GET
$action = $_REQUEST['action'] ?? null;

if ($action == 'create' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    // Aksi: TAMBAH STUDIO BARU (CREATE)
    $result = createStudio($_POST);

    if ($result === true) {
        $_SESSION['message'] = "Sukses: Studio **" . htmlspecialchars($_POST['nama_studio']) . "** berhasil ditambahkan.";
        header("Location: manage_studio.php");
        exit;
    } else {
        // Simpan data form dan error ke session untuk ditampilkan kembali
        $_SESSION['form_data'] = $_POST;
        $_SESSION['form_errors'] = $result;
        $_SESSION['message'] = "Gagal: Terdapat kesalahan saat menambah studio.";
        header("Location: manage_studio.php");
        exit;
    }

} elseif ($action == 'update' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    // Aksi: PERBARUI STUDIO (UPDATE)
    $id_studio = $_POST['id_studio'] ?? null;
    
    if (!$id_studio) {
        $_SESSION['message'] = "Gagal: ID Studio tidak ditemukan.";
        header("Location: manage_studio.php");
        exit;
    }

    $result = updateStudio($id_studio, $_POST);

    if ($result === true) {
        $_SESSION['message'] = "Sukses: Studio **" . htmlspecialchars($_POST['nama_studio']) . "** berhasil diperbarui.";
        header("Location: manage_studio.php");
        exit;
    } else {
        // Simpan data form dan error ke session untuk ditampilkan kembali
        $_SESSION['form_data'] = $_POST;
        $_SESSION['form_errors'] = $result;
        $_SESSION['message'] = "Gagal: Terdapat kesalahan saat memperbarui studio.";
        header("Location: manage_studio.php?id=" . $id_studio); // Kembali ke halaman edit
        exit;
    }

} elseif ($action == 'delete' && $_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['id'])) {
    // Aksi: HAPUS STUDIO (DELETE)
    $id_studio = $_GET['id'];
    
    // Ambil nama studio untuk pesan sukses/gagal
    $studio = getStudios($id_studio);
    $studio_name = $studio ? htmlspecialchars($studio['nama_studio']) : "Studio";

    $result = deleteStudio($id_studio);

    if ($result === true) {
        $_SESSION['message'] = "Sukses: Studio **" . $studio_name . "** berhasil dihapus.";
    } else {
        // Pesan error dari deleteStudio sudah mencakup pengecekan jadwal terkait
        $_SESSION['message'] = "Gagal: Studio **" . $studio_name . "** tidak dapat dihapus. " . ($result['general'] ?? 'Kesalahan tidak diketahui.');
    }

    header("Location: manage_studio.php");
    exit;

} else {
    // Jika tidak ada aksi yang valid
    $_SESSION['message'] = "Gagal: Aksi tidak valid atau metode request salah.";
    header("Location: manage_studio.php");
    exit;
}
?>