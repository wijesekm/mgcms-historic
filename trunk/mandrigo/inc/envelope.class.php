<?php
/**********************************************************
    envelope.class.php
	Last Edited By: Kevin Wijesekera
	Date Last Edited: 07/25/07

	Copyright (C) 2006-2007 the MandrigoCMS Group

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
if(!defined("START_MANDRIGO")){
    die($GLOBALS["MANDRIGO"]["CONFIG"]["DIE_STRING"]);
}

@include_once($GLOBALS["MANDRIGO"]["CONFIG"]["ROOT_PATH"]."phpmailer.class.".PHP_EXT);

class envelope extends phpmailer{
	
	var $sender="";
	var $recipients="";
	var $body="";
	var $attachment="";
	var $subject="";
	var $alt=false;
	
    //
    //constructor envelope($id)
    //
    //Initializes the envelope script
    //
    //INPUTS:
    //$id		-	page id
    //
    //returns object on sucess or false on fail
	function envelope($id){
      	if(!$sql_result=$GLOBALS["MANDRIGO"]["DB"]->db_fetcharray(TABLE_PREFIX.TABLE_ENVELOPE_DATA,"",array(array("page_id","=",$GLOBALS["MANDRIGO"]["CURRENTPAGE"]["ID"],DB_AND),array("part_id","=",$id)))){
            return false;
        }	
        if($sql_result['alt']){
			$this->ev_setalt(true);
		}
		$conf=array("sendmail"=>$sql_result['sendmail'],
				    "mailer"=>$sql_result['mailer'],
				    "encoding"=>$sql_result['encoding'],
				    "dctype"=>$sql_result['dctype'],
				    "wrap"=>$sql_result['wrap'],
				    "priority"=>$sql_result['priority']);

	 	$this->phpmailer($conf);
		$this->recipients["to"]=array();
		$this->recipients["cc"]=array();
		$this->recipients["bcc"]=array();
	}
	
	//#################################
	//
	// PUBLIC FUNCTIONS
	//
	//#################################
	
    //
    //public function ev_send()
    //
    //Sends the current message as is
    //
    //returns true on sucess or false on fail
	function ev_send(){
		return $this->pm_mail($this->recipients,$this->sender,$this->subject,$this->body,$this->attachment,$this->alt);	
	}
	
    //
    //public function ev_setalt($a=true)
    //
    //Sets the ALT of the current message
    //INPUTS:
    //$a	-	alt [boolean] (default: true)
    //
    //returns true on sucess or false on fail
	function ev_setalt($a=true){
		if($a){
			$this->alt=true;
		}
		return true;
	}
	
	
    //
    //public function ev_addsender($sender_addr,$sender_name,$alt_addr="")
    //
    //Adds a sender to the message
    //INPUTS:
    //$sender_addr		-	address of the sender [string]
    //$sender_name		-	name of the sender [string]
    //$alt_addr			-	alternative address for the sender [string]
    //
    //returns true on sucess or false on fail
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
		
    //
    //public function ev_addrecipient($addr,$name,$type="to")
    //
    //Adds a recipient to the message
    //INPUTS:
    //$addr				-	address of the recipient [string]
    //$sender_name		-	name of the recipient [string]
    //$type				-	type to add: to,cc,bcc [string] (default: to)
    //
    //returns true on sucess or false on fail
	function ev_addrecipient($addr,$name,$type="to"){
	 	if(!$addr){
			return false;
		}
		$this->recipients[$type]=array_merge($this->recipients[$type],array(array($name,$addr)));
		return true;
	}
		
    //
    //public function ev_addbody($body)
    //
    //Adds the body of the message
    //INPUTS:
    //$body		-	body of the message [string]
    //
    //returns true on sucess or false on fail
	function ev_addbody($body){
	 	if(!$body){
			return false;
		}
		$this->body=$body;	
	}
	
    //
    //public function ev_addsubject($subject)
    //
    //Adds the subject of the message
    //INPUTS:
    //$subject		-	subject of the message [string]
    //
    //returns true on sucess or false on fail
	function ev_addsubject($subject){
		if(!$subject){
			return false;
		}
		$this->subject=$subject;
	}
	
    //
    //public function ev_addattachment($params,$atype="misc",$encoding="base64",$type="application/octet-stream")
    //
    //Adds an attachment to the message
    //INPUTS:
    //$params		-	parameters of the attachment [array:attrs (filename,path,name,string)]
    //$atype		-	type of the attachment: string,img,misc [string] (default: misc)
    //$encoding		-	the way the attachment is going to be encoded [string] (default: base64)
    //$type			-	the attachment file type [string] (default: application/octet-stream)
    //
    //returns true on sucess or false on fail
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