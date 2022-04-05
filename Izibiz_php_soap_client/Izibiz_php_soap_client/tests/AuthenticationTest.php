<?php
ini_set("soap.wsdl_cache_enabled", "0");
global $baseURL;
global $sessionId;

class AuthenticationTest extends PHPUnit\Framework\TestCase
{

  public function testAuthenticationLogin()
  {$GLOBALS['baseURL']='https://efaturatest.izibiz.com.tr';

    $params = array(
      'REQUEST_HEADER' => array(
        "SESSION_ID" => '',
      ),
      'USER_NAME' => 'izibiz-test2',
      'PASSWORD' => 'izi321',
    );

    $GLOBALS['client'] = new SoapClient($GLOBALS['baseURL'].'/AuthenticationWS?wsdl', array('trace' => 1, 'exceptions' => 1));
    $response =  $GLOBALS['client']->__soapCall("Login", array($params));
    $this->assertNotNull($response->{'SESSION_ID'}, "hatali kullanici adi ve sifresi girdiniz.");
    $GLOBALS['sessionId'] = $response->SESSION_ID;
    //echo ( $GLOBALS['client']->__getLastResponse());
  }

  public function Logout()
  {
    global $sessionId;
    global $client;
    $logout = array('REQUEST_HEADER' => array('SESSION_ID' => $GLOBALS['sessionId']));
    $responseLogout = $GLOBALS['client']->__soapCall("Logout", array($logout));;
    $this->assertEquals($responseLogout->REQUEST_RETURN->{'RETURN_CODE'}, '0');
    echo ($GLOBALS['client']->__getLastResponse());
  }


  public function testChechUser()
  {
    $request = array(
      'REQUEST_HEADER' => array(
        "SESSION_ID" => $GLOBALS['sessionId'],
      ),
      'USER' => array(
        "IDENTIFIER" => '4840847211'
      ),
      'DOCUMENT_TYPE' => 'INVOICE'
    );
    $response = $GLOBALS['client']->__soapCall("CheckUser", array($request));
    $checkUserNumber = count($response->{'USER'});
    $this->assertEquals($checkUserNumber, 6);
  }

  public function testGetGibUserList()
  {
    $request = array(
      'REQUEST_HEADER' => array(
        "SESSION_ID" => $GLOBALS['sessionId'],
      ),
      'TYPE' => 'XML',
      'DOCUMENT_TYPE' => 'ALL',
      'REGISTER_TIME_START' => date('d/m/Y H:i:s'),
      'ALIAS_TYPE' => 'PK',

    );
    $response = $GLOBALS['client']->__soapCall("GetGibUserList", array($request));
    $deger = $response->CONTENT;
    $operations = new Operations();
    $path = $operations->homeFileOpen();
    file_put_contents("$path\.GetGibUserList.zip", (array)$deger);
    $this->assertTrue((array)$deger > 0);
  }
}

class Operations
{
  public function homeFileOpen()
  {
    $file_path = dirname(__FILE__, 4);
    $klas = "$file_path\.Izibiz_php_soap_client";
    $this->fileExists($klas);
    return $klas;
  }

  public function fileExists($klas)
  {
    if (!file_exists($klas)) {
      $olustur = mkdir("$klas");
    }
  }
}
