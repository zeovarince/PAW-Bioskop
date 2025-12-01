<?php
session_start();
// Tidak perlu include koneksi jika tidak mengambil data dari database untuk halaman ini
// Tapi jika navbar butuh logika login, session_start() wajib ada.
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact Us - Sonic Cinema</title>
    <link rel="icon" href="../logo.png">
    
    <!-- Tailwind & Icons -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        cinemaBlack: '#141414',
                        cinemaDark: '#1f1f1f',
                        cinemaRed: '#E50914',
                        cinemaGold: '#FFD700',
                    }
                }
            }
        }
    </script>
    <script src="https://unpkg.com/@phosphor-icons/web"></script>
    
    <style>
        .glass-nav {
            background: rgba(20, 20, 20, 0.9);
            backdrop-filter: blur(10px);
        }
    </style>
</head>
<body class="bg-cinemaBlack font-sans text-gray-200">

    <!-- NAVBAR USER (Updated Sesuai Request) -->
    <nav class="bg-cinemaBlack border-b border-gray-800 py-4 sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-6 flex justify-between items-center">
            
            <div class="flex items-center gap-4">
                <img src="../logo.png" 
                     alt="Onic Logo" class="h-11 w-auto object-contain drop-shadow-lg">
                
                <h1 class="text-3xl font-bold text-cinemaGold tracking-widest uppercase" style="text-shadow: 0px 0px 7px;">
                    ONIC <span class="text-white">CINEMA</span>
                </h1>
            </div>

            <div class="hidden md:block">
                <div class="ml-10 flex items-baseline space-x-4">
                    <a href="index.php" class="text-gray-300 hover:text-cinemaGold px-3 py-2 rounded-md text-sm font-medium transition">Home</a>
                    <a href="movies.php" class="text-gray-300 hover:text-cinemaGold px-3 py-2 rounded-md text-sm font-medium transition">Movies</a>
                    
                    <!-- MENU DASHBOARD (Hanya muncul jika login) -->
                    <?php if (isset($_SESSION['login'])): ?>
                        <a href="dashboard.php" class="text-gray-300 hover:text-cinemaGold px-3 py-2 rounded-md text-sm font-medium transition">Dashboard</a>
                    <?php endif; ?>
                    
                    <!-- Menu Contact Aktif -->
                    <a href="contact.php" class="bg-cinemaGold text-black px-3 py-2 rounded-md text-sm font-medium transition">Contact</a>
                </div>
            </div>

            <div class="flex items-center gap-4">
                <?php if (isset($_SESSION['login'])): ?>
                    <div class="text-right hidden sm:block">
                        <p class="text-sm font-bold text-white">Halo, <?= $_SESSION['username'] ?></p>
                        <p class="text-xs text-cinemaGold">Member</p>
                    </div>
                    <a href="logout.php" class="bg-gray-800 hover:bg-cinemaRed text-white p-2 rounded-full transition" title="Logout">
                        <i class="ph ph-sign-out text-xl"></i>
                    </a>
                <?php else: ?>
                    <a href="login.php" class="bg-cinemaRed hover:bg-red-700 text-white px-5 py-2 rounded-full font-bold text-sm transition shadow-lg shadow-red-900/20">
                        Masuk / Daftar
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </nav>

    <!-- 2. HEADER SECTION -->
    <header class="pt-24 pb-16 text-center px-6">
        <h1 class="text-4xl md:text-5xl font-extrabold text-white mb-4">Meet The Team</h1>
        <p class="text-gray-400 max-w-2xl mx-auto text-lg">
            Di balik layar Sonic Cinema, ada kelompok yang bekerja keras untuk mengerjakan tugas akhir Pemrograman Aplikasi Web.
        </p>
    </header>

    <!-- 3. TEAM PROFILES (Grid 5 Kolom) -->
    <section class="max-w-7xl mx-auto px-6 mb-24">
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-6">
            
            <!-- ANGGOTA 1 -->
            <div class="bg-cinemaDark rounded-xl p-6 text-center border border-gray-800 hover:border-cinemaRed transition duration-300 group">
                <div class="relative w-24 h-24 mx-auto mb-4">
                    <img src="https://ui-avatars.com/api/?name=Ketua+Tim&background=E50914&color=fff" alt="Foto Profil" class="w-full h-full rounded-full object-cover border-2 border-cinemaRed group-hover:scale-110 transition duration-300">
                </div>
                <h3 class="text-white font-bold text-lg">Nama Lengkap 1</h3>
                <p class="text-cinemaGold text-sm font-mono mb-2">NIM: 123456789</p>
                <span class="inline-block bg-gray-800 text-gray-300 text-xs px-2 py-1 rounded">Username GitHub : 4uRiel</span>
            </div>

            <!-- ANGGOTA 2 -->
            <div class="bg-cinemaDark rounded-xl p-6 text-center border border-gray-800 hover:border-cinemaRed transition duration-300 group">
                <div class="relative w-24 h-24 mx-auto mb-4">
                    <img src="https://ui-avatars.com/api/?name=Anggota+Dua&background=random" alt="Foto Profil" class="w-full h-full rounded-full object-cover border-2 border-gray-700 group-hover:border-cinemaRed transition duration-300">
                </div>
                <h3 class="text-white font-bold text-lg">Nama Lengkap 2</h3>
                <p class="text-cinemaGold text-sm font-mono mb-2">NIM: 123456789</p>
                <span class="inline-block bg-gray-800 text-gray-300 text-xs px-2 py-1 rounded"></span>
            </div>

            <!-- ANGGOTA 3 -->
            <div class="bg-cinemaDark rounded-xl p-6 text-center border border-gray-800 hover:border-cinemaRed transition duration-300 group">
                <div class="relative w-24 h-24 mx-auto mb-4">
                    <img src="https://ui-avatars.com/api/?name=Anggota+Tiga&background=random" alt="Foto Profil" class="w-full h-full rounded-full object-cover border-2 border-gray-700 group-hover:border-cinemaRed transition duration-300">
                </div>
                <h3 class="text-white font-bold text-lg">Nama Lengkap 3</h3>
                <p class="text-cinemaGold text-sm font-mono mb-2">NIM: 123456789</p>
                <span class="inline-block bg-gray-800 text-gray-300 text-xs px-2 py-1 rounded"></span>
            </div>

            <!-- ANGGOTA 4 -->
            <div class="bg-cinemaDark rounded-xl p-6 text-center border border-gray-800 hover:border-cinemaRed transition duration-300 group">
                <div class="relative w-24 h-24 mx-auto mb-4">
                    <img src="https://ui-avatars.com/api/?name=Anggota+Empat&background=random" alt="Foto Profil" class="w-full h-full rounded-full object-cover border-2 border-gray-700 group-hover:border-cinemaRed transition duration-300">
                </div>
                <h3 class="text-white font-bold text-lg">Nama Lengkap 4</h3>
                <p class="text-cinemaGold text-sm font-mono mb-2">NIM: 123456789</p>
                <span class="inline-block bg-gray-800 text-gray-300 text-xs px-2 py-1 rounded"></span>
            </div>

            <!-- ANGGOTA 5 -->
            <div class="bg-cinemaDark rounded-xl p-6 text-center border border-gray-800 hover:border-cinemaRed transition duration-300 group">
                <div class="relative w-24 h-24 mx-auto mb-4">
                    <img src="https://ui-avatars.com/api/?name=Anggota+Lima&background=random" alt="Foto Profil" class="w-full h-full rounded-full object-cover border-2 border-gray-700 group-hover:border-cinemaRed transition duration-300">
                </div>
                <h3 class="text-white font-bold text-lg">Nama Lengkap 5</h3>
                <p class="text-cinemaGold text-sm font-mono mb-2">NIM: 123456789</p>
                <span class="inline-block bg-gray-800 text-gray-300 text-xs px-2 py-1 rounded"></span>
            </div>

        </div>
    </section>
    <!-- FOOTER -->
    <footer class="bg-black border-t border-gray-900 pt-16 pb-8">
        <div class="max-w-7xl mx-auto px-6">
            <div class="flex flex-col md:flex-row justify-between items-center mb-10">
                <div class="flex items-center gap-3 mb-6 md:mb-0">
                    <img src="logo.png" alt="Logo" class="h-12 opacity-80 grayscale hover:grayscale-0 transition" onerror="this.style.display='none'">
                    <div class="text-gray-400 text-sm">
                        <p class="font-bold text-white">SONIC CINEMA</p>
                        <p>Hiburan Tanpa Batas.</p>
                    </div>
                </div>
                <div class="text-center md:text-right">
                    <p class="text-gray-500 text-sm">Tugas Akhir Pemrograman Aplikasi Web</p>
                    <p class="text-gray-500 text-sm">Dosen: Moch Kautsar Sophan</p>
                </div>
            </div>
            <div class="border-t border-gray-900 pt-8 text-center text-gray-600 text-sm">
                &copy; <?= date('Y') ?> Sonic Cinema Team. All rights reserved.
            </div>
        </div>
    </footer>

</body>
</html>