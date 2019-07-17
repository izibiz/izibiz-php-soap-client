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
	
	$allMethods = $archiveClient->__getFunctions();
	
	print_r($allMethods);	
	
	$Request = array(
		"REQUEST_HEADER"	=>	array(
			"SESSION_ID"	=>	$Res->SESSION_ID
		),
		"CancelEArsivInvoiceContent"	=>	array(
			"IPTAL_EAINVOICE_FLAG"	=>	"N",
			"FATURANO"				=>	"4ffbb726-1153-f45f-4a9b-1a524ed70313",
			"IPTAL_TARIHI"			=>	date("Y-m-d"),
			"TOPLAM_TUTAR"			=>	944
		)
	);
	$sendF = $archiveClient->CancelEArchiveInvoice($Request);
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