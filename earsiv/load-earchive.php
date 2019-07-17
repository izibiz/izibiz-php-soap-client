<?php
	ini_set("soap.wsdl_cache_enabled", "0"); // wsdl cache 'ini devre disi birak
	$Username = 'izibiz-test1';
	$Password = 'izi321';

	$client = new SoapClient("https://efaturatest.izibiz.com.tr/EFaturaOIB?wsdl");
try {
	$Req["USER_NAME"] =	"izibiz-test2";
	$Req["PASSWORD"] = "izi321";
	$RequestHeader["SESSION_ID"] = "-1";
	$Req["REQUEST_HEADER"] = $RequestHeader;
	$Res = $client->Login($Req);
	$archiveClient = new SoapClient("https://efaturatest.izibiz.com.tr/EIArchiveWS/EFaturaArchive?wsdl");

	$content = file_get_contents('earsivxml.php');

	$Request = array(
		"REQUEST_HEADER"	=>	array(
			"SESSION_ID"	=>	$Res->SESSION_ID,
			"COMPRESSED"	=>	"N"
		),
		"ArchiveInvoiceExtendedContent"	=>	array(
			"INVOICE_PROPERTIES"	=>	array(
				"EARSIV_FLAG"	=>	"Y",
				"EARSIV_PROPERTIES"	=>	array(
					"EARSIV_TYPE"	=>	"NORMAL",
					"EARSIV_EMAIL_FLAG"	=>	"Y",
					"EARSIV_EMAIL"	=>	"entegrasyon@izibiz.com.tr",
					"SUB_STATUS"	=>	"NEW"
				),
				"INVOICE_CONTENT"	=>	$content
			)
		)
	);

	print_r($Request);
	$sendF = $archiveClient->WriteToArchiveExtended($Request);
	print_r($sendF);
} catch (Exception $exc) { // Hata olusursa yakala
  // Son istegi ekrana bas
  echo "Son yapilan istek asagidadir<br/><pre>";
  echo htmlentities($client->__getLastRequest());
  echo "</pre>";

  echo "<br/><br/><br/>";

  // Son istegin header kismini ekrana bas
  echo "Son yapilan istegin header kismi<br/><pre>";
  echo htmlentities($client->__getLastRequestHeaders());
  echo "</pre>";

  echo "<br/><br/><br/>";

  // Son yapilan istege sunucunun verdigi yanit
  echo "Son yapilan metod cagrisinin yaniti<br/><pre>";
  echo htmlentities($client->__getLastResponse());
  echo "</pre>";

  echo "<br/><br/><br/>";

  // Son yapilan istege sunucunun verdigi yanitin header kismi
  echo "Son yapilan metod cagrisinin yanitinin header kismi<br/><pre>";
  echo htmlentities($client->__getLastResponseHeaders());
  echo "</pre>";

  echo "Soap Hatasi Olustu: " . $exc->getMessage()."<br>";
  //print_r($exc);
}

?>
