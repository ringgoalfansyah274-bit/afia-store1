<?php
session_start();
include 'includes/config.php';

// Ambil nomor order dari URL
$order_number = isset($_GET['order']) ? $_GET['order'] : '';
$payment_method = isset($_GET['payment']) ? $_GET['payment'] : '';

// Ambil data pesanan dari database
$order_data = null;
$total = 0;

if(!empty($order_number)) {
    $query = "SELECT * FROM orders WHERE order_number = '$order_number'";
    $result = mysqli_query($conn, $query);
    
    if(mysqli_num_rows($result) > 0) {
        $order_data = mysqli_fetch_assoc($result);
        $total = $order_data['total_amount'];
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pesanan Berhasil - Afia Cake</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .btn-konfirmasi {
            display: inline-block;
            background: #4CAF50;
            color: white;
            padding: 15px 40px;
            border-radius: 50px;
            text-decoration: none;
            font-weight: bold;
            font-size: 1.1rem;
            box-shadow: 0 5px 20px rgba(76,175,80,0.3);
            transition: all 0.3s;
            border: none;
            cursor: pointer;
        }

        .btn-konfirmasi:hover {
            background: #45a049;
            transform: translateY(-3px);
            box-shadow: 0 10px 30px rgba(76,175,80,0.4);
        }

        .btn-konfirmasi i {
            margin-right: 10px;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #f5f5f5, #fff);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .success-container {
            max-width: 600px;
            margin: 20px;
            background: white;
            border-radius: 30px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.1);
            overflow: hidden;
            animation: slideUp 0.5s ease;
        }
        
        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(50px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .success-header {
            background: linear-gradient(135deg, #ff6b6b, #ff8e8e);
            color: white;
            padding: 40px 30px;
            text-align: center;
        }
        
        .success-header i {
            font-size: 5rem;
            margin-bottom: 20px;
            animation: bounce 1s infinite;
        }
        
        @keyframes bounce {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-10px); }
        }
        
        .success-header h1 {
            font-size: 2.5rem;
            margin-bottom: 10px;
        }
        
        .success-body {
            padding: 40px 30px;
            text-align: center;
        }
        
        .order-number {
            background: #f5f5f5;
            padding: 15px;
            border-radius: 10px;
            margin: 20px 0;
            font-size: 1.2rem;
        }
        
        .order-number strong {
            color: #ff6b6b;
            font-size: 1.5rem;
            letter-spacing: 2px;
        }
        
        .info-box {
            background: #fff8e7;
            border-left: 4px solid #ff6b6b;
            padding: 20px;
            margin: 30px 0;
            text-align: left;
            border-radius: 5px;
        }
        
        .info-box h3 {
            margin-bottom: 15px;
            color: #333;
        }
        
        .info-box ul {
            list-style: none;
        }
        
        .info-box li {
            margin-bottom: 10px;
            color: #666;
        }
        
        .info-box li i {
            color: #ff6b6b;
            width: 25px;
        }
        
        .whatsapp-button {
            display: inline-block;
            background: #25D366;
            color: white;
            padding: 15px 40px;
            border-radius: 50px;
            text-decoration: none;
            font-weight: bold;
            margin: 20px 0;
            transition: all 0.3s;
            box-shadow: 0 5px 20px rgba(37,211,102,0.3);
        }
        
        .whatsapp-button:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 30px rgba(37,211,102,0.4);
        }
        
        .whatsapp-button i {
            margin-right: 10px;
        }
        
        .action-buttons {
            display: flex;
            gap: 15px;
            justify-content: center;
            margin-top: 30px;
        }
        
        .btn {
            padding: 12px 30px;
            border-radius: 50px;
            text-decoration: none;
            font-weight: 500;
            transition: all 0.3s;
        }
        
        .btn-primary {
            background: #ff6b6b;
            color: white;
        }
        
        .btn-primary:hover {
            background: #ff5252;
            transform: translateY(-3px);
        }
        
        .btn-outline {
            border: 2px solid #ff6b6b;
            color: #ff6b6b;
        }
        
        .btn-outline:hover {
            background: #ff6b6b;
            color: white;
        }
        
        .total-amount {
            font-size: 1.5rem;
            font-weight: bold;
            color: #ff6b6b;
            margin: 15px 0;
        }
        
        footer {
            background: #f5f5f5;
            padding: 20px;
            text-align: center;
            color: #666;
        }
    </style>
</head>
<body>
    <div class="success-container">
        <div class="success-header">
            <i class="fas fa-check-circle"></i>
            <h1>Pesanan Berhasil!</h1>
            <p>Terima kasih telah memesan di Afia Cake</p>
        </div>
        
        <div class="success-body">
            <div class="order-number">
                <p>Nomor Pesanan Anda:</p>
                <strong>#<?= htmlspecialchars($order_number) ?></strong>
            </div>
            
            <?php if($order_data): ?>
            <div class="total-amount">
                Total Pembayaran: Rp <?= number_format($total, 0, ',', '.') ?>
            </div>
            <?php endif; ?>
            
            <div class="info-box">
                <h3><i class="fas fa-clock"></i> Langkah Selanjutnya:</h3>
                <ul>
                    <li><i class="fas fa-check-circle"></i> Pesanan Anda akan segera kami proses</li>
                    <li><i class="fas fa-phone-alt"></i> Admin akan menghubungi Anda via WhatsApp untuk konfirmasi</li>
                    <li><i class="fas fa-truck"></i> Pengiriman dilakukan setelah konfirmasi pembayaran</li>
                    <li><i class="fas fa-clock"></i> Estimasi proses: 1-2 jam kerja</li>
                </ul>
            </div>

            <!-- ===== TOMBOL KONFIRMASI PEMBAYARAN (DITAMBAHKAN SETELAH INFO BOX) ===== -->
            <?php if($payment_method != 'Bayar di Tempat (COD)'): ?>
            <div style="text-align: center; margin: 30px 0;">
                <a href="konfirmasi.php?order=<?= urlencode($order_number) ?>" 
                   class="btn-konfirmasi">
                    <i class="fas fa-upload"></i> Konfirmasi Pembayaran
                </a>
                <p style="margin-top: 10px; color: #ff6b6b;">
                    <i class="fas fa-clock"></i> Batas pembayaran 24 jam
                </p>
            </div>
            <?php endif; ?>
            <!-- ===== AKHIR TOMBOL KONFIRMASI ===== -->
            
            <?php if($payment_method == 'DANA' || $payment_method == 'OVO' || $payment_method == 'GoPay'): ?>
            <div class="info-box" style="margin-top: 20px; background: #e3f2fd; border-left-color: #2196F3;">
                <h3><i class="fas fa-clock"></i> Instruksi Pembayaran E-Wallet:</h3>
                <ul>
                    <li>1. Buka aplikasi <?= $payment_method ?> Anda</li>
                    <li>2. Pilih menu "Bayar" atau "Transfer"</li>
                    <li>3. Scan QR Code yang muncul di halaman checkout</li>
                    <li>4. Masukkan jumlah Rp <?= number_format($total, 0, ',', '.') ?></li>
                    <li>5. Konfirmasi pembayaran dengan mengirim bukti transfer ke WhatsApp kami</li>
                </ul>
            </div>
            <?php endif; ?>
            
            <?php
            // Ambil nomor WA admin dari database atau gunakan default
            $wa_admin = '6287875196663'; // Ganti dengan nomor WA admin
            $pesan_wa = "Halo Admin Afia Cake, saya baru saja melakukan pesanan dengan nomor #$order_number. Mohon segera diproses.";
            ?>
            
            <a href="https://wa.me/<?= $wa_admin ?>?text=<?= urlencode($pesan_wa) ?>" 
               target="_blank" 
               class="whatsapp-button">
                <i class="fab fa-whatsapp"></i> Chat Admin via WhatsApp
            </a>
            
            <div class="action-buttons">
                <a href="cakes.php" class="btn btn-primary">
                    <i class="fas fa-shopping-bag"></i> Belanja Lagi
                </a>
                <a href="index.php" class="btn btn-outline">
                    <i class="fas fa-home"></i> Kembali ke Home
                </a>
            </div>
        </div>
        
        <footer>
            <p>&copy; 2026 Afia Cake - Tim Peristiwa Penting</p>
        </footer>
    </div>
</body>
</html>