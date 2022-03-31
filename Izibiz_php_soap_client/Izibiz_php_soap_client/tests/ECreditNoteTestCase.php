<?php

ini_set("soap.wsdl_cache_enabled", "0");
include './app/ECreditNoteTemplate.php';
$operations = new Operations();
$homeFilePath = $operations->homeFileOpen();
// $cal = new AuthenticationTest();
// $cal->func();
$GLOBALS['CreditNoteXml'] = $CreditNoteXml;

class ECreditNoteTest extends PHPUnit\Framework\TestCase
{

    public function LoadCreditNote()
    {
        $CreditNote = new SimpleXMLElement($GLOBALS['CreditNoteXml']);
        $CreditNote->registerXPathNamespace('cbc', 'urn:oasis:names:specification:ubl:schema:xsd:CommonBasicComponents-2');
        $GLOBALS['content'] = str_replace('<?xml version="1.0"?>', '', $CreditNote->asXML());

        $GLOBALS['ID'] = $CreditNote->xpath('//cbc:ID');
        $GLOBALS['UUID'] = $CreditNote->xpath('//cbc:UUID');

        $output = 'ECreditNote.zip';
        $zip = new ZipArchive;
        if ($zip->open($output, ZipArchive::CREATE) == FALSE) {
            die("$output");
        }
        $zip->addFromString($GLOBALS['ID'][0] . '.xml',   $GLOBALS['content']);
        $zip->close();
        $GLOBALS['ECreditNoteContent'] = file_get_contents("ECreditNote.zip");
        unlink('ECreditNote.zip');


        $request = array(
            'REQUEST_HEADER' => array(
                "SESSION_ID" => $GLOBALS['sessionIdD'],
                "COMPRESSED" => 'Y',
                "APPLICATION_NAME" => 'Izibiz_php_soap_client.Application'
            ),
            'CREDITNOTE' => array(
                'ID' =>  $GLOBALS['ID'][0],
                'UUID' => $GLOBALS['UUID'],
                'CONTENT' => $GLOBALS['ECreditNoteContent']
            ),
            'CREDITNOTE_PROPERTIES' => array()
        );
        $GLOBALS['client'] = new SoapClient('https://efaturatest.izibiz.com.tr/CreditNoteWS/CreditNote?wsdl', array('trace' => 1, 'exceptions' => 1));
        $response = $GLOBALS['client']->__soapCall("LoadCreditNote", array($request));
        echo ($GLOBALS['client']->__getLastResponse());
    }

    public function SendCreditNote()
    {
        //  $CreditNote = new SimpleXMLElement($GLOBALS['CreditNoteXml']);
        //  $CreditNote->registerXPathNamespace('cbc', 'urn:oasis:names:specification:ubl:schema:xsd:CommonBasicComponents-2');

        // $ID = $CreditNote ->xpath('//cbc:ID');
        // $UUID = $CreditNote->xpath('//cbc:UUID');

        // $output = 'ECreditNote.zip';
        // $zip = new ZipArchive;
        // if ($zip->open($output, ZipArchive::CREATE) == FALSE) {
        //     die("$output");
        // }
        // $zip->addFromString($ID[0] . '.xml',   $GLOBALS['content']);
        // $zip->close();
        // $GLOBALS['ECreditNoteContent'] = file_get_contents("ECreditNote.zip");
        // unlink('ECreditNote.zip');

        $request = array(
            'REQUEST_HEADER' => array(
                "SESSION_ID" => $GLOBALS['sessionIdD'],
                "COMPRESSED" => 'Y',
                "APPLICATION_NAME" => 'Izibiz_php_soap_client.Application'
            ),
            'CREDITNOTE' => array(
                'ID' =>  $GLOBALS['ID'][0],
                'UUID' => $GLOBALS['UUID'],
                'CONTENT' => $GLOBALS['ECreditNoteContent'],
            ),
            'CREDITNOTE_PROPERTIES' => array(
                'EMAIL_FLAG' => 'Y',
                'EMAIL' => "b@gmail.com", "c@gmail.com",
                'SENDING_TYPE' => 'KAGIT'
            )
        );

        $response = $GLOBALS['client']->__soapCall("SendCreditNote", var_dump($request));
        echo ($GLOBALS['client']->__getLastResponse());
    }

    public function testGetCreditNote()
    {
        $request = array(
            'REQUEST_HEADER' => array(
                "SESSION_ID" => $GLOBALS['sessionIdD'],
                "COMPRESSED" => 'Y',
                "APPLICATION_NAME" => 'Izibiz_php_soap_client.Application'
            ),
            'CREDITNOTE_SEARCH_KEY' => array(
                'LIMIT' => 10,
                //'READ_INCLUDED' => 'N' ,
                'READ_INCLUDED' => 'Y',
                "START_DATE" => date("Y-m-d", mktime(0, 0, 0, date("m") - 6, date("d") - 20, date("Y"))),
                "END_DATE" => date("Y-m-d"),

            ),
            'HEADER_ONLY' => 'N',
            'CONTENT_TYPE' => 'PDF',
        );
        $GLOBALS['client'] = new SoapClient('https://efaturatest.izibiz.com.tr/CreditNoteWS/CreditNote?wsdl', array('trace' => 1, 'exceptions' => 1));
        $response = $GLOBALS['client']->__soapCall("GetCreditNote", array($request));
        if (in_array("ERROR_TYPE", (array)$response)) {
            $this->assertNull($response->ERROR_TYPE);
        }
        $this->assertNotNull($response->CREDITNOTE);
        $fileHomePath = $GLOBALS['homeFilePath'];
        $GLOBALS['ECreditNoteFolderPath'] = "$fileHomePath\ECreditNote";
        $GLOBALS['operations']->fileExists($GLOBALS['ECreditNoteFolderPath']);
        // $GLOBALS['path'] = $request['SEARCH_KEY']['DIRECTION'] == "IN" ?  $GLOBALS['EReceiptFolderPath'] . "\GelenYanıt" : ($request['SEARCH_KEY']['DIRECTION'] == "OUT" ?  $GLOBALS['EReceiptFolderPath'] . "\GidenYanıt" :  $GLOBALS['EReceiptFolderPath'] . "\TaslakYanıt");
        $GLOBALS['ECreditNoteUUIDList'] = [];
        if ($request['HEADER_ONLY'] == 'N') {
            foreach ($response->CREDITNOTE as $creditNote) {

                array_push($GLOBALS['ECreditNoteUUIDList'], $creditNote->UUID);
                // $fileexists = $GLOBALS['operations']->fileExists($GLOBALS['path']);
                $saveToDisk =  $GLOBALS['ECreditNoteFolderPath'] . "\\" . substr(json_encode($creditNote->UUID), 1, -1) . "-" . substr(json_encode($creditNote->ID), 1, -1)  . ".zip";
                file_put_contents($saveToDisk, (array)$creditNote->CONTENT);
                $zip = new ZipArchive;
                $res = $zip->open($saveToDisk);
                if ($res === TRUE) {
                    $zip->extractTo($GLOBALS['ECreditNoteFolderPath'] . "\\");

                    $zip->close();
                } else {
                    echo 'Unzipped Process failed';
                }
                unlink($saveToDisk);
            }
        }
    }

    public function testGetCreditNoteStatus()
    {
        $request = array(
            'REQUEST_HEADER' => array(
                "SESSION_ID" => $GLOBALS['sessionIdD'],
                "COMPRESSED" => 'Y',
                "APPLICATION_NAME" => 'Izibiz_php_soap_client.Application'
            ),
            'UUID' => $GLOBALS['ECreditNoteUUIDList'], // "669962aa-3931-49e1-bb67-97d65702baac" UUİDsi bilenen müstahsil bu şekilde sorgulanabilir.
        );

        $response = $GLOBALS['client']->__soapCall("GetCreditNoteStatus", array($request));
        if (in_array("ERROR_TYPE", (array)$response)) {
            $this->assertNull($response->ERROR_TYPE);
        }
        $this->assertNotNull($response->CREDITNOTE_STATUS);
        file_put_contents($GLOBALS['ECreditNoteFolderPath'] . "\\CreditNoteStatus.xml", $GLOBALS['client']->__getLastResponse());
    }


    public function testMarkCreditNote()
    {
        $request = array(
            'REQUEST_HEADER' => array(
                "SESSION_ID" => $GLOBALS['sessionIdD'],
                "COMPRESSED" => 'Y',
                "APPLICATION_NAME" => 'Izibiz_php_soap_client.Application'
            ),
            'MARK' => array(
                'value' => 'UNREAD',
                'UUID' =>  $GLOBALS['ECreditNoteUUIDList'],
            )
        );
        $response = $GLOBALS['client']->__soapCall("MarkCreditNote", array($request));
        if (in_array("ERROR_TYPE", (array)$response)) {
            $this->assertNull($response->ERROR_TYPE);
        }
        $this->assertEquals($response->REQUEST_RETURN->RETURN_CODE, 0);
        echo ($GLOBALS['client']->__getLastResponse());
    }


    public function CancelCreditNote()
    { //testCancelEArchiveInvoice()
        $request = array(
            'REQUEST_HEADER' => array(
                "SESSION_ID" => $GLOBALS['sessionIdD'],
                "COMPRESSED" => 'Y',
                "APPLICATION_NAME" => 'Izibiz_php_soap_client.Application'
            ),
            'UUID' => '52e3493e-b991-4bea-87d0-5914aaa22a60',//İptal edilecek müstahsilin uuidsi yazılır.
        );

        $response = $GLOBALS['client']->__soapCall("CancelCreditNote", array($request));
        if (isset($response->ERROR_TYPE)) {
            $this->assertNull($response->ERROR_TYPE);
        }
        $this->assertEquals($response->REQUEST_RETURN->RETURN_CODE, 0);
        echo ($GLOBALS['client']->__getLastResponse());
    }
}
