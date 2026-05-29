<?php
session_start();
include '../includes/config.php';

// Cek login
if(!isset($_SESSION['user']) || $_SESSION['user']['role'] != 'admin') {
    header('Location: login.php');
    exit;
}

// ===== PROSES VERIFIKASI PEMBAYARAN =====
if(isset($_POST['verifikasi'])) {
    $order_id = $_POST['order_id'];
    
    $query = "UPDATE orders SET status = 'Diproses' WHERE id = $order_id";
    if(mysqli_query($conn, $query)) {
        $order = query("SELECT * FROM orders WHERE id = $order_id")[0];
        $_SESSION['success'] = "✅ Pembayaran untuk order #{$order['order_number']} telah diverifikasi. Pesanan sekarang DIPROSES.";
    } else {
        $_SESSION['error'] = "Gagal verifikasi: " . mysqli_error($conn);
    }
    header('Location: kelola-pesanan.php');
    exit;
}

// ===== UPDATE STATUS PESANAN (LENGKAP DENGAN TRACKING) =====
if(isset($_POST['update_status'])) {
    $order_id = $_POST['order_id'];
    $status = $_POST['status'];
    $tracking_status = $_POST['tracking_status'];
    $resi = $_POST['resi'];
    
    // Tentukan lokasi berdasarkan tracking_status
    $lokasi = '';
    if($tracking_status == 'dikemas') $lokasi = 'Sedang dikemas di toko';
    elseif($tracking_status == 'dikirim') $lokasi = 'Dalam perjalanan bersama kurir';
    elseif($tracking_status == 'selesai') $lokasi = 'Pesanan telah diterima';
    
    $query = "UPDATE orders SET 
              status = '$status',
              tracking_status = '$tracking_status',
              resi = '$resi',
              tracking_lokasi = '$lokasi'
              WHERE id = $order_id";
    
    if(mysqli_query($conn, $query)) {
        $_SESSION['success'] = "✅ Status pesanan berhasil diupdate!";
    } else {
        $_SESSION['error'] = "Gagal update status: " . mysqli_error($conn);
    }
    header('Location: kelola-pesanan.php');
    exit;
}

// Hapus pesanan
if(isset($_GET['hapus'])) {
    $id = $_GET['hapus'];
    
    mysqli_query($conn, "DELETE FROM order_items WHERE order_id=$id");
    mysqli_query($conn, "DELETE FROM orders WHERE id=$id");
    
    $_SESSION['success'] = "Pesanan berhasil dihapus!";
    header('Location: kelola-pesanan.php');
    exit;
}

// ===== AMBIL SEMUA ORDERS =====
$orders = query("
    SELECT 
        o.*,
        GROUP_CONCAT(CONCAT(p.nama_produk, ' (', oi.quantity, 'x)') SEPARATOR '<br>') as items
    FROM orders o
    LEFT JOIN order_items oi ON o.id = oi.order_id
    LEFT JOIN products p ON oi.product_id = p.id
    GROUP BY o.id
    ORDER BY 
        CASE o.status
            WHEN 'Menunggu Pembayaran' THEN 1
            WHEN 'Menunggu Verifikasi' THEN 2
            WHEN 'Diproses' THEN 3
            WHEN 'Dikirim' THEN 4
            WHEN 'Selesai' THEN 5
            WHEN 'Dibatalkan' THEN 6
            ELSE 7
        END, 
        o.created_at DESC
");

// Statistik
$total_pesanan = count($orders);
$menunggu_pembayaran = query("SELECT COUNT(*) as total FROM orders WHERE status='Menunggu Pembayaran'")[0]['total'];
$menunggu_verifikasi = query("SELECT COUNT(*) as total FROM orders WHERE status='Menunggu Verifikasi'")[0]['total'];
$diproses = query("SELECT COUNT(*) as total FROM orders WHERE status='Diproses'")[0]['total'];
$dikirim = query("SELECT COUNT(*) as total FROM orders WHERE status='Dikirim'")[0]['total'];
$selesai = query("SELECT COUNT(*) as total FROM orders WHERE status='Selesai'")[0]['total'];
$dibatalkan = query("SELECT COUNT(*) as total FROM orders WHERE status='Dibatalkan'")[0]['total'];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Pesanan - Afia Cake</title>
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
        
        /* Alert */
        .alert {
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .alert-success { background: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .alert-error { background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
        
        /* Stats Cards */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 15px;
            margin-bottom: 30px;
        }
        
        .stat-card {
            background: white;
            padding: 20px;
            text-align: center;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            transition: all 0.3s;
        }
        
        .stat-card:hover { transform: translateY(-3px); }
        .stat-card i { font-size: 2rem; color: #ff6b6b; margin-bottom: 10px; }
        .stat-card .jumlah { font-size: 2rem; font-weight: bold; color: #333; }
        .stat-card.menunggu-pembayaran { border-top: 4px solid #ffc107; }
        .stat-card.menunggu-verifikasi { border-top: 4px solid #17a2b8; }
        .stat-card.diproses { border-top: 4px solid #007bff; }
        .stat-card.dikirim { border-top: 4px solid #fd7e14; }
        .stat-card.selesai { border-top: 4px solid #28a745; }
        
        /* Filter */
        .filter-section {
            background: white;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 20px;
            display: flex;
            gap: 15px;
            flex-wrap: wrap;
            align-items: center;
        }
        
        .filter-section select, .filter-section input {
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            min-width: 200px;
        }
        
        .filter-section button {
            padding: 10px 20px;
            background: #ff6b6b;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }
        
        /* Table */
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
            vertical-align: top;
        }
        
        tr:hover { background: #f9f9f9; }
        
        .status-badge {
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 500;
            display: inline-block;
        }
        .status-menunggu-pembayaran { background: #fff3cd; color: #856404; }
        .status-menunggu-verifikasi { background: #d1ecf1; color: #0c5460; }
        .status-diproses { background: #cce5ff; color: #004085; }
        .status-dikirim { background: #fff3cd; color: #856404; }
        .status-selesai { background: #d4edda; color: #155724; }
        .status-dibatalkan { background: #f8d7da; color: #721c24; }
        
        .tracking-badge {
            display: inline-block;
            padding: 3px 8px;
            border-radius: 12px;
            font-size: 0.7rem;
            font-weight: 500;
        }
        .tracking-dikemas { background: #ffc107; color: #333; }
        .tracking-dikirim { background: #17a2b8; color: white; }
        .tracking-selesai { background: #28a745; color: white; }
        
        .btn-verifikasi {
            background: #28a745;
            color: white;
            padding: 6px 10px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            width: 100%;
            margin-bottom: 5px;
        }
        .btn-verifikasi:hover { background: #218838; }
        
        .btn-update {
            background: #ff6b6b;
            color: white;
            padding: 6px 10px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            width: 100%;
            margin-bottom: 5px;
        }
        
        .btn-hapus {
            background: #dc3545;
            color: white;
            padding: 6px 10px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
            text-align: center;
            width: 100%;
        }
        
        select, input[type="text"] {
            padding: 6px;
            border: 1px solid #ddd;
            border-radius: 5px;
            width: 100%;
            margin-bottom: 5px;
        }
        
        .resi-input {
            width: 100%;
            padding: 6px;
            margin-bottom: 5px;
            font-size: 0.8rem;
        }
        
        .items-list { font-size: 0.85rem; color: #666; }
        .payment-method { background: #e9ecef; padding: 2px 6px; border-radius: 3px; font-size: 0.75rem; }
        .batas-waktu { font-size: 0.7rem; color: #ff6b6b; }
        
        @media (max-width: 768px) {
            .stats-grid { grid-template-columns: repeat(2, 1fr); }
            .header { flex-direction: column; gap: 10px; text-align: center; }
        }
    </style>
</head>
<body>
    <div class="header">
        <h2><i class="fas fa-shopping-cart"></i> Kelola Pesanan - Afia Cake</h2>
        <div>
            <span>Halo, <?= $_SESSION['user']['nama_lengkap'] ?></span>
            <a href="dashboard.php"><i class="fas fa-arrow-left"></i> Dashboard</a>
            <a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
        </div>
    </div>
    
    <div class="container">
        <?php if(isset($_SESSION['success'])): ?>
            <div class="alert alert-success"><i class="fas fa-check-circle"></i> <?= $_SESSION['success'] ?></div>
            <?php unset($_SESSION['success']); ?>
        <?php endif; ?>
        
        <?php if(isset($_SESSION['error'])): ?>
            <div class="alert alert-error"><i class="fas fa-exclamation-circle"></i> <?= $_SESSION['error'] ?></div>
            <?php unset($_SESSION['error']); ?>
        <?php endif; ?>
        
        <!-- Stats Cards -->
        <div class="stats-grid">
            <div class="stat-card menunggu-pembayaran"><i class="fas fa-clock"></i><div class="jumlah"><?= $menunggu_pembayaran ?></div><div>Menunggu Bayar</div></div>
            <div class="stat-card menunggu-verifikasi"><i class="fas fa-hourglass-half"></i><div class="jumlah"><?= $menunggu_verifikasi ?></div><div>Verifikasi</div></div>
            <div class="stat-card diproses"><i class="fas fa-cogs"></i><div class="jumlah"><?= $diproses ?></div><div>Diproses</div></div>
            <div class="stat-card dikirim"><i class="fas fa-truck"></i><div class="jumlah"><?= $dikirim ?></div><div>Dikirim</div></div>
            <div class="stat-card selesai"><i class="fas fa-check-circle"></i><div class="jumlah"><?= $selesai ?></div><div>Selesai</div></div>
            <div class="stat-card"><i class="fas fa-ban"></i><div class="jumlah"><?= $dibatalkan ?></div><div>Dibatalkan</div></div>
            <div class="stat-card"><i class="fas fa-shopping-bag"></i><div class="jumlah"><?= $total_pesanan ?></div><div>Total</div></div>
        </div>
        
        <!-- Filter -->
        <div class="filter-section">
            <select id="filterStatus" onchange="filterTable()">
                <option value="">Semua Status</option>
                <option value="Menunggu Pembayaran">Menunggu Pembayaran</option>
                <option value="Menunggu Verifikasi">Menunggu Verifikasi</option>
                <option value="Diproses">Diproses</option>
                <option value="Dikirim">Dikirim</option>
                <option value="Selesai">Selesai</option>
                <option value="Dibatalkan">Dibatalkan</option>
            </select>
            <select id="filterPayment" onchange="filterTable()">
                <option value="">Semua Pembayaran</option>
                <option value="Transfer Bank">Transfer Bank</option>
                <option value="Bayar di Tempat (COD)">COD</option>
                <option value="DANA">DANA</option>
                <option value="OVO">OVO</option>
                <option value="GoPay">GoPay</option>
            </select>
            <input type="text" id="searchInput" placeholder="Cari customer..." onkeyup="filterTable()">
            <button onclick="filterTable()"><i class="fas fa-search"></i> Filter</button>
        </div>
        
        <!-- Tabel Pesanan -->
        <div class="table-container">
            <table id="ordersTable">
                <thead>
                    <tr>
                        <th>No.Order</th><th>Customer</th><th>Items</th><th>Total</th>
                        <th>Metode</th><th>Status</th><th>Tracking</th><th>Resi</th><th>Tanggal</th><th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(empty($orders)): ?>
                        <tr><td colspan="10" style="text-align:center; padding:50px;">Belum ada pesanan</td></tr>
                    <?php else: ?>
                        <?php foreach($orders as $order): ?>
                        <tr class="order-row" data-status="<?= $order['status'] ?>" data-payment="<?= $order['payment_method'] ?>" data-customer="<?= strtolower($order['customer_name']) ?>">
                            <td><strong>#<?= $order['order_number'] ?: $order['id'] ?></strong></td>
                            <td><?= $order['customer_name'] ?><br><small><?= $order['customer_phone'] ?></small></td>
                            <td class="items-list"><?= $order['items'] ?: '-' ?></td>
                            <td><strong>Rp <?= number_format($order['total_amount'], 0, ',', '.') ?></strong><?php if($order['diskon']>0): ?><br><small style="color:#28a745;">Diskon: Rp <?= number_format($order['diskon'],0,',','.') ?></small><?php endif; ?></td>
                            <td><span class="payment-method"><?= $order['payment_method'] ?></span></td>
                            <td><span class="status-badge status-<?= strtolower(str_replace(' ','-',$order['status'])) ?>"><?= $order['status'] ?></span></td>
                            <td>
                                <?php 
                                $tracking_class = '';
                                if($order['tracking_status'] == 'dikemas') $tracking_class = 'tracking-dikemas';
                                elseif($order['tracking_status'] == 'dikirim') $tracking_class = 'tracking-dikirim';
                                elseif($order['tracking_status'] == 'selesai') $tracking_class = 'tracking-selesai';
                                ?>
                                <span class="tracking-badge <?= $tracking_class ?>">
                                    <?php 
                                    if($order['tracking_status'] == 'dikemas') echo '📦 Dikemas';
                                    elseif($order['tracking_status'] == 'dikirim') echo '🚚 Dikirim';
                                    elseif($order['tracking_status'] == 'selesai') echo '✅ Selesai';
                                    else echo '-';
                                    ?>
                                </span>
                            </td>
                            <td><?= !empty($order['resi']) ? $order['resi'] : '-' ?></td>
                            <td><?= date('d/m/Y H:i', strtotime($order['created_at'])) ?></td>
                            <td style="min-width: 200px;">
                                <!-- VERIFIKASI (khusus Menunggu Verifikasi) -->
                                <?php if($order['status'] == 'Menunggu Verifikasi'): ?>
                                <form method="POST">
                                    <input type="hidden" name="order_id" value="<?= $order['id'] ?>">
                                    <button type="submit" name="verifikasi" class="btn-verifikasi">✅ Verifikasi Pembayaran</button>
                                </form>
                                <?php endif; ?>
                                
                                <!-- FORM UPDATE STATUS LENGKAP -->
                                <form method="POST">
                                    <input type="hidden" name="order_id" value="<?= $order['id'] ?>">
                                    <select name="status">
                                        <option value="Menunggu Pembayaran" <?= $order['status']=='Menunggu Pembayaran' ? 'selected' : '' ?>>Menunggu Pembayaran</option>
                                        <option value="Menunggu Verifikasi" <?= $order['status']=='Menunggu Verifikasi' ? 'selected' : '' ?>>Menunggu Verifikasi</option>
                                        <option value="Diproses" <?= $order['status']=='Diproses' ? 'selected' : '' ?>>Diproses</option>
                                        <option value="Dikirim" <?= $order['status']=='Dikirim' ? 'selected' : '' ?>>Dikirim</option>
                                        <option value="Selesai" <?= $order['status']=='Selesai' ? 'selected' : '' ?>>Selesai</option>
                                        <option value="Dibatalkan" <?= $order['status']=='Dibatalkan' ? 'selected' : '' ?>>Dibatalkan</option>
                                    </select>
                                    
                                    <!-- TRACKING STATUS -->
                                    <select name="tracking_status">
                                        <option value="dikemas" <?= ($order['tracking_status'] ?? 'dikemas') == 'dikemas' ? 'selected' : '' ?>>📦 Dikemas</option>
                                        <option value="dikirim" <?= ($order['tracking_status'] ?? '') == 'dikirim' ? 'selected' : '' ?>>🚚 Dikirim</option>
                                        <option value="selesai" <?= ($order['tracking_status'] ?? '') == 'selesai' ? 'selected' : '' ?>>✅ Selesai</option>
                                    </select>
                                    
                                    <!-- NOMOR RESI -->
                                    <input type="text" name="resi" placeholder="No. Resi" value="<?= $order['resi'] ?? '' ?>" class="resi-input">
                                    
                                    <button type="submit" name="update_status" class="btn-update">🔄 Update</button>
                                </form>
                                
                                <!-- HAPUS -->
                                <a href="?hapus=<?= $order['id'] ?>" class="btn-hapus" onclick="return confirm('Hapus pesanan?')">🗑️ Hapus</a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
    
    <script>
        function filterTable() {
            var statusFilter = document.getElementById('filterStatus').value.toLowerCase();
            var paymentFilter = document.getElementById('filterPayment').value.toLowerCase();
            var searchFilter = document.getElementById('searchInput').value.toLowerCase();
            var rows = document.getElementsByClassName('order-row');
            for (var i = 0; i < rows.length; i++) {
                var row = rows[i];
                var status = row.getAttribute('data-status').toLowerCase();
                var payment = row.getAttribute('data-payment').toLowerCase();
                var customer = row.getAttribute('data-customer');
                var matchStatus = statusFilter === '' || status.includes(statusFilter);
                var matchPayment = paymentFilter === '' || payment.includes(paymentFilter);
                var matchSearch = searchFilter === '' || customer.includes(searchFilter);
                row.style.display = (matchStatus && matchPayment && matchSearch) ? '' : 'none';
            }
        }
    </script>
</body>
</html>