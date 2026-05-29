<?php
// Aktifkan error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// ===== KONEKSI DATABASE =====
$host = 'localhost';
$username = 'root';
$password = '';
$database = 'afia_store';

$conn = mysqli_connect($host, $username, $password, $database);

// Jika koneksi gagal, tampilkan pesan
if (!$conn) {
    die("
    <div style='padding:20px; background:#f8d7da; color:#721c24; border-radius:5px; margin:20px;'>
        <h2>❌ Koneksi Database Gagal</h2>
        <p><strong>Error:</strong> " . mysqli_connect_error() . "</p>
        <p><strong>Solusi:</strong> 
            <ul>
                <li>Buka XAMPP Control Panel</li>
                <li>Start MySQL (klik tombol Start)</li>
                <li>Refresh halaman ini</li>
            </ul>
        </p>
    </div>
    ");
}

// ===== FUNCTION QUERY =====
function query($sql) {
    global $conn;
    $result = mysqli_query($conn, $sql);
    
    if(!$result) {
        return [];
    }
    
    $rows = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $rows[] = $row;
    }
    return $rows;
}

// ===== FUNCTION AMBIL KONTAK =====
function getKontak() {
    global $conn;
    $result = mysqli_query($conn, "SELECT * FROM kontak WHERE id = 1");
    if($result && mysqli_num_rows($result) > 0) {
        return mysqli_fetch_assoc($result);
    }
    return [
        'telepon' => '(021) 1234-5678',
        'email' => 'info@afiastore.com',
        'jam_operasional' => 'Senin - Sabtu: 09.00 - 18.00',
        'whatsapp' => '6287875196663',
        'instagram' => 'afia_cake',
        'alamat' => 'Jl. Contoh No. 123, Jakarta Selatan'
    ];
}

// ===== AMBIL DATA KONTAK =====
$kontak = getKontak();

// ===== FUNCTION RUPIAH =====
function rupiah($angka) {
    $angka = $angka ?? 0;
    return 'Rp ' . number_format($angka, 0, ',', '.');
}

// ===== FUNCTION CEK STOK =====
function cekStok($id) {
    global $conn;
    $result = mysqli_query($conn, "SELECT stok FROM products WHERE id = $id");
    if($result && mysqli_num_rows($result) > 0) {
        $data = mysqli_fetch_assoc($result);
        return $data['stok'] ?? 0;
    }
    return 0;
}

// ===== FUNCTION CEK APAKAH SUDAH LOGIN =====
function isLoggedIn() {
    return isset($_SESSION['customer']);
}
?>