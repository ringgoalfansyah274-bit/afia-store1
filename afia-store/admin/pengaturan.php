<?php
session_start();
include '../includes/config.php';

if(!isset($_SESSION['user']) || $_SESSION['user']['role'] != 'admin') {
    header('Location: login.php');
    exit;
}

// Ambil data kontak
$kontak = getKontak();

// Proses update pengaturan
if(isset($_POST['update'])) {
    $whatsapp = $_POST['whatsapp'];
    $instagram = $_POST['instagram'];
    $email = $_POST['email'];
    $telepon = $_POST['telepon'];
    $alamat = $_POST['alamat'];
    $jam = $_POST['jam_operasional'];
    $maps_lat = $_POST['maps_lat'];
    $maps_lng = $_POST['maps_lng'];
    
    $query = "UPDATE kontak SET 
              whatsapp = '$whatsapp',
              instagram = '$instagram',
              email = '$email',
              telepon = '$telepon',
              alamat = '$alamat',
              jam_operasional = '$jam',
              maps_lat = '$maps_lat',
              maps_lng = '$maps_lng'
              WHERE id = 1";
    
    if(mysqli_query($conn, $query)) {
        $success = "Pengaturan berhasil disimpan!";
        $kontak = getKontak(); // Refresh data
    } else {
        $error = "Gagal menyimpan: " . mysqli_error($conn);
    }
}

// Proses ganti password admin
if(isset($_POST['ganti_password'])) {
    $password_lama = md5($_POST['password_lama']);
    $password_baru = $_POST['password_baru'];
    $password_konfirmasi = $_POST['password_konfirmasi'];
    
    // Cek password lama
    $cek = query("SELECT * FROM users WHERE username='admin' AND password='$password_lama'");
    
    if(empty($cek)) {
        $error_pass = "Password lama salah!";
    } elseif($password_baru != $password_konfirmasi) {
        $error_pass = "Password baru tidak cocok!";
    } elseif(strlen($password_baru) < 6) {
        $error_pass = "Password minimal 6 karakter!";
    } else {
        $password_baru_md5 = md5($password_baru);
        mysqli_query($conn, "UPDATE users SET password='$password_baru_md5' WHERE username='admin'");
        $success_pass = "Password berhasil diganti!";
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pengaturan - Afia Store</title>
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
            max-width: 800px;
            margin: 30px auto;
            padding: 0 20px;
        }
        
        .card {
            background: white;
            border-radius: 10px;
            padding: 30px;
            margin-bottom: 30px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }
        
        .card h3 {
            margin-bottom: 20px;
            color: #333;
            border-bottom: 2px solid #ff6b6b;
            padding-bottom: 10px;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 5px;
            color: #666;
            font-weight: 500;
        }
        
        .form-group input,
        .form-group textarea {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-family: inherit;
        }
        
        .form-group input:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: #ff6b6b;
        }
        
        .btn-simpan {
            background: linear-gradient(135deg, #ff6b6b, #ff8e8e);
            color: white;
            padding: 12px 30px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 1rem;
        }
        
        .btn-simpan:hover {
            opacity: 0.9;
        }
        
        .success {
            background: #d4edda;
            color: #155724;
            padding: 12px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        
        .error {
            background: #f8d7da;
            color: #721c24;
            padding: 12px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        
        .info-text {
            font-size: 0.9rem;
            color: #666;
            margin-top: 5px;
        }
        
        .grid-2 {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
        }
    </style>
</head>
<body>
    <div class="header">
        <h2><i class="fas fa-cog"></i> Pengaturan Toko - Afia Store</h2>
        <div>
            <a href="dashboard.php"><i class="fas fa-arrow-left"></i> Kembali</a>
            <a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
        </div>
    </div>
    
    <div class="container">
        <?php if(isset($success)): ?>
            <div class="success"><i class="fas fa-check-circle"></i> <?= $success ?></div>
        <?php endif; ?>
        
        <?php if(isset($error)): ?>
            <div class="error"><i class="fas fa-exclamation-circle"></i> <?= $error ?></div>
        <?php endif; ?>
        
        <!-- Form Pengaturan Toko -->
        <div class="card">
            <h3><i class="fas fa-store"></i> Informasi Toko</h3>
            
            <form method="POST">
                <div class="form-group">
                    <label>WhatsApp (Format: 62812xxxxxx)</label>
                    <input type="text" name="whatsapp" value="<?= $kontak['whatsapp'] ?>" required>
                    <small class="info-text">Contoh: 6281234567890 (tanpa + dan spasi)</small>
                </div>
                
                <div class="form-group">
                    <label>Instagram (tanpa @)</label>
                    <input type="text" name="instagram" value="<?= $kontak['instagram'] ?>" required>
                </div>
                
                <div class="form-group">
                    <label>Email</label>
                    <input type="email" name="email" value="<?= $kontak['email'] ?>" required>
                </div>
                
                <div class="form-group">
                    <label>Telepon</label>
                    <input type="text" name="telepon" value="<?= $kontak['telepon'] ?>" required>
                </div>
                
                <div class="form-group">
                    <label>Alamat Lengkap</label>
                    <textarea name="alamat" rows="3" required><?= $kontak['alamat'] ?></textarea>
                </div>
                
                <div class="form-group">
                    <label>Jam Operasional</label>
                    <input type="text" name="jam_operasional" value="<?= $kontak['jam_operasional'] ?>" required>
                </div>
                
                <h4 style="margin: 20px 0 10px;">Koordinat Google Maps</h4>
                <div class="grid-2">
                    <div class="form-group">
                        <label>Latitude</label>
                        <input type="text" name="maps_lat" value="<?= $kontak['maps_lat'] ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label>Longitude</label>
                        <input type="text" name="maps_lng" value="<?= $kontak['maps_lng'] ?>" required>
                    </div>
                </div>
                <small class="info-text">
                    <i class="fas fa-info-circle"></i> 
                    Cara dapat koordinat: Buka Google Maps, klik kanan di lokasi toko, pilih "Apa yang ada di sini?"
                </small>
                
                <button type="submit" name="update" class="btn-simpan" style="margin-top: 20px;">
                    <i class="fas fa-save"></i> Simpan Pengaturan
                </button>
            </form>
        </div>
        
        <!-- Ganti Password Admin -->
        <div class="card">
            <h3><i class="fas fa-key"></i> Ganti Password Admin</h3>
            
            <?php if(isset($error_pass)): ?>
                <div class="error"><?= $error_pass ?></div>
            <?php endif; ?>
            
            <?php if(isset($success_pass)): ?>
                <div class="success"><?= $success_pass ?></div>
            <?php endif; ?>
            
            <form method="POST">
                <div class="form-group">
                    <label>Password Lama</label>
                    <input type="password" name="password_lama" required>
                </div>
                
                <div class="form-group">
                    <label>Password Baru (min. 6 karakter)</label>
                    <input type="password" name="password_baru" required>
                </div>
                
                <div class="form-group">
                    <label>Konfirmasi Password Baru</label>
                    <input type="password" name="password_konfirmasi" required>
                </div>
                
                <button type="submit" name="ganti_password" class="btn-simpan">
                    <i class="fas fa-sync-alt"></i> Ganti Password
                </button>
            </form>
        </div>
    </div>
</body>
</html>