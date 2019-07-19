<?
ini_set("soap.wsdl_cache_enabled", "0"); //	WSDL cache özelligi kaldiriliyor.
		
class efaturaService {
	
	function print_html($text) {
		$text = print_r($text, true);
		$text = str_replace('<', '&lt;', $text);
		$text = str_replace('>', '&gt;', $text);
		$text = str_replace(chr(10), '<br>', $text);
		$text = str_replace(chr(32), '&ensp;', $text);
		
		return $text;
	}
	
	function print_xml($soapclient) {
		$request = $soapclient->__getLastRequest();
		$response = $soapclient->__getLastResponse();
			
		$dom = new DOMDocument();
		$dom->preserveWhiteSpace = false;
		$dom->formatOutput = true;
		
		$text = '<table border="1" bordercolor="#0D79AE" cellpadding="10" cellspacing="0" align="center"><tr><td><table barder="0" cellpadding="10" bgcolor="#E3F4FD" class="style2"><tr><td><font color="#F00E12">Giden XML : </font><br />';
		$dom->loadXML($request);
		$text .= self::print_html($dom->saveXML());
		
		$text .= '<br /><font color="#F00E12">Dönen XML : </font><br />';
		$dom->loadXML($response);
		$text .= self::print_html($dom->saveXML());
		$text .= '</td></tr></table></td></tr></table>';
		
		return $text;
	}
	
	function Questionize($connection, $query) {
		$parse = oci_parse($connection, $query);
		if(!$parse) {
			$error = oci_error($connection);
		} else {		
			$exec = oci_execute($parse, OCI_DEFAULT);
			if(!$exec) {
				$error = oci_error($parse);
			}
		}
		
		$result["parse"] = $parse;
		$result["error"] = $error;
			
		return $result;	
	}
	
	function CreateInvoice($Parameters) {
		$Content = new DOMDocument('1.0', 'UTF-8');
		$Content->preserveWhiteSpace = false;
		$Content->formatOutput = true;
		
		$Content->loadXML('<?xml version="1.0" encoding="UTF-8"?><?xml-stylesheet type="text/xsl" href="general.xslt"?><Invoice xmlns="urn:oasis:names:specification:ubl:schema:xsd:Invoice-2" xmlns:cac="urn:oasis:names:specification:ubl:schema:xsd:CommonAggregateComponents-2" xmlns:cbc="urn:oasis:names:specification:ubl:schema:xsd:CommonBasicComponents-2" xmlns:ext="urn:oasis:names:specification:ubl:schema:xsd:CommonExtensionComponents-2" xmlns:ds="http://www.w3.org/2000/09/xmldsig#" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:xades="http://uri.etsi.org/01903/v1.3.2#" xmlns:n4="http://www.altova.com/samplexml/other-namespace" xsi:schemaLocation="urn:oasis:names:specification:ubl:schema:xsd:Invoice-2 ../xsdrt/maindoc/UBL-Invoice-2.1.xsd" />');
		$Invoice = $Content->getElementsByTagName('Invoice')->item(0);
		
		$UBLExtensions = $Invoice->appendChild($Content->createElement('ext:UBLExtensions'));
		$UBLExtension = $UBLExtensions->appendChild($Content->createElement('ext:UBLExtension'));
		$ExtensionContent = $UBLExtension->appendChild($Content->createElement('ext:ExtensionContent'));
		$ExtensionContent->appendChild($Content->createElement('n4:auto-generated_for_wildcard'));
		$Invoice->appendChild($Content->createElement('cbc:UBLVersionID', $Parameters["UBLVersionID"]));
		$Invoice->appendChild($Content->createElement('cbc:CustomizationID', $Parameters["CustomizationID"]));
		$Invoice->appendChild($Content->createElement('cbc:ProfileID', $Parameters["ProfileID"]));
		$Invoice->appendChild($Content->createElement('cbc:ID', $Parameters["ID"]));
		$Invoice->appendChild($Content->createElement('cbc:CopyIndicator', $Parameters["CopyIndicator"]));
		$Invoice->appendChild($Content->createElement('cbc:UUID', $Parameters["UUID"]));
		$Invoice->appendChild($Content->createElement('cbc:IssueDate', $Parameters["IssueDate"]));
		$Invoice->appendChild($Content->createElement('cbc:IssueTime', $Parameters["IssueTime"]));
		$Invoice->appendChild($Content->createElement('cbc:InvoiceTypeCode', $Parameters["InvoiceTypeCode"]));
		$Notes = str_getcsv($Parameters["Note"], $Parameters["Note"][0]);
		foreach($Notes as $Note) {
			if($Note != '') $Invoice->appendChild($Content->createElement('cbc:Note', $Note));
		}
		$Invoice->appendChild($Content->createElement('cbc:DocumentCurrencyCode', $Parameters["DocumentCurrencyCode"]));
		$Invoice->appendChild($Content->createElement('cbc:LineCountNumeric', $Parameters["LineCountNumeric"]));
		
		//=======================================================================================================//
		
		if($Parameters["BillingReference"]) {
			$BillingReference = $Invoice->appendChild($Content->createElement('cac:BillingReference'));
			$InvoiceDocumentReference = $BillingReference->appendChild($Content->createElement('cac:InvoiceDocumentReference'));
			$InvoiceDocumentReference->appendChild($Content->createElement('cbc:ID', $Parameters["BillingReference"]["InvoiceDocumentReference"]["ID"]));
			$InvoiceDocumentReference->appendChild($Content->createElement('cbc:IssueDate', $Parameters["BillingReference"]["InvoiceDocumentReference"]["IssueDate"]));
		}
		
		//=======================================================================================================//
		
		$ProNos = str_getcsv($Parameters["DespatchDocumentReference"]["ID"], $Parameters["DespatchDocumentReference"]["ID"][0]);
		$ProDates = str_getcsv($Parameters["DespatchDocumentReference"]["IssueDate"], $Parameters["DespatchDocumentReference"]["IssueDate"][0]);
		foreach($ProNos as $i => $ProNo) {
			$ProDate = $ProDates[$i];
			if($ProNo || $ProDate) {
				$DespatchDocumentReference = $Invoice->appendChild($Content->createElement('cac:DespatchDocumentReference'));
				if($ProNo) {
					$DespatchDocumentReference->appendChild($Content->createElement('cbc:ID', $ProNo));
				}
				if($ProDate) {
					$DespatchDocumentReference->appendChild($Content->createElement('cbc:IssueDate', $ProDate));
				}
			}
		}
		
		$AdditionalDocumentReference = $Invoice->appendChild($Content->createElement('cac:AdditionalDocumentReference'));
		$AdditionalDocumentReference->appendChild($Content->createElement('cbc:ID', $Parameters["AdditionalDocumentReference"]["ID"]));
		$AdditionalDocumentReference->appendChild($Content->createElement('cbc:IssueDate', $Parameters["AdditionalDocumentReference"]["IssueDate"]));
		$Attachment = $AdditionalDocumentReference->appendChild($Content->createElement('cac:Attachment'));
		$EmbeddedDocumentBinaryObject = $Attachment->appendChild($Content->createElement('cbc:EmbeddedDocumentBinaryObject', $Parameters["Attachment"]["EmbeddedDocumentBinaryObject"]));
		$characterSetCode = $Content->createAttribute('characterSetCode');
		$characterSetCode->value = $Parameters["Attachment"]["characterSetCode"];
		$EmbeddedDocumentBinaryObject->appendChild($characterSetCode);
		$encodingCode = $Content->createAttribute('encodingCode');
		$encodingCode->value = $Parameters["Attachment"]["encodingCode"];
		$EmbeddedDocumentBinaryObject->appendChild($encodingCode);
		$mimeCode = $Content->createAttribute('mimeCode');
		$mimeCode->value = $Parameters["Attachment"]["mimeCode"];
		$EmbeddedDocumentBinaryObject->appendChild($mimeCode);
		$filename = $Content->createAttribute('filename');
		$filename->value = $Parameters["Attachment"]["filename"];
		$EmbeddedDocumentBinaryObject->appendChild($filename);
		
		$SignParty = $Parameters["Signature"]["SignatoryParty"];
		$Signature = $Invoice->appendChild($Content->createElement('cac:Signature'));
		$ID = $Signature->appendChild($Content->createElement('cbc:ID', $Parameters["Signature"]["ID"]));
		$schemeID = $Content->createAttribute('schemeID');
		$schemeID->value = $Parameters["Signature"]["schemeID1"];
		$ID->appendChild($schemeID);
		$SignatoryParty = $Signature->appendChild($Content->createElement('cac:SignatoryParty'));
		$PartyIdentification = $SignatoryParty->appendChild($Content->createElement('cac:PartyIdentification'));
		$ID = $PartyIdentification->appendChild($Content->createElement('cbc:ID', $SignParty["PartyIdentification"]["ID"]));
		$schemeID = $Content->createAttribute('schemeID');
		$schemeID->value = $Parameters["Signature"]["schemeID2"];
		$ID->appendChild($schemeID);
		$Address = $SignParty["PostalAddress"];
		$PostalAddress = $SignatoryParty->appendChild($Content->createElement('cac:PostalAddress'));
		$PostalAddress->appendChild($Content->createElement('cbc:ID', $Address["ID"]));
		$PostalAddress->appendChild($Content->createElement('cbc:Room', $Address["Room"]));
		$PostalAddress->appendChild($Content->createElement('cbc:StreetName', $Address["StreetName"]));
		$PostalAddress->appendChild($Content->createElement('cbc:BuildingName', $Address["BuildingName"]));
		$PostalAddress->appendChild($Content->createElement('cbc:BuildingNumber', $Address["BuildingNumber"]));
		$PostalAddress->appendChild($Content->createElement('cbc:CitySubdivisionName', $Address["CitySubdivisionName"]));
		$PostalAddress->appendChild($Content->createElement('cbc:CityName', $Address["CityName"]));
		$PostalAddress->appendChild($Content->createElement('cbc:PostalZone', $Address["PostalZone"]));
		$Country = $PostalAddress->appendChild($Content->createElement('cac:Country'));
		$Country->appendChild($Content->createElement('cbc:Name', $Address["Country"]["Name"]));
		$DigitalSignatureAttachment = $Signature->appendChild($Content->createElement('cac:DigitalSignatureAttachment'));
		$ExternalReference = $DigitalSignatureAttachment->appendChild($Content->createElement('cac:ExternalReference'));
		$ExternalReference->appendChild($Content->createElement('cbc:URI', $Parameters["Signature"]["DigitalSignatureAttachment"]["ExternalReference"]["URI"]));
		
		$SupplierParty = $Parameters["AccountingSupplierParty"]["Party"];
		$AccountingSupplierParty = $Invoice->appendChild($Content->createElement('cac:AccountingSupplierParty'));
		$Party = $AccountingSupplierParty->appendChild($Content->createElement('cac:Party'));
		if($SupplierParty["WebsiteURI"]) $Party->appendChild($Content->createElement('cbc:WebsiteURI',  $SupplierParty["WebsiteURI"]));
		
		$PartyIdentificationIDs = str_getcsv($SupplierParty["PartyIdentification"]["ID"], $SupplierParty["PartyIdentification"]["ID"][0]);
		$PartyIdentificationSchemeIDs = str_getcsv($SupplierParty["PartyIdentification"]["schemeID"], $SupplierParty["PartyIdentification"]["schemeID"][0]);
		$i = 1;
		foreach($PartyIdentificationIDs as $PartyIdentificationID) {
			if($PartyIdentificationID != '') {
				$PartyIdentification = $Party->appendChild($Content->createElement('cac:PartyIdentification'));
				$ID = $PartyIdentification->appendChild($Content->createElement('cbc:ID', $PartyIdentificationID));
				$schemeID = $Content->createAttribute('schemeID');
				$schemeID->value = $PartyIdentificationSchemeIDs[$i];
				$ID->appendChild($schemeID);
				$i++;
			}
		}		
		
		$PartyName = $Party->appendChild($Content->createElement('cac:PartyName'));
		$PartyName->appendChild($Content->createElement('cbc:Name', $SupplierParty["PartyName"]["Name"]));
		$Address = $SupplierParty["PostalAddress"];
		$PostalAddress = $Party->appendChild($Content->createElement('cac:PostalAddress'));
		$PostalAddress->appendChild($Content->createElement('cbc:ID', $Address["ID"]));
		$PostalAddress->appendChild($Content->createElement('cbc:Room', $Address["Room"]));
		$PostalAddress->appendChild($Content->createElement('cbc:StreetName', $Address["StreetName"]));
		$PostalAddress->appendChild($Content->createElement('cbc:BuildingName', $Address["BuildingName"]));
		$PostalAddress->appendChild($Content->createElement('cbc:BuildingNumber', $Address["BuildingNumber"]));
		$PostalAddress->appendChild($Content->createElement('cbc:CitySubdivisionName', $Address["CitySubdivisionName"]));
		$PostalAddress->appendChild($Content->createElement('cbc:CityName', $Address["CityName"]));
		$PostalAddress->appendChild($Content->createElement('cbc:PostalZone', $Address["PostalZone"]));
		$Country = $PostalAddress->appendChild($Content->createElement('cac:Country'));
		$Country->appendChild($Content->createElement('cbc:Name', $Address["Country"]["Name"]));
		$PartyTaxScheme = $Party->appendChild($Content->createElement('cac:PartyTaxScheme'));
		$TaxScheme = $PartyTaxScheme->appendChild($Content->createElement('cac:TaxScheme'));
		$TaxScheme->appendChild($Content->createElement('cbc:Name', $SupplierParty["PartyTaxScheme"]["TaxScheme"]["Name"]));
		$Cont = $SupplierParty["Contact"];
		if($Cont["Telephone"] || $Cont["Telefax"] || $Cont["ElectronicMail"]) $Contact = $Party->appendChild($Content->createElement('cac:Contact'));
		if($Cont["Telephone"]) $Contact->appendChild($Content->createElement('cbc:Telephone', $Cont["Telephone"]));
		if($Cont["Telefax"]) $Contact->appendChild($Content->createElement('cbc:Telefax', $Cont["Telefax"]));
		if($Cont["ElectronicMail"]) $Contact->appendChild($Content->createElement('cbc:ElectronicMail', $Cont["ElectronicMail"]));
		
		$CustomerParty = $Parameters["AccountingCustomerParty"]["Party"];
		$AccountingCustomerParty = $Invoice->appendChild($Content->createElement('cac:AccountingCustomerParty'));
		$Party = $AccountingCustomerParty->appendChild($Content->createElement('cac:Party'));
		if($CustomerParty["WebsiteURI"]) $Party->appendChild($Content->createElement('cbc:WebsiteURI',  $CustomerParty["WebsiteURI"]));
		
		$PartyIdentificationIDs = str_getcsv($CustomerParty["PartyIdentification"]["ID"], $CustomerParty["PartyIdentification"]["ID"][0]);
		$PartyIdentificationSchemeIDs = str_getcsv($CustomerParty["PartyIdentification"]["schemeID"], $CustomerParty["PartyIdentification"]["schemeID"][0]);
		$i = 1;
		foreach($PartyIdentificationIDs as $PartyIdentificationID) {
			if($PartyIdentificationID != '') {
				$PartyIdentification = $Party->appendChild($Content->createElement('cac:PartyIdentification'));
				$ID = $PartyIdentification->appendChild($Content->createElement('cbc:ID', $PartyIdentificationID));
				$schemeID = $Content->createAttribute('schemeID');
				$schemeID->value = $PartyIdentificationSchemeIDs[$i];
				$ID->appendChild($schemeID);
				$i++;
			}
		}
		
		$PartyName = $Party->appendChild($Content->createElement('cac:PartyName'));
		$PartyName->appendChild($Content->createElement('cbc:Name', $CustomerParty["PartyName"]["Name"]));
		$Address = $CustomerParty["PostalAddress"];
		$PostalAddress = $Party->appendChild($Content->createElement('cac:PostalAddress'));
		$PostalAddress->appendChild($Content->createElement('cbc:ID', $Address["ID"]));
		$PostalAddress->appendChild($Content->createElement('cbc:Room', $Address["Room"]));
		$PostalAddress->appendChild($Content->createElement('cbc:StreetName', $Address["StreetName"]));
		$PostalAddress->appendChild($Content->createElement('cbc:BuildingName', $Address["BuildingName"]));
		$PostalAddress->appendChild($Content->createElement('cbc:BuildingNumber', $Address["BuildingNumber"]));
		$PostalAddress->appendChild($Content->createElement('cbc:CitySubdivisionName', $Address["CitySubdivisionName"]));
		$PostalAddress->appendChild($Content->createElement('cbc:CityName', $Address["CityName"]));
		$PostalAddress->appendChild($Content->createElement('cbc:PostalZone', $Address["PostalZone"]));
		$Country = $PostalAddress->appendChild($Content->createElement('cac:Country'));
		$Country->appendChild($Content->createElement('cbc:Name', $Address["Country"]["Name"]));
		$PartyTaxScheme = $Party->appendChild($Content->createElement('cac:PartyTaxScheme'));
		$TaxScheme = $PartyTaxScheme->appendChild($Content->createElement('cac:TaxScheme'));
		$TaxScheme->appendChild($Content->createElement('cbc:Name', $CustomerParty["PartyTaxScheme"]["TaxScheme"]["Name"]));
		$Cont = $CustomerParty["Contact"];
		if($Cont["Telephone"] || $Cont["Telefax"] || $Cont["ElectronicMail"]) $Contact = $Party->appendChild($Content->createElement('cac:Contact'));
		if($Cont["Telephone"]) $Contact->appendChild($Content->createElement('cbc:Telephone', $Cont["Telephone"]));
		if($Cont["Telefax"]) $Contact->appendChild($Content->createElement('cbc:Telefax', $Cont["Telefax"]));
		if($Cont["ElectronicMail"]) $Contact->appendChild($Content->createElement('cbc:ElectronicMail', $Cont["ElectronicMail"]));
		
		//=======================================================================================================//
		
		if($Parameters["BuyerCustomerParty"]) {
			$CustomerParty = $Parameters["BuyerCustomerParty"]["Party"];
			$BuyerCustomerParty = $Invoice->appendChild($Content->createElement('cac:BuyerCustomerParty'));
			$Party = $BuyerCustomerParty->appendChild($Content->createElement('cac:Party'));
			
			$PartyIdentificationIDs = str_getcsv($CustomerParty["PartyIdentification"]["ID"], $CustomerParty["PartyIdentification"]["ID"][0]);
			$PartyIdentificationSchemeIDs = str_getcsv($CustomerParty["PartyIdentification"]["schemeID"], $CustomerParty["PartyIdentification"]["schemeID"][0]);
			$i = 1;
			foreach($PartyIdentificationIDs as $PartyIdentificationID) {
				if($PartyIdentificationID != '') {
					$PartyIdentification = $Party->appendChild($Content->createElement('cac:PartyIdentification'));
					$ID = $PartyIdentification->appendChild($Content->createElement('cbc:ID', $PartyIdentificationID));
					$schemeID = $Content->createAttribute('schemeID');
					$schemeID->value = $PartyIdentificationSchemeIDs[$i];
					$ID->appendChild($schemeID);
					$i++;
				}
			}
			
			$Address = $CustomerParty["PostalAddress"];
			$PostalAddress = $Party->appendChild($Content->createElement('cac:PostalAddress'));
			$PostalAddress->appendChild($Content->createElement('cbc:ID', $Address["ID"]));
			$PostalAddress->appendChild($Content->createElement('cbc:Room', $Address["Room"]));
			$PostalAddress->appendChild($Content->createElement('cbc:StreetName', $Address["StreetName"]));
			$PostalAddress->appendChild($Content->createElement('cbc:BuildingName', $Address["BuildingName"]));
			$PostalAddress->appendChild($Content->createElement('cbc:BuildingNumber', $Address["BuildingNumber"]));
			$PostalAddress->appendChild($Content->createElement('cbc:CitySubdivisionName', $Address["CitySubdivisionName"]));
			$PostalAddress->appendChild($Content->createElement('cbc:CityName', $Address["CityName"]));
			$PostalAddress->appendChild($Content->createElement('cbc:PostalZone', $Address["PostalZone"]));
			$Country = $PostalAddress->appendChild($Content->createElement('cac:Country'));
			$Country->appendChild($Content->createElement('cbc:Name', $Address["Country"]["Name"]));
			$Person = $Party->appendChild($Content->createElement('cac:Person'));
			$Person->appendChild($Content->createElement('cbc:FirstName', $CustomerParty["Person"]["FirstName"]));
			$Person->appendChild($Content->createElement('cbc:FamilyName', $CustomerParty["Person"]["FamilyName"]));
		}
		
		//=======================================================================================================//
		
		if($Parameters["PaymentMeans"]) {
			$FinancialAccount = $Parameters["PaymentMeans"]["PayeeFinancialAccount"];
			$PaymentMeans = $Invoice->appendChild($Content->createElement('cac:PaymentMeans'));
			$PaymentMeans->appendChild($Content->createElement('cbc:PaymentMeansCode', $Parameters["PaymentMeans"]["PaymentMeansCode"]));
			$PayeeFinancialAccount = $PaymentMeans->appendChild($Content->createElement('cac:PayeeFinancialAccount'));
			$PayeeFinancialAccount->appendChild($Content->createElement('cbc:ID', $FinancialAccount["ID"]));
			$PayeeFinancialAccount->appendChild($Content->createElement('cbc:CurrencyCode', $FinancialAccount["CurrencyCode"]));
			$PayeeFinancialAccount->appendChild($Content->createElement('cbc:PaymentNote', $FinancialAccount["PaymentNote"]));
			$FinancialInstitutionBranch = $PayeeFinancialAccount->appendChild($Content->createElement('cac:FinancialInstitutionBranch'));
			$FinancialInstitutionBranch->appendChild($Content->createElement('cbc:Name', $FinancialAccount["FinancialInstitutionBranch"]["Name"]));
			$FinancialInstitution = $FinancialInstitutionBranch->appendChild($Content->createElement('cac:FinancialInstitution'));
			$FinancialInstitution->appendChild($Content->createElement('cbc:Name', $FinancialAccount["FinancialInstitutionBranch"]["FinancialInstitution"]["Name"]));
		}
		
		//=======================================================================================================//
		
		if($Parameters["PaymentTerms"]) {
			$PaymentTerms = $Invoice->appendChild($Content->createElement('cac:PaymentTerms'));
			$PaymentTerms->appendChild($Content->createElement('cbc:Note', $Parameters["PaymentTerms"]["Note"]));
			$PaymentTerms->appendChild($Content->createElement('cbc:PaymentDueDate', $Parameters["PaymentTerms"]["PaymentDueDate"]));
		}
		
		//=======================================================================================================//
		
		$TaxTotal = $Invoice->appendChild($Content->createElement('cac:TaxTotal'));
		$TaxAmount = $TaxTotal->appendChild($Content->createElement('cbc:TaxAmount', $Parameters["TaxTotal"]["TaxAmount"]));
		$currencyID = $Content->createAttribute('currencyID');
		$currencyID->value = $Parameters["TaxTotal"]["currencyID"];
		$TaxAmount->appendChild($currencyID);
		
		foreach($Parameters["TaxTotal"]["TaxSubtotals"] as $TaxSub) {
			$TaxSubtotal = $TaxTotal->appendChild($Content->createElement('cac:TaxSubtotal'));
			$TaxableAmount = $TaxSubtotal->appendChild($Content->createElement('cbc:TaxableAmount', $TaxSub["TaxableAmount"]));
			$currencyID = $Content->createAttribute('currencyID');
			$currencyID->value = $Parameters["TaxTotal"]["currencyID"];
			$TaxableAmount->appendChild($currencyID);
			$TaxAmount = $TaxSubtotal->appendChild($Content->createElement('cbc:TaxAmount', $TaxSub["TaxAmount"]));
			$currencyID = $Content->createAttribute('currencyID');
			$currencyID->value = $Parameters["TaxTotal"]["currencyID"];
			$TaxAmount->appendChild($currencyID);
			$TaxSubtotal->appendChild($Content->createElement('cbc:CalculationSequenceNumeric', $TaxSub["CalculationSequenceNumeric"]));
			$TaxSubtotal->appendChild($Content->createElement('cbc:Percent', $TaxSub["Percent"]));
			$TaxCategory = $TaxSubtotal->appendChild($Content->createElement('cac:TaxCategory'));
			//$TaxCategory->appendChild($Content->createElement('cbc:TaxExemptionReason', $TaxSub["TaxCategory"]["TaxExemptionReason"]));
			$Scheme = $TaxSub["TaxCategory"]["TaxScheme"];
			$TaxScheme = $TaxCategory->appendChild($Content->createElement('cac:TaxScheme'));
			$TaxScheme->appendChild($Content->createElement('cbc:Name', $Scheme["Name"]));
			$TaxScheme->appendChild($Content->createElement('cbc:TaxTypeCode', $Scheme["TaxTypeCode"]));
		}
		
		$MonetaryTotal = $Parameters["LegalMonetaryTotal"];
		$LegalMonetaryTotal = $Invoice->appendChild($Content->createElement('cac:LegalMonetaryTotal'));
		$LineExtensionAmount = $LegalMonetaryTotal->appendChild($Content->createElement('cbc:LineExtensionAmount', $MonetaryTotal["LineExtensionAmount"]));
		$currencyID = $Content->createAttribute('currencyID');
		$currencyID->value = $Parameters["LegalMonetaryTotal"]["currencyID"];
		$LineExtensionAmount->appendChild($currencyID);
		$TaxExclusiveAmount = $LegalMonetaryTotal->appendChild($Content->createElement('cbc:TaxExclusiveAmount', $MonetaryTotal["TaxExclusiveAmount"]));
		$currencyID = $Content->createAttribute('currencyID');
		$currencyID->value = $Parameters["LegalMonetaryTotal"]["currencyID"];
		$TaxExclusiveAmount->appendChild($currencyID);
		$TaxInclusiveAmount = $LegalMonetaryTotal->appendChild($Content->createElement('cbc:TaxInclusiveAmount', $MonetaryTotal["TaxInclusiveAmount"]));
		$currencyID = $Content->createAttribute('currencyID');
		$currencyID->value = $Parameters["LegalMonetaryTotal"]["currencyID"];
		$TaxInclusiveAmount->appendChild($currencyID);
		$AllowanceTotalAmount = $LegalMonetaryTotal->appendChild($Content->createElement('cbc:AllowanceTotalAmount', $MonetaryTotal["AllowanceTotalAmount"]));
		$currencyID = $Content->createAttribute('currencyID');
		$currencyID->value = $Parameters["LegalMonetaryTotal"]["currencyID"];
		$AllowanceTotalAmount->appendChild($currencyID);
		$PayableAmount = $LegalMonetaryTotal->appendChild($Content->createElement('cbc:PayableAmount', $MonetaryTotal["PayableAmount"]));
		$currencyID = $Content->createAttribute('currencyID');
		$currencyID->value = $Parameters["LegalMonetaryTotal"]["currencyID"];
		$PayableAmount->appendChild($currencyID);
		
		foreach($Parameters["InvoiceLines"] as $InLine) {
			$InvoiceLine = $Invoice->appendChild($Content->createElement('cac:InvoiceLine'));
			$InvoiceLine->appendChild($Content->createElement('cbc:ID', $InLine["ID"]));
			$InvoiceLine->appendChild($Content->createElement('cbc:Note', $InLine["Note"]));
			$InvoicedQuantity = $InvoiceLine->appendChild($Content->createElement('cbc:InvoicedQuantity', $InLine["InvoicedQuantity"]));
			$unitCode = $Content->createAttribute('unitCode');
			$unitCode->value = $InLine["unitCode"];
			$InvoicedQuantity->appendChild($unitCode);
			$LineExtensionAmount = $InvoiceLine->appendChild($Content->createElement('cbc:LineExtensionAmount', $InLine["LineExtensionAmount"]));
			$currencyID = $Content->createAttribute('currencyID');
			$currencyID->value = $InLine["currencyID"];
			$LineExtensionAmount->appendChild($currencyID);
			$Allowance = $InLine["AllowanceCharge"];
			$AllowanceCharge = $InvoiceLine->appendChild($Content->createElement('cac:AllowanceCharge'));
			$AllowanceCharge->appendChild($Content->createElement('cbc:ChargeIndicator', $Allowance["ChargeIndicator"]));
			$AllowanceCharge->appendChild($Content->createElement('cbc:AllowanceChargeReason', $Allowance["AllowanceChargeReason"]));
			$AllowanceCharge->appendChild($Content->createElement('cbc:MultiplierFactorNumeric',$Allowance["MultiplierFactorNumeric"]));
			$Amount = $AllowanceCharge->appendChild($Content->createElement('cbc:Amount', $Allowance["Amount"]));
			$currencyID = $Content->createAttribute('currencyID');
			$currencyID->value = $InLine["currencyID"];
			$Amount->appendChild($currencyID);
			$TaxTotal = $InvoiceLine->appendChild($Content->createElement('cac:TaxTotal'));
			$TaxAmount = $TaxTotal->appendChild($Content->createElement('cbc:TaxAmount', $InLine["TaxTotal"]["TaxAmount"]));
			$currencyID = $Content->createAttribute('currencyID');
			$currencyID->value = $InLine["currencyID"];
			$TaxAmount->appendChild($currencyID);
			$TaxSub = $InLine["TaxTotal"]["TaxSubtotal"];
			$TaxSubtotal = $TaxTotal->appendChild($Content->createElement('cac:TaxSubtotal'));
			$TaxableAmount = $TaxSubtotal->appendChild($Content->createElement('cbc:TaxableAmount', $TaxSub["TaxableAmount"]));
			$currencyID = $Content->createAttribute('currencyID');
			$currencyID->value = $InLine["currencyID"];
			$TaxableAmount->appendChild($currencyID);
			$TaxAmount = $TaxSubtotal->appendChild($Content->createElement('cbc:TaxAmount', $TaxSub["TaxAmount"]));
			$currencyID = $Content->createAttribute('currencyID');
			$currencyID->value = $InLine["currencyID"];
			$TaxAmount->appendChild($currencyID);
			$TaxSubtotal->appendChild($Content->createElement('cbc:CalculationSequenceNumeric', $TaxSub["CalculationSequenceNumeric"]));
			$TaxSubtotal->appendChild($Content->createElement('cbc:Percent', $TaxSub["Percent"]));
			$TaxCategory = $TaxSubtotal->appendChild($Content->createElement('cac:TaxCategory'));
			//$TaxCategory->appendChild($Content->createElement('cbc:TaxExemptionReason', $TaxSub["TaxCategory"]["TaxExemptionReason"]));
			$Scheme = $TaxSub["TaxCategory"]["TaxScheme"];
			$TaxScheme = $TaxCategory->appendChild($Content->createElement('cac:TaxScheme'));
			$TaxScheme->appendChild($Content->createElement('cbc:Name', $Scheme["Name"]));
			$TaxScheme->appendChild($Content->createElement('cbc:TaxTypeCode', $Scheme["TaxTypeCode"]));
			$Item = $InvoiceLine->appendChild($Content->createElement('cac:Item'));
			$Item->appendChild($Content->createElement('cbc:Name', $InLine["Item"]["Name"]));
			$Price = $InvoiceLine->appendChild($Content->createElement('cac:Price'));
			$PriceAmount = $Price->appendChild($Content->createElement('cbc:PriceAmount', $InLine["Price"]["PriceAmount"]));
			$currencyID = $Content->createAttribute('currencyID');
			$currencyID->value = $InLine["currencyID"];
			$PriceAmount->appendChild($currencyID);
		}
		
		if($Parameters["Save"]) {
			if(!file_exists('xml')) mkdir('xml');
			$Content->save('xml/' . $Parameters["ID"] . '.xml');
		}
		return $Content->saveXML();
	}
	
	function GetInvoiceData($Params1, $Params2) {	
		
		function zero($value) {
			if($value[0] == '.') $value = "0" . $value;
			
			return $value;
		}
		
		$username = $Params1->UserName;
		$password = $Params1->Password;
		$service = $Params1->Service;
		$invoiceid = $Params1->InvoiceID;
		$save = $Params1->Save;
		$logo = $Params1->Logo;
		
		putenv("NLS_LANG=AMERICAN_AMERICA.AL32UTF8");
		
		$connection = oci_connect($username, $password, $service);
		
		if(!$connection) {
			$error = oci_error();
		}
		
		if(!$error) {
			try {
				$InvoiceSQL = str_replace(':INV_ID', $invoiceid, $Params2["SQL"]["INVOICE_SQL"]);
				$BillingReferenceSQL = str_replace(':INV_ID', $invoiceid, $Params2["SQL"]["BILLING_REFERENCE_SQL"]);
				$SignatureSQL = str_replace(':INV_ID', $invoiceid, $Params2["SQL"]["SIGNATURE_SQL"]);
				$AccountingSupplierPartySQL = str_replace(':INV_ID', $invoiceid, $Params2["SQL"]["ACCOUNTING_SUPPLIER_PARTY_SQL"]);
				$AccountingCustomerPartySQL = str_replace(':INV_ID', $invoiceid, $Params2["SQL"]["ACCOUNTING_CUSTOMER_PARTY_SQL"]);
				$BuyerCustomerPartySQL = str_replace(':INV_ID', $invoiceid, $Params2["SQL"]["BUYER_CUSTOMER_PARTY_SQL"]);
				$PaymentMeansSQL = str_replace(':INV_ID', $invoiceid, $Params2["SQL"]["PAYMENT_MEANS_SQL"]);
				$PaymentTermsSQL = str_replace(':INV_ID', $invoiceid, $Params2["SQL"]["PAYMENT_TERMS_SQL"]);
				$TaxTotalSQL = str_replace(':INV_ID', $invoiceid, $Params2["SQL"]["TAX_TOTAL_SQL"]);
				$LegalMonetaryTotalSQL = str_replace(':INV_ID', $invoiceid, $Params2["SQL"]["LEGAL_MONETARY_TOTAL_SQL"]);
				$InvoiceLineSQL = str_replace(':INV_ID', $invoiceid, $Params2["SQL"]["INVOICE_LINE_SQL"]);
				
				$output = self::Questionize($connection, $InvoiceSQL);
				$error = $output["error"];
				if($error) throw new exception();
				$parse = $output["parse"];
				
				if($DataRow = oci_fetch_array($parse, OCI_RETURN_NULLS)) {
					$InvoiceData["UBLVersionID"] = $DataRow["UBLVERSIONID"];
					$InvoiceData["CustomizationID"] = $DataRow["CUSTOMIZATIONID"];
					$InvoiceData["ProfileID"] = $DataRow["PROFILEID"];
					$InvoiceData["ID"] = $DataRow["ID"];
					$InvoiceData["CopyIndicator"] = $DataRow["COPYINDICATOR"];
					$InvoiceData["UUID"] = substr(com_create_guid(), 1, 36);
					$InvoiceData["IssueDate"] = $DataRow["ISSUEDATE"];
					$InvoiceData["IssueTime"] = $DataRow["ISSUETIME"];
					$InvoiceData["InvoiceTypeCode"] = $DataRow["INVOICETYPECODE"];
					$InvoiceData["Note"] = $DataRow["NOTE"];
					$InvoiceData["DocumentCurrencyCode"] = $DataRow["DOCUMENTCURRENCYCODE"];
					$InvoiceData["LineCountNumeric"] = $DataRow["LINECOUNTNUMERIC"];
					
					$InvoiceData["DespatchDocumentReference"]["ID"] = $DataRow["DDR_ID"];
					$InvoiceData["DespatchDocumentReference"]["IssueDate"] = $DataRow["DDR_ISSUEDATE"];
					
					$InvoiceData["AdditionalDocumentReference"]["ID"] = substr(com_create_guid(), 1, 36);
					$InvoiceData["AdditionalDocumentReference"]["IssueDate"] = date("Y-m-d", filemtime('invoice.xslt'));
					
					$xslt = file_get_contents('invoice.xslt');
					
					if($logo && file_exists('logo.jpg')) {
						$logo = base64_encode(file_get_contents('logo.jpg', FILE_BINARY));
						$xslt = str_replace('<!-- <FirmaLogo> -->', '<img style="width:150px;height:150px;" align="middle" alt="Firma Logo" src="data:image/jpeg;base64,' . $logo . '"/>', $xslt);
					}
					
					$InvoiceData["Attachment"]["EmbeddedDocumentBinaryObject"] = base64_encode($xslt);
					$InvoiceData["Attachment"]["characterSetCode"] = 'UTF-8';
					$InvoiceData["Attachment"]["encodingCode"] = 'Base64';
					$InvoiceData["Attachment"]["mimeCode"] = 'application/xml';
					$InvoiceData["Attachment"]["filename"] = $DataRow["ID"] . '.xslt';
				}
				
				$UUIDSetSQL = " BEGIN UPDATE INVOICES SET UUID = '" . $InvoiceData["UUID"] . "' WHERE ID = $invoiceid; COMMIT; END; ";
				$output = self::Questionize($connection, $UUIDSetSQL);
				$error = $output["error"];
				if($error) throw new exception();
				
				//=======================================================================================================//
				
				$output = self::Questionize($connection, $BillingReferenceSQL);
				$error = $output["error"];
				if($error) throw new exception();
				$parse = $output["parse"];
				
				if($DataRow = oci_fetch_array($parse, OCI_RETURN_NULLS)) {
					$InvoiceDocumentReference["ID"] = $DataRow["ID"];
					$InvoiceDocumentReference["IssueDate"] = $DataRow["ISSUEDATE"];
					$BillingReference["InvoiceDocumentReference"] = $InvoiceDocumentReference;
					$InvoiceData["BillingReference"] = $BillingReference;
				}
				
				//=======================================================================================================//
				
				$output = self::Questionize($connection, $SignatureSQL);
				$error = $output["error"];
				if($error) throw new exception();
				$parse = $output["parse"];
				
				if($DataRow = oci_fetch_array($parse, OCI_RETURN_NULLS)) {
					$InvoiceData["Signature"]["ID"] = $DataRow["ID"];
					$SignatoryParty["PartyIdentification"]["ID"] = $DataRow["PARTYIDENTIFICATIONID"];
					$PostalAddress["ID"] = $DataRow["POSTALADDRESSID"];
					$PostalAddress["Room"] = $DataRow["ROOM"];
					$PostalAddress["StreetName"] = $DataRow["STREETNAME"];
					$PostalAddress["BuildingName"] = $DataRow["BUILDINGNAME"];
					$PostalAddress["BuildingNumber"] = $DataRow["BUILDINGNUMBER"];
					$PostalAddress["CitySubdivisionName"] = $DataRow["CITYSUBDIVISIONNAME"];
					$PostalAddress["CityName"] = $DataRow["CITYNAME"];
					$PostalAddress["PostalZone"] = $DataRow["POSTALZONE"];
					$PostalAddress["Country"]["Name"] = $DataRow["COUNTRYNAME"];
					$SignatoryParty["PostalAddress"] = $PostalAddress;
					$InvoiceData["Signature"]["SignatoryParty"] = $SignatoryParty;
					$InvoiceData["Signature"]["DigitalSignatureAttachment"]["ExternalReference"]["URI"] = $DataRow["URI"];
					$InvoiceData["Signature"]["schemeID1"] = $DataRow["SCHEMEID1"];
					$InvoiceData["Signature"]["schemeID2"] = $DataRow["SCHEMEID2"];
				}
				
				$output = self::Questionize($connection, $AccountingSupplierPartySQL);
				$error = $output["error"];
				if($error) throw new exception();
				$parse = $output["parse"];
				
				if($DataRow = oci_fetch_array($parse, OCI_RETURN_NULLS)) {
					$SupplierParty["WebsiteURI"] = $DataRow["WEBSITEURI"];
					$SupplierParty["PartyIdentification"]["ID"] = $DataRow["ID"]; //"|S_123|S_456|S_789";
					$SupplierParty["PartyIdentification"]["schemeID"] = $DataRow["SCHEMEID"]; //"|S_VKN|S_TICARETSICILNO|S_BAYINO";
					$SupplierParty["PartyName"]["Name"] = $DataRow["PARTYNAME"];
					$PostalAddress["ID"] = $DataRow["POSTALADDRESSID"];
					$PostalAddress["Room"] = $DataRow["ROOM"];
					$PostalAddress["StreetName"] = $DataRow["STREETNAME"];
					$PostalAddress["BuildingName"] = $DataRow["BUILDINGNAME"];
					$PostalAddress["BuildingNumber"] = $DataRow["BUILDINGNUMBER"];
					$PostalAddress["CitySubdivisionName"] = $DataRow["CITYSUBDIVISIONNAME"];
					$PostalAddress["CityName"] = $DataRow["CITYNAME"];
					$PostalAddress["PostalZone"] = $DataRow["POSTALZONE"];
					$PostalAddress["Country"]["Name"] = $DataRow["COUNTRYNAME"];
					$SupplierParty["PostalAddress"] = $PostalAddress;
					$SupplierParty["PartyTaxScheme"]["TaxScheme"]["Name"] = $DataRow["TAXSCHEMENAME"];
					$Contact["Telephone"] = $DataRow["TELEPHONE"];
					$Contact["Telefax"] = $DataRow["TELEFAX"];
					$Contact["ElectronicMail"] = $DataRow["ELECTRONICMAIL"];
					$SupplierParty["Contact"] = $Contact;
					$InvoiceData["AccountingSupplierParty"]["Party"] = $SupplierParty;
					//$InvoiceData["AccountingSupplierParty"]["schemeID"] = $DataRow["SCHEMEID"];
				}
				
				$output = self::Questionize($connection, $AccountingCustomerPartySQL);
				$error = $output["error"];
				if($error) throw new exception();
				$parse = $output["parse"];
				
				if($DataRow = oci_fetch_array($parse, OCI_RETURN_NULLS)) {
					$CustomerParty["WebsiteURI"] = $DataRow["WEBSITEURI"];
					$CustomerParty["PartyIdentification"]["ID"] = $DataRow["ID"]; //"|C_123|C_456|C_789";
					$CustomerParty["PartyIdentification"]["schemeID"] = $DataRow["SCHEMEID"]; //"|C_VKN|C_TICARETSICILNO|C_BAYINO";
					$CustomerParty["PartyName"]["Name"] = $DataRow["PARTYNAME"];
					$PostalAddress["ID"] = $DataRow["POSTALADDRESSID"];
					$PostalAddress["Room"] = $DataRow["ROOM"];
					$PostalAddress["StreetName"] = $DataRow["STREETNAME"];
					$PostalAddress["BuildingName"] = $DataRow["BUILDINGNAME"];
					$PostalAddress["BuildingNumber"] = $DataRow["BUILDINGNUMBER"];
					$PostalAddress["CitySubdivisionName"] = $DataRow["CITYSUBDIVISIONNAME"];
					$PostalAddress["CityName"] = $DataRow["CITYNAME"];
					$PostalAddress["PostalZone"] = $DataRow["POSTALZONE"];
					$PostalAddress["Country"]["Name"] = $DataRow["COUNTRYNAME"];
					$CustomerParty["PostalAddress"] = $PostalAddress;
					$CustomerParty["PartyTaxScheme"]["TaxScheme"]["Name"] = $DataRow["TAXSCHEMENAME"];
					$Contact["Telephone"] = $DataRow["TELEPHONE"];
					$Contact["Telefax"] = $DataRow["TELEFAX"];
					$Contact["ElectronicMail"] = $DataRow["ELECTRONICMAIL"];
					$CustomerParty["Contact"] = $Contact;
					$InvoiceData["AccountingCustomerParty"]["Party"] = $CustomerParty;
					//$InvoiceData["AccountingCustomerParty"]["schemeID"] = $DataRow["SCHEMEID"];
				}
				
				//=======================================================================================================//
				
				$output = self::Questionize($connection, $BuyerCustomerPartySQL);
				$error = $output["error"];
				if($error) throw new exception();
				$parse = $output["parse"];
				
				if($DataRow = oci_fetch_array($parse, OCI_RETURN_NULLS)) {
					$BuyerCustomerParty["PartyIdentification"]["ID"] = $DataRow["ID"]; //"|B_10101010101|B_123456789";
					$BuyerCustomerParty["PartyIdentification"]["schemeID"] = $DataRow["SCHEMEID"]; //|B_TCKNO|B_PROTOKOLNO";
					$PostalAddress["ID"] = $DataRow["POSTALADDRESSID"];
					$PostalAddress["Room"] = $DataRow["ROOM"];
					$PostalAddress["StreetName"] = $DataRow["STREETNAME"];
					$PostalAddress["BuildingName"] = $DataRow["BUILDINGNAME"];
					$PostalAddress["BuildingNumber"] = $DataRow["BUILDINGNUMBER"];
					$PostalAddress["CitySubdivisionName"] = $DataRow["CITYSUBDIVISIONNAME"];
					$PostalAddress["CityName"] = $DataRow["CITYNAME"];
					$PostalAddress["PostalZone"] = $DataRow["POSTALZONE"];
					$PostalAddress["Country"]["Name"] = $DataRow["COUNTRYNAME"];
					$BuyerCustomerParty["PostalAddress"] = $PostalAddress;
					$Person["FirstName"] = $DataRow["FIRSTNAME"];
					$Person["FamilyName"] = $DataRow["FAMILYNAME"];
					$BuyerCustomerParty["Person"] = $Person;
					$InvoiceData["BuyerCustomerParty"]["Party"] = $BuyerCustomerParty;
					//$InvoiceData["BuyerCustomerParty"]["schemeID"] = $DataRow["SCHEMEID"];
				}
				
				//=======================================================================================================//
				
				$output = self::Questionize($connection, $PaymentMeansSQL);
				$error = $output["error"];
				if($error) throw new exception();
				$parse = $output["parse"];
				
				if($DataRow = oci_fetch_array($parse, OCI_RETURN_NULLS)) {
					$PaymentMeans["PaymentMeansCode"] = $DataRow["PAYMENTMEANSCODE"];
					$PayeeFinancialAccount["ID"] = $DataRow["ID"];
					$PayeeFinancialAccount["CurrencyCode"] = $DataRow["CURRENCYCODE"];
					$PayeeFinancialAccount["PaymentNote"] = $DataRow["PAYMENTNOTE"];
					$FinancialInstitutionBranch["Name"] = $DataRow["NAME"];
					$FinancialInstitutionBranch["FinancialInstitution"]["Name"] = $DataRow["FINANCIALINSTITUTIONNAME"];
					$PayeeFinancialAccount["FinancialInstitutionBranch"] = $FinancialInstitutionBranch;
					$PaymentMeans["PayeeFinancialAccount"] = $PayeeFinancialAccount;
					$InvoiceData["PaymentMeans"] = $PaymentMeans;
				}
				
				//=======================================================================================================//
				
				$output = self::Questionize($connection, $PaymentTermsSQL);
				$error = $output["error"];
				if($error) throw new exception();
				$parse = $output["parse"];
				
				if($DataRow = oci_fetch_array($parse, OCI_RETURN_NULLS)) {
					$PaymentTerms["Note"] = $DataRow["NOTE"];
					$PaymentTerms["PaymentDueDate"] = $DataRow["PAYMENTDUEDATE"];
					$InvoiceData["PaymentTerms"] = $PaymentTerms;
				}
				
				//=======================================================================================================//
				
				$output = self::Questionize($connection, $TaxTotalSQL);
				$error = $output["error"];
				if($error) throw new exception();
				$parse = $output["parse"];
				
				while($DataRow = oci_fetch_array($parse, OCI_RETURN_NULLS)) {
					$TaxSubtotal["TaxableAmount"] = zero($DataRow["TAXABLEAMOUNT"]);
					$TaxSubtotal["TaxAmount"] = zero($DataRow["TAXAMOUNT"]);
					$TaxSubtotal["CalculationSequenceNumeric"] = $DataRow["CALCULATIONSEQUENCENUMERIC"];
					$TaxSubtotal["Percent"] = $DataRow["PERCENT"];
					//$TaxSubtotal["TaxCategory"]["TaxExemptionReason"] = $DataRow["TAXEXEMPTIONREASON"];
					$TaxSubtotal["TaxCategory"]["TaxScheme"]["Name"] = $DataRow["NAME"];
					$TaxSubtotal["TaxCategory"]["TaxScheme"]["TaxTypeCode"] = $DataRow["TAXTYPECODE"];
					$currencyID = $DataRow["CURRENCYID"];
					$TaxSubtotals[] = $TaxSubtotal;
					$SumTaxAmount += $DataRow["TAXAMOUNT"];
				}
				$InvoiceData["TaxTotal"]["TaxAmount"] = zero($SumTaxAmount);
				$InvoiceData["TaxTotal"]["TaxSubtotals"] = $TaxSubtotals;
				$InvoiceData["TaxTotal"]["currencyID"] = $currencyID;
				
				$output = self::Questionize($connection, $LegalMonetaryTotalSQL);
				$error = $output["error"];
				if($error) throw new exception();
				$parse = $output["parse"];
				
				if($DataRow = oci_fetch_array($parse, OCI_RETURN_NULLS)) {

					$LegalMonetaryTotal["LineExtensionAmount"] = zero($DataRow["LINEEXTENSIONAMOUNT"]);
					$LegalMonetaryTotal["TaxExclusiveAmount"] = zero($DataRow["TAXEXCLUSIVEAMOUNT"]);
					$LegalMonetaryTotal["TaxInclusiveAmount"] = zero($DataRow["TAXINCLUSIVEAMOUNT"]);
					$LegalMonetaryTotal["AllowanceTotalAmount"] = zero($DataRow["ALLOWANCETOTALAMOUNT"]);
					$LegalMonetaryTotal["PayableAmount"] = zero($DataRow["PAYABLEAMOUNT"]);
					$LegalMonetaryTotal["currencyID"] = $DataRow["CURRENCYID"];
					$InvoiceData["LegalMonetaryTotal"] = $LegalMonetaryTotal;
				}
				
				$output = self::Questionize($connection, $InvoiceLineSQL);
				$error = $output["error"];
				if($error) throw new exception();
				$parse = $output["parse"];
				
				while($DataRow = oci_fetch_array($parse, OCI_RETURN_NULLS)) {
					$InvoiceLine["ID"] = $DataRow["ID"];
					$InvoiceLine["Note"] = $DataRow["NOTE"];
					$InvoiceLine["InvoicedQuantity"] = $DataRow["INVOICEDQUANTITY"];
					$InvoiceLine["unitCode"] = $DataRow["UNITCODE"];
					$InvoiceLine["LineExtensionAmount"] = zero($DataRow["LINEEXTENSIONAMOUNT"]);
					$AllowanceCharge["ChargeIndicator"] = $DataRow["CHARGEINDICATOR"];
					$AllowanceCharge["AllowanceChargeReason"] = $DataRow["ALLOWANCECHARGEREASON"];
					$AllowanceCharge["MultiplierFactorNumeric"] = zero($DataRow["MULTIPLIERFACTORNUMERIC"]);
					$AllowanceCharge["Amount"] = zero($DataRow["AMOUNT"]);
					$InvoiceLine["AllowanceCharge"] = $AllowanceCharge;
					$TaxTotal["TaxAmount"] = zero($DataRow["TOTALTAXAMOUNT"]);
					$TaxSubtotal["TaxableAmount"] = zero($DataRow["TAXABLEAMOUNT"]);
					$TaxSubtotal["TaxAmount"] = zero($DataRow["TAXAMOUNT"]);
					$TaxSubtotal["CalculationSequenceNumeric"] = $DataRow["CALCULATIONSEQUENCENUMERIC"];
					$TaxSubtotal["Percent"] = $DataRow["PERCENT"];
					//$TaxSubtotal["TaxCategory"]["TaxExemptionReason"] = $DataRow["TAXEXEMPTIONREASON"];
					$TaxSubtotal["TaxCategory"]["TaxScheme"]["Name"] = $DataRow["TAXSCHEMENAME"];
					$TaxSubtotal["TaxCategory"]["TaxScheme"]["TaxTypeCode"] = $DataRow["TAXTYPECODE"];
					$TaxTotal["TaxSubtotal"] = $TaxSubtotal;
					$InvoiceLine["TaxTotal"] = $TaxTotal;
					$InvoiceLine["Item"]["Name"] = $DataRow["ITEMNAME"];
					$InvoiceLine["Price"]["PriceAmount"] = zero($DataRow["PRICEAMOUNT"]);
					$InvoiceLine["currencyID"] = $DataRow["CURRENCYID"];
					$InvoiceData["InvoiceLines"][] = $InvoiceLine;
				}
				
				$Result["INVOICE_DATA"] = $InvoiceData;
				$Result["INVOICE_DATA"]["Save"] = $save;
				$Result["ReturnCode"] = "0";
				
				oci_close($connection); 
				
			} catch(exception $except) {
				$Result["ReturnCode"] = "\nErrorCode : " . $error["code"] . "\nMessage : " . $error["message"] . "\nOffset : " . $error["offset"] . "\nSqlText : " . $error["sqltext"];
			}
		} else {
			$Result["ReturnCode"] = "\nErrorCode : " . $error["code"] . "\nMessage : " . $error["message"] . "\nOffset : " . $error["offset"] . "\nSqlText : " . $error["sqltext"];
		}
		
		return $Result;
	}
	
	function Login($Parameters) {
		try {
			$username = $Parameters->UserName;
			
			$password = $Parameters->Password;
			$service = $Parameters->Service;
			$invoiceid = $Parameters->InvoiceID;
			$portaltype = $Parameters->PortalType;
			$xmldisplay = $Parameters->XmlDisplay;
			
			if($portaltype == "") $portaltype = "real";
			$connection = oci_connect($username, $password, $service);
			$inifile = parse_ini_file("efatura.ini", true);
			if($portaltype == "test") {
				$portal = $inifile["WSDL"]["TEST_PORTAL"];
			} else if($portaltype == "real") {
				$portal = $inifile["WSDL"]["REAL_PORTAL"];
			}
			
			putenv("NLS_LANG=AMERICAN_AMERICA.AL32UTF8");
			$connection = oci_connect($username, $password, $service);
			
			if(!$connection) {
				$error = oci_error();
			}
			
			if(!$error) {
				try {
					$query = "	SELECT
								  A.REQUEST_SQL,
								  A.INVOICE_SQL,
								  A.BILLING_REFERENCE_SQL,
								  A.SIGNATURE_SQL,
								  A.ACCOUNTING_SUPPLIER_PARTY_SQL,
								  A.ACCOUNTING_CUSTOMER_PARTY_SQL,
								  A.BUYER_CUSTOMER_PARTY_SQL,
								  A.PAYMENT_MEANS_SQL,
								  A.PAYMENT_TERMS_SQL,
								  A.TAX_TOTAL_SQL,
								  A.LEGAL_MONETARY_TOTAL_SQL,
								  A.INVOICE_LINE_SQL
								FROM ASSOCIATION_INVOICE_FORMS A
								JOIN INVOICES INV ON INV.ASS_ID = A.ASS_ID
								JOIN PATIENT_TRANSACTIONS PT ON PT.ID = INV.PT_ID AND PT.OFF_ID = A.OFF_ID
								WHERE INV.ID = $invoiceid ";
					
					$output = self::Questionize($connection, $query);
					$error = $output["error"];
					if($error) throw new exception();
					$parse = $output["parse"];
					
					if(!($DataRow = oci_fetch_array($parse, OCI_RETURN_NULLS))) {
						$query = "	SELECT
									  A.REQUEST_SQL,
									  A.INVOICE_SQL,
									  A.BILLING_REFERENCE_SQL,
									  A.SIGNATURE_SQL,
									  A.ACCOUNTING_SUPPLIER_PARTY_SQL,
									  A.ACCOUNTING_CUSTOMER_PARTY_SQL,
									  A.BUYER_CUSTOMER_PARTY_SQL,
									  A.PAYMENT_MEANS_SQL,
									  A.PAYMENT_TERMS_SQL,
									  A.TAX_TOTAL_SQL,
									  A.LEGAL_MONETARY_TOTAL_SQL,
									  A.INVOICE_LINE_SQL
									FROM ASSOCIATION_INVOICE_FORMS A
									WHERE A.ID = 100 ";
						
						$output = self::Questionize($connection, $query);
						$error = $output["error"];
						if($error) throw new exception();
						$parse = $output["parse"];
						
						$DataRow = oci_fetch_array($parse, OCI_RETURN_NULLS);
					}
				} catch(exception $except) {
					$Result["ReturnCode"] = "\nErrorCode : " . $error["code"] . "\nMessage : " . $error["message"] . "\nOffset : " . $error["offset"] . "\nSqlText : " . $error["sqltext"];
					return $Result;
				}
			} else {
				$Result["ReturnCode"] = "\nErrorCode : " . $error["code"] . "\nMessage : " . $error["message"] . "\nOffset : " . $error["offset"] . "\nSqlText : " . $error["sqltext"];
				return $Result;
			}
			
			$Result["SQL"]["REQUEST_SQL"] = $DataRow["REQUEST_SQL"];
			$Result["SQL"]["INVOICE_SQL"] = $DataRow["INVOICE_SQL"];
			$Result["SQL"]["BILLING_REFERENCE_SQL"] = $DataRow["BILLING_REFERENCE_SQL"];
			$Result["SQL"]["SIGNATURE_SQL"] = $DataRow["SIGNATURE_SQL"];
			$Result["SQL"]["ACCOUNTING_SUPPLIER_PARTY_SQL"] = $DataRow["ACCOUNTING_SUPPLIER_PARTY_SQL"];
			$Result["SQL"]["ACCOUNTING_CUSTOMER_PARTY_SQL"] = $DataRow["ACCOUNTING_CUSTOMER_PARTY_SQL"];
			$Result["SQL"]["BUYER_CUSTOMER_PARTY_SQL"] = $DataRow["BUYER_CUSTOMER_PARTY_SQL"];
			$Result["SQL"]["PAYMENT_MEANS_SQL"] = $DataRow["PAYMENT_MEANS_SQL"];
			$Result["SQL"]["PAYMENT_TERMS_SQL"] = $DataRow["PAYMENT_TERMS_SQL"];
			$Result["SQL"]["TAX_TOTAL_SQL"] = $DataRow["TAX_TOTAL_SQL"];
			$Result["SQL"]["LEGAL_MONETARY_TOTAL_SQL"] = $DataRow["LEGAL_MONETARY_TOTAL_SQL"];
			$Result["SQL"]["INVOICE_LINE_SQL"] = $DataRow["INVOICE_LINE_SQL"];
			
			if($portaltype == "real") {
				$output = self::Questionize($connection, str_replace(':INV_ID', $invoiceid, $Result["SQL"]["REQUEST_SQL"]));
				$error = $output["error"];
				if($error) throw new exception();
				$parse = $output["parse"];
				
				if($DataRow = oci_fetch_array($parse, OCI_RETURN_NULLS)) {
					$Req["USER_NAME"] = $DataRow["USER_NAME"];
					$Req["PASSWORD"] = $DataRow["PASSWORD"];
					$RequestHeader["SESSION_ID"] = "-1";
					//$RequestHeader["REASON"] = "FATURA_GONDERME_TEST";
					//$RequestHeader["APPLICATION_NAME"] = "ENTEGRASYON_TEST_CLIENT";
					//$RequestHeader["HOSTNAME"] = "SINERJI";
					//$RequestHeader["CHANNEL_NAME"] = "SINERJI_TEST_CHANNEL";
					$RequestHeader["COMPRESSED"] = "N";
					$Req["REQUEST_HEADER"] = $RequestHeader;
					$Sender["vkn"] = $DataRow["SENDER_VKN"];
					$Sender["alias"] = $DataRow["SENDER_ALIAS"];
					$Req["SENDER"] = $Sender;
					$Receiver["vkn"] = $DataRow["RECEIVER_VKN"];
					$Receiver["alias"] = $DataRow["RECEIVER_ALIAS"];
					$Req["RECEIVER"] = $Receiver;
					$Header["SENDER"] = $DataRow["SENDER_VKN"];
					$Header["RECEIVER"] = $DataRow["RECEIVER_VKN"];
					//$Header["SUPPLIER"] = "";
					//$Header["CUSTOMER"] = "";
					//$Header["ISSUE_DATE"] = "";
					//$Header["PAYABLE_AMOUNT"] = "";
					$Header["FROM"] = $DataRow["SENDER_ALIAS"];
					$Header["TO"] = $DataRow["RECEIVER_ALIAS"];
					$Req["INVOICE"]["HEADER"] = $Header;
					$Result["REQUEST"] = $Req;
				}
			} else if($portaltype == "test") {
				$Req["USER_NAME"] =	$inifile["REQUEST"]["USER_NAME"];
				$Req["PASSWORD"] = $inifile["REQUEST"]["PASSWORD"];
				$RequestHeader["SESSION_ID"] = "-1";
				$RequestHeader["COMPRESSED"] = "N";
				$Req["REQUEST_HEADER"] = $RequestHeader;
				$Req["SENDER"] = $inifile["SENDER"];
				$Req["RECEIVER"] = $inifile["RECEIVER"];
				$Req["INVOICE"]["HEADER"] = $inifile["HEADER"];
				$Result["REQUEST"] = $Req;
			}
			
			$Request["REQUEST_HEADER"] = $Req["REQUEST_HEADER"];
			$Request["USER_NAME"] = $Req["USER_NAME"];
			$Request["PASSWORD"] = $Req["PASSWORD"];
			
			$client = new SoapClient($portal, array('trace' => 1));
			$Res = $client->Login($Request);
			
			$Result["REQUEST"]["REQUEST_HEADER"]["SESSION_ID"] = $Res->SESSION_ID;
			$Result["PORTAL"] = $portal;
			$Result["XML_DISPLAY"] = $portal;
			
			$Result["ReturnCode"] = "0";
			if($xmldisplay) $Result["Message"] = "<br><br>" . self::print_xml($client);
			return $Result;
			
		} catch(Exception $except) {
			$Result["ReturnCode"] = "\nErrorCode : " . $except->detail->RequestFault->ERROR_CODE . "\nMessage : " . $except->detail->RequestFault->ERROR_LONG_DES;
			if($xmldisplay) $Result["Message"] = "<br><br>" . self::print_xml($client);
			return $Result;
		}
	}
	
	function Logout($Params) {
		try {
			$xmldisplay = $Params["XML_DISPLAY"];
			
			$Request["REQUEST_HEADER"] = $Params["REQUEST"]["REQUEST_HEADER"];
			
			$client = new SoapClient($Params["PORTAL"], array('trace' => 1));
			$Res = $client->Logout($Request);
			
			$Result["ReturnCode"] = $Res->REQUEST_RETURN->RETURN_CODE;
			if($xmldisplay) $Result["Message"] = "<br><br>" . self::print_xml($client);
			return $Result;
			
		} catch(Exception $except) {
			$Result["ReturnCode"] = "\nErrorCode : " . $except->detail->RequestFault->ERROR_CODE . "\nMessage : " . $except->detail->RequestFault->ERROR_LONG_DES;
			if($xmldisplay) $Result["Message"] = "<br><br>" . self::print_xml($client);
			return $Result;
		}
	}
	
	function LoadInvoice($Params1, $Params2) {
		try {
			$xmldisplay = $Params1->XmlDisplay;
			
			$Invoice = self::GetInvoiceData($Params1, $Params2);
			if($Invoice["ReturnCode"] != "0") {
				return $Invoice;
			}
			
			$Request["REQUEST_HEADER"] = $Params2["REQUEST"]["REQUEST_HEADER"];
			$Request["INVOICE"] = $Params2["REQUEST"]["INVOICE"];
			$Request["INVOICE"]["CONTENT"] = self::CreateInvoice($Invoice["INVOICE_DATA"]);
			
			$client = new SoapClient($Params2["PORTAL"], array('trace' => 1));
			$Res = $client->LoadInvoice($Request);
			
			$Result["ReturnCode"] = $Res->REQUEST_RETURN->RETURN_CODE;
			if($xmldisplay) $Result["Message"] = "<br><br>" . self::print_xml($client);
			return $Result;
			
		} catch(Exception $except) {
			$Result["ReturnCode"] = "\nErrorCode : " . $except->detail->RequestFault->ERROR_CODE . "\nMessage : " . $except->detail->RequestFault->ERROR_LONG_DES;
			if($xmldisplay) $Result["Message"] = "<br><br>" . self::print_xml($client);
			return $Result;
		}
	}
	
	function SendInvoice($Params1, $Params2) {
		try {
			$xmldisplay = $Params1->XmlDisplay;
			
			$Invoice = self::GetInvoiceData($Params1, $Params2);
			if($Invoice["ReturnCode"] != "0") {
				return $Invoice;
			}
			
			$Request["REQUEST_HEADER"] = $Params2["REQUEST"]["REQUEST_HEADER"];
			$Request["SENDER"] = $Params2["REQUEST"]["SENDER"];
			$Request["RECEIVER"] = $Params2["REQUEST"]["RECEIVER"];
			$Request["INVOICE"] = $Params2["REQUEST"]["INVOICE"];
			$Request["INVOICE"]["CONTENT"] = self::CreateInvoice($Invoice["INVOICE_DATA"]);
			
			$client = new SoapClient($Params2["PORTAL"], array('trace' => 1));
			$Res = $client->SendInvoice($Request);
			
			$Result["ReturnCode"] = $Res->REQUEST_RETURN->RETURN_CODE;
			if($xmldisplay) $Result["Message"] = "<br><br>" . self::print_xml($client);
			return $Result;
			
		} catch(Exception $except) {
			$Result["ReturnCode"] = "\nErrorCode : " . $except->detail->RequestFault->ERROR_CODE . "\nMessage : " . $except->detail->RequestFault->ERROR_LONG_DES;
			if($xmldisplay) $Result["Message"] = "<br><br>" . self::print_xml($client);
			return $Result;
		}
	}
	
	function LoadInvoiceWithInvoiceID($Parameters) {
		
		$Login = self::Login($Parameters);
		if($Login["ReturnCode"] != "0") {
			return $Login;
		}
		
		$Result = self::LoadInvoice($Parameters, $Login);
		if($Result["ReturnCode"] != "0") {
			return $Result;
		}
			
		$Result = self::Logout($Login);
		if($Result["ReturnCode"] != "0") {
			return $Result;
		}
		
		return $Result;
	}
	
	function SendInvoiceWithInvoiceID($Parameters) {
		
		$Login = self::Login($Parameters);
		if($Login["ReturnCode"] != "0") {
			return $Login;
		}
		
		$Result = self::SendInvoice($Parameters, $Login);
		if($Result["ReturnCode"] != "0") {
			return $Result;
		}
			
		$Result = self::Logout($Login);
		if($Result["ReturnCode"] != "0") {
			return $Result;
		}
		
		return $Result;
	}
	
	function CheckUser() {
		
	}
	
	function GetUserList() {
		
	}
}

$inifile = parse_ini_file("efatura.ini", true);
$url = $inifile["WSDL"]["URL"];
$server = new SoapServer($url . "efatura_wsdl.php", array('encoding' => 'utf-8'));
$server->setClass("efaturaService");
$server->handle();
?>