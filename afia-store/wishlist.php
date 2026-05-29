<?php
include 'includes/header.php';

// Inisialisasi session wishlist
if(!isset($_SESSION['wishlist'])) {
    $_SESSION['wishlist'] = [];
}

// Variabel notifikasi
$notification = '';
$notification_type = '';

// TAMBAH KE WISHLIST (mendukung ?add=1)
if(isset($_GET['add'])) {
    $id = $_GET['add'];
    if(!in_array($id, $_SESSION['wishlist'])) {
        $_SESSION['wishlist'][] = $id;
        $_SESSION['notification'] = [
            'type' => 'success',
            'message' => '✅ Produk ditambahkan ke wishlist!'
        ];
    } else {
        $_SESSION['notification'] = [
            'type' => 'error',
            'message' => '❌ Produk sudah ada di wishlist!'
        ];
    }
    header('Location: wishlist.php');
    exit;
}

// HAPUS DARI WISHLIST
if(isset($_GET['remove'])) {
    $key = array_search($_GET['remove'], $_SESSION['wishlist']);
    if($key !== false) {
        unset($_SESSION['wishlist'][$key]);
        $_SESSION['wishlist'] = array_values($_SESSION['wishlist']);
        $_SESSION['notification'] = [
            'type' => 'success',
            'message' => '🗑️ Produk dihapus dari wishlist!'
        ];
    }
    header('Location: wishlist.php');
    exit;
}

// Ambil notifikasi
if(isset($_SESSION['notification'])) {
    $notification = $_SESSION['notification']['message'];
    $notification_type = $_SESSION['notification']['type'];
    unset($_SESSION['notification']);
}

// Ambil produk wishlist
$wishlist_items = [];
if(!empty($_SESSION['wishlist'])) {
    $ids = implode(',', $_SESSION['wishlist']);
    $wishlist_items = query("SELECT * FROM products WHERE id IN ($ids)");
}
?>

<style>
    .wishlist-page {
        padding: 60px 0;
        min-height: 60vh;
    }
    
    .wishlist-header {
        text-align: center;
        margin-bottom: 40px;
    }
    
    .wishlist-header h1 {
        font-size: 2.5rem;
        color: #333;
    }
    
    .wishlist-header i {
        color: #ff6b6b;
    }
    
    .wishlist-grid {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 25px;
        margin-top: 30px;
    }
    
    .wishlist-card {
        background: white;
        border-radius: 20px;
        overflow: hidden;
        box-shadow: 0 5px 20px rgba(0,0,0,0.05);
        transition: all 0.3s;
    }
    
    .wishlist-card:hover {
        transform: translateY(-10px);
        box-shadow: 0 15px 30px rgba(255,107,107,0.15);
    }
    
    .wishlist-image {
        height: 200px;
        overflow: hidden;
    }
    
    .wishlist-image img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        transition: transform 0.5s;
    }
    
    .wishlist-card:hover .wishlist-image img {
        transform: scale(1.1);
    }
    
    .wishlist-info {
        padding: 20px;
    }
    
    .wishlist-info h3 {
        font-size: 1.1rem;
        margin-bottom: 10px;
    }
    
    .wishlist-price {
        color: #ff6b6b;
        font-weight: bold;
        font-size: 1.2rem;
        margin: 10px 0;
    }
    
    .wishlist-actions {
        display: flex;
        gap: 10px;
        margin-top: 15px;
    }
    
    .btn-cart {
        flex: 2;
        background: #ff6b6b;
        color: white;
        border: none;
        padding: 10px;
        border-radius: 8px;
        cursor: pointer;
        text-align: center;
        text-decoration: none;
        transition: all 0.3s;
    }
    
    .btn-cart:hover {
        background: #e84545;
        transform: translateY(-2px);
    }
    
    .btn-remove-wishlist {
        flex: 1;
        background: #f5f5f5;
        color: #dc3545;
        border: none;
        padding: 10px;
        border-radius: 8px;
        cursor: pointer;
        text-align: center;
        text-decoration: none;
        transition: all 0.3s;
    }
    
    .btn-remove-wishlist:hover {
        background: #dc3545;
        color: white;
        transform: translateY(-2px);
    }
    
    .empty-wishlist {
        text-align: center;
        padding: 80px 20px;
        background: white;
        border-radius: 20px;
        box-shadow: 0 5px 20px rgba(0,0,0,0.05);
    }
    
    .empty-wishlist i {
        font-size: 5rem;
        color: #ddd;
        margin-bottom: 20px;
    }
    
    .empty-wishlist h2 {
        color: #333;
        margin-bottom: 10px;
    }
    
    .empty-wishlist p {
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
    
    @media (max-width: 1024px) {
        .wishlist-grid {
            grid-template-columns: repeat(3, 1fr);
        }
    }
    
    @media (max-width: 768px) {
        .wishlist-grid {
            grid-template-columns: repeat(2, 1fr);
            gap: 15px;
        }
        
        .wishlist-header h1 {
            font-size: 2rem;
        }
        
        .wishlist-image {
            height: 180px;
        }
    }
    
    @media (max-width: 480px) {
        .wishlist-grid {
            grid-template-columns: 1fr;
        }
    }
</style>

<div class="container">
    <div class="wishlist-page">
        <div class="wishlist-header">
            <h1><i class="fas fa-heart" style="color: #ff6b6b;"></i> Wishlist Saya</h1>
            <p>Produk favorit yang kamu simpan</p>
        </div>
        
        <?php if(empty($wishlist_items)): ?>
            <div class="empty-wishlist">
                <i class="far fa-heart"></i>
                <h2>Wishlist masih kosong</h2>
                <p>Tambahkan produk favoritmu dengan mengklik ikon hati di halaman produk</p>
                <a href="cakes.php" class="btn-shop">Lihat Produk</a>
            </div>
        <?php else: ?>
            <div class="wishlist-grid">
                <?php foreach($wishlist_items as $item): ?>
                <div class="wishlist-card">
                    <div class="wishlist-image">
                        <img src="<?= !empty($item['gambar']) ? 'uploads/products/'.$item['gambar'] : 'https://images.unsplash.com/photo-1578985545062-69928b1d9587?ixlib=rb-1.2.1&auto=format&fit=crop&w=500&q=80' ?>" 
                             alt="<?= htmlspecialchars($item['nama_produk']) ?>">
                    </div>
                    
                    <div class="wishlist-info">
                        <h3><?= htmlspecialchars($item['nama_produk']) ?></h3>
                        <div class="wishlist-price">Rp <?= number_format($item['harga'], 0, ',', '.') ?></div>
                        
                        <div class="wishlist-actions">
                            <form method="POST" action="keranjang.php" style="flex: 2;">
                                <input type="hidden" name="product_id" value="<?= $item['id'] ?>">
                                <input type="hidden" name="quantity" value="1">
                                <button type="submit" name="add_to_cart" class="btn-cart">
                                    <i class="fas fa-shopping-cart"></i> Keranjang
                                </button>
                            </form>
                            <a href="?remove=<?= $item['id'] ?>" class="btn-remove-wishlist" onclick="return confirm('Hapus dari wishlist?')">
                                <i class="fas fa-trash"></i> Hapus
                            </a>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
<?php ob_end_flush(); ?>