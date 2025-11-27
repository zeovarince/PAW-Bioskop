<?php
session_start();
if (!isset($_SESSION['login']) || $_SESSION['role'] != '1') {
    header("Location: ../login.php");
    exit;
}
include "../koneksi.php";
include "../function.php"; // Memuat fungsi CRUD Studio

$is_edit = false;
$studio_data = [];
$form_errors = [];
$page_title = "Manajemen Studio";

// Cek apakah mode EDIT
if (isset($_GET['id'])) {
    $id_studio = $_GET['id'];
    $studio_data = getStudios($id_studio); 
    
    if ($studio_data) {
        $is_edit = true;
        $page_title = "Edit Studio: " . htmlspecialchars($studio_data['nama_studio']);
    } else {
        $_SESSION['message'] = "Gagal: Studio dengan ID tersebut tidak ditemukan.";
        header("Location: manage_studio.php");
        exit;
    }
}

// Ambil data dari SESSION jika ada error saat submit
if (isset($_SESSION['form_data'])) {
    $studio_data = array_merge($studio_data, $_SESSION['form_data']);
    unset($_SESSION['form_data']);
}
if (isset($_SESSION['form_errors'])) {
    $form_errors = $_SESSION['form_errors'];
    unset($_SESSION['form_errors']);
}

// Ambil semua data studio untuk tampilan daftar
$all_studios = getStudios();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $page_title ?> - Onic Cinema Admin</title>
    <link rel="icon" href="../logo.png">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://unpkg.com/@phosphor-icons/web@2.1.1/src/css/icons.css">
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
    <style>
        .glass-nav {
            background: rgba(20, 20, 20, 0.9);
            backdrop-filter: blur(10px);
        }
        .form-input {
            @apply w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-cinemaRed focus:border-cinemaRed transition duration-200;
        }
        .form-error {
             @apply text-red-500 text-sm mt-1;
        }
        .modal {
            transition: opacity 0.3s ease-in-out;
        }
    </style>
</head>
<body class="bg-gray-50 font-sans text-gray-800">

    <!-- Navigasi Admin -->
    <nav class="glass-nav fixed w-full z-50 bg-cinemaBlack">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-20">
                <div class="flex items-center gap-4">
                    <img src="../logo.png" alt="Onic Logo" class="h-11 w-auto object-contain drop-shadow-lg">
                    <h1 class="text-3xl font-bold text-cinemaGold tracking-widest uppercase" style="text-shadow: 0px 0px 7px;">
                        ONIC <span class="text-white">ADMINISTRATOR</span>
                    </h1>
                </div>

                <div class="hidden md:block">
                    <div class="ml-10 flex items-baseline space-x-4">
                        <a href="index.php" class="text-gray-300 hover:text-cinemaRed px-3 py-2 rounded-md text-sm font-medium transition">Dashboard</a>
                        <a href="movies.php" class="text-gray-300 hover:text-cinemaRed px-3 py-2 rounded-md text-sm font-medium transition">Movies</a>
                        <a href="studio_admin.php" class="text-gray-300 hover:text-cinemaRed px-3 py-2 rounded-md text-sm font-medium transition">Studio</a>
                        <a href="schedule.php" class="text-gray-300 hover:text-cinemaRed px-3 py-2 rounded-md text-sm font-medium transition">Schedules</a>
                        <a href="manage_studio.php" class="bg-cinemaGold text-black px-3 py-2 text-l rounded-full text-sm font-medium transition">Studios</a>
                        <a href="validation.php" class="text-gray-300 hover:text-cinemaRed px-3 py-2 rounded-md text-sm font-medium transition">Validation</a>
                        <a href="report.php" class="text-gray-300 hover:text-cinemaRed px-3 py-2 rounded-md text-sm font-medium transition">Report</a>
                    </div>
                </div>

                <div class="flex items-center gap-4">
                    <div class="hidden md:flex flex-col items-end">
                        <span class="text-white text-sm font-bold"><?= isset($_SESSION['username']) ? $_SESSION['username'] : 'Admin' ?></span>
                        <span class="text-gray-400 text-xs">Administrator</span>
                    </div>
                    <a href="../logout.php" class="text-gray-400 hover:text-white transition" title="Logout">
                        <i class="ph ph-sign-out text-2xl"></i>
                    </a>
                </div>
            </div>
        </div>
    </nav>
    <!-- End Navigasi -->

    <div class="pt-24 pb-12 min-h-screen">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 grid grid-cols-1 lg:grid-cols-3 gap-8">
            
            <!-- Kolom Kiri: Form Tambah/Edit Studio -->
            <div class="lg:col-span-1">
                <div class="bg-white shadow-xl rounded-xl p-6">
                    <h2 class="text-2xl font-extrabold text-cinemaBlack mb-4 flex items-center gap-2">
                        <i class="ph ph-plus-circle text-cinemaRed"></i> <?= $is_edit ? 'Edit Studio' : 'Tambah Studio' ?>
                    </h2>

                    <!-- Tampilkan Error General Form -->
                    <?php if (isset($form_errors['general'])): ?>
                        <div class="p-3 mb-4 text-sm text-red-700 bg-red-100 rounded-lg" role="alert">
                            <?= $form_errors['general']; ?>
                        </div>
                    <?php endif; ?>

                    <form action="action_studio.php" method="POST">
                        
                        <?php if ($is_edit): ?>
                            <input type="hidden" name="action" value="update">
                            <input type="hidden" name="id_studio" value="<?= $studio_data['Id_studio'] ?>">
                        <?php else: ?>
                            <input type="hidden" name="action" value="create">
                        <?php endif; ?>

                        <!-- Nama Studio -->
                        <div class="mb-4">
                            <label for="nama_studio" class="block text-sm font-medium text-gray-700 mb-1">Nama Studio</label>
                            <input type="text" id="nama_studio" name="nama_studio" class="form-input" placeholder="Contoh: Studio 1, Dolby" 
                                value="<?= htmlspecialchars($studio_data['nama_studio'] ?? '') ?>" required>
                            <?php if (isset($form_errors['nama_studio'])): ?>
                                <p class="form-error"><?= $form_errors['nama_studio'] ?></p>
                            <?php endif; ?>
                        </div>

                        <!-- Total Baris -->
                        <div class="mb-4">
                            <label for="total_baris" class="block text-sm font-medium text-gray-700 mb-1">Jumlah Baris Kursi</label>
                            <input type="number" id="total_baris" name="total_baris" class="form-input" placeholder="Contoh: 10" min="1" 
                                value="<?= htmlspecialchars($studio_data['total_baris'] ?? '') ?>" required>
                            <?php if (isset($form_errors['total_baris'])): ?>
                                <p class="form-error"><?= $form_errors['total_baris'] ?></p>
                            <?php endif; ?>
                        </div>
                        
                        <!-- Kursi Per Baris -->
                        <div class="mb-4">
                            <label for="total_kursi_per_baris" class="block text-sm font-medium text-gray-700 mb-1">Kursi per Baris</label>
                            <input type="number" id="total_kursi_per_baris" name="total_kursi_per_baris" class="form-input" placeholder="Contoh: 15" min="1" 
                                value="<?= htmlspecialchars($studio_data['total_kursi_per_baris'] ?? '') ?>" required>
                            <?php if (isset($form_errors['total_kursi_per_baris'])): ?>
                                <p class="form-error"><?= $form_errors['total_kursi_per_baris'] ?></p>
                            <?php endif; ?>
                        </div>

                        <!-- Kapasitas Total -->
                        <div class="mb-4">
                            <label for="capacity" class="block text-sm font-medium text-gray-700 mb-1">Kapasitas Total</label>
                            <input type="number" id="capacity" name="capacity" class="form-input bg-gray-100" placeholder="Otomatis (Baris x Kursi)" readonly
                                value="<?= htmlspecialchars($studio_data['capacity'] ?? '') ?>" required>
                            <?php if (isset($form_errors['capacity'])): ?>
                                <p class="form-error"><?= $form_errors['capacity'] ?></p>
                            <?php endif; ?>
                            <p class="text-xs text-gray-500 mt-1">Harus sama dengan (Baris x Kursi per Baris).</p>
                        </div>
                        
                        <div class="mt-6 flex justify-between">
                            <?php if ($is_edit): ?>
                                <a href="manage_studio.php" class="text-gray-500 hover:text-gray-700 py-2 transition text-sm font-semibold">
                                    <i class="ph ph-x-circle mr-1"></i> Batal Edit
                                </a>
                            <?php endif; ?>
                            <button type="submit" class="bg-cinemaRed hover:bg-red-700 text-white px-5 py-2 rounded-lg font-bold shadow-md transition">
                                <i class="ph ph-floppy-disk mr-1"></i> <?= $is_edit ? 'Simpan Perubahan' : 'Tambah Studio' ?>
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Kolom Kanan: Daftar Studio (READ) -->
            <div class="lg:col-span-2">
                <div class="mb-8 flex justify-between items-center">
                    <h2 class="text-3xl font-extrabold text-cinemaBlack flex items-center gap-2">
                        <i class="ph ph-list text-cinemaRed"></i> Daftar Studio Bioskop
                    </h2>
                    <a href="studio_admin.php" class="text-blue-600 hover:text-blue-800 text-sm font-bold flex items-center gap-1 transition">
                        <i class="ph ph-eye"></i> Lihat Visual Denah
                    </a>
                </div>

                <!-- Tampilkan Pesan Sukses/Error (jika ada) -->
                <?php if (isset($_SESSION['message'])): ?>
                    <div id="alert" class="p-4 mb-4 text-sm rounded-lg <?= strpos($_SESSION['message'], 'Gagal') !== false ? 'bg-red-100 text-red-700' : 'bg-green-100 text-green-700' ?>" role="alert">
                        <?= $_SESSION['message']; ?>
                    </div>
                    <?php unset($_SESSION['message']); ?>
                <?php endif; ?>

                <div class="bg-white shadow-xl rounded-xl overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-100">
                                <tr>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider w-1/12">ID</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider w-4/12">Nama Studio</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider w-3/12">Denah (Baris x Kursi)</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider w-2/12">Kapasitas</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider w-2/12">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <?php if (count($all_studios) > 0): ?>
                                    <?php foreach ($all_studios as $studio): ?>
                                        <tr>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900"><?= $studio['Id_studio'] ?></td>
                                            <td class="px-6 py-4 text-sm text-gray-900 font-bold"><?= htmlspecialchars($studio['nama_studio']) ?></td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                <?= $studio['total_baris'] ?> Baris x <?= $studio['total_kursi_per_baris'] ?> Kursi
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 font-bold text-cinemaRed">
                                                <?= $studio['capacity'] ?> Kursi
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium flex items-center gap-3">
                                                <!-- Tombol Edit -->
                                                <a href="manage_studio.php?id=<?= $studio['Id_studio'] ?>" class="text-blue-600 hover:text-blue-900 transition flex items-center gap-1">
                                                    <i class="ph ph-pencil-simple"></i> Edit
                                                </a>
                                                <!-- Tombol Hapus (Trigger Modal) -->
                                                <button onclick="openModal(<?= $studio['Id_studio'] ?>)" class="text-red-600 hover:text-red-900 transition flex items-center gap-1">
                                                    <i class="ph ph-trash"></i> Hapus
                                                </button>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="5" class="px-6 py-10 text-center text-gray-500">
                                            <i class="ph ph-projector-screen-slash text-5xl text-gray-200 mb-3 block"></i>
                                            Belum ada data studio. Silakan tambahkan di formulir samping.
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

        </div>
    </div>

    <!-- Modal Konfirmasi Hapus -->
    <div id="deleteModal" class="modal fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center opacity-0 pointer-events-none z-[100]">
        <div class="bg-white rounded-lg shadow-2xl w-full max-w-sm p-6 transform translate-y-2 scale-95 transition-all duration-300">
            <div class="text-center">
                <i class="ph ph-warning-circle text-6xl text-red-500 mx-auto mb-4"></i>
                <h3 class="text-lg font-bold text-gray-900 mb-2">Konfirmasi Hapus</h3>
                <p class="text-sm text-gray-500 mb-6">Anda yakin ingin menghapus Studio ini? Studio yang masih memiliki jadwal tidak dapat dihapus.</p>
                
                <div class="flex justify-center gap-4">
                    <button onclick="closeModal()" class="px-4 py-2 bg-gray-200 text-gray-800 rounded-lg hover:bg-gray-300 transition font-semibold">
                        Batal
                    </button>
                    <a href="#" id="deleteConfirmBtn" class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition font-semibold">
                        Ya, Hapus
                    </a>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Fungsi untuk mengupdate kapasitas secara real-time
        const barisInput = document.getElementById('total_baris');
        const kursiInput = document.getElementById('total_kursi_per_baris');
        const capacityInput = document.getElementById('capacity');

        function updateCapacity() {
            const baris = parseInt(barisInput.value) || 0;
            const kursi = parseInt(kursiInput.value) || 0;
            capacityInput.value = baris * kursi;
        }

        barisInput.addEventListener('input', updateCapacity);
        kursiInput.addEventListener('input', updateCapacity);

        // --- Fungsi Modal (Sama seperti movies.php) ---
        function openModal(studioId) {
            const modal = document.getElementById('deleteModal');
            const confirmBtn = document.getElementById('deleteConfirmBtn');
            confirmBtn.href = `action_studio.php?action=delete&id=${studioId}`;
            modal.classList.remove('opacity-0', 'pointer-events-none');
            modal.querySelector('div').classList.remove('translate-y-2', 'scale-95');
        }

        function closeModal() {
            const modal = document.getElementById('deleteModal');
            modal.classList.add('opacity-0', 'pointer-events-none');
            modal.querySelector('div').classList.add('translate-y-2', 'scale-95');
        }

        // Auto hide alert after 5 seconds
        setTimeout(() => {
            const alert = document.getElementById('alert');
            if (alert) {
                alert.style.opacity = '0';
                setTimeout(() => alert.remove(), 500);
            }
        }, 5000);
    </script>
</body>
</html>