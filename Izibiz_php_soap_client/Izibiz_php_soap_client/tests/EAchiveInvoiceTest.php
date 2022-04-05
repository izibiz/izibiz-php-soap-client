<?php
ini_set("soap.wsdl_cache_enabled", "0");

include './app/EArchiveInvoiceTemplate.php';
$GLOBALS['xmlEArchiveInvoice'] = $xmlEArchiveInvoice;
$GLOBALS['Id'] = $Id;
$GLOBALS['Uuid'] = $uuid;

$operations = new Operations();
$homeFilePath = $operations->homeFileOpen();

class EAchiveInvoiceTest extends PHPUnit\Framework\TestCase
{

  public function testEArchive()
  {
    $request = array(
      'REQUEST_HEADER' => array(
        "SESSION_ID" => $GLOBALS['sessionId'],
        "COMPRESSED" => 'Y',
        "APPLICATION_NAME" => 'Izibiz_php_soap_client.Application'
      ),
      'INVOICEID' => 'b26ebc99-934e-4310-93e8-6b37525f6d24',
      'PORTAL_DIRECTION' => 'OUT',
      'PROFILE' => 'HTML'
    );
    $GLOBALS['client'] = new SoapClient($GLOBALS['baseURL'] . '/EIArchiveWS/EFaturaArchive?wsdl', array('trace' => 1, 'exceptions' => 1));
    $response = $GLOBALS['client']->__soapCall("ReadFromArchive", array($request));
    $fileHomePath = $GLOBALS['homeFilePath'];
    $GLOBALS['EArchiveFolderPath'] = "$fileHomePath\EArchive";
    $GLOBALS['operations']->fileExists($GLOBALS['EArchiveFolderPath']);

    $deneme = $response->INVOICE;
    $path = dirname(__FILE__, 2);
    echo "$path\\" . $request['INVOICEID'] . ".zip";
    file_put_contents("$path\\" . $request['INVOICEID'] . ".zip", (array)$deneme);

    $unzip = new ZipArchive;
    $out = $unzip->open($request['INVOICEID'] . ".zip");

    for ($i = 0; $i <    $unzip->numFiles; $i++) {
      $stat =    $unzip->statIndex($i);
      print_r(basename($stat['name']) . PHP_EOL);

      if ($out === TRUE) {
          $unzip->extractTo($path."\\".$request['INVOICEID'].".zip");
       // $unzip->extractTo($path . '\\' . basename($stat['name']));
        $unzip->close();
        unlink($request['INVOICEID'] . ".zip");
        echo 'File unzipped';
      } else {
        echo 'Error';
      }


      // $unzip2=new ZipArchive;
      // $outt = $unzip->open(basename( $stat['name'] ));

      // if($outt==TRUE){
      //   $unzip2->extractTo($path.'\\'.basename( $stat['name'] ));
      //   $unzip2->close();
      // }

    }
  }

  public function testWriteToArchieveExtended()
  {
    $InvoiceA = new SimpleXMLElement($GLOBALS['xmlEArchiveInvoice']);
    $InvoiceA->registerXPathNamespace('cbc', 'urn:oasis:names:specification:ubl:schema:xsd:CommonBasicComponents-2');
    $GLOBALS['content'] = str_replace('<?xml version="1.0"?>', '', $InvoiceA->asXML());


    $output = 'EArchiveSend.zip';
    $zip = new ZipArchive;
    if ($zip->open($output, ZipArchive::CREATE) == FALSE) {
      die("$output");
    }
    $zip->addFromString($GLOBALS['Id'] . '.xml', $GLOBALS['content']);
    $zip->close();
    $GLOBALS['EArchiveZipContent'] = file_get_contents("EArchiveSend.zip");
    unlink('EArchiveSend.zip');

    $request = array(
      'REQUEST_HEADER' => array(
        "SESSION_ID" => $GLOBALS['sessionId'],
        "COMPRESSED" => 'Y',
        "APPLICATION_NAME" => 'Izibiz_php_soap_client.Application'
      ),
      'ArchiveInvoiceExtendedContent' => array(
        'INVOICE_PROPERTIES' => array(
          'EARSIV_FLAG' => 'Y',
          'EARSIV_PROPERTIES' => array(
            'EARSIV_TYPE' => 'NORMAL',
            'SUB_STATUS' => 'NEW', //Taslak olarak gönderilmek istenirse NEW yerine DRAFT yazılmalıdır.
          ),
          'INVOICE_CONTENT' => $GLOBALS['EArchiveZipContent'],
        )
      )
    );
    $GLOBALS['client'] = new SoapClient($GLOBALS['baseURL'] . '/EIArchiveWS/EFaturaArchive?wsdl', array('trace' => 1, 'exceptions' => 1));
    $response = $GLOBALS['client']->__soapCall("WriteToArchiveExtended", array($request));
    if (isset($response->ERROR_TYPE)) {
      $this->assertNull($response->ERROR_TYPE);
    }
    $this->assertEquals($response->REQUEST_RETURN->RETURN_CODE, 0);
  }

  public function testEArchiveInvoiceList()
  { 
    $request = array(
      'REQUEST_HEADER' => array(
        "SESSION_ID" => $GLOBALS['sessionId'],
        "COMPRESSED" => 'Y',
        "APPLICATION_NAME" => 'Izibiz_php_soap_client.Application'
      ),
      'LIMIT' => 2,
      "START_DATE" => date("Y-m-d", mktime(0, 0, 0, date("m") - 3, date("d"), date("Y"))) . 'T' . date("h:i:s", mktime(date("H"), date("i"), date("s"), 0, 0, 0)),
      "END_DATE" => date("Y-m-d") . 'T' . date("h:i:s"),
      'HEADER_ONLY' => 'N',
    'CONTENT_TYPE'=>'XML',
    'READ_INCLUDED'=>'Y'
    );

    $response = $GLOBALS['client']->__soapCall("GetEArchiveInvoiceList", array($request));
    // echo ($GLOBALS['client']->__getLastResponse());
    if (isset($response->ERROR_TYPE)) {
      $this->assertNull($response->ERROR_TYPE);
    }
    $this->assertEquals($response->REQUEST_RETURN->RETURN_CODE, 0);

    $GLOBALS['path'] =    $GLOBALS['EArchiveFolderPath'] . '\\EArchiveInvoices';
    $GLOBALS['EArchiveInvoices'] = [];
    file_put_contents("C:\Users\meryem.aksu\Downloads\.Izibiz_php_soap_client\EArchive\EArchiveInvoices\DENEME.xml",array($response->INVOICE));
    foreach ($response->INVOICE as $archiveinvoice) {
      if ($request['HEADER_ONLY'] == 'N') {
        array_push($GLOBALS['EArchiveInvoices'], $archiveinvoice);
        $fileexists = $GLOBALS['operations']->fileExists($GLOBALS['path']);
        $saveToDisk = $GLOBALS['path'] . "\\" . $archiveinvoice->HEADER->UUID . "-" . $archiveinvoice->HEADER->INVOICE_ID . ".xml";
       
     // echo gettype($archiveinvoice->{'CONTENT'});
      
       //// echo $GLOBALS['path']."\\" . $archiveinvoice->INVOICE_ID . ".zip";
        //file_put_contents( $GLOBALS['path'] ."\\" .  $archiveinvoice->INVOICE_ID. ".zip", (array)$content);
    
    //     $unzip = new ZipArchive;
    //     $out = $unzip->open( $archiveinvoice->INVOICE_ID. ".zip");

    //     if ($out === TRUE) {
    //       $unzip->extractTo(getcwd());
    //       $unzip->close();
    //       unlink($archiveinvoice->INVOICE_ID . ".zip");
    //       echo 'File unzipped';
    //     } else {
    //       echo 'Error';
    //     }
    //  //   file_put_contents($saveToDisk, (array)$archiveinvoice->CONTENT);
    //   } {
    //    // file_put_contents($saveToDisk, $GLOBALS['client']->__getLastResponse());
       }
     }
  }

  public function CancelEArchiveInvoice()
  { //testCancelEArchiveInvoice()
    $request = array(
      'REQUEST_HEADER' => array(
        "SESSION_ID" => $GLOBALS['sessionId'],
        "COMPRESSED" => 'Y',
        "APPLICATION_NAME" => 'Izibiz_php_soap_client.Application'
      ),
      'CancelEArsivInvoiceContent' => array(
        'FATURA_ID' => 'ABC2022000000031',
        'FATURA_UUID' => 'b629a814-7572-4a30-b827-14ae088373a0',
        'DELETE_FLAG' => 'Y', //Raporlandı konumundaki faturaların iptalinde bu alan yazılmamalı raporlanacak durumunda olanlarda ise bu alan belirtilmelidir. Bu alan ekliyken istek yapıldığında iptal edilen fatura portalde raporlanmasın kartına düşmektedir.
        'IPTAL_NOTU' => 'deneme iptali'
      )
    );

    $response = $GLOBALS['client']->__soapCall("CancelEArchiveInvoice", array($request));
    if (isset($response->ERROR_TYPE)) {
      $this->assertNull($response->ERROR_TYPE);
    }
    $this->assertEquals($response->REQUEST_RETURN->RETURN_CODE, 0);
  }
  public function testGetEmailEarchiveInvoice()
  {
    $request = array(
      'REQUEST_HEADER' => array(
        "SESSION_ID" => $GLOBALS['sessionId'],
        "COMPRESSED" => 'Y',
        "APPLICATION_NAME" => 'Izibiz_php_soap_client.Application'
      ),
      'FATURA_UUID' => 'ebe86637-ca04-4388-ace2-83f9db321878',
      'EMAIL' => 'b@gmail.com, c@izibiz.com.tr',
    );
    $response = $GLOBALS['client']->__soapCall("GetEmailEarchiveInvoice", array($request));
    if (isset($response->ERROR_TYPE)) {
      $this->assertNull($response->ERROR_TYPE);
    }
    $this->assertEquals($response->REQUEST_RETURN->RETURN_CODE, 0);
  }

  public function GetEArchiveReport()
  {
    $request = array(
      'REQUEST_HEADER' => array(
        "SESSION_ID" => $GLOBALS['sessionId'],
        "COMPRESSED" => 'Y',
        "APPLICATION_NAME" => 'Izibiz_php_soap_client.Application'
      ),
      'REPORT_PERIOD' => '202001',
      'REPORT_STATUS_FLAG' => 'N'
    );

    $response = $GLOBALS['client']->__soapCall("GetEArchiveReport", array($request));
    $fileHomePath = $GLOBALS['homeFilePath'];
    $GLOBALS['EArchiveFolderPath'] = "$fileHomePath\EArchive";
    $GLOBALS['operations']->fileExists($GLOBALS['EArchiveFolderPath']);
    $GLOBALS['EArchiveReport'] = $GLOBALS['EArchiveFolderPath'] . "\EArchiveReport";
    $GLOBALS['operations']->fileExists($GLOBALS['EArchiveReport']);
    file_put_contents($GLOBALS['EArchiveReport'] . '\\' . $request['REPORT_PERIOD'] . '.xml', json_encode((array)$response->REPORT));
    if ($request['REPORT_STATUS_FLAG'] == 'N') {
      file_put_contents('C:\Users\meryem.aksu\Desktop\Hira.xml', json_encode((array)$response->INVOICE), FILE_APPEND);
    }
    $GLOBALS['ReportNo'] = [];
    foreach ($response->REPORT as $report) {
      array_push($GLOBALS['ReportNo'], $report->REPORT_NO);
    }
  }

  public function ReadEArchiveReport()
  { //BAK
    $request = array(
      'REQUEST_HEADER' => array(
        "SESSION_ID" => $GLOBALS['sessionId'],
        //"COMPRESSED" => 'Y',
        "APPLICATION_NAME" => 'Izibiz_php_soap_client.Application'
      ),
      'RAPOR_NO' => $GLOBALS['ReportNo'][0],
    );
    $response = $GLOBALS['client']->__soapCall("ReadEArchiveReport", array($request));


    $resBase = $response->{0}->{'EARCHIVEREPORT'};
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
