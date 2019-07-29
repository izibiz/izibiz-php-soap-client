<?php   
    include '../servisLinkleri.php';
    error_reporting(0);
    //$eFatura = 'https://efaturadev.izibiz.com.tr:443/EFaturaOIB?wsdl';

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
        $compressed='Y';
        

        if (1) {
        
        
            
        $okuma = false;
        $trace = true;
        $exceptions = false;
        
            
        
            $xml_array -> REQUEST_HEADER -> SESSION_ID  = $session;
            $xml_array -> REQUEST_HEADER -> APPLICATION_NAME  = 's';
            $xml_array -> REQUEST_HEADER -> COMPRESSED = $compressed;
            $xml_array -> INVOICE_SEARCH_KEY -> READ_INCLUDED  = 'true' ;
            $xml_array -> INVOICE_SEARCH_KEY -> DIRECTION  = 'OUT';
            $xml_array -> INVOICE_SEARCH_KEY -> LIMIT = '';
            $xml_array -> HEADER_ONLY  = 'N';
            

try
{
    
   $client = new SoapClient($eFatura, array('trace' => $trace, 'exceptions' => $exceptions));
   $response = $client->GetInvoice($xml_array);
      //echo "<pre>";
      //print_r($response);

     foreach ($response->INVOICE as $list) {
            echo "<tr id='baslik' align='left'>";
        $faturano=$list->ID;
            echo "<td>".$faturano."</td>";
        $UUID=$list->UUID;
        $tarihl=$list->HEADER->ISSUE_DATE;
            $date=date_create($tarihl);
            $tarih=date_format($date,"d-m-Y ");
            echo "<td>".$tarih."</td>";
        $vkn=$list->HEADER->SENDER;
            echo "<td>".$vkn."</td>";
        $customer=$list->HEADER->CUSTOMER;
            echo "<td>".$customer."</td>";
        $tip=$list->HEADER->PROFILEID."-".$list->HEADER->INVOICE_TYPE_CODE;
            echo "<td>".$tip."</td>";
        $tutars=$list->HEADER->PAYABLE_AMOUNT->_;
            $tutar=number_format($tutars, 2, ',', '.');
            echo "<td>".$tutar."</td>";
        $birim=$list->HEADER->PAYABLE_AMOUNT->currencyID;
            echo "<td>".$birim."</td>";
        $alınma_z=$list->HEADER->CDATE ;
            $date=date_create($alınma_z);
            $alınma_zamanı=date_format($date,"d-m-Y ");
            echo "<td>".$alınma_zamanı."</td>";
        $durum=$list->HEADER->STATUS;
            echo "<td>".$durum."</td>";
            echo "<td><input type='checkbox' value='s' name='sil[]'></td>";
            echo "</tr>";
        $content=$list->CONTENT->_;  
        if($compressed == 'Y'){
            saveFolderZip();
            saveDiskZip($faturano,$content);
            saveUnZip($faturano);
            markInvoice($faturano,$UUID);
        }else if ($compressed == 'N'){
            saveFolder();    
            saveDisk($faturano,$content);
            markInvoice($faturano,$UUID);
        }

    }

       
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
 function saveDisk($_faturano,$_content){
    touch('Content/'.$_faturano.'.xml');
    $dosya = fopen('Content/'.$_faturano.'.xml', 'w');
    fwrite($dosya, $_content);
    fclose($dosya);
}
 function saveFolder(){
    $klasorYol = "Content";
    if(!file_exists($klasorYol)) {
        $olustur = mkdir("Content");
            if($olustur) {
              //  echo "Klasör Oluşturuldu.";
            }else{ 
              //  echo "Klasör Oluşturulamadı";
            }
    }else{

    }  
}
function saveDiskZip($_faturano,$_content){
    touch('ContentZip/'.$_faturano.'.zip');
    $dosya = fopen('ContentZip/'.$_faturano.'.zip', 'w');
    fwrite($dosya, $_content);
    fclose($dosya);
} 
function saveFolderZip(){
    $klasorYol = "ContentZip";
    if(!file_exists($klasorYol)) {
        $olustur = mkdir("ContentZip");
            if($olustur) {
              //  echo "Klasör Oluşturuldu.";
            }else{ 
              //  echo "Klasör Oluşturulamadı";
            }
    }else{

    }  
} 
function saveUnZip($_faturano){
   $zip = new ZipArchive;
   $res = $zip->open('ContentZip/'.$_faturano.'.zip');
   if ($res === TRUE) {
     //echo 'Tamam';
     //echo $_faturano;
     $zip->extractTo('contentUnZip');
     $zip->close();
}  else {
    echo 'Olmadı, kod:' . $res;
}
}  

 function markInvoice($_faturano,$_UUID){
   if(isset($_faturano) && isset($_UUID)){
            $_value='READ';
            //echo $_value;
            include 'mark-invoice.php';
        }else{
            $_value='UNREAD';
            //echo $_value;
            include 'mark-invoice.php';
        }
}   
       
    
?>