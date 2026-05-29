<?php
include 'includes/header.php';

// Proses quiz
$rekomendasi = '';
$gambar_rekomendasi = '';

if(isset($_POST['submit_quiz'])) {
    $acara = $_POST['acara'];
    $rasa = $_POST['rasa'];
    $orang = $_POST['orang'];
    $budget = $_POST['budget'];
    
    // Logika rekomendasi
    if($acara == 'ulang_tahun') {
        if($rasa == 'coklat') {
            $rekomendasi = 'Chocolate Fudge Cake';
            $gambar_rekomendasi = 'chocolate-fudge.jpg';
        } elseif($rasa == 'strawberry') {
            $rekomendasi = 'Strawberry Shortcake';
            $gambar_rekomendasi = 'strawberry-shortcake.jpg';
        } else {
            $rekomendasi = 'Rainbow Birthday Cake';
            $gambar_rekomendasi = 'rainbow-cake.jpg';
        }
    } elseif($acara == 'pernikahan') {
        $rekomendasi = 'Wedding Cake 3 Tingkat';
        $gambar_rekomendasi = 'wedding-cake.jpg';
    } elseif($acara == 'anniversary') {
        $rekomendasi = 'Red Velvet Cake';
        $gambar_rekomendasi = 'red-velvet.jpg';
    } elseif($acara == 'arisan') {
        $rekomendasi = 'Cupcake Set (12 pcs)';
        $gambar_rekomendasi = 'cupcake-set.jpg';
    }
    
    // Sesuaikan dengan budget
    if($budget < 150000) {
        $rekomendasi = 'Cupcake Mini (4 pcs)';
    } elseif($budget > 500000) {
        $rekomendasi = 'Custom Cake ' . $rekomendasi;
    }
}
?>

<style>
    .quiz-page {
        padding: 60px 0;
        background: linear-gradient(135deg, #f8f9fa, #fff);
    }
    
    .quiz-container {
        max-width: 700px;
        margin: 0 auto;
        background: white;
        border-radius: 30px;
        box-shadow: 0 20px 60px rgba(0,0,0,0.1);
        overflow: hidden;
    }
    
    .quiz-header {
        background: linear-gradient(135deg, #ff6b6b, #ff8e8e);
        color: white;
        padding: 40px 30px;
        text-align: center;
    }
    
    .quiz-header i {
        font-size: 4rem;
        margin-bottom: 15px;
    }
    
    .quiz-header h1 {
        font-size: 2.2rem;
        margin-bottom: 10px;
    }
    
    .quiz-header p {
        opacity: 0.9;
    }
    
    .quiz-body {
        padding: 40px;
    }
    
    .question {
        margin-bottom: 30px;
    }
    
    .question h3 {
        margin-bottom: 15px;
        color: #333;
        font-size: 1.2rem;
    }
    
    .options {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
        gap: 15px;
    }
    
    .option {
        position: relative;
    }
    
    .option input[type="radio"] {
        display: none;
    }
    
    .option label {
        display: block;
        padding: 15px;
        background: #f8f9fa;
        border: 2px solid #eee;
        border-radius: 12px;
        text-align: center;
        cursor: pointer;
        transition: all 0.3s;
    }
    
    .option input[type="radio"]:checked + label {
        border-color: #ff6b6b;
        background: linear-gradient(135deg, #fff, #fff0f0);
        color: #ff6b6b;
        font-weight: 500;
    }
    
    .option label:hover {
        border-color: #ff6b6b;
        transform: translateY(-2px);
    }
    
    .btn-quiz {
        width: 100%;
        padding: 15px;
        background: linear-gradient(135deg, #ff6b6b, #ff8e8e);
        color: white;
        border: none;
        border-radius: 12px;
        font-size: 1.1rem;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.3s;
        margin-top: 20px;
    }
    
    .btn-quiz:hover {
        transform: translateY(-3px);
        box-shadow: 0 10px 30px rgba(255,107,107,0.3);
    }
    
    .result {
        margin-top: 40px;
        padding: 30px;
        background: linear-gradient(135deg, #fff9f9, #fff);
        border-radius: 20px;
        text-align: center;
        border: 2px solid #ff6b6b;
    }
    
    .result h2 {
        color: #ff6b6b;
        margin-bottom: 15px;
    }
    
    .result .cake-name {
        font-size: 2rem;
        font-weight: 700;
        margin-bottom: 20px;
        color: #333;
    }
    
    .result .btn-add {
        display: inline-block;
        padding: 12px 30px;
        background: #ff6b6b;
        color: white;
        text-decoration: none;
        border-radius: 50px;
        margin-top: 20px;
    }
    
    .reset-btn {
        display: inline-block;
        margin-top: 15px;
        color: #ff6b6b;
        text-decoration: none;
    }
</style>

<div class="quiz-page">
    <div class="container">
        <div class="quiz-container">
            <div class="quiz-header">
                <i class="fas fa-question-circle"></i>
                <h1>🎂 Cake Quiz</h1>
                <p>Jawab 4 pertanyaan, dapatkan rekomendasi kue yang tepat untukmu!</p>
            </div>
            
            <div class="quiz-body">
                <?php if(!isset($_POST['submit_quiz']) || isset($_POST['ulangi'])): ?>
                
                <form method="POST">
                    <!-- Pertanyaan 1: Acara -->
                    <div class="question">
                        <h3>1. Untuk acara apa kue ini?</h3>
                        <div class="options">
                            <div class="option">
                                <input type="radio" name="acara" id="ulang_tahun" value="ulang_tahun" required>
                                <label for="ulang_tahun">🎂 Ulang Tahun</label>
                            </div>
                            <div class="option">
                                <input type="radio" name="acara" id="pernikahan" value="pernikahan">
                                <label for="pernikahan">💒 Pernikahan</label>
                            </div>
                            <div class="option">
                                <input type="radio" name="acara" id="anniversary" value="anniversary">
                                <label for="anniversary">💝 Anniversary</label>
                            </div>
                            <div class="option">
                                <input type="radio" name="acara" id="arisan" value="arisan">
                                <label for="arisan">👥 Arisan/Kumpul</label>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Pertanyaan 2: Rasa Favorit -->
                    <div class="question">
                        <h3>2. Rasa apa yang paling kamu suka?</h3>
                        <div class="options">
                            <div class="option">
                                <input type="radio" name="rasa" id="coklat" value="coklat" required>
                                <label for="coklat">🍫 Coklat</label>
                            </div>
                            <div class="option">
                                <input type="radio" name="rasa" id="strawberry" value="strawberry">
                                <label for="strawberry">🍓 Strawberry</label>
                            </div>
                            <div class="option">
                                <input type="radio" name="rasa" id="keju" value="keju">
                                <label for="keju">🧀 Keju</label>
                            </div>
                            <div class="option">
                                <input type="radio" name="rasa" id="red_velvet" value="red velvet">
                                <label for="red_velvet">🥀 Red Velvet</label>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Pertanyaan 3: Jumlah Orang -->
                    <div class="question">
                        <h3>3. Untuk berapa orang?</h3>
                        <div class="options">
                            <div class="option">
                                <input type="radio" name="orang" id="4-6" value="4-6" required>
                                <label for="4-6">4-6 orang</label>
                            </div>
                            <div class="option">
                                <input type="radio" name="orang" id="8-10" value="8-10">
                                <label for="8-10">8-10 orang</label>
                            </div>
                            <div class="option">
                                <input type="radio" name="orang" id="12+" value="12+">
                                <label for="12+">12+ orang</label>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Pertanyaan 4: Budget -->
                    <div class="question">
                        <h3>4. Budget yang disiapkan?</h3>
                        <div class="options">
                            <div class="option">
                                <input type="radio" name="budget" id="150" value="150000" required>
                                <label for="150">Rp 100-150k</label>
                            </div>
                            <div class="option">
                                <input type="radio" name="budget" id="250" value="250000">
                                <label for="250">Rp 150-250k</label>
                            </div>
                            <div class="option">
                                <input type="radio" name="budget" id="400" value="400000">
                                <label for="400">Rp 250-400k</label>
                            </div>
                            <div class="option">
                                <input type="radio" name="budget" id="500" value="500000">
                                <label for="500">> Rp 500k</label>
                            </div>
                        </div>
                    </div>
                    
                    <button type="submit" name="submit_quiz" class="btn-quiz">
                        <i class="fas fa-magic"></i> Dapatkan Rekomendasi
                    </button>
                </form>
                
                <?php else: ?>
                
                <!-- HASIL REKOMENDASI -->
                <div class="result">
                    <i class="fas fa-check-circle" style="font-size: 4rem; color: #4CAF50; margin-bottom: 20px;"></i>
                    <h2>🎉 Rekomendasi Kue Untukmu!</h2>
                    
                    <div class="cake-name">
                        <?= $rekomendasi ?>
                    </div>
                    
                    <p style="margin: 20px 0; color: #666;">
                        Berdasarkan pilihanmu, kami rekomendasikan kue ini.
                        Cocok untuk acara <strong><?= $_POST['acara'] ?></strong> 
                        dengan budget <strong>Rp <?= number_format($_POST['budget'], 0, ',', '.') ?></strong>
                    </p>
                    
                    <a href="cakes.php?search=<?= urlencode($rekomendasi) ?>" class="btn-add">
                        <i class="fas fa-shopping-cart"></i> Lihat Produk
                    </a>
                    
                    <form method="POST" style="margin-top: 20px;">
                        <button type="submit" name="ulangi" class="reset-btn">
                            <i class="fas fa-redo"></i> Ulangi Quiz
                        </button>
                    </form>
                </div>
                
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>