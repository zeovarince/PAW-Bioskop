<?php
session_start();
include "function.php";
if (isset($_POST['login'])){
    $loginresult = checklogin($_POST);
    if ($loginresult !== true){
        $erorr = $loginresult;
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link rel="icon" href="logo.png">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        cinemaBlack: '#141414',  /* Hitam Pekat */
                        cinemaDark: '#222222',   /* Abu Gelap (Panel) */
                        cinemaRed: '#E50914',    /* Merah Branding */
                        cinemaGold: '#FFD700',   /* Emas Tombol */
                        cinemaGoldHover: '#FBC02D',
                    }
                }
            }
        }
    </script>
</head>
<body class="bg-cinemaBlack text-white flex items-center justify-center min-h-screen font-sans">

    <div class="w-full max-w-md p-8 space-y-6 bg-cinemaDark rounded-xl shadow-2xl">
        
        <div class="text-center">
            <h1 class="text-3xl font-bold text-cinemaRed tracking-wider uppercase">ONIC CINEMA</h1>
            <p class="text-gray-400 text-sm mt-2">Login to continue</p>
        </div>

        <form action="" method="POST" class="space-y-5">
            <div>
                <label for="email" class="block mb-2 text-sm font-medium text-gray-300">Email</label>
                <input type="text" name="email" id="email" required 
                    class="w-full px-4 py-3 bg-neutral-800 border border-neutral-700 text-white rounded-lg focus:outline-none focus:border-cinemaRed focus:ring-1 focus:ring-cinemaRed transition duration-200"
                    placeholder="Masukkan email">
            </div>

            <div>
                <label for="password" class="block mb-2 text-sm font-medium text-gray-300">Password</label>
                <input type="password" name="password" id="password" required 
                    class="w-full px-4 py-3 bg-neutral-800 border border-neutral-700 text-white rounded-lg focus:outline-none focus:border-cinemaRed focus:ring-1 focus:ring-cinemaRed transition duration-200"
                    placeholder="Masukkan password">
            </div>

            <div>
                <button type="submit" name="login" 
                    class="w-full px-4 py-3 font-bold text-black uppercase bg-cinemaGold rounded-lg hover:bg-cinemaGoldHover focus:outline-none focus:bg-yellow-400 transition duration-300 transform hover:scale-[1.02]">
                    Login
                </button>
            </div>

        </form>

        <div class="text-center text-sm text-gray-500">
            Don't have an account?
            <a href="register.php" class="text-cinemaGold hover:underline hover:text-yellow-300">Daftar disini</a>
        </div>

    </div>

</body>
</html>