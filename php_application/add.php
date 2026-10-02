<?php 
// Formun gönderilip gönderilmediğini kontrol ediyoruz
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // Klasör yolunu ve dosya adını güvenli hale getiriyoruz
    $target_dir = "images/"; 
    $pic = isset($_FILES['photo']['name']) ? basename($_FILES['photo']['name']) : '';
    $target_file = $target_dir . $pic; 
    
    // Post verilerini alıyoruz (Boş gelme durumuna karşı önlem)
    $name  = isset($_POST['name']) ? $_POST['name'] : ''; 
    $email = isset($_POST['email']) ? $_POST['email'] : ''; 
    $phone = isset($_POST['phone']) ? $_POST['phone'] : ''; 

    $servername = "://amazonaws.com";
    $username = "projemaster";
    $password = "master1234";
    $dbname = "proje";

    // Bağlantı oluşturma
    $conn = new mysqli($servername, $username, $password, $dbname);

    // Bağlantı kontrolü
    if ($conn->connect_error) {
        die("Veritabanı bağlantısı başarısız: " . $conn->connect_error);
    }

    // PHP 8 Uyumlu ve Güvenli SQL Hazırlığı (SQL Injection Engelleme)
    $stmt = $conn->prepare("INSERT INTO visitors (name, email, phone, photo) VALUES (?, ?, ?, ?)");
    
    if ($stmt) {
        // Parametreleri bağlıyoruz (4 adet string tipi veri için "ssss")
        $stmt->bind_param("ssss", $name, $email, $phone, $pic);
        
        // Sorguyu çalıştırıyoruz
        if ($stmt->execute() === TRUE) {
            echo "Kayıt başarıyla yaratildi. ";
            
            // Sunucuya dosya yükleme işlemi (Hatalı olan 'uploadedfile' tanımı 'photo' olarak düzeltildi)
            if (!empty($pic) && move_uploaded_file($_FILES['photo']['tmp_name'], $target_file)) { 
                echo "Dosya " . htmlspecialchars($pic) . " başarılı bir şekilde sunucuya yüklendi ve diğer kayıtlar da veritabanına başarılı şekilde girildi."; 
            } else { 
                echo "Kayıt yapıldı ancak resim dosyası yüklenirken bir hata oluştu. Lütfen 'images/' klasörünün yazma izinlerini kontrol edin."; 
            }
        } else {
            echo "Veritabanı kayıt hatası: " . $stmt->error;
        }
        
        $stmt->close();
    } else {
        echo "Sorgu hazırlama hatası: " . $conn->error;
    }

    // Bağlantıyı tüm işlemler bittikten sonra kapatıyoruz
    $conn->close();
}
?>

<p>
<a href="view.php">Kayıtları Gör</a>
</p>
