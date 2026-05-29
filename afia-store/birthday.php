<?php
include 'includes/header.php';

$error = '';
$sukses = '';

if(isset($_POST['daftar'])) {
    $nama = mysqli_real_escape_string($conn, trim($_POST['nama']));
    $email = mysqli_real_escape_string($conn, trim($_POST['email']));
    $whatsapp = mysqli_real_escape_string($conn, trim($_POST['whatsapp']));
    $tanggal = $_POST['tanggal_lahir'];
    
    // Validasi input
    $errors = [];
    
    if(empty($nama)) $errors[] = "Nama harus diisi";
    if(empty($email)) $errors[] = "Email harus diisi";
    if(empty($whatsapp)) $errors[] = "Nomor WhatsApp harus diisi";
    if(empty($tanggal)) $errors[] = "Tanggal lahir harus diisi";
    
    // Validasi format email
    if(!empty($email) && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Format email tidak valid";
    }
    
    // Validasi nomor WhatsApp (hanya angka)
    if(!empty($whatsapp) && !preg_match('/^[0-9]{10,13}$/', $whatsapp)) {
        $errors[] = "Nomor WhatsApp harus 10-13 digit angka";
    }
    
    // Validasi umur (minimal 10 tahun)
    $tanggal_lahir = new DateTime($tanggal);
    $today = new DateTime();
    $umur = $today->diff($tanggal_lahir)->y;
    
    if($umur < 10) {
        $errors[] = "Minimal usia 10 tahun untuk mendaftar";
    }
    
    // Cek apakah email sudah terdaftar
    $check_email = mysqli_query($conn, "SELECT id FROM birthday_club WHERE email = '$email'");
    if(mysqli_num_rows($check_email) > 0) {
        $errors[] = "Email sudah terdaftar di Birthday Club";
    }
    
    // Cek apakah WhatsApp sudah terdaftar
    $check_wa = mysqli_query($conn, "SELECT id FROM birthday_club WHERE whatsapp = '$whatsapp'");
    if(mysqli_num_rows($check_wa) > 0) {
        $errors[] = "Nomor WhatsApp sudah terdaftar di Birthday Club";
    }
    
    // Jika tidak ada error, simpan data
    if(empty($errors)) {
        // Generate kode voucher unik yang sulit ditebak
        do {
            // Format: BDAY + TAHUN + BULAN + RANDOM(6) + CHECKSUM
            $tahun = date('y');
            $bulan = date('m');
            $random = strtoupper(substr(md5(uniqid(mt_rand(), true)), 0, 6));
            $kode = "BDAY{$tahun}{$bulan}{$random}";
            
            // Cek apakah kode sudah ada
            $check_kode = mysqli_query($conn, "SELECT id FROM birthday_club WHERE kode_voucher = '$kode'");
        } while(mysqli_num_rows($check_kode) > 0);
        
        $query = "INSERT INTO birthday_club (nama, email, whatsapp, tanggal_lahir, kode_voucher) 
                  VALUES ('$nama', '$email', '$whatsapp', '$tanggal', '$kode')";
        
        if(mysqli_query($conn, $query)) {
            $sukses = "✅ Pendaftaran berhasil! Simpan kode vouchermu: <strong>$kode</strong>";
            
            // Kirim email konfirmasi (opsional)
            $to = $email;
            $subject = "🎂 Selamat Bergabung di Birthday Club Afia Store!";
            $message = "
            <html>
            <head>
                <title>Birthday Club Afia Store</title>
            </head>
            <body>
                <h2>Halo $nama!</h2>
                <p>Terima kasih telah mendaftar di Birthday Club Afia Store.</p>
                <p>Berikut adalah kode voucher eksklusifmu:</p>
                <h1 style='background: #ff6b6b; color: white; padding: 15px; text-align: center; letter-spacing: 2px;'>$kode</h1>
                <p><strong>Kode ini hanya berlaku H-3 sampai H+3 dari tanggal ulang tahunmu.</strong></p>
                <p>Simpan kode ini baik-baik dan jangan berikan kepada orang lain.</p>
                <br>
                <p>Happy Birthday in Advance! 🎂</p>
                <p>- Afia Store</p>
            </body>
            </html>
            ";
            
            $headers = "MIME-Version: 1.0" . "\r\n";
            $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
            $headers .= "From: Afia Store <info@afiastore.com>" . "\r\n";
            
            @mail($to, $subject, $message, $headers);
            
        } else {
            $error = "Gagal mendaftar: " . mysqli_error($conn);
        }
    } else {
        $error = implode("<br>", $errors);
    }
}
?>

<style>
    /* Style yang sama seperti sebelumnya */
    .birthday-page {
        padding: 60px 0;
        background: linear-gradient(135deg, #f8f9fa, #fff);
        min-height: 100vh;
    }
    
    .birthday-container {
        max-width: 500px;
        margin: 0 auto;
        background: white;
        padding: 40px;
        border-radius: 20px;
        box-shadow: 0 10px 40px rgba(0,0,0,0.1);
    }
    
    .birthday-header {
        text-align: center;
        margin-bottom: 30px;
    }
    
    .birthday-header i {
        font-size: 4rem;
        color: #ff6b6b;
        margin-bottom: 15px;
    }
    
    .birthday-header h1 {
        font-size: 2rem;
        color: #333;
        margin-bottom: 10px;
    }
    
    .birthday-header p {
        color: #666;
    }
    
    .form-group {
        margin-bottom: 20px;
    }
    
    .form-group label {
        display: block;
        margin-bottom: 8px;
        font-weight: 500;
        color: #333;
    }
    
    .form-group input {
        width: 100%;
        padding: 12px 15px;
        border: 2px solid #eee;
        border-radius: 10px;
        font-size: 1rem;
        transition: all 0.3s;
    }
    
    .form-group input:focus {
        outline: none;
        border-color: #ff6b6b;
        box-shadow: 0 0 0 4px rgba(255,107,107,0.1);
    }
    
    .btn-daftar {
        width: 100%;
        padding: 15px;
        background: linear-gradient(135deg, #ff6b6b, #ff8e8e);
        color: white;
        border: none;
        border-radius: 10px;
        font-size: 1.1rem;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.3s;
    }
    
    .btn-daftar:hover {
        transform: translateY(-3px);
        box-shadow: 0 10px 20px rgba(255,107,107,0.3);
    }
    
    .alert {
        padding: 15px;
        border-radius: 10px;
        margin-bottom: 20px;
    }
    
    .alert-success {
        background: #d4edda;
        color: #155724;
        border: 1px solid #c3e6cb;
    }
    
    .alert-error {
        background: #f8d7da;
        color: #721c24;
        border: 1px solid #f5c6cb;
    }
    
    .voucher-code {
        background: #f0f0f0;
        padding: 15px;
        text-align: center;
        font-size: 1.5rem;
        font-weight: bold;
        letter-spacing: 2px;
        border-radius: 10px;
        margin: 20px 0;
        border: 2px dashed #ff6b6b;
    }
    
    .info-text {
        color: #666;
        font-size: 0.9rem;
        margin-top: 5px;
    }
</style>

<div class="birthday-page">
    <div class="container">
        <div class="birthday-container">
            <div class="birthday-header">
                <i class="fas fa-birthday-cake"></i>
                <h1>🎂 Birthday Club</h1>
                <p>Dapatkan diskon spesial di hari ulang tahunmu!</p>
            </div>
            
            <?php if($sukses): ?>
                <div class="alert alert-success"><?= $sukses ?></div>
                <div style="text-align: center;">
                    <a href="index.php" class="btn-daftar" style="width: auto; padding: 12px 30px;">Kembali ke Home</a>
                </div>
            <?php else: ?>
                <?php if($error): ?>
                    <div class="alert alert-error"><?= $error ?></div>
                <?php endif; ?>
                
                <form method="POST">
                    <div class="form-group">
                        <label><i class="fas fa-user"></i> Nama Lengkap</label>
                        <input type="text" name="nama" placeholder="Masukkan nama lengkap" value="<?= isset($_POST['nama']) ? htmlspecialchars($_POST['nama']) : '' ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label><i class="fas fa-envelope"></i> Email</label>
                        <input type="email" name="email" placeholder="contoh@email.com" value="<?= isset($_POST['email']) ? htmlspecialchars($_POST['email']) : '' ?>" required>
                        <div class="info-text">Email akan digunakan untuk mengirim kode voucher</div>
                    </div>
                    
                    <div class="form-group">
                        <label><i class="fab fa-whatsapp"></i> Nomor WhatsApp</label>
                        <input type="text" name="whatsapp" placeholder="081234567890" value="<?= isset($_POST['whatsapp']) ? htmlspecialchars($_POST['whatsapp']) : '' ?>" required>
                        <div class="info-text">Masukkan tanpa spasi dan tanda kurung</div>
                    </div>
                    
                    <div class="form-group">
                        <label><i class="fas fa-calendar"></i> Tanggal Lahir</label>
                        <input type="date" name="tanggal_lahir" value="<?= isset($_POST['tanggal_lahir']) ? $_POST['tanggal_lahir'] : '' ?>" required>
                    </div>
                    
                    <button type="submit" name="daftar" class="btn-daftar">
                        Daftar Sekarang
                    </button>
                </form>
                
                <div style="margin-top: 30px; padding-top: 20px; border-top: 1px solid #eee;">
                    <h3 style="margin-bottom: 15px;">🎁 Yang Kamu Dapatkan:</h3>
                    <ul style="list-style: none;">
                        <li style="margin-bottom: 10px;"><i class="fas fa-check-circle" style="color: #4CAF50;"></i> Kode voucher unik & rahasia</li>
                        <li style="margin-bottom: 10px;"><i class="fas fa-check-circle" style="color: #4CAF50;"></i> Diskon 20% di hari ulang tahun</li>
                        <li style="margin-bottom: 10px;"><i class="fas fa-check-circle" style="color: #4CAF50;"></i> Gratis cupcake spesial</li>
                        <li style="margin-bottom: 10px;"><i class="fas fa-check-circle" style="color: #4CAF50;"></i> Notifikasi H-3 via email</li>
                    </ul>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>