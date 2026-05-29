<?php
include 'includes/header.php';

$faq = query("SELECT * FROM faq ORDER BY urutan");
?>
<style>
    .faq-page {
        padding: 60px 0;
        background: white;
    }
    
    .faq-header {
        text-align: center;
        margin-bottom: 50px;
    }
    
    .faq-header h1 {
        font-size: 2.5rem;
        color: #333;
        margin-bottom: 15px;
    }
    
    .faq-container {
        max-width: 800px;
        margin: 0 auto;
    }
    
    .faq-item {
        background: #f9f9f9;
        border-radius: 10px;
        margin-bottom: 15px;
        overflow: hidden;
    }
    
    .faq-question {
        padding: 20px;
        background: #fff;
        cursor: pointer;
        display: flex;
        justify-content: space-between;
        align-items: center;
        font-weight: 500;
        border: 1px solid #eee;
    }
    
    .faq-question:hover {
        background: #f5f5f5;
    }
    
    .faq-question i {
        transition: transform 0.3s;
    }
    
    .faq-question.active i {
        transform: rotate(180deg);
    }
    
    .faq-answer {
        padding: 0 20px;
        max-height: 0;
        overflow: hidden;
        transition: all 0.3s;
        background: white;
    }
    
    .faq-answer.show {
        padding: 20px;
        max-height: 500px;
        border-top: 1px solid #eee;
    }
    
    .contact-support {
        text-align: center;
        margin-top: 50px;
        padding: 30px;
        background: linear-gradient(135deg, #ff6b6b, #ff8e8e);
        border-radius: 10px;
        color: white;
    }
    
    .contact-support h3 {
        margin-bottom: 15px;
    }
    
    .contact-support .btn {
        background: white;
        color: #ff6b6b;
        padding: 12px 30px;
        border-radius: 25px;
        text-decoration: none;
        display: inline-block;
        margin-top: 15px;
    }
</style>

<div class="faq-page">
    <div class="container">
        <div class="faq-header" data-aos="fade-up">
            <h1>Frequently Asked Questions</h1>
            <p>Pertanyaan yang sering diajukan</p>
        </div>
        
        <div class="faq-container">
            <?php foreach($faq as $index => $item): ?>
            <div class="faq-item" data-aos="fade-up">
                <div class="faq-question" onclick="toggleFAQ(this)">
                    <span><?= ($index + 1) ?>. <?= $item['pertanyaan'] ?></span>
                    <i class="fas fa-chevron-down"></i>
                </div>
                <div class="faq-answer">
                    <?= $item['jawaban'] ?>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        
        <div class="contact-support" data-aos="fade-up">
            <h3>Masih ada pertanyaan?</h3>
            <p>Hubungi kami via WhatsApp, kami siap membantu</p>
            <a href="https://wa.me/<?= $kontak['whatsapp'] ?>" class="btn" target="_blank">
                <i class="fab fa-whatsapp"></i> Chat Admin
            </a>
        </div>
    </div>
</div>

<script>
function toggleFAQ(element) {
    element.classList.toggle('active');
    var answer = element.nextElementSibling;
    answer.classList.toggle('show');
}
</script>

<?php include 'includes/footer.php'; ?>