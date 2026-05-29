<?php
include 'includes/config.php';

header('Content-Type: application/json');

// Ambil parameter
$kode = isset($_GET['kode']) ? trim($_GET['kode']) : '';
$total = isset($_GET['total']) ? (int)$_GET['total'] : 0;

// Validasi input
if(empty($kode)) {
    echo json_encode([
        'valid' => false, 
        'pesan' => 'Masukkan kode voucher terlebih dahulu'
    ]);
    exit;
}

// Validasi format kode (BDAY + 4 digit + 6 karakter)
if(!preg_match('/^BDAY[0-9]{4}[A-Z0-9]{6}$/', $kode)) {
    echo json_encode([
        'valid' => false, 
        'pesan' => 'Format kode voucher tidak valid'
    ]);
    exit;
}

// Cek koneksi database
if(!$conn) {
    echo json_encode([
        'valid' => false, 
        'pesan' => 'Koneksi database gagal'
    ]);
    exit;
}

// Cek apakah kode voucher ada di database
$query = "SELECT * FROM birthday_club WHERE kode_voucher = '$kode' AND is_active = 1";
$result = mysqli_query($conn, $query);

if(!$result) {
    echo json_encode([
        'valid' => false, 
        'pesan' => 'Error database: ' . mysqli_error($conn)
    ]);
    exit;
}

if(mysqli_num_rows($result) == 0) {
    echo json_encode([
        'valid' => false, 
        'pesan' => 'Kode voucher tidak ditemukan atau sudah tidak aktif'
    ]);
    exit;
}

$user = mysqli_fetch_assoc($result);

// Cek apakah hari ini dalam rentang H-3 sampai H+3 dari ulang tahun
$today = date('Y-m-d');
$birthday = $user['tanggal_lahir'];
$birthday_this_year = date('Y') . '-' . date('m-d', strtotime($birthday));

$start = date('Y-m-d', strtotime($birthday_this_year . ' -3 days'));
$end = date('Y-m-d', strtotime($birthday_this_year . ' +3 days'));

if($today >= $start && $today <= $end) {
    $diskon = $total * 0.2; // 20% diskon
    $total_akhir = $total - $diskon;
    
    // Catat penggunaan voucher
    $update = "UPDATE birthday_club SET 
               used_count = used_count + 1, 
               last_used = '$today' 
               WHERE id = {$user['id']}";
    mysqli_query($conn, $update);
    
    echo json_encode([
        'valid' => true,
        'pesan' => '🎉 Selamat! Voucher berlaku! Diskon 20%',
        'diskon' => $diskon,
        'total_akhir' => $total_akhir,
        'nama' => $user['nama']
    ]);
} else {
    echo json_encode([
        'valid' => false, 
        'pesan' => 'Voucher hanya berlaku H-3 sampai H+3 dari tanggal ulang tahunmu'
    ]);
}
?>