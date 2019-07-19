<html>
 <head>
  <title>IZIBIZ Webservice Entegrasyon Projesi - PHP</title>
 </head>
 <body>
 		<?php
		echo '<h1>IZIBIZ Webservice Entegrasyon Projesi - PHP</h1>';
		echo '<h2>MÜKELLEF SERVİSİ (Kimlik Doğrulama Servisi)</h2>';
 		echo '<a href="auth/authentication.php">Oturum Aç / Kapat</a><br>';
		echo '<a href="auth/get-gib-user-list.php">Mükellef Listesi Çek</a><br>';
		echo '<h2>E-FATURA SERVİSİ</h2>';
    echo '<a href="efatura/einvoice-services.php">E-Fatura Servis Demo</a><br>';
    echo 'E-Fatura ekranlarında kullanılacak servislerin kullanımı gösterilmektedir.<br>';
	 

		echo '<h2>E-ARŞİV FATURA SERVİSİ</h2>';
		echo '<a href="earsiv/create-earsiv.php">E-Arşiv Fatura Oluştur</a><br>';
		echo '<a href="earsiv/load-earchive.php">E-Arşiv Fatura Portale Yükle</a><br>';
 		echo '<a href="earsiv/send-earchive.php">E-Arşiv Fatura Gönder</a><br>';
		echo '<a href="earsiv/cancel-earsiv.php">E-Arşiv Fatura İptal Et</a><br>';
 ?>
 </body>
</html>
