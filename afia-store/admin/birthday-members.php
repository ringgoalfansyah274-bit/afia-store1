<?php
session_start();
include '../includes/config.php';

// Cek login admin
if(!isset($_SESSION['user']) || $_SESSION['user']['role'] != 'admin') {
    header('Location: login.php');
    exit;
}

// ===== PROSES HAPUS MEMBER =====
if(isset($_GET['hapus'])) {
    $id = $_GET['hapus'];
    
    // Hapus data member berdasarkan ID
    $query = "DELETE FROM birthday_club WHERE id = $id";
    
    if(mysqli_query($conn, $query)) {
        $_SESSION['notification'] = [
            'type' => 'success',
            'message' => '✅ Member berhasil dihapus!'
        ];
    } else {
        $_SESSION['notification'] = [
            'type' => 'error',
            'message' => '❌ Gagal menghapus member: ' . mysqli_error($conn)
        ];
    }
    
    header('Location: birthday-members.php');
    exit;
}
// ===== AKHIR PROSES HAPUS =====

// TEST: Cek apakah tabel ada
$table_check = mysqli_query($conn, "SHOW TABLES LIKE 'birthday_club'");
if(mysqli_num_rows($table_check) == 0) {
    // Tabel belum ada, buat dulu
    mysqli_query($conn, "CREATE TABLE birthday_club (
        id INT AUTO_INCREMENT PRIMARY KEY,
        nama VARCHAR(100) NOT NULL,
        email VARCHAR(100) NOT NULL UNIQUE,
        whatsapp VARCHAR(20) NOT NULL UNIQUE,
        tanggal_lahir DATE NOT NULL,
        kode_voucher VARCHAR(50) NOT NULL UNIQUE,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");
}

// AMBIL DATA MEMBER
$members = [];
$query = "SELECT * FROM birthday_club ORDER BY created_at DESC";
$result = mysqli_query($conn, $query);

if($result) {
    while($row = mysqli_fetch_assoc($result)) {
        $members[] = $row;
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Birthday Club Members - Afia Cake</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI', sans-serif; background: #f5f5f5; }
        
        .header {
            background: linear-gradient(135deg, #ff6b6b, #ff8e8e);
            color: white;
            padding: 1rem 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
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
        
        .card {
            background: white;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            overflow: hidden;
        }
        
        .card-header {
            background: linear-gradient(135deg, #ff6b6b, #ff8e8e);
            color: white;
            padding: 20px;
            font-weight: bold;
        }
        
        .table-container {
            overflow-x: auto;
            padding: 20px;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
        }
        
        th {
            background: #f8f8f8;
            padding: 12px;
            text-align: left;
            border-bottom: 2px solid #eee;
        }
        
        td {
            padding: 12px;
            border-bottom: 1px solid #eee;
        }
        
        tr:hover {
            background: #f9f9f9;
        }
        
        .btn-hapus {
            background: #dc3545;
            color: white;
            border: none;
            padding: 5px 12px;
            border-radius: 5px;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
        }
        
        .btn-hapus:hover {
            background: #c82333;
        }
        
        .btn-wa {
            background: #25D366;
            color: white;
            border: none;
            padding: 5px 12px;
            border-radius: 5px;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
        }
        
        .btn-wa:hover {
            background: #128C7E;
        }
        
        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: #999;
        }
        
        .empty-state i {
            font-size: 4rem;
            margin-bottom: 15px;
        }
        
        .back-link {
            display: inline-block;
            margin: 20px 0;
            color: #ff6b6b;
            text-decoration: none;
        }
        
        /* NOTIFIKASI */
        .notification {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 9999;
            animation: slideIn 0.3s ease;
            max-width: 400px;
        }
        
        @keyframes slideIn {
            from {
                transform: translateX(100%);
                opacity: 0;
            }
            to {
                transform: translateX(0);
                opacity: 1;
            }
        }
        
        .notification-content {
            background: white;
            padding: 15px 20px;
            border-radius: 10px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.15);
            display: flex;
            align-items: center;
            gap: 15px;
            border-left: 4px solid;
        }
        
        .notification-success .notification-content {
            border-left-color: #4CAF50;
            background: #f0f9f0;
        }
        
        .notification-error .notification-content {
            border-left-color: #f44336;
            background: #fef0f0;
        }
        
        .notification-close {
            background: none;
            border: none;
            font-size: 1.2rem;
            cursor: pointer;
            color: #999;
            margin-left: auto;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1><i class="fas fa-gift"></i> Birthday Club Members</h1>
        <div>
            <span>Halo, <?= $_SESSION['user']['nama_lengkap'] ?></span>
            <a href="dashboard.php">Dashboard</a>
            <a href="logout.php">Logout</a>
        </div>
    </div>
    
    <!-- NOTIFIKASI -->
    <?php if(isset($_SESSION['notification'])): ?>
    <div id="notification" class="notification notification-<?= $_SESSION['notification']['type'] ?>">
        <div class="notification-content">
            <i class="fas <?= $_SESSION['notification']['type'] == 'success' ? 'fa-check-circle' : 'fa-exclamation-circle' ?>"></i>
            <span><?= $_SESSION['notification']['message'] ?></span>
            <button onclick="this.parentElement.parentElement.style.display='none'" class="notification-close">&times;</button>
        </div>
    </div>
    <script>
        setTimeout(function() {
            var notif = document.getElementById('notification');
            if(notif) notif.style.display = 'none';
        }, 3000);
    </script>
    <?php unset($_SESSION['notification']); ?>
    <?php endif; ?>
    
    <div class="container">
        <a href="dashboard.php" class="back-link">
            <i class="fas fa-arrow-left"></i> Kembali ke Dashboard
        </a>
        
        <div class="card">
            <div class="card-header">
                <i class="fas fa-users"></i> Daftar Member Birthday Club
            </div>
            
            <div class="table-container">
                <?php if(empty($members)): ?>
                    <div class="empty-state">
                        <i class="fas fa-users"></i>
                        <h3>Belum ada member</h3>
                        <p>Belum ada yang mendaftar di Birthday Club</p>
                    </div>
                <?php else: ?>
                    <table>
                        <thead>
                            <tr>
                                <th>Nama</th>
                                <th>Email</th>
                                <th>WhatsApp</th>
                                <th>Tanggal Lahir</th>
                                <th>Kode Voucher</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($members as $member): ?>
                            <tr>
                                <td><strong><?= htmlspecialchars($member['nama']) ?></strong></td>
                                <td><?= htmlspecialchars($member['email']) ?></td>
                                <td>
                                    <a href="https://wa.me/<?= $member['whatsapp'] ?>" target="_blank" class="btn-wa">
                                        <i class="fab fa-whatsapp"></i> <?= $member['whatsapp'] ?>
                                    </a>
                                </td>
                                <td><?= date('d/m/Y', strtotime($member['tanggal_lahir'])) ?></td>
                                <td><code><?= $member['kode_voucher'] ?></code></td>
                                <td>
                                    <a href="?hapus=<?= $member['id'] ?>" class="btn-hapus" onclick="return confirm('Yakin ingin menghapus member <?= htmlspecialchars($member['nama']) ?>?')">
                                        <i class="fas fa-trash"></i> Hapus
                                    </a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html>