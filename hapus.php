<?php
session_start();

if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header("Location: login.php");
    exit;
}

if (isset($_SESSION['login_time']) && (time() - $_SESSION['login_time'] > 86400)) {
    session_unset();
    session_destroy();
    header("Location: login.php?expired=1");
    exit;
}

include 'koneksi.php';

$id = filter_var($_GET['id'], FILTER_VALIDATE_INT);
if (!$id) {
    header("Location: index.php");
    exit;
}

$stmt = $conn->prepare("SELECT gambar FROM produk WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$produk = $result->fetch_assoc();

if ($produk && $produk['gambar'] && file_exists("Gambar/" . $produk['gambar'])) {
    unlink("Gambar/" . $produk['gambar']);
}

$stmt->close();

$stmt = $conn->prepare("DELETE FROM produk WHERE id = ?");
$stmt->bind_param("i", $id);

if ($stmt->execute()) {
    $_SESSION['success'] = "Barang berhasil dihapus!";
} else {
    $_SESSION['error'] = "Gagal menghapus barang!";
}

$stmt->close();
$conn->close();
header("Location: index.php");
exit;
?>