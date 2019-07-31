
<?php
$session_ID="";


    include '../servisLinkleri.php';
    ini_set('display_errors','on');
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
   if(isset($_POST["username"])){

    //http://stackoverflow.com/questions/15485354/angular-http-post-to-php-and-undefined
    //$postdata = file_get_contents("/input");
    if (isset($_POST)) {
       // $request = json_decode($postdata);
        $username = $_POST['username'];//"izibiz-dev";//$request->username;
        $password = $_POST['password'];//"izi321";//$request ->password;


        if ($username != "" && $password != "" ) {
			 //echo "kullanıcı adı:" .$username;
			 //echo "şifre:".$password;



		$trace = true;
		$exceptions = false;

			$xml_array -> REQUEST_HEADER -> SESSION_ID  = '';
      $xml_array -> REQUEST_HEADER -> APPLICATION_NAME  = 's';
			$xml_array -> USER_NAME = $username;
			$xml_array -> PASSWORD = $password;
     
try
{
  $client = new SoapClient($eFatura, array('trace' => $trace, 'exceptions' => $exceptions));
   //echo "Client oluşturuldu";
   $response = $client->Login($xml_array);
   $session_ID=$response->SESSION_ID;

  
   echo $response->faultstring;
   
}

catch (SoapFault $e)
{
   echo "Error!";
   echo $e -> getMessage ();
   echo 'Last response: '. $client->__getLastResponse();
}



$json = json_encode($response);

//echo $json;

        }
        else {
            echo "Empty username parameter!";
          
        }
    }
    else {
        echo "Not called properly with username parameter!";
    }
  }
    else{}

?>
