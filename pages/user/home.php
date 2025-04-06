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

$user_id = (int) $_SESSION['id'];

// Ambil data proyek dari database
$sql = "SELECT status, approval FROM projects WHERE user_id = ?";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

$projects = [];
$total = 0;
$notStarted = 0;
$completed = 0;
$inProgress = 0;
$denied = 0;

while ($row = mysqli_fetch_assoc($result)) {
    $total++;
    if ($row['approval'] === "Disetujui" && $row['status'] === "Selesai") {
        $completed++;
    } elseif ($row['status'] === "Dalam Progress" && $row['approval'] === "Disetujui" || $row['status'] === "Dalam Progress" && $row['approval'] === "Butuh Peninjauan" || $row['status'] === "Dalam Progress" && $row['approval'] === "Belum Diajukan") {
        $inProgress++;
    } elseif ($row['approval'] === "Ditolak") {
        $denied++;
    } elseif ($row['status'] === "Belum Dimulai" && $row['approval'] === "Disetujui" || $row['status'] === "Belum Dimulai" && $row['approval'] === "Butuh Peninjauan" || $row['status'] === "Belum Dimulai" && $row['approval'] === "Belum Diajukan") {
        $notStarted++;
    }
}


mysqli_stmt_close($stmt);
mysqli_close($conn);

$chartData = json_encode([$completed, $inProgress, $denied, $notStarted]);
?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Dashboard - PLN Web App</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
      .sidebar-open main {
        margin-left: 20rem;
      }
    </style>
  </head>
  <body class="bg-gray-100">
    <header class="bg-white shadow-md py-4 px-6 flex justify-between items-center">
      <button id="menu-toggle" class="text-gray-700 text-2xl">☰</button>
      <img src="../../assets/img/logo_pln1.jpg" alt="PLN Logo" class="w-40" />
    </header>
    
    <aside id="sidebar" class="fixed top-0 left-0 w-64 h-full bg-white shadow-lg p-6 transform -translate-x-full transition-transform duration-300">
      <button id="close-menu" class="text-black-700 text-xl absolute top-4 right-4">✖</button>
      <nav class="mt-10">
        <a href="home.php" class="block py-3 px-4 text-lg text-[#009DDB] hover:bg-[#8ac8e0] hover:text-white">Dashboard</a>
        <a href="new_project.php" class="block py-3 px-4 text-lg text-[#009DDB] hover:bg-[#8ac8e0] hover:text-white">New Project</a>
        <a href="tracker.php" class="block py-3 px-4 text-lg text-[#009DDB] hover:bg-[#8ac8e0] hover:text-white">Tracker</a>
        <button onclick="window.location.href='../../auth/logout.php'" class="block py-3 px-4 w-full text-left text-lg text-red-600 hover:bg-red-400 hover:text-white">Logout</button>
      </nav>
    </aside>
    
    <main id="main-content" class="p-8 transition-all duration-300">
      <h1 class="text-3xl font-bold text-gray-700 mb-6">Dashboard</h1>
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <div class="bg-[#8ac8e0] p-6 rounded-xl shadow-lg">
          <h3 class="text-lg font-semibold text-black-600">Total Projects</h3>
          <p class="text-3xl font-bold text-black-600"><?= $total ?></p>
        </div>
        <div class="bg-[#8ac8e0] p-6 rounded-xl shadow-lg">
          <h3 class="text-lg font-semibold text-black-600">Not Yet Started</h3>
          <p class="text-3xl font-bold text-black-600"><?= $notStarted ?></p>
        </div>
        <div class="bg-[#8ac8e0] p-6 rounded-xl shadow-lg">
          <h3 class="text-lg font-semibold text-black-600">In Progress</h3>
          <p class="text-3xl font-bold text-black-600"><?= $inProgress ?></p>
        </div>
        <div class="bg-[#8ac8e0] p-6 rounded-xl shadow-lg">
          <h3 class="text-lg font-semibold text-black-600">Rejected</h3>
          <p class="text-3xl font-bold text-black-600"><?= $denied ?></p>
        </div>
        <div class="bg-[#8ac8e0] p-6 rounded-xl shadow-lg">
          <h3 class="text-lg font-semibold text-black-600">Completed Projects</h3>
          <p class="text-3xl font-bold text-black-600"><?= $completed ?></p>
        </div>
      </div>
      <div class="bg-[#8ac8e0] p-6 rounded-xl shadow-lg mt-8">
        <h3 class="text-lg font-semibold text-black-600 text-center">Project Status Overview</h3>
        <div class="flex justify-center mt-6">
          <canvas id="projectChart" class="w-48 h-48"></canvas>
        </div>
      </div>
    </main>
    
    <script>
      document.addEventListener("DOMContentLoaded", function () {
        let menuToggle = document.getElementById("menu-toggle");
        let closeMenu = document.getElementById("close-menu");
        let sidebar = document.getElementById("sidebar");
        let mainContent = document.getElementById("main-content");

        menuToggle.addEventListener("click", function () {
          sidebar.classList.toggle("-translate-x-full");
          mainContent.classList.toggle("ml-64");
        });

        closeMenu.addEventListener("click", function () {
          sidebar.classList.add("-translate-x-full");
          mainContent.classList.remove("ml-64");
        });

        const ctx = document.getElementById("projectChart").getContext("2d");
        const chartData = <?= $chartData ?>;
        
        new Chart(ctx, {
          type: "doughnut",
          data: {
            labels: ["Completed", "In Progress", "Rejected", "Not Yet Started"],
            datasets: [
              {
                data: chartData,
                backgroundColor: ["#4CAF50", "#FFC107", "#F44336", "#9E9E9E"],
              },
            ],
          },
          options: {
            responsive: true,
            maintainAspectRatio: false,
          },
        });
      });
    </script>
  </body>
</html>
