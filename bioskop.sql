-- INI DI IMPORT KE PHP MY ADMIN KALIAN
CREATE DATABASE db_bioskop;

USE db_bioskop;

-- Tabel Users
CREATE TABLE users (
    Id_user INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(50) NOT NULL,
    email VARCHAR(100) NOT NULL,
    password VARCHAR(100) NOT NULL,
    role CHAR(1) NOT NULL DEFAULT '2' COMMENT '1=admin, 2=user',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Tabel Studios
CREATE TABLE studios (
    Id_studio INT PRIMARY KEY AUTO_INCREMENT,
    nama_studio VARCHAR(50) NOT NULL,
    capacity INT NOT NULL,
    total_baris INT NOT NULL,
    total_kursi_per_baris INT NOT NULL
);

INSERT INTO `studios` (`Id_studio`, `nama_studio`, `capacity`, `total_baris`, `total_kursi_per_baris`) VALUES
(1, 'Regular 2D', 20, 4, 5),
(2, 'Dolby Atmos', 0, 10, 120),
(3, 'IMAX', 0, 10, 120);

-- Tabel Movies
CREATE TABLE movies (
    Id_movie INT PRIMARY KEY AUTO_INCREMENT,
    judul VARCHAR(100) NOT NULL,
    description TEXT,
    duration INT NOT NULL,
    poster VARCHAR(255),
    release_date DATE
);

INSERT INTO `movies` (`Id_movie`, `judul`, `rating`, `genre`, `description`, `duration`, `director`, `poster`, `release_date`, `age_rating`) VALUES
(1, 'Pangku', '8.7', 'Drama, Romance', 'Sartika, seorang perempuan muda yang hamil, meninggalkan kampung halamannya untuk mencari harapan di Pantura, dan bertemu Maya yang membantunya melalui kehidupan baru penuh tantangan dan cinta.', 120, 'Reza Rahadian', 'pangku.jpg', '2025-11-06', '17+'),
(2, 'Kuncen', '7.9', 'Horror, Misteri', 'Awindya nekat mendaki Gunung Merapi untuk mencari Devlin yang hilang, namun kekuatan gelap pegunungan mulai mengancam jiwa mereka.', 105, 'Jose Poernomo', 'kuncen.jpg', '2025-11-06', '17+'),
(3, 'Sosok Ketiga: Lintrik', '8.2', 'Horor, Drama', 'Andin merasa ada kekuatan gelap dalam rumah tangganya setelah enam bulan menikah; Naura menggunakan ilmu pelet “Lintrik” yang menghantui kehidupan Andin & Aryo.', 110, 'Fajar Nugros', 'sosok_ketiga_lintrik.jpg', '2025-11-06', '17+'),
(4, 'Solata', '7.5', 'Drama, Religi', 'Angkasa menjadi relawan guru di Tana Toraja dan bertemu murid-murid yang mengubah pandangannya tentang kehidupan dan makna doa.', 110, 'Ody C. Harahap', 'solata.jpg', '2025-11-06', 'all'),
(5, 'Dopamin', '9.0', 'Drama, Thriller', 'Malik dan Alya menghadapi krisis rumah tangga dan keuangan setelah Malik di-PHK; sebuah kejadian misterius terjadi saat seorang tamu asing datang dengan koper miliaran.', 95, 'Teddy Soeriaatmadja', 'dopamin.jpg', '2025-11-13', '13+'),
(6, 'Wicked: For Good', '8.8', 'Musikal, Fantasi', 'Elphaba dan Glinda menghadapi pilihan moral dan ambisi dalam dunia sihir; konflik persahabatan dan kekuasaan mewarnai kisah mereka.', 137, 'Jon M.Chu', 'wicked_for_good.jpg', '2025-11-19', '13+'),
(7, 'Now You See Me: Now You Don’t', '8.6', 'Thriller, Kejahatan', 'Para pesulap Four Horsemen dan generasi baru bersatu untuk mencuri berlian besar milik perusahaan kriminal dan membongkar konspirasi global.', 125, 'Ruben Fleischer', 'now_you_see_me_3.jpg', '2025-11-14', '13+'),
(8, 'Avatar 3', '9.5', 'action-adventure, science fiction, and fantasy', 'The next chapter of the Avatar saga.', 190, 'James Cameron', 'avatar3.jpg', '2025-12-20', '13+'),
(12, 'Five Nights at Freddy’s 2', '0', 'Horror', 'Sekuel menegangkan dari franchise animatronik horror. ‒ akan tayang Desember 2025 ', 100, 'Emma Tammi', 'fnaf2_2.jpg', '2025-12-05', '17+'),
(13, 'The Housemaid', '0', 'Horror / Thriller', 'Seorang pembantu rumah tangga menghadapi rahasia gelap keluarga majikannya. ‒ rilis 31 Desember 2025 ', 95, 'Jean Kim', 'the_housemaid.jpg', '2025-12-31', '17+');


-- Tabel Jadwal
CREATE TABLE jadwal (
    Id_jadwal INT PRIMARY KEY AUTO_INCREMENT,
    Waktu_tayang DATETIME NOT NULL,
    harga DECIMAL(10,2) NOT NULL,
    Id_studio INT NOT NULL,
    Id_movie INT NOT NULL
);

-- Tabel Booking 
CREATE TABLE booking (
    Id_booking INT PRIMARY KEY AUTO_INCREMENT,
    tanggal_booking TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    total_harga DECIMAL(10,2) NOT NULL,
    code_booking VARCHAR(50) NOT NULL,
    status_booking CHAR(1) NOT NULL DEFAULT '2' COMMENT '1=confirmed, 2=pending, 3=cancelled',
    Id_user INT NOT NULL,
    Id_jadwal INT NOT NULL
);

-- Tabel Detail Booking 
CREATE TABLE detail_booking (
    Id_detail INT PRIMARY KEY AUTO_INCREMENT,
    no_kursi VARCHAR(10) NOT NULL,
    Id_booking INT NOT NULL
);

-- Tabel Reviews
CREATE TABLE reviews (
    Id_reviews INT PRIMARY KEY AUTO_INCREMENT,
    rating INT NOT NULL,
    komentar TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    Id_user INT NOT NULL,
    Id_movie INT NOT NULL
);

CREATE TABLE `transaksi` (
  `id_transaksi` int NOT NULL,
  `id_jadwal` int NOT NULL,
  `seat` varchar(5) NOT NULL,
  `nama_customer` varchar(100) DEFAULT NULL,
  `tanggal_beli` datetime DEFAULT CURRENT_TIMESTAMP,
  `kursi` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;


-- ngehubungin antar table fk (foreign key)
ALTER TABLE jadwal ADD CONSTRAINT fk_jadwal_film 
    FOREIGN KEY (Id_movie) REFERENCES movies(Id_movie) ON DELETE CASCADE;

ALTER TABLE jadwal ADD CONSTRAINT fk_jadwal_studio
    FOREIGN KEY (Id_studio) REFERENCES studios(Id_studio) ON DELETE CASCADE;

ALTER TABLE booking ADD CONSTRAINT fk_booking_user
    FOREIGN KEY (Id_user) REFERENCES users(Id_user);

ALTER TABLE booking ADD CONSTRAINT fk_booking_jadwal
    FOREIGN KEY (Id_jadwal) REFERENCES jadwal(Id_jadwal);

ALTER TABLE detail_booking ADD CONSTRAINT fk_detail_booking
    FOREIGN KEY (Id_booking) REFERENCES booking(Id_booking) ON DELETE CASCADE;

ALTER TABLE reviews ADD CONSTRAINT fk_reviews_user
    FOREIGN KEY (Id_user) REFERENCES users(Id_user);

ALTER TABLE reviews ADD CONSTRAINT fk_reviews_movie
    FOREIGN KEY (Id_movie) REFERENCES movies(Id_movie) ON DELETE CASCADE;

ALTER TABLE movies
    ADD COLUMN rating VARCHAR(10),
    ADD COLUMN genre VARCHAR(100),
    ADD COLUMN age_rating VARCHAR(5),
    ADD COLUMN director VARCHAR(100);

ALTER TABLE studios MODIFY capacity INT NOT NULL DEFAULT 0;


-- nambah admin
INSERT INTO users (username, email, password, role) 
VALUES ('adminPAW', 'adminpaw@gmail.com', 'd8d3aedd4b5d0ce0131600eaadc48dcb', '1');