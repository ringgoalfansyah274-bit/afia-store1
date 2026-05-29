<?php
include 'includes/header.php';

// Proses form kontak
if(isset($_POST['send_message'])) {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $message = $_POST['message'];
    
    // Redirect ke WhatsApp dengan pesan
    $whatsapp_message = "Halo Afia Cake, saya $name ($email) ingin bertanya: $message";
    $whatsapp_url = "https://wa.me/{$kontak['whatsapp']}?text=" . urlencode($whatsapp_message);
    
    header("Location: $whatsapp_url");
    exit;
}
?>

<style>
    .contact-page {
        padding: 60px 0;
        background: linear-gradient(135deg, #f5f5f5, #fff);
    }
    
    .contact-header {
        text-align: center;
        margin-bottom: 50px;
    }
    
    .contact-header h2 {
        font-size: 2.5rem;
        color: #333;
        margin-bottom: 15px;
    }
    
    .contact-header p {
        color: #666;
        font-size: 1.1rem;
    }
    
    .contact-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
        gap: 30px;
        margin-bottom: 50px;
    }
    
    .contact-info-card {
        background: white;
        padding: 30px;
        border-radius: 20px;
        box-shadow: 0 10px 30px rgba(0,0,0,0.05);
        text-align: center;
        transition: all 0.3s;
    }
    
    .contact-info-card:hover {
        transform: translateY(-10px);
        box-shadow: 0 15px 40px rgba(255,107,107,0.1);
    }
    
    .info-icon {
        width: 80px;
        height: 80px;
        background: linear-gradient(135deg, #ff6b6b, #ff8e8e);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 25px;
        color: white;
        font-size: 2rem;
    }
    
    .contact-info-card h3 {
        margin-bottom: 15px;
        color: #333;
    }
    
    .contact-info-card p {
        color: #666;
        line-height: 1.8;
    }
    
    .contact-info-card a {
        color: #ff6b6b;
        text-decoration: none;
        font-weight: 500;
    }
    
    .contact-info-card a:hover {
        text-decoration: underline;
    }
    
    /* Form Section */
    .contact-form-section {
        background: white;
        border-radius: 30px;
        padding: 50px;
        box-shadow: 0 20px 50px rgba(0,0,0,0.1);
        margin-bottom: 50px;
    }
    
    .form-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 30px;
    }
    
    .form-group {
        margin-bottom: 25px;
    }
    
    .form-group label {
        display: block;
        margin-bottom: 8px;
        color: #333;
        font-weight: 500;
    }
    
    .form-group input,
    .form-group textarea {
        width: 100%;
        padding: 15px;
        border: 2px solid #eee;
        border-radius: 10px;
        font-family: 'Poppins', sans-serif;
        transition: all 0.3s;
    }
    
    .form-group input:focus,
    .form-group textarea:focus {
        outline: none;
        border-color: #ff6b6b;
        box-shadow: 0 0 0 4px rgba(255,107,107,0.1);
    }
    
    .btn-submit {
        background: linear-gradient(135deg, #ff6b6b, #ff8e8e);
        color: white;
        border: none;
        padding: 15px 40px;
        border-radius: 50px;
        font-size: 1.1rem;
        font-weight: bold;
        cursor: pointer;
        transition: all 0.3s;
        display: inline-flex;
        align-items: center;
        gap: 10px;
    }
    
    .btn-submit:hover {
        transform: translateY(-3px);
        box-shadow: 0 10px 30px rgba(255,107,107,0.4);
    }
    
    /* Maps Section */
    .maps-section {
        border-radius: 20px;
        overflow: hidden;
        box-shadow: 0 10px 30px rgba(0,0,0,0.1);
    }
    
    .maps-section iframe {
        width: 100%;
        height: 450px;
        border: none;
    }
    
    /* Social Media Cards */
    .social-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 20px;
        margin-top: 40px;
    }
    
    .social-card {
        background: white;
        padding: 25px;
        border-radius: 15px;
        text-align: center;
        text-decoration: none;
        color: #333;
        transition: all 0.3s;
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 15px;
    }
    
    .social-card.wa:hover {
        background: #25D366;
        color: white;
        transform: translateY(-5px);
    }
    
    .social-card.ig:hover {
        background: linear-gradient(135deg, #833AB4, #E1306C);
        color: white;
        transform: translateY(-5px);
    }
    
    .social-card.fb:hover {
        background: #4267B2;
        color: white;
        transform: translateY(-5px);
    }
    
    .social-card i {
        font-size: 2.5rem;
    }
    
    @media (max-width: 768px) {
        .form-grid {
            grid-template-columns: 1fr;
        }
        
        .contact-form-section {
            padding: 30px;
        }
    }
</style>

<div class="contact-page">
    <div class="container">
        <!-- Header -->
        <div class="contact-header" data-aos="fade-up">
            <h2>Get in Touch</h2>
            <p>Kami siap membantu Anda 24/7. Hubungi kami melalui berbagai platform berikut</p>
        </div>
        
        <!-- Contact Info Grid -->
        <div class="contact-grid">
            <div class="contact-info-card" data-aos="fade-right">
                <div class="info-icon"><i class="fas fa-map-marker-alt"></i></div>
                <h3>Alamat</h3>
                <p><?= $kontak['alamat'] ?></p>
                <a href="#maps" class="view-map">Lihat Peta <i class="fas fa-arrow-right"></i></a>
            </div>
            
            <div class="contact-info-card" data-aos="fade-up">
                <div class="info-icon"><i class="fas fa-phone"></i></div>
                <h3>Telepon</h3>
                <p><?= $kontak['telepon'] ?></p>
                <p style="color: #999; font-size: 0.9rem;">Senin - Sabtu, 09.00 - 18.00</p>
                <a href="tel:<?= $kontak['telepon'] ?>"><i class="fas fa-phone-alt"></i> Call Now</a>
            </div>
            
            <div class="contact-info-card" data-aos="fade-left">
                <div class="info-icon"><i class="fas fa-envelope"></i></div>
                <h3>Email</h3>
                <p><?= $kontak['email'] ?></p>
                <p style="color: #999; font-size: 0.9rem;">24 jam, balas dalam 1-2 jam</p>
                <a href="mailto:<?= $kontak['email'] ?>"><i class="fas fa-paper-plane"></i> Send Email</a>
            </div>
        </div>
        
        <!-- Form Section -->
        <div class="contact-form-section" data-aos="fade-up">
            <h3 style="font-size: 1.8rem; margin-bottom: 30px; text-align: center;">Kirim Pesan via WhatsApp</h3>
            
            <form method="POST">
                <div class="form-grid">
                    <div class="form-group">
                        <label><i class="fas fa-user"></i> Nama Lengkap</label>
                        <input type="text" name="name" placeholder="Masukkan nama Anda" required>
                    </div>
                    
                    <div class="form-group">
                        <label><i class="fas fa-envelope"></i> Email</label>
                        <input type="email" name="email" placeholder="Masukkan email Anda" required>
                    </div>
                </div>
                
                <div class="form-group">
                    <label><i class="fas fa-comment"></i> Pesan</label>
                    <textarea name="message" rows="5" placeholder="Tulis pesan Anda di sini..." required></textarea>
                </div>
                
                <div style="text-align: center;">
                    <button type="submit" name="send_message" class="btn-submit">
                        <i class="fab fa-whatsapp"></i> Kirim via WhatsApp
                    </button>
                    <p style="margin-top: 15px; color: #666; font-size: 0.9rem;">
                        *Pesan akan langsung terkirim ke WhatsApp kami
                    </p>
                </div>
            </form>
        </div>
        
        <!-- Social Media Cards -->
        <h3 style="text-align: center; margin: 40px 0 30px;" data-aos="fade-up">Follow & Chat with Us</h3>
        
        <div class="social-grid">
            <a href="https://wa.me/<?= $kontak['whatsapp'] ?>" target="_blank" class="social-card wa" data-aos="zoom-in">
                <i class="fab fa-whatsapp"></i>
                <span>WhatsApp</span>
                <small>+<?= $kontak['whatsapp'] ?></small>
            </a>
            
            <a href="https://instagram.com/<?= $kontak['instagram'] ?>" target="_blank" class="social-card ig" data-aos="zoom-in" data-aos-delay="100">
                <i class="fab fa-instagram"></i>
                <span>Instagram</span>
                <small>@<?= $kontak['instagram'] ?></small>
            </a>
            
            <a href="#" target="_blank" class="social-card fb" data-aos="zoom-in" data-aos-delay="200">
                <i class="fab fa-facebook"></i>
                <span>Facebook</span>
                <small>Afia Cake</small>
            </a>
        </div>
        
        <!-- Maps Section -->
        <div id="maps" class="maps-section" data-aos="fade-up">
            <iframe 
                src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d126908.13891519684!2d106.77910703787136!3d-6.229411668815479!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x2e69f3e945f34dbf%3A0xedbd2dcefa9be408!2sJakarta!5e0!3m2!1sid!2sid!4v1701234567890!5m2!1sid!2sid"
                allowfullscreen=""
                loading="lazy">
            </iframe>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>