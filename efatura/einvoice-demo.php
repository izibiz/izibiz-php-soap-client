<?php
//echo '<br><br><br><br><br><br><br><br>';
echo 'Fatura Listesi<br>';
echo '
<li>Table eklenecek. tabloda listenen kayıtlar multiselect olacak. yani birden fazla kayıt seçilerek işlem yapılabilecek.
<li>sayfa açılınca boş gelecek. Tablonun tarafında aşağıda ki işlemler yapılacak:<br>
<li>YENİ FATURALARI AL : butonu eklenecek. Tıklanınca GetInvoice servisi çağırılacak. dönen sonuç tabloda gösterilecek. Alınan faturalar diske kaydedilecek.<br>
Çekilen faturalar için MarkInvoice metodu tetiklenecek.<br>
<li>DURUM SORGULA butonu eklenecek: Listeden seçilen faturaların durumunu GetInvoiceStatusAll metodu çağrılarak durum sorgulaması yapılacak. ve sonuç datatableda güncellenecek.
<li>YANIT GÖNDER butonu eklenecek: seçilen fatura ticari fatura ise dialogda (KABUL, RED ) radio butonu ve AÇIKLAMA alanı gösterilecek.
Gönder butonu tıklanınca SendInvoiceResponseWithServerSign metodu tetiklenecek.

';

echo '<br>';
echo '<a href="efatura/create-invoice.php">E-Fatura Oluştur</a><br>';
?>
