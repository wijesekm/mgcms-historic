<?php

/*!
 * @file		ewsauth.class.php
 * @author 		Kevin Wijesekera
 * @copyright 	2015
 * @edited		9-5-2015

 ###################################
 This program is free software: you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation, either version 3 of the License, or
 (at your option) any later version.

 This program is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.

 You should have received a copy of the GNU General Public License
 along with this program.  If not, see http://www.gnu.org/licenses/.
 ###################################
 */

if(!defined('STARTED')){
	die();
}

class ewsauth extends auth{

	final public function auth_authenticate($username,$password){
		$ch = curl_init();
		$opts = array(
				CURLOPT_URL             => $GLOBALS['MG']['SITE']['EWS_SERVER'].'/ews/Services.wsdl',
				CURLOPT_HTTPAUTH        => CURLAUTH_NTLM,
				CURLOPT_RETURNTRANSFER  => true,
				CURLOPT_USERPWD         => $username.':'.$password,
				CURLOPT_TIMEOUT         => 10,
				CURLOPT_CONNECTTIMEOUT  => 10,
				CURLOPT_FOLLOWLOCATION  => true,
				CURLOPT_HEADER          => 0,
				CURLOPT_IPRESOLVE       => CURL_IPRESOLVE_V4,
				CURLOPT_SSL_VERIFYPEER  => true,
				CURLOPT_SSL_VERIFYHOST  => 2,
		        CURLOPT_FRESH_CONNECT   => true,
		        CURLOPT_USERAGENT       => 'Advantage+ CURL',
		        CURLOPT_HTTP_VERSION    => CURL_HTTP_VERSION_1_1
		);
		// Set the appropriate content-type.
		curl_setopt_array($ch, $opts);
		$response = curl_exec($ch);
		$info = curl_getinfo( $ch );
		if($info['http_code']=='200'){
			return true;
		}
		else if($info['http_code'] == 0){
		    trigger_error('(ewsauth): Could not connect to server: '.$GLOBALS['MG']['SITE']['EWS_SERVER'],E_USER_WARNING);
		}
		else if($info['http_code'] != 403){
		    trigger_error('(ewsauth): Server unable to handle query: '.$info['http_code'].' '.$response,E_USER_WARNING);
		}
		return false;
	}

	final public function auth_canChangePass(){
	    return false;
	}

	final public function auth_changePass($uid,$newPass){
		return false;
	}

	final public function auth_getAutoReg($uid,$password){
		$ch = curl_init();
		$opts = array(
				CURLOPT_URL             => $GLOBALS['MG']['SITE']['EWS_SERVER'].'/ews/Exchange.asmx',
				CURLOPT_HTTPAUTH        => CURLAUTH_NTLM,
				CURLOPT_CUSTOMREQUEST   => 'POST',
				CURLOPT_POSTFIELDS      =>
'<?xml version="1.0" encoding="utf-8"?>
<soap:Envelope xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
               xmlns:xsd="http://www.w3.org/2001/XMLSchema"
               xmlns:soap="http://schemas.xmlsoap.org/soap/envelope/"
               xmlns:t="http://schemas.microsoft.com/exchange/services/2006/types">
  <soap:Body>
    <ResolveNames xmlns="http://schemas.microsoft.com/exchange/services/2006/messages"
                  xmlns:t="http://schemas.microsoft.com/exchange/services/2006/types"
                  ReturnFullContactData="true">
      <UnresolvedEntry>'.$uid.'@'.$GLOBALS['MG']['SITE']['EWS_DOMAIN'].'</UnresolvedEntry>
    </ResolveNames>
  </soap:Body>
</soap:Envelope>
',
				CURLOPT_RETURNTRANSFER  => true,
				CURLOPT_USERPWD         => $uid.':'.$password,
				CURLOPT_TIMEOUT         => 10,
				CURLOPT_CONNECTTIMEOUT  => 10,
				CURLOPT_FOLLOWLOCATION  => true,
				CURLOPT_HEADER          => false,
				CURLOPT_IPRESOLVE       => CURL_IPRESOLVE_V4,
				CURLOPT_SSL_VERIFYPEER  => true,
				CURLOPT_SSL_VERIFYHOST  => 2,
		        CURLOPT_FRESH_CONNECT   => true,
		        CURLOPT_USERAGENT       => 'Advantage+ CURL',
		        CURLOPT_HTTP_VERSION    => CURL_HTTP_VERSION_1_1
		);
		// Set the appropriate content-type.
		curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: text/xml; charset=utf-8'));
		curl_setopt_array($ch, $opts);
		$response = curl_exec($ch);
		$info = curl_getinfo( $ch );

		if($info['http_code']=='200'){
			$p = xml_parser_create();
			xml_parse_into_struct($p, $response, $vals, $index);
			xml_parser_free($p);
			$n = explode(',',$vals[$index['T:NAME'][0]]['value']);
			return array(
					'NAME'=>array(trim($n[1]),'',trim($n[0])),
					'EMAIL'=>$vals[$index['T:EMAILADDRESS'][0]]['value'],
					'COMPANY'=>$vals[$index['T:COMPANYNAME'][0]]['value'],
					'ADDRESS'=>
					$vals[$index['T:STREET'][0]]['value']."\n".$vals[$index['T:CITY'][0]]['value'].' '.$vals[$index['T:STATE'][0]]['value'].
					' '.$vals[$index['T:COUNTRYORREGION'][0]]['value'].' '.$vals[$index['T:POSTALCODE'][0]]['value'],
					'ABOUT'=>$vals[$index['T:JOBTITLE'][0]]['value']."\n".$vals[$index['T:DEPARTMENT'][0]]['value'],
					'PHONE'=>$vals[$index['T:PHONENUMBERS'][0]+1]['value'],
					'LOCATION'=>$vals[$index['T:STATE'][0]]['value']
			);
		}
		return false;
	}

}