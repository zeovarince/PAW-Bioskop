<?php
require 'function.php';
$errors = [];
if (isset($_POST['register'])) {
    $result = register($_POST);
    
    if ($result === true) {
        echo "<script>
                alert('Registrasi Berhasil! Silakan Login.');
                document.location.href = 'login.php';
              </script>";
        exit;
    } else {
        $errors = $result;
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register Onic Cinema</title>
    <link rel="icon" href="logo.png">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        cinemaBlack: '#141414',
                        cinemaDark: '#222222',
                        cinemaRed: '#E50914',
                        cinemaGold: '#FFD700',
                        cinemaGoldHover: '#FBC02D',
                    }
                }
            }
        }
    </script>
</head>
<body class="bg-cinemaBlack text-white flex items-center justify-center min-h-screen font-sans py-10">

    <div class="w-full max-w-md p-8 space-y-6 bg-cinemaDark rounded-xl shadow-2xl border border-neutral-800">
        <div class="text-center">
            <h1 class="text-3xl font-bold text-cinemaRed tracking-wider uppercase">Sign Up <span class="text-white">Onic</span></h1>
            <p class="text-gray-400 text-sm mt-2">Create a new account to start booking tickets</p>
        </div>
        <form action="" method="POST" class="space-y-4">
            <div>
                <label for="username" class="block mb-1 text-sm font-medium text-gray-300">Name</label>
                <input type="text" name="username" id="username" 
                    class="w-full px-4 py-3 bg-neutral-800 border <?= isset($errors['username']) ? 'border-red-500' : 'border-neutral-700' ?> text-white rounded-lg focus:outline-none focus:border-cinemaRed transition placeholder-gray-500"
                    placeholder="Your name"
                    value="<?= htmlspecialchars($_POST['username'] ?? '') ?>">
                <?php if(isset($errors['username'])): ?>
                    <p class="text-red-500 text-xs mt-1 italic">* <?= $errors['username'] ?></p>
                <?php endif; ?>
            </div>

            <div>
                <label for="email" class="block mb-1 text-sm font-medium text-gray-300">Email</label>
                <input type="email" name="email" id="email" 
                    class="w-full px-4 py-3 bg-neutral-800 border <?= isset($errors['email']) ? 'border-red-500' : 'border-neutral-700' ?> text-white rounded-lg focus:outline-none focus:border-cinemaRed transition placeholder-gray-500"
                    placeholder="name@email.com"
                    value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">

                <?php if(isset($errors['email'])): ?>
                    <p class="text-red-500 text-xs mt-1 italic">* <?= $errors['email'] ?></p>
                <?php endif; ?>
            </div>

            <div>
                <label for="password" class="block mb-1 text-sm font-medium text-gray-300">Password</label>
                <input type="password" name="password" id="password" 
                    class="w-full px-4 py-3 bg-neutral-800 border <?= isset($errors['password']) ? 'border-red-500' : 'border-neutral-700' ?> text-white rounded-lg focus:outline-none focus:border-cinemaRed transition placeholder-gray-500"
                    placeholder="Min 6 characters and 1 capital letter">
                
                <?php if(isset($errors['password'])): ?>
                    <p class="text-red-500 text-xs mt-1 italic">* <?= $errors['password'] ?></p>
                <?php endif; ?>
            </div>

            <div>
                <label for="confirm_password" class="block mb-1 text-sm font-medium text-gray-300">Confirm Password</label>
                <input type="password" name="confirm_password" id="confirm_password" 
                    class="w-full px-4 py-3 bg-neutral-800 border <?= isset($errors['confirm_password']) ? 'border-red-500' : 'border-neutral-700' ?> text-white rounded-lg focus:outline-none focus:border-cinemaRed transition placeholder-gray-500"
                    placeholder="Repeat your password">
                
                <?php if(isset($errors['confirm_password'])): ?>
                    <p class="text-red-500 text-xs mt-1 italic">* <?= $errors['confirm_password'] ?></p>
                <?php endif; ?>
            </div>

            <div class="pt-2">
                <button type="submit" name="register"
                    class="w-full px-4 py-3 font-bold text-black uppercase bg-cinemaGold rounded-lg hover:bg-cinemaGoldHover focus:outline-none focus:bg-yellow-400 transition duration-300 transform hover:scale-[1.02]">
                    Register Now
                </button>
            </div>

        </form>

        <div class="text-center text-sm text-gray-500 border-t border-gray-700 pt-4">
            Already have an account?
            <a href="login.php" class="text-cinemaGold font-bold hover:underline hover:text-yellow-300">Login Here!</a>
        </div>

    </div>

</body>
</html>