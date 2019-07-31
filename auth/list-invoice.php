<html>
<body>
<div class="body"  style="width: 700px; height: 200px; margin-left: 100px;">
  <div class="form-body" style="width: auto; float: left;">
    <h1>Faturalar Tablosu</h1>
<form action="list-invoice.php" method="POST">
<input type="submit" value="Yeni Fatura Al" name="yenifatura" style="margin-bottom: 10px;">
<input type="submit" value="Durum Sorgula" name="silbuton" style="margin-bottom: 10px;">
<input type="submit" value="Yanıt Gönder" name="silbuton" style="margin-bottom: 10px;">
<table border="1" id="kayitlar" cellpadding="5" width="1300px" cellspacing="0">

<tr id="baslik" align="left">

<td>Fatura No</td>
<td>Tarih</td>
<td>VKN/TCKN</td>
<td>Ünvan</td>
<td>Tip</td>
<td>Tutar</td>
<td>Birim</td>
<td>Alınma Zamanı</td>
<td>Durum</td>
<td>Seç</td>

</tr>
<?php
if(isset($_POST["yenifatura"])){
 include 'get-invoice.php';
}
?>
</table>
</form>
</div>

   
</div>
</body>
</html>  