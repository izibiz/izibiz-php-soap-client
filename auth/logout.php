<?php

    ini_set('display_errors','on');
    error_reporting(0);
     include 'servisLinkleri.php';
    if (isset($_SERVER['HTTP_ORIGIN'])) {
        header("Access-Control-Allow-Origin: {$_SERVER['HTTP_ORIGIN']}");
        header('Access-Control-Allow-Credentials: true');
        header('Access-Control-Max-Age: 86400');    // cache for 1 day
    }

    // Access-Control headers are received during OPTIONS requests
    if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {

        if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_METHOD']))
            header("Access-Control-Allow-Methods: GET, POST, OPTIONS");         

        if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS']))
            header("Access-Control-Allow-Headers:        {$_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS']}");

        exit(0);
    }

    if(isset($_POST['sessionId'])){
    //http://stackoverflow.com/questions/15485354/angular-http-post-to-php-and-undefined
    //$postdata2 = file_get_contents("/input");
    //if (isset($postdata2)) {
        //$request = json_decode($postdata2);
        $sessionID=$_POST['sessionId'];
        //echo $sessionID;

        if (1) {
	
		$trace = true;
		$exceptions = true;
		
			$xml_array -> REQUEST_HEADER -> SESSION_ID  = $sessionID;
            
	

try
{
   $client = new SoapClient($eFatura, array('trace' => $trace, 'exceptions' => $exceptions));
   $response = $client->Logout($xml_array);
   $a=$response->REQUEST_RETURN->RETURN_CODE;
   //$a=$response->REQUEST_RETURN;
   //echo $a;
}
catch (Exception $e)
{
   echo "Error!";
   echo $e -> getMessage ();
   echo 'Last response: '. $client->__getLastResponse();
}


if($a==0){
    echo "Başarılı bir şekilde çıkış yapıldı";
    //echo $a;
}else{
    echo "string";
}
 
$json = json_encode($response);

//echo "string";
//echo '<br>'.$json;


        	

            
        }
        else {
            echo "Empty username parameter!";
        }
    //}
    //else {
       // echo "Not called properly with username parameter!";
    //}
    }
    
    
    
?>