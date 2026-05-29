<?php
include 'includes/config.php';

// Batalkan order yang lewat 24 jam
$query = "UPDATE orders SET status = 'Dibatalkan' 
          WHERE status = 'Menunggu Pembayaran' 
          AND batas_pembayaran < NOW()";
mysqli_query($conn, $query);
?>