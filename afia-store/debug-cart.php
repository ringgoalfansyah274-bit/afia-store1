<?php
session_start();
echo "<h2>Debug Session Cart</h2>";
echo "<pre>";
print_r($_SESSION['cart']);
echo "</pre>";

echo "<h3>Detail Produk:</h3>";
include 'includes/config.php';
if(!empty($_SESSION['cart'])) {
    foreach($_SESSION['cart'] as $item) {
        $id = $item['id'];
        $result = mysqli_query($conn, "SELECT id, nama_produk, stok FROM products WHERE id = $id");
        if(mysqli_num_rows($result) > 0) {
            $produk = mysqli_fetch_assoc($result);
            echo "ID $id - {$produk['nama_produk']} - Stok: {$produk['stok']}<br>";
        } else {
            echo "ID $id - <span style='color:red'>PRODUK TIDAK DITEMUKAN!</span><br>";
        }
    }
} else {
    echo "Cart kosong";
}
?>