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

$search = isset($_GET['search']) ? htmlspecialchars(trim($_GET['search'])) : '';

if ($search) {
    $stmt = $conn->prepare("SELECT * FROM produk WHERE nama_produk LIKE ? OR kategori LIKE ? ORDER BY id DESC");
    $like = "%$search%";
    $stmt->bind_param("ss", $like, $like);
} else {
    $stmt = $conn->prepare("SELECT * FROM produk ORDER BY id DESC");
}
$stmt->execute();
$result = $stmt->get_result();

$total_produk = $conn->query("SELECT COUNT(*) as total FROM produk")->fetch_assoc()['total'];
$total_stok = $conn->query("SELECT SUM(stok) as total FROM produk")->fetch_assoc()['total'] ?? 0;
$total_nilai = $conn->query("SELECT SUM(stok * harga) as total FROM produk")->fetch_assoc()['total'] ?? 0;
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inventaris ATK - Dashboard</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>📦 Inventaris ATK <span>Tugas PWL</span></h1>
            <div class="header-user">
                <span class="user-name">👤 <?= htmlspecialchars($_SESSION['username']) ?></span>
                <a href="logout.php" class="btn btn-danger">🚪 Logout</a>
                <a href="tambah.php" class="btn btn-primary">+ Tambah Barang</a>
            </div>
        </div>

        <?php if(isset($_SESSION['success'])): ?>
            <div class="alert alert-success"><?= htmlspecialchars($_SESSION['success']) ?></div>
            <?php unset($_SESSION['success']); ?>
        <?php endif; ?>

        <?php if(isset($_SESSION['error'])): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($_SESSION['error']) ?></div>
            <?php unset($_SESSION['error']); ?>
        <?php endif; ?>

        <div class="stats">
            <div class="stat-card">
                <h3>Total Produk</h3>
                <div class="value"><?= $total_produk ?></div>
            </div>
            <div class="stat-card">
                <h3>Total Stok</h3>
                <div class="value"><?= number_format($total_stok) ?></div>
            </div>
            <div class="stat-card">
                <h3>Total Nilai Inventaris</h3>
                <div class="value rupiah">Rp <?= number_format($total_nilai, 0, ',', '.') ?></div>
            </div>
        </div>

        <div class="table-wrapper">
            <div class="search-bar">
                <form method="GET" class="search-form">
                    <input type="text" name="search" class="search-input" placeholder="Cari nama atau kategori..." value="<?= $search ?>">
                    <button type="submit" class="btn btn-primary">🔍 Cari</button>
                    <?php if($search): ?>
                        <a href="index.php" class="btn btn-secondary">Reset</a>
                    <?php endif; ?>
                </form>
            </div>
            <table>
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Gambar</th>
                        <th>Nama Produk</th>
                        <th>Kategori</th>
                        <th>Stok</th>
                        <th>Harga</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
    <?php if($result->num_rows > 0): ?>
        <?php $no = 1; while($row = $result->fetch_assoc()): ?>
            <tr style="height: 70px;">  <!-- ← TAMBAHKAN INI -->
                <td style="vertical-align: middle;"><?= $no++ ?></td>
                <td style="vertical-align: middle;">
                    <?php if($row['gambar'] && file_exists("Gambar/" . $row['gambar'])): ?>
                        <img src="Gambar/<?= htmlspecialchars($row['gambar']) ?>" class="gambar-preview">
                    <?php else: ?>
                        <div class="gambar-preview" style="background:#2a2a35; display:flex; align-items:center; justify-content:center;">📄</div>
                    <?php endif; ?>
                </td>
                <td style="vertical-align: middle;"><?= htmlspecialchars($row['nama_produk']) ?></td>
                <td style="vertical-align: middle;"><?= htmlspecialchars($row['kategori']) ?></td>
                <td style="vertical-align: middle; text-align: center;"><?= number_format($row['stok']) ?></td>
                <td style="vertical-align: middle; text-align: right;">Rp <?= number_format($row['harga'], 0, ',', '.') ?></td>
                <td style="vertical-align: middle; text-align: center;">
                    <div class="action-btns">
                        <a href="edit.php?id=<?= $row['id'] ?>" class="btn btn-warning">✏️ Edit</a>
                        <button class="btn btn-danger" onclick="confirmDelete(<?= $row['id'] ?>, '<?= htmlspecialchars($row['nama_produk']) ?>')">🗑️ Hapus</button>
                    </div>
                </td>
            </tr>
        <?php endwhile; ?>
    <?php else: ?>
        <tr>
            <td colspan="7" class="empty-state">📭 Tidak ada data produk</td>
        </tr>
    <?php endif; ?>
</tbody>
            </table>
        </div>
    </div>

    <div id="deleteModal" class="modal">
        <div class="modal-content">
            <p>⚠️ Apakah Anda yakin ingin menghapus <strong id="deleteNama"></strong>?</p>
            <div class="modal-buttons">
                <button class="btn btn-secondary" onclick="closeModal()">Batal</button>
                <a href="#" id="deleteLink" class="btn btn-danger">Hapus</a>
            </div>
        </div>
    </div>

    <script>
        function confirmDelete(id, nama) {
            document.getElementById('deleteNama').innerText = nama;
            document.getElementById('deleteLink').href = 'hapus.php?id=' + id;
            document.getElementById('deleteModal').style.display = 'flex';
        }
        
        function closeModal() {
            document.getElementById('deleteModal').style.display = 'none';
        }
        
        window.onclick = function(event) {
            let modal = document.getElementById('deleteModal');
            if (event.target == modal) {
                modal.style.display = 'none';
            }
        }
    </script>
</body>
</html>
<?php $stmt->close(); $conn->close(); ?>