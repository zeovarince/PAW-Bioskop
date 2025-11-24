<?php
include "koneksi.php";

// --- Fungsi Autentikasi dan Registrasi ---

function checklogin ($data){
    global $conn;
    $email = mysqli_real_escape_string($conn,$data['email']);
    $password = md5($data['password']);

    // --- DEBUGGING LINE START ---
    // Gunakan baris di bawah ini untuk melihat query yang dihasilkan di browser, 
    // jika login.php mengembalikan pesan error.
    // $debug_query = "SELECT * FROM users WHERE email ='$email' AND password= '$password' ";
    // error_log("Login Query: " . $debug_query); 
    // --- DEBUGGING LINE END ---

    $query = "SELECT * FROM users WHERE email ='$email' AND password= '$password' ";
    $result = mysqli_query($conn,$query);
    
    // Cek apakah ada error MySQL saat menjalankan query
    if (mysqli_error($conn)) {
        error_log("MySQL Error in checklogin: " . mysqli_error($conn));
        return "Terjadi kesalahan database. Cek log server.";
    }


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


// --- FUNGSI CRUD MOVIE (FILM) ---

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
        $old_path = "../assets/images/" . $old_poster; // Path relatif dari admin/
        if ($old_poster && file_exists($old_path)) {
            if (!unlink($old_path)) {
                // Log jika gagal menghapus file lama
                error_log("Gagal menghapus file lama: " . $old_path);
            }
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

    // Path lengkap ke file poster. Path ini relatif terhadap action_movie.php (di folder admin/)
    $file_path = "../assets/images/" . $movie['poster'];

    // Query DELETE
    $query = "DELETE FROM movies WHERE Id_movie = '$id'";

    if (mysqli_query($conn, $query)) {
        // Coba hapus file poster
        if ($movie['poster'] && file_exists($file_path)) {
            // Kita bungkus unlink dengan cek untuk melihat apakah penghapusan berhasil
            if (!unlink($file_path)) {
                 // Jika unlink gagal, log errornya (terlihat di error log XAMPP)
                error_log("Gagal menghapus file poster: " . $file_path);
            }
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

    // Pastikan folder assets/images ada. targetDir relatif terhadap file yang me-include function.php
    $targetDir = "../assets/images/";
    if (!is_dir($targetDir)) {
        // Jika folder belum ada, buat (dengan izin 0777 agar bisa diakses)
        if (!mkdir($targetDir, 0777, true)) {
             return ["poster" => "Gagal membuat folder upload (assets/images). Cek hak akses."];
        }
    }

    // Pindahkan file ke direktori tujuan
    if (move_uploaded_file($tmpName, $targetDir . $namaFileBaru)) {
        return $namaFileBaru;
    } else {
        return ["poster" => "Gagal memindahkan file. Cek hak akses folder assets/images/."];
    }
}

// --- FUNGSI CRUD STUDIO ---

/**
 * Mendapatkan semua data studio atau satu studio berdasarkan ID.
 * @param int|null $id Id_studio yang ingin diambil (jika null, ambil semua).
 * @return array|bool Data studio(s) atau false jika gagal.
 */
function getStudios($id = null) {
    global $conn;
    if ($id) {
        $id = mysqli_real_escape_string($conn, $id);
        $query = "SELECT * FROM studios WHERE Id_studio = '$id'";
        $result = mysqli_query($conn, $query);
        return mysqli_fetch_assoc($result);
    } else {
        $query = "SELECT * FROM studios ORDER BY nama_studio ASC";
        $result = mysqli_query($conn, $query);
        $studios = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $studios[] = $row;
        }
        return $studios;
    }
}

/**
 * Menambah studio baru ke database.
 * @param array $data Data dari form.
 * @return array|bool Error array atau true jika berhasil.
 */
function createStudio($data) {
    global $conn;
    $nama_studio = mysqli_real_escape_string($conn, $data['nama_studio']);
    $capacity = (int)$data['capacity'];
    $total_baris = (int)$data['total_baris'];
    $total_kursi_per_baris = (int)$data['total_kursi_per_baris'];

    // Validasi dasar
    if (empty($nama_studio) || $capacity <= 0 || $total_baris <= 0 || $total_kursi_per_baris <= 0) {
        return ["general" => "Semua kolom wajib diisi dan harus lebih dari nol."];
    }

    // Validasi Kapasitas
    if ($capacity != ($total_baris * $total_kursi_per_baris)) {
        return ["capacity" => "Kapasitas harus sama dengan (Total Baris x Kursi per Baris). (yaitu " . ($total_baris * $total_kursi_per_baris) . " kursi)"];
    }

    // Query INSERT
    $query = "INSERT INTO studios (nama_studio, capacity, total_baris, total_kursi_per_baris) 
              VALUES ('$nama_studio', '$capacity', '$total_baris', '$total_kursi_per_baris')";
    
    if (mysqli_query($conn, $query)) {
        return true;
    } else {
        return ["general" => "Gagal menambahkan studio: " . mysqli_error($conn)];
    }
}

/**
 * Memperbarui data studio.
 * @param int $id Id_studio yang akan diubah.
 * @param array $data Data dari form.
 * @return array|bool Error array atau true jika berhasil.
 */
function updateStudio($id, $data) {
    global $conn;
    $id = mysqli_real_escape_string($conn, $id);
    $nama_studio = mysqli_real_escape_string($conn, $data['nama_studio']);
    $capacity = (int)$data['capacity'];
    $total_baris = (int)$data['total_baris'];
    $total_kursi_per_baris = (int)$data['total_kursi_per_baris'];

    // Validasi dasar
    if (empty($nama_studio) || $capacity <= 0 || $total_baris <= 0 || $total_kursi_per_baris <= 0) {
        return ["general" => "Semua kolom wajib diisi dan harus lebih dari nol."];
    }

    // Validasi Kapasitas
    if ($capacity != ($total_baris * $total_kursi_per_baris)) {
        return ["capacity" => "Kapasitas harus sama dengan (Total Baris x Kursi per Baris). (yaitu " . ($total_baris * $total_kursi_per_baris) . " kursi)"];
    }
    
    // Query UPDATE
    $query = "UPDATE studios SET 
              nama_studio = '$nama_studio', 
              capacity = '$capacity', 
              total_baris = '$total_baris', 
              total_kursi_per_baris = '$total_kursi_per_baris' 
              WHERE Id_studio = '$id'";
    
    if (mysqli_query($conn, $query)) {
        return true;
    } else {
        return ["general" => "Gagal memperbarui studio: " . mysqli_error($conn)];
    }
}

/**
 * Menghapus studio dari database.
 * @param int $id Id_studio yang akan dihapus.
 * @return array|bool Error array atau true jika berhasil.
 */
function deleteStudio($id) {
    global $conn;
    $id = mysqli_real_escape_string($conn, $id);
    
    // Periksa apakah studio ini masih memiliki jadwal tayang terkait
    $cek_jadwal = mysqli_query($conn, "SELECT COUNT(*) as total FROM jadwal WHERE Id_studio = '$id'");
    $count = mysqli_fetch_assoc($cek_jadwal)['total'];

    if ($count > 0) {
        return ["general" => "Gagal menghapus studio. Studio ini memiliki " . $count . " jadwal tayang terkait. Hapus jadwalnya terlebih dahulu."];
    }

    $query = "DELETE FROM studios WHERE Id_studio = '$id'";

    if (mysqli_query($conn, $query)) {
        return true;
    } else {
        return ["general" => "Gagal menghapus studio: " . mysqli_error($conn)];
    }
}

// --- FUNGSI CRUD JADWAL (BARU) ---

/**
 * Mendapatkan semua data jadwal dengan detail film dan studio.
 * @param int|null $id Id_jadwal yang ingin diambil (jika null, ambil semua).
 * @return array|bool Data jadwal(s) atau false jika gagal.
 */
function getSchedules($id = null) {
    global $conn;
    
    $query = "
        SELECT 
            j.Id_jadwal,
            j.Waktu_tayang, 
            j.harga,
            m.judul AS Judul_Film, 
            s.nama_studio,
            j.Id_movie,
            j.Id_studio
        FROM jadwal j
        JOIN movies m ON j.Id_movie = m.Id_movie
        JOIN studios s ON j.Id_studio = s.Id_studio
    ";
    
    if ($id) {
        $id = mysqli_real_escape_string($conn, $id);
        $query .= " WHERE j.Id_jadwal = '$id'";
        $result = mysqli_query($conn, $query);
        return mysqli_fetch_assoc($result);
    } else {
        $query .= " ORDER BY j.Waktu_tayang ASC";
        $result = mysqli_query($conn, $query);
        $schedules = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $schedules[] = $row;
        }
        return $schedules;
    }
}

/**
 * Menambah jadwal baru ke database.
 * @param array $data Data dari form.
 * @return array|bool Error array atau true jika berhasil.
 */
function createSchedule($data) {
    global $conn;
    $id_movie = (int)$data['Id_movie'];
    $id_studio = (int)$data['Id_studio'];
    $waktu_tayang = mysqli_real_escape_string($conn, $data['Waktu_tayang']);
    $harga = (float)$data['harga'];

    // Validasi dasar
    if (empty($id_movie) || empty($id_studio) || empty($waktu_tayang) || $harga <= 0) {
        return ["general" => "Semua kolom wajib diisi dan harga harus lebih dari nol."];
    }
    
    // Validasi waktu (tidak boleh di masa lalu)
    if (strtotime($waktu_tayang) < time()) {
        return ["Waktu_tayang" => "Waktu tayang tidak boleh di masa lalu."];
    }

    // Validasi Ketersediaan Studio dan Waktu
    // Cek apakah studio ini sudah ada jadwal lain pada waktu yang berdekatan
    // Kita asumsikan dua jadwal tidak boleh overlap dalam 2 jam (misal: durasi film terpanjang)
    $waktu_mulai = date('Y-m-d H:i:s', strtotime($waktu_tayang));
    $waktu_akhir = date('Y-m-d H:i:s', strtotime($waktu_tayang . ' + 120 minutes')); // Tambahkan buffer 2 jam

    $cek_overlap_query = "
        SELECT COUNT(*) as total 
        FROM jadwal 
        WHERE Id_studio = '$id_studio' 
        AND Waktu_tayang < '$waktu_akhir' 
        AND DATE_ADD(Waktu_tayang, INTERVAL 120 MINUTE) > '$waktu_mulai'
    ";
    $cek_overlap = mysqli_query($conn, $cek_overlap_query);
    $overlap_count = mysqli_fetch_assoc($cek_overlap)['total'];

    if ($overlap_count > 0) {
        return ["Waktu_tayang" => "Studio ini sudah ada jadwal tayang lain yang tumpang tindih pada waktu tersebut."];
    }


    // Query INSERT
    $query = "INSERT INTO jadwal (Id_movie, Id_studio, Waktu_tayang, harga) 
              VALUES ('$id_movie', '$id_studio', '$waktu_tayang', '$harga')";
    
    if (mysqli_query($conn, $query)) {
        return true;
    } else {
        return ["general" => "Gagal menambahkan jadwal: " . mysqli_error($conn)];
    }
}

/**
 * Memperbarui data jadwal.
 * @param int $id Id_jadwal yang akan diubah.
 * @param array $data Data dari form.
 * @return array|bool Error array atau true jika berhasil.
 */
function updateSchedule($id, $data) {
    global $conn;
    $id = mysqli_real_escape_string($conn, $id);
    $id_movie = (int)$data['Id_movie'];
    $id_studio = (int)$data['Id_studio'];
    $waktu_tayang = mysqli_real_escape_string($conn, $data['Waktu_tayang']);
    $harga = (float)$data['harga'];

    // Validasi dasar
    if (empty($id_movie) || empty($id_studio) || empty($waktu_tayang) || $harga <= 0) {
        return ["general" => "Semua kolom wajib diisi dan harga harus lebih dari nol."];
    }

    // Validasi waktu (tidak boleh di masa lalu)
    if (strtotime($waktu_tayang) < time()) {
        return ["Waktu_tayang" => "Waktu tayang tidak boleh di masa lalu."];
    }

    // Validasi Ketersediaan Studio dan Waktu (cek overlap, kecuali dirinya sendiri)
    $waktu_mulai = date('Y-m-d H:i:s', strtotime($waktu_tayang));
    $waktu_akhir = date('Y-m-d H:i:s', strtotime($waktu_tayang . ' + 120 minutes')); // Tambahkan buffer 2 jam

    $cek_overlap_query = "
        SELECT COUNT(*) as total 
        FROM jadwal 
        WHERE Id_studio = '$id_studio' 
        AND Id_jadwal != '$id'
        AND Waktu_tayang < '$waktu_akhir' 
        AND DATE_ADD(Waktu_tayang, INTERVAL 120 MINUTE) > '$waktu_mulai'
    ";
    $cek_overlap = mysqli_query($conn, $cek_overlap_query);
    $overlap_count = mysqli_fetch_assoc($cek_overlap)['total'];

    if ($overlap_count > 0) {
        return ["Waktu_tayang" => "Studio ini sudah ada jadwal tayang lain yang tumpang tindih pada waktu tersebut."];
    }
    
    // Query UPDATE
    $query = "UPDATE jadwal SET 
              Id_movie = '$id_movie', 
              Id_studio = '$id_studio', 
              Waktu_tayang = '$waktu_tayang', 
              harga = '$harga'
              WHERE Id_jadwal = '$id'";
    
    if (mysqli_query($conn, $query)) {
        return true;
    } else {
        return ["general" => "Gagal memperbarui jadwal: " . mysqli_error($conn)];
    }
}

/**
 * Menghapus jadwal dari database.
 * @param int $id Id_jadwal yang akan dihapus.
 * @return array|bool Error array atau true jika berhasil.
 */
function deleteSchedule($id) {
    global $conn;
    $id = mysqli_real_escape_string($conn, $id);
    
    // Cek apakah jadwal ini masih memiliki booking yang aktif (status 1=confirmed atau 2=pending)
    $cek_booking = mysqli_query($conn, "SELECT COUNT(*) as total FROM booking WHERE Id_jadwal = '$id' AND status_booking IN ('1', '2')");
    $count = mysqli_fetch_assoc($cek_booking)['total'];

    if ($count > 0) {
        return ["general" => "Gagal menghapus jadwal. Jadwal ini memiliki " . $count . " booking yang masih aktif (confirmed/pending)."];
    }

    $query = "DELETE FROM jadwal WHERE Id_jadwal = '$id'";

    if (mysqli_query($conn, $query)) {
        return true;
    } else {
        return ["general" => "Gagal menghapus jadwal: " . mysqli_error($conn)];
    }
}
?>