<?php
session_start();
include '../includes/config.php';

// Cek login
if(!isset($_SESSION['user']) || $_SESSION['user']['role'] != 'admin') {
    header('Location: login.php');
    exit;
}

// Ambil data statistik orders
$total_orders = query("SELECT COUNT(*) as total FROM orders")[0]['total'];
$total_revenue = query("SELECT SUM(total_amount) as total FROM orders")[0]['total'] ?? 0;
$new_orders = query("SELECT COUNT(*) as total FROM orders WHERE status='Diproses'")[0]['total'];

// ===== STATISTIK BIRTHDAY CLUB - DITAMBAHKAN =====
// Cek apakah tabel birthday_club ada
$table_check = mysqli_query($conn, "SHOW TABLES LIKE 'birthday_club'");
if(mysqli_num_rows($table_check) > 0) {
    $total_member = query("SELECT COUNT(*) as total FROM birthday_club")[0]['total'];
    $birthday_today = query("SELECT COUNT(*) as total FROM birthday_club WHERE DATE_FORMAT(tanggal_lahir, '%m-%d') = '" . date('m-d') . "'")[0]['total'];
    
    // Ambil 5 member terbaru
    $recent_members = query("SELECT * FROM birthday_club ORDER BY created_at DESC LIMIT 5");
} else {
    $total_member = 0;
    $birthday_today = 0;
    $recent_members = [];
}
// ===== AKHIR STATISTIK BIRTHDAY CLUB =====
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Afia Cake</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        .dashboard-header {
            background: linear-gradient(135deg, #333, #222);
            color: white;
            padding: 1rem 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .dashboard-header h2 {
            font-size: 1.5rem;
        }
        
        .dashboard-header a {
            color: white;
            text-decoration: none;
            margin-left: 20px;
            padding: 5px 15px;
            border-radius: 5px;
            background: rgba(255,255,255,0.2);
        }
        
        .dashboard-header a:hover {
            background: rgba(255,255,255,0.3);
        }
        
        .container {
            max-width: 1200px;
            margin: 30px auto;
            padding: 0 20px;
        }
        
        /* Stats Grid */
        .dashboard-stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin: 30px 0;
        }
        
        .stat-card {
            background: linear-gradient(135deg, #ff6b6b, #ff8e8e);
            color: white;
            padding: 25px;
            border-radius: 10px;
            text-align: center;
            box-shadow: 0 5px 15px rgba(255,107,107,0.2);
            transition: all 0.3s;
        }
        
        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(255,107,107,0.3);
        }
        
        .stat-card i {
            font-size: 2.5rem;
            margin-bottom: 15px;
        }
        
        .stat-card h3 {
            font-size: 2.5rem;
            margin-bottom: 10px;
        }
        
        .stat-card p {
            font-size: 1rem;
            opacity: 0.9;
        }
        
        /* Stat card khusus birthday */
        .stat-card.birthday {
            background: linear-gradient(135deg, #4CAF50, #45a049);
        }
        
        /* Section Title */
        .section-title {
            margin: 40px 0 20px;
            color: #333;
            font-size: 1.5rem;
            border-left: 4px solid #ff6b6b;
            padding-left: 15px;
        }
        
        /* Dashboard Menu */
        .dashboard-menu {
            background-color: #f5f5f5;
            padding: 20px;
            border-radius: 10px;
            margin-top: 30px;
        }
        
        .menu-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin-top: 15px;
        }
        
        .menu-item {
            background-color: white;
            padding: 20px;
            border-radius: 10px;
            text-align: center;
            text-decoration: none;
            color: #333;
            transition: all 0.3s;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }
        
        .menu-item:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(255,107,107,0.2);
            color: #ff6b6b;
        }
        
        .menu-item i {
            font-size: 2rem;
            margin-bottom: 10px;
            color: #ff6b6b;
        }
        
        /* Recent Members Table */
        .members-table {
            background: white;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            margin-top: 20px;
        }
        
        .members-table table {
            width: 100%;
            border-collapse: collapse;
        }
        
        .members-table th {
            background: #f8f8f8;
            padding: 15px;
            text-align: left;
            font-weight: 600;
            color: #333;
        }
        
        .members-table td {
            padding: 12px 15px;
            border-bottom: 1px solid #eee;
        }
        
        .members-table tr:hover {
            background: #f9f9f9;
        }
        
        .birthday-badge {
            background: #ff6b6b;
            color: white;
            padding: 3px 8px;
            border-radius: 3px;
            font-size: 0.8rem;
        }
        
        .view-all {
            display: block;
            text-align: center;
            margin-top: 15px;
            color: #ff6b6b;
            text-decoration: none;
        }
        
        .view-all:hover {
            text-decoration: underline;
        }
        
        .logout-btn {
            background-color: #ff4444;
            color: white;
            padding: 8px 20px;
            text-decoration: none;
            border-radius: 5px;
            margin-left: 15px;
        }
        
        .logout-btn:hover {
            background-color: #ff0000;
        }
    </style>
</head>
<body>
    <div class="dashboard-header">
        <h2><i class="fas fa-chart-line"></i> Admin Dashboard - Afia Cake</h2>
        <div>
            <span><i class="fas fa-user"></i> <?= $_SESSION['user']['nama_lengkap'] ?></span>
            <a href="logout.php" class="logout-btn"><i class="fas fa-sign-out-alt"></i> Logout</a>
        </div>
    </div>
    
    <div class="container">
        <!-- Statistik Utama -->
        <div class="dashboard-stats">
            <div class="stat-card">
                <i class="fas fa-shopping-cart"></i>
                <h3><?= $new_orders ?></h3>
                <p>Pesanan Baru</p>
            </div>
            
            <div class="stat-card">
                <i class="fas fa-money-bill-wave"></i>
                <h3>Rp <?= number_format($total_revenue, 0, ',', '.') ?></h3>
                <p>Total Pendapatan</p>
            </div>
            
            <div class="stat-card">
                <i class="fas fa-box"></i>
                <h3><?= $total_orders ?></h3>
                <p>Total Pesanan</p>
            </div>
        </div>
        
        <!-- ===== STATISTIK BIRTHDAY CLUB - DITAMBAHKAN ===== -->
        <h2 class="section-title"><i class="fas fa-gift"></i> Birthday Club Statistics</h2>
        
        <div class="dashboard-stats">
            <div class="stat-card birthday">
                <i class="fas fa-users"></i>
                <h3><?= $total_member ?></h3>
                <p>Total Member Birthday Club</p>
            </div>
            
            <div class="stat-card birthday">
                <i class="fas fa-birthday-cake"></i>
                <h3><?= $birthday_today ?></h3>
                <p>Ulang Tahun Hari Ini</p>
            </div>
            
            <div class="stat-card birthday">
                <i class="fas fa-calendar-check"></i>
                <h3>
                    <?php
                    $next_week = date('Y-m-d', strtotime('+7 days'));
                    $next_week_month_day = date('m-d', strtotime($next_week));
                    $upcoming = query("SELECT COUNT(*) as total FROM birthday_club WHERE DATE_FORMAT(tanggal_lahir, '%m-%d') BETWEEN '" . date('m-d') . "' AND '$next_week_month_day'")[0]['total'];
                    echo $upcoming;
                    ?>
                </h3>
                <p>Ulang Tahun Minggu Ini</p>
            </div>
        </div>
        
        <!-- Recent Members -->
        <?php if(!empty($recent_members)): ?>
        <div style="background: white; border-radius: 10px; padding: 20px; margin-top: 20px;">
            <h3 style="margin-bottom: 15px; color: #333;">
                <i class="fas fa-user-plus"></i> Member Terbaru
            </h3>
            
            <div class="members-table">
                <table>
                    <thead>
                        <tr>
                            <th>Nama</th>
                            <th>Email</th>
                            <th>WhatsApp</th>
                            <th>Tanggal Lahir</th>
                            <th>Kode Voucher</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($recent_members as $member): 
                            $birthday_this_year = date('Y') . '-' . date('m-d', strtotime($member['tanggal_lahir']));
                            $is_today = (date('m-d') == date('m-d', strtotime($member['tanggal_lahir'])));
                        ?>
                        <tr>
                            <td><strong><?= htmlspecialchars($member['nama']) ?></strong></td>
                            <td><?= htmlspecialchars($member['email']) ?></td>
                            <td>
                                <a href="https://wa.me/<?= $member['whatsapp'] ?>" target="_blank" style="color: #25D366;">
                                    <i class="fab fa-whatsapp"></i> <?= $member['whatsapp'] ?>
                                </a>
                            </td>
                            <td><?= date('d/m/Y', strtotime($member['tanggal_lahir'])) ?></td>
                            <td><code><?= $member['kode_voucher'] ?></code></td>
                            <td>
                                <?php if($is_today): ?>
                                    <span class="birthday-badge"><i class="fas fa-birthday-cake"></i> Ulang Tahun!</span>
                                <?php else: ?>
                                    <span style="color: #999;">-</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            
            <a href="birthday-members.php" class="view-all">
                Lihat Semua Member <i class="fas fa-arrow-right"></i>
            </a>
        </div>
        <?php endif; ?>
        <!-- ===== AKHIR STATISTIK BIRTHDAY CLUB ===== -->
        
        <!-- Menu Admin -->
        <div class="dashboard-menu">
            <h3><i class="fas fa-bars"></i> Menu Admin</h3>
            
            <div class="menu-grid">
                <a href="kelola-pesanan.php" class="menu-item">
                    <i class="fas fa-shopping-cart"></i>
                    <h4>Kelola Pesanan</h4>
                    <p>Lihat dan update status pesanan</p>
                </a>
                
                <a href="kelola-produk.php" class="menu-item">
                    <i class="fas fa-cake-candles"></i>
                    <h4>Kelola Produk</h4>
                    <p>Tambah/edit produk kue</p>
                </a>
                
                <a href="laporan.php" class="menu-item">
                    <i class="fas fa-chart-bar"></i>
                    <h4>Laporan</h4>
                    <p>Lihat laporan penjualan</p>
                </a>
                
                <a href="pengaturan.php" class="menu-item">
                    <i class="fas fa-cog"></i>
                    <h4>Pengaturan</h4>
                    <p>Pengaturan toko</p>
                </a>
                
                <!-- Menu baru untuk Birthday Club -->
                <a href="birthday-members.php" class="menu-item">
                    <i class="fas fa-gift"></i>
                    <h4>Birthday Club</h4>
                    <p>Kelola member & voucher</p>
                </a>
            </div>
        </div>
    </div>
</body>
</html>