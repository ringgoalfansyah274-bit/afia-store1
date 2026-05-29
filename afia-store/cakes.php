<?php
include 'includes/header.php';

// Inisialisasi wishlist dari session
$wishlist = isset($_SESSION['wishlist']) ? $_SESSION['wishlist'] : [];

// Ambil semua kategori
$categories = query("SELECT * FROM categories ORDER BY nama_kategori");

// Ambil parameter filter
$kategori_terpilih = isset($_GET['kategori']) ? $_GET['kategori'] : '';
$search = isset($_GET['search']) ? $_GET['search'] : '';

// Query produk
$query = "SELECT * FROM products WHERE 1=1";
if($kategori_terpilih) {
    $query .= " AND kategori = '$kategori_terpilih'";
}
if($search) {
    $query .= " AND (nama_produk LIKE '%$search%' OR deskripsi LIKE '%$search%')";
}
$query .= " ORDER BY 
            CASE WHEN kategori = 'Best Seller' THEN 1 ELSE 2 END, 
            created_at DESC";

$products = query($query);
?>

<style>
    :root {
        --primary: #e84545;
        --primary-dark: #d43a3a;
        --primary-light: #ff6b6b;
        --text-dark: #333;
        --text-light: #666;
        --text-lighter: #999;
        --bg-light: #f8f9fa;
        --white: #fff;
        --shadow: 0 10px 30px rgba(0,0,0,0.05);
        --shadow-hover: 0 20px 40px rgba(232,69,69,0.15);
    }

    * { margin: 0; padding: 0; box-sizing: border-box; }
    body { font-family: 'Poppins', sans-serif; background: var(--bg-light); color: var(--text-dark); }
    .container { max-width: 1200px; margin: 0 auto; padding: 0 20px; }

    /* HEADER */
    .page-header {
        text-align: center;
        padding: 60px 0 30px;
        background: linear-gradient(135deg, rgba(232,69,69,0.05), rgba(255,107,107,0.05));
        margin-bottom: 40px;
    }
    .page-title {
        font-size: 2.8rem;
        font-weight: 700;
        margin-bottom: 15px;
        background: linear-gradient(135deg, var(--primary), var(--primary-light));
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        animation: fadeInUp 0.8s ease;
    }
    .page-subtitle {
        color: var(--text-light);
        font-size: 1.1rem;
        max-width: 600px;
        margin: 0 auto;
        animation: fadeInUp 1s ease;
    }

    /* KATEGORI */
    .categories-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
        gap: 20px;
        margin: 40px 0;
    }
    .category-card {
        background: var(--white);
        padding: 25px 20px;
        border-radius: 15px;
        text-align: center;
        text-decoration: none;
        color: var(--text-dark);
        box-shadow: var(--shadow);
        transition: all 0.3s;
        border: 2px solid transparent;
    }
    .category-card:hover {
        transform: translateY(-5px);
        box-shadow: var(--shadow-hover);
        border-color: var(--primary);
    }
    .category-card.active {
        border-color: var(--primary);
        background: linear-gradient(135deg, #fff, #fff0f0);
    }
    .category-icon {
        width: 70px;
        height: 70px;
        background: linear-gradient(135deg, rgba(232,69,69,0.1), rgba(255,107,107,0.1));
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 15px;
        font-size: 1.8rem;
        color: var(--primary);
        transition: all 0.3s;
    }
    .category-card:hover .category-icon {
        background: linear-gradient(135deg, var(--primary), var(--primary-light));
        color: var(--white);
        transform: scale(1.1) rotate(5deg);
    }

    /* SEARCH */
    .search-section {
        background: var(--white);
        padding: 30px;
        border-radius: 20px;
        box-shadow: var(--shadow);
        margin-bottom: 30px;
    }
    .search-box {
        display: flex;
        gap: 15px;
        max-width: 700px;
        margin: 0 auto;
    }
    .search-box input {
        flex: 1;
        padding: 15px 20px;
        border: 2px solid #eee;
        border-radius: 12px;
        font-size: 1rem;
        transition: all 0.3s;
    }
    .search-box input:focus {
        outline: none;
        border-color: var(--primary);
        box-shadow: 0 0 0 4px rgba(232,69,69,0.15);
    }
    .search-box button {
        padding: 15px 35px;
        background: linear-gradient(135deg, var(--primary), var(--primary-light));
        color: white;
        border: none;
        border-radius: 12px;
        font-weight: 600;
        cursor: pointer;
    }
    .search-box button:hover {
        transform: translateY(-2px);
        box-shadow: 0 10px 20px rgba(232,69,69,0.3);
    }

    /* FILTER INFO */
    .filter-info {
        background: linear-gradient(135deg, var(--primary), var(--primary-light));
        color: white;
        padding: 15px 25px;
        border-radius: 12px;
        margin-bottom: 30px;
        display: inline-flex;
        align-items: center;
        gap: 15px;
        flex-wrap: wrap;
    }
    .clear-filter {
        color: white;
        background: rgba(255,255,255,0.2);
        padding: 5px 10px;
        border-radius: 20px;
        text-decoration: none;
    }

    /* PRODUCT GRID */
    .product-grid {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 25px;
        margin: 30px 0 50px;
    }
    .product-card {
        background: white;
        border-radius: 20px;
        overflow: hidden;
        box-shadow: var(--shadow);
        transition: all 0.3s;
        position: relative;
        display: flex;
        flex-direction: column;
    }
    .product-card:hover {
        transform: translateY(-10px);
        box-shadow: var(--shadow-hover);
    }
    .product-badge {
        position: absolute;
        top: 15px;
        left: 15px;
        background: linear-gradient(135deg, var(--primary), var(--primary-light));
        color: white;
        padding: 5px 15px;
        border-radius: 25px;
        font-size: 0.8rem;
        font-weight: 600;
        z-index: 2;
    }
    .product-badge.stok-habis { background: var(--text-lighter); }
    .product-image {
        height: 200px;
        overflow: hidden;
    }
    .product-image img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        transition: transform 0.5s;
    }
    .product-card:hover .product-image img { transform: scale(1.1); }
    .product-info {
        padding: 20px;
        flex: 1;
        display: flex;
        flex-direction: column;
    }
    .product-category {
        color: var(--primary);
        font-size: 0.8rem;
        text-transform: uppercase;
        letter-spacing: 1px;
        margin-bottom: 8px;
    }
    .product-name {
        font-size: 1.2rem;
        font-weight: 600;
        margin-bottom: 10px;
    }
    .product-description {
        color: var(--text-light);
        font-size: 0.9rem;
        margin-bottom: 15px;
        flex: 1;
    }
    .product-price {
        font-size: 1.3rem;
        font-weight: 700;
        color: var(--primary);
        margin-bottom: 15px;
    }
    .product-meta {
        display: flex;
        gap: 15px;
        margin-bottom: 15px;
        padding: 10px 0;
        border-top: 1px solid #eee;
        border-bottom: 1px solid #eee;
        font-size: 0.85rem;
        flex-wrap: wrap;
    }
    .product-meta span { display: flex; align-items: center; gap: 5px; }
    .product-meta i { color: var(--primary); }

    /* BUTTONS */
    .action-buttons {
        display: flex;
        gap: 10px;
        margin-top: 15px;
    }
    .btn-add {
        flex: 3;
        padding: 12px;
        background: linear-gradient(135deg, var(--primary), var(--primary-light));
        color: white;
        border: none;
        border-radius: 8px;
        cursor: pointer;
        transition: all 0.3s;
    }
    .btn-add:hover { transform: translateY(-2px); box-shadow: 0 10px 20px rgba(232,69,69,0.3); }
    .btn-add.disabled { background: #ccc; cursor: not-allowed; }
    .btn-wishlist {
        flex: 1;
        display: flex;
        align-items: center;
        justify-content: center;
        background: #f5f5f5;
        border-radius: 8px;
        color: var(--primary);
        text-decoration: none;
        transition: all 0.3s;
        height: 42px;
    }
    .btn-wishlist:hover { background: var(--primary); color: white; transform: scale(1.05); }
    .btn-wishlist.active { background: var(--primary); color: white; }

    /* RESPONSIVE */
    @media (max-width: 1024px) { .product-grid { grid-template-columns: repeat(3, 1fr); } }
    @media (max-width: 768px) {
        .product-grid { grid-template-columns: repeat(2, 1fr); }
        .page-title { font-size: 2rem; }
        .categories-grid { grid-template-columns: repeat(2, 1fr); }
        .search-box { flex-direction: column; }
    }
    @media (max-width: 480px) {
        .product-grid { grid-template-columns: 1fr; }
        .categories-grid { grid-template-columns: 1fr; }
    }
    @keyframes fadeInUp {
        from { opacity: 0; transform: translateY(30px); }
        to { opacity: 1; transform: translateY(0); }
    }
</style>

<div class="page-header">
    <div class="container">
        <h1 class="page-title">Our Cakes</h1>
        <p class="page-subtitle">Temukan berbagai macam kue lezat untuk momen spesialmu</p>
    </div>
</div>

<div class="container">
    <!-- Categories Grid -->
    <div class="categories-grid">
        <a href="cakes.php" class="category-card <?= !$kategori_terpilih ? 'active' : '' ?>">
            <div class="category-icon"><i class="fas fa-utensils"></i></div>
            <h3>Semua Kue</h3>
            <p>Lihat semua koleksi</p>
        </a>
        <?php foreach($categories as $cat): ?>
        <a href="?kategori=<?= urlencode($cat['nama_kategori']) ?>" 
           class="category-card <?= $kategori_terpilih == $cat['nama_kategori'] ? 'active' : '' ?>">
            <div class="category-icon"><i class="fas <?= strpos($cat['nama_kategori'],'Birthday')!==false ? 'fa-birthday-cake' : (strpos($cat['nama_kategori'],'Wedding')!==false ? 'fa-heart' : (strpos($cat['nama_kategori'],'Cupcake')!==false ? 'fa-cookie-bite' : 'fa-pencil-ruler')) ?>"></i></div>
            <h3><?= $cat['nama_kategori'] ?></h3>
            <p><?= $cat['deskripsi'] ?? 'Kue lezat untuk acara spesial' ?></p>
        </a>
        <?php endforeach; ?>
    </div>

    <!-- Search -->
    <div class="search-section">
        <form method="GET" class="search-box">
            <?php if($kategori_terpilih): ?>
                <input type="hidden" name="kategori" value="<?= htmlspecialchars($kategori_terpilih) ?>">
            <?php endif; ?>
            <input type="text" name="search" placeholder="Cari kue favoritmu..." value="<?= htmlspecialchars($search) ?>">
            <button type="submit"><i class="fas fa-search"></i> Cari</button>
        </form>
    </div>

    <!-- Filter Info -->
    <?php if($kategori_terpilih || $search): ?>
    <div class="filter-info">
        <i class="fas fa-filter"></i>
        <span>Filter aktif: 
            <?php if($kategori_terpilih): ?> Kategori <strong><?= $kategori_terpilih ?></strong><?php endif; ?>
            <?php if($search): ?> Pencarian "<strong><?= $search ?></strong>"<?php endif; ?>
        </span>
        <a href="cakes.php" class="clear-filter"><i class="fas fa-times"></i> Hapus</a>
    </div>
    <?php endif; ?>

    <!-- Products -->
    <?php if(empty($products)): ?>
        <div class="empty-state" style="text-align:center; padding:80px; background:white; border-radius:20px;">
            <i class="fas fa-search" style="font-size:5rem; color:#ddd;"></i>
            <h3>Produk Tidak Ditemukan</h3>
            <a href="cakes.php" class="btn-back" style="display:inline-block; padding:12px 30px; background:#ff6b6b; color:white; border-radius:10px; margin-top:20px;">Lihat Semua Produk</a>
        </div>
    <?php else: ?>
        <div class="section-header" style="display:flex; justify-content:space-between; margin-bottom:30px;">
            <h2><?= $kategori_terpilih ?: 'Semua Produk' ?></h2>
            <div class="product-count"><?= count($products) ?> produk ditemukan</div>
        </div>

        <div class="product-grid">
            <?php foreach($products as $p): 
                $in_wishlist = in_array($p['id'], $wishlist);
            ?>
            <div class="product-card">
                <?php if($p['kategori'] == 'Best Seller'): ?>
                    <div class="product-badge">Best Seller</div>
                <?php elseif($p['stok'] <= 0): ?>
                    <div class="product-badge stok-habis">Stok Habis</div>
                <?php endif; ?>
                
                <div class="product-image">
                    <img src="<?= !empty($p['gambar']) ? 'uploads/products/'.$p['gambar'] : 'https://images.unsplash.com/photo-1578985545062-69928b1d9587?ixlib=rb-1.2.1&auto=format&fit=crop&w=500&q=80' ?>" 
                         alt="<?= htmlspecialchars($p['nama_produk']) ?>">
                </div>
                
                <div class="product-info">
                    <div class="product-category"><?= $p['kategori'] ?></div>
                    <h3 class="product-name"><?= $p['nama_produk'] ?></h3>
                    <p class="product-description"><?= substr($p['deskripsi'], 0, 70) ?>...</p>
                    <div class="product-price">Rp <?= number_format($p['harga'], 0, ',', '.') ?></div>
                    
                    <div class="product-meta">
                        <span><i class="fas fa-box"></i> Stok: <?= $p['stok'] ?>
                            <?php if($p['stok'] <= 5 && $p['stok'] > 0): ?>
                                <span style="color:#ff6b6b;"> (Sisa <?= $p['stok'] ?> pcs!)</span>
                            <?php elseif($p['stok'] <= 0): ?>
                                <span style="color:#dc3545;"> (Habis)</span>
                            <?php endif; ?>
                        </span>
                        <span><i class="fas fa-weight"></i> <?= $p['berat'] ?>gr</span>
                    </div>
                    
                    <div class="action-buttons">
                        <?php if($p['stok'] > 0): ?>
                        <form method="POST" action="keranjang.php" style="flex:3;">
                            <input type="hidden" name="product_id" value="<?= $p['id'] ?>">
                            <input type="hidden" name="quantity" value="1">
                            <button type="submit" name="add_to_cart" class="btn-add">
                                <i class="fas fa-shopping-cart"></i>
                            </button>
                        </form>
                        <?php else: ?>
                        <button class="btn-add disabled" style="flex:3;" disabled>
                            <i class="fas fa-times"></i> Stok Habis
                        </button>
                        <?php endif; ?>
                        
                        <a href="wishlist.php?add=<?= $p['id'] ?>" 
                           class="btn-wishlist <?= $in_wishlist ? 'active' : '' ?>">
                            <i class="fas fa-heart"></i>
                        </a>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>

        <?php if($kategori_terpilih || $search): ?>
        <div class="view-all" style="text-align:center; margin:50px 0;">
            <a href="cakes.php" style="display:inline-flex; align-items:center; gap:10px; padding:15px 40px; border:2px solid #e84545; border-radius:50px; color:#e84545; text-decoration:none;">Lihat Semua Produk <i class="fas fa-arrow-right"></i></a>
        </div>
        <?php endif; ?>
    <?php endif; ?>
</div>

<?php include 'includes/footer.php'; ?>