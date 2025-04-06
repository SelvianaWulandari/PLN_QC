<?php
require 'database.php';
session_start();

$user_id = $_SESSION['id'] ?? null;
if (!$user_id) {
    header("Location: ../../auth/login.php");
    exit;
}

// Pastikan ada ID proyek yang dikirim
if (!isset($_GET['id'])) {
    echo "ID proyek tidak ditemukan!";
    exit;
}

$project_id = $_GET['id'];

// Ambil semua file_path yang terkait dengan project_id
$checkSql = "SELECT file_path FROM uploads WHERE project_id = ?";
$checkStmt = mysqli_prepare($conn, $checkSql);
mysqli_stmt_bind_param($checkStmt, "i", $project_id);
mysqli_stmt_execute($checkStmt);
$checkResult = mysqli_stmt_get_result($checkStmt);

// Loop untuk menghapus semua file gambar dari server
while ($fileData = mysqli_fetch_assoc($checkResult)) {
    $filePath = '../' . $fileData['file_path']; // Path file yang akan dihapus
    if (file_exists($filePath)) {
        unlink($filePath); // Hapus file dari folder uploads
    }
}
mysqli_stmt_close($checkStmt);

// Hapus semua entri dari tabel uploads terkait project_id
$deleteUploadsSql = "DELETE FROM uploads WHERE project_id = ?";
$deleteUploadsStmt = mysqli_prepare($conn, $deleteUploadsSql);
mysqli_stmt_bind_param($deleteUploadsStmt, "i", $project_id);
mysqli_stmt_execute($deleteUploadsStmt);
mysqli_stmt_close($deleteUploadsStmt);

// Hapus entri dari tabel projects
$deleteProjectsSql = "DELETE FROM projects WHERE id = ?";
$deleteProjectsStmt = mysqli_prepare($conn, $deleteProjectsSql);
mysqli_stmt_bind_param($deleteProjectsStmt, "i", $project_id);
if (mysqli_stmt_execute($deleteProjectsStmt)) {
    // Redirect ke halaman tracker setelah berhasil
    header("Location: ../pages/user/tracker.php?success=Proyek berhasil dihapus!");
    exit;
} else {
    echo "Gagal menghapus data dari tabel projects!";
}
mysqli_stmt_close($deleteProjectsStmt);

mysqli_close($conn);
?>
