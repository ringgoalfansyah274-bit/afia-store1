<?php
include 'includes/header.php';

// Ambil produk best seller
$best_sellers = query("SELECT * FROM products WHERE kategori='Best Seller' LIMIT 4");

// Ambil produk terbaru
$new_products = query("SELECT * FROM products ORDER BY id DESC LIMIT 4");
?>

<style>
    .hero {
        background: linear-gradient(rgba(0,0,0,0.5), rgba(0,0,0,0.5)), 
                    url('https://images.unsplash.com/photo-1464349095431-e921ac0f6cac?ixlib=rb-1.2.1&auto=format&fit=crop&w=1950&q=80');
        background-size: cover;
        background-position: center;
        height: 500px;
        display: flex;
        align-items: center;
        justify-content: center;
        text-align: center;
        color: white;
    }
    
    .hero-content {
        max-width: 800px;
        padding: 20px;
    }
    
    .hero h2 {
        font-size: 3rem;
        margin-bottom: 20px;
    }
    
    .hero p {
        font-size: 1.2rem;
        margin-bottom: 30px;
    }
    
    .btn {
        display: inline-block;
        padding: 15px 40px;
        background: linear-gradient(135deg, #ff6b6b, #ff8e8e);
        color: white;
        text-decoration: none;
        border-radius: 50px;
        font-weight: bold;
    }
    
    .container {
        max-width: 1200px;
        margin: 0 auto;
        padding: 0 20px;
    }
    
    .section-title {
        text-align: center;
        font-size: 2.5rem;
        margin: 60px 0 20px;
        color: #333;
    }
    
    .section-subtitle {
        text-align: center;
        color: #666;
        margin-bottom: 40px;
    }
    
    /* SEARCH SECTION */
    .search-section {
        background: white;
        padding: 40px 20px;
        border-radius: 20px;
        box-shadow: 0 5px 30px rgba(0,0,0,0.05);
        margin: 40px 0;
        text-align: center;
    }
    
    .search-section h3 {
        font-size: 1.8rem;
        margin-bottom: 20px;
        color: #333;
    }
    
    .search-box {
        display: flex;
        max-width: 600px;
        margin: 0 auto;
        gap: 10px;
    }
    
    .search-box input {
        flex: 1;
        padding: 15px 20px;
        border: 2px solid #eee;
        border-radius: 10px;
        font-size: 1rem;
        transition: all 0.3s;
    }
    
    .search-box input:focus {
        outline: none;
        border-color: #ff6b6b;
        box-shadow: 0 0 0 4px rgba(255,107,107,0.1);
    }
    
    .search-box button {
        padding: 15px 30px;
        background: linear-gradient(135deg, #ff6b6b, #ff8e8e);
        color: white;
        border: none;
        border-radius: 10px;
        font-size: 1rem;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.3s;
    }
    
    .search-box button:hover {
        transform: translateY(-2px);
        box-shadow: 0 10px 20px rgba(255,107,107,0.3);
    }
    
    .popular-searches {
        margin-top: 20px;
    }
    
    .popular-searches span {
        color: #666;
        margin-right: 10px;
    }
    
    .popular-searches a {
        display: inline-block;
        padding: 5px 15px;
        background: #f5f5f5;
        color: #333;
        text-decoration: none;
        border-radius: 20px;
        margin: 5px;
        font-size: 0.9rem;
        transition: all 0.3s;
    }
    
    .popular-searches a:hover {
        background: #ff6b6b;
        color: white;
    }
    
    /* FEATURED BUTTONS */
    .featured-buttons {
        padding: 40px 0;
        background: linear-gradient(135deg, #f8f9fa, #ffffff);
    }
    
    .buttons-grid {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 30px;
        max-width: 1000px;
        margin: 0 auto;
    }
    
    .feature-btn {
        display: flex;
        align-items: center;
        gap: 20px;
        background: white;
        padding: 25px 30px;
        border-radius: 20px;
        text-decoration: none;
        box-shadow: 0 10px 30px rgba(0,0,0,0.05);
        transition: all 0.3s ease;
        border: 2px solid transparent;
    }
    
    .feature-btn:hover {
        transform: translateY(-10px);
        box-shadow: 0 20px 40px rgba(255,107,107,0.15);
        border-color: #ff6b6b;
    }
    
    .feature-btn .btn-icon {
        width: 70px;
        height: 70px;
        background: linear-gradient(135deg, #ff6b6b, #ff8e8e);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 2rem;
        color: white;
        transition: all 0.3s;
        box-shadow: 0 10px 20px rgba(255,107,107,0.2);
    }
    
    .feature-btn:hover .btn-icon {
        transform: scale(1.1) rotate(5deg);
        box-shadow: 0 15px 30px rgba(255,107,107,0.3);
    }
    
    .feature-btn .btn-text {
        flex: 1;
    }
    
    .feature-btn .btn-title {
        display: block;
        font-size: 1.3rem;
        font-weight: 700;
        color: #333;
        margin-bottom: 5px;
        transition: color 0.3s ease;
    }
    
    .feature-btn:hover .btn-title {
        color: #ff6b6b;
    }
    
    .feature-btn .btn-desc {
        display: block;
        font-size: 0.9rem;
        color: #666;
    }
    
    .feature-btn .btn-arrow {
        width: 40px;
        height: 40px;
        background: #f5f5f5;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #ff6b6b;
        font-size: 1.2rem;
        transition: all 0.3s;
    }
    
    .feature-btn:hover .btn-arrow {
        background: #ff6b6b;
        color: white;
        transform: translateX(5px);
    }
    
    .birthday-btn .btn-icon {
        background: linear-gradient(135deg, #FF9800, #FFC107);
    }
    
    .quiz-btn .btn-icon {
        background: linear-gradient(135deg, #9C27B0, #E040FB);
    }
    
    /* PRODUCT GRID */
    .product-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 30px;
        margin: 40px 0;
    }
    
    .product-card {
        background: white;
        border-radius: 15px;
        overflow: hidden;
        box-shadow: 0 5px 20px rgba(0,0,0,0.05);
        transition: all 0.3s;
    }
    
    .product-card:hover {
        transform: translateY(-10px);
        box-shadow: 0 15px 30px rgba(255,107,107,0.15);
    }
    
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
    
    .product-card:hover .product-image img {
        transform: scale(1.1);
    }
    
    .product-info {
        padding: 20px;
    }
    
    .product-info h3 {
        margin-bottom: 10px;
    }
    
    .product-info p {
        color: #666;
        font-size: 0.9rem;
        margin-bottom: 10px;
    }
    
    .price {
        color: #ff6b6b;
        font-weight: bold;
        font-size: 1.2rem;
        margin: 10px 0;
    }
    
    .btn-add {
        width: 100%;
        padding: 10px;
        background: #ff6b6b;
        color: white;
        border: none;
        border-radius: 5px;
        cursor: pointer;
        transition: all 0.3s;
    }
    
    .btn-add:hover {
        background: #ff5252;
    }
    
    /* FEATURES */
    .features {
        background: linear-gradient(135deg, #ff6b6b, #ff8e8e);
        color: white;
        padding: 60px 0;
        margin-top: 60px;
    }
    
    .features-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 40px;
        text-align: center;
    }
    
    .feature-item i {
        font-size: 3rem;
        margin-bottom: 20px;
    }
    
    @media (max-width: 768px) {
        .hero h2 {
            font-size: 2rem;
        }
        
        .search-box {
            flex-direction: column;
        }
        
        .buttons-grid {
            grid-template-columns: 1fr;
            gap: 20px;
        }
        
        .feature-btn {
            padding: 20px;
        }
        
        .feature-btn .btn-icon {
            width: 60px;
            height: 60px;
            font-size: 1.5rem;
        }

        .product-card.disabled {
    opacity: 0.6;
    filter: grayscale(0.3);
}
.product-card.disabled .btn-add {
    background: #ccc;
    cursor: not-allowed;
}
.product-card.disabled .btn-add:hover {
    transform: none;
    box-shadow: none;
    }
</style>

<!-- Hero Section -->
<section class="hero">
    <div class="hero-content">
        <h2>Delicious Cakes for Every Occasion</h2>
        <p>Dari ulang tahun hingga pernikahan, kami punya kue spesial untukmu</p>
        <a href="cakes.php" class="btn">Shop Now</a>
    </div>
</section>

<!-- Birthday & Quiz Buttons -->
<div class="featured-buttons">
    <div class="container">
        <div class="buttons-grid">
            <a href="birthday.php" class="feature-btn birthday-btn">
                <div class="btn-icon"><i class="fas fa-birthday-cake"></i></div>
                <div class="btn-text">
                    <span class="btn-title">Birthday Club</span>
                    <span class="btn-desc">Dapatkan diskon spesial di hari ulang tahunmu!</span>
                </div>
                <div class="btn-arrow"><i class="fas fa-arrow-right"></i></div>
            </a>
            <a href="cake-quiz.php" class="feature-btn quiz-btn">
                <div class="btn-icon"><i class="fas fa-question-circle"></i></div>
                <div class="btn-text">
                    <span class="btn-title">Cake Quiz</span>
                    <span class="btn-desc">Temukan rekomendasi kue yang cocok untukmu!</span>
                </div>
                <div class="btn-arrow"><i class="fas fa-arrow-right"></i></div>
            </a>
        </div>
    </div>
</div>

<div class="container">
    <!-- Search Section -->
    <div class="search-section">
        <h3>🔍 Cari Kue Favoritmu</h3>
        <form action="cakes.php" method="GET" class="search-box">
            <input type="text" name="search" placeholder="Masukkan nama kue... (contoh: chocolate, strawberry)" required>
            <button type="submit"><i class="fas fa-search"></i> Cari</button>
        </form>
        <div class="popular-searches">
            <span>Populer:</span>
            <a href="cakes.php?search=chocolate">Chocolate</a>
            <a href="cakes.php?search=strawberry">Strawberry</a>
            <a href="cakes.php?search=rainbow">Rainbow</a>
            <a href="cakes.php?search=red velvet">Red Velvet</a>
            <a href="cakes.php?search=cheese">Cheese</a>
        </div>
    </div>
    
    <!-- BEST SELLERS -->
    <h2 class="section-title">Best Sellers</h2>
    <p class="section-subtitle">Paling laris dan favorit pelanggan</p>
    
    <div class="product-grid">
        <?php foreach($best_sellers as $p): ?>
        <div class="product-card">
            <div class="product-image">
                <img src="<?= !empty($p['gambar']) ? 'uploads/products/'.$p['gambar'] : 'https://images.unsplash.com/photo-1578985545062-69928b1d9587?ixlib=rb-1.2.1&auto=format&fit=crop&w=500&q=80' ?>" 
                     alt="<?= $p['nama_produk'] ?>">
            </div>
            <div class="product-info">
                <h3><?= $p['nama_produk'] ?></h3>
                <p><?= substr($p['deskripsi'], 0, 50) ?>...</p>
                <div class="price">Rp <?= number_format($p['harga'], 0, ',', '.') ?></div>
                <form method="POST" action="keranjang.php">
                    <input type="hidden" name="product_id" value="<?= $p['id'] ?>">
                    <input type="hidden" name="quantity" value="1">
                    <button type="submit" name="add_to_cart" class="btn-add">+ Tambah ke Keranjang</button>
                </form>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    
    <!-- PRODUK TERBARU -->
    <h2 class="section-title">Produk Terbaru</h2>
    <p class="section-subtitle">Kreasi terbaru dari kami</p>
    
    <div class="product-grid">
        <?php foreach($new_products as $p): ?>
        <div class="product-card">
            <div class="product-image">
                <img src="<?= !empty($p['gambar']) ? 'uploads/products/'.$p['gambar'] : 'https://images.unsplash.com/photo-1578985545062-69928b1d9587?ixlib=rb-1.2.1&auto=format&fit=crop&w=500&q=80' ?>" 
                     alt="<?= $p['nama_produk'] ?>">
            </div>
            <div class="product-info">
                <h3><?= $p['nama_produk'] ?></h3>
                <p><?= substr($p['deskripsi'], 0, 50) ?>...</p>
                <div class="price">Rp <?= number_format($p['harga'], 0, ',', '.') ?></div>
                <form method="POST" action="keranjang.php">
                    <input type="hidden" name="product_id" value="<?= $p['id'] ?>">
                    <input type="hidden" name="quantity" value="1">
                    <button type="submit" name="add_to_cart" class="btn-add">+ Tambah ke Keranjang</button>
                </form>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</div>

<!-- Features Section -->
<section class="features">
    <div class="container">
        <div class="features-grid">
            <div class="feature-item"><i class="fas fa-truck"></i><h4>Free Delivery</h4><p>Gratis ongkir area Jakarta</p></div>
            <div class="feature-item"><i class="fas fa-clock"></i><h4>24/7 Service</h4><p>Pesan kapan saja</p></div>
            <div class="feature-item"><i class="fas fa-leaf"></i><h4>Fresh Ingredients</h4><p>Bahan berkualitas</p></div>
            <div class="feature-item"><i class="fas fa-certificate"></i><h4>Certified Baker</h4><p>Chef berpengalaman</p></div>
        </div>
    </div>
</section>

<?php include 'includes/footer.php'; ?>