<!DOCTYPE html>
<html lang="en">
<head>
  <title>Fatura oluşturma Yiğit Tasarım</title>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css">
  <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.1.1/jquery.min.js"></script>
  <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js"></script>
  <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.1.1/jquery.min.js"></script>
  <link rel="stylesheet" href="https://www.w3schools.com/lib/w3.css">
<script>

</script>



  <style>


  body {

	  background-color:#FFFFE0;

  }

  .kaydir {


	  margin-left:20px;

  }

  input:focus:invalid{
    border:solid 2px #FF0000;
	background-color:pink;
	color:white;
}

input:focus:valid{
    border:solid 2px #18E109;
    background-color:lightgreen;
	color:black;
}

.border{

	border:1px solid black;

}

.control {

	width:150px;
	height:30px;
	border-radius:5px;

}

  .satir{

	  margin:auto;
	  width:1150px;

  }


  </style>

</head>
<body>

<form action="earsivxml.php" method="POST">
<div class="container">
 <h2>Fatura Oluşturma İşlemleri</h2><br>
  <p>Gönderici  Bilgilerini Giriniz</p>


      <div class="col-xs-4">
	  <input class="form-control border" type="text" name="gunvan" placeholder="Gönderici Unvan" required>
      </div>

	  <div class="col-xs-2">

        <input class="form-control border" type="text" name="gvkn" placeholder="Gönderici VKN" required>
      </div>

	   <div class="col-xs-2">

        <input class="form-control border" type="text" name="gtel" placeholder="Gönderici Tel" required>
      </div>

	   <div class="col-xs-4">

        <input class="form-control border" type="text" name="gsokak" placeholder="Gönderici Sokak" required>
      </div>

	  <div class="col-xs-2">

        <input class="form-control border" type="text" name="gbina" placeholder="Gönderici Bina" required>
      </div>

     <div class="col-xs-2">

        <input class="form-control border" type="text" name="gbinano" placeholder="Gönderici Bina no" required>
      </div>
	   <div class="col-xs-2">

        <input class="form-control border" type="text" name="gil" placeholder="Gönderici il" required>
      </div>
	   <div class="col-xs-2">

        <input class="form-control border" type="text" name="gilce" placeholder="Gönderici ilce" required>
      </div>
	   <div class="col-xs-2">

        <input class="form-control border" type="text" name="gulke" placeholder="Gönderici ulke" required>
      </div>
	   <div class="col-xs-2">

        <input class="form-control border" type="text" name="gweb" placeholder="Gönderici web" required>
      </div>
	   <div class="col-xs-2">

        <input class="form-control border" type="text" name="gvergidr" placeholder="Gönderici vergi daire" required>
      </div>
	   <div class="col-xs-2">

        <input class="form-control border" type="text" name="gmersis" placeholder="Gönderici mersis" required>
      </div>
	    <div class="col-xs-2">

        <input class="form-control border" type="text" name="gposta" placeholder="Gönderici Eposta" required>
      </div>




</div>





<br>
<br>
<div class="container">

  <p>Alıcı  Bilgilerini Giriniz</p>


      <div class="col-xs-4">
	  <input class="form-control border" type="text" name="aunvan" placeholder="alıcı Unvan" required>
      </div>

	  <div class="col-xs-2">

        <input class="form-control border" type="text" name="avkn" placeholder="alıcı VKN" required>
      </div>

	   <div class="col-xs-2">

        <input class="form-control border" type="text" name="atel" placeholder="alıcı Tel" required>
      </div>

	   <div class="col-xs-4">

        <input class="form-control border" type="text" name="asokak" placeholder="alıcı Sokak" required>
      </div>

	  <div class="col-xs-2">

        <input class="form-control border" type="text" name="abina" placeholder="alıcı Bina" required>
      </div>

     <div class="col-xs-2">

        <input class="form-control border" type="text" name="abinano" placeholder="alıcı Bina no" required>
      </div>
	   <div class="col-xs-2">

        <input class="form-control border" type="text" name="ail" placeholder="alıcı il" required>
      </div>
	   <div class="col-xs-2">

        <input class="form-control border" type="text" name="ailce" placeholder="alıcı ilce" required>
      </div>
	   <div class="col-xs-2">

        <input class="form-control border" type="text" name="aulke" placeholder="alıcı ulke" required>
      </div>
	   <div class="col-xs-2">

        <input class="form-control border" type="text" name="aweb" placeholder="alıcı web" required>
      </div>
	   <div class="col-xs-2">

        <input class="form-control border" type="text" name="avergidr" placeholder="alıcı verai daire" required>
      </div>
	   <div class="col-xs-2">

        <input class="form-control border" type="text" name="amersis" placeholder="alıcı mersis" required>
      </div>

	  <div class="col-xs-2">

        <input class="form-control border" type="text" name="aposta" placeholder="alıcı posta" required>
      </div>


</div>
<br>
<br>
<div class="satir">

  <p>Fatura oluşturmak için aşağıdaki alanları giriniz</p>


      <div class="col-xs-4">
	  <input class="form-control border" type="text" name="mal" placeholder="Mal hizmet" required>
      </div>

	  <div class="col-xs-1">

        <input class="form-control border" type="text" name="miktar" placeholder="Miktar" required>
      </div>

      <div class="col-xs-1">

        <input class="form-control border" type="text" name="adet" placeholder="Adet" value="adet" required>
      </div>

	  <div class="col-xs-1">

        <input class="form-control border" type="text" name="birim" placeholder="birim">
      </div>
	  <div class="col-xs-1">

        <input class="form-control border" type="text" name="kdvoran" placeholder="kdvoran" value="%18">
      </div>

	  <div class="col-xs-1">

        <input class="form-control border" type="text" name="kdvtutar" placeholder="kdvtutar">
      </div>

	  <div class="col-xs-2">

        <input class="form-control border" type="text" name="toplam" placeholder="toplam">
      </div>


	  <br><br><br>



</div>
<br>
<br>
<div class="container">

  <p>Fatura Özellikleri</p>



      <label>Fatura Senaryosu:</label>
      <select style="width:150px" class="control" name="fsenaryo" >
        <option>TICARIFATURA</option>
        <option>TEMELFATURA</option>

      </select>
	  <label>Fatura Tipi:</label>
      <select  style="width:150px" name="ftipi" class="control">
        <option>SATIS</option>
        <option>IADE</option>
        <option>TEVKIFAT</option>
        <option>ISTISNA</option>
      </select>






</div><br>
<div class="container">

  <p>Fatura oluşturmak için aşağıdaki alanları giriniz</p>


      <div>

	  <textarea class="form-control border" rows="5" col="10" name="not" placeholder="Not Giriniz"></textarea>
      </div>




</div><br>

<div class="container">

  <button type="submit" class="btn btn-success">Fatura Oluştur ve Gönder</button>
 <button type="reset" class="btn btn-info">Temizle</button>




</div>

</form>

</body>
</html>
