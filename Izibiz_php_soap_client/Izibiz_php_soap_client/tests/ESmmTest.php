<?php

ini_set("soap.wsdl_cache_enabled", "0");
include './app/ESmmTemplate.php';
$operations = new Operations();
$homeFilePath = $operations->homeFileOpen();

$GLOBALS['SmmXml'] = $xmlSmm;

class ESmmTest extends PHPUnit\Framework\TestCase
{
    public function testGetSmm()
    {
      

        $request = array(
            'REQUEST_HEADER' => array(
                "SESSION_ID" => $GLOBALS['sessionIdD'],
                "COMPRESSED" => 'Y',
                "APPLICATION_NAME" => 'Izibiz_php_soap_client.Application'
            ),
            'SMM_SEARCH_KEY' => array(
                'LIMIT' => 10,
                //'READ_INCLUDED' => 'N' ,
                'READ_INCLUDED' => TRUE,
                "START_DATE" => date("Y-m-d", mktime(0, 0, 0, date("m") - 6, date("d") - 20, date("Y"))),
                "END_DATE" => date("Y-m-d"),

            ),
            'CONTENT_TYPE' => 'PDF',
        );
        $GLOBALS['client'] = new SoapClient('https://efaturatest.izibiz.com.tr/SmmWS?wsdl', array('trace' => 1, 'exceptions' => 1));
        $response = $GLOBALS['client']->__soapCall("GetSmm", array($request));
        if (in_array("ERROR_TYPE", (array)$response)) {
            $this->assertNull($response->ERROR_TYPE);
        }
        $this->assertNotNull($response->SMM);
        $fileHomePath = $GLOBALS['homeFilePath'];
        $GLOBALS['ESmmFolderPath'] = "$fileHomePath\ESmm";
        $GLOBALS['operations']->fileExists($GLOBALS['ESmmFolderPath']);
        file_put_contents($GLOBALS['ESmmFolderPath'] . "\\ESmmGet.xml", $GLOBALS['client']->__getLastResponse());
        $GLOBALS['ESmmUUIDList'] = [];
            foreach ($response->SMM as $smm) {

                array_push($GLOBALS['ESmmUUIDList'], $smm->UUID);
              
            }
    }

    public function GetSMMStatus()
    {
        $request = array(
            'REQUEST_HEADER' => array(
                "SESSION_ID" => $GLOBALS['sessionIdD'],
                "COMPRESSED" => 'Y',
                "APPLICATION_NAME" => 'Izibiz_php_soap_client.Application'
            ),
            'UUID' => $GLOBALS['ESmmUUIDList'], // "669962aa-3931-49e1-bb67-97d65702baac" UUİDsi bilenen müstahsil bu şekilde sorgulanabilir.
        );

        $response = $GLOBALS['client']->__soapCall("GetSmmStatus", array($request));
        if (in_array("ERROR_TYPE", (array)$response)) {
            $this->assertNull($response->ERROR_TYPE);
        }
        $this->assertNotNull($response->SMM_STATUS);
        file_put_contents($GLOBALS['ESmmFolderPath'] . "\\SMMStatus.xml", $GLOBALS['client']->__getLastResponse());
    }

    public function GetSMMReport()
    {
        $request = array(
            'REQUEST_HEADER' => array(
                "SESSION_ID" => $GLOBALS['sessionIdD'],
                "COMPRESSED" => 'Y',
                "APPLICATION_NAME" => 'Izibiz_php_soap_client.Application'
            ),
            "START_DATE" => date("Y-m-d", mktime(0, 0, 0, date("m") - 3, date("d"), date("Y"))),
            "END_DATE" => date("Y-m-d"),
            'HEADER_ONLY'=>'N'
        );


        $response = $GLOBALS['client']->__soapCall("GetSmmReport", array($request));
        if (in_array("ERROR_TYPE", (array)$response)) {
            $this->assertNull($response->ERROR_TYPE);
        }
        $this->assertNotNull($response->SMM_REPORT);
        file_put_contents($GLOBALS['ESmmFolderPath'] . "\\SMMReport.xml", $GLOBALS['client']->__getLastResponse());
    }


    public function testLoadSmm()
    {
        $Invoice = new SimpleXMLElement($GLOBALS['SmmXml']);
        $Invoice->registerXPathNamespace('cbc', 'urn:oasis:names:specification:ubl:schema:xsd:CommonBasicComponents-2');
        $GLOBALS['content'] = str_replace('<?xml version="1.0"?>', '', $Invoice->asXML());

        $GLOBALS['ID'] = $Invoice->xpath('//cbc:ID');
        $GLOBALS['UUID'] = $Invoice->xpath('//cbc:UUID');

        $output = 'ESmm.zip';
        $zip = new ZipArchive;
        if ($zip->open($output, ZipArchive::CREATE) == FALSE) {
            die("$output");
        }
        $zip->addFromString($GLOBALS['ID'][0] . '.xml',   $GLOBALS['content']);
        $zip->close();
        $GLOBALS['ESmmContent'] = file_get_contents("ESmm.zip");
        unlink('ESmm.zip');


        $request = array(
            'REQUEST_HEADER' => array(
                "SESSION_ID" => $GLOBALS['sessionIdD'],
                "COMPRESSED" => 'Y',
                "APPLICATION_NAME" => 'Izibiz_php_soap_client.Application'
            ),
            'SMM' => array(
                'ID' =>  $GLOBALS['ID'][0],
                'UUID' => $GLOBALS['UUID'],
                'CONTENT' => $GLOBALS['ESmmContent']
            ),
            'SMM_PROPERTIES' => array(
                'SENDING_TYPE'=>'KAGIT',    
            )
        );
      
        $response = $GLOBALS['client']->__soapCall("LoadSmm", json_encode($request));
        echo ($GLOBALS['client']->__getLastResponse());
        if (in_array("ERROR_TYPE", (array)$response)) {
            $this->assertNull($response->ERROR_TYPE);
        }
        $this->assertEquals($response->REQUEST_RETURN->RETURN_CODE, 0);

    }

    

}