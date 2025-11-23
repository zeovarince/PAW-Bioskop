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

-- Tabel Movies
CREATE TABLE movies (
    Id_movie INT PRIMARY KEY AUTO_INCREMENT,
    judul VARCHAR(100) NOT NULL,
    description TEXT,
    duration INT NOT NULL,
    poster VARCHAR(255),
    release_date DATE
);

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


-- nambah admin
INSERT INTO users (username, email, password, role) 
VALUES ('adminPAW', 'adminpaw@gmail.com', 'd8d3aedd4b5d0ce0131600eaadc48dcb', '1');