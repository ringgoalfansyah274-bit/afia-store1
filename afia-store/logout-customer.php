<?php
session_start();

// Hapus session customer
unset($_SESSION['customer']);

// Set notifikasi berhasil logout
$_SESSION['notification'] = [
    'type' => 'success',
    'message' => '✅ Anda telah berhasil logout!'
];

// Redirect ke halaman utama
header('Location: index.php');
exit;
?>