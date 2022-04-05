<?php
ini_set("soap.wsdl_cache_enabled", "0");


$operations = new Operations();
$homeFilePath = $operations->homeFileOpen();

include './app/EInvoiceTemplate.php';
$GLOBALS['EInvoiceXml'] = $xmlEInvoice;
$GLOBALS['Id']=$Id;
$GLOBALS['Uuid']=$uuid;
class EInvoiceTest extends PHPUnit\Framework\TestCase
{

  public function testEInvoice()
  {
    $request = array(
      'REQUEST_HEADER' => array(
        "SESSION_ID" => $GLOBALS['sessionId'],
        "COMPRESSED" => 'N',
        "APPLICATION_NAME" => 'Izibiz_php_soap_client.Application'
      ),
      'INVOICE_SEARCH_KEY' => array(
        "LIMIT" => 10,
        "START_DATE" => date("Y-m-d", mktime(0, 0, 0, date("m"), date("d"), date("Y") - 1)),
        "END_DATE" => date("Y-m-d"),
        "READ_INCLUDED" => true,
        "DIRECTION" => 'IN'
      ),
      'HEADER_ONLY' => 'N'
    );
    $GLOBALS['client'] = new SoapClient($GLOBALS['baseURL'].'/EInvoiceWS?wsdl', array('trace' => 1, 'exceptions' => 1));
    $response = $GLOBALS['client']->__soapCall("GetInvoice", array($request));

    $fileHomePath = $GLOBALS['homeFilePath'];
    $GLOBALS['EInvoiceFolderPath'] = "$fileHomePath\.EInvoice";
    $GLOBALS['operations']->fileExists($GLOBALS['EInvoiceFolderPath']);
    $GLOBALS['path'] = $request['INVOICE_SEARCH_KEY']['DIRECTION'] == "IN" ? $GLOBALS['EInvoiceFolderPath'] . "\GelenKutusu" : ($request['INVOICE_SEARCH_KEY']['DIRECTION'] == "OUT" ? $GLOBALS['EInvoiceFolderPath'] . "\GidenKutusu" : $GLOBALS['EInvoiceFolderPath'] . "\TaslakFatura");
    $this->assertTrue((array)$response->INVOICE > 0);
    $GLOBALS['EInvoices'] = [];
    foreach ($response->INVOICE as $invoice) {
      array_push($GLOBALS['EInvoices'], $invoice);
      $fileexists = $GLOBALS['operations']->fileExists($GLOBALS['path']);
      $saveToDisk = $GLOBALS['path'] . "\.$invoice->UUID-$invoice->ID.xml";
      file_put_contents($saveToDisk, (array)$invoice->CONTENT);
    }
  }


  public function testEInvoiceWithTypeXML()
  {
    $request = array(
      'REQUEST_HEADER' => array(
        "SESSION_ID" => $GLOBALS['sessionId'],
        "COMPRESSED" => 'N',
        "APPLICATION_NAME" => 'Izibiz_php_soap_client.Application'
      ),
      'INVOICE_SEARCH_KEY' => array(
        "UUID" => $GLOBALS['EInvoices'][0]->{'UUID'},
        "TYPE" => "HTML",
        "DIRECTION" => "IN",
        "READ_INCLUDED" => true,
      ),
      'HEADER_ONLY' => 'N'
    );

    $response = $GLOBALS['client']->__soapCall("GetInvoiceWithType", array($request));
    if (in_array("ERROR_TYPE", (array)$response)) {
      $this->assertNull($response->ERROR_TYPE);
    }
    $this->assertTrue((array)$response->INVOICE > 0);
    $GLOBALS['operations']->fileExists($GLOBALS['EInvoiceFolderPath']);
    $fileexists = $GLOBALS['operations']->fileExists($GLOBALS['path']);
    $DocumentType = $request['INVOICE_SEARCH_KEY']['TYPE'] == "XML" ? $request['INVOICE_SEARCH_KEY']['UUID'] . ".xml" : ($request['INVOICE_SEARCH_KEY']['TYPE'] == "PDF" ? $request['INVOICE_SEARCH_KEY']['UUID'] . ".pdf" : $request['INVOICE_SEARCH_KEY']['UUID'] . ".html");
    $saveToDisk = $GLOBALS['path'] . "\.$DocumentType";
    file_put_contents($saveToDisk, (array)$response->INVOICE->CONTENT);
  }

  public function testGetInvoiceStatus()
  {
    $eInvoiceUUID = [];
    foreach ($GLOBALS['EInvoices'] as $invoice) {
      array_push($eInvoiceUUID, $invoice->UUID);
    }

    $request = array(
      'REQUEST_HEADER' => array(
        "SESSION_ID" => $GLOBALS['sessionId'],
        "COMPRESSED" => 'N',
        "APPLICATION_NAME" => 'Izibiz_php_soap_client.Application'
      ),
      'UUID' => $eInvoiceUUID,
    );
    $response = $GLOBALS['client']->__soapCall("GetInvoiceStatusAll", array($request));
    file_put_contents($GLOBALS['EInvoiceFolderPath'] . "\EInvoiceStatusAll.xml", json_encode((array)$response->INVOICE_STATUS));
    $this->assertTrue((array)$response->INVOICE_STATUS > 0);
  }


  public function testLoadInvoice()
  {
    $Invoice = new SimpleXMLElement($GLOBALS['EInvoiceXml']);
    $Invoice->registerXPathNamespace('cbc', 'urn:oasis:names:specification:ubl:schema:xsd:CommonBasicComponents-2');
    $GLOBALS['content'] = str_replace('<?xml version="1.0"?>', '', $Invoice->asXML());

    $output = 'ZipEInvoice.zip';
    $zip = new ZipArchive;
    if ($zip->open($output, ZipArchive::CREATE) == FALSE) {
      die("$output");
    }
    $zip->addFromString($GLOBALS['Id'] . '.xml', $GLOBALS['content']);
    $zip->close();
    $GLOBALS['EInvoiceZip'] = file_get_contents("ZipEInvoice.zip");
    unlink('ZipEInvoice.zip');

    $request = array(
      'REQUEST_HEADER' => array(
        "SESSION_ID" => $GLOBALS['sessionId'],
        "COMPRESSED" => 'Y',
        "APPLICATION_NAME" => 'Izibiz_php_soap_client.Application'
      ),
      'INVOICE' => array(
        'CONTENT' => $GLOBALS['EInvoiceZip']
      )
    );

    $response = $GLOBALS['client']->__soapCall("LoadInvoice", array($request));
    if (in_array("ERROR_TYPE", (array)$response)) {
      $this->assertNull($response->ERROR_TYPE);
    }
    $this->assertEquals($response->REQUEST_RETURN->RETURN_CODE, 0);
  }

  public function testSendInvoice()
  {

    $request = array(
      'REQUEST_HEADER' => array(
        "SESSION_ID" => $GLOBALS['sessionId'],
        "COMPRESSED" => 'Y',
        "APPLICATION_NAME" => 'Izibiz_php_soap_client.Application'
      ),
      'SENDER' => array(
        'vkn' => '4840847211',
        'alias' => 'urn:mail:defaultgb@izibiz.com.tr',
      ),
      'RECEIVER' => array(
        'vkn' => '4840847211',
        'alias' => 'urn:mail:defaultpk@izibiz.com.tr'
      ),
      'INVOICE' => array(
        'CONTENT' => $GLOBALS['EInvoiceZip']
      ),
    );

    $response = $GLOBALS['client']->__soapCall("SendInvoice", array($request));
    echo ($GLOBALS['client']->__getLastResponse());
    if (in_array("ERROR_TYPE", (array)$response)) {
      $this->assertNull($response->ERROR_TYPE);
    }
  }

  public function testMarkInvoice()
  {
    $request = array(
      'REQUEST_HEADER' => array(
        "SESSION_ID" => $GLOBALS['sessionId'],
        "COMPRESSED" => 'Y',
        "APPLICATION_NAME" => 'Izibiz_php_soap_client.Application'
      ),
      'MARK' => array(
        'value' => 'READ',
        'INVOICE' => $GLOBALS['EInvoices']
      ),
    );

    $response = $GLOBALS['client']->__soapCall("MarkInvoice", array($request));
    $this->testEInvoice();
  }

  public function testSendInvoiceResponseWithServerSign()
  {
    $request = array(
      'REQUEST_HEADER' => array(
        "SESSION_ID" => $GLOBALS['sessionId'],
        "COMPRESSED" => 'Y',
        "APPLICATION_NAME" => 'Izibiz_php_soap_client.Application'
      ),
      'STATUS' => 'KABUL',
      'INVOICE' => array(

        'ID' => 'MTL2021000900137'
      ),
      'DESCRIPTION' => 'psdal',
    );
    $response = $GLOBALS['client']->__soapCall("SendInvoiceResponseWithServerSign", array($request));
    $this->assertNull($response->ERROR_TYPE->ERROR_CODE);
    $this->assertEquals($response->REQUEST_RETURN->RETURN_CODE, 0);
  }
}
