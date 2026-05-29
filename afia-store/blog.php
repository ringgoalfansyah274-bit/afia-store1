<?php
include 'includes/header.php';

// Ambil semua artikel blog (HANYA SEKALI)
$artikel = query("SELECT * FROM blog ORDER BY created_at DESC");
?>

<style>
    .blog-page {
        padding: 60px 0;
        background: #f9f9f9;
    }
    
    .blog-header {
        text-align: center;
        margin-bottom: 50px;
    }
    
    .blog-header h1 {
        font-size: 2.5rem;
        color: #333;
        margin-bottom: 15px;
    }
    
    .blog-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
        gap: 30px;
    }
    
    .blog-card {
        background: white;
        border-radius: 15px;
        overflow: hidden;
        box-shadow: 0 5px 20px rgba(0,0,0,0.05);
        transition: all 0.3s;
    }
    
    .blog-card:hover {
        transform: translateY(-10px);
        box-shadow: 0 15px 30px rgba(255,107,107,0.1);
    }
    
    .blog-image {
        height: 200px;
        overflow: hidden;
    }
    
    .blog-image img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }
    
    .blog-content {
        padding: 25px;
    }
    
    .blog-date {
        color: #999;
        font-size: 0.9rem;
        margin-bottom: 10px;
    }
    
    .blog-content h3 {
        margin-bottom: 15px;
        font-size: 1.3rem;
    }
    
    .blog-content p {
        color: #666;
        margin-bottom: 20px;
        line-height: 1.6;
    }
    
    .read-more {
        color: #ff6b6b;
        text-decoration: none;
        font-weight: 500;
        display: inline-flex;
        align-items: center;
        gap: 5px;
    }
    
    .read-more:hover {
        gap: 10px;
    }
</style>

<div class="blog-page">
    <div class="container">
        <div class="blog-header" data-aos="fade-up">
            <h1>Blog & Artikel</h1>
            <p>Tips, resep, dan berita seputar dunia kue</p>
        </div>
        
        <div class="blog-grid">
            <?php foreach($artikel as $post): ?>
            <div class="blog-card" data-aos="fade-up">
                <div class="blog-image">
                    <img src="https://images.unsplash.com/photo-1551024506-0bccd828d307?ixlib=rb-1.2.1&auto=format&fit=crop&w=500&q=80" alt="">
                </div>
                <div class="blog-content">
                    <div class="blog-date">
                        <i class="far fa-calendar"></i> 
                        <?= date('d M Y', strtotime($post['created_at'])) ?>
                    </div>
                    <h3><?= $post['judul'] ?></h3>
                    <p><?= substr($post['konten'], 0, 150) ?>...</p>
                    <a href="blog-detail.php?id=<?= $post['id'] ?>" class="read-more">
                        Baca Selengkapnya <i class="fas fa-arrow-right"></i>
                    </a>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>