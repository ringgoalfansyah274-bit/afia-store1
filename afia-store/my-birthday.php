<?php
session_start();
include 'includes/config.php';

// Asumsikan user sudah login (simplifikasi)
$email = 'user@email.com'; // Ganti dengan session user

$query = "SELECT * FROM birthday_club WHERE email = '$email'";
$result = mysqli_query($conn, $query);
$user = mysqli_fetch_assoc($result);
?>

<div class="birthday-profile">
    <h2>🎂 My Birthday Club</h2>
    
    <div class="info-card">
        <h3>Voucher Kamu:</h3>
        <div class="voucher-code"><?= $user['kode_voucher'] ?></div>
        
        <h3>Tanggal Lahir:</h3>
        <p><?= date('d F Y', strtotime($user['tanggal_lahir'])) ?></p>
        
        <h3>Status Voucher:</h3>
        <?php
        $today = date('Y-m-d');
        $birthday = date('m-d', strtotime($user['tanggal_lahir']));
        $birthday_this_year = date('Y') . '-' . $birthday;
        $start = date('Y-m-d', strtotime($birthday_this_year . ' -3 days'));
        $end = date('Y-m-d', strtotime($birthday_this_year . ' +3 days'));
        
        if($today >= $start && $today <= $end):
        ?>
            <p class="active">✅ Aktif (berlaku sampai <?= date('d F Y', strtotime($end)) ?>)</p>
        <?php else: ?>
            <p class="inactive">⏳ Belum aktif (akan aktif pada <?= date('d F Y', strtotime($start)) ?>)</p>
        <?php endif; ?>
    </div>
</div>