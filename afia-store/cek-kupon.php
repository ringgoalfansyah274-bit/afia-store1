<?php
include 'includes/config.php';

$kode = $_GET['kode'];
$total = $_GET['total'];

$kupon = query("SELECT * FROM coupons WHERE kode = '$kode' AND (berlaku_sampai IS NULL OR berlaku_sampai >= CURDATE())");

if(empty($kupon)) {
    echo json_encode(['valid' => false, 'pesan' => 'Kupon tidak valid']);
    exit;
}

$kupon = $kupon[0];

if($total < $kupon['min_belanja']) {
    echo json_encode([
        'valid' => false, 
        'pesan' => 'Minimal belanja Rp ' . number_format($kupon['min_belanja'], 0, ',', '.')
    ]);
    exit;
}

$diskon = 0;
if($kupon['diskon_persen'] > 0) {
    $diskon = $total * $kupon['diskon_persen'] / 100;
} else {
    $diskon = $kupon['diskon_nominal'];
}

$total_akhir = $total - $diskon;

echo json_encode([
    'valid' => true,
    'pesan' => 'Diskon Rp ' . number_format($diskon, 0, ',', '.'),
    'diskon' => $diskon,
    'total_akhir' => 'Rp ' . number_format($total_akhir, 0, ',', '.')
]);
?>