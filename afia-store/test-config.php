<?php
echo "<h2>Test Config</h2>";

// Cek apakah file config.php ada
if(file_exists('includes/config.php')) {
    echo "✅ File config.php ADA<br>";
    include 'includes/config.php';
    
    // Cek apakah $conn ada
    if(isset($conn)) {
        echo "✅ Variabel \$conn ADA<br>";
        
        if($conn) {
            echo "✅ Koneksi database BERHASIL<br>";
            
            // Test query
            $test = mysqli_query($conn, "SELECT 1 as test");
            if($test) {
                echo "✅ Query BERHASIL<br>";
            } else {
                echo "❌ Query GAGAL: " . mysqli_error($conn) . "<br>";
            }
        } else {
            echo "❌ Koneksi database GAGAL<br>";
        }
    } else {
        echo "❌ Variabel \$conn TIDAK ADA<br>";
    }
} else {
    echo "❌ File config.php TIDAK ADA<br>";
}
?>