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
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Barang - Inventaris ATK</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <div class="form-container">
            <h2>➕ Tambah Barang Baru</h2>
            
            <?php if(isset($_SESSION['error'])): ?>
                <div class="alert alert-danger"><?= htmlspecialchars($_SESSION['error']) ?></div>
                <?php unset($_SESSION['error']); ?>
            <?php endif; ?>
            
            <form action="proses_upload.php" method="POST" enctype="multipart/form-data" id="formTambah">
                <div class="form-group">
                    <label>Nama Produk</label>
                    <input type="text" name="nama_produk" required placeholder="Contoh: Pulpen Joyko">
                </div>
                
                <div class="form-group">
                    <label>Kategori</label>
                    <select name="kategori" required>
                        <option value="">Pilih Kategori</option>
                        <option value="Alat Tulis">✏️ Alat Tulis</option>
                        <option value="Kertas">📄 Kertas</option>
                        <option value="Alat Kantor">📎 Alat Kantor</option>
                        <option value="Elektronik">💻 Elektronik</option>
                        <option value="Lainnya">📦 Lainnya</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label>Stok</label>
                    <input type="number" name="stok" required min="0" value="0">
                </div>
                
                <div class="form-group">
                    <label>Harga (Rp)</label>
                    <input type="number" name="harga" required min="0" placeholder="10000">
                </div>
                
                <div class="form-group">
                    <label>Foto Produk</label>
                    <div class="upload-area" id="uploadArea">
                        <div class="upload-icon">📸</div>
                        <div class="upload-text">
                            <span>Klik atau tarik gambar</span> ke sini
                        </div>
                        <input type="file" name="gambar" id="gambarInput" accept="image/jpeg,image/png,image/jpg" style="display: none;">
                    </div>
                    <div class="image-preview" id="imagePreview">
                        <img id="previewImg" src="#" alt="Preview">
                    </div>
                    <small>Maksimal 2MB, format JPG/PNG (akan dikompres otomatis)</small>
                </div>
                
                <div class="form-actions">
                    <button type="submit" name="tambah" class="btn btn-primary">💾 Simpan</button>
                    <a href="index.php" class="btn btn-secondary">Kembali</a>
                </div>
            </form>
        </div>
    </div>

    <script>
        const uploadArea = document.getElementById('uploadArea');
        const gambarInput = document.getElementById('gambarInput');
        const imagePreview = document.getElementById('imagePreview');
        const previewImg = document.getElementById('previewImg');

        // Klik area upload
        uploadArea.addEventListener('click', () => {
            gambarInput.click();
        });

        // Drag & Drop
        uploadArea.addEventListener('dragover', (e) => {
            e.preventDefault();
            uploadArea.classList.add('drag-over');
        });

        uploadArea.addEventListener('dragleave', () => {
            uploadArea.classList.remove('drag-over');
        });

        uploadArea.addEventListener('drop', (e) => {
            e.preventDefault();
            uploadArea.classList.remove('drag-over');
            const file = e.dataTransfer.files[0];
            if (file && file.type.match('image.*')) {
                gambarInput.files = e.dataTransfer.files;
                previewImage(file);
            }
        });

        // Preview saat pilih file
        gambarInput.addEventListener('change', (e) => {
            if (gambarInput.files && gambarInput.files[0]) {
                previewImage(gambarInput.files[0]);
            }
        });

        function previewImage(file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                previewImg.src = e.target.result;
                imagePreview.classList.add('active');
                uploadArea.style.display = 'none';
            }
            reader.readAsDataURL(file);
        }

        // Reset preview (opsional, untuk kasus tertentu)
        function resetPreview() {
            imagePreview.classList.remove('active');
            uploadArea.style.display = 'block';
            gambarInput.value = '';
        }
    </script>
</body>
</html>