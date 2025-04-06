<?php
require 'database.php';
session_start();

$user_id = $_SESSION['id'] ?? null;
if (!$user_id) {
    header("Location: ../index.php");
    exit;
}

header('Content-Type: application/json');

$user_id = $_SESSION['id'] ?? null;
if (!$user_id) {
    echo json_encode(["success" => false, "error" => "Unauthorized"]);
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['upload_id']) && isset($_POST['file_path'])) {
    $upload_id = $_POST['upload_id'];
    $filePath = '../' . $_POST['file_path']; // Path relatif dari root proyek

    // Cek apakah file ada
    if (file_exists($filePath)) {
        unlink($filePath); // Hapus file dari server
    }

    // Hapus entri dari database
    $deleteSql = "DELETE FROM uploads WHERE id = ?";
    $deleteStmt = mysqli_prepare($conn, $deleteSql);
    mysqli_stmt_bind_param($deleteStmt, "i", $upload_id);

    if (mysqli_stmt_execute($deleteStmt)) {
        echo json_encode(["success" => true]);
    } else {
        echo json_encode(["success" => false, "error" => "Gagal menghapus dari database"]);
    }

    mysqli_stmt_close($deleteStmt);
} else {
    echo json_encode(["success" => false, "error" => "Invalid request"]);
}

mysqli_close($conn);
?>
