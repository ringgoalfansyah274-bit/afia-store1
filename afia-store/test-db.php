<?php
echo "<h2>Test Koneksi Database</h2>";

// Coba koneksi langsung
$host = 'localhost';
$user = 'root';
$pass = '';
$db = 'afia_store';

$conn = mysqli_connect($host, $user, $pass, $db);

if($conn) {
    echo "✅ Koneksi database BERHASIL!<br>";
    
    // Cek tabel
    $result = mysqli_query($conn, "SHOW TABLES");
    echo "<h3>Daftar Tabel:</h3>";
    echo "<ul>";
    while($row = mysqli_fetch_array($result)) {
        echo "<li>" . $row[0] . "</li>";
    }
    echo "</ul>";
} else {
    echo "❌ Koneksi GAGAL: " . mysqli_connect_error();
}
?>