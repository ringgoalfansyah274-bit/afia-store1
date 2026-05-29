<?php
session_start();
include '../includes/config.php';

// Cek login admin
if(!isset($_SESSION['user']) || $_SESSION['user']['role'] != 'admin') {
    header('Location: login.php');
    exit;
}

// Ambil parameter filter
$filter_tahun = isset($_GET['tahun']) ? (int)$_GET['tahun'] : date('Y');
$filter_bulan = isset($_GET['bulan']) ? (int)$_GET['bulan'] : date('m');

// ===== LAPORAN PENJUALAN PER BULAN =====
$penjualan_per_bulan = query("
    SELECT 
        DATE_FORMAT(created_at, '%Y-%m') as bulan,
        COUNT(*) as jumlah_order,
        SUM(total_amount) as total_penjualan,
        SUM(diskon) as total_diskon
    FROM orders 
    WHERE status != 'Dibatalkan'
    GROUP BY DATE_FORMAT(created_at, '%Y-%m')
    ORDER BY bulan DESC
    LIMIT 12
");

// ===== LAPORAN PENJUALAN PER MINGGU (Bulan ini) =====
$penjualan_per_minggu = query("
    SELECT 
        WEEK(created_at, 1) as minggu_ke,
        DATE_FORMAT(created_at, '%Y') as tahun,
        COUNT(*) as jumlah_order,
        SUM(total_amount) as total_penjualan
    FROM orders 
    WHERE status != 'Dibatalkan' 
        AND MONTH(created_at) = $filter_bulan 
        AND YEAR(created_at) = $filter_tahun
    GROUP BY WEEK(created_at, 1)
    ORDER BY minggu_ke ASC
");

// ===== LAPORAN PENJUALAN PER HARI (Bulan ini) =====
$penjualan_per_hari = query("
    SELECT 
        DAY(created_at) as tgl,
        COUNT(*) as jumlah_order,
        SUM(total_amount) as total_penjualan
    FROM orders 
    WHERE status != 'Dibatalkan' 
        AND MONTH(created_at) = $filter_bulan 
        AND YEAR(created_at) = $filter_tahun
    GROUP BY DAY(created_at)
    ORDER BY tgl ASC
");

// ===== PRODUK TERLARIS =====
$produk_terlaris = query("
    SELECT 
        p.id,
        p.nama_produk,
        p.kategori,
        COUNT(oi.id) as jumlah_dipesan,
        SUM(oi.quantity) as total_terjual,
        SUM(oi.quantity * oi.price) as total_pendapatan
    FROM order_items oi
    JOIN products p ON oi.product_id = p.id
    JOIN orders o ON oi.order_id = o.id
    WHERE o.status != 'Dibatalkan'
    GROUP BY p.id
    ORDER BY total_terjual DESC
    LIMIT 10
");

// ===== PRODUK TERLARIS BULAN INI =====
$produk_terlaris_bulan_ini = query("
    SELECT 
        p.id,
        p.nama_produk,
        SUM(oi.quantity) as total_terjual,
        SUM(oi.quantity * oi.price) as total_pendapatan
    FROM order_items oi
    JOIN products p ON oi.product_id = p.id
    JOIN orders o ON oi.order_id = o.id
    WHERE o.status != 'Dibatalkan' 
        AND MONTH(o.created_at) = $filter_bulan 
        AND YEAR(o.created_at) = $filter_tahun
    GROUP BY p.id
    ORDER BY total_terjual DESC
    LIMIT 5
");

// ===== TOTAL STATISTIK =====
$total_order = query("SELECT COUNT(*) as total FROM orders WHERE status != 'Dibatalkan'")[0]['total'];
$total_pendapatan = query("SELECT SUM(total_amount) as total FROM orders WHERE status != 'Dibatalkan'")[0]['total'] ?? 0;
$total_diskon = query("SELECT SUM(diskon) as total FROM orders")[0]['total'] ?? 0;
$total_produk_terjual = query("SELECT SUM(quantity) as total FROM order_items oi JOIN orders o ON oi.order_id = o.id WHERE o.status != 'Dibatalkan'")[0]['total'] ?? 0;

// Order per status
$status_orders = query("
    SELECT status, COUNT(*) as jumlah FROM orders GROUP BY status
");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Penjualan - Afia Cake</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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
        
        /* Filter */
        .filter-box {
            background: white;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 30px;
            display: flex;
            gap: 20px;
            align-items: center;
            flex-wrap: wrap;
        }
        
        .filter-box label {
            font-weight: 500;
        }
        
        .filter-box select {
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
        }
        
        .filter-box button {
            padding: 10px 25px;
            background: #ff6b6b;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }
        
        .btn-cetak {
            background: #4CAF50 !important;
        }
        
        /* Stat Cards */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .stat-card {
            background: white;
            padding: 25px;
            border-radius: 10px;
            text-align: center;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            transition: all 0.3s;
        }
        
        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(255,107,107,0.1);
        }
        
        .stat-card i {
            font-size: 2rem;
            color: #ff6b6b;
            margin-bottom: 15px;
        }
        
        .stat-card .jumlah {
            font-size: 2rem;
            font-weight: bold;
            color: #333;
        }
        
        .stat-card .label {
            color: #666;
            font-size: 0.9rem;
        }
        
        /* Chart Box */
        .chart-box {
            background: white;
            border-radius: 10px;
            padding: 25px;
            margin-bottom: 30px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }
        
        .chart-box h3 {
            margin-bottom: 20px;
            border-left: 4px solid #ff6b6b;
            padding-left: 15px;
        }
        
        .chart-container {
            height: 300px;
        }
        
        /* Tabel */
        .table-container {
            background: white;
            border-radius: 10px;
            padding: 25px;
            margin-bottom: 30px;
            overflow-x: auto;
        }
        
        .table-container h3 {
            margin-bottom: 20px;
            border-left: 4px solid #ff6b6b;
            padding-left: 15px;
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
        
        .badge {
            padding: 3px 8px;
            border-radius: 3px;
            font-size: 0.8rem;
        }
        
        .badge-success { background: #d4edda; color: #155724; }
        .badge-warning { background: #fff3cd; color: #856404; }
        .badge-danger { background: #f8d7da; color: #721c24; }
        .badge-info { background: #d1ecf1; color: #0c5460; }
        
        .summary-row {
            display: flex;
            justify-content: space-between;
            padding: 15px;
            background: #f8f9fa;
            border-radius: 5px;
            margin-top: 20px;
        }
        
        @media print {
            .header, .filter-box, .btn-cetak {
                display: none;
            }
        }
    </style>
</head>
<body>
    <div class="header">
        <h2><i class="fas fa-chart-bar"></i> Laporan Penjualan - Afia Cake</h2>
        <div>
            <span>Halo, <?= $_SESSION['user']['nama_lengkap'] ?></span>
            <a href="dashboard.php"><i class="fas fa-arrow-left"></i> Dashboard</a>
            <a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
        </div>
    </div>
    
    <div class="container">
        <!-- Filter Box -->
        <div class="filter-box">
            <label><i class="fas fa-calendar"></i> Tahun:</label>
            <select id="tahun" name="tahun">
                <?php for($y = 2023; $y <= date('Y'); $y++): ?>
                    <option value="<?= $y ?>" <?= $y == $filter_tahun ? 'selected' : '' ?>><?= $y ?></option>
                <?php endfor; ?>
            </select>
            
            <label><i class="fas fa-calendar-alt"></i> Bulan:</label>
            <select id="bulan" name="bulan">
                <?php for($m = 1; $m <= 12; $m++): ?>
                    <option value="<?= $m ?>" <?= $m == $filter_bulan ? 'selected' : '' ?>>
                        <?= date('F', mktime(0,0,0,$m,1)) ?>
                    </option>
                <?php endfor; ?>
            </select>
            
            <button onclick="filterLaporan()"><i class="fas fa-filter"></i> Tampilkan</button>
            <button onclick="window.print()" class="btn-cetak"><i class="fas fa-print"></i> Cetak</button>
        </div>
        
        <!-- Statistik Utama -->
        <div class="stats-grid">
            <div class="stat-card">
                <i class="fas fa-shopping-cart"></i>
                <div class="jumlah"><?= $total_order ?></div>
                <div class="label">Total Pesanan</div>
            </div>
            <div class="stat-card">
                <i class="fas fa-money-bill-wave"></i>
                <div class="jumlah">Rp <?= number_format($total_pendapatan, 0, ',', '.') ?></div>
                <div class="label">Total Pendapatan</div>
            </div>
            <div class="stat-card">
                <i class="fas fa-percent"></i>
                <div class="jumlah">Rp <?= number_format($total_diskon, 0, ',', '.') ?></div>
                <div class="label">Total Diskon</div>
            </div>
            <div class="stat-card">
                <i class="fas fa-cake-candles"></i>
                <div class="jumlah"><?= $total_produk_terjual ?></div>
                <div class="label">Produk Terjual</div>
            </div>
        </div>
        
        <!-- Status Pesanan -->
        <div class="stats-grid">
            <?php foreach($status_orders as $s): 
                $color = '';
                if($s['status'] == 'Selesai') $color = 'badge-success';
                elseif($s['status'] == 'Diproses') $color = 'badge-info';
                elseif($s['status'] == 'Dikirim') $color = 'badge-warning';
                elseif($s['status'] == 'Dibatalkan') $color = 'badge-danger';
                else $color = 'badge-info';
            ?>
            <div class="stat-card">
                <i class="fas <?= $s['status'] == 'Selesai' ? 'fa-check-circle' : ($s['status'] == 'Dibatalkan' ? 'fa-ban' : 'fa-clock') ?>"></i>
                <div class="jumlah"><?= $s['jumlah'] ?></div>
                <div class="label"><?= $s['status'] ?></div>
            </div>
            <?php endforeach; ?>
        </div>
        
        <!-- Grafik Penjualan Per Bulan -->
        <div class="chart-box">
            <h3><i class="fas fa-chart-line"></i> Grafik Penjualan 12 Bulan Terakhir</h3>
            <div class="chart-container">
                <canvas id="salesChart"></canvas>
            </div>
        </div>
        
        <!-- Penjualan Per Minggu (Bulan Ini) -->
        <div class="table-container">
            <h3><i class="fas fa-calendar-week"></i> Penjualan Per Minggu - <?= date('F Y', mktime(0,0,0,$filter_bulan,1,$filter_tahun)) ?></h3>
            <?php if(empty($penjualan_per_minggu)): ?>
                <p style="text-align:center; padding:30px; color:#999;">Belum ada data penjualan bulan ini</p>
            <?php else: ?>
                <table>
                    <thead>
                        <tr>
                            <th>Minggu ke-</th>
                            <th>Jumlah Order</th>
                            <th>Total Penjualan</th>
                            <th>Rata-rata per Order</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($penjualan_per_minggu as $w): ?>
                        <tr>
                            <td><strong>Minggu <?= $w['minggu_ke'] ?></strong></td>
                            <td><?= $w['jumlah_order'] ?>x订单</td>
                            <td>Rp <?= number_format($w['total_penjualan'], 0, ',', '.') ?></td>
                            <td>Rp <?= number_format($w['total_penjualan'] / $w['jumlah_order'], 0, ',', '.') ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
        
        <!-- Penjualan Per Hari (Bulan Ini) -->
        <div class="chart-box">
            <h3><i class="fas fa-calendar-day"></i> Grafik Penjualan Per Hari - <?= date('F Y', mktime(0,0,0,$filter_bulan,1,$filter_tahun)) ?></h3>
            <div class="chart-container">
                <canvas id="dailyChart"></canvas>
            </div>
        </div>
        
        <!-- Produk Terlaris All Time -->
        <div class="table-container">
            <h3><i class="fas fa-trophy"></i> 10 Produk Terlaris Sepanjang Masa</h3>
            <table>
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Nama Produk</th>
                        <th>Kategori</th>
                        <th>Terjual</th>
                        <th>Total Pendapatan</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $no=1; foreach($produk_terlaris as $p): ?>
                    <tr>
                        <td><?= $no++ ?></td>
                        <td><strong><?= $p['nama_produk'] ?></strong></td>
                        <td><?= $p['kategori'] ?></td>
                        <td><?= $p['total_terjual'] ?> pcs (<?= $p['jumlah_dipesan'] ?>x order)</td>
                        <td>Rp <?= number_format($p['total_pendapatan'], 0, ',', '.') ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        
        <!-- Produk Terlaris Bulan Ini -->
        <div class="table-container">
            <h3><i class="fas fa-star"></i> 5 Produk Terlaris Bulan <?= date('F Y', mktime(0,0,0,$filter_bulan,1,$filter_tahun)) ?></h3>
            <?php if(empty($produk_terlaris_bulan_ini)): ?>
                <p style="text-align:center; padding:30px; color:#999;">Belum ada penjualan bulan ini</p>
            <?php else: ?>
                <table>
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Nama Produk</th>
                            <th>Terjual</th>
                            <th>Total Pendapatan</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $no=1; foreach($produk_terlaris_bulan_ini as $p): ?>
                        <tr>
                            <td><?= $no++ ?></td>
                            <td><strong><?= $p['nama_produk'] ?></strong></td>
                            <td><?= $p['total_terjual'] ?> pcs</td>
                            <td>Rp <?= number_format($p['total_pendapatan'], 0, ',', '.') ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
        
        <!-- Ringkasan -->
        <div class="summary-row">
            <span><strong>Total Pendapatan:</strong> Rp <?= number_format($total_pendapatan, 0, ',', '.') ?></span>
            <span><strong>Total Diskon:</strong> Rp <?= number_format($total_diskon, 0, ',', '.') ?></span>
            <span><strong>Total Produk Terjual:</strong> <?= $total_produk_terjual ?> pcs</span>
            <span><strong>Total Order:</strong> <?= $total_order ?> transaksi</span>
        </div>
    </div>
    
    <script>
        // Filter laporan
        function filterLaporan() {
            var tahun = document.getElementById('tahun').value;
            var bulan = document.getElementById('bulan').value;
            window.location.href = 'laporan.php?tahun=' + tahun + '&bulan=' + bulan;
        }
        
        // Data untuk grafik penjualan per bulan
        const bulanLabels = <?= json_encode(array_column($penjualan_per_bulan, 'bulan')) ?>;
        const totalPenjualan = <?= json_encode(array_column($penjualan_per_bulan, 'total_penjualan')) ?>;
        
        // Grafik Penjualan
        const ctx = document.getElementById('salesChart').getContext('2d');
        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: bulanLabels,
                datasets: [{
                    label: 'Total Penjualan (Rp)',
                    data: totalPenjualan,
                    backgroundColor: 'rgba(255,107,107,0.6)',
                    borderColor: '#ff6b6b',
                    borderWidth: 2,
                    borderRadius: 5
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { position: 'top' }
                }
            }
        });
        
        // Data untuk grafik penjualan per hari
        const hariLabels = <?= json_encode(array_column($penjualan_per_hari, 'tgl')) ?>;
        const totalHari = <?= json_encode(array_column($penjualan_per_hari, 'total_penjualan')) ?>;
        
        // Grafik Penjualan Per Hari
        const ctx2 = document.getElementById('dailyChart').getContext('2d');
        new Chart(ctx2, {
            type: 'line',
            data: {
                labels: hariLabels,
                datasets: [{
                    label: 'Penjualan (Rp)',
                    data: totalHari,
                    borderColor: '#4CAF50',
                    backgroundColor: 'rgba(76,175,80,0.1)',
                    tension: 0.4,
                    fill: true
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { position: 'top' }
                }
            }
        });
    </script>
</body>
</html>