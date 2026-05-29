<?php
include 'includes/header.php';
?>

<style>
    .about-page {
        padding: 60px 0;
        background: linear-gradient(135deg, #f8f9fa, #fff);
        min-height: 60vh;
    }
    
    .about-header {
        text-align: center;
        margin-bottom: 40px;
    }
    
    .about-header h1 {
        font-size: 2.5rem;
        color: #e84545;
        margin-bottom: 15px;
    }
    
    .about-header p {
        color: #666;
        font-size: 1.1rem;
    }
    
    .about-content {
        background: white;
        border-radius: 20px;
        padding: 40px;
        box-shadow: 0 10px 30px rgba(0,0,0,0.05);
        margin-bottom: 50px;
    }
    
    .about-content h2 {
        color: #e84545;
        margin: 25px 0 15px;
        font-size: 1.5rem;
    }
    
    .about-content p {
        color: #555;
        line-height: 1.8;
        margin-bottom: 15px;
    }
    
    .about-content ul {
        margin-left: 25px;
        margin-bottom: 20px;
    }
    
    .about-content li {
        color: #555;
        margin-bottom: 8px;
    }
    
    .team-grid {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 25px;
        margin-top: 30px;
    }
    
    .team-card {
        background: #f8f9fa;
        padding: 25px 20px;
        border-radius: 15px;
        text-align: center;
        transition: all 0.3s;
    }
    
    .team-card:hover {
        transform: translateY(-10px);
        box-shadow: 0 10px 25px rgba(0,0,0,0.1);
    }
    
    .team-card i {
        font-size: 3rem;
        color: #e84545;
        margin-bottom: 15px;
    }
    
    .team-card h3 {
        color: #333;
        margin-bottom: 5px;
        font-size: 1.1rem;
    }
    
    .team-card p {
        color: #888;
        font-size: 0.9rem;
    }
    
    .maps-section {
        border-radius: 20px;
        overflow: hidden;
        box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        margin-top: 30px;
    }
    
    .maps-section iframe {
        width: 100%;
        height: 400px;
        border: none;
    }
    
    .alamat-info {
        text-align: center;
        margin-top: 20px;
        color: #666;
    }
    
    .alamat-info i {
        color: #e84545;
        margin-right: 5px;
    }
    
    @media (max-width: 768px) {
        .team-grid {
            grid-template-columns: repeat(2, 1fr);
            gap: 15px;
        }
        
        .about-header h1 {
            font-size: 2rem;
        }
        
        .about-content {
            padding: 25px;
        }
        
        .maps-section iframe {
            height: 280px;
        }
    }
    
    @media (max-width: 480px) {
        .team-grid {
            grid-template-columns: 1fr;
        }
    }
</style>

<div class="about-page">
    <div class="container">
        <div class="about-header">
            <h1>Tentang Afia Cake</h1>
            <p>Delicious Cakes for Every Occasion</p>
        </div>
        
        <div class="about-content">
            <h2>🍰 Sejarah Afia Cake</h2>
            <p>Afia Cake adalah toko kue online yang menyediakan berbagai macam kue lezat untuk berbagai acara spesial Anda. Kami berdiri sejak tahun 2024 dengan komitmen untuk menghadirkan kue berkualitas dengan rasa yang istimewa.</p>
            
            <h2>🎯 Visi Kami</h2>
            <p>Menjadi toko kue online terpercaya yang selalu mengutamakan kepuasan pelanggan.</p>
            
            <h2>⭐ Misi Kami</h2>
            <ul>
                <li>Menyediakan kue berkualitas dengan bahan-bahan terbaik</li>
                <li>Memberikan pelayanan yang ramah dan profesional</li>
                <li>Mengutamakan kebersihan dan keamanan produk</li>
                <li>Terus berinovasi dalam menciptakan varian kue baru</li>
            </ul>
            
            <h2>👨‍🍳 Tim Afia Cake</h2>
            <div class="team-grid">
                <div class="team-card">
                    <i class="fas fa-user-tie"></i>
                    <h3>M. Fariz</h3>
                    <p>Project Manager</p>
                </div>
                <div class="team-card">
                    <i class="fas fa-code"></i>
                    <h3>Wida Muya Ningsih</h3>
                    <p>Frontend Developer</p>
                </div>
                <div class="team-card">
                    <i class="fas fa-database"></i>
                    <h3>Ringgo Alfansyah Aditya</h3>
                    <p>Backend Developer</p>
                </div>
                <div class="team-card">
                    <i class="fas fa-chart-line"></i>
                    <h3>Rendi Arif Firmansyah</h3>
                    <p>Database Administrator</p>
                </div>
            </div>
        </div>
        
        <!-- Location Section (MAPS) -->
        <div class="location-section">
            <h2 class="section-title" style="text-align: center; font-size: 2rem; margin-bottom: 30px; color: #333;">
                <span style="color: #e84545;">Our</span> Location
            </h2>
            <div class="maps-section">
                <?php
                // Ambil koordinat dari database
                $lat = isset($kontak['maps_lat']) ? $kontak['maps_lat'] : '-6.2088';
                $lng = isset($kontak['maps_lng']) ? $kontak['maps_lng'] : '106.8456';
                $zoom = isset($kontak['maps_zoom']) ? $kontak['maps_zoom'] : 15;
                ?>
                <iframe 
                    src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d126908.13891519684!2d106.77910703787136!3d-6.229411668815479!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x2e69f3e945f34dbf%3A0xedbd2dcefa9be408!2sJakarta!5e0!3m2!1sid!2sid!4v1701234567890!5m2!1sid!2sid"
                    width="100%" 
                    height="450" 
                    style="border:0;" 
                    allowfullscreen="" 
                    loading="lazy"
                    title="Lokasi Afia Cake">
                </iframe>
            </div>
            
            <div class="alamat-info">
                <i class="fas fa-map-marker-alt"></i>
                <?= isset($kontak['alamat']) ? $kontak['alamat'] : 'Jl. Contoh No. 123, Jakarta Selatan' ?>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>