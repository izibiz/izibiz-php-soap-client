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
    $GLOBALS['EArchiveFolderPath'] = $GLOBALS['homeFilePath'] . "\EArchive";
    $GLOBALS['operations']->fileExists($GLOBALS['EArchiveFolderPath']);
    $path = $GLOBALS['EArchiveFolderPath'] . "\ReadFromArchive";
    $GLOBALS['operations']->fileExists($path);
    $eArchive = $response->INVOICE;
    file_put_contents("$path\\" . $request['INVOICEID'] . ".zip", (array)$eArchive);

    $unzip = new ZipArchive;
    $zip1 = $unzip->open($path . "\\" . $request['INVOICEID'] . ".zip");

    for ($i = 0; $i <    $unzip->numFiles; $i++) {
      $stat =    $unzip->statIndex($i);
      print_r(basename($stat['name']) . PHP_EOL);
      $son = basename($stat['name']);
    }

    if ($zip1 === TRUE) {
      $unzip->extractTo($path . "\\");
      $unzip->close();
      unlink($path . "\\" . $request['INVOICEID'] . ".zip");
    }

    $zip2 = $unzip->open($path . "\\" . $son);
    if ($zip2 === TRUE) {
      $unzip->extractTo($path . "\\");
      $unzip->close();
      unlink($path . "\\" . $son);
    }
  }

  public function WriteToArchieveExtended()
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

    $response = $GLOBALS['client']->__soapCall("WriteToArchiveExtended", array($request));
    if (isset($response->ERROR_TYPE)) {
      $this->assertNull($response->ERROR_TYPE);
    }
    $this->assertEquals($response->REQUEST_RETURN->RETURN_CODE, 0);
  }

  public function EArchiveInvoiceList()
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
      'CONTENT_TYPE' => 'XML',
      'READ_INCLUDED' => 'Y'
    );

    $response = $GLOBALS['client']->__soapCall("GetEArchiveInvoiceList", array($request));
    // echo ($GLOBALS['client']->__getLastResponse());
    if (isset($response->ERROR_TYPE)) {
      $this->assertNull($response->ERROR_TYPE);
    }
    $this->assertEquals($response->REQUEST_RETURN->RETURN_CODE, 0);

    $GLOBALS['path'] =    $GLOBALS['EArchiveFolderPath'] . '\\EArchiveInvoices';
    $GLOBALS['EArchiveInvoices'] = [];
    
    //  file_put_contents("C:\Users\meryem.aksu\Downloads\.Izibiz_php_soap_client\EArchive\EArchiveInvoices\DENEME.xml",array($response->INVOICE));
    foreach ($response->INVOICE as $archiveinvoice) {
      if ($request['HEADER_ONLY'] == 'N') {
        if ($request['REQUEST_HEADER']['COMPRESSED'] == 'Y') {
          array_push($GLOBALS['EArchiveInvoices'], $archiveinvoice);
          $fileexists = $GLOBALS['operations']->fileExists($GLOBALS['path']);
          $saveToDisk = $GLOBALS['path'] . "\\" . $archiveinvoice->HEADER->UUID . "-" . $archiveinvoice->HEADER->INVOICE_ID . ".zip";
          file_put_contents( $saveToDisk,(array)$archiveinvoice->CONTENT);
          $unzip = new ZipArchive;
          $zip1 = $unzip->open( $saveToDisk);   
      
          if ($zip1 === TRUE) {
            $unzip->extractTo($GLOBALS['path'] . "\\");
            $unzip->close();
            unlink( $saveToDisk );
          }
        }
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
  public function GetEmailEarchiveInvoice()
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

  public function testGetEArchiveReport()
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
    $GLOBALS['EArchiveReport'] = $GLOBALS['EArchiveFolderPath'] . "\EArchiveReport";
    $GLOBALS['operations']->fileExists($GLOBALS['EArchiveReport']);
   // file_put_contents($GLOBALS['EArchiveReport'] . '\\' . $request['REPORT_PERIOD'] . '.xml', json_encode((array)$response->REPORT));
    if ($request['REPORT_STATUS_FLAG'] == 'N') {
      file_put_contents($GLOBALS['EArchiveReport'] . '\\' . $request['REPORT_PERIOD'] . '.xml', json_encode((array)$response->REPORT));
      file_put_contents($GLOBALS['EArchiveReport'] . '\\' . $request['REPORT_PERIOD'] . '.xml', json_encode((array)$response->INVOICE), FILE_APPEND);
    }
    $GLOBALS['ReportNo'] = [];
    foreach ($response->REPORT as $report) {
      array_push($GLOBALS['ReportNo'], $report->REPORT_NO);
    }
  }

  public function testReadEArchiveReport()
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
   echo $GLOBALS['client']->__getLastResponse();
  }
}
