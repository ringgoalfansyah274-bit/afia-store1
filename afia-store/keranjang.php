<?php
include 'includes/header.php';

// Inisialisasi keranjang jika belum ada
if(!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

// ===== PROSES TAMBAH KE KERANJANG =====
if(isset($_POST['add_to_cart'])) {
    $product_id = $_POST['product_id'];
    $quantity = (int)$_POST['quantity'];
    
    if($quantity < 1) $quantity = 1;
    
    // Cek apakah produk sudah ada di keranjang
    $found = false;
    foreach($_SESSION['cart'] as &$item) {
        if($item['id'] == $product_id) {
            $item['quantity'] += $quantity;
            $found = true;
            $product_name = $item['nama'];
            break;
        }
    }
    
    // Jika belum ada, ambil data produk dari database
    if(!$found) {
        $query = "SELECT * FROM products WHERE id = $product_id";
        $result = mysqli_query($conn, $query);
        
        if(mysqli_num_rows($result) > 0) {
            $product = mysqli_fetch_assoc($result);
            $product_name = $product['nama_produk'];
            
            $_SESSION['cart'][] = [
                'id' => $product['id'],
                'nama' => $product['nama_produk'],
                'harga' => $product['harga'],
                'quantity' => $quantity
            ];
        }
    }
    
    // Set notifikasi sukses
    $_SESSION['notification'] = [
        'type' => 'success',
        'message' => "✅ $product_name berhasil ditambahkan ke keranjang!"
    ];
    
    // Kembali ke halaman sebelumnya (cakes.php)
    header('Location: ' . $_SERVER['HTTP_REFERER']);
    exit;
}
// ===== AKHIR PROSES TAMBAH KE KERANJANG =====

// Proses HAPUS dari keranjang
if(isset($_GET['remove'])) {
    $index = $_GET['remove'];
    if(isset($_SESSION['cart'][$index])) {
        $product_name = $_SESSION['cart'][$index]['nama'];
        unset($_SESSION['cart'][$index]);
        $_SESSION['cart'] = array_values($_SESSION['cart']);
        
        $_SESSION['notification'] = [
            'type' => 'success',
            'message' => "🗑️ $product_name telah dihapus dari keranjang!"
        ];
    }
    header('Location: keranjang.php');
    exit;
}

// Proses UPDATE quantity
if(isset($_POST['update_cart'])) {
    $updated = false;
    foreach($_POST['quantity'] as $index => $qty) {
        if(isset($_SESSION['cart'][$index])) {
            $old_qty = $_SESSION['cart'][$index]['quantity'];
            $new_qty = (int)$qty;
            if($new_qty < 1) $new_qty = 1;
            
            if($old_qty != $new_qty) {
                $_SESSION['cart'][$index]['quantity'] = $new_qty;
                $updated = true;
            }
        }
    }
    
    if($updated) {
        $_SESSION['notification'] = [
            'type' => 'success',
            'message' => "🔄 Keranjang berhasil diperbarui!"
        ];
    }
    
    header('Location: keranjang.php');
    exit;
}

// Hitung total
$total = 0;
foreach($_SESSION['cart'] as $item) {
    $total += $item['harga'] * $item['quantity'];
}
?>

<style>
    .cart-page {
        padding: 60px 0;
        min-height: 60vh;
    }
    
    .cart-header {
        text-align: center;
        margin-bottom: 40px;
    }
    
    .cart-header h1 {
        font-size: 2.5rem;
        color: #333;
    }
    
    .cart-header i {
        color: #ff6b6b;
    }
    
    .cart-table {
        width: 100%;
        background: white;
        border-radius: 20px;
        overflow: hidden;
        box-shadow: 0 5px 20px rgba(0,0,0,0.05);
        margin-bottom: 20px;
    }
    
    .cart-table th {
        background: #f8f8f8;
        padding: 15px;
        text-align: left;
        font-weight: 600;
    }
    
    .cart-table td {
        padding: 15px;
        border-bottom: 1px solid #eee;
    }
    
    .cart-table input[type="number"] {
        width: 70px;
        padding: 8px;
        border: 1px solid #ddd;
        border-radius: 5px;
    }
    
    .btn-remove {
        color: #dc3545;
        background: none;
        border: none;
        cursor: pointer;
        font-size: 1.2rem;
        transition: all 0.3s;
    }
    
    .btn-remove:hover {
        color: #ff0000;
        transform: scale(1.1);
    }
    
    .btn-update {
        background: #ff6b6b;
        color: white;
        border: none;
        padding: 10px 20px;
        border-radius: 8px;
        cursor: pointer;
        transition: all 0.3s;
    }
    
    .btn-update:hover {
        background: #e84545;
        transform: translateY(-2px);
    }
    
    .btn-checkout {
        background: #4CAF50;
        color: white;
        padding: 15px 30px;
        text-decoration: none;
        border-radius: 8px;
        display: inline-block;
        transition: all 0.3s;
        font-weight: bold;
    }
    
    .btn-checkout:hover {
        background: #45a049;
        transform: translateY(-3px);
        box-shadow: 0 5px 15px rgba(76,175,80,0.3);
    }
    
    .cart-actions {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-top: 20px;
        flex-wrap: wrap;
        gap: 15px;
    }
    
    .total {
        font-size: 1.5rem;
        font-weight: bold;
        color: #ff6b6b;
        background: white;
        padding: 10px 25px;
        border-radius: 8px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.05);
    }
    
    .empty-cart {
        text-align: center;
        padding: 80px 20px;
        background: white;
        border-radius: 20px;
        box-shadow: 0 5px 20px rgba(0,0,0,0.05);
    }
    
    .empty-cart i {
        font-size: 5rem;
        color: #ddd;
        margin-bottom: 20px;
    }
    
    .empty-cart h2 {
        color: #333;
        margin-bottom: 10px;
    }
    
    .empty-cart p {
        color: #999;
    }
    
    .btn-shop {
        display: inline-block;
        padding: 12px 30px;
        background: #ff6b6b;
        color: white;
        text-decoration: none;
        border-radius: 8px;
        margin-top: 20px;
        transition: all 0.3s;
    }
    
    .btn-shop:hover {
        background: #e84545;
        transform: translateY(-2px);
    }
    
    .back-link {
        display: inline-block;
        margin-top: 20px;
    }
    
    .back-link a {
        color: #ff6b6b;
        text-decoration: none;
    }
    
    @media (max-width: 768px) {
        .cart-table {
            font-size: 0.9rem;
        }
        
        .cart-table th, .cart-table td {
            padding: 10px;
        }
        
        .cart-table input[type="number"] {
            width: 60px;
        }
        
        .cart-actions {
            flex-direction: column;
            align-items: stretch;
        }
        
        .total {
            text-align: center;
        }
    }
</style>

<div class="container">
    <div class="cart-page">
        <div class="cart-header">
            <h1><i class="fas fa-shopping-cart"></i> Keranjang Belanja</h1>
            <p>Review pesanan kamu sebelum checkout</p>
        </div>
        
        <?php if(empty($_SESSION['cart'])): ?>
            <div class="empty-cart">
                <i class="fas fa-shopping-cart"></i>
                <h2>Keranjang belanja masih kosong</h2>
                <p>Yuk, tambahkan kue favoritmu!</p>
                <a href="cakes.php" class="btn-shop">Mulai Belanja</a>
            </div>
        <?php else: ?>
            <form method="POST">
                <table class="cart-table">
                    <thead>
                        <tr>
                            <th>Produk</th>
                            <th>Harga</th>
                            <th>Jumlah</th>
                            <th>Subtotal</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($_SESSION['cart'] as $index => $item): 
                            $subtotal = $item['harga'] * $item['quantity'];
                        ?>
                        <tr>
                            <td><strong><?= htmlspecialchars($item['nama']) ?></strong></td>
                            <td>Rp <?= number_format($item['harga'], 0, ',', '.') ?></td>
                            <td>
                                <input type="number" name="quantity[<?= $index ?>]" value="<?= $item['quantity'] ?>" min="1">
                            </td>
                            <td><strong>Rp <?= number_format($subtotal, 0, ',', '.') ?></strong></td>
                            <td>
                                <a href="?remove=<?= $index ?>" class="btn-remove" onclick="return confirm('Hapus item ini?')">
                                    <i class="fas fa-trash"></i>
                                </a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                
                <div class="cart-actions">
                    <button type="submit" name="update_cart" class="btn-update">
                        <i class="fas fa-sync-alt"></i> Update Keranjang
                    </button>
                    <div class="total">
                        Total: Rp <?= number_format($total, 0, ',', '.') ?>
                    </div>
                </div>
            </form>
            
            <div style="text-align: right; margin-top: 30px;">
                <a href="checkout.php" class="btn-checkout">
                    <i class="fas fa-credit-card"></i> Lanjut ke Checkout
                </a>
            </div>
            
            <div class="back-link">
                <a href="cakes.php">
                    <i class="fas fa-arrow-left"></i> Lanjut Belanja
                </a>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php include 'includes/footer.php'; ?>