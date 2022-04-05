<?php

ini_set("soap.wsdl_cache_enabled", "0");

$operations = new Operations();
$homeFilePath = $operations->homeFileOpen();
global $uuid;
function v4_UUID() {
    return sprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
      // 32 bits for the time_low
      mt_rand(0, 0xffff), mt_rand(0, 0xffff),
      // 16 bits for the time_mid
      mt_rand(0, 0xffff),
      // 16 bits for the time_hi,
      mt_rand(0, 0x0fff) | 0x4000,

      // 8 bits and 16 bits for the clk_seq_hi_res,
      // 8 bits for the clk_seq_low,
      mt_rand(0, 0x3fff) | 0x8000,
      // 48 bits for the node
      mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)
    );
  }

for ($x = 0; $x <= 10; $x++) {
    $v4_uuid = v4_UUID();
    $uuid=$v4_uuid;

}


class ReconciliationTest extends PHPUnit\Framework\TestCase
 {

public function testSendReconciliation()
    {
   echo $GLOBALS['uuid'];
        $request = array(
            'REQUEST_HEADER' => array(
                "SESSION_ID" => $GLOBALS['sessionId'],
                "COMPRESSED" => 'Y',
                "APPLICATION_NAME" => 'Izibiz_php_soap_client.Application'
            ),
            'RECONCILIATION' => array(
               'TYPE'=>'EM',
                'UUID' => $GLOBALS['uuid'],
                'CUSTOMER_IDENTIFIER'=>'4840847211',
                'COMMERCIAL_NAME'=>'İZİBİZ BİLİŞİM TEKNOLOJİLERİ ANONİM',
                'CUSTOMER_ADDRESS'=>'İSTANBUL\ESENLER',
                'EMAIL'=>'meryem.aksu@izibiz.com.tr',
                'CURRENCY_CODE'=>'TRY',
                'BABS_ACCOUNTING_PERIOD'=>'202107',
                'BA_DOCUMENT_COUNT'=>1,
                'BA_DOCUMENT_AMOUNT'=>5000,
                'BS_DOCUMENT_COUNT'=>1,
                'BS_DOCUMENT_AMOUNT'=>5000,
                'NOTE'=>'Denemedir.'
            ),
        );
    
        $GLOBALS['client'] = new SoapClient($GLOBALS['baseURL'].'/ReconciliationWS?wsdl', array('trace' => 1, 'exceptions' => 1));
         $response = $GLOBALS['client']->__soapcall('SendReconciliation',array($request));
         echo ($GLOBALS['client']->__getLastResponse());
         echo '-----------------------------------';
                 // if (in_array("ERROR_TYPE", (array)$response)) {
        //     $this->assertNull($response->ERROR_TYPE);
        // }
        // $this->assertEquals($response->REQUEST_RETURN->RETURN_CODE, 0);

    }


    public function testSendMailReconciliation()
    {      

        $request = array(
            'REQUEST_HEADER' => array(
                "SESSION_ID" => $GLOBALS['sessionId'],
                "COMPRESSED" => 'Y',
                "APPLICATION_NAME" => 'Izibiz_php_soap_client.Application'
            ),
            'MAIL_SEARCHING' => array(
               'UUID'=>  $GLOBALS['uuid'],
            ),
            
        );
        $response = $GLOBALS['client']->__soapCall("SendMailReconciliation", array($request));
        echo ($GLOBALS['client']->__getLastResponse());
        echo '-----------------------------------';
        //   if (in_array("ERROR_TYPE", (array)$response)) {
        //     $this->assertNull($response->ERROR_TYPE);
        // }
        // $this->assertEquals($response->REQUEST_RETURN->RETURN_CODE, 0);
        // $GLOBALS['EReconciliationFolderPath'] = $GLOBALS['homeFilePath']."\EReconciliation";
        // $GLOBALS['operations']->fileExists($GLOBALS['EReconciliationFolderPath']);
        // file_put_contents($GLOBALS['EReconciliationFolderPath'] . "\EReceiptGetStatus.xml", $GLOBALS['client']->__getLastResponse());
    }


    public function testGetReconciliationStatus()
    {      

        $request = array(
            'REQUEST_HEADER' => array(
                "SESSION_ID" => $GLOBALS['sessionId'],
                "COMPRESSED" => 'Y',
                "APPLICATION_NAME" => 'Izibiz_php_soap_client.Application'
            ),
            'RECONCILIATION_SEARCHING' => array(
               'UUID'=>  $GLOBALS['uuid'],
            ),
            
        );
        $response = $GLOBALS['client']->__soapCall("GetReconciliationStatus", array($request));
        echo ($GLOBALS['client']->__getLastResponse());
        echo '-----------------------------------';
          if (in_array("ERROR_TYPE", (array)$response)) {
            $this->assertNull($response->ERROR_TYPE);
        }
        $this->assertEquals($response->REQUEST_RETURN->RETURN_CODE, 0);
        $GLOBALS['EReconciliationFolderPath'] = $GLOBALS['homeFilePath']."\EReconciliation";
        $GLOBALS['operations']->fileExists($GLOBALS['EReconciliationFolderPath']);
        file_put_contents($GLOBALS['EReconciliationFolderPath'] . "\EReceiptGetStatus.xml", $GLOBALS['client']->__getLastResponse());
    }

    

}