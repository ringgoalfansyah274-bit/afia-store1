<?php
include 'includes/header.php';

// Cek apakah pembeli sudah login
if(!isset($_SESSION['customer'])) {
    $_SESSION['notification'] = [
        'type' => 'error',
        'message' => '⚠️ Silakan login terlebih dahulu untuk melihat tracking pesanan!'
    ];
    header('Location: login-customer.php');
    exit;
}

$customer_id = $_SESSION['customer']['id'];

// Ambil semua pesanan customer (DENGAN PENGECEKAN ERROR)
$orders = [];
$query = "SELECT * FROM orders WHERE customer_id = $customer_id ORDER BY created_at DESC";
$result = mysqli_query($conn, $query);

if($result) {
    while($row = mysqli_fetch_assoc($result)) {
        $orders[] = $row;
    }
} else {
    // Jika kolom customer_id belum ada, coba pakai customer_phone
    $phone = $_SESSION['customer']['no_hp'];
    $result2 = mysqli_query($conn, "SELECT * FROM orders WHERE customer_phone = '$phone' ORDER BY created_at DESC");
    if($result2) {
        while($row = mysqli_fetch_assoc($result2)) {
            $orders[] = $row;
        }
    }
}
?>

<style>
    .tracking-page {
        padding: 60px 0;
        background: #f8f9fa;
        min-height: 70vh;
    }
    
    .tracking-header {
        text-align: center;
        margin-bottom: 50px;
    }
    
    .tracking-header h1 {
        font-size: 2.5rem;
        color: #333;
        margin-bottom: 10px;
    }
    
    .tracking-header p {
        color: #666;
    }
    
    .order-card {
        background: white;
        border-radius: 20px;
        box-shadow: 0 5px 20px rgba(0,0,0,0.05);
        margin-bottom: 30px;
        overflow: hidden;
        transition: all 0.3s;
    }
    
    .order-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 15px 30px rgba(255,107,107,0.1);
    }
    
    .order-header {
        background: linear-gradient(135deg, #ff6b6b, #ff8e8e);
        color: white;
        padding: 20px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
    }
    
    .order-number {
        font-size: 1.2rem;
        font-weight: bold;
    }
    
    .order-date {
        font-size: 0.9rem;
        opacity: 0.9;
    }
    
    .order-body {
        padding: 25px;
    }
    
    .order-info {
        display: flex;
        gap: 30px;
        flex-wrap: wrap;
        margin-bottom: 25px;
        padding-bottom: 20px;
        border-bottom: 1px solid #eee;
    }
    
    .info-item {
        flex: 1;
        min-width: 150px;
    }
    
    .info-label {
        font-size: 0.8rem;
        color: #999;
        margin-bottom: 5px;
    }
    
    .info-value {
        font-size: 1rem;
        font-weight: 500;
        color: #333;
    }
    
    .info-value.total {
        color: #ff6b6b;
        font-size: 1.2rem;
    }
    
    /* TRACKING PROGRESS BAR */
    .tracking-container {
        margin: 30px 0;
        position: relative;
    }
    
    .tracking-steps {
        display: flex;
        justify-content: space-between;
        position: relative;
        z-index: 2;
    }
    
    .step {
        text-align: center;
        flex: 1;
        position: relative;
    }
    
    .step-icon {
        width: 50px;
        height: 50px;
        background: #e0e0e0;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 10px;
        transition: all 0.3s;
        position: relative;
        z-index: 2;
    }
    
    .step-icon i {
        font-size: 1.2rem;
        color: #999;
    }
    
    .step.active .step-icon {
        background: #ff6b6b;
        box-shadow: 0 0 0 5px rgba(255,107,107,0.2);
    }
    
    .step.active .step-icon i {
        color: white;
    }
    
    .step.completed .step-icon {
        background: #4CAF50;
    }
    
    .step.completed .step-icon i {
        color: white;
    }
    
    .step-label {
        font-size: 0.85rem;
        font-weight: 500;
        color: #666;
    }
    
    .step.active .step-label {
        color: #ff6b6b;
        font-weight: 600;
    }
    
    .step.completed .step-label {
        color: #4CAF50;
    }
    
    .progress-line {
        position: absolute;
        top: 25px;
        left: 10%;
        width: 80%;
        height: 3px;
        background: #e0e0e0;
        z-index: 1;
    }
    
    .progress-line-fill {
        height: 100%;
        background: linear-gradient(90deg, #4CAF50, #ff6b6b);
        width: 0%;
        transition: width 0.5s ease;
    }
    
    /* Lokasi Terkini */
    .lokasi-box {
        background: #e8f4f8;
        border-radius: 10px;
        padding: 15px;
        margin-top: 20px;
        display: flex;
        align-items: center;
        gap: 15px;
    }
    
    .lokasi-box i {
        font-size: 1.5rem;
        color: #ff6b6b;
    }
    
    .lokasi-box .lokasi-text {
        flex: 1;
    }
    
    .lokasi-box .resi {
        font-family: monospace;
        font-size: 0.9rem;
        color: #666;
    }
    
    .empty-orders {
        text-align: center;
        padding: 80px 20px;
        background: white;
        border-radius: 20px;
    }
    
    .empty-orders i {
        font-size: 5rem;
        color: #ddd;
        margin-bottom: 20px;
    }
    
    .empty-orders h2 {
        color: #333;
        margin-bottom: 10px;
    }
    
    .btn-shop {
        display: inline-block;
        padding: 12px 30px;
        background: #ff6b6b;
        color: white;
        text-decoration: none;
        border-radius: 8px;
        margin-top: 20px;
    }
    
    @media (max-width: 768px) {
        .tracking-steps {
            flex-direction: column;
            gap: 20px;
        }
        
        .progress-line {
            display: none;
        }
        
        .step {
            display: flex;
            align-items: center;
            gap: 15px;
        }
        
        .step-icon {
            margin: 0;
        }
    }
</style>

<div class="tracking-page">
    <div class="container">
        <div class="tracking-header">
            <h1><i class="fas fa-truck"></i> Tracking Pesanan</h1>
            <p>Pantau status pesanan kamu di sini</p>
        </div>
        
        <?php if(empty($orders)): ?>
            <div class="empty-orders">
                <i class="fas fa-shopping-cart"></i>
                <h2>Belum ada pesanan</h2>
                <p>Kamu belum memiliki pesanan. Yuk, mulai belanja!</p>
                <a href="cakes.php" class="btn-shop">Mulai Belanja</a>
            </div>
        <?php else: ?>
            <?php foreach($orders as $order): 
                $progress = 0;
                if(isset($order['tracking_status'])) {
                    if($order['tracking_status'] == 'dikemas') $progress = 33;
                    elseif($order['tracking_status'] == 'dikirim') $progress = 66;
                    elseif($order['tracking_status'] == 'selesai') $progress = 100;
                }
            ?>
            <div class="order-card">
                <div class="order-header">
                    <div>
                        <div class="order-number">#<?= $order['order_number'] ?></div>
                        <div class="order-date"><?= date('d M Y H:i', strtotime($order['created_at'])) ?></div>
                    </div>
                    <div class="status-badge" style="background: rgba(255,255,255,0.2); padding: 5px 15px; border-radius: 20px;">
                        <?php 
                        $status_text = '';
                        if(isset($order['tracking_status'])) {
                            if($order['tracking_status'] == 'dikemas') $status_text = '📦 Sedang Dikemas';
                            elseif($order['tracking_status'] == 'dikirim') $status_text = '🚚 Dalam Perjalanan';
                            elseif($order['tracking_status'] == 'selesai') $status_text = '✅ Pesanan Selesai';
                            else $status_text = $order['status'];
                        } else {
                            $status_text = $order['status'];
                        }
                        ?>
                        <?= $status_text ?>
                    </div>
                </div>
                
                <div class="order-body">
                    <div class="order-info">
                        <div class="info-item">
                            <div class="info-label">Total Pembayaran</div>
                            <div class="info-value total">Rp <?= number_format($order['total_amount'], 0, ',', '.') ?></div>
                        </div>
                        <div class="info-item">
                            <div class="info-label">Metode Pembayaran</div>
                            <div class="info-value"><?= $order['payment_method'] ?></div>
                        </div>
                        <div class="info-item">
                            <div class="info-label">Tanggal Pesanan</div>
                            <div class="info-value"><?= date('d M Y', strtotime($order['created_at'])) ?></div>
                        </div>
                    </div>
                    
                    <!-- TRACKING PROGRESS -->
                    <div class="tracking-container">
                        <div class="tracking-steps">
                            <div class="step <?= $progress >= 33 ? 'completed' : ($progress >= 0 ? 'active' : '') ?>">
                                <div class="step-icon"><i class="fas fa-box"></i></div>
                                <div class="step-label">Dikemas</div>
                            </div>
                            <div class="step <?= $progress >= 66 ? 'completed' : ($progress >= 33 ? 'active' : '') ?>">
                                <div class="step-icon"><i class="fas fa-truck"></i></div>
                                <div class="step-label">Dikirim</div>
                            </div>
                            <div class="step <?= $progress >= 100 ? 'completed' : ($progress >= 66 ? 'active' : '') ?>">
                                <div class="step-icon"><i class="fas fa-check-circle"></i></div>
                                <div class="step-label">Selesai</div>
                            </div>
                        </div>
                        <div class="progress-line">
                            <div class="progress-line-fill" style="width: <?= $progress ?>%;"></div>
                        </div>
                    </div>
                    
                    <!-- Lokasi & Resi -->
                    <div class="lokasi-box">
                        <i class="fas fa-map-marker-alt"></i>
                        <div class="lokasi-text">
                            <strong>Lokasi Terkini:</strong> <?= $order['tracking_lokasi'] ?? 'Sedang diproses' ?>
                            <?php if(!empty($order['resi'])): ?>
                                <br><span class="resi"><strong>No. Resi:</strong> <?= $order['resi'] ?></span>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<?php include 'includes/footer.php'; ?>