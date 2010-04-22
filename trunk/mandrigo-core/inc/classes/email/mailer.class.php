<?php

/**
 * @file                mailer.class.php
 * @author              Kevin Wijesekera
 * @copyright   		2008
 * @edited              2-23-2010
 
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
	
	private $mail;
	private $mcfg;

	public function __construct($cfg=false){
		$this->mail = new phpmailer();
		$this->$mcfg=$cfg;
		$keys=array_keys($cfg);
		switch($keys){
			case 'priority':
				$this->mail->Priority=(int)$cfg['priority'];
			break;
			case 'charset':
				$this->mail->CharSet=$cfg['charset'];
			break;
			case 'contenttype ':
				$this->mail->ContentType =$cfg['contenttype'];
			break;
			case 'encoding':
				$this->mail->Encoding=$cfg['encoding'];
			break;
			case 'charset':
				$this->mail->CharSet=$cfg['charset'];
			break;
			case 'mailer':
				$this->mail->Mailer=$cfg['mailer'];
			break;
			case 'sendmailpath':
				$this->mail->Sendmail=$cfg['sendmailpath'];
			break;
			case 'host':
			default:
				if(!$cfg['host']){
					$cfg['host']=explode('/',$GLOBALS['MG']['SITE']['URI']);
					$cfg['host']=$cfg['host'][0];
				}
				$this->mail->Hostname=$cfg['host'];
			break;
		 	case 'smpthost':
		 		$this->mail->Host=$cfg['smtphost'];
		 	break;
		 	case 'smptport':
		 		$this->mail->Port=$cfg['smtpport'];
		 	break;
		 	case 'smpthelo':
		 		$this->mail->Helo=$cfg['smtphelo'];
		 	break;
		 	case 'smptsecure':
		 		$this->mail->SMTPSecure=$cfg['smtpsecure'];
		 	break;
		 	case 'smptauth':
		 		$this->mail->SMTPAuth=true;
		 		$this->mail->Username=$cfg['smtpauth'][0];
		 		$this->mail->Password=$cfg['smtpauth'][1];
		 	break;
			case 'singleto':
				$this->mail->SingleTo=$cfg['singleto'];
			break;	
		}
	}
	
	public function phpm_setFrom($name,$email,$confirmMsg=false){
		if(!$email){
			return false;
		}
		if(!$name){
			$name=$email;
		}
		$this->From=$email;
		$this->FromName=$name;
		$this->Sender=$email;
		$this->ConfirmReadingTo=$confirmMsg;
		return true;
	}
	
	public function phpm_setSubject($subject){
		$this->Mailer->Subject=strip_tags($subject);
		return true;
	}
	
	public function phpm_setBody($body){
		if(!$body){
			return false;
		}
		$this->Mailer->Body=$body;
		if($this->$mcfg['multipart']){
			$this->Mailer->AltBody=strip_tags($body);
		}
		return true;
	}
	
	public function phpm_addReplyTo($name,$email){
		if(!$email){
			return false;
		}
		if(!$name){
			$name=$email;
		}
		$this->mail->AddReplyTo($email,$name);
		return true;
	}

	public function phpm_addAddress($email,$name,$type='To'){
		if(!$email){
			return false;
		}
		if(!$name){
			$name=$email;
		}
		switch($type){
			case 'bcc':
				$this->mail->AddBCC($email,$name);
			break;
			case 'cc':
				$this->mail->AddCC($email,$name);
			break;
			case 'replyto':
				$this->mail->AddReplyTo($email,$name);
			break;
			case 'to':
			default:
				$this->mail->AddAddress($email,$name);
			break;

		}
	}
	
	public function phpm_send(){
		return $this->mail->Send();
	}
}