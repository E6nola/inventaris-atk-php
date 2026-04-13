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

function compressImage($source, $destination, $quality = 75) {
    $info = getimagesize($source);
    
    if ($info['mime'] == 'image/jpeg') {
        $image = imagecreatefromjpeg($source);
    } elseif ($info['mime'] == 'image/png') {
        $image = imagecreatefrompng($source);
        imagepalettetotruecolor($image);
        imagealphablending($image, true);
        imagesavealpha($image, true);
    } else {
        return false;
    }
    
    $width = imagesx($image);
    $height = imagesy($image);
    $max_width = 800;
    
    if ($width > $max_width) {
        $new_width = $max_width;
        $new_height = floor($height * ($max_width / $width));
        $resized = imagecreatetruecolor($new_width, $new_height);
        
        if ($info['mime'] == 'image/png') {
            imagealphablending($resized, false);
            imagesavealpha($resized, true);
            $transparent = imagecolorallocatealpha($resized, 255, 255, 255, 127);
            imagefilledrectangle($resized, 0, 0, $new_width, $new_height, $transparent);
        }
        
        imagecopyresampled($resized, $image, 0, 0, 0, 0, $new_width, $new_height, $width, $height);
        imagedestroy($image);
        $image = $resized;
    }
    
    if ($info['mime'] == 'image/jpeg') {
        imagejpeg($image, $destination, $quality);
    } elseif ($info['mime'] == 'image/png') {
        $pngQuality = floor(($quality - 10) / 10);
        imagepng($image, $destination, $pngQuality);
    }
    
    imagedestroy($image);
    return true;
}

// ========== HANDLE EDIT ==========
if (isset($_POST['edit'])) {
    $id = filter_var($_POST['id'], FILTER_VALIDATE_INT);
    $nama_produk = htmlspecialchars(trim($_POST['nama_produk']));
    $kategori = htmlspecialchars(trim($_POST['kategori']));
    $stok = filter_var($_POST['stok'], FILTER_VALIDATE_INT);
    $harga = filter_var($_POST['harga'], FILTER_VALIDATE_INT);
    $gambar_lama = $_POST['gambar_lama'] ?? null;
    
    if (!$id || empty($nama_produk) || empty($kategori) || $stok === false || $harga === false) {
        $_SESSION['error'] = "Semua field harus diisi dengan benar!";
        header("Location: edit.php?id=$id");
        exit;
    }
    
    $gambar_name = $gambar_lama;
    
    if (isset($_FILES['gambar']) && $_FILES['gambar']['error'] == 0) {
        $allowed = ['jpg', 'jpeg', 'png'];
        $file_ext = strtolower(pathinfo($_FILES['gambar']['name'], PATHINFO_EXTENSION));
        
        if (!in_array($file_ext, $allowed)) {
            $_SESSION['error'] = "Hanya file JPG, JPEG, PNG yang diperbolehkan!";
            header("Location: edit.php?id=$id");
            exit;
        }
        
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime = finfo_file($finfo, $_FILES['gambar']['tmp_name']);
        finfo_close($finfo);
        
        if (!in_array($mime, ['image/jpeg', 'image/png'])) {
            $_SESSION['error'] = "File bukan gambar yang valid!";
            header("Location: edit.php?id=$id");
            exit;
        }
        
        if ($_FILES['gambar']['size'] > 2 * 1024 * 1024) {
            $_SESSION['error'] = "Ukuran file maksimal 2MB!";
            header("Location: edit.php?id=$id");
            exit;
        }
        
        if ($gambar_lama && file_exists("Gambar/" . $gambar_lama)) {
            unlink("Gambar/" . $gambar_lama);
        }
        
        if (!file_exists('Gambar')) {
            mkdir('Gambar', 0777, true);
        }
        
        $gambar_name = time() . '_' . uniqid() . '.' . $file_ext;
        $upload_path = 'Gambar/' . $gambar_name;
        
        if (!compressImage($_FILES['gambar']['tmp_name'], $upload_path, 70)) {
            move_uploaded_file($_FILES['gambar']['tmp_name'], $upload_path);
        }
    }
    
    $stmt = $conn->prepare("UPDATE produk SET nama_produk=?, kategori=?, stok=?, harga=?, gambar=? WHERE id=?");
    $stmt->bind_param("ssiisi", $nama_produk, $kategori, $stok, $harga, $gambar_name, $id);
    
    if ($stmt->execute()) {
        $_SESSION['success'] = "Barang berhasil diupdate!";
    } else {
        $_SESSION['error'] = "Gagal mengupdate barang!";
    }
    
    $stmt->close();
    $conn->close();
    header("Location: index.php");
    exit;
}

// ========== HANDLE TAMBAH ==========
if (isset($_POST['tambah'])) {
    $nama_produk = htmlspecialchars(trim($_POST['nama_produk']));
    $kategori = htmlspecialchars(trim($_POST['kategori']));
    $stok = filter_var($_POST['stok'], FILTER_VALIDATE_INT);
    $harga = filter_var($_POST['harga'], FILTER_VALIDATE_INT);
    
    if (empty($nama_produk) || empty($kategori) || $stok === false || $harga === false) {
        $_SESSION['error'] = "Semua field harus diisi dengan benar!";
        header("Location: tambah.php");
        exit;
    }
    
    $gambar_name = null;
    
    if (isset($_FILES['gambar']) && $_FILES['gambar']['error'] == 0) {
        $allowed = ['jpg', 'jpeg', 'png'];
        $file_ext = strtolower(pathinfo($_FILES['gambar']['name'], PATHINFO_EXTENSION));
        
        if (!in_array($file_ext, $allowed)) {
            $_SESSION['error'] = "Hanya file JPG, JPEG, PNG yang diperbolehkan!";
            header("Location: tambah.php");
            exit;
        }
        
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime = finfo_file($finfo, $_FILES['gambar']['tmp_name']);
        finfo_close($finfo);
        
        if (!in_array($mime, ['image/jpeg', 'image/png'])) {
            $_SESSION['error'] = "File bukan gambar yang valid!";
            header("Location: tambah.php");
            exit;
        }
        
        if ($_FILES['gambar']['size'] > 2 * 1024 * 1024) {
            $_SESSION['error'] = "Ukuran file maksimal 2MB!";
            header("Location: tambah.php");
            exit;
        }
        
        if (!file_exists('Gambar')) {
            mkdir('Gambar', 0777, true);
        }
        
        $gambar_name = time() . '_' . uniqid() . '.' . $file_ext;
        $upload_path = 'Gambar/' . $gambar_name;
        
        if (!compressImage($_FILES['gambar']['tmp_name'], $upload_path, 70)) {
            move_uploaded_file($_FILES['gambar']['tmp_name'], $upload_path);
        }
    }
    
    if ($gambar_name) {
        $stmt = $conn->prepare("INSERT INTO produk (nama_produk, kategori, stok, harga, gambar) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("ssiis", $nama_produk, $kategori, $stok, $harga, $gambar_name);
    } else {
        $stmt = $conn->prepare("INSERT INTO produk (nama_produk, kategori, stok, harga) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssii", $nama_produk, $kategori, $stok, $harga);
    }
    
    if ($stmt->execute()) {
        $_SESSION['success'] = "Barang berhasil ditambahkan!";
    } else {
        $_SESSION['error'] = "Gagal menambahkan barang: " . $conn->error;
    }
    
    $stmt->close();
    $conn->close();
    header("Location: index.php");
    exit;
}

header("Location: index.php");
exit;
?>