<?php
session_start();
include 'includes/config.php';

// ===== CEK LOGIN PEMBELI =====
if(!isset($_SESSION['customer'])) {
    $_SESSION['notification'] = [
        'type' => 'error',
        'message' => '⚠️ Silakan login terlebih dahulu untuk melanjutkan checkout!'
    ];
    header('Location: login-customer.php');
    exit;
}

// Ambil data customer yang login
$customer = $_SESSION['customer'];

// Cek apakah keranjang kosong
if(empty($_SESSION['cart'])) {
    header('Location: keranjang.php');
    exit;
}

// Proses checkout
if(isset($_POST['checkout'])) {
    $customer_id = $customer['id'];
    $nama = mysqli_real_escape_string($conn, $_POST['nama']);
    $phone = mysqli_real_escape_string($conn, $_POST['phone']);
    $alamat = mysqli_real_escape_string($conn, $_POST['alamat']);
    $payment = mysqli_real_escape_string($conn, $_POST['payment']);
    $kode_voucher = isset($_POST['voucher']) ? mysqli_real_escape_string($conn, $_POST['voucher']) : '';
    
    // Validasi metode pembayaran
    $valid_payments = ['Transfer Bank', 'Bayar di Tempat (COD)', 'DANA', 'OVO', 'GoPay'];
    if(!in_array($payment, $valid_payments)) {
        $payment = 'Transfer Bank'; // Default jika tidak valid
    }
    
    // Hitung total
    $total = 0;
    if(isset($_SESSION['cart']) && is_array($_SESSION['cart'])) {
        foreach($_SESSION['cart'] as $item) {
            $total += $item['harga'] * $item['quantity'];
        }
    }
    
    // Proses diskon voucher jika ada
    $diskon = 0;
    if(!empty($kode_voucher)) {
        // Cek apakah kode voucher valid
        $query_voucher = "SELECT * FROM birthday_club WHERE kode_voucher = '$kode_voucher'";
        $result_voucher = mysqli_query($conn, $query_voucher);
        
        if(mysqli_num_rows($result_voucher) > 0) {
            $user_voucher = mysqli_fetch_assoc($result_voucher);
            $birthday = $user_voucher['tanggal_lahir'];
            $birthday_this_year = date('Y') . '-' . date('m-d', strtotime($birthday));
            $start = date('Y-m-d', strtotime($birthday_this_year . ' -3 days'));
            $end = date('Y-m-d', strtotime($birthday_this_year . ' +3 days'));
            $today = date('Y-m-d');
            
            if($today >= $start && $today <= $end) {
                $diskon = $total * 0.2; // 20% diskon
            }
        }
    }
    
    $total_setelah_diskon = $total - $diskon;
    
    // Buat nomor order unik
    $order_number = 'ORD' . date('Ymd') . rand(1000, 9999);
    
    // ===== CEK KETERSEDIAAN KOLOM =====
    $check_diskon = mysqli_query($conn, "SHOW COLUMNS FROM orders LIKE 'diskon'");
    $check_voucher = mysqli_query($conn, "SHOW COLUMNS FROM orders LIKE 'kode_voucher'");
    $check_batas = mysqli_query($conn, "SHOW COLUMNS FROM orders LIKE 'batas_pembayaran'");
    $check_customer = mysqli_query($conn, "SHOW COLUMNS FROM orders LIKE 'customer_id'");
    
    $has_diskon = mysqli_num_rows($check_diskon) > 0;
    $has_voucher = mysqli_num_rows($check_voucher) > 0;
    $has_batas = mysqli_num_rows($check_batas) > 0;
    $has_customer = mysqli_num_rows($check_customer) > 0;
    
    if($has_diskon && $has_voucher && $has_batas && $has_customer) {
        // Semua kolom tersedia
        if($payment == 'Bayar di Tempat (COD)') {
            $query = "INSERT INTO orders (customer_id, order_number, customer_name, customer_phone, alamat, total_amount, diskon, kode_voucher, payment_method, status) 
                      VALUES ('$customer_id', '$order_number', '$nama', '$phone', '$alamat', '$total_setelah_diskon', '$diskon', '$kode_voucher', '$payment', 'Diproses')";
        } else {
            $query = "INSERT INTO orders (customer_id, order_number, customer_name, customer_phone, alamat, total_amount, diskon, kode_voucher, payment_method, status, batas_pembayaran) 
                      VALUES ('$customer_id', '$order_number', '$nama', '$phone', '$alamat', '$total_setelah_diskon', '$diskon', '$kode_voucher', '$payment', 'Menunggu Pembayaran', DATE_ADD(NOW(), INTERVAL 24 HOUR))";
        }
    } else {
        // Kolom belum lengkap, gunakan query minimal
        $status = ($payment == 'Bayar di Tempat (COD)') ? 'Diproses' : 'Menunggu Pembayaran';
        $query = "INSERT INTO orders (order_number, customer_name, customer_phone, alamat, total_amount, payment_method, status) 
                  VALUES ('$order_number', '$nama', '$phone', '$alamat', '$total_setelah_diskon', '$payment', '$status')";
    }
    // ===== AKHIR CEK KOLOM =====
    
    if(mysqli_query($conn, $query)) {
        $order_id = mysqli_insert_id($conn);
        
        // Simpan detail pesanan ke order_items
        if(isset($_SESSION['cart']) && is_array($_SESSION['cart'])) {
            foreach($_SESSION['cart'] as $item) {
                $product_id = $item['id'];
                $qty = $item['quantity'];
                $harga = $item['harga'];
                
                $item_query = "INSERT INTO order_items (order_id, product_id, quantity, price) 
                               VALUES ('$order_id', '$product_id', '$qty', '$harga')";
                mysqli_query($conn, $item_query);
            }
            
            // Kurangi stok produk
            foreach($_SESSION['cart'] as $item) {
                $update_stok = "UPDATE products SET stok = stok - {$item['quantity']} WHERE id = {$item['id']}";
                mysqli_query($conn, $update_stok);
            }
        }
        
        // Catat log transaksi
        $log_message = "Pesanan baru: $order_number - Customer: {$customer['nama']} - Total: Rp " . number_format($total_setelah_diskon, 0, ',', '.') . " - Metode: $payment";
        $log_query = "INSERT INTO logs (aksi, user_id, ip_address) VALUES ('$log_message', 0, '{$_SERVER['REMOTE_ADDR']}')";
        @mysqli_query($conn, $log_query);
        
        // Kosongkan keranjang
        unset($_SESSION['cart']);
        
        // Redirect ke halaman sukses
        header("Location: sukses.php?order=" . urlencode($order_number) . "&payment=" . urlencode($payment));
        exit;
    } else {
        $error = "Gagal memproses pesanan: " . mysqli_error($conn);
    }
}

// Ambil data keranjang untuk ditampilkan
$cart_items = isset($_SESSION['cart']) ? $_SESSION['cart'] : [];
$total_belanja = 0;
foreach($cart_items as $item) {
    $total_belanja += $item['harga'] * $item['quantity'];
}

// Ambil data e-wallet dari database (jika ada)
$ewallet_accounts = [];
$ewallet_query = "SELECT * FROM ewallet_accounts";
$ewallet_result = mysqli_query($conn, $ewallet_query);
if($ewallet_result) {
    while($row = mysqli_fetch_assoc($ewallet_result)) {
        $ewallet_accounts[$row['nama_ewallet']] = $row;
    }
}

// Default e-wallet jika tabel belum ada
if(empty($ewallet_accounts)) {
    $ewallet_accounts = [
        'DANA' => ['nomor' => '081234567890', 'atas_nama' => 'Afia Cake'],
        'OVO' => ['nomor' => '081298765432', 'atas_nama' => 'Afia Cake'],
        'GoPay' => ['nomor' => '081234567891', 'atas_nama' => 'Afia Cake Official']
    ];
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout - Afia Cake</title>
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
        }
        
        header {
            background: linear-gradient(135deg, #ff6b6b, #ff8e8e);
            color: white;
            padding: 1rem 2rem;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        nav {
            max-width: 1200px;
            margin: 0 auto;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .logo h1 {
            font-size: 1.8rem;
        }
        
        .nav-menu {
            display: flex;
            list-style: none;
        }
        
        .nav-menu li {
            margin-left: 30px;
        }
        
        .nav-menu a {
            color: white;
            text-decoration: none;
        }
        
        .container {
            max-width: 1200px;
            margin: 40px auto;
            padding: 0 20px;
        }
        
        .checkout-container {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 30px;
        }
        
        /* Form Checkout */
        .form-section {
            background: white;
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        }
        
        .form-section h2 {
            margin-bottom: 25px;
            color: #333;
            display: flex;
            align-items: center;
            gap: 10px;
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
        .form-group textarea,
        .form-group select {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid #eee;
            border-radius: 8px;
            font-family: inherit;
            transition: all 0.3s;
        }
        
        .form-group input:focus,
        .form-group textarea:focus,
        .form-group select:focus {
            outline: none;
            border-color: #ff6b6b;
            box-shadow: 0 0 0 4px rgba(255,107,107,0.1);
        }
        
        /* Ringkasan Pesanan */
        .order-summary {
            background: white;
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            position: sticky;
            top: 20px;
        }
        
        .order-summary h2 {
            margin-bottom: 25px;
            color: #333;
        }
        
        .cart-items {
            margin-bottom: 20px;
            max-height: 400px;
            overflow-y: auto;
        }
        
        .cart-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px 0;
            border-bottom: 1px solid #eee;
        }
        
        .item-info h4 {
            margin-bottom: 5px;
            color: #333;
        }
        
        .item-info p {
            color: #ff6b6b;
            font-weight: bold;
        }
        
        .item-qty {
            background: #f5f5f5;
            padding: 5px 10px;
            border-radius: 5px;
            font-size: 0.9rem;
        }
        
        .total-row {
            display: flex;
            justify-content: space-between;
            padding: 15px 0;
            font-size: 1.2rem;
            border-top: 2px solid #333;
            margin-top: 15px;
        }
        
        .total-row .amount {
            font-weight: bold;
            color: #ff6b6b;
            font-size: 1.5rem;
        }
        
        .btn-checkout {
            width: 100%;
            padding: 15px;
            background: linear-gradient(135deg, #ff6b6b, #ff8e8e);
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 1.2rem;
            font-weight: bold;
            cursor: pointer;
            transition: all 0.3s;
            margin-top: 20px;
        }
        
        .btn-checkout:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 30px rgba(255,107,107,0.3);
        }
        
        .error {
            background: #f8d7da;
            color: #721c24;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        
        .back-link {
            display: inline-block;
            margin-bottom: 20px;
            color: #ff6b6b;
            text-decoration: none;
        }
        
        .back-link i {
            margin-right: 5px;
        }
        
        /* PAYMENT OPTIONS */
        .payment-options {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 15px;
            margin-top: 10px;
        }

        .payment-option {
            cursor: pointer;
        }

        .payment-option input[type="radio"] {
            display: none;
        }

        .payment-card {
            display: block;
            padding: 15px;
            border: 2px solid #eee;
            border-radius: 10px;
            text-align: center;
            transition: all 0.3s;
        }

        .payment-card i {
            font-size: 2rem;
            margin-bottom: 10px;
            color: #666;
        }

        .payment-card strong {
            display: block;
            margin-bottom: 5px;
        }

        .payment-card small {
            color: #999;
            font-size: 0.8rem;
        }

        .payment-card:hover {
            border-color: #ff6b6b;
            transform: translateY(-3px);
        }

        .payment-option input[type="radio"]:checked + .payment-card {
            border-color: #ff6b6b;
            background: linear-gradient(135deg, #fff, #fff0f0);
        }

        .payment-option input[type="radio"]:checked + .payment-card i {
            color: #ff6b6b;
        }

        .payment-card.dana i { color: #0088cc; }
        .payment-card.ovo i { color: #4a2c8f; }
        .payment-card.gopay i { color: #009aab; }

        /* Payment Info */
        .payment-info-box {
            margin-top: 20px;
            padding: 20px;
            background: #f8f9fa;
            border-radius: 10px;
            display: none;
        }

        .payment-detail {
            background: white;
            padding: 20px;
            border-radius: 10px;
            margin-top: 10px;
        }

        .payment-detail table {
            width: 100%;
        }

        .payment-detail td {
            padding: 10px;
            border-bottom: 1px solid #eee;
        }

        .payment-detail td:first-child {
            font-weight: bold;
            width: 150px;
        }

        .qr-code {
            text-align: center;
            padding: 20px;
        }

        .qr-code img {
            max-width: 200px;
            margin: 10px 0;
            border: 1px solid #ddd;
            border-radius: 10px;
        }

        /* Style untuk voucher */
        .voucher-section {
            background: linear-gradient(135deg, #fff9e6, #fff);
            border: 2px dashed #ff6b6b;
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 20px;
        }
        
        .voucher-input-group {
            display: flex;
            gap: 10px;
        }
        
        .voucher-input-group input {
            flex: 1;
            padding: 12px 15px;
            border: 2px solid #ff6b6b;
            border-radius: 8px;
            font-size: 1rem;
        }
        
        .btn-cek-voucher {
            padding: 12px 25px;
            background: linear-gradient(135deg, #4CAF50, #45a049);
            color: white;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 500;
            transition: all 0.3s;
        }
        
        .btn-cek-voucher:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(76,175,80,0.3);
        }
        
        .voucher-info {
            margin-top: 10px;
            padding: 10px;
            border-radius: 8px;
        }
        
        .voucher-info.success {
            background: #d4edda;
            color: #155724;
        }
        
        .voucher-info.error {
            background: #f8d7da;
            color: #721c24;
        }
        
        .customer-info {
            background: #e8f4f8;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: 0.95rem;
        }
        
        .customer-info i {
            color: #ff6b6b;
            margin-right: 8px;
        }
        
        @media (max-width: 768px) {
            .checkout-container {
                grid-template-columns: 1fr;
            }
            
            nav {
                flex-direction: column;
                gap: 10px;
            }
            
            .voucher-input-group {
                flex-direction: column;
            }
        }
    </style>
</head>
<body>
    <header>
        <nav>
            <div class="logo">
                <h1>🍰 Afia Cake</h1>
            </div>
            <ul class="nav-menu">
                <li><a href="index.php">Home</a></li>
                <li><a href="cakes.php">Cakes</a></li>
                <li><a href="about.php">About Us</a></li>
                <li><a href="contact.php">Contact</a></li>
                <li><a href="keranjang.php"><i class="fas fa-shopping-cart"></i> Keranjang</a></li>
            </ul>
        </nav>
    </header>
    
    <div class="container">
        <a href="keranjang.php" class="back-link">
            <i class="fas fa-arrow-left"></i> Kembali ke Keranjang
        </a>
        
        <div class="checkout-container">
            <!-- Form Checkout -->
            <div class="form-section">
                <h2><i class="fas fa-truck"></i> Detail Pengiriman</h2>
                
                <div class="customer-info">
                    <i class="fas fa-user-circle"></i> <strong>Customer:</strong> <?= $customer['nama'] ?>
                    <br>
                    <i class="fas fa-envelope"></i> <strong>Email:</strong> <?= $customer['email'] ?>
                </div>
                
                <?php if(isset($error)): ?>
                    <div class="error"><?= $error ?></div>
                <?php endif; ?>
                
                <form method="POST" id="checkoutForm">
                    <div class="form-group">
                        <label><i class="fas fa-user"></i> Nama Lengkap</label>
                        <input type="text" name="nama" value="<?= $customer['nama'] ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label><i class="fas fa-phone"></i> Nomor WhatsApp</label>
                        <input type="tel" name="phone" value="<?= $customer['no_hp'] ?>" placeholder="Contoh: 081234567890" required>
                        <small style="color: #999;">Kami akan konfirmasi via WA</small>
                    </div>
                    
                    <div class="form-group">
                        <label><i class="fas fa-map-marker-alt"></i> Alamat Lengkap</label>
                        <textarea name="alamat" rows="3" placeholder="Masukkan alamat lengkap" required><?= $customer['alamat'] ?></textarea>
                    </div>
                    
                    <!-- FORM VOUCHER BIRTHDAY CLUB -->
                    <div class="voucher-section">
                        <h3 style="margin-bottom: 15px; color: #ff6b6b;">
                            <i class="fas fa-gift"></i> Birthday Club Voucher
                        </h3>
                        <div class="voucher-input-group">
                            <input type="text" name="voucher" id="voucher" placeholder="Masukkan kode voucher (contoh: BDAY2025021)">
                            <button type="button" onclick="cekVoucherBirthday()" class="btn-cek-voucher">
                                <i class="fas fa-check"></i> Cek Voucher
                            </button>
                        </div>
                        <div id="voucher-info" class="voucher-info"></div>
                    </div>
                    
                    <!-- METODE PEMBAYARAN -->
                    <div class="form-group">
                        <label><i class="fas fa-credit-card"></i> Metode Pembayaran</label>
                        <div class="payment-options">
                            <!-- Transfer Bank -->
                            <label class="payment-option">
                                <input type="radio" name="payment" value="Transfer Bank" checked>
                                <span class="payment-card">
                                    <i class="fas fa-university"></i>
                                    <strong>Transfer Bank</strong>
                                    <small>BCA, Mandiri, BNI</small>
                                </span>
                            </label>
                            
                            <!-- COD -->
                            <label class="payment-option">
                                <input type="radio" name="payment" value="Bayar di Tempat (COD)">
                                <span class="payment-card">
                                    <i class="fas fa-money-bill-wave"></i>
                                    <strong>Bayar di Tempat</strong>
                                    <small>COD</small>
                                </span>
                            </label>
                            
                            <!-- DANA -->
                            <label class="payment-option">
                                <input type="radio" name="payment" value="DANA">
                                <span class="payment-card dana">
                                    <i class="fas fa-donate"></i>
                                    <strong>DANA</strong>
                                    <small>Scan QR</small>
                                </span>
                            </label>
                            
                            <!-- OVO -->
                            <label class="payment-option">
                                <input type="radio" name="payment" value="OVO">
                                <span class="payment-card ovo">
                                    <i class="fas fa-mobile-alt"></i>
                                    <strong>OVO</strong>
                                    <small>Scan QR</small>
                                </span>
                            </label>
                            
                            <!-- GoPay -->
                            <label class="payment-option">
                                <input type="radio" name="payment" value="GoPay">
                                <span class="payment-card gopay">
                                    <i class="fas fa-motorcycle"></i>
                                    <strong>GoPay</strong>
                                    <small>Scan QR</small>
                                </span>
                            </label>
                        </div>
                    </div>

                    <!-- Informasi Pembayaran (akan muncul sesuai pilihan) -->
                    <div id="payment-info" class="payment-info-box">
                        <h4 style="margin-bottom: 15px;"><i class="fas fa-info-circle"></i> Detail Pembayaran</h4>
                        <div id="payment-detail-content"></div>
                    </div>
                    
                    <!-- Form Kupon (untuk diskon biasa) -->
                    <div class="form-group">
                        <label><i class="fas fa-ticket-alt"></i> Kode Kupon (jika ada)</label>
                        <div style="display: flex; gap: 10px;">
                            <input type="text" name="kupon" id="kupon" placeholder="Masukkan kode kupon">
                            <button type="button" onclick="cekKupon()" style="padding: 12px 20px; background: #4CAF50; color: white; border: none; border-radius: 5px;">
                                Cek
                            </button>
                        </div>
                        <div id="kupon-info" style="margin-top: 10px;"></div>
                    </div>
                    
                    <button type="submit" name="checkout" class="btn-checkout">
                        <i class="fas fa-check-circle"></i> Buat Pesanan
                    </button>
                </form>
            </div>
            
            <!-- Ringkasan Pesanan -->
            <div class="order-summary">
                <h2><i class="fas fa-shopping-bag"></i> Ringkasan Pesanan</h2>
                
                <div class="cart-items">
                    <?php foreach($cart_items as $item): ?>
                    <div class="cart-item">
                        <div class="item-info">
                            <h4><?= $item['nama'] ?></h4>
                            <p>Rp <?= number_format($item['harga'] ?? 0, 0, ',', '.') ?></p>
                        </div>
                        <div class="item-qty">
                            <?= $item['quantity'] ?>x
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                
                <div class="total-row">
                    <span>Total</span>
                    <span class="amount" id="total-amount"><?= rupiah($total_belanja) ?></span>
                </div>
                <div id="total-after" style="text-align: right; color: #4CAF50; font-weight: bold;"></div>
                <div id="diskon-info" style="text-align: right; color: #ff6b6b; font-weight: bold; margin-top: 5px;"></div>
                
                <div style="background: #f9f9f9; padding: 15px; border-radius: 8px; margin-top: 20px;">
                    <p style="color: #666; font-size: 0.9rem;">
                        <i class="fas fa-info-circle" style="color: #ff6b6b;"></i>
                        Pesanan akan diproses setelah konfirmasi pembayaran. Kami akan menghubungi Anda via WhatsApp.
                    </p>
                </div>
            </div>
        </div>
    </div>

    <script>
    // Fungsi untuk menampilkan detail pembayaran
    document.querySelectorAll('input[name="payment"]').forEach(radio => {
        radio.addEventListener('change', function() {
            var paymentInfo = document.getElementById('payment-info');
            var paymentDetail = document.getElementById('payment-detail-content');
            
            if(this.value === 'Transfer Bank') {
                paymentInfo.style.display = 'block';
                paymentDetail.innerHTML = `
                    <div class="payment-detail">
                        <h4>🏦 Transfer Bank</h4>
                        <table>
                            <tr><td>Bank BCA</td><td><strong>1234567890</strong></td><td>a.n. Afia Cake</td></tr>
                            <tr><td>Bank Mandiri</td><td><strong>9876543210</strong></td><td>a.n. Afia Cake</td></tr>
                            <tr><td>Bank BNI</td><td><strong>5555555555</strong></td><td>a.n. Afia Cake</td></tr>
                        </table>
                        <p style="color: #ff6b6b; margin-top: 10px;">
                            <i class="fas fa-clock"></i> Konfirmasi pembayaran via WhatsApp
                        </p>
                    </div>
                `;
            }
            else if(this.value === 'DANA') {
                paymentInfo.style.display = 'block';
                paymentDetail.innerHTML = `
                    <div class="payment-detail">
                        <h4><i class="fas fa-donate" style="color: #0088cc;"></i> DANA</h4>
                        <div class="qr-code">
                            <p>Scan QR Code di bawah ini:</p>
                            <img src="https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=081234567890" alt="QR DANA">
                            <p><strong>Nomor DANA: 081234567890</strong></p>
                            <p>a.n. Afia Cake</p>
                        </div>
                        <p style="color: #666;">Atau transfer ke nomor DANA di atas</p>
                    </div>
                `;
            }
            else if(this.value === 'OVO') {
                paymentInfo.style.display = 'block';
                paymentDetail.innerHTML = `
                    <div class="payment-detail">
                        <h4><i class="fas fa-mobile-alt" style="color: #4a2c8f;"></i> OVO</h4>
                        <div class="qr-code">
                            <p>Scan QR Code OVO:</p>
                            <img src="https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=081298765432" alt="QR OVO">
                            <p><strong>Nomor OVO: 081298765432</strong></p>
                            <p>a.n. Afia Cake</p>
                        </div>
                    </div>
                `;
            }
            else if(this.value === 'GoPay') {
                paymentInfo.style.display = 'block';
                paymentDetail.innerHTML = `
                    <div class="payment-detail">
                        <h4><i class="fas fa-motorcycle" style="color: #009aab;"></i> GoPay</h4>
                        <div class="qr-code">
                            <p>Scan QR Code GoPay:</p>
                            <img src="https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=081234567891" alt="QR GoPay">
                            <p><strong>Nomor GoPay: 081234567891</strong></p>
                            <p>a.n. Afia Cake Official</p>
                        </div>
                    </div>
                `;
            }
            else if(this.value === 'Bayar di Tempat (COD)') {
                paymentInfo.style.display = 'none';
            }
        });
    });

    // Fungsi cek kupon (diskon biasa)
    function cekKupon() {
        var kode = document.getElementById('kupon').value;
        var total = <?= $total_belanja ?>;
        
        fetch('cek-kupon.php?kode=' + kode + '&total=' + total)
            .then(response => response.json())
            .then(data => {
                var info = document.getElementById('kupon-info');
                if(data.valid) {
                    info.innerHTML = '<span style="color: #4CAF50;">✅ ' + data.pesan + '</span>';
                    document.getElementById('total-after').innerHTML = 'Total setelah diskon: ' + data.total_akhir;
                } else {
                    info.innerHTML = '<span style="color: #dc3545;">❌ ' + data.pesan + '</span>';
                }
            });
    }
    
    // Fungsi cek voucher birthday club
    function cekVoucherBirthday() {
        var kode = document.getElementById('voucher').value;
        var total = <?= $total_belanja ?>;
        var voucherInfo = document.getElementById('voucher-info');
        
        if(kode.trim() === '') {
            voucherInfo.className = 'voucher-info error';
            voucherInfo.innerHTML = '<i class="fas fa-exclamation-circle"></i> Masukkan kode voucher terlebih dahulu';
            return;
        }
        
        voucherInfo.className = 'voucher-info';
        voucherInfo.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Memeriksa kode voucher...';
        
        fetch('cek-voucher-birthday.php?kode=' + encodeURIComponent(kode) + '&total=' + total)
            .then(response => response.json())
            .then(data => {
                if(data.valid) {
                    voucherInfo.className = 'voucher-info success';
                    voucherInfo.innerHTML = `
                        <i class="fas fa-check-circle"></i> ${data.pesan}<br>
                        <strong>Diskon: Rp ${new Intl.NumberFormat('id-ID').format(data.diskon)}</strong><br>
                        <strong>Total setelah diskon: Rp ${new Intl.NumberFormat('id-ID').format(data.total_akhir)}</strong>
                    `;
                    document.getElementById('total-after').innerHTML = 'Total setelah diskon: Rp ' + new Intl.NumberFormat('id-ID').format(data.total_akhir);
                    document.getElementById('diskon-info').innerHTML = 'Diskon: Rp ' + new Intl.NumberFormat('id-ID').format(data.diskon);
                } else {
                    voucherInfo.className = 'voucher-info error';
                    voucherInfo.innerHTML = '<i class="fas fa-exclamation-circle"></i> ' + data.pesan;
                }
            })
            .catch(error => {
                console.error('Error:', error);
                voucherInfo.className = 'voucher-info error';
                voucherInfo.innerHTML = '<i class="fas fa-exclamation-circle"></i> Terjadi kesalahan koneksi. Silakan coba lagi.';
            });
    }
    </script>
</body>
</html>