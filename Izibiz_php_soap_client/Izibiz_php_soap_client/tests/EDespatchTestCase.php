<?php

include './app/EDespatchTemplate.php';

use SemiorbitGuid\Guid;

$GLOBALS['xmlS'] = $xml;

$operations = new Operations();
$homeFilePath = $operations->homeFileOpen();
class EDespatchTest extends PHPUnit\Framework\TestCase
{

    public function testEDespatch()
    {
        $request = array(
            'REQUEST_HEADER' => array(
                "SESSION_ID" => $GLOBALS['sessionIdD'],
                "COMPRESSED" => 'Y',
                "APPLICATION_NAME" => 'Izibiz_php_soap_client.Application'
            ),
            'SEARCH_KEY' => array(
                'LIMIT' => 10,
                'READ_INCLUDED' => true,
                'START_DATE' => date("Y-m-d", mktime(0, 0, 0, date("m") - 1, date("d"), date("Y"))),
                'END_DATE' => date("Y-m-d"),
                'DIRECTION' => 'OUT',
            ),
            'HEADER_ONLY' => 'N',
        );
        $GLOBALS['client'] = new SoapClient('https://efaturatest.izibiz.com.tr/EIrsaliyeWS/EIrsaliye?wsdl', array('trace' => 1, 'exceptions' => 1));
        $response = $GLOBALS['client']->__soapCall("GetDespatchAdvice", array($request));
        $this->assertNotNull($response->DESPATCHADVICE);
        $fileHomePath = $GLOBALS['homeFilePath'];
        $GLOBALS['EDespatchFolderPath'] = "$fileHomePath\EDespatch";
        $GLOBALS['operations']->fileExists($GLOBALS['EDespatchFolderPath']);
        $GLOBALS['path'] = $request['SEARCH_KEY']['DIRECTION'] == "IN" ? $GLOBALS['EDespatchFolderPath'] . "\GelenKutusu" : ($request['SEARCH_KEY']['DIRECTION'] == "OUT" ? $GLOBALS['EDespatchFolderPath'] . "\GidenKutusu" : $GLOBALS['EDespatchFolderPath'] . "\Taslakİrsaliye");
        $this->assertTrue((array)$response->DESPATCHADVICE > 0);
        $GLOBALS['EDespatchsUUID'] = [];
        file_put_contents('C:\Users\meryem.aksu\Desktop\despatc\deneme2.xml', (array)$response->DESPATCHADVICE[0]->CONTENT);
        foreach ($response->DESPATCHADVICE as $despatch) {
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
                "SESSION_ID" => $GLOBALS['sessionIdD'],
                "COMPRESSED" => 'Y',
                "APPLICATION_NAME" => 'Izibiz_php_soap_client.Application'
            ),
            'UUID' => $GLOBALS['EDespatchsUUID'],
        );
        $response = $GLOBALS['client']->__soapCall("GetDespatchAdviceStatus", array($request));
        $this->assertNotNull($response->DESPATCHADVICE_STATUS);
        $fileHomePath = $GLOBALS['homeFilePath'];
        $GLOBALS['EArchiveFolderPath'] = "$fileHomePath\EArchive";
        $GLOBALS['operations']->fileExists($GLOBALS['EArchiveFolderPath']);
        $GLOBALS['EDespatchReport'] = $GLOBALS['EDespatchFolderPath'] . "\EArchiveReport";
        $GLOBALS['operations']->fileExists($GLOBALS['EDespatchReport']);
        file_put_contents($GLOBALS['EDespatchReport'] . '\\' . 'EDespatchStatus.xml', json_encode((array)$response->DESPATCHADVICE_STATUS));
    }

    public function LoadDespatchAdvice()
    {//HATA

        $DespatchAdvice = new SimpleXMLElement($GLOBALS['xmlS']);
        $DespatchAdvice->registerXPathNamespace('cbc', 'urn:oasis:names:specification:ubl:schema:xsd:CommonBasicComponents-2');
        $delete = str_replace('<?xml version="1.0"?>', '', $DespatchAdvice->asXML());
        $ID = $DespatchAdvice->xpath('//cbc:ID');
        $UUID = $DespatchAdvice->xpath('//cbc:UUID');
        // $GLOBALS['EDespatchContent'] = $DespatchAdvice->asXML();
        $GLOBALS['EDespatchContent'] =  $delete;
        $output = 'EDespatchZip.zip';
        $zip = new ZipArchive;
        if ($zip->open($output, ZipArchive::CREATE) == FALSE) {
            die("$output");
        }
        $zip->addFromString('text.xml',  $GLOBALS['EDespatchContent']);
        $zip->close();
        $GLOBALS['EDespatchContentt'] = file_get_contents("EDespatchZip.zip");
        unlink('EDespatchZip.zip');

        $request = array(
            'REQUEST_HEADER' => array(
                "SESSION_ID" => $GLOBALS['sessionIdD'],
                "COMPRESSED" => 'Y',
                "APPLICATION_NAME" => 'Izibiz_php_soap_client.Application'
            ),
            'DESPATCHADVICE' => array(
                'ID' => $ID,
                'UUID' => $UUID,
                'CONTENT' =>   $GLOBALS['EDespatchContentt']
            )
        );
        $response = $GLOBALS['client']->__soapCall("LoadDespatchAdvice", (array)$request);
        echo ($GLOBALS['client']->__getlastrequest());
    }

    public function testSendDespatchAdvice()
    {
        $DespatchAdvice = new SimpleXMLElement($GLOBALS['xmlS']);
        $DespatchAdvice->registerXPathNamespace('cbc', 'urn:oasis:names:specification:ubl:schema:xsd:CommonBasicComponents-2');
        $GLOBALS['content'] = str_replace('<?xml version="1.0"?>', '', $DespatchAdvice->asXML());
        $ID = $DespatchAdvice->xpath('//cbc:ID');
        $UUID = $DespatchAdvice->xpath('//cbc:UUID');
        $GLOBALS['content'] = str_replace($ID[0], 'DAA' . date("Y") . rand(100000000, 999999999), $GLOBALS['content']);
        $quid = substr(Guid::NewGuid(), 1, -1);
        $GLOBALS['content'] = str_replace($UUID, $quid,  $GLOBALS['content']);
    
        $output = 'EDespatchFile.zip';
        $zip = new ZipArchive;
        if ($zip->open($output, ZipArchive::CREATE) == FALSE) {
            die("$output");
        }
        $zip->addFromString('text.xml',  $GLOBALS['content']);
        $zip->close();
        $GLOBALS['EdespatchZip'] = file_get_contents("EDespatchFile.zip");
        unlink('EDespatchFile.zip');


        $request = array(
            'REQUEST_HEADER' => array(
                "SESSION_ID" => $GLOBALS['sessionIdD'],
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
                'CONTENT' => $GLOBALS['EdespatchZip'],
            )
        );
        $response = $GLOBALS['client']->__soapCall("SendDespatchAdvice", array($request));
      //  echo ($GLOBALS['client']->__getLastResponse());
        $this->assertEquals($response->REQUEST_RETURN->RETURN_CODE,0);
    }

   // public function testMarkDespatchAdvice()
    public function MarkDespatchAdvice()
    {//HATA VERİYOR
        $request = array(
            'REQUEST_HEADER' => array(
                "SESSION_ID" => $GLOBALS['sessionIdD'],
                "COMPRESSED" => 'Y',
                "APPLICATION_NAME" => 'Izibiz_php_soap_client.Application'
            ),
            'MARK' => array(
                'value' => 'UNREAD',
                'DESPATCHADVICEINFO' => array(
                    'UUID' => $GLOBALS['EDespatchsUUID'],
                ),
            )
        );

        $response = $GLOBALS['client']->__soapCall("MarkDespatchAdvice", array($request));
        echo ($GLOBALS['client']->__getLastResponse());
    }
}
