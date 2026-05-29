<?php
include 'includes/config.php';

// Cek member yang ulang tahun dalam 3 hari ke depan
$three_days_later = date('Y-m-d', strtotime('+3 days'));
$three_days_month_day = date('m-d', strtotime($three_days_later));

$query = "SELECT * FROM birthday_club WHERE DATE_FORMAT(tanggal_lahir, '%m-%d') = '$three_days_month_day'";
$result = mysqli_query($conn, $query);

while($row = mysqli_fetch_assoc($result)) {
    $nama = $row['nama'];
    $email = $row['email'];
    $whatsapp = $row['whatsapp'];
    $kode = $row['kode_voucher'];
    
    // Kirim email
    $subject = "🎂 Ulang Tahunmu Sudah Dekat!";
    $message = "
    <html>
    <head>
        <title>Happy Birthday in Advance!</title>
    </head>
    <body>
        <h2>Halo $nama!</h2>
        <p>Dalam 3 hari lagi kamu akan berulang tahun! 🎉</p>
        <p>Sebagai hadiah dari Afia Cake, kamu mendapatkan:</p>
        <ul>
            <li><strong>Diskon 20%</strong> dengan kode: <strong>$kode</strong></li>
            <li><strong>Gratis 1 Cupcake</strong> (dengan minimal belanja Rp 100.000)</li>
        </ul>
        <p>Voucher berlaku mulai hari ini sampai H+3 setelah ulang tahunmu.</p>
        <p><a href='http://localhost/afia-store/cakes.php' style='background: #ff6b6b; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>Belanja Sekarang</a></p>
        <br>
        <p>Happy Birthday in Advance! 🎂</p>
        <p>- Afia Cake</p>
    </body>
    </html>
    ";
    
    $headers = "MIME-Version: 1.0" . "\r\n";
    $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
    $headers .= "From: Afia Cake <info@afiacake.com>" . "\r\n";
    
    mail($email, $subject, $message, $headers);
    
    // Kirim WhatsApp (opsional)
    $wa_pesan = "Halo $nama!%0A%0A🎂 Ulang tahunmu tinggal 3 hari lagi!%0A%0ADapatkan diskon 20% dengan kode: $kode%0A%0ABerlaku sampai H+3 setelah ulang tahunmu.%0A%0A%0A- Afia Store";
    $wa_link = "https://wa.me/$whatsapp?text=$wa_pesan";
    // Bisa diakses manual atau pakai API
}
?>