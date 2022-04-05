<?php

include './app/EDespatchTemplate.php';

use SemiorbitGuid\Guid;

$GLOBALS['xmlS'] = $xml;
$GLOBALS['Id']=$Id;
$GLOBALS['Uuid']=$uuid;
$operations = new Operations();
$homeFilePath = $operations->homeFileOpen();
class EDespatchTest extends PHPUnit\Framework\TestCase
{

    public function testEDespatch()
    {
        $request = array(
            'REQUEST_HEADER' => array(
                "SESSION_ID" => $GLOBALS['sessionId'],
                "COMPRESSED" => 'Y',
                "APPLICATION_NAME" => 'Izibiz_php_soap_client.Application'
            ),
            'SEARCH_KEY' => array(
                'LIMIT' => 2,
                'READ_INCLUDED' => false,
                'START_DATE' => date("Y-m-d", mktime(0, 0, 0, date("m") - 1, date("d"), date("Y"))),
                'END_DATE' => date("Y-m-d"),
                'DIRECTION' => 'OUT',
            ),
            'HEADER_ONLY' => 'N',
        );
        $GLOBALS['client'] = new SoapClient($GLOBALS['baseURL'].'/EIrsaliyeWS/EIrsaliye?wsdl', array('trace' => 1, 'exceptions' => 1));
        $response = $GLOBALS['client']->__soapCall("GetDespatchAdvice", array($request));
        $this->assertNotNull($response->DESPATCHADVICE);
        $fileHomePath = $GLOBALS['homeFilePath'];
        $GLOBALS['EDespatchFolderPath'] = "$fileHomePath\EDespatch";
        $GLOBALS['operations']->fileExists($GLOBALS['EDespatchFolderPath']);
        $GLOBALS['path'] = $request['SEARCH_KEY']['DIRECTION'] == "IN" ? $GLOBALS['EDespatchFolderPath'] . "\GelenKutusu" : ($request['SEARCH_KEY']['DIRECTION'] == "OUT" ? $GLOBALS['EDespatchFolderPath'] . "\GidenKutusu" : $GLOBALS['EDespatchFolderPath'] . "\Taslakİrsaliye");
        $this->assertTrue((array)$response->DESPATCHADVICE > 0);
        $GLOBALS['EDespatchsUUID'] = [];
        foreach ($response->DESPATCHADVICE as $despatch) {
            echo  $despatch->UUID;
            array_push($GLOBALS['EDespatchsUUID'], $despatch->UUID);
            $fileexists = $GLOBALS['operations']->fileExists($GLOBALS['path']);
            $saveToDisk = $GLOBALS['path'] . "\\" . "$despatch->UUID-$despatch->ID.zip";
            file_put_contents($saveToDisk, serialize((array)$despatch->{'CONTENT'}));
        }
    }

    public function testGetDespatchAdviceStatus()
    {
        $request = array(
            'REQUEST_HEADER' => array(
                "SESSION_ID" => $GLOBALS['sessionId'],
                "COMPRESSED" => 'Y',
                "APPLICATION_NAME" => 'Izibiz_php_soap_client.Application'
            ),
            'UUID' => $GLOBALS['EDespatchsUUID'],
        );
        $response = $GLOBALS['client']->__soapCall("GetDespatchAdviceStatus", array($request));
        $this->assertNotNull($response->DESPATCHADVICE_STATUS);
        $GLOBALS['EDespatchReport'] = $GLOBALS['EDespatchFolderPath'] . "\EArchiveReport";
        $GLOBALS['operations']->fileExists($GLOBALS['EDespatchReport']);
        file_put_contents($GLOBALS['EDespatchReport'] . '\\' . 'EDespatchStatus.xml', json_encode((array)$response->DESPATCHADVICE_STATUS));
    }

    public function testLoadDespatchAdvice()
    {
        $DespatchAdvice = new SimpleXMLElement($GLOBALS['xmlS']);
        $DespatchAdvice->registerXPathNamespace('cbc', 'urn:oasis:names:specification:ubl:schema:xsd:CommonBasicComponents-2');
        $content = str_replace('<?xml version="1.0"?>', '', $DespatchAdvice->asXML());
       
        $GLOBALS['EDespatchContent'] =   $content;
        $output = 'EDespatchZip.zip';
        $zip = new ZipArchive;
        if ($zip->open($output, ZipArchive::CREATE) == FALSE) {
            die("$output");
        }
        $zip->addFromString($GLOBALS['Id'].'.xml',  $GLOBALS['EDespatchContent']);
        $zip->close();
        $GLOBALS['EDespatchContentt'] = file_get_contents("EDespatchZip.zip");
        unlink('EDespatchZip.zip');

        $request = array(
            'REQUEST_HEADER' => array(
                "SESSION_ID" => $GLOBALS['sessionId'],
                "COMPRESSED" => 'Y',
                "APPLICATION_NAME" => 'Izibiz_php_soap_client.Application'
            ),
            'DESPATCHADVICE' => array(
                'ID' => $GLOBALS['Id'],
                'UUID' => $GLOBALS['Uuid'],
                'CONTENT' =>   $GLOBALS['EDespatchContentt']
            )
        );
        $response = $GLOBALS['client']->__soapCall("LoadDespatchAdvice", array($request));
        if (in_array("ERROR_TYPE", (array)$response)) {
            $this->assertNull($response->ERROR_TYPE);
        }
        $this->assertEquals($response->REQUEST_RETURN->RETURN_CODE, 0);
    }

    public function testSendDespatchAdvice()
    {     
        $request = array(
            'REQUEST_HEADER' => array(
                "SESSION_ID" => $GLOBALS['sessionId'],
                "COMPRESSED" => 'Y',
                "APPLICATION_NAME" => 'Izibiz_php_soap_client.Application'
            ),
            'SENDER' => array(
                'vkn' => "4840847211",
                'alias' => "urn:mail:defaultgb@izibiz.com.tr"
            ),
            'RECEIVER' => array(
                'vkn' => "2710788108",
                'alias' => "urn:mail:defaultpk@izibiz.com.tr"
            ),
            'DESPATCHADVICE' => array(
                'CONTENT' => $GLOBALS['EDespatchContentt'],
            )
        );
        $response = $GLOBALS['client']->__soapCall("SendDespatchAdvice", array($request));
      //  echo ($GLOBALS['client']->__getLastResponse());
        $this->assertEquals($response->REQUEST_RETURN->RETURN_CODE,0);
    }

    public function testMarkDespatchAdvice()
    {
        $request = array(
            'REQUEST_HEADER' => array(
                "SESSION_ID" => $GLOBALS['sessionId'],
                "COMPRESSED" => 'Y',
                "APPLICATION_NAME" => 'Izibiz_php_soap_client.Application'
            ),
            'MARK' => array(
                'value' => 'READ',
                'DESPATCHADVICEINFO' =>$GLOBALS['EDespatchsUUID'],

            )
        );

        $response = $GLOBALS['client']->__soapCall("MarkDespatchAdvice", array($request));
        echo ($GLOBALS['client']->__getLastResponse());
        print_r($GLOBALS['EDespatchsUUID']);
        $this->testEDespatch();
    }
}
