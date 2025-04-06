<?php
require 'database.php';
session_start();

// Pastikan user sudah login
$user_id = $_SESSION['id'] ?? null;
if (!$user_id) {
    header("Location: ../index.php");
    exit;
}

// Pastikan request adalah POST dan ada file yang diupload
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_FILES['image'])) {
    $project_id = $_POST['project_id'] ?? null;
    if (!$project_id) {
        die("ID proyek tidak ditemukan!");
    }

    $uploadDir = '../uploads/'; // Folder tempat menyimpan file
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true); // Buat folder jika belum ada
    }

    $fileName = uniqid() . "_" . basename($_FILES["image"]["name"]); // Buat nama unik
    $targetFilePath = $uploadDir . $fileName;
    $fileType = strtolower(pathinfo($targetFilePath, PATHINFO_EXTENSION));

    // Validasi jenis file
    $allowedTypes = ['jpg', 'jpeg', 'png'];
    if (!in_array($fileType, $allowedTypes)) {
        die("Format file tidak diizinkan!");
    }

    // Pindahkan file ke folder uploads
    if (move_uploaded_file($_FILES["image"]["tmp_name"], $targetFilePath)) {
        // Simpan path ke database
        $filePathDb = "uploads/" . $fileName;
        $insertSql = "INSERT INTO uploads (project_id, file_path, uploaded_at) VALUES (?, ?, NOW())";
        $insertStmt = mysqli_prepare($conn, $insertSql);
        mysqli_stmt_bind_param($insertStmt, "is", $project_id, $filePathDb);

        if (mysqli_stmt_execute($insertStmt)) {
            header("Location: ../pages/user/tracker.php?success=Gambar berhasil diupload!");
            exit;
        } else {
            echo "Gagal menyimpan data ke database!";
        }
        mysqli_stmt_close($insertStmt);
    } else {
        echo "Gagal mengunggah file!";
    }
} else {
    echo "Permintaan tidak valid!";
}

mysqli_close($conn);
?>
