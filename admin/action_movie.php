<?php
session_start();
// Pastikan user adalah admin
if (!isset($_SESSION['login']) || $_SESSION['role'] != '1') {
    header("Location: ../login.php");
    exit;
}

include "../koneksi.php";
include "../function.php"; // Memuat semua fungsi, termasuk CRUD Movie

// Tentukan aksi berdasarkan input POST atau GET
$action = $_REQUEST['action'] ?? null;

if ($action == 'create' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    // Aksi: TAMBAH FILM BARU (CREATE)
    $result = createMovie($_POST, $_FILES);

    if ($result === true) {
        $_SESSION['message'] = "Sukses: Film **" . htmlspecialchars($_POST['judul']) . "** berhasil ditambahkan.";
        header("Location: movies.php");
        exit;
    } else {
        // Simpan data form dan error ke session untuk ditampilkan kembali
        $_SESSION['form_data'] = $_POST;
        $_SESSION['form_errors'] = $result;
        $_SESSION['message'] = "Gagal: Terdapat kesalahan saat menambah film.";
        header("Location: manage_movie.php");
        exit;
    }

} elseif ($action == 'update' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    // Aksi: PERBARUI FILM (UPDATE)
    $id_movie = $_POST['id_movie'] ?? null;
    
    if (!$id_movie) {
        $_SESSION['message'] = "Gagal: ID Film tidak ditemukan.";
        header("Location: movies.php");
        exit;
    }

    $result = updateMovie($id_movie, $_POST, $_FILES);

    if ($result === true) {
        $_SESSION['message'] = "Sukses: Film **" . htmlspecialchars($_POST['judul']) . "** berhasil diperbarui.";
        header("Location: movies.php");
        exit;
    } else {
        // Simpan data form dan error ke session untuk ditampilkan kembali
        $_SESSION['form_data'] = $_POST;
        $_SESSION['form_errors'] = $result;
        $_SESSION['message'] = "Gagal: Terdapat kesalahan saat memperbarui film.";
        header("Location: manage_movie.php?id=" . $id_movie); // Kembali ke halaman edit
        exit;
    }

} elseif ($action == 'delete' && $_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['id'])) {
    // Aksi: HAPUS FILM (DELETE)
    $id_movie = $_GET['id'];
    
    // Ambil judul untuk pesan sukses/gagal
    $movie = getMovies($id_movie);
    $movie_title = $movie ? htmlspecialchars($movie['judul']) : "Film";

    $result = deleteMovie($id_movie);

    if ($result === true) {
        $_SESSION['message'] = "Sukses: Film **" . $movie_title . "** berhasil dihapus.";
    } else {
        $_SESSION['message'] = "Gagal: Film **" . $movie_title . "** tidak dapat dihapus. " . ($result['general'] ?? '');
    }

    header("Location: movies.php");
    exit;

} else {
    // Jika tidak ada aksi yang valid
    $_SESSION['message'] = "Gagal: Aksi tidak valid atau metode request salah.";
    header("Location: movies.php");
    exit;
}
?>