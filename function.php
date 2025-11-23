<?php
include "koneksi.php";

// --- Fungsi Autentikasi dan Registrasi (Sudah Ada) ---

function checklogin ($data){
    global $conn;
    $email = mysqli_real_escape_string($conn,$data['email']);
    $password = md5($data['password']);

    $query = "SELECT * FROM users WHERE email ='$email' AND password= '$password' ";
    $result = mysqli_query($conn,$query);

    if (mysqli_num_rows($result) > 0){
        $user = mysqli_fetch_assoc($result);
        $_SESSION['login'] = true;
        $_SESSION['user_id'] = $user['Id_user'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['role'] = ($user['role']);

        if ($user ['role'] == '1'){
            header("location: admin/index.php");
        }else{
            header("location: custommer/index.php");
        }
        exit;

    }else { 
        return "email dan password anda salah";
    }
}

function validateEmail(&$errors, $field_list, $field_name){
    $email = strtolower(trim($field_list[$field_name] ?? ''));
    if (empty($email)) {
        $errors[$field_name] = 'Email wajib diisi.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[$field_name] = 'Format email tidak valid.';
    }
}
function validatePassword(&$errors, $field_list, $field_name){
    $password = $field_list[$field_name] ?? '';
    if (empty($password)) {
        $errors[$field_name] = 'Password wajib diisi.';
    } elseif (strlen($password) < 6) {
        $errors[$field_name] = 'Password minimal 6 karakter.';
    } elseif (!preg_match("/[A-Z]/", $password)) {
        $errors[$field_name] = 'Password harus ada huruf besar.';
    }
}
function validateName(&$errors, $field_list, $field_name){
    $pattern = "/^[a-zA-Z' -]+$/";
    if (empty(trim($field_list[$field_name] ?? ''))) {
        $errors[$field_name] = 'Nama wajib diisi.';
    } elseif (!preg_match($pattern, $field_list[$field_name])) {
        $errors[$field_name] = 'Nama hanya boleh huruf dan spasi.';
    }
}

function register($data){
    global $conn;
    $username = mysqli_real_escape_string($conn, $data['username']);
    $email = mysqli_real_escape_string($conn, $data['email']);
    $password = mysqli_real_escape_string($conn, $data['password']);
    $confirm_password = mysqli_real_escape_string($conn, $data['confirm_password']);

    $error = [];
    validateName($error,$data,'username');
    validateEmail($error,$data,'email');
    validatePassword($error,$data,'password');

    if ($password !== $confirm_password) {
        $error['confirm_password'] = "Konfirmasi password tidak sesuai!";
    }

    $cek_email = mysqli_query($conn, "SELECT email FROM users WHERE email = '$email'");
    if (mysqli_num_rows($cek_email) > 0){
        $error['email'] = "Email sudah terdaftar! Silakan login.";
    }

    if (!empty($error)) {
        return $error;
    }
    $pw=md5($password);

    $tambah_user = "INSERT INTO users (username, email, password) VALUES ('$username', '$email', '$pw')";
    if (mysqli_query($conn, $tambah_user)) {
        return true;
    } else {
        return ["general" => "Gagal mendaftar: " . mysqli_error($conn)];
    }
}


// --- FUNGSI BARU UNTUK CRUD MOVIE (FILM) ---

/**
 * Mendapatkan semua data film atau satu film berdasarkan ID.
 * @param int|null $id Id_movie yang ingin diambil (jika null, ambil semua).
 * @return array|bool Data film(s) atau false jika gagal.
 */
function getMovies($id = null) {
    global $conn;
    if ($id) {
        $id = mysqli_real_escape_string($conn, $id);
        $query = "SELECT * FROM movies WHERE Id_movie = '$id'";
        $result = mysqli_query($conn, $query);
        return mysqli_fetch_assoc($result);
    } else {
        $query = "SELECT * FROM movies ORDER BY release_date DESC, judul ASC";
        $result = mysqli_query($conn, $query);
        $movies = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $movies[] = $row;
        }
        return $movies;
    }
}

/**
 * Menambah film baru ke database.
 * @param array $data Data dari form.
 * @param array $file Data file poster.
 * @return array|bool Error array atau true jika berhasil.
 */
function createMovie($data, $file) {
    global $conn;
    $judul = mysqli_real_escape_string($conn, $data['judul']);
    $description = mysqli_real_escape_string($conn, $data['description']);
    $duration = (int)$data['duration']; // Pastikan integer
    $release_date = mysqli_real_escape_string($conn, $data['release_date']);

    // Validasi input
    if (empty($judul) || empty($description) || empty($duration) || empty($release_date)) {
        return ["general" => "Semua kolom wajib diisi."];
    }
    if ($duration <= 0) {
        return ["duration" => "Durasi harus lebih dari 0 menit."];
    }

    $poster = uploadPoster($file);
    if (is_array($poster)) { // Berarti ada error saat upload
        return $poster;
    }
    
    // Query INSERT
    $query = "INSERT INTO movies (judul, description, duration, poster, release_date) 
              VALUES ('$judul', '$description', '$duration', '$poster', '$release_date')";
    
    if (mysqli_query($conn, $query)) {
        return true;
    } else {
        return ["general" => "Gagal menambahkan film: " . mysqli_error($conn)];
    }
}

/**
 * Memperbarui data film.
 * @param int $id Id_movie yang akan diubah.
 * @param array $data Data dari form.
 * @param array $file Data file poster.
 * @return array|bool Error array atau true jika berhasil.
 */
function updateMovie($id, $data, $file) {
    global $conn;
    $id = mysqli_real_escape_string($conn, $id);
    $judul = mysqli_real_escape_string($conn, $data['judul']);
    $description = mysqli_real_escape_string($conn, $data['description']);
    $duration = (int)$data['duration'];
    $release_date = mysqli_real_escape_string($conn, $data['release_date']);
    $old_poster = mysqli_real_escape_string($conn, $data['old_poster']);

    // Validasi input
    if (empty($judul) || empty($description) || empty($duration) || empty($release_date)) {
        return ["general" => "Semua kolom wajib diisi."];
    }
    if ($duration <= 0) {
        return ["duration" => "Durasi harus lebih dari 0 menit."];
    }

    $poster_name = $old_poster;

    // Cek apakah ada file baru yang diupload
    if ($file['poster']['error'] === 0) {
        // Upload poster baru
        $new_poster = uploadPoster($file);
        if (is_array($new_poster)) {
            return $new_poster; // Upload error
        }
        
        // Hapus poster lama jika berhasil upload poster baru
        if ($old_poster && file_exists("assets/images/" . $old_poster)) {
            unlink("assets/images/" . $old_poster);
        }
        $poster_name = $new_poster;

    } 
    
    // Query UPDATE
    $query = "UPDATE movies SET 
              judul = '$judul', 
              description = '$description', 
              duration = '$duration', 
              poster = '$poster_name', 
              release_date = '$release_date' 
              WHERE Id_movie = '$id'";
    
    if (mysqli_query($conn, $query)) {
        return true;
    } else {
        return ["general" => "Gagal memperbarui film: " . mysqli_error($conn)];
    }
}

/**
 * Menghapus film dari database dan file posternya.
 * @param int $id Id_movie yang akan dihapus.
 * @return array|bool Error array atau true jika berhasil.
 */
function deleteMovie($id) {
    global $conn;
    $id = mysqli_real_escape_string($conn, $id);
    
    // Ambil nama poster untuk dihapus
    $movie = getMovies($id);
    if (!$movie) {
        return ["general" => "Film tidak ditemukan."];
    }

    // Query DELETE
    $query = "DELETE FROM movies WHERE Id_movie = '$id'";

    if (mysqli_query($conn, $query)) {
        // Hapus file poster
        if ($movie['poster'] && file_exists("assets/images/" . $movie['poster'])) {
            unlink("assets/images/" . $movie['poster']);
        }
        return true;
    } else {
        return ["general" => "Gagal menghapus film: " . mysqli_error($conn)];
    }
}

/**
 * Fungsi untuk mengupload file poster.
 * @param array $file Data file poster.
 * @return string|array Nama file yang tersimpan atau array error.
 */
function uploadPoster($file) {
    $namaFile = $file['poster']['name'];
    $ukuranFile = $file['poster']['size'];
    $error = $file['poster']['error'];
    $tmpName = $file['poster']['tmp_name'];

    // Cek apakah tidak ada gambar yang diupload
    if ($error === 4) {
        // Jika untuk CREATE, ini error. Jika untuk UPDATE, ini bisa diabaikan.
        return ["poster" => "Pilih gambar poster terlebih dahulu."];
    }

    // Cek apakah yang diupload adalah gambar
    $ekstensiValid = ['jpg', 'jpeg', 'png'];
    $ekstensiGambar = explode('.', $namaFile);
    $ekstensiGambar = strtolower(end($ekstensiGambar));

    if (!in_array($ekstensiGambar, $ekstensiValid)) {
        return ["poster" => "Yang Anda upload bukan gambar. Hanya JPG, JPEG, PNG."];
    }

    // Cek jika ukurannya terlalu besar (misal max 2MB)
    if ($ukuranFile > 2000000) { 
        return ["poster" => "Ukuran gambar terlalu besar (Maks 2MB)."];
    }

    // Lolos pengecekan, generate nama baru
    $namaFileBaru = uniqid() . '.' . $ekstensiGambar;

    // Pastikan folder assets/images ada. Jika tidak ada, buat folder.
    $targetDir = "../assets/images/";
    if (!is_dir($targetDir)) {
        mkdir($targetDir, 0777, true);
    }

    // Pindahkan file ke direktori tujuan
    if (move_uploaded_file($tmpName, $targetDir . $namaFileBaru)) {
        return $namaFileBaru;
    } else {
        return ["poster" => "Gagal memindahkan file."];
    }
}

?>