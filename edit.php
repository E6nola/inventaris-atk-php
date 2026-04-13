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

$stmt = $conn->prepare("SELECT * FROM produk WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$produk = $result->fetch_assoc();

if (!$produk) {
    header("Location: index.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Barang - Inventaris ATK</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <div class="form-container">
            <h2>✏️ Edit Barang</h2>
            
            <?php if(isset($_SESSION['error'])): ?>
                <div class="alert alert-danger"><?= htmlspecialchars($_SESSION['error']) ?></div>
                <?php unset($_SESSION['error']); ?>
            <?php endif; ?>
            
            <form action="proses_upload.php" method="POST" enctype="multipart/form-data" id="formEdit">
                <input type="hidden" name="id" value="<?= $produk['id'] ?>">
                <input type="hidden" name="gambar_lama" value="<?= htmlspecialchars($produk['gambar']) ?>">
                
                <div class="form-group">
                    <label>Nama Produk</label>
                    <input type="text" name="nama_produk" required value="<?= htmlspecialchars($produk['nama_produk']) ?>">
                </div>
                
                <div class="form-group">
                    <label>Kategori</label>
                    <select name="kategori" required>
                        <option value="">Pilih Kategori</option>
                        <option value="Alat Tulis" <?= $produk['kategori'] == 'Alat Tulis' ? 'selected' : '' ?>>✏️ Alat Tulis</option>
                        <option value="Kertas" <?= $produk['kategori'] == 'Kertas' ? 'selected' : '' ?>>📄 Kertas</option>
                        <option value="Alat Kantor" <?= $produk['kategori'] == 'Alat Kantor' ? 'selected' : '' ?>>📎 Alat Kantor</option>
                        <option value="Elektronik" <?= $produk['kategori'] == 'Elektronik' ? 'selected' : '' ?>>💻 Elektronik</option>
                        <option value="Lainnya" <?= $produk['kategori'] == 'Lainnya' ? 'selected' : '' ?>>📦 Lainnya</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label>Stok</label>
                    <input type="number" name="stok" required min="0" value="<?= $produk['stok'] ?>">
                </div>
                
                <div class="form-group">
                    <label>Harga (Rp)</label>
                    <input type="number" name="harga" required min="0" value="<?= $produk['harga'] ?>">
                </div>
                
                <div class="form-group">
                    <label>Foto Produk</label>
                    
                    <?php if($produk['gambar'] && file_exists("Gambar/" . $produk['gambar'])): ?>
                        <div class="gambar-lama" id="gambarLama">
                            <p>Gambar saat ini:</p>
                            <img src="Gambar/<?= htmlspecialchars($produk['gambar']) ?>" alt="Gambar lama">
                        </div>
                    <?php endif; ?>
                    
                    <div class="upload-area" id="uploadArea">
                        <div class="upload-icon">📸</div>
                        <div class="upload-text">
                            <span>Klik atau tarik gambar baru</span> untuk mengganti
                        </div>
                        <input type="file" name="gambar" id="gambarInput" accept="image/jpeg,image/png,image/jpg" style="display: none;">
                    </div>
                    
                    <div class="image-preview" id="imagePreview">
                        <img id="previewImg" src="#" alt="Preview">
                        <button type="button" class="btn btn-secondary" style="margin-top: 0.5rem; padding: 0.25rem 0.75rem;" onclick="resetPreview()">Batalkan</button>
                    </div>
                    <small>Kosongkan jika tidak ingin mengubah gambar</small>
                </div>
                
                <div class="form-actions">
                    <button type="submit" name="edit" class="btn btn-primary">💾 Update</button>
                    <a href="index.php" class="btn btn-secondary">Batal</a>
                </div>
            </form>
        </div>
    </div>

    <script>
        const uploadArea = document.getElementById('uploadArea');
        const gambarInput = document.getElementById('gambarInput');
        const imagePreview = document.getElementById('imagePreview');
        const previewImg = document.getElementById('previewImg');
        const gambarLama = document.getElementById('gambarLama');

        uploadArea.addEventListener('click', () => {
            gambarInput.click();
        });

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
                if (gambarLama) {
                    gambarLama.style.display = 'none';
                }
            }
            reader.readAsDataURL(file);
        }

        function resetPreview() {
            imagePreview.classList.remove('active');
            uploadArea.style.display = 'block';
            if (gambarLama) {
                gambarLama.style.display = 'block';
            }
            gambarInput.value = '';
        }
    </script>
</body>
</html>
<?php $stmt->close(); $conn->close(); ?>