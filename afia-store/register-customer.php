<?php
session_start();
include 'includes/config.php';

$error = '';
$success = '';

if(isset($_POST['register'])) {
    $nama = mysqli_real_escape_string($conn, $_POST['nama']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $password = md5($_POST['password']);
    $no_hp = mysqli_real_escape_string($conn, $_POST['no_hp']);
    $alamat = mysqli_real_escape_string($conn, $_POST['alamat']);
    
    $cek = mysqli_query($conn, "SELECT id FROM customers WHERE email = '$email'");
    
    if(mysqli_num_rows($cek) > 0) {
        $error = "Email sudah terdaftar!";
    } else {
        $query = "INSERT INTO customers (nama, email, password, no_hp, alamat) 
                  VALUES ('$nama', '$email', '$password', '$no_hp', '$alamat')";
        
        if(mysqli_query($conn, $query)) {
            $success = "Pendaftaran berhasil! Silakan login.";
        } else {
            $error = "Gagal mendaftar: " . mysqli_error($conn);
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Akun - Afia Cake</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Segoe UI', sans-serif;
            background: linear-gradient(135deg, #ff6b6b, #ff8e8e);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        .register-container {
            background: white;
            padding: 40px;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.2);
            width: 100%;
            max-width: 500px;
        }
        .register-header { text-align: center; margin-bottom: 30px; }
        .register-header i { font-size: 3rem; color: #ff6b6b; margin-bottom: 15px; }
        .register-header h2 { color: #333; font-size: 1.8rem; }
        .register-header p { color: #666; font-size: 0.9rem; }
        .form-group { margin-bottom: 20px; }
        .form-group label { display: block; margin-bottom: 8px; color: #333; font-weight: 500; }
        .form-group input, .form-group textarea {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid #eee;
            border-radius: 10px;
            font-size: 1rem;
            font-family: inherit;
            transition: all 0.3s;
        }
        .form-group input:focus, .form-group textarea:focus { outline: none; border-color: #ff6b6b; box-shadow: 0 0 0 4px rgba(255,107,107,0.1); }
        .btn-register {
            width: 100%;
            padding: 12px;
            background: linear-gradient(135deg, #ff6b6b, #ff8e8e);
            color: white;
            border: none;
            border-radius: 10px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
        }
        .btn-register:hover { transform: translateY(-2px); box-shadow: 0 10px 20px rgba(255,107,107,0.3); }
        .error { background: #f8d7da; color: #721c24; padding: 12px; border-radius: 8px; margin-bottom: 20px; text-align: center; }
        .success { background: #d4edda; color: #155724; padding: 12px; border-radius: 8px; margin-bottom: 20px; text-align: center; }
        .login-link { text-align: center; margin-top: 25px; padding-top: 20px; border-top: 1px solid #eee; }
        .login-link a { color: #ff6b6b; text-decoration: none; }
        .login-link a:hover { text-decoration: underline; }
        .back-home { text-align: center; margin-top: 15px; }
        .back-home a { color: #999; text-decoration: none; font-size: 0.9rem; }
        .back-home a:hover { color: #ff6b6b; }
    </style>
</head>
<body>
    <div class="register-container">
        <div class="register-header">
            <i class="fas fa-user-plus"></i>
            <h2>Daftar Akun</h2>
            <p>Buat akun untuk berbelanja di Afia Cake</p>
        </div>
        
        <?php if($error): ?>
            <div class="error"><i class="fas fa-exclamation-circle"></i> <?= $error ?></div>
        <?php endif; ?>
        <?php if($success): ?>
            <div class="success"><i class="fas fa-check-circle"></i> <?= $success ?></div>
        <?php endif; ?>
        
        <?php if(!$success): ?>
        <form method="POST">
            <div class="form-group">
                <label><i class="fas fa-user"></i> Nama Lengkap</label>
                <input type="text" name="nama" placeholder="Masukkan nama lengkap" required>
            </div>
            <div class="form-group">
                <label><i class="fas fa-envelope"></i> Email</label>
                <input type="email" name="email" placeholder="Masukkan email" required>
            </div>
            <div class="form-group">
                <label><i class="fas fa-lock"></i> Password</label>
                <input type="password" name="password" placeholder="Minimal 6 karakter" required>
            </div>
            <div class="form-group">
                <label><i class="fab fa-whatsapp"></i> Nomor WhatsApp</label>
                <input type="text" name="no_hp" placeholder="Contoh: 081234567890" required>
            </div>
            <div class="form-group">
                <label><i class="fas fa-map-marker-alt"></i> Alamat</label>
                <textarea name="alamat" rows="3" placeholder="Masukkan alamat lengkap" required></textarea>
            </div>
            <button type="submit" name="register" class="btn-register"><i class="fas fa-user-plus"></i> Daftar</button>
        </form>
        <?php endif; ?>
        
        <div class="login-link">
            Sudah punya akun? <a href="login-customer.php">Login di sini</a>
        </div>
        <div class="back-home">
            <a href="index.php"><i class="fas fa-arrow-left"></i> Kembali ke Home</a>
        </div>
    </div>
</body>
</html>