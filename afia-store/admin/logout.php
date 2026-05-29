<?php
session_start();

// Hapus semua session admin
session_unset();
session_destroy();

// Set notifikasi untuk ditampilkan di halaman home
session_start(); // Mulai session baru untuk notifikasi
$_SESSION['notification'] = [
    'type' => 'success',
    'message' => '✅ Anda telah berhasil logout dari panel admin!'
];

// Redirect ke halaman home (index.php)
header('Location: ../index.php');
exit;
?>