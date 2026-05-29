<?php
session_start();
include 'includes/config.php';

// Ambil nomor order dari URL
$order_number = isset($_GET['order']) ? $_GET['order'] : '';

if(empty($order_number)) {
    header('Location: index.php');
    exit;
}

// Ambil data order dari database
$query = "SELECT * FROM orders WHERE order_number = '$order_number'";
$result = mysqli_query($conn, $query);
$order = mysqli_fetch_assoc($result);

if(!$order) {
    // Order tidak ditemukan
    $error = "Order tidak ditemukan";
}

// Proses upload bukti pembayaran
$sukses = '';
$error = '';

if(isset($_POST['konfirmasi'])) {
    $bank = mysqli_real_escape_string($conn, $_POST['bank']);
    $atas_nama = mysqli_real_escape_string($conn, $_POST['atas_nama']);
    $jumlah = mysqli_real_escape_string($conn, $_POST['jumlah']);
    $order_id = $order['id'];
    
    // Validasi file upload
    if($_FILES['bukti']['error'] == 0) {
        $target_dir = "uploads/bukti/";
        
        // Buat folder jika belum ada
        if(!file_exists($target_dir)) {
            mkdir($target_dir, 0777, true);
        }
        
        $file_name = time() . '_' . basename($_FILES["bukti"]["name"]);
        $target_file = $target_dir . $file_name;
        $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
        
        // Cek apakah file gambar
        $check = getimagesize($_FILES["bukti"]["tmp_name"]);
        if($check === false) {
            $error = "File bukan gambar.";
        }
        
        // Cek format file
        if($imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg" && $imageFileType != "gif") {
            $error = "Hanya file JPG, JPEG, PNG & GIF yang diperbolehkan.";
        }
        
        // Cek ukuran file (max 2MB)
        if($_FILES["bukti"]["size"] > 2000000) {
            $error = "Ukuran file maksimal 2MB.";
        }
        
        // Jika tidak ada error, upload file
        if(empty($error)) {
            if(move_uploaded_file($_FILES["bukti"]["tmp_name"], $target_file)) {
                // Update status order
                $update = "UPDATE orders SET 
                           status = 'Menunggu Verifikasi',
                           bukti_pembayaran = '$file_name',
                           bank = '$bank',
                           atas_nama = '$atas_nama',
                           jumlah_bayar = '$jumlah'
                           WHERE id = $order_id";
                
                if(mysqli_query($conn, $update)) {
                    $sukses = "Bukti pembayaran berhasil diupload. Admin akan segera memverifikasi.";
                    
                    // Kirim notifikasi ke admin via WhatsApp (opsional)
                    $wa_admin = "6281234567890"; // Ganti dengan nomor WA admin
                    $pesan = "🔔 Konfirmasi Pembayaran Baru!%0A";
                    $pesan .= "Order: $order_number%0A";
                    $pesan .= "Bank: $bank%0A";
                    $pesan .= "Atas Nama: $atas_nama%0A";
                    $pesan .= "Jumlah: Rp " . number_format($jumlah, 0, ',', '.') . "%0A";
                    $pesan .= "Silahkan cek di dashboard admin.";
                    
                    // Bisa redirect ke wa atau dikirim background
                    // header("Location: https://wa.me/$wa_admin?text=$pesan");
                    
                } else {
                    $error = "Gagal update status: " . mysqli_error($conn);
                }
            } else {
                $error = "Gagal upload file.";
            }
        }
    } else {
        $error = "Pilih file bukti transfer terlebih dahulu.";
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Konfirmasi Pembayaran - Afia Store</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #f5f5f5, #fff);
            min-height: 100vh;
            padding: 20px;
        }
        
        .container {
            max-width: 600px;
            margin: 50px auto;
        }
        
        .card {
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.1);
            overflow: hidden;
        }
        
        .card-header {
            background: linear-gradient(135deg, #ff6b6b, #ff8e8e);
            color: white;
            padding: 30px;
            text-align: center;
        }
        
        .card-header i {
            font-size: 4rem;
            margin-bottom: 15px;
        }
        
        .card-header h1 {
            font-size: 2rem;
            margin-bottom: 10px;
        }
        
        .card-body {
            padding: 40px;
        }
        
        .order-info {
            background: #f8f9fa;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 30px;
        }
        
        .order-info .row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
            padding-bottom: 10px;
            border-bottom: 1px solid #eee;
        }
        
        .order-info .row:last-child {
            border-bottom: none;
            margin-bottom: 0;
            padding-bottom: 0;
        }
        
        .order-info .label {
            color: #666;
            font-weight: 500;
        }
        
        .order-info .value {
            font-weight: bold;
            color: #333;
        }
        
        .order-info .value.total {
            color: #ff6b6b;
            font-size: 1.2rem;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: #555;
            font-weight: 500;
        }
        
        .form-group input,
        .form-group select {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid #eee;
            border-radius: 8px;
            font-size: 1rem;
            transition: all 0.3s;
        }
        
        .form-group input:focus,
        .form-group select:focus {
            outline: none;
            border-color: #ff6b6b;
            box-shadow: 0 0 0 4px rgba(255,107,107,0.1);
        }
        
        .form-group input[type="file"] {
            padding: 10px;
            background: #f8f9fa;
            border: 2px dashed #ff6b6b;
        }
        
        .btn-konfirmasi {
            width: 100%;
            padding: 15px;
            background: linear-gradient(135deg, #4CAF50, #45a049);
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 1.1rem;
            font-weight: bold;
            cursor: pointer;
            transition: all 0.3s;
        }
        
        .btn-konfirmasi:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 30px rgba(76,175,80,0.3);
        }
        
        .btn-kembali {
            display: inline-block;
            margin-top: 20px;
            color: #ff6b6b;
            text-decoration: none;
        }
        
        .btn-kembali i {
            margin-right: 5px;
        }
        
        .alert {
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
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
        
        .info-box {
            background: #e3f2fd;
            border-left: 4px solid #2196F3;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        
        .info-box i {
            color: #2196F3;
            margin-right: 10px;
        }
        
        .info-box ul {
            margin-left: 30px;
            margin-top: 10px;
        }
        
        .info-box li {
            margin-bottom: 5px;
        }
        
        .payment-detail {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 8px;
            margin: 20px 0;
            font-family: monospace;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="card">
            <div class="card-header">
                <i class="fas fa-upload"></i>
                <h1>Konfirmasi Pembayaran</h1>
                <p>Upload bukti transfer untuk mempercepat proses</p>
            </div>
            
            <div class="card-body">
                <?php if(isset($error) && $error): ?>
                    <div class="alert alert-error">
                        <i class="fas fa-exclamation-circle"></i> <?= $error ?>
                    </div>
                <?php endif; ?>
                
                <?php if(isset($sukses) && $sukses): ?>
                    <div class="alert alert-success">
                        <i class="fas fa-check-circle"></i> <?= $sukses ?>
                    </div>
                    
                    <div style="text-align: center; margin-top: 20px;">
                        <a href="index.php" class="btn-konfirmasi" style="width: auto; padding: 12px 40px;">
                            <i class="fas fa-home"></i> Kembali ke Home
                        </a>
                    </div>
                <?php else: ?>
                
                <?php if($order): ?>
                    <div class="order-info">
                        <div class="row">
                            <span class="label">Nomor Order</span>
                            <span class="value">#<?= $order['order_number'] ?></span>
                        </div>
                        <div class="row">
                            <span class="label">Total Pembayaran</span>
                            <span class="value total">Rp <?= number_format($order['total_amount'], 0, ',', '.') ?></span>
                        </div>
                        <div class="row">
                            <span class="label">Metode Pembayaran</span>
                            <span class="value"><?= $order['payment_method'] ?></span>
                        </div>
                        <div class="row">
                            <span class="label">Status</span>
                            <span class="value"><?= $order['status'] ?></span>
                        </div>
                    </div>
                    
                    <!-- Informasi Rekening / E-Wallet -->
                    <div class="info-box">
                        <i class="fas fa-info-circle"></i>
                        <strong>Instruksi Pembayaran:</strong>
                        <ul>
                            <?php if($order['payment_method'] == 'Transfer Bank'): ?>
                                <li>Bank BCA: 1234567890 a.n. Afia Store</li>
                                <li>Bank Mandiri: 9876543210 a.n. Afia Store</li>
                                <li>Bank BNI: 5555555555 a.n. Afia Store</li>
                            <?php elseif($order['payment_method'] == 'DANA'): ?>
                                <li>Nomor DANA: 081234567890 a.n. Afia Store</li>
                                <li>Scan QR Code yang muncul di halaman checkout</li>
                            <?php elseif($order['payment_method'] == 'OVO'): ?>
                                <li>Nomor OVO: 081298765432 a.n. Afia Store</li>
                                <li>Transfer ke nomor OVO di atas</li>
                            <?php elseif($order['payment_method'] == 'GoPay'): ?>
                                <li>Nomor GoPay: 081234567891 a.n. Afia Store Official</li>
                                <li>Transfer ke nomor GoPay di atas</li>
                            <?php endif; ?>
                        </ul>
                    </div>
                    
                    <form method="POST" enctype="multipart/form-data">
                        <div class="form-group">
                            <label><i class="fas fa-university"></i> Bank / E-Wallet yang Digunakan</label>
                            <select name="bank" required>
                                <option value="">Pilih metode</option>
                                <option value="BCA">BCA</option>
                                <option value="Mandiri">Mandiri</option>
                                <option value="BNI">BNI</option>
                                <option value="DANA">DANA</option>
                                <option value="OVO">OVO</option>
                                <option value="GoPay">GoPay</option>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label><i class="fas fa-user"></i> Nama Pengirim</label>
                            <input type="text" name="atas_nama" placeholder="Nama sesuai rekening" required>
                        </div>
                        
                        <div class="form-group">
                            <label><i class="fas fa-money-bill-wave"></i> Jumlah Transfer</label>
                            <input type="number" name="jumlah" value="<?= $order['total_amount'] ?>" required>
                        </div>
                        
                        <div class="form-group">
                            <label><i class="fas fa-image"></i> Upload Bukti Transfer</label>
                            <input type="file" name="bukti" accept="image/*" required>
                            <small style="color: #999;">Format: JPG, PNG, GIF (max 2MB)</small>
                        </div>
                        
                        <button type="submit" name="konfirmasi" class="btn-konfirmasi">
                            <i class="fas fa-paper-plane"></i> Kirim Konfirmasi
                        </button>
                    </form>
                    
                <?php else: ?>
                    <div class="alert alert-error">
                        <i class="fas fa-exclamation-circle"></i> Order tidak ditemukan. Silakan cek nomor order Anda.
                    </div>
                <?php endif; ?>
                
                <?php endif; ?>
                
                <div style="text-align: center; margin-top: 20px;">
                    <a href="sukses.php?order=<?= urlencode($order_number) ?>" class="btn-kembali">
                        <i class="fas fa-arrow-left"></i> Kembali ke Halaman Sukses
                    </a>
                </div>
            </div>
        </div>
    </div>
</body>
</html>