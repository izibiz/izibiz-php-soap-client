<html>
<body>
<div class="body"  style="width: 700px; height: 200px; margin-left: 100px;">
  <div class="form-body" style="width: 300px; float: left;">
    <h1>Login</h1>
<form name="uyegiris" action="authentication.php" method="post" align="center">
	<table>
		<tr>
			<td>Kullanıcı adı:</td>
			<td><input type="text" name="username" id="username" /></td>
		</tr>
		<tr>
			<td>Parola:</td>
			<td><input type="password" name="password" id="password" /></td>
		</tr>
		<tr>
			<td colspan="2"><input type="submit" value="Giriş Yap" name="Giriş Yap" style="float:right;"/></td>
		</tr>
	</table>
</form>
</div>
<?php
	include 'login.php';
 ?>
</br>
    <div class="sesion-body" style="width: 381px; float: right; margin-top: 58px;">
      <form name="uyegiris" action="authentication.php" method="post" align="center">
  <table>
    <tr>
      <td>Sesion ID:</td>
      <td> <input type="text" name="sessionId" value="<?php echo $session_ID; ?>" style="width: 270px;" readonly/></td>
    <tr>
      <td colspan="2"><input type="submit" value="Logout" name="Logout" style="float:right;"/></td>
    </tr>
    </tr>
  </table>
</form>
<?php
  include 'logout.php';
?>
</div>
</div>
</body>
</html>  