<?php
if (session_status() == PHP_SESSION_NONE) session_start();
include "koneksi.php";

// --- HELPER FUNCTION (Biar kodingan lebih pendek) ---
function esc($str) { global $conn; return mysqli_real_escape_string($conn, trim($str)); }

// --- AUTHENTICATION ---
function checklogin($data) {
    global $conn;
    $email = esc($data['email']);
    $password = md5($data['password']);

    $res = mysqli_query($conn, "SELECT * FROM users WHERE email ='$email' AND password='$password'");
    if (mysqli_num_rows($res) > 0) {
        $u = mysqli_fetch_assoc($res);
        $_SESSION = ['login' => true, 'user_id' => $u['Id_user'], 'username' => $u['username'], 'role' => $u['role']];
        header("location: " . ($u['role'] == '1' ? "admin/index.php" : "index.php")); 
        exit;
    }
    return "Email atau password salah.";
}

function register($data) {
    global $conn;
    $user = esc($data['username']);
    $email = esc($data['email']);
    $pass = $data['password'];

    // Validasi Simple
    $err = [];
    if (empty($user) || !preg_match("/^[a-zA-Z' -]+$/", $user)) $err['username'] = "Nama wajib isi & hanya huruf.";
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) $err['email'] = "Format email salah.";
    if (strlen($pass) < 6 || !preg_match("/[A-Z]/", $pass)) $err['password'] = "Pass min 6 kar & 1 huruf besar.";
    if ($pass !== $data['confirm_password']) $err['confirm_password'] = "Konfirmasi password beda.";
    
    if (mysqli_num_rows(mysqli_query($conn, "SELECT email FROM users WHERE email='$email'")) > 0) 
        $err['email'] = "Email sudah terdaftar.";

    if (!empty($err)) return $err;

    $pw_hash = md5($pass);
    return mysqli_query($conn, "INSERT INTO users (username, email, password) VALUES ('$user', '$email', '$pw_hash')") ? true : ["general" => "Gagal: ".mysqli_error($conn)];
}

// --- CRUD MOVIES ---
function getMovies($id = null) {
    global $conn;
    $sql = "SELECT * FROM movies";
    if ($id) {
        $res = mysqli_query($conn, "$sql WHERE Id_movie = '".esc($id)."'");
        return mysqli_fetch_assoc($res);
    }
    $res = mysqli_query($conn, "$sql ORDER BY release_date DESC");
    $data = []; while($r = mysqli_fetch_assoc($res)) $data[] = $r;
    return $data;
}

function createMovie($data, $file) {
    global $conn;
    $judul = esc($data['judul']); $desc = esc($data['description']); $dur = (int)$data['duration']; $date = esc($data['release_date']);

    if (!$judul || !$desc || !$dur || !$date) return ["general" => "Semua kolom wajib diisi."];
    if (is_array($poster = uploadPoster($file))) return $poster;

    $q = "INSERT INTO movies (judul, description, duration, poster, release_date) VALUES ('$judul', '$desc', '$dur', '$poster', '$date')";
    return mysqli_query($conn, $q) ? true : ["general" => "Gagal: ".mysqli_error($conn)];
}

function updateMovie($id, $data, $file) {
    global $conn;
    $id = esc($id); $judul = esc($data['judul']); $desc = esc($data['description']); $dur = (int)$data['duration']; $date = esc($data['release_date']); $poster = esc($data['old_poster']);

    if (!$judul || !$desc || !$dur || !$date) return ["general" => "Semua kolom wajib diisi."];

    if ($file['poster']['error'] === 0) {
        if (is_array($new = uploadPoster($file))) return $new;
        if ($poster && file_exists("../assets/images/$poster")) unlink("../assets/images/$poster");
        $poster = $new;
    }

    $q = "UPDATE movies SET judul='$judul', description='$desc', duration='$dur', poster='$poster', release_date='$date' WHERE Id_movie='$id'";
    return mysqli_query($conn, $q) ? true : ["general" => "Gagal: ".mysqli_error($conn)];
}

function deleteMovie($id) {
    global $conn;
    $m = getMovies($id);
    if (!$m) return ["general" => "Film tidak ditemukan."];
    
    if (mysqli_query($conn, "DELETE FROM movies WHERE Id_movie = '".esc($id)."'")) {
        if ($m['poster'] && file_exists("../assets/images/".$m['poster'])) unlink("../assets/images/".$m['poster']);
        return true;
    }
    return ["general" => "Gagal hapus: ".mysqli_error($conn)];
}

function uploadPoster($file) {
    $f = $file['poster'];
    if ($f['error'] == 4) return ["poster" => "Pilih gambar."];
    
    $ext = strtolower(pathinfo($f['name'], PATHINFO_EXTENSION));
    if (!in_array($ext, ['jpg', 'jpeg', 'png'])) return ["poster" => "Hanya JPG/PNG."];
    if ($f['size'] > 2000000) return ["poster" => "Max 2MB."];

    $newName = uniqid() . '.' . $ext;
    $dir = "../assets/images/";
    if (!is_dir($dir)) mkdir($dir, 0777, true);

    return move_uploaded_file($f['tmp_name'], $dir . $newName) ? $newName : ["poster" => "Gagal upload."];
}

// --- CRUD STUDIO ---
function getStudios($id = null) {
    global $conn;
    $sql = "SELECT * FROM studios";
    if ($id) return mysqli_fetch_assoc(mysqli_query($conn, "$sql WHERE Id_studio='".esc($id)."'"));
    
    $res = mysqli_query($conn, "$sql ORDER BY nama_studio ASC");
    $data = []; while($r = mysqli_fetch_assoc($res)) $data[] = $r;
    return $data;
}

function createStudio($data) {
    global $conn;
    $nama = esc($data['nama_studio']); $cap = (int)$data['capacity']; $row = (int)$data['total_baris']; $seat = (int)$data['total_kursi_per_baris'];

    if (!$nama || $cap <= 0 || $row <= 0 || $seat <= 0) return ["general" => "Isi semua data dengan benar."];
    if ($cap != ($row * $seat)) return ["capacity" => "Kapasitas harus = Baris x Kursi (" . ($row * $seat) . ")"];

    return mysqli_query($conn, "INSERT INTO studios (nama_studio, capacity, total_baris, total_kursi_per_baris) VALUES ('$nama', '$cap', '$row', '$seat')") ? true : ["general" => "Gagal: ".mysqli_error($conn)];
}

function updateStudio($id, $data) {
    global $conn;
    $id = esc($id); $nama = esc($data['nama_studio']); $cap = (int)$data['capacity']; $row = (int)$data['total_baris']; $seat = (int)$data['total_kursi_per_baris'];

    if (!$nama || $cap <= 0) return ["general" => "Data tidak valid."];
    if ($cap != ($row * $seat)) return ["capacity" => "Kapasitas salah hitung."];

    return mysqli_query($conn, "UPDATE studios SET nama_studio='$nama', capacity='$cap', total_baris='$row', total_kursi_per_baris='$seat' WHERE Id_studio='$id'") ? true : ["general" => "Gagal update."];
}

function deleteStudio($id) {
    global $conn; $id = esc($id);
    $cek = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as t FROM jadwal WHERE Id_studio='$id'"))['t'];
    if ($cek > 0) return ["general" => "Gagal: Masih ada $cek jadwal di studio ini."];
    
    return mysqli_query($conn, "DELETE FROM studios WHERE Id_studio='$id'") ? true : ["general" => "Gagal hapus."];
}

// --- CRUD JADWAL ---
function getSchedules($id = null) {
    global $conn;
    $sql = "SELECT j.*, m.judul AS Judul_Film, s.nama_studio FROM jadwal j JOIN movies m ON j.Id_movie = m.Id_movie JOIN studios s ON j.Id_studio = s.Id_studio";
    
    if ($id) return mysqli_fetch_assoc(mysqli_query($conn, "$sql WHERE j.Id_jadwal='".esc($id)."'"));
    
    $res = mysqli_query($conn, "$sql ORDER BY j.Waktu_tayang ASC");
    $data = []; while($r = mysqli_fetch_assoc($res)) $data[] = $r;
    return $data;
}

function createSchedule($data) { return saveSchedule($data); }
function updateSchedule($id, $data) { return saveSchedule($data, $id); }

function saveSchedule($data, $id = null) {
    global $conn;
    $mov = (int)$data['Id_movie']; $std = (int)$data['Id_studio']; $time = esc($data['Waktu_tayang']); $price = (float)$data['harga'];

    if (!$mov || !$std || !$time || $price <= 0) return ["general" => "Lengkapi data."];
    if (strtotime($time) < time()) return ["Waktu_tayang" => "Waktu sudah lewat."];

    // Cek Bentrok (2 Jam)
    $start = date('Y-m-d H:i:s', strtotime($time));
    $end = date('Y-m-d H:i:s', strtotime($time . ' + 120 minutes'));
    
    $sql_check = "SELECT COUNT(*) as t FROM jadwal WHERE Id_studio='$std' AND Waktu_tayang < '$end' AND DATE_ADD(Waktu_tayang, INTERVAL 120 MINUTE) > '$start'";
    if ($id) $sql_check .= " AND Id_jadwal != '".esc($id)."'";
    
    if (mysqli_fetch_assoc(mysqli_query($conn, $sql_check))['t'] > 0) return ["Waktu_tayang" => "Jadwal bentrok di studio ini."];

    $q = $id ? "UPDATE jadwal SET Id_movie='$mov', Id_studio='$std', Waktu_tayang='$time', harga='$price' WHERE Id_jadwal='".esc($id)."'" 
             : "INSERT INTO jadwal (Id_movie, Id_studio, Waktu_tayang, harga) VALUES ('$mov', '$std', '$time', '$price')";
    
    return mysqli_query($conn, $q) ? true : ["general" => "Gagal simpan: ".mysqli_error($conn)];
}

function deleteSchedule($id) {
    global $conn; $id = esc($id);
    $cek = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as t FROM booking WHERE Id_jadwal='$id' AND status_booking IN ('1','2')"))['t'];
    if ($cek > 0) return ["general" => "Gagal: Ada $cek booking aktif."];
    
    return mysqli_query($conn, "DELETE FROM jadwal WHERE Id_jadwal='$id'") ? true : ["general" => "Gagal hapus."];
}
?>