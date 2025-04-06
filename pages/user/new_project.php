<?php
session_start();
require_once '../../config/database.php';

$user_id = $_SESSION['id'] ?? null;
if (!$user_id) {
    header("Location: ../../index.php");
    exit;
}

if ($_SESSION['role'] !== 'User') {
  header("Location: ../admin/home.php");
  exit;
}


$messages = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
  $name = trim($_POST['name']);
  $user_id = (int) $_SESSION['id'];
  $date = trim($_POST['date']);
  $status = "Belum Dimulai";
  $approval = "Pending";

  if (empty($name) || empty($date) || empty($status)) {
    $messages = "<div class='text-red-500'>Semua kolom harus diisi!</div>";
  } else {
    $sql = "INSERT INTO projects (user_id, name, date, status) VALUES (?, ?, ?, ?)";
    $stmt = mysqli_prepare($conn, $sql);

    if ($stmt) {
      mysqli_stmt_bind_param($stmt, "isss", $user_id, $name, $date, $status);
      if (mysqli_stmt_execute($stmt)) {
        $messages = "<div class='text-green-500'>Proyek berhasil ditambahkan!</div>";
      } else {
        $messages = "<div class='text-red-500'>Terjadi kesalahan: " . mysqli_error($conn) . "</div>";
      }
      mysqli_stmt_close($stmt);
    } else {
      $messages = "<div class='text-red-500'>Kesalahan SQL: " . mysqli_error($conn) . "</div>";
    }
  }

  mysqli_close($conn);
}
?>
<!DOCTYPE html>
<html lang="id">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>New Project - PLN Web App</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-gray-100 flex justify-center items-center h-screen p-4">
  <div class="bg-white p-8 rounded-lg shadow-lg w-96 text-center">
    <img src="../../assets/img/logo_pln.png" alt="PLN Logo" class="w-20 mx-auto mb-4" />
    <h2 class="text-3xl font-semibold mb-6 text-gray-800">Tambah Pekerjaan Baru</h2>
    <div class="mb-4 text-center"><?= $messages; ?></div>
    <form id="projectForm" method="POST" action="" class="space-y-4 text-left">
      <div>
        <label class="block text-gray-700 font-medium mb-1">Nama Pekerjaan</label>
        <input type="text" name="name" id="projectName" class="w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#009DDB] focus:border-[#009DDB] outline-none" required />
      </div>
      <div>
        <label class="block text-gray-700 font-medium mb-1">Tanggal</label>
        <input type="date" name="date" id="projectDate" class="w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#009DDB] focus:border-[#009DDB] outline-none" required />
      </div>
      <button type="submit" class="w-full bg-[#009DDB] text-white py-3 rounded-lg text-lg font-medium transition duration-300 hover:bg-[#0078B8]">
        Tambah Pekerjaan
      </button>
      <button type="button" onclick="window.location.href='home.php'" class="w-full bg-gray-500 text-white py-3 rounded-lg text-lg font-medium transition duration-300 hover:bg-gray-600">
        Kembali
      </button>
    </form>

  </div>
</body>

</html>