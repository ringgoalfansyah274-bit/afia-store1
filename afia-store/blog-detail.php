<?php
include 'includes/header.php';

// Ambil ID dari URL
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if($id == 0) {
    header('Location: blog.php');
    exit;
}

// Ambil data artikel berdasarkan ID (HANYA SATU)
$artikel = query("SELECT * FROM blog WHERE id = $id");

if(empty($artikel)) {
    header('Location: blog.php');
    exit;
}

$post = $artikel[0]; // Ambil artikel pertama (karena hanya satu)
?>

<style>
    .blog-detail {
        padding: 60px 0;
        background: #f8f9fa;
    }
    
    .blog-container {
        max-width: 800px;
        margin: 0 auto;
        background: white;
        padding: 40px;
        border-radius: 20px;
        box-shadow: 0 10px 30px rgba(0,0,0,0.05);
    }
    
    .blog-header {
        text-align: center;
        margin-bottom: 40px;
    }
    
    .blog-header h1 {
        font-size: 2.5rem;
        color: #333;
        margin-bottom: 15px;
    }
    
    .blog-meta {
        color: #666;
        font-size: 0.9rem;
    }
    
    .blog-meta i {
        color: #ff6b6b;
        margin-right: 5px;
    }
    
    .blog-content {
        line-height: 1.8;
        color: #333;
        font-size: 1.1rem;
    }
    
    .blog-content p {
        margin-bottom: 20px;
    }
    
    .back-link {
        display: inline-block;
        margin-top: 30px;
        color: #ff6b6b;
        text-decoration: none;
    }
    
    .back-link i {
        margin-right: 5px;
    }
</style>

<div class="blog-detail">
    <div class="container">
        <div class="blog-container">
            <div class="blog-header">
                <h1><?= htmlspecialchars($post['judul']) ?></h1>
                <div class="blog-meta">
                    <i class="fas fa-calendar"></i> <?= date('d F Y', strtotime($post['created_at'])) ?>
                </div>
            </div>
            
            <div class="blog-content">
                <?= nl2br(htmlspecialchars($post['konten'])) ?>
            </div>
            
            <a href="blog.php" class="back-link">
                <i class="fas fa-arrow-left"></i> Kembali ke Blog
            </a>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>