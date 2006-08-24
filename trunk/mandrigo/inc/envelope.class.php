<?php
/**********************************************************
    envelope.class.php
	Last Edited By: Kevin Wijesekera
	Date Last Edited: 08/24/06

	Copyright (C) 2006 the MandrigoCMS Group

	phpmailer.class.php is a rewrite of PHPMailer which is
	Copyright (C) 2001 - 2003  Brent R. Matzelle and is licensed
	under the Light General Public License

    ##########################################################
	This program is free software; you can redistribute it and/or
	modify it under the terms of the GNU General Public License
	as published by the Free Software Foundation; either version 2
	of the License, or (at your option) any later version.

	This program is distributed in the hope that it will be useful,
	but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	GNU General Public License for more details.

	You should have received a copy of the GNU General Public License
	along with this program; if not, write to the Free Software
	Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.

	###########################################################

**********************************************************/

//
//To prevent direct script access
//
if(!defined('START_MANDRIGO')){
    die('<html><head>
            <title>Forbidden</title>
        </head><body>
            <h1>Forbidden</h1><hr width="300" align="left"/><p>You do not have permission to access this file directly.</p>
        </html></body>');
}

class envelope extends phpmailer{
	
	var $sender="";
	var $recipients="";
	var $body="";
	var $attachment="";
	var $subject="";
	var $alt=false;
	var $db;
	
	function envelope($id,&$sql_db){
	 	$this->db=$sql_db;
	 	$conf=$this->ev_load($id);
	 	$this->phpmailer($conf);
		$this->recipients["to"]=array();
		$this->recipients["cc"]=array();
		$this->recipients["bcc"]=array();
	}
	function ev_load($id){
      	if(!$sql_result=$this->db->db_fetcharray(TABLE_PREFIX.TABLE_ENVELOPE_DATA,"",array(array("page_id","=",$GLOBALS["PAGE_DATA"]["ID"],DB_AND),array("part_id","=",$i)))){
            return false;
        }	
		return array("sendmail"=>$sql_result['sendmail']
						,"mailer"=>$sql_result['mailer']
						,"encoding"=>$sql_result['encoding']
						,"dctype"=>$sql_result['dctype']
						,"wrap"=>$sql_result['wrap']
						,"priority"=>$sql_result['priority']);
	}
	//#################################
	//
	// PUBLIC FUNCTIONS
	//
	//#################################	
	function ev_send(){
		return $this->pm_mail($this->recipients,$this->sender,$this->subject,$this->body,$this->attachment,$this->alt);	
	}
	function ev_setalt($a=true){
		if($a){
			$this->alt=true;
		}
		return true;
	}
	function ev_addsender($sender_addr,$sender_name,$alt_addr=""){
	 	if(!$sender_addr){
			return false;
		}
	 	if($alt_addr){
			$this->sender=array(array($sender_name,$sender_addr),array($sender_name,$alt_addr));		
		}
		else{
			$this->sender=array(array($sender_name,$sender_addr));	
		}
		return true;
	}
	function ev_addrecipient($addr,$name,$type="to"){
	 	if(!$addr){
			return false;
		}
		$this->recipients[$type]=array_merge($this->recipients[$type],array(array($name,$addr)));
		return true;
	}
	function ev_addbody($body){
	 	if(!$body){
			return false;
		}
		$this->body=$body;	
	}
	function ev_addsubject($subject){
		if(!$subject){
			return false;
		}
		$this->subject=$subject;
	}
	function ev_addattachment($params,$atype="misc",$encoding="base64",$type="application/octet-stream"){
    	if($atype!='string'){
			if(!@is_file($params["path"])){
	            return false;
	        }	
        	$params["filename"]=basename($params["path"]);
	        if($params["name"]==""){
				$params["name"]=$params["filename"];	
			}					
		}
		switch($atype){
			case 'string':
		        $cur=count($this->attachment);
		        $this->attachment[$cur][0] = $params["string"];
		        $this->attachment[$cur][1] = $params["filename"];
		        $this->attachment[$cur][2] = $params["filename"];
		        $this->attachment[$cur][3] = $encoding;
		        $this->attachment[$cur][4] = $type;
		        $this->attachment[$cur][5] = true; // isString
		        $this->attachment[$cur][6] = "attachment";
		        $this->attachment[$cur][7] = 0;			
			break;
			case 'img':
		        $cur = count($this->attachment);
		        $this->attachment[$cur][0] = $params["path"];
		        $this->attachment[$cur][1] = $params["filename"];
		        $this->attachment[$cur][2] = $params["name"];
		        $this->attachment[$cur][3] = $encoding;
		        $this->attachment[$cur][4] = $type;
		        $this->attachment[$cur][5] = false; // isStringAttachment
		        $this->attachment[$cur][6] = "inline";
		        $this->attachment[$cur][7] = $params["cid"];
			break;
			case 'misc':
			default:
		        $cur=count($this->attachment);
		        $this->attachment[$cur][0]=$params["path"];
		        $this->attachment[$cur][1]=$params["filename"];
		        $this->attachment[$cur][2]=$params["name"];
		        $this->attachment[$cur][3]=$encoding;
		        $this->attachment[$cur][4]=$type;
		        $this->attachment[$cur][5]=false; // isStringAttachment
		        $this->attachment[$cur][6]="attachment";
		        $this->attachment[$cur][7]=0;			
			break;
			
		}
        return true;
	}	
}