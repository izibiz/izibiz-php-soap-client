<?php   
    include '../servisLinkleri.php';
    error_reporting(0);
    if (isset($_SERVER['HTTP_ORIGIN'])) {
        header("Access-Control-Allow-Origin: {$_SERVER['HTTP_ORIGIN']}");
        header('Access-Control-Allow-Credentials: true');
        
    }

    // Access-Control headers are received during OPTIONS requests
    if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {

        if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_METHOD']))
            header("Access-Control-Allow-Methods: GET, POST, OPTIONS");         

        if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS']))
            header("Access-Control-Allow-Headers:        {$_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS']}");

        exit(0);
    }


    //http://stackoverflow.com/questions/15485354/angular-http-post-to-php-and-undefined
    $postdata = file_get_contents("php://input");
    if (isset($postdata)) {
        $request = json_decode($postdata);
        //Login ekranından alınacak
        $session = 'd4b32fc1-feb0-4266-bdc4-56c6a46ea859';
        
        

        if (1) {
		
		
			
		$okuma = false;
		$trace = true;
		$exceptions = false;
		
			
		
			$xml_array -> REQUEST_HEADER -> SESSION_ID  = $session;
            $xml_array -> REQUEST_HEADER -> APPLICATION_NAME  = 's';
            $xml_array -> MARK -> value = $_value;
			$xml_array -> MARK -> INVOICE -> ID  = $faturano;
			$xml_array -> MARK -> INVOICE -> UUID  = $UUID;
            
			

try
{
    
   $client = new SoapClient($eFatura, array('trace' => $trace, 'exceptions' => $exceptions));
   $response = $client->MarkInvoice($xml_array);
    //echo "<pre>";
   //print_r($response);

     
        
         
     
       
}

catch (Exception $e)
{
   echo "Error!";
   echo $e -> getMessage ();
   echo 'Last response: '. $client->__getLastResponse();
}


            }
            else {
            echo "Empty username parameter!";
            }
    }
    else {
        echo "Not called properly with username parameter!";
    }
    
 //Sayfalandırma yapılacak   


   
  
       
    
?>