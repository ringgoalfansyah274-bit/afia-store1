<?php
include 'includes/header.php';

// Proses upload gambar
$sukses = '';
$error = '';

if(isset($_POST['upload'])) {
    $product_id = $_POST['product_id'];
    $target_dir = "uploads/products/";
    
    // Buat folder jika belum ada
    if(!file_exists($target_dir)) {
        mkdir($target_dir, 0777, true);
    }
    
    $file_name = time() . '_' . basename($_FILES["gambar"]["name"]);
    $target_file = $target_dir . $file_name;
    $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
    
    // Cek apakah file gambar
    $check = getimagesize($_FILES["gambar"]["tmp_name"]);
    if($check !== false) {
        // Cek format
        if($imageFileType == "jpg" || $imageFileType == "png" || $imageFileType == "jpeg" || $imageFileType == "gif") {
            if(move_uploaded_file($_FILES["gambar"]["tmp_name"], $target_file)) {
                // Update database
                $update = "UPDATE products SET gambar = '$file_name' WHERE id = $product_id";
                if(mysqli_query($conn, $update)) {
                    $sukses = "✅ Gambar berhasil diupload untuk produk ID: $product_id";
                } else {
                    $error = "Gagal update database: " . mysqli_error($conn);
                }
            } else {
                $error = "Gagal upload file.";
            }
        } else {
            $error = "Hanya file JPG, JPEG, PNG & GIF yang diperbolehkan.";
        }
    } else {
        $error = "File bukan gambar.";
    }
}

// Ambil semua produk
$products = query("SELECT * FROM products ORDER BY id");
?>

<style>
    * { margin: 0; padding: 0; box-sizing: border-box; }
    body { font-family: 'Segoe UI', sans-serif; background: #f5f5f5; padding: 20px; }
    .container { max-width: 1000px; margin: 0 auto; background: white; padding: 30px; border-radius: 15px; box-shadow: 0 5px 20px rgba(0,0,0,0.1); }
    h2 { color: #e84545; margin-bottom: 20px; border-left: 4px solid #e84545; padding-left: 15px; }
    .alert-success { background: #d4edda; color: #155724; padding: 12px; border-radius: 5px; margin-bottom: 20px; }
    .alert-error { background: #f8d7da; color: #721c24; padding: 12px; border-radius: 5px; margin-bottom: 20px; }
    .table { width: 100%; border-collapse: collapse; }
    .table th { background: #f8f9fa; padding: 12px; text-align: left; }
    .table td { padding: 12px; border-bottom: 1px solid #eee; }
    .product-img { width: 60px; height: 60px; object-fit: cover; border-radius: 8px; background: #f0f0f0; }
    .btn-upload { background: #e84545; color: white; border: none; padding: 8px 20px; border-radius: 5px; cursor: pointer; }
    .btn-upload:hover { background: #d43a3a; }
    input[type="file"] { padding: 5px; }
    .note { background: #fff3cd; padding: 15px; border-radius: 5px; margin-bottom: 20px; color: #856404; }
</style>

<div class="container">
    <h2>🖼️ Upload Gambar Produk Afia Cake</h2>
    
    <div class="note">
        <strong>📌 Cara Upload:</strong><br>
        1. Cari produk yang ingin diganti gambarnya<br>
        2. Klik "Pilih File" lalu pilih gambar dari komputer<br>
        3. Klik "Upload Gambar"<br>
        4. Gambar akan otomatis tersimpan
    </div>
    
    <?php if($sukses): ?>
        <div class="alert-success"><?= $sukses ?></div>
    <?php endif; ?>
    
    <?php if($error): ?>
        <div class="alert-error"><?= $error ?></div>
    <?php endif; ?>
    
    <table class="table">
        <thead>
            <tr>
                <th>ID</th>
                <th>Gambar Saat Ini</th>
                <th>Nama Produk</th>
                <th>Upload Gambar Baru</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach($products as $p): ?>
            <tr style="border-bottom: 1px solid #eee;">
                <td style="padding: 12px;"><?= $p['id'] ?></td>
                <td style="padding: 12px;">
                    <img class="product-img" src="<?= !empty($p['gambar']) ? 'uploads/products/'.$p['gambar'] : 'https://images.unsplash.com/photo-1578985545062-69928b1d9587?w=60&h=60&fit=crop' ?>" alt="">
                </td>
                <td style="padding: 12px;"><strong><?= htmlspecialchars($p['nama_produk']) ?></strong></td>
                <td style="padding: 12px;">
                    <form method="POST" enctype="multipart/form-data">
                        <input type="hidden" name="product_id" value="<?= $p['id'] ?>">
                        <input type="file" name="gambar" accept="image/*" required style="margin-bottom: 5px;">
                        <button type="submit" name="upload" class="btn-upload">📤 Upload Gambar</button>
                    </form>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    
    <div style="margin-top: 30px; text-align: center;">
        <a href="cakes.php" style="color: #e84545;">← Kembali ke Halaman Produk</a>
    </div>
</div>

<?php include 'includes/footer.php'; ?>