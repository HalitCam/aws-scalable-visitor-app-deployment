<?php 
$servername = "://amazonaws.com";
$username = "projemaster";
$password = "master1234";
$dbname = "proje";

// PHP 8 Uyumlu Nesne Yönelimli Bağlantı
$conn = new mysqli($servername, $username, $password, $dbname);

// Bağlantı kontrolü
if ($conn->connect_error) {
    die("Veritabanı bağlantısı başarısız: " . $conn->connect_error);
}

$sql    = 'SELECT * FROM visitors';
// PHP 8 Uyumlu Nesne Yönelimli Sorgu Çalıştırma
$query  = $conn->query($sql);

if ($query) {
    // PHP 8 Uyumlu fetch_assoc kullanımı
    while ($info = $query->fetch_assoc()) { 
        // AWS S3 üzerindeki gerçek resim URL formatı (Konsol linki değil, doğrudan erişim linki)
        $s3_url = "https://amazonaws.com" . $info['photo'];
        
        // HTML XSS güvenliği için htmlspecialchars eklendi
        echo "<img src='" . htmlspecialchars($s3_url) . "' width='150' alt='Ziyaretçi Fotoğrafı'> <br>"; 
        echo "<b>Foto:</b> " . htmlspecialchars($info['photo']) . "<br> ";
        echo "<b>Name:</b> " . htmlspecialchars($info['name']) . "<br> "; 
        echo "<b>Email:</b> " . htmlspecialchars($info['email']) . " <br>"; 
        echo "<b>Phone:</b> " . htmlspecialchars($info['phone']) . " <hr>"; 
    }
} else {
    echo "Veri çekilirken hata oluştu: " . $conn->error;
}

$conn->close();
?>
