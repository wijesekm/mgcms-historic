<?php

/**
 * @file                phpmailer.class.php
 * @author              Kevin Wijesekera
 * @copyright   		2008
 * @edited              9-7-2008
 
 * Based of the PHPMailer library
 * ------------------------------
 * Copyright (c) 2004-2007, Andy Prevost. All Rights Reserved.
 * Copyright (c) 2001-2003, Brent R. Matzelle
 * License: Distributed under the Lesser General Public License (LGPL)
 * http://www.gnu.org/copyleft/lesser.html
 * ------------------------------
 
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

class phpmailer{
	
	private $cfg;
	private	$to = array();
	private $cc = array();
	private $bcc = array();
	private $replyTo = array();
	private $sender = false;
	private $subject = false;
	private $messagetype = false;
	private $body = false;
	private $altbody = false;
	private $attachment = array();
	private $boundary = array();
	private $priority = 3;
	private $confirm = '';
	private $customheaders = array();
	private $signcertfile  = "";
	private $signkeyfile   = "";
	private $signkeypass   = "";
	private $contenttype	= 'text/plain';

	const	SMTP_DEBUG 			= false;
	const	SMTP_TIMEOUT		= 10;
	const	SMTP_KEEP_ALIVE		= false;
	const	SMTP_HELO			= false;
	const	LE					= "\n";
	
	public function __construct($newcfg=false){
		$uri=explode('/',$GLOBALS['MG']['SITE']['URI']);
		$uri=$uri[0];
		$this->cfg=array(
			'charset'=>$GLOBALS['MG']['SITE']['EMAIL-CHARSET'],
			'encoding'=>$GLOBALS['MG']['SITE']['EMAIL-ENCODING'],
			'defaultfrom'=>$GLOBALS['MG']['SITE']['EMAIL-DEFAULT-FROM'],
			'wordwrap'=>$GLOBALS['MG']['SITE']['EMAIL-WW'],
			'mailer'=>$GLOBALS['MG']['SITE']['EMAIL-MAILER'],
			'mailfunct_path'=>$GLOBALS['MG']['SITE']['EMAIL-MAILFUNCT-PATH'],
			'hostname'=>$uri,
			'smtp_host'=>'localhost',
			'smtp_port'=>25,
			'smtp_secure'=>'tls',
			'smtp_username'=>'',
			'smtp_password'=>'',
			'seperate_to'=>(boolean)$GLOBALS['MG']['SITE']['EMAIL-SEP-TO']
		);
		if($newcfg){
			$this->cfg=array_merge($this->cfg,$newcfg);	
		}
	}

	public function phpm_send(){

		if((count($this->to) + count($this->cc) + count($this->bcc)) < 1) {
			trigger_error('(PHPMAILER): Cannot send message with no recipiants!',E_USER_WARNING);
			return false;
		}
		
		$this->phpm_setMessageType();
		$header=$this->phpm_createHeader();
		$body=$this->phpm_createBody();

		if(!$body){
			return false;
		}
		
		switch($this->cfg['mailer']){
			case 'mailfunct':
				return $this->phpm_mailfunctSend($header,$body);
			break;
			case 'smtp':
				//not yet supported
			break;
			case 'mail':
			default;
				return $this->phpm_mailSend($header,$body);
			break;
		}
		return false;
	}

	public function phpm_addSubject($subject){
		$this->subject = $subject;
	}
	
	public function phpm_addBody($body,$html=false,$alt=false){
		if($html){
			$this->body=$this->phpm_msgHTML($body);
			$this->contenttype='text/html';
			if($alt){
				$this->altbody=trim(strip_tags(preg_replace('/<(head|title|style|script)[^>]*>.*?<\/\\1>/s','',$this->body)));
			}
		}
		else{
			$this->body=$body;
		}
	}

	public function phpm_addAddress($name,$address,$type='to'){
		switch($type){
			case 'replyto':
				$this->replyTo[]=array(trim($address),trim($name));
			break;
			case 'bcc':
				$this->bcc[]=array(trim($address),trim($name));
			break;
			case 'cc':
				$this->cc[]=array(trim($address),trim($name));
			break;
			case 'to':
			default:
				$this->to[]=array(trim($address),trim($name));
			break;
		}
	}

	public function phpm_addSender($name,$email){
		$this->sender=array(array(trim($email),trim($name)));
	}

	public function phpm_setPriority($p){
		$this->priority = $p;
	}

	public function phpm_setConformAddr($addr){
		$this->confirm=$addr;
	}

	public function phpm_addCustomHeader($custom_header) {
		$this->customheaders[] = explode(':', $custom_header, 2);
	}

	public function phpm_setContenttype($ctype){
		$this->contenttype=$ctype;
	}

	public function phpm_addAttachment($path, $name = '', $encoding = 'base64', $type = 'application/octet-stream') {
		if(!@is_file($path)) {
			return false;
		}

		$filename = basename($path);
		if($name == '') {
			$name = $filename;
		}

		$this->attachment[][0] = $path;
		$this->attachment[][1] = $filename;
		$this->attachment[][2] = $name;
		$this->attachment[][3] = $encoding;
		$this->attachment[][4] = $type;
		$this->attachment[][5] = false; // isStringAttachment
		$this->attachment[][6] = 'attachment';
		$this->attachment[][7] = 0;
		return true;
	}
	
	public function phpm_addStringAttachment($string, $filename, $encoding = 'base64', $type = 'application/octet-stream') {
		/* Append to $attachment array */
		$this->attachment[][0] = $string;
		$this->attachment[][1] = $filename;
		$this->attachment[][2] = $filename;
		$this->attachment[][3] = $encoding;
		$this->attachment[][4] = $type;
		$this->attachment[][5] = true; // isString
		$this->attachment[][6] = 'attachment';
		$this->attachment[][7] = 0;
	}
	
	public function phpm_addEmbeddedImage($path, $cid, $name = '', $encoding = 'base64', $type = 'application/octet-stream') {

		if(!@is_file($path)) {
			return false;
		}

		$filename = basename($path);
		if($name == '') {
			$name = $filename;
		}

		/* Append to $attachment array */
		$this->attachment[][0] = $path;
		$this->attachment[][1] = $filename;
		$this->attachment[][2] = $name;
		$this->attachment[][3] = $encoding;
		$this->attachment[][4] = $type;
		$this->attachment[][5] = false; // isStringAttachment
		$this->attachment[][6] = 'inline';
		$this->attachment[][7] = $cid;
		return true;
	}
	
	public function phpm_signMsg($cert_filename, $key_filename, $key_pass) {
		$this->signcertfile = $cert_filename;
		$this->signkeyfile = $key_filename;
		$this->signkeypass = $key_pass;
	}
	
	/**
	*
	* Private message format functions
	*
	*/
	private function phpm_createHeader() {
		$result = '';
		/* Set the boundaries */
		$uniq_id = md5(uniqid(time()));
		$this->boundary[1] = 'b1_' . $uniq_id;
		$this->boundary[2] = 'b2_' . $uniq_id;

		$result .= $this->phpm_headerLine('Date', $this->phpm_RFCDate());
    	if($this->sender[0][0] == '') {
      		$result .= $this->phpm_headerLine('Return-Path', trim($this->cfg['defaultfrom']));
		} 
		else {
			$result .= $this->phpm_headerLine('Return-Path', trim($this->sender[0][0]));
 		}

		/* To be created automatically by mail() */
		if($this->cfg['mailer'] != 'mail') {
			if(count($this->to) > 0) {
				$result .= $this->phpm_addrAppend('To', $this->to);
			} 
			elseif (count($this->cc) == 0) {
				$result .= $this->phpm_headerLine('To', 'undisclosed-recipients:;');
			}
			if(count($this->cc) > 0) {
				$result .= $this->phpm_addrAppend('Cc', $this->cc);
			}
		}

		$result .= $this->phpm_addrAppend('From', $this->sender);
		
 		/* sendmail and mail() extract Cc from the header before sending */
		if((($this->cfg['mailer'] == 'mailfunct') || ($this->cfg['mailer'] == 'mail')) && (count($this->cc) > 0)) {
			$result .= $this->phpm_addrAppend('Cc', $this->cc);
		}

		/* sendmail and mail() extract Bcc from the header before sending */
		if((($this->cfg['mailer'] == 'mailfunct') || ($this->cfg['mailer'] == 'mail')) && (count($this->bcc) > 0)) {
			$result .= $this->phpm_addrAppend('Bcc', $this->bcc);
		}

		if(count($this->replyTo) > 0) {
      		$result .= $this->phpm_addrAppend('Reply-to', $this->replyTo);
		}

		/* mail() sets the subject itself */
		if($this->cfg['mailer'] != 'mail') {
			$result .= $this->phpm_headerLine('Subject', $this->phpm_encodeHeader($this->phpm_secureHeader($this->subject)));
		}

		$result .= sprintf("Message-ID: <%s@%s>%s", $uniq_id, $this->cfg['hostname'], phpmailer::LE);

		$result .= $this->phpm_headerLine('X-Priority', $this->priority);
		$result .= $this->phpm_headerLine('X-Mailer', 'PHPMailer (phpmailer.codeworxtech.com)');

		if($this->confirm != '') {
			$result .= $this->phpm_headerLine('Disposition-Notification-To', '<' . trim($this->confirm) . '>');
		}

		// Add custom headers
		$sohh = count($this->customheaders);
		for($index = 0; $index < $sohh; $index++) {
			$result .= $this->phpm_headerLine(trim($this->customheaders[$index][0]), $this->phpm_encodeHeader(trim($this->customheaders[$index][1])));
		}
		if (!$this->signkeyfile) {
			$result .= $this->phpm_headerLine('MIME-Version', '1.0');
			$result .= $this->phpm_getMailMIME();
		}
		return $result;
	}
	public function phpm_createBody() {
		$result = '';

		if ($this->signkeyfile) {
			$result .= $this->phpm_getMailMIME();
		}

		$this->phpm_setWordWrap();

		switch($this->messagetype) {
			case 'alt':
				$result .= $this->phpm_getBoundry($this->boundary[1], '', 'text/plain', '');
				$result .= $this->phpm_encodeString($this->altbody, $this->cfg['encoding']);
				$result .= phpmailer::LE.phpmailer::LE;
				$result .= $this->phpm_getBoundry($this->boundary[1], '', 'text/html', '');
				$result .= $this->phpm_encodeString($this->body, $this->cfg['encoding']);
				$result .= phpmailer::LE.phpmailer::LE;
				$result .= $this->phpm_endBoundry($this->boundary[1]);
			break;
			case 'plain':
				$result .= $this->phpm_encodeString($this->Body, $this->cfg['encoding']);
			break;
			case 'attachments':
				$result .= $this->phpm_getBoundry($this->boundary[1], '', '', '');
				$result .= $this->phpm_encodeString($this->Body, $this->cfg['encoding']);
				$result .= phpmailer::LE;
				$result .= $this->phpm_attachAll();
			break;
			case 'alt_attachments':
				$result .= sprintf("--%s%s", $this->boundary[1], phpmailer::LE);
				$result .= sprintf("Content-Type: %s;%s" . "\tboundary=\"%s\"%s", 'multipart/alternative', phpmailer::LE, $this->boundary[2], phpmailer::LE.phpmailer::LE);
				$result .= $this->phpm_getBoundry($this->boundary[2], '', 'text/plain', '') . phpmailer::LE; // Create text body
				$result .= $this->phpm_encodeString($this->altbody, $this->cfg['encoding']);
				$result .= phpmailer::LE.phpmailer::LE;
				$result .= $this->phpm_getBoundry($this->boundary[2], '', 'text/html', '') . phpmailer::LE; // Create the HTML body
				$result .= $this->phpm_encodeString($this->Body, $this->cfg['encoding']);
				$result .= phpmailer::LE.phpmailer::LE;
				$result .= $this->phpm_endBoundry($this->boundary[2]);
				$result .= $this->phpm_attachAll();
			break;
		};

		if ($this->signkeyfile) {
			$file = tempnam("", "mail");
			$fp = fopen($file, "w");
			fwrite($fp, $result);
			fclose($fp);
			$signed = tempnam("", "signed");

			if (@openssl_pkcs7_sign($file, $signed, "file://".$this->signcertfile, array("file://".$this->signkeyfile, $this->signkeypass), null)) {
				$fp = fopen($signed, "r");
				$result = '';
				while(!feof($fp)){
					$result = $result . fread($fp, 1024);
				}
				fclose($fp);
			} 
			else {

		      $result= false;   
			}
			unlink($file);
			unlink($signed);
    	}
		return $result;
	}

	public function phpm_getMailMIME() {
		$result = '';
		switch($this->messagetype) {
			case 'plain':
 				$result .= $this->phpm_headerLine('Content-Transfer-Encoding', $this->cfg['encoding']);
			$result .= sprintf("Content-Type: %s; charset=\"%s\"", $this->contenttype, $this->cfg['charset']);
	        break;
			case 'attachments':
			case 'alt_attachments':
				if($this->phpm_inlineImageExists()){
					$result .= sprintf("Content-Type: %s;%s\ttype=\"text/html\";%s\tboundary=\"%s\"%s", 'multipart/related', phpmailer::LE, phpmailer::LE, $this->boundary[1], phpmailer::LE);
				} 
				else {
					$result .= $this->phpm_headerLine('Content-Type', 'multipart/mixed;');
					$result .= $this->phpm_textLine("\tboundary=\"" . $this->boundary[1] . '"');
				}
	        break;
			case 'alt':
				$result .= $this->phpm_headerLine('Content-Type', 'multipart/alternative;');
				$result .= $this->phpm_textLine("\tboundary=\"" . $this->boundary[1] . '"');
			break;
		}
		if($this->cfg['mailer'] != 'mail') {
			$result .= phpmailer::LE.phpmailer::LE;
		}
		return $result;
	}
	
	private function phpm_addrAppend($type, $addr) {
		$addr_str = $type . ': ';
		$addr_str .= $this->phpm_addressFormat($addr[0]);
		if(count($addr) > 1) {
			$soaa=count($addr);
			for($i = 1; $i < count($soaa); $i++) {
  				$addr_str .= ', ' . $this->phpm_addressFormat($addr[$i]);
			}
		}
		$addr_str .= phpmailer::LE;
 		return $addr_str;
	}
	
	private function phpm_SetMessageType() {
		if(count($this->attachment) < 1 && strlen($this->altbody) < 1) {
			$this->messagetype = 'plain';
		} 
		else {
			if(count($this->attachment) > 0) {
				$this->messagetype = 'attachments';
			}
			if(strlen($this->altbody) > 0 && count($this->attachment) < 1) {
				$this->messagetype = 'alt';
			}
			if(strlen($this->altbody) > 0 && count($this->attachment) > 0) {
				$this->messagetype = 'alt_attachments';
			}
		}
	}
	
	private function phpm_setWordWrap() {
		if($this->cfg['wordwrap'] < 1) {
			return false;
		}
		switch($this->messagetype) {
			case 'alt':
			case 'alt_attachments':
				$this->altbody = $this->phpm_wrapText($this->altbody, $this->cfg['wordwrap']);
			break;
			default:
				$this->body = $this->phpm_wrapText($this->body, $this->cfg['wordwrap']);
			break;
		}
	}
  	private function phpm_addressFormat($addr) {
    	if(empty($addr[1])) {
      		$formatted = $this->phpm_secureHeader($addr[0]);
    	} 
		else {
      		$formatted = $this->phpm_encodeHeader($this->phpm_secureHeader($addr[1]), 'phrase') . " <" . $this->phpm_secureHeader($addr[0]) . ">";
    	}
		return $formatted;
	}

	private function phpm_headerLine($name, $value) {
		return $name . ': ' . $value . phpmailer::LE;
	}

	public function phpm_textLine($value) {
		return $value . phpmailer::LE;
	}

	private static function phpm_RFCDate() {
		$tz = date('Z');
		$tzs = ($tz < 0) ? '-' : '+';
		$tz = abs($tz);
		$tz = (int)($tz/3600)*100 + ($tz%3600)/60;
		$result = sprintf("%s %s%04d", date('D, j M Y H:i:s'), $tzs, $tz);
		return $result;
	}

	public function phpm_inlineImageExists() {
		$result = false;
		$soa=count($this->attachment);
		for($i = 0; $i < $soa; $i++) {
			if($this->attachment[$i][6] == 'inline') {
				$result = true;
				break;
			}
		}
    	return $result;
	}

	public function phpm_getBoundry($boundary, $charSet, $contentType, $encoding) {
		$result = '';
		if($charSet == '') {
			$charSet = $this->cfg['charset'];
		}
		if($contentType == '') {
			$contentType = $this->contenttype;
		}
		if($encoding == '') {
			$encoding = $this->cfg['encoding'];
		}
		$result .= $this->phpm_textLine('--' . $boundary);
		$result .= sprintf("Content-Type: %s; charset = \"%s\"", $contentType, $charSet);
		$result .= phpmailer::LE;
		$result .= $this->phpm_headerline('Content-Transfer-Encoding', $encoding);
		$result .= phpmailer::LE;
		return $result;
	}

	public function phpm_endBoundry($boundary) {
		return phpmailer::LE . '--' . $boundary . '--' . phpmailer::LE;
	}
	
	public function phpm_attachAll(){
		/* Return text of body */
		$mime = array();

		/* Add all attachments */
		$soa=count($this->attachment);
		for($i = 0; $i < $soa; $i++) {
			/* Check for string attachment */
			$bString = $this->attachment[$i][5];
			if ($bString) {
				$string = $this->attachment[$i][0];
			}
			else {
				$path = $this->attachment[$i][0];
			}

			$filename    = $this->attachment[$i][1];
			$name        = $this->attachment[$i][2];
			$encoding    = $this->attachment[$i][3];
			$type        = $this->attachment[$i][4];
			$disposition = $this->attachment[$i][6];
			$cid         = $this->attachment[$i][7];

			$mime[] = sprintf("--%s%s", $this->boundary[1], phpmailer::LE);
			$mime[] = sprintf("Content-Type: %s; name=\"%s\"%s", $type, $this->phpm_encodeHeader($this->phpm_secureHeader($name)), phpmailer::LE);
			$mime[] = sprintf("Content-Transfer-Encoding: %s%s", $encoding, phpmailer::LE);

			if($disposition == 'inline') {
				$mime[] = sprintf("Content-ID: <%s>%s", $cid, phpmailer::LE);
			}
			
			$mime[] = sprintf("Content-Disposition: %s; filename=\"%s\"%s", $disposition, $this->phpm_encodeHeader($this->phpm_secureHeader($name)), phpmailer::LE.phpmailer::LE);

			/* Encode as string attachment */
			if($bString) {
				$mime[] = $this->phpm_encodeString($string, $encoding).phpmailer::LE.phpmailer::LE;
			}
			else {
				$mime[] = $this->phpm_encodeFile($path, $encoding).phpmailer::LE.phpmailer::LE;
			}
		}
		$mime[] = sprintf("--%s--%s", $this->boundary[1], phpmailer::LE);
		return join('', $mime);
	}
	
	/**
	*
	* Private Send Functions
	*
	*/
	private function phpm_mailfunctSend($header,$body){
		if($this->sender[0][0]){
			$sendmail = sprintf("%s -oi -f %s -t", escapeshellcmd($this->cfg['mailfunct_path']),escapeshellarg($this->sender[0][0]));
		}
		else{
			$sendmail = sprintf("%s -oi -t", escapeshellcmd($this->cfg['mailfunct_path']));
		}
		
		if(!$smproc = popen($sendmail,'w')){
			trigger_error('(PHPMAILER): Could not open mailfunct process: '.$sendmail,E_USER_WARNING);
		}
		fputs($smproc,$header);
		fputs($smproc,$body);
		
		$result = pclose($mail);
	
		if($result != 0){
			trigger_error('(PHPMAILER): Mailfunct process error: '.$result,E_USER-WARNING);
			return false;
		}
		return true;
		
	}
	private function phpm_mailSend($header, $body){
		$toArr = array();
		$sot=count($this->to);
		for($i=0;$i<$sot;$i++){
			if($this->to[$i][0]){
				$toArr[] = $this->phpm_addressFormat($this->to[$i]);
			}
		}
		$params = sprintf("-oi -f %s", $this->sender[0][0]);
		if($this->sender != '' && strlen(ini_get('safe_mode')) < 1){
			$old_from = ini_get('sendmail_from');
			ini_set('sendmail_from',$this->sender[0][0]);
		}
		if($this->cfg['seperate_to'] && count($toArr) > 1){
			foreach ($toArr as $key => $val) {
				$rt = @mail($val, $this->phpm_encodeHeader($this->phpm_secureHeader($this->subject)), $body, $header, $params);
			}
		}
		else{
			
			$rt = @mail(implode(', ',$toArr), $this->phpm_encodeHeader($this->phpm_secureHeader($this->subject)), $body, $header, $params);
		}
		if (isset($old_from)) {
			ini_set('sendmail_from', $old_from);
		}
		
		if(!$rt){
			trigger_error('(PHPMAILER): Could not send using mail function due to internal error!',E_USER_WARNING);
			return false;
		}
		return true;	
	}
	
	/**
	*
	* Private Format/Encoding Functions
	*
	*/
	
	private function phpm_encodeFile ($path, $encoding = 'base64') {
		if(!@$fd = fopen($path, 'rb')) {
			trigger_error('(PHPMAILER): Could not open file for attaching: '.$path,E_USER_WARNING);
			return false;
		}
		if (function_exists('get_magic_quotes')) {
			function get_magic_quotes() {
				return false;
			}
		}
		if (PHP_VERSION < 6) {
			$magic_quotes = get_magic_quotes_runtime();
			set_magic_quotes_runtime(0);
		}
		$file_buffer  = file_get_contents($path);
		$file_buffer  = $this->EncodeString($file_buffer, $encoding);
		fclose($fd);
		if (PHP_VERSION < 6) { 
			set_magic_quotes_runtime($magic_quotes); 
		}
		return $file_buffer;
	}
	
	private function phpm_encodeString($str, $encoding = 'base64') {
		$encoded = '';
		switch(strtolower($encoding)) {
			case 'base64':
				$encoded = chunk_split(base64_encode($str), 76, phpmailer::LE);
			break;

        	break;
      		case 'binary':
        		$encoded = $str;
			break;
			case 'quoted-printable':
				$encoded = $this->phpm_encodeQP($str);
			break;
			default:
			case '7bit':
			case '8bit':
				$encoded = $this->phpm_fixEOL($str);
				if (substr($encoded, -(strlen(phpmailer::LE))) != phpmailer::LE){
					$encoded .= phpmailer::LE;
				}
			break;
    	}
    	return $encoded;
	}
	
	private function phpm_encodeHeader($str, $position = 'text'){
		$x = 0;
		switch($position){
			case 'phrase':
				if(!preg_match('/[\200-\377]/', $str)){
					$encoded = addcslashes($str, "\0..\37\177\\\"");
					if(($str == $encoded) && !preg_match('/[^A-Za-z0-9!#$%&\'*+\/=?^_`{|}~ -]/', $str)){
						return $encoded;
					}
					else{
						return "\"$encoded\"";
					}
				}
				$x = preg_match_all('/[^\040\041\043-\133\135-\176]/', $str, $matches);
			break;
			case 'comment':
				$x = preg_match_all('/[()"]/', $str, $matches);
			break;
			case 'text':
			default:
				$x += preg_match_all('/[\000-\010\013\014\016-\037\177-\377]/', $str, $matches);
			break;
		};
		
		if($x == 0){
			return $str;
		}
		
		$maxlen = 75 - 7 - strlen($this->cfg['charset']);
		if(strlen($str)/3 < $x){
			$encoding = 'B';
			if($this->phpm_hasMB($str)){
				$encoded = $this->phpm_base64EncodeWrapMB($str);
			}
			else{
				$encoded = base64_encode($str);
				$maxlen -= $maxlen % 4;
				$encoded = trim(chunk_split($encoded, $maxlen, "\n"));
			}
		}
		else{
			$encoding = 'Q';
			$encoded = $this->phpm_encodeQ($str,$position);
			$encoded = $this->phpm_wrapText($encoded, $maxlen, true);
			$encoded = str_replace('='.phpmailer::LE, "\n", trim($encoded));
		}
		$encoded = preg_replace('/^(.*)$/m', " =?".$this->cfg['charset']."?$encoding?\\1?=", $encoded);
		$encoded = trim(str_replace("\n", phpmailer::LE, $encoded));
		
		return $encoded;
	}
  
	private function phpm_hasMB($str) {
		if (function_exists('mb_strlen')) {
			return (strlen($str) > mb_strlen($str, $this->cfg['charset']));
		} 
		else {
			return False;
		}
	}
	
  	private function phpm_base64EncodeWrapMB($str){
		$start = "=?".$this->cfg['charset']."?B?";
		$end = "?=";
		$encoded = "";

		$mb_length = mb_strlen($str, $this->cfg['charset']);
		// Each line must have length <= 75, including $start and $end
		$length = 75 - strlen($start) - strlen($end);
		// Average multi-byte ratio
		$ratio = $mb_length / strlen($str);
		// Base64 has a 4:3 ratio
		$offset = $avgLength = floor($length * $ratio * .75);

		for ($i = 0; $i < $mb_length; $i += $offset) {
			$lookBack = 0;
			do {
				$offset = $avgLength - $lookBack;
				$chunk = mb_substr($str, $i, $offset, $this->cfg['charset']);
				$chunk = base64_encode($chunk);
				$lookBack++;
			}
			while (strlen($chunk) > $length);
			$encoded .= $chunk . phpmailer::LE;
    	}
    	// Chomp the last linefeed
    	$encoded = substr($encoded, 0, -strlen(phpmailer::LE));
		return $encoded;
	}
	
	private function phpm_encodeQP( $input = '', $line_max = 76, $space_conv = false ) {
		$hex = array('0','1','2','3','4','5','6','7','8','9','A','B','C','D','E','F');
		$lines = preg_split('/(?:\r\n|\r|\n)/', $input);
		$eol = "\r\n";
		$escape = '=';
		$output = '';
		while( list(, $line) = each($lines) ) {
			$linlen = strlen($line);
			$newline = '';
			for($i = 0; $i < $linlen; $i++) {
				$c = substr( $line, $i, 1 );
				$dec = ord( $c );
				if ( ( $i == 0 ) && ( $dec == 46 ) ) { // convert first point in the line into =2E
					$c = '=2E';
				}
				if ( $dec == 32 ) {
					if ( $i == ( $linlen - 1 ) ) { // convert space at eol only
						$c = '=20';
					}
					else if ( $space_conv ) {
						$c = '=20';
					}
				}
				else if ( ($dec == 61) || ($dec < 32 ) || ($dec > 126) ) { // always encode "\t", which is *not* required
					$h2 = floor($dec/16);
					$h1 = floor($dec%16);
					$c = $escape.$hex[$h2].$hex[$h1];
				}
				if ( (strlen($newline) + strlen($c)) >= $line_max ) { // CRLF is not counted
					$output .= $newline.$escape.$eol; //  soft line break; " =\r\n" is okay
					$newline = '';
					// check if newline first character will be point or not
					if ( $dec == 46 ) {
						$c = '=2E';
					}
				}
				$newline .= $c;
			}
			$output .= $newline.$eol;
		}
		return trim($output);
	}

	private function phpm_encodeQ($str, $position = 'text'){
	    /* There should not be any EOL in the string */
	    $encoded = preg_replace("[\r\n]", '', $str);
		switch (strtolower($position)) {
			case 'phrase':
				$encoded = preg_replace("/([^A-Za-z0-9!*+\/ -])/e", "'='.sprintf('%02X', ord('\\1'))", $encoded);
			break;
			case 'comment':
				$encoded = preg_replace("/([\(\)\"])/e", "'='.sprintf('%02X', ord('\\1'))", $encoded);
			case 'text':
			default:
				/* Replace every high ascii, control =, ? and _ characters */
				$encoded = preg_replace('/([\000-\011\013\014\016-\037\075\077\137\177-\377])/e',"'='.sprintf('%02X', ord('\\1'))", $encoded);
			break;
		};
		/* Replace every spaces to _ (more readable than =20) */
		$encoded = str_replace(' ', '_', $encoded);	
		return $encoded;
	}
	
	private function phpm_wrapText($message, $length, $qp_mode = false) {
		$soft_break = ($qp_mode) ? sprintf(" =%s", phpmailer::LE) : phpmailer::LE;
		// If utf-8 encoding is used, we will need to make sure we don't
		// split multibyte characters when we wrap
		$is_utf8 = (strtolower($this->cfg['charset']) == "utf-8");

		$message = $this->phpm_fixEOL($message);
		if (substr($message, -1) == phpmailer::LE) {
			$message = substr($message, 0, -1);
		}

		$line = explode(phpmailer::LE, $message);
		$message = '';
		$soq=count($line);
		for ($i=0 ;$i < $soq; $i++) {
			$line_part = explode(' ', $line[$i]);
			$buf = '';
			$soq2=count($line_part);
			for ($e = 0; $e<$soq2; $e++) {
  				$word = $line_part[$e];
				if ($qp_mode and (strlen($word) > $length)) {
					$space_left = $length - strlen($buf) - 1;
					if ($e != 0) {
						if ($space_left > 20) {
							$len = $space_left;
							if ($is_utf8) {
								$len = $this->phpm_UTF8CharBoundary($word, $len);
							} 
							else if (substr($word, $len - 1, 1) == "=") {
								$len--;
							} 
							else if (substr($word, $len - 2, 1) == "=") {
								$len -= 2;
							}
							$part = substr($word, 0, $len);
							$word = substr($word, $len);
							$buf .= ' ' . $part;
							$message .= $buf . sprintf("=%s", $this->LE);
						} 
						else {
							$message .= $buf . $soft_break;
						}
						$buf = '';
					}
					while (strlen($word) > 0) {
						$len = $length;
						if ($is_utf8) {
							$len = $this->phpm_UTF8CharBoundary($word, $len);
						} 
						else if (substr($word, $len - 1, 1) == "=") {
							$len--;
						} 
						else if (substr($word, $len - 2, 1) == "=") {
							$len -= 2;
						}
						$part = substr($word, 0, $len);
						$word = substr($word, $len);

						if (strlen($word) > 0) {
							$message .= $part . sprintf("=%s", $this->LE);
						} 
						else {
							$buf = $part;
						}
					}
				} 
				else {
					$buf_o = $buf;
					$buf .= ($e == 0) ? $word : (' ' . $word);

					if (strlen($buf) > $length and $buf_o != '') {
 						$message .= $buf_o . $soft_break;
						$buf = $word;
					}
				}
			}
			$message .= $buf . $this->LE;
		}
		return $message;
	}
	
	private function phpm_UTF8CharBoundary($encodedText, $maxLength) {
		$foundSplitPos = false;
		$lookBack = 3;
		while (!$foundSplitPos) {
			$lastChunk = substr($encodedText, $maxLength - $lookBack, $lookBack);
			$encodedCharPos = strpos($lastChunk, "=");
			if ($encodedCharPos !== false) {
				// Found start of encoded character byte within $lookBack block.
				// Check the encoded byte value (the 2 chars after the '=')
				$hex = substr($encodedText, $maxLength - $lookBack + $encodedCharPos + 1, 2);
				$dec = hexdec($hex);
				if ($dec < 128) { // Single byte character.
					// If the encoded char was found at pos 0, it will fit
 					// otherwise reduce maxLength to start of the encoded char
					$maxLength = ($encodedCharPos == 0) ? $maxLength :
					$maxLength - ($lookBack - $encodedCharPos);
					$foundSplitPos = true;
				}
				else if ($dec >= 192) { // First byte of a multi byte character
					// Reduce maxLength to split at start of character
					$maxLength = $maxLength - ($lookBack - $encodedCharPos);
					$foundSplitPos = true;
				} 
				elseif ($dec < 192) { // Middle byte of a multi byte character, look further back
					$lookBack += 3;
				}
			} 
			else {
				// No encoded character found
				$foundSplitPos = true;
			}
		}
		return $maxLength;
	}
	
	private function phpm_secureHeader($str) {
		$str = trim($str);
		$str = str_replace("\r", "", $str);
		$str = str_replace("\n", "", $str);
		return $str;
	}
	
	private function phpm_fixEOL($str) {
		$str = str_replace("\r\n", "\n", $str);
		$str = str_replace("\r", "\n", $str);
		$str = str_replace("\n", phpmailer::LE, $str);
		return $str;
	}
	private function phpm_msgHTML($message,$basedir='') {
		preg_match_all("/(src|background)=\"(.*)\"/Ui", $message, $images);
		if(isset($images[2])) {
			foreach($images[2] as $i => $url) {
				// do not change urls for absolute images (thanks to corvuscorax)
				if (!preg_match('/^[A-z][A-z]*:\/\//',$url)) {
					$filename = basename($url);
					$directory = dirname($url);
					($directory == '.')?$directory='':'';
					$cid = 'cid:' . md5($filename);
					$fileParts = split("\.", $filename);
					$ext = $fileParts[1];
					$mimeType = $this->_mime_types($ext);
					if ( strlen($basedir) > 1 && substr($basedir,-1) != '/') { 
						$basedir .= '/'; 
					}
					if ( strlen($directory) > 1 && substr($basedir,-1) != '/') { 
						$directory .= '/'; 
					}
					$this->phpm_addEmbeddedImage($basedir.$directory.$filename, md5($filename), $filename, 'base64', $mimeType);
					if ( $this->phpm_addEmbeddedImage($basedir.$directory.$filename, md5($filename), $filename, 'base64',$mimeType) ) {
						$message = preg_replace("/".$images[1][$i]."=\"".preg_quote($url, '/')."\"/Ui", $images[1][$i]."=\"".$cid."\"", $message);
					}
				}
			}
		}
		return $message;
  }
	public function _mime_types($ext = '') {
		$mimes = array(
			'hqx'   =>  'application/mac-binhex40',
			'cpt'   =>  'application/mac-compactpro',
			'doc'   =>  'application/msword',
			'bin'   =>  'application/macbinary',
			'dms'   =>  'application/octet-stream',
			'lha'   =>  'application/octet-stream',
			'lzh'   =>  'application/octet-stream',
			'exe'   =>  'application/octet-stream',
			'class' =>  'application/octet-stream',
			'psd'   =>  'application/octet-stream',
			'so'    =>  'application/octet-stream',
			'sea'   =>  'application/octet-stream',
			'dll'   =>  'application/octet-stream',
			'oda'   =>  'application/oda',
			'pdf'   =>  'application/pdf',
			'ai'    =>  'application/postscript',
			'eps'   =>  'application/postscript',
			'ps'    =>  'application/postscript',
			'smi'   =>  'application/smil',
			'smil'  =>  'application/smil',
			'mif'   =>  'application/vnd.mif',
			'xls'   =>  'application/vnd.ms-excel',
			'ppt'   =>  'application/vnd.ms-powerpoint',
			'wbxml' =>  'application/vnd.wap.wbxml',
			'wmlc'  =>  'application/vnd.wap.wmlc',
			'dcr'   =>  'application/x-director',
			'dir'   =>  'application/x-director',
			'dxr'   =>  'application/x-director',
			'dvi'   =>  'application/x-dvi',
			'gtar'  =>  'application/x-gtar',
			'php'   =>  'application/x-httpd-php',
			'php4'  =>  'application/x-httpd-php',
			'php3'  =>  'application/x-httpd-php',
			'phtml' =>  'application/x-httpd-php',
			'phps'  =>  'application/x-httpd-php-source',
			'js'    =>  'application/x-javascript',
			'swf'   =>  'application/x-shockwave-flash',
			'sit'   =>  'application/x-stuffit',
			'tar'   =>  'application/x-tar',
			'tgz'   =>  'application/x-tar',
			'xhtml' =>  'application/xhtml+xml',
			'xht'   =>  'application/xhtml+xml',
			'zip'   =>  'application/zip',
			'mid'   =>  'audio/midi',
			'midi'  =>  'audio/midi',
			'mpga'  =>  'audio/mpeg',
			'mp2'   =>  'audio/mpeg',
			'mp3'   =>  'audio/mpeg',
			'aif'   =>  'audio/x-aiff',
			'aiff'  =>  'audio/x-aiff',
			'aifc'  =>  'audio/x-aiff',
			'ram'   =>  'audio/x-pn-realaudio',
			'rm'    =>  'audio/x-pn-realaudio',
			'rpm'   =>  'audio/x-pn-realaudio-plugin',
			'ra'    =>  'audio/x-realaudio',
			'rv'    =>  'video/vnd.rn-realvideo',
			'wav'   =>  'audio/x-wav',
			'bmp'   =>  'image/bmp',
			'gif'   =>  'image/gif',
			'jpeg'  =>  'image/jpeg',
			'jpg'   =>  'image/jpeg',
			'jpe'   =>  'image/jpeg',
			'png'   =>  'image/png',
			'tiff'  =>  'image/tiff',
			'tif'   =>  'image/tiff',
			'css'   =>  'text/css',
			'html'  =>  'text/html',
			'htm'   =>  'text/html',
			'shtml' =>  'text/html',
			'txt'   =>  'text/plain',
			'text'  =>  'text/plain',
			'log'   =>  'text/plain',
			'rtx'   =>  'text/richtext',
			'rtf'   =>  'text/rtf',
			'xml'   =>  'text/xml',
			'xsl'   =>  'text/xml',
			'mpeg'  =>  'video/mpeg',
			'mpg'   =>  'video/mpeg',
			'mpe'   =>  'video/mpeg',
			'qt'    =>  'video/quicktime',
			'mov'   =>  'video/quicktime',
			'avi'   =>  'video/x-msvideo',
			'movie' =>  'video/x-sgi-movie',
			'doc'   =>  'application/msword',
			'word'  =>  'application/msword',
			'xl'    =>  'application/excel',
			'eml'   =>  'message/rfc822'
		);
		return ( ! isset($mimes[strtolower($ext)])) ? 'application/octet-stream' : $mimes[strtolower($ext)];
	}
}