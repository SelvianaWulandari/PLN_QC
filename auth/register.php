<?php
session_start();
require '../config/database.php'; // Pastikan koneksi database sudah ada

$messages = "";
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST["username"]);
    $email = trim($_POST["email"]);
    $password = trim($_POST["password"]);
    $passwordRepeat = trim($_POST["repeatPassword"]);

    $errors = [];

    // Validasi input
    if (empty($username) || empty($email) || empty($password) || empty($passwordRepeat)) {
        $errors[] = "Semua kolom harus diisi!";
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Email tidak valid!";
    }
    if (strlen($password) < 4) {
        $errors[] = "Password setidaknya harus 4 karakter!";
    }
    if ($password !== $passwordRepeat) {
        $errors[] = "Password tidak sama!";
    }

    // Cek apakah email sudah ada di database
    $sql = "SELECT * FROM users WHERE email = ?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "s", $email);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    if (mysqli_num_rows($result) > 0) {
        $errors[] = "Email sudah digunakan!";
    }
    mysqli_stmt_close($stmt);

    // Jika ada error, tampilkan pesan
    if (!empty($errors)) {
        foreach ($errors as $error) {
            $messages .= "<div class='bg-red-500 text-white p-2 rounded mt-2'>$error</div>";
        }
    } else {
        // Hash password dan simpan ke database
        $password_hash = password_hash($password, PASSWORD_DEFAULT);
        $sql = "INSERT INTO users (username, email, password, role) VALUES (?, ?, ?, 'user')";
        $stmt = mysqli_prepare($conn, $sql);

        if ($stmt) {
            mysqli_stmt_bind_param($stmt, "sss", $username, $email, $password_hash);
            if (mysqli_stmt_execute($stmt)) {
                $messages = "<div class='bg-green-500 text-white p-2 rounded mt-2'>Akun berhasil dibuat. Silakan <a href='../index.php' class='underline'>login</a>.</div>";
            } else {
                $messages = "<div class='bg-red-500 text-white p-2 rounded mt-2'>Terjadi kesalahan saat registrasi.</div>";
            }
            mysqli_stmt_close($stmt);
        } else {
            $messages = "<div class='bg-red-500 text-white p-2 rounded mt-2'>Kesalahan dalam SQL.</div>";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Register - Sistem Monitoring PLN</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-gray-200 flex items-center justify-center min-h-screen">

  <div class="bg-white rounded-3xl shadow-2xl flex w-full max-w-5xl overflow-hidden">

    <!-- Bagian Kiri: Form Register -->
    <div class="w-1/2 p-14 flex flex-col justify-center">
      <div class="text-center">
        <h2 class="text-4xl font-bold text-gray-800 mb-2">REGISTER</h2>
        <p class="text-sm text-gray-500 mb-6">Buat akun baru User di sistem</p>
      </div>

      <!-- Pesan Error atau Sukses -->
      <?php if (!empty($messages)) echo "<div class='mt-2 text-sm text-red-500 text-center'>$messages</div>"; ?>

      <!-- Form Register -->
      <form method="POST" action="register.php" class="space-y-5">
        <div>
          <label class="block text-sm text-gray-600 font-medium mb-1">Username</label>
          <input type="text" name="username" placeholder="Masukkan username"
            class="w-full border rounded-lg p-3 focus:outline-none focus:ring-2 focus:ring-cyan-400" required />
        </div>

        <div>
          <label class="block text-sm text-gray-600 font-medium mb-1">Email</label>
          <input type="email" name="email" placeholder="Masukkan email"
            class="w-full border rounded-lg p-3 focus:outline-none focus:ring-2 focus:ring-cyan-400" required />
        </div>

        <div>
          <label class="block text-sm text-gray-600 font-medium mb-1">Password</label>
          <input type="password" name="password" placeholder="Masukkan password"
            class="w-full border rounded-lg p-3 focus:outline-none focus:ring-2 focus:ring-cyan-400" required />
        </div>

        <div>
          <label class="block text-sm text-gray-600 font-medium mb-1">Ulangi Password</label>
          <input type="password" name="repeatPassword" placeholder="Ulangi password"
            class="w-full border rounded-lg p-3 focus:outline-none focus:ring-2 focus:ring-cyan-400" required />
        </div>

        <button type="submit"
          class="w-full bg-cyan-500 hover:bg-cyan-600 text-white py-3 rounded-lg font-semibold transition-all duration-300">
          Daftar Akun
        </button>

        <button type="button" onclick="window.location.href='../index.php'"
          class="w-full bg-gray-300 hover:bg-gray-400 text-gray-800 py-2 rounded-lg font-medium transition-all duration-300">
          Kembali 
        </button>
      </form>

      <p class="text-xs text-gray-400 text-center mt-6">© PLN UPT Manado <?= date("Y") ?>. All rights reserved.</p>
    </div>

    <!-- Bagian Kanan: Logo PLN dan Branding -->
    <div class="w-1/2 bg-gradient-to-br from-cyan-400 to-green-400 flex flex-col items-center justify-center p-14 rounded-l-[5rem]">
    <img src="../assets/img/logo_pln.png" alt="Logo PLN" class="w-28 mb-4" />
      <h3 class="text-2xl font-bold text-white text-center">PLN UPT Manado</h3>
    </div>

  </div>

</body>

</html>
