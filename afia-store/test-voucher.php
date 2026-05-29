<?php
include 'includes/config.php';

echo "<h2>TEST VOUCHER BIRTHDAY</h2>";

// Cek koneksi database
echo "<h3>1. Koneksi Database</h3>";
if($conn) {
    echo "✅ Database terkoneksi<br>";
} else {
    echo "❌ Gagal koneksi: " . mysqli_connect_error() . "<br>";
}

// Cek tabel birthday_club
echo "<h3>2. Tabel birthday_club</h3>";
$result = mysqli_query($conn, "SHOW TABLES LIKE 'birthday_club'");
if(mysqli_num_rows($result) > 0) {
    echo "✅ Tabel birthday_club ada<br>";
    
    // Hitung jumlah data
    $count = mysqli_query($conn, "SELECT COUNT(*) as total FROM birthday_club");
    $data = mysqli_fetch_assoc($count);
    echo "Jumlah data: " . $data['total'] . "<br>";
    
    // Tampilkan semua kode voucher
    $vouchers = mysqli_query($conn, "SELECT kode_voucher, nama, tanggal_lahir FROM birthday_club");
    echo "<h4>Daftar Voucher:</h4>";
    echo "<ul>";
    while($v = mysqli_fetch_assoc($vouchers)) {
        echo "<li><strong>" . $v['kode_voucher'] . "</strong> - " . $v['nama'] . " (" . $v['tanggal_lahir'] . ")</li>";
    }
    echo "</ul>";
} else {
    echo "❌ Tabel birthday_club tidak ada<br>";
}

// Test validasi tanggal
echo "<h3>3. Test Validasi Tanggal</h3>";
$today = date('Y-m-d');
$birthday = '1990-02-20';
$birthday_this_year = date('Y') . '-' . date('m-d', strtotime($birthday));
$start = date('Y-m-d', strtotime($birthday_this_year . ' -3 days'));
$end = date('Y-m-d', strtotime($birthday_this_year . ' +3 days'));

echo "Hari ini: $today<br>";
echo "Ulang tahun: $birthday<br>";
echo "Tahun ini: $birthday_this_year<br>";
echo "Berlaku: $start sampai $end<br>";

if($today >= $start && $today <= $end) {
    echo "✅ Hari ini DALAM masa berlaku<br>";
} else {
    echo "❌ Hari ini LUAR masa berlaku<br>";
}
?>