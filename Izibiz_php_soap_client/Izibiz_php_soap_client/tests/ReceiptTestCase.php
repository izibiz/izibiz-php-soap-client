<?php

include './app/EReceiptTemplate.php';

use SemiorbitGuid\Guid;

$GLOBALS['xmlReceipt'] = $xmlReceipt;

$operations = new Operations();
$homeFilePath = $operations->homeFileOpen();
class ReceiptTest extends PHPUnit\Framework\TestCase
{
    //public function testLoadReceipt()
    public function LoadReceipt()
    { 
        $ReceiptAdvice = new SimpleXMLElement($GLOBALS['xmlReceipt']);
        $ReceiptAdvice->registerXPathNamespace('cbc', 'urn:oasis:names:specification:ubl:schema:xsd:CommonBasicComponents-2');
        $GLOBALS['content'] = str_replace('<?xml version="1.0"?>', '', $ReceiptAdvice->asXML());
        $ID = $ReceiptAdvice->xpath('//cbc:ID');
        $UUID = $ReceiptAdvice->xpath('//cbc:UUID');
        $GLOBALS['content'] = str_replace($ID[0], 'DAA' . date("Y") . rand(100000000, 999999999), $GLOBALS['content']);
        $quid = substr(Guid::NewGuid(), 1, -1);
        $GLOBALS['content'] = str_replace($UUID, $quid,  $GLOBALS['content']);
        //  file_put_contents("C:\Users\meryem.aksu\Desktop\despatc\yenicontent.xml",   $GLOBALS['content']);

        $output = 'EReceiptLoad.zip';
        $zip = new ZipArchive;
        if ($zip->open($output, ZipArchive::CREATE) == FALSE) {
            die("$output");
        }
        $zip->addFromString($ID[0] . '.xml',   $GLOBALS['content']);
        $zip->close();
        $GLOBALS['EReceiptContent'] = file_get_contents("EReceiptLoad.zip");
        unlink('EReceiptLoad.zip');

        $request = array(
            'REQUEST_HEADER' => array(
                "SESSION_ID" => $GLOBALS['sessionIdD'],
                "COMPRESSED" => 'Y',
                "APPLICATION_NAME" => 'Izibiz_php_soap_client.Application'
            ),
            'RECEIPTADVICE' => array(
                'CONTENT' => $GLOBALS['EReceiptContent']
            ),
        );
        $GLOBALS['client'] = new SoapClient('https://efaturatest.izibiz.com.tr/EIrsaliyeWS/EIrsaliye?wsdl', array('trace' => 1, 'exceptions' => 1));
        $response = $GLOBALS['client']->__soapCall("LoadReceiptAdvice", array($request));
        echo ($GLOBALS['client']->__getLastResponse());
    }
    //  public function testSendReceipt()
    public function SendReceipt()
    { //denenecek

        $ReceiptAdvice = new SimpleXMLElement($GLOBALS['xmlReceipt']);
        $ReceiptAdvice->registerXPathNamespace('cbc', 'urn:oasis:names:specification:ubl:schema:xsd:CommonBasicComponents-2');
        $GLOBALS['content'] = str_replace('<?xml version="1.0"?>', '', $ReceiptAdvice->asXML());
        $ID = $ReceiptAdvice->xpath('//cbc:ID');

        echo $ID[0];
        $UUID = $ReceiptAdvice->xpath('//cbc:UUID');
        $GLOBALS['content'] = str_replace($ID[0], 'DAA' . date("Y") . rand(100000000, 999999999), $GLOBALS['content']);
        echo $ID[0];

        $quid = substr(Guid::NewGuid(), 1, -1);
        $GLOBALS['content'] = str_replace($UUID, $quid,  $GLOBALS['content']);
        //file_put_contents("C:\Users\meryem.aksu\Desktop\despatc\yenicontent.xml",   $GLOBALS['content']);

        $output = 'EReceiptFile.zip';
        $zip = new ZipArchive;
        if ($zip->open($output, ZipArchive::CREATE) == FALSE) {
            die("$output");
        }
        $zip->addFromString($ID[0] . '.xml',  $GLOBALS['content']);
        $zip->close();
        $GLOBALS['EReceiptContent'] = file_get_contents("EReceiptFile.zip");
        unlink('EReceiptFile.zip');


        $request = array(
            'REQUEST_HEADER' => array(
                "SESSION_ID" => $GLOBALS['sessionIdD'],
                "COMPRESSED" => 'Y',
                "APPLICATION_NAME" => 'Izibiz_php_soap_client.Application'
            ),
            'RECEIPTADVICE' => array(
                'CONTENT' =>   $GLOBALS['EReceiptContent']
            ),
        );

        $response = $GLOBALS['client']->__soapCall("SendReceiptAdvice", array($request));
        echo ($GLOBALS['client']->__getLastResponse());

        $GLOBALS['eReceiptUuidList'] = [];
        array_push($GLOBALS['eReceiptUuidList'],  $quid);
    }

    public function testGetReceiptAdviceStatus()
    {
        $request = array(
            'REQUEST_HEADER' => array(
                "SESSION_ID" => $GLOBALS['sessionIdD'],
                "COMPRESSED" => 'Y',
                "APPLICATION_NAME" => 'Izibiz_php_soap_client.Application'
            ),
            'UUID' => "1fe35ed1-4154-4eef-80ab-f112977a11b7", // $GLOBALS['eReceiptUuidList'],
        );
        $GLOBALS['client'] = new SoapClient('https://efaturatest.izibiz.com.tr/EIrsaliyeWS/EIrsaliye?wsdl', array('trace' => 1, 'exceptions' => 1));
        $response = $GLOBALS['client']->__soapCall("GetReceiptAdviceStatus", array($request));
        if (in_array("ERROR_TYPE", (array)$response)) {
            $this->assertNull($response->ERROR_TYPE);
        }
        $this->assertNotNull($response->RECEIPTADVICE_STATUS);
    }


    public function testGetReceiptAdvice()
    {
        $request = array(
            'REQUEST_HEADER' => array(
                "SESSION_ID" => $GLOBALS['sessionIdD'],
                "COMPRESSED" => 'Y',
                "APPLICATION_NAME" => 'Izibiz_php_soap_client.Application'
            ),
            'SEARCH_KEY' => array(
                'LIMIT' => 10,
                'READ_INCLUDED' => 'Y',
                "START_DATE" => date("Y-m-d", mktime(0, 0, 0, date("m") - 6, date("d")-20, date("Y"))),
                "END_DATE" => date("Y-m-d"),
                'DIRECTION' => 'IN'
            ),
            'HEADER_ONLY' => 'N',
        );

        $response = $GLOBALS['client']->__soapCall("GetReceiptAdvice", array($request));
        if (in_array("ERROR_TYPE", (array)$response)) {
            $this->assertNull($response->ERROR_TYPE);
        }
        $this->assertNotNull($response->RECEIPTADVICE);
        $fileHomePath = $GLOBALS['homeFilePath'];
        $GLOBALS['EReceiptFolderPath'] = "$fileHomePath\EReceipt";
        $GLOBALS['operations']->fileExists($GLOBALS['EReceiptFolderPath']);
        $GLOBALS['path'] = $request['SEARCH_KEY']['DIRECTION'] == "IN" ?  $GLOBALS['EReceiptFolderPath'] . "\GelenYanıt" : ($request['SEARCH_KEY']['DIRECTION'] == "OUT" ?  $GLOBALS['EReceiptFolderPath'] . "\GidenYanıt" :  $GLOBALS['EReceiptFolderPath'] . "\TaslakYanıt");
        $GLOBALS['EReceipts'] = [];
        if ($request['HEADER_ONLY'] == 'N') {
            foreach ($response->RECEIPTADVICE as $receipt) {
          
                array_push($GLOBALS['EReceipts'], $receipt->UUID);
                $fileexists = $GLOBALS['operations']->fileExists($GLOBALS['path']);
                $saveToDisk = $GLOBALS['path'] . "\\".substr(json_encode($receipt->UUID),1,-1) . "-".substr(json_encode($receipt->ID),1,-1)  .".zip";
                file_put_contents($saveToDisk, (array)$receipt->CONTENT);
                $zip = new ZipArchive;
                $res = $zip->open($saveToDisk); 
                if ($res === TRUE) {
                    $zip->extractTo($GLOBALS['path']."\\");
                          
                    $zip->close();
                }
                else {
                    echo 'Unzipped Process failed';
                }
                unlink($saveToDisk);
            }
        }
    }

    public function testMarkReceiptAdvice()
    {
        $request = array(
            'REQUEST_HEADER' => array(
                "SESSION_ID" => $GLOBALS['sessionIdD'],
                "COMPRESSED" => 'Y',
                "APPLICATION_NAME" => 'Izibiz_php_soap_client.Application'
            ),
            'MARK'=>array(
                'value'=>'READ',
                'RECEIPTADVICEINFO'=> $GLOBALS['EReceipts'],
            )
        );
        $response = $GLOBALS['client']->__soapCall("MarkReceiptAdvice", array($request));
        if (in_array("ERROR_TYPE", (array)$response)) {
            $this->assertNull($response->ERROR_TYPE);
        }
        $this->assertEquals($response->REQUEST_RETURN->RETURN_CODE,0);
    }
}
