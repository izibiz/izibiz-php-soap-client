<html>
<body>
<div class="body"  style="width: 700px; height: 200px; margin-left: 100px;">
  <div class="form-body" style="width: 300px; float: left;">
    <h1>Login</h1>
<form name="uyegiris" action="login.php" method="post" align="center">
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
			<td colspan="2"><input type="submit" value="Gönder" name="gonder" style="float:right;"/></td>
		</tr>
	</table>
</form>
</div>
<?php

    include '../servisLinkleri.php';
    $sessıon_ID="";
    error_reporting(0);
    if (isset($_SERVER['HTTP_ORIGIN'])) {
        header("Access-Control-Allow-Origin: {$_SERVER['HTTP_ORIGIN']}");
        header('Access-Control-Allow-Credentials: true');
        header('Access-Control-Max-Age: 86400');
           // cache for 1 day
    }

    // Access-Control headers are received during OPTIONS requests
    if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {

        if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_METHOD']))
            header("Access-Control-Allow-Methods: GET, POST, OPTIONS");

        if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS']))
            header("Access-Control-Allow-Headers:{$_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS']}");

        exit(0);
    }


    //http://stackoverflow.com/questions/15485354/angular-http-post-to-php-and-undefined
    $postdata = file_get_contents("php://input");
    if (isset($postdata)) {
        $request = json_decode($postdata);
        $username = $_POST['username'];//"izibiz-dev";//$request->username;
        $password = $_POST['password'];//"izi321";//$request ->password;


        if ($username != "" && $password != "" ) {
			 //echo "kullanıcı adı:" .$username;
			 //echo "şifre:".$password;



		$trace = true;
		$exceptions = false;

			$xml_array -> REQUEST_HEADER -> SESSION_ID  = '';

			$xml_array -> USER_NAME = $username;
			$xml_array -> PASSWORD = $password;

try
{
   $client = new SoapClient($eFatura, array('trace' => $trace, 'exceptions' => $exceptions));
   //echo "Client oluşturuldu";
   $response = $client->Login($xml_array);
   $sessıon_ID=$response->SESSION_ID;

   ?></br><?php
   //echo $sessıon_ID;?></br>
    <div class="sesion-body" style="width: 381px; float: right; margin-top: 58px;">
  <table>
    <tr>
      <td>Sesion ID:</td>
      <td> <input type="text" name="sessionId" value="<?php echo $sessıon_ID; ?>" style="width: 270px;" readonly/></td>
    </tr>
  </table>
</div>
</div>
</body>
</html>
   <?php
   echo $response->faultstring;

}

catch (SoapFault $e)
{
   echo "Error!";
   echo $e -> getMessage ();
   echo 'Last response: '. $client->__getLastResponse();
}



$json = json_encode($response);


echo $json;




        }
        else {
           // echo "Empty username parameter!";

        }
    }
    else {
        echo "Not called properly with username parameter!";
    }



?>
