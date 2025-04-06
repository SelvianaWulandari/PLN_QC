<?php
require 'config/database.php';
session_start();

$messages = "";

if (isset($_SESSION['role'])) {
    if ($_SESSION['role'] == 'Admin') {
        header("Location: pages/admin/home.php");
        exit();
    } else {
        header("Location: pages/user/home.php");
        exit();
    }
}

// Fungsi untuk autentikasi user
function authenticate_user($conn, $username, $password)
{
    $sql = "SELECT id, username, password, role, email FROM users WHERE username = ?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "s", $username);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $user = mysqli_fetch_assoc($result);

    if ($user && password_verify($password, $user['password'])) {
        return $user; // Return data user jika login sukses
    }
    return false; // Return false jika gagal
}

// Cek data login jika form dikirim
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['login'])) {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    // Validasi input kosong
    if (empty($username) || empty($password)) {
        $messages = "<div class='text-red-500'>Username dan Password wajib diisi!</div>";
    } else {
        $user = authenticate_user($conn, $username, $password);

        if ($user) {
            $_SESSION['id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['email'] = $user['email'];

            // Arahkan berdasarkan role
            if ($user['role'] == 'Admin') {
                header("Location: pages/admin/home.php");
                exit();
            } else if ($user['role'] == 'User') {
                header("Location: pages/user/home.php");
                exit();
            }
        } else {
            $messages = "<div class='text-red-500'>Username atau Password salah!</div>";
        }
    }
}
?>

<!-- Tetap pakai kode PHP Anda di atas seperti biasa -->

<!DOCTYPE html>
<html lang="id">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Login - Sistem Monitoring PLN</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-gray-200 flex items-center justify-center min-h-screen">

  <div class="bg-white rounded-3xl shadow-2xl flex w-full max-w-5xl overflow-hidden">

    <!-- Bagian Kiri: Form Login -->
    <div class="w-1/2 p-14 flex flex-col justify-center">
      <div class="text-center">
        <h2 class="text-4xl font-bold text-gray-800 mb-2">LOGIN</h2>
        <p class="text-sm text-gray-500 mb-6">Masukkan username dan password Anda</p>
      </div>

      <!-- Pesan Error -->
      <?php if (!empty($messages)) echo "<div class='mt-2 text-sm text-red-500 text-center'>$messages</div>"; ?>

      <!-- Form Login -->
      <form method="POST" class="space-y-5">
        <div>
          <label class="block text-sm text-gray-600 font-medium mb-1">Username</label>
          <input type="text" name="username"
            class="w-full border rounded-lg p-3 focus:outline-none focus:ring-2 focus:ring-cyan-400"
            placeholder="Masukkan username" required />
        </div>

        <div>
          <label class="block text-sm text-gray-600 font-medium mb-1">Password</label>
          <input type="password" name="password"
            class="w-full border rounded-lg p-3 focus:outline-none focus:ring-2 focus:ring-cyan-400"
            placeholder="Masukkan password" required />
        </div>

        <button type="submit" name="login"
          class="w-full bg-cyan-500 hover:bg-cyan-600 text-white py-3 rounded-lg font-semibold transition-all duration-300">
          Sign In
        </button>
      </form>

      <p class="text-xs text-gray-400 text-center mt-6">© PLN UPT Manado <?= date("Y") ?>. All rights reserved.</p>
    </div>

    <!-- Bagian Kanan: Logo PLN dan Branding -->
    <div class="w-1/2 bg-gradient-to-br from-cyan-400 to-green-400 flex flex-col items-center justify-center p-14 rounded-l-[5rem]">
      <img src="assets/img/logo_pln.png" alt="Logo PLN" class="w-28 mb-4" />
      <h3 class="text-2xl font-bold text-white text-center">PLN UPT Manado</h3>
    </div>

  </div>

</body>

</html>
