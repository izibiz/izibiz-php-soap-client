<?php
ini_set("soap.wsdl_cache_enabled", "0");
$operations = new Operations();
$homeFilePath = $operations->homeFileOpen();

class EAchiveInvoiceTest extends PHPUnit\Framework\TestCase
{

  public function testEArchive()
  {
    $request = array(
      'REQUEST_HEADER' => array(
        "SESSION_ID" => $GLOBALS['sessionIdD'],
        "COMPRESSED" => 'Y',
        "APPLICATION_NAME" => 'Izibiz_php_soap_client.Application'
      ),
      'INVOICEID' => 'b26ebc99-934e-4310-93e8-6b37525f6d24',
      'PORTAL_DIRECTION' => 'OUT',
      'PROFILE' => 'HTML'
    );
    $GLOBALS['client'] = new SoapClient('https://efaturatest.izibiz.com.tr/EIArchiveWS/EFaturaArchive?wsdl', array('trace' => 1, 'exceptions' => 1));
    $response = $GLOBALS['client']->__soapCall("ReadFromArchive", array($request));
    $deneme = $response->INVOICE;
    $path = dirname(__FILE__, 2);
    file_put_contents("$path\deneme.zip", (array)$deneme);


    $unzip = new ZipArchive;
    $out = $unzip->open("$path\deneme.zip");

    if ($out === TRUE) {
    $unzip->extractTo(getcwd());
    $unzip->extractTo(getcwd());
      $unzip->close();
      echo 'File unzipped';
    } else {
      echo 'Error';
    }

  }

  public function WriteToArchieveExtended()
  {//testin çalışması için fatura no ve uuid değişmesi gerekmektedir
    $dosya = fopen('app/EArchiveInvoiceTemplate.xml', 'r');
    $content = file_get_contents('app/EArchiveInvoiceTemplate.xml');
    $output = 'EArchiveSendZip.zip';
    $zip = new ZipArchive;
    if ($zip->open($output, ZipArchive::CREATE) == FALSE) {
      die("$output");
    }
    $zip->addFromString('text.xml', $content);
    $zip->close();
    $GLOBALS['degis'] = file_get_contents("EArchiveSendZip.zip");
    unlink('EArchiveSendZip.zip');

   $request = array(
    'REQUEST_HEADER' => array(
      "SESSION_ID" => $GLOBALS['sessionIdD'],
      "COMPRESSED" => 'Y',
      "APPLICATION_NAME" => 'Izibiz_php_soap_client.Application'
    ),
    'ArchiveInvoiceExtendedContent'=>array(
      'INVOICE_PROPERTIES'=>array(
        'EARSIV_FLAG'=>'Y',
        'EARSIV_PROPERTIES'=>array(
          'EARSIV_TYPE'=>'NORMAL',
          'SUB_STATUS'=>'NEW',//Taslak olarak gönderilmek istenirse NEW yerine DRAFT yazılmalıdır.
        ),
        'INVOICE_CONTENT'=>$GLOBALS['degis'] ,
      )
    )
  );

  $response = $GLOBALS['client']->__soapCall("WriteToArchiveExtended", array($request));  
  if(isset($response->ERROR_TYPE)){
    $this->assertNull($response->ERROR_TYPE);
  }
 $this->assertEquals($response->REQUEST_RETURN->RETURN_CODE,0);
 
  }

  public function testEArchiveInvoiceList()
  {
    $request = array(
      'REQUEST_HEADER' => array(
        "SESSION_ID" => $GLOBALS['sessionIdD'],
        "COMPRESSED" => 'Y',
        "APPLICATION_NAME" => 'Izibiz_php_soap_client.Application'
      ),
    'LIMIT'=>10,
    "START_DATE" => date("Y-m-d", mktime(0, 0, 0, date("m")-1, date("d"), date("Y"))),
        "END_DATE" => date("Y-m-d"),
    'HEADER_ONLY'=>'N',
    );

    $response = $GLOBALS['client']->__soapCall("GetEArchiveInvoiceList", array($request));  
    //echo ($GLOBALS['client']->__getLastResponse());
  }

  public function CancelEArchiveInvoice()
  {//testCancelEArchiveInvoice()
    $request = array(
      'REQUEST_HEADER' => array(
        "SESSION_ID" => $GLOBALS['sessionIdD'],
        "COMPRESSED" => 'Y',
        "APPLICATION_NAME" => 'Izibiz_php_soap_client.Application'
      ),
      'CancelEArsivInvoiceContent'=>array(
      'FATURA_ID'=>'ABC2022000000031',
      'FATURA_UUID'=>'b629a814-7572-4a30-b827-14ae088373a0',
      'DELETE_FLAG'=>'Y',//Raporlandı konumundaki faturaların iptalinde bu alan yazılmamalı raporlanacak durumunda olanlarda ise bu alan belirtilmelidir. Bu alan ekliyken istek yapıldığında iptal edilen fatura portalde raporlanmasın kartına düşmektedir.
      'IPTAL_NOTU'=>'deneme iptali'
    ));

    $response = $GLOBALS['client']->__soapCall("CancelEArchiveInvoice", array($request));  
    if(isset($response->ERROR_TYPE)){
      $this->assertNull($response->ERROR_TYPE);
    }
   $this->assertEquals($response->REQUEST_RETURN->RETURN_CODE,0);
   
  }
  public function testGetEmailEarchiveInvoice()
  {
    $request = array(
      'REQUEST_HEADER' => array(
        "SESSION_ID" => $GLOBALS['sessionIdD'],
        "COMPRESSED" => 'Y',
        "APPLICATION_NAME" => 'Izibiz_php_soap_client.Application'
      ),
      'FATURA_UUID'=>'ebe86637-ca04-4388-ace2-83f9db321878',
      'EMAIL'=>'b@gmail.com, c@izibiz.com.tr',
    );
    $response = $GLOBALS['client']->__soapCall("GetEmailEarchiveInvoice", array($request));  
    if(isset($response->ERROR_TYPE)){
      $this->assertNull($response->ERROR_TYPE);
    }
   $this->assertEquals($response->REQUEST_RETURN->RETURN_CODE,0);
  }

  public function testGetEArchiveReport()
  {
    $request = array(
      'REQUEST_HEADER' => array(
        "SESSION_ID" => $GLOBALS['sessionIdD'],
        "COMPRESSED" => 'Y',
        "APPLICATION_NAME" => 'Izibiz_php_soap_client.Application'
      ),
    'REPORT_PERIOD'=>'202001',
    'REPORT_STATUS_FLAG'=>'N'
    );

    $response = $GLOBALS['client']->__soapCall("GetEArchiveReport", array($request));  
    $fileHomePath = $GLOBALS['homeFilePath'];
    $GLOBALS['EArchiveFolderPath'] = "$fileHomePath\EArchive";
    $GLOBALS['operations']->fileExists($GLOBALS['EArchiveFolderPath']);
    $GLOBALS['EArchiveReport']= $GLOBALS['EArchiveFolderPath']."\EArchiveReport";
    $GLOBALS['operations']->fileExists($GLOBALS['EArchiveReport']);
    file_put_contents($GLOBALS['EArchiveReport'].'\\'.$request['REPORT_PERIOD'].'.xml',json_encode((array)$response->REPORT));
    if($request['REPORT_STATUS_FLAG']=='N'){
    file_put_contents('C:\Users\meryem.aksu\Desktop\Hira.xml', json_encode((array)$response->INVOICE), FILE_APPEND);
  }
  $GLOBALS['ReportNo']=[];
  foreach($response->REPORT as $report){
    array_push($GLOBALS['ReportNo'], $report->REPORT_NO);
  }
  }

  public function ReadEArchiveReport()
  {//BAK
    $request = array(
      'REQUEST_HEADER' => array(
        "SESSION_ID" => $GLOBALS['sessionIdD'],
        //"COMPRESSED" => 'Y',
        "APPLICATION_NAME" => 'Izibiz_php_soap_client.Application'
      ),
      'RAPOR_NO'=>$GLOBALS['ReportNo'][0],
    );
    $response = $GLOBALS['client']->__soapCall("ReadEArchiveReport", array($request));  
   

    $resBase=$response->{0}->{'EARCHIVEREPORT'};
    print $resBase;

//file_put_contents("C:\Users\meryem.aksu\Desktop\New folder (2)\GetGibUserList.zip",var_export((array)json_encode($resBase)));
// $file = tempnam("tmp", "zip");
// $zip = new ZipArchive();
// $zip->open($file, ZipArchive::OVERWRITE);

// // Add contents
// $zip->addFromString('your_file_name', base64_decode($resBase));

// // Close and send to users
// $zip->close();
// header('Content-Type: application/zip');
// header('Content-Length: ' . filesize($file));
// header('Content-Disposition: attachment; filename="file.zip"');
// readfile($file);
// unlink($file);
  }
}
