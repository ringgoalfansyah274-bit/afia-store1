<?php
session_start();
include '../includes/config.php';

// Cek login admin
if(!isset($_SESSION['user']) || $_SESSION['user']['role'] != 'admin') {
    header('Location: login.php');
    exit;
}

// Variabel notifikasi
$error = '';
$success = '';

// ===== PROSES TAMBAH PRODUK =====
if(isset($_POST['tambah'])) {
    $nama = mysqli_real_escape_string($conn, $_POST['nama_produk']);
    $deskripsi = mysqli_real_escape_string($conn, $_POST['deskripsi']);
    $harga = (int)$_POST['harga'];
    $stok = (int)$_POST['stok'];
    $berat = (int)$_POST['berat'];
    $kategori = mysqli_real_escape_string($conn, $_POST['kategori']);
    
    $query = "INSERT INTO products (nama_produk, deskripsi, harga, stok, berat, kategori) 
              VALUES ('$nama', '$deskripsi', '$harga', '$stok', '$berat', '$kategori')";
    
    if(mysqli_query($conn, $query)) {
        $_SESSION['success'] = "✅ Produk berhasil ditambahkan!";
    } else {
        $_SESSION['error'] = "❌ Gagal menambahkan produk: " . mysqli_error($conn);
    }
    header('Location: kelola-produk.php');
    exit;
}

// ===== PROSES HAPUS PRODUK =====
if(isset($_GET['hapus'])) {
    $id = (int)$_GET['hapus'];
    
    $cek_order = mysqli_query($conn, "SELECT * FROM order_items WHERE product_id = $id");
    
    if(mysqli_num_rows($cek_order) > 0) {
        $_SESSION['error'] = "❌ Produk tidak bisa dihapus karena sudah pernah dipesan!";
    } else {
        if(mysqli_query($conn, "DELETE FROM products WHERE id = $id")) {
            $_SESSION['success'] = "✅ Produk berhasil dihapus!";
        } else {
            $_SESSION['error'] = "❌ Gagal hapus produk: " . mysqli_error($conn);
        }
    }
    header('Location: kelola-produk.php');
    exit;
}

// ===== PROSES EDIT PRODUK (LENGKAP DENGAN STOK & BERAT) =====
if(isset($_POST['edit'])) {
    $id = (int)$_POST['id'];
    $nama = mysqli_real_escape_string($conn, $_POST['nama_produk']);
    $deskripsi = mysqli_real_escape_string($conn, $_POST['deskripsi']);
    $harga = (int)$_POST['harga'];
    $stok = (int)$_POST['stok'];
    $berat = (int)$_POST['berat'];
    $kategori = mysqli_real_escape_string($conn, $_POST['kategori']);
    
    $query = "UPDATE products SET 
              nama_produk = '$nama',
              deskripsi = '$deskripsi',
              harga = '$harga',
              stok = '$stok',
              berat = '$berat',
              kategori = '$kategori'
              WHERE id = $id";
    
    if(mysqli_query($conn, $query)) {
        $_SESSION['success'] = "✅ Produk berhasil diupdate!";
    } else {
        $_SESSION['error'] = "❌ Gagal update produk: " . mysqli_error($conn);
    }
    header('Location: kelola-produk.php');
    exit;
}

// ===== PROSES UPDATE STOK CEPAT (TANPA EDIT FORM) =====
if(isset($_POST['update_stok'])) {
    $id = (int)$_POST['id'];
    $stok = (int)$_POST['stok'];
    
    $query = "UPDATE products SET stok = '$stok' WHERE id = $id";
    
    if(mysqli_query($conn, $query)) {
        $_SESSION['success'] = "✅ Stok produk berhasil diperbarui!";
    } else {
        $_SESSION['error'] = "❌ Gagal update stok: " . mysqli_error($conn);
    }
    header('Location: kelola-produk.php');
    exit;
}

// Ambil notifikasi dari session
if(isset($_SESSION['success'])) {
    $success = $_SESSION['success'];
    unset($_SESSION['success']);
}
if(isset($_SESSION['error'])) {
    $error = $_SESSION['error'];
    unset($_SESSION['error']);
}

// Ambil semua produk
$products = query("SELECT * FROM products ORDER BY id DESC");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Produk - Afia Cake</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f5f5f5;
        }
        
        .header {
            background: linear-gradient(135deg, #ff6b6b, #ff8e8e);
            color: white;
            padding: 1rem 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
        }
        
        .header h2 {
            font-size: 1.5rem;
        }
        
        .header a {
            color: white;
            text-decoration: none;
            margin-left: 20px;
            padding: 5px 15px;
            border-radius: 5px;
            background: rgba(255,255,255,0.2);
        }
        
        .container {
            max-width: 1200px;
            margin: 30px auto;
            padding: 0 20px;
        }
        
        /* Alert Messages */
        .alert {
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .alert-success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        
        .alert-error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        
        /* Tombol */
        .btn-tambah {
            background: linear-gradient(135deg, #4CAF50, #45a049);
            color: white;
            padding: 12px 25px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 1rem;
            margin-bottom: 20px;
            display: inline-block;
        }
        
        .btn-tambah:hover {
            transform: translateY(-2px);
        }
        
        /* Modal */
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.5);
            z-index: 1000;
        }
        
        .modal-content {
            background: white;
            width: 90%;
            max-width: 550px;
            margin: 50px auto;
            padding: 30px;
            border-radius: 10px;
        }
        
        .modal-content h3 {
            margin-bottom: 20px;
            color: #333;
        }
        
        .form-group {
            margin-bottom: 15px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 5px;
            color: #666;
            font-weight: 500;
        }
        
        .form-group input,
        .form-group textarea,
        .form-group select {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-family: inherit;
        }
        
        .btn-simpan {
            background: #ff6b6b;
            color: white;
            padding: 12px 25px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            width: 100%;
            font-size: 1rem;
        }
        
        .btn-simpan:hover {
            background: #e84545;
        }
        
        .close {
            float: right;
            font-size: 1.5rem;
            cursor: pointer;
            color: #999;
        }
        
        /* Tabel */
        .table-container {
            background: white;
            border-radius: 10px;
            padding: 20px;
            overflow-x: auto;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
        }
        
        th {
            background: #f8f8f8;
            padding: 12px;
            text-align: left;
            font-weight: 600;
        }
        
        td {
            padding: 12px;
            border-bottom: 1px solid #eee;
        }
        
        tr:hover {
            background: #f9f9f9;
        }
        
        .btn-edit {
            background: #2196F3;
            color: white;
            padding: 5px 10px;
            border: none;
            border-radius: 3px;
            cursor: pointer;
            margin-right: 5px;
        }
        
        .btn-hapus {
            background: #f44336;
            color: white;
            padding: 5px 10px;
            border: none;
            border-radius: 3px;
            cursor: pointer;
        }
        
        .btn-stok {
            background: #FF9800;
            color: white;
            padding: 5px 10px;
            border: none;
            border-radius: 3px;
            cursor: pointer;
            margin-right: 5px;
        }
        
        .badge {
            background: #ff6b6b;
            color: white;
            padding: 3px 8px;
            border-radius: 3px;
            font-size: 0.8rem;
        }
        
        .badge-stok {
            background: #4CAF50;
            color: white;
            padding: 3px 8px;
            border-radius: 3px;
            font-size: 0.8rem;
        }
        
        .badge-stok-habis {
            background: #dc3545;
            color: white;
            padding: 3px 8px;
            border-radius: 3px;
            font-size: 0.8rem;
        }
        
        .search-box {
            margin-bottom: 20px;
            display: flex;
            gap: 10px;
        }
        
        .search-box input {
            flex: 1;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
        }
        
        .search-box button {
            padding: 10px 20px;
            background: #ff6b6b;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }
        
        /* Form update stok cepat */
        .stok-form {
            display: flex;
            gap: 5px;
            align-items: center;
        }
        
        .stok-form input {
            width: 70px;
            padding: 5px;
            text-align: center;
        }
        
        .stok-form button {
            padding: 5px 10px;
            background: #4CAF50;
            color: white;
            border: none;
            border-radius: 3px;
            cursor: pointer;
        }
    </style>
</head>
<body>
    <div class="header">
        <h2><i class="fas fa-cake-candles"></i> Kelola Produk - Afia Cake</h2>
        <div>
            <span>Halo, <?= $_SESSION['user']['nama_lengkap'] ?></span>
            <a href="dashboard.php"><i class="fas fa-arrow-left"></i> Kembali</a>
            <a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
        </div>
    </div>
    
    <div class="container">
        <button onclick="bukaModalTambah()" class="btn-tambah">
            <i class="fas fa-plus"></i> Tambah Produk Baru
        </button>
        
        <?php if($success): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i> <?= $success ?>
            </div>
        <?php endif; ?>
        
        <?php if($error): ?>
            <div class="alert alert-error">
                <i class="fas fa-exclamation-circle"></i> <?= $error ?>
            </div>
        <?php endif; ?>
        
        <div class="search-box">
            <input type="text" id="searchInput" placeholder="Cari produk..." onkeyup="cariProduk()">
            <button onclick="cariProduk()"><i class="fas fa-search"></i> Cari</button>
        </div>
        
        <div class="table-container">
            <table id="productTable">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nama Produk</th>
                        <th>Deskripsi</th>
                        <th>Harga</th>
                        <th>Stok</th>
                        <th>Berat</th>
                        <th>Kategori</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($products as $p): ?>
                    <tr>
                        <td>#<?= $p['id'] ?></td>
                        <td><strong><?= htmlspecialchars($p['nama_produk']) ?></strong></td>
                        <td><?= htmlspecialchars(substr($p['deskripsi'], 0, 40)) ?>...</td>
                        <td>Rp <?= number_format($p['harga'], 0, ',', '.') ?></td>
                        <td>
                            <?php if($p['stok'] <= 0): ?>
                                <span class="badge-stok-habis">Habis (0)</span>
                            <?php elseif($p['stok'] <= 5): ?>
                                <span class="badge-stok"><?= $p['stok'] ?> pcs</span>
                            <?php else: ?>
                                <span class="badge-stok"><?= $p['stok'] ?> pcs</span>
                            <?php endif; ?>
                            <!-- Form update stok cepat -->
                            <form method="POST" class="stok-form" style="margin-top: 5px;">
                                <input type="hidden" name="id" value="<?= $p['id'] ?>">
                                <input type="number" name="stok" value="<?= $p['stok'] ?>" min="0">
                                <button type="submit" name="update_stok">Update</button>
                            </form>
                         </td>
                        <td><?= $p['berat'] ?> gr</td>
                        <td><span class="badge"><?= $p['kategori'] ?></span></td>
                        <td>
                            <button onclick="bukaModalEdit(<?= $p['id'] ?>, '<?= addslashes($p['nama_produk']) ?>', '<?= addslashes($p['deskripsi']) ?>', <?= $p['harga'] ?>, <?= $p['stok'] ?>, <?= $p['berat'] ?>, '<?= $p['kategori'] ?>')" class="btn-edit">
                                <i class="fas fa-edit"></i> Edit
                            </button>
                            <button onclick="hapusProduk(<?= $p['id'] ?>, '<?= addslashes($p['nama_produk']) ?>')" class="btn-hapus">
                                <i class="fas fa-trash"></i> Hapus
                            </button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
    
    <!-- MODAL TAMBAH PRODUK -->
    <div id="modalTambah" class="modal">
        <div class="modal-content">
            <span class="close" onclick="tutupModal('modalTambah')">&times;</span>
            <h3><i class="fas fa-plus-circle"></i> Tambah Produk Baru</h3>
            
            <form method="POST">
                <div class="form-group">
                    <label>Nama Produk</label>
                    <input type="text" name="nama_produk" required>
                </div>
                
                <div class="form-group">
                    <label>Deskripsi</label>
                    <textarea name="deskripsi" rows="3" required></textarea>
                </div>
                
                <div class="form-group">
                    <label>Harga (Rp)</label>
                    <input type="number" name="harga" required>
                </div>
                
                <div class="form-group">
                    <label>Stok (pcs)</label>
                    <input type="number" name="stok" value="10" required>
                </div>
                
                <div class="form-group">
                    <label>Berat (gram)</label>
                    <input type="number" name="berat" value="500" required>
                </div>
                
                <div class="form-group">
                    <label>Kategori</label>
                    <select name="kategori">
                        <option value="Best Seller">Best Seller</option>
                        <option value="Regular">Regular</option>
                        <option value="Birthday Cakes">Birthday Cakes</option>
                        <option value="Wedding Cakes">Wedding Cakes</option>
                        <option value="Cupcakes">Cupcakes</option>
                        <option value="Custom Cakes">Custom Cakes</option>
                    </select>
                </div>
                
                <button type="submit" name="tambah" class="btn-simpan">
                    <i class="fas fa-save"></i> Simpan Produk
                </button>
            </form>
        </div>
    </div>
    
    <!-- MODAL EDIT PRODUK -->
    <div id="modalEdit" class="modal">
        <div class="modal-content">
            <span class="close" onclick="tutupModal('modalEdit')">&times;</span>
            <h3><i class="fas fa-edit"></i> Edit Produk</h3>
            
            <form method="POST">
                <input type="hidden" name="id" id="edit_id">
                
                <div class="form-group">
                    <label>Nama Produk</label>
                    <input type="text" name="nama_produk" id="edit_nama" required>
                </div>
                
                <div class="form-group">
                    <label>Deskripsi</label>
                    <textarea name="deskripsi" id="edit_deskripsi" rows="3" required></textarea>
                </div>
                
                <div class="form-group">
                    <label>Harga (Rp)</label>
                    <input type="number" name="harga" id="edit_harga" required>
                </div>
                
                <div class="form-group">
                    <label>Stok (pcs)</label>
                    <input type="number" name="stok" id="edit_stok" required>
                </div>
                
                <div class="form-group">
                    <label>Berat (gram)</label>
                    <input type="number" name="berat" id="edit_berat" required>
                </div>
                
                <div class="form-group">
                    <label>Kategori</label>
                    <select name="kategori" id="edit_kategori">
                        <option value="Best Seller">Best Seller</option>
                        <option value="Regular">Regular</option>
                        <option value="Birthday Cakes">Birthday Cakes</option>
                        <option value="Wedding Cakes">Wedding Cakes</option>
                        <option value="Cupcakes">Cupcakes</option>
                        <option value="Custom Cakes">Custom Cakes</option>
                    </select>
                </div>
                
                <button type="submit" name="edit" class="btn-simpan">
                    <i class="fas fa-save"></i> Update Produk
                </button>
            </form>
        </div>
    </div>
    
    <script>
        // Buka modal tambah
        function bukaModalTambah() {
            document.getElementById('modalTambah').style.display = 'block';
        }
        
        // Buka modal edit (dengan parameter stok dan berat)
        function bukaModalEdit(id, nama, deskripsi, harga, stok, berat, kategori) {
            document.getElementById('edit_id').value = id;
            document.getElementById('edit_nama').value = nama;
            document.getElementById('edit_deskripsi').value = deskripsi;
            document.getElementById('edit_harga').value = harga;
            document.getElementById('edit_stok').value = stok;
            document.getElementById('edit_berat').value = berat;
            document.getElementById('edit_kategori').value = kategori;
            
            document.getElementById('modalEdit').style.display = 'block';
        }
        
        // Hapus produk
        function hapusProduk(id, nama) {
            if(confirm('Yakin ingin menghapus produk "' + nama + '" ?\nProduk yang sudah dipesan TIDAK BISA dihapus!')) {
                window.location.href = '?hapus=' + id;
            }
        }
        
        // Tutup modal
        function tutupModal(id) {
            document.getElementById(id).style.display = 'none';
        }
        
        // Tutup modal jika klik di luar
        window.onclick = function(event) {
            if (event.target.classList.contains('modal')) {
                event.target.style.display = 'none';
            }
        }
        
        // Auto hilangkan alert
        setTimeout(function() {
            document.querySelectorAll('.alert').forEach(a => {
                a.style.opacity = '0';
                setTimeout(() => a.style.display = 'none', 500);
            });
        }, 3000);
        
        // Pencarian
        function cariProduk() {
            var input = document.getElementById('searchInput').value.toLowerCase();
            var rows = document.getElementById('productTable').getElementsByTagName('tr');
            for(var i = 1; i < rows.length; i++) {
                var cells = rows[i].getElementsByTagName('td');
                var found = false;
                for(var j = 0; j < cells.length - 1; j++) {
                    if(cells[j].textContent.toLowerCase().indexOf(input) > -1) {
                        found = true;
                        break;
                    }
                }
                rows[i].style.display = found ? '' : 'none';
            }
        }
    </script>
</body>
</html>