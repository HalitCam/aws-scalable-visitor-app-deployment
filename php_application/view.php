<?php 
$servername = "php-mysql-database-instance.ccdk0q246eof.us-east-1.rds.amazonaws.com";
$username = "projemaster";
$password = "master1234";
$dbname = "proje";

// PHP 8 
$conn = new mysqli($servername, $username, $password, $dbname);

// Check Connection
if ($conn->connect_error) {
    die("Veritabanı bağlantısı başarısız: " . $conn->connect_error);
}

$sql    = 'SELECT * FROM visitors';
// query
$query  = $conn->query($sql);

if ($query) {
    // PHP 8 - fetch_assoc usage
    while ($info = $query->fetch_assoc()) { 

        $s3_url = "https://s3-eu-west-1.amazonaws.com/phpprojeimage-resized/resized-images/" . $info['photo'];
        
        // for HTML XSS security htmlspecialchars added
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

