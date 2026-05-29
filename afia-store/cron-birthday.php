<?php
include 'includes/config.php';

// Cek siapa yang ulang tahun hari ini
$today = date('m-d');
$query = "SELECT * FROM birthday_club WHERE DATE_FORMAT(tanggal_lahir, '%m-%d') = '$today'";
$result = mysqli_query($conn, $query);

while($row = mysqli_fetch_assoc($result)) {
    $nama = $row['nama'];
    $email = $row['email'];
    $wa = $row['whatsapp'];
    $kode = $row['kode_voucher'];
    
    // Kirim email
    $subject = "🎂 Happy Birthday dari Afia Cake!";
    $message = "Halo $nama,\n\nSelamat ulang tahun! 🎉\n\nSebagai hadiah, kamu mendapatkan diskon 20% dengan kode: $kode\n\nBerlaku H-3 sampai H+3 dari hari ulang tahunmu.\n\n- Afia Store";
    mail($email, $subject, $message);
    
    // Catat log
    echo "Birthday email sent to $nama ($email)\n";
}
?>