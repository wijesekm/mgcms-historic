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

class mailer{
	
	private $mail;
	private $mcfg;
	private $logDb;
	private $logmsg;

	public function __construct($cfg=false,$parse=false){
		if($parse==true){
			$cfg=$this->phpm_parseConfig($cfg);
		}
		$this->mail = new phpmailer();
		$this->mcfg=$cfg;
		$this->logDb=false;
		$this->logmsg='';
		$keys=array_keys($cfg);
		foreach($cfg as $keys=>$value){
		switch($keys){
			case 'priority':
				$this->mail->Priority=(int)$value;
			break;
			case 'charset':
				$this->mail->CharSet=$value;
			break;
			case 'contenttype ':
				$this->mail->ContentType =$value;
			break;
			case 'encoding':
				$this->mail->Encoding=$value;
			break;
			case 'mailer':
				$this->mail->Mailer=$value;
			break;
			case 'sendmailpath':
				$this->mail->Sendmail=$value;
			break;
			case 'host':
			default:
				if(!$value){
					$cfg['host']=explode('/',$GLOBALS['MG']['SITE']['URI']);
					$cfg['host']=$cfg['host'][0];
				}
				$this->mail->Hostname=$value;
			break;
		 	case 'smpthost':
		 		$this->mail->Host=$value;
		 	break;
		 	case 'smptport':
		 		$this->mail->Port=$value;
		 	break;
		 	case 'smpthelo':
		 		$this->mail->Helo=$value;
		 	break;
		 	case 'smptsecure':
		 		$this->mail->SMTPSecure=$value;
		 	break;
		 	case 'smptauth':
		 		$this->mail->SMTPAuth=true;
		 		$this->mail->Username=$value[0];
		 		$this->mail->Password=$value[1];
		 	break;
			case 'singleto':
				$this->mail->SingleTo=$value;
			break;	
			case 'logdb':
				$this->logDb=$value;
			break;
		};			
		}

	}
	
	private function phpm_parseConfig($conf){
		preg_match_all("/(.*?)\=\>(.*?)\;/",$conf,$temp);
		$conf=array();
		$soq=count($temp[1]);
		for($i=0;$i<$soq;$i++){
			if($temp[2][$i]=='true'){
				$conf[$temp[1][$i]]=true;
			}
			else if($temp[2][$i]=='false'){
				$conf[$temp[1][$i]]=false;
			}
			else{
				$conf[$temp[1][$i]]=$temp[2][$i];
			}
		}
		return $conf;
	}
	
	public function phpm_reset(){
		$this->mail->From=false;
		$this->mail->FromName=false;
		$this->mail->Sender=false;
		$this->mail->ConfirmReadingTo=false;
		$this->mail->Subject=false;
		$this->mail->AltBody=false;
		$this->mail->Body=false;
		$this->mail->to=array();
		$this->mail->bcc=array();
  		$this->mail->boundary = array();
 		$this->mail->language = array();
 		$this->mail->to = array();
  		$this->mail->cc = array();
  		$this->mail->bcc = array();
 		$this->mail->ReplyTo = array();
  		$this->mail->attachment = array();
  		$this->mail->CustomHeader = array();
	}

	public function phpm_setFrom($name,$email,$confirmMsg=false){
		if(!$email){
			return false;
		}
		if(!$name){
			$name=$email;
		}
		$this->mail->From=$email;
		$this->mail->FromName=$name;
		$this->mail->Sender=$email;
		$this->mail->ConfirmReadingTo=$confirmMsg;
		return true;
	}
	
	public function phpm_setSubject($subject){
		$this->mail->Subject=strip_tags($subject);
		return true;
	}
	
	public function phpm_setBody($body){
		if(!$body){
			return false;
		}
		$this->mail->Body=$body;
		if($this->mcfg['multipart']){
			$this->mail->AltBody=strip_tags($body);
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
		if($this->logDb){
			$this->logmsg.=$type.': '.$name.'<'.$email.'>'."\n";
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
	
	public function phpm_send($dryRun=false){
		if($this->logDb){
			$rows=array('uid','timestamp','page','recipients');
			$data=array($GLOBALS['MG']['USER']['UID'],$GLOBALS['MG']['SITE']['TIME'],$GLOBALS['MG']['PAGE']['PATH'],$this->logmsg);
			$GLOBALS['MG']['SQL']->sql_dataCommands(DB_INSERT,array($this->logDb),$rows,$data);
			$this->logmsg='';
		}
		if(!$dryRun){
			return $this->mail->Send();
		}
		else{
			print_r($this->mail);
		}
	}
	
	public function phpm_attach($file,$name){
		$this->mail->AddAttachment($file,$name);
	}
}