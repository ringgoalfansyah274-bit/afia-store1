<?php
ob_start();
session_start();
include 'config.php';

// Ambil notifikasi
$notification = '';
$notification_type = '';
if(isset($_SESSION['notification'])) {
    $notification = $_SESSION['notification']['message'];
    $notification_type = $_SESSION['notification']['type'];
    unset($_SESSION['notification']);
}

// Hitung jumlah item di keranjang
$cart_count = isset($_SESSION['cart']) ? count($_SESSION['cart']) : 0;
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Afia Cake - Toko Kue Online</title>
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
    
    <!-- AOS Animation -->
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Poppins', sans-serif;
            color: #333;
            overflow-x: hidden;
        }
        
        /* NOTIFIKASI */
        .notification {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 9999;
            animation: slideIn 0.3s ease;
            max-width: 400px;
            min-width: 300px;
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
        
        @keyframes slideOut {
            from { transform: translateX(0); opacity: 1; }
            to { transform: translateX(100%); opacity: 0; }
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
        
        .notification-success i {
            color: #4CAF50;
            font-size: 1.5rem;
        }
        
        .notification-error .notification-content {
            border-left-color: #f44336;
            background: #fef0f0;
        }
        
        .notification-error i {
            color: #f44336;
            font-size: 1.5rem;
        }
        
        .notification-close {
            background: none;
            border: none;
            font-size: 1.5rem;
            cursor: pointer;
            color: #999;
            margin-left: auto;
            padding: 0 5px;
        }
        
        .notification-close:hover {
            color: #333;
        }
        
        /* TOP BAR */
        .top-bar {
            background: linear-gradient(135deg, #ff6b6b, #ff8e8e);
            color: white;
            padding: 8px 0;
            font-size: 14px;
            width: 100%;
        }
        
        .top-bar .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
        }
        
        .contact-info i {
            margin-right: 5px;
        }
        
        .contact-info span {
            margin-right: 20px;
        }
        
        .social-links a {
            color: white;
            margin-left: 15px;
            font-size: 18px;
            transition: transform 0.3s;
            display: inline-block;
        }
        
        .social-links a:hover {
            transform: translateY(-3px);
        }
        
        /* MAIN HEADER */
        .main-header {
            background: white;
            box-shadow: 0 2px 20px rgba(0,0,0,0.1);
            position: sticky;
            top: 0;
            z-index: 1000;
        }
        
        nav {
            max-width: 1200px;
            margin: 0 auto;
            padding: 15px 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 10px;
        }
        
        .logo h1 {
            font-size: 28px;
            font-weight: 700;
            background: linear-gradient(135deg, #ff6b6b, #ff8e8e);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }
        
        .nav-menu {
            display: flex;
            list-style: none;
            align-items: center;
            flex-wrap: wrap;
            gap: 5px;
        }
        
        .nav-menu li {
            margin-left: 15px;
        }
        
        .nav-menu a {
            color: #333;
            text-decoration: none;
            font-weight: 500;
            transition: color 0.3s;
            position: relative;
            display: flex;
            align-items: center;
            gap: 5px;
            padding: 8px 12px;
            border-radius: 5px;
        }
        
        .nav-menu a:hover {
            color: #ff6b6b;
            background: #fff5f5;
        }
        
        .nav-menu a i {
            font-size: 1rem;
        }
        
        /* CART BADGE */
        .cart-badge {
            background: #ff6b6b;
            color: white;
            border-radius: 50%;
            padding: 2px 6px;
            font-size: 0.7rem;
            position: relative;
            top: -10px;
            right: 5px;
        }
        
        /* FLOATING SOCIAL */
        .floating-social {
            position: fixed;
            bottom: 30px;
            right: 30px;
            z-index: 999;
        }
        
        .floating-social a {
            display: block;
            width: 60px;
            height: 60px;
            border-radius: 50%;
            text-align: center;
            line-height: 60px;
            color: white;
            font-size: 30px;
            margin-bottom: 15px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
            transition: all 0.3s;
            animation: pulse 2s infinite;
        }
        
        .floating-social a:hover {
            transform: scale(1.1) rotate(360deg);
        }
        
        .floating-social .wa {
            background: linear-gradient(135deg, #25D366, #128C7E);
        }
        
        .floating-social .ig {
            background: linear-gradient(135deg, #833AB4, #E1306C, #FCAF45);
        }
        
        @keyframes pulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.05); }
            100% { transform: scale(1); }
        }
        
        /* RESPONSIVE */
        @media (max-width: 768px) {
            .top-bar .container {
                flex-direction: column;
                gap: 10px;
                text-align: center;
            }
            
            .contact-info span {
                margin-right: 10px;
            }
            
            nav {
                flex-direction: column;
                gap: 15px;
            }
            
            .nav-menu {
                justify-content: center;
            }
            
            .nav-menu li {
                margin: 5px;
            }
            
            .nav-menu a {
                padding: 5px 10px;
                font-size: 0.9rem;
            }
            
            .notification {
                top: 10px;
                right: 10px;
                left: 10px;
                max-width: none;
            }
            
            .floating-social {
                bottom: 80px;
                right: 15px;
            }
            
            .floating-social a {
                width: 45px;
                height: 45px;
                line-height: 45px;
                font-size: 22px;
            }
        }
    </style>
</head>
<body>
    <!-- NOTIFIKASI -->
    <?php if($notification): ?>
    <div id="notification" class="notification notification-<?= $notification_type ?>">
        <div class="notification-content">
            <i class="fas <?= $notification_type == 'success' ? 'fa-check-circle' : 'fa-exclamation-circle' ?>"></i>
            <span><?= $notification ?></span>
            <button onclick="tutupNotifikasi()" class="notification-close">&times;</button>
        </div>
    </div>
    <script>
        function tutupNotifikasi() {
            var notif = document.getElementById('notification');
            if(notif) {
                notif.style.animation = 'slideOut 0.3s ease';
                setTimeout(function() {
                    notif.style.display = 'none';
                }, 300);
            }
        }
        setTimeout(function() {
            var notif = document.getElementById('notification');
            if(notif) {
                notif.style.animation = 'slideOut 0.3s ease';
                setTimeout(function() {
                    notif.style.display = 'none';
                }, 300);
            }
        }, 3000);
    </script>
    <?php endif; ?>
    
    <!-- TOP BAR -->
    <div class="top-bar">
        <div class="container">
            <div class="contact-info">
                <i class="fas fa-phone"></i> <span><?= $kontak['telepon'] ?></span>
                <i class="fas fa-envelope"></i> <span><?= $kontak['email'] ?></span>
                <i class="fas fa-clock"></i> <span><?= $kontak['jam_operasional'] ?></span>
            </div>
            <div class="social-links">
                <a href="https://wa.me/<?= $kontak['whatsapp'] ?>" target="_blank"><i class="fab fa-whatsapp"></i></a>
                <a href="https://instagram.com/<?= $kontak['instagram'] ?>" target="_blank"><i class="fab fa-instagram"></i></a>
                <a href="#"><i class="fab fa-facebook"></i></a>
                <a href="#"><i class="fab fa-twitter"></i></a>
            </div>
        </div>
    </div>
    
    <!-- MAIN HEADER -->
    <div class="main-header">
        <nav>
            <div class="logo">
                <h1>Afia Cake</h1>
            </div>
            <ul class="nav-menu">
                <li><a href="index.php"><i class="fas fa-home"></i> Home</a></li>
                <li><a href="cakes.php"><i class="fas fa-cake-candles"></i> Cakes</a></li>
                <li><a href="blog.php"><i class="fas fa-blog"></i> Blog</a></li>
                <li><a href="faq.php"><i class="fas fa-question-circle"></i> FAQ</a></li>
                <li><a href="wishlist.php"><i class="fas fa-heart"></i> Wishlist</a></li>
                <li><a href="about.php"><i class="fas fa-info-circle"></i> About</a></li>
                <li><a href="contact.php"><i class="fas fa-envelope"></i> Contact</a></li>
                <li><a href="keranjang.php">
                    <i class="fas fa-shopping-cart"></i> Cart
                    <?php if($cart_count > 0): ?>
                        <span class="cart-badge"><?= $cart_count ?></span>
                    <?php endif; ?>
                </a></li>
                
                <!-- ===== MENU LOGIN CUSTOMER & TRACKING ===== -->
                <?php if(isset($_SESSION['customer'])): ?>
                    <li><a href="tracking-order.php"><i class="fas fa-truck"></i> Tracking</a></li>
                    <li><a href="logout-customer.php"><i class="fas fa-sign-out-alt"></i> Logout (<?= $_SESSION['customer']['nama'] ?>)</a></li>
                <?php else: ?>
                    <li><a href="login-customer.php"><i class="fas fa-user"></i> Login</a></li>
                    <li><a href="register-customer.php"><i class="fas fa-user-plus"></i> Daftar</a></li>
                <?php endif; ?>
                <!-- ===== AKHIR MENU LOGIN ===== -->
                
                <li><a href="admin/login.php"><i class="fas fa-user-shield"></i> Admin</a></li>
            </ul>
        </nav>
    </div>
    
    <!-- FLOATING SOCIAL -->
    <div class="floating-social">
        <a href="https://wa.me/<?= $kontak['whatsapp'] ?>?text=Halo%20Afia%20Cake%2C%20saya%20mau%20pesan%20kue" 
           target="_blank" class="wa" title="Chat WhatsApp">
            <i class="fab fa-whatsapp"></i>
        </a>
        <a href="https://instagram.com/<?= $kontak['instagram'] ?>" 
           target="_blank" class="ig" title="Follow Instagram">
            <i class="fab fa-instagram"></i>
        </a>
    </div>