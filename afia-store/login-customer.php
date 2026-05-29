<?php
session_start();
include 'includes/config.php';

$error = '';

if(isset($_POST['login'])) {
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $password = md5($_POST['password']);
    
    $query = "SELECT * FROM customers WHERE email = '$email' AND password = '$password'";
    $result = mysqli_query($conn, $query);
    
    if(mysqli_num_rows($result) > 0) {
        $customer = mysqli_fetch_assoc($result);
        $_SESSION['customer'] = $customer;
        $_SESSION['notification'] = [
            'type' => 'success',
            'message' => '✅ Selamat datang, ' . $customer['nama'] . '!'
        ];
        header('Location: index.php');
        exit;
    } else {
        $error = "Email atau password salah!";
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Pembeli - Afia Cake</title>
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
        .login-container {
            background: white;
            padding: 40px;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.2);
            width: 100%;
            max-width: 400px;
        }
        .login-header { text-align: center; margin-bottom: 30px; }
        .login-header i { font-size: 3rem; color: #ff6b6b; margin-bottom: 15px; }
        .login-header h2 { color: #333; font-size: 1.8rem; }
        .login-header p { color: #666; font-size: 0.9rem; }
        .form-group { margin-bottom: 20px; }
        .form-group label { display: block; margin-bottom: 8px; color: #333; font-weight: 500; }
        .form-group input {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid #eee;
            border-radius: 10px;
            font-size: 1rem;
            transition: all 0.3s;
        }
        .form-group input:focus { outline: none; border-color: #ff6b6b; box-shadow: 0 0 0 4px rgba(255,107,107,0.1); }
        .btn-login {
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
        .btn-login:hover { transform: translateY(-2px); box-shadow: 0 10px 20px rgba(255,107,107,0.3); }
        .error { background: #f8d7da; color: #721c24; padding: 12px; border-radius: 8px; margin-bottom: 20px; text-align: center; }
        .register-link { text-align: center; margin-top: 25px; padding-top: 20px; border-top: 1px solid #eee; }
        .register-link a { color: #ff6b6b; text-decoration: none; }
        .register-link a:hover { text-decoration: underline; }
        .back-home { text-align: center; margin-top: 15px; }
        .back-home a { color: #999; text-decoration: none; font-size: 0.9rem; }
        .back-home a:hover { color: #ff6b6b; }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-header">
            <i class="fas fa-user-circle"></i>
            <h2>Login Pembeli</h2>
            <p>Silakan login untuk melanjutkan belanja</p>
        </div>
        
        <?php if($error): ?>
            <div class="error"><i class="fas fa-exclamation-circle"></i> <?= $error ?></div>
        <?php endif; ?>
        
        <form method="POST">
            <div class="form-group">
                <label><i class="fas fa-envelope"></i> Email</label>
                <input type="email" name="email" placeholder="Masukkan email Anda" required>
            </div>
            <div class="form-group">
                <label><i class="fas fa-lock"></i> Password</label>
                <input type="password" name="password" placeholder="Masukkan password" required>
            </div>
            <button type="submit" name="login" class="btn-login"><i class="fas fa-sign-in-alt"></i> Login</button>
        </form>
        
        <div class="register-link">
            Belum punya akun? <a href="register-customer.php">Daftar di sini</a>
        </div>
        <div class="back-home">
            <a href="index.php"><i class="fas fa-arrow-left"></i> Kembali ke Home</a>
        </div>
    </div>
</body>
</html>