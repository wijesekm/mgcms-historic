<?php
/**********************************************************
    phpmailer.class.php
	Last Edited By: Kevin Wijesekera
	Date Last Edited: 07/25/07

	Copyright (C) 2006-2007 the MandrigoCMS Group

	Based off of the phpmailer class Version 1.73 which is written by
	the php mailer team - http://phpmailer.sourceforge.net/
	
	phpmailer is Copyright (C) 2005 the php mailer team

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

class phpmailer{

	var $config;
	
    //
    //constructor phpmailer($conf="")
    //
    //Initializes the phpmailer script
    //
    //INPUTS:
    //$conf		-	configuration [array:attributes(sendmail,lf,crlf,mailer,hostname,encoding,dctype,wrap,priority)] (default: )
    //
    //returns object on sucess or false on fail	
	function phpmailer($conf=""){
	 	$this->config=array("sendmail"=>"/usr/sbin/sendmail"
			  				,"lf"=>"\n"
							,"crlf"=>"\r\n"
							,"mailer"=>"mail"
							,"hostname"=>""
							,"encoding"=>"8bit"
							,"dctype"=>TEXT_PLAIN
							,"wrap"=>40
							,"priority"=>3);
	 	$host=explode("/",ereg_replace("^[a-z]+://","",$GLOBALS["SITE_DATA"]["SITE_URL"]));
	 	$this->config["hostname"]=$host[0];
		if($conf){
			$this->config=array_merge($this->config,$conf);
		}
	}

	//#################################
	//
	// PUBLIC FUNCTIONS
	//
	//#################################
	
    //
    //public function pm_mail($recipients,$sender,$subject,$body,$attachments=array(),$alt=true)
    //
    //Sends a message
    //INPUTS:
    //$recipiants		-	list of recipients [array]
    //$sender			-	name and address of sender [array]
    //$subject			-	subject of message [string]
    //$body				-	message body [string]
    //$attachments		-	array of attachments [array] (default: array())
    //$alt				-	sending using alt instead of plain [boolean] (default: true)
    //
	//returns true on sucess or false on fail
	function pm_mail($recipients,$sender,$subject,$body,$attachments=array(),$alt=true){
	  	$subject=strip_tags($subject);
	  	print_r($attachments);
	  	$ctype=$this->config["dctype"];
	  	$mtype="";
		if((count($recipients["to"])+count($recipients["cc"])+count($recipients["bcc"]))<1){
            return false;
        }
		if($alt){
			$ctype=MULTIPART_ALT;
		}	
		if($attachments[1]){
			if($alt){
				$mtype="alt_attachments";
			}
			else{
				$mtype="attachments";
			}
		}
		else if($alt){
			$mtype="alt";
		}
		else{
			$mtype="plain";
		}
		$headers=$this->pm_makeheader($recipients,$sender,$subject,$attachments,$ctype,$mtype);
		$body=$this->pm_makebody($body,$attachments,$mtype);
		$send_to=$this->pm_mkaddr($recipients["to"],"",false);
		
		switch($this->config["mailer"]){
			case "sendmail":
				return $this->pm_sendmail($body,$headers,$sender[0][1]);
			break;
			case "mail":
			default:
				return $this->pm_phpmail($send_to,$subject,$body,$headers,$sender[0][1]);
			break;
		};  
		return false;
	}
	
	//#################################
	//
	// PRIVATE FUNCTIONS
	//
	//#################################	

    //
    //private function pm_phpmail($send_to,$subject,$body,$headers,$sender)
    //
    //Sends a message using the php mail function
    //INPUTS:
    //$send_to		-	recipiant to send to [string]
    //$subject		-	subject [string]
    //$body			-	body of message [string]
    //$headers		-	message headers [string]
    //$sender		-	message sender [string]
    //
	//returns true on sucess or false on fail
	function pm_phpmail($send_to,$subject,$body,$headers,$sender){
	  	$params="";
		if($sender!=""&&strlen(ini_get("safe_mode"))<1){
		    $old_from = ini_get("sendmail_from");
            ini_set("sendmail_from", $sender);
            $params = sprintf("-oi -f %s", $sender);
		}
        if($GLOBALS["MANDRIGO"]["CONFIG"]["DEBUG_MODE"]){
			mail($send_to, $this->pm_encodeheader($subject), $body, $header,$params);
		}
		else{
		 	if(!(@mail($send_to, $this->pm_encodeheader($subject), $body, $header, $params))){
				return false;
			}
		}
		if (isset($old_from)){
			ini_set("sendmail_from", $old_from); 
		}
		return true;
	}
	
    //
    //private function pm_sendmail($body,$headers,$sender)
    //
    //Sends a message using the sendmail script when installed
    //INPUTS:
    //$body			-	body of message [string]
    //$headers		-	message headers [string]
    //$sender		-	message sender [string]
    //
	//returns true on sucess or false on fail	
	function pm_sendmail($body,$headers,$sender){
        if ($sender!=""){
			$sendmail=sprintf("%s -oi -f %s -t", $this->config["sendmail"], $sender);	
		}
        else{
			$sendmail=sprintf("%s -oi -t", $this->config["sendmail"]);
		}
		if($GLOBALS["MANDRIGO"]["CONFIG"]["DEBUG_MODE"]){
			$mail = popen($sendmail, "w");
		}
		else{
		 	if(!(@$mail=popen($sendmail, "w"))){
				return false;
			}
		}

        @fputs($mail, $headers);
        @fputs($mail, $body);

		@$result=pclose($mail) >> 8 & 0xFF;
      
        return true;
	}
	
    //
    //private function pm_mkaddr($addr,$type="To",$header=true)
    //
    //Changes an address array into a string of recipiants formatted to send
    //INPUTS:
    //$addr			-	address [string]
    //$type			-	address type: to,cc,bcc [string] (default: To)
    //$header		-	address going in the header of a message [boolean] (default: true)
    //
	//returns string of addresses on sucess or false on fail	
	function pm_mkaddr($addr,$type="To",$header=true){
	  	$str="";
		if(!count($addr)){
			return false;
		}
	  	if($header){
			$str=$type.": ";
		}
		$soa=count($addr);
		for($i=0;$i<$soa;$i++){
			$str.=$this->pm_formataddr($addr[$i][0],$addr[$i][1],$header);	
			if($i+1<$soa){
				$str.=", ";
			}
		}
		if($header){
			$str.=$this->config["crlf"];
		}
		return $str;
	}

    //
    //private function pm_formataddr($name,$address,$encode=true)
    //
    //Formats a single address
    //INPUTS:
    //$name			-	name [string]
    //$address		-	address [string]
    //$encode		-	encode the address (if going in the header you should)? [boolean] (default: true)
    //
	//returns address string on sucess or false on fail	
	function pm_formataddr($name,$address,$encode=true){
		if($encode){
			if($name){
				return $this->pm_encodeheader(trim($name),"phrase")." <".trim($address).">";
			}
			else{
				return $address;
			}
		}
		else{
			if($name){
				return $name." <".$address.">";
			}
			else{
				return $address;
			}			
		}
		return false;
	}
	

    //
    //private function pm_makeheader($recipients,$sender,$subject,$attachments,$ctype,$mtype="plain")
    //
    //Formats the message header
    //INPUTS:
    //$recipients	-	array of recipients [array with to,bcc,cc subarrays]
    //$sender		-	array of sender data [array]
    //$subject		-	subject of the message [string]
    //$attachments  -   array of attachments [array]
    //$ctype		-	content type of message [string]
    //$mtype		-	message type: plain,attachments,alt_attachments,alt [string] (default: plain)
    //
	//returns header string on sucess or false on fail		
	function pm_makeheader($recipients,$sender,$subject,$attachments,$ctype,$mtype="plain"){
	  
	  	$mid = md5(uniqid(time()));
	  	$this->config["b1"]="b1_" . $mid;
	  	$this->config["b2"]="b2_" . $mid;
		if(!$sender||!$recipients){
			return false;
		}
		$header="";
		$header.=$this->pm_headerline("Date",date("D, j M Y H:i:s O"));
		$header.=$this->pm_headerline("Return-Path",$this->pm_formataddr($sender[0][0],$sender[0][1]));
		
        // To be created automatically by mail()
        if($this->config["mailer"]!="mail"){
            if(count($recipients["to"]) > 0){
				$header.=$this->pm_mkaddr($recipients["to"],"To");
			}
            else if (count($recipients["cc"]) == 0){
				$header.=$this->pm_headerline("To","undisclosed-recipients:;");
			}
            if(count($recipients["cc"]) > 0){
				$header.=$this->pm_mkaddr($recipients["cc"],"Cc");
			}
        }
		$header.=$this->pm_headerline("From",$this->pm_formataddr($sender[0][0],$sender[0][1]));
		if((($this->config["mailer"]=="sendmail")||($this->config["mailer"]=="mail"))&&(count($recipients["bcc"])>0)){
			$header.=$this->pm_mkaddr($recipients["bcc"],"Bcc");
		}
		if($sender[1]){
			$header.=$this->pm_headerline("Reply-to",$this->pm_formataddr($sender[1][0],$sender[1][1]));	
		}
		else{
			$header.=$this->pm_headerline("Reply-to",$this->pm_formataddr($sender[0][0],$sender[0][1]));		
		}
		if($this->config["mailer"]!="mail"){
			$header.=$this->pm_headerline("Subject",$this->pm_encodeheader(trim($subject)));
		}
		$header.=$this->pm_headerline("Message-ID","<".$mid."@".$this->config["hostname"].">");
		$header.=$this->pm_headerline("X-Priority",$this->config["priority"]);
		$header.=$this->pm_headerline("X-Mailer", "mandrigoCMS [version ".$GLOBALS["MANDRIGO"]["SITE"]["MANDRIGO_VER"]."]");
		$header.=$this->pm_headerline("MIME-Version", "1.0");
		switch($mtype){
			case "plain":
				$header.=$this->pm_headerline("Content-Transfer-Encoding",$this->config["encoding"]);
				$header.=sprintf("Content-Type: %s; charset=\"%s\"",$ctype, $GLOBALS["MANDRIGO"]["LANGUAGE"]["ENCODING"]);
			break;
			case "attachments":
			case "alt_attachments":
				if($this->pm_inlineimg($attachments)){
					$header.=sprintf("Content-Type: %s;%s\ttype=\"text/html\";%s\tboundary=\"%s\"%s", "multipart/related", $this->config["crlf"], $this->config["crlf"], $this->config["b1"], $this->config["crlf"]);
				}
				else{
                    $header.=$this->pm_headerline("Content-Type", "multipart/mixed;");
                    $header.=$this->pm_bodyline("\tboundary=\"".$this->config["b1"].'"');					
				}
			break;
			case "alt":
			    $header.=$this->pm_headerline("Content-Type", "multipart/alternative;");
                $header.=$this->pm_bodyline("\tboundary=\"".$this->config["b1"].'"');
			break;
		}
		if($this->config["mailer"]!="mail"){
            $header.=$this->config["crlf"].$this->config["crlf"];
        }
		return $header;
	}

    //
    //private function pm_makebody($body,$attachments,$mtype)
    //
    //Formats the message body
    //INPUTS:
    //$body			-	body contents [string]
    //$attachments	-	array of attachments [array]
    //$mtype		-	message type: plain,attachments,alt_attachments,alt [string] (default: plain)
    //
	//returns body string on sucess or false on fail	
	function pm_makebody($body,$attachments,$mtype){
        $result = "";
        $plain_body=strip_tags($body);
        $plain_body=$this->pm_wrapbody($plain_body);
        switch($mtype){
        	case "alt":
            	$result .= $this->pm_formatboundary($this->config["b1"],TEXT_PLAIN);
				$result .= $this->pm_encodestring($plain_body, $this->config["encoding"]);
				$result .= $this->config["lf"].$this->config["lf"];
                $result .= $this->pm_formatboundary($this->config["b1"],TEXT_HTML);
				$result .= $this->pm_encodestring($body, $this->config["encoding"]);
				$result .= $this->config["lf"].$this->config["lf"];
                $result .= $this->pm_endboundary($this->config["b1"]);

            break;
            case "plain":
                $result.=$this->pm_encodestring($body, $this->config["encoding"]);
            break;
            case "attachments":
            	$result .= $this->pm_formatboundary($this->config["b1"],"");
                $result .= $this->pm_encodestring($body, $this->config["encoding"]);
                $result .= $this->config["lf"];
                $result .= $this->pm_attach($attachments);
            break;
            case "alt_attachments":
                $result .= sprintf("--%s%s",$this->config["b1"],$this->config["lf"]);
                $result .= sprintf("Content-Type: %s;%s\tboundary=\"%s\"%s",MULTI_ALT, $this->config["lf"],$this->config["b2"], $this->config["lf"].$this->config["lf"]);
                // Create text body
           		$result .= $this->pm_formatboundary($this->config["b2"],TEXT_PLAIN);
                $result .= $this->pm_encodestring($plain_body, $this->config["encoding"]);
                $result .= $this->config["lf"].$this->config["lf"];
    
                // Create the HTML body
                $result .= $this->pm_formatboundary($this->config["b2"],TEXT_HTML);
                $result .= $this->pm_encodestring($body, $this->config["encoding"]);
                $result .= $this->config["lf"].$this->config["lf"];
                $result .= $this->pm_endboundary($this->config["b2"]);
                $result .= $this->pm_attach($attachments);
            break;
        };
        return $result;
    }

    //
    //private function pm_formatboundary($boundary,$ctype)
    //
    //Formats the message boundry
    //INPUTS:
    //$boundary		-	boundry id [string]
    //$ctype		-	content type [string]
    //
	//returns boundry string on sucess or false on fail
    function pm_formatboundary($boundary,$ctype){
        $result = "";
        $result .= "--".$boundary.$this->config["lf"];
        $result .= sprintf("Content-Type: %s; charset = \"%s\"",$ctype,$GLOBALS["MANDRIGO"]["LANGUAGE"]["CHARSET"]);
        $result .= $this->config["lf"];
        $result .= $this->pm_headerline("Content-Transfer-Encoding", $this->config["encoding"]);
        $result .= $this->config["lf"];
        return $result;
    }

    //
    //private function pm_endboundary($boundary)
    //
    //Ends the boundry
    //INPUTS:
    //$boundary		-	boundry id [string]
    //
	//returns boundry string on sucess or false on fail
    function pm_endboundary($boundary) {
        return $this->config["lf"] . "--" . $boundary . "--" . $this->config["lf"]; 
    }
    
    //
    //private function pm_wrapbody($body)
    //
    //Wraps the body text to fit the message
    //INPUTS:
    //$body		-	body text [string]
    //
	//returns body string on sucess or false on fail
    function pm_wrapbody($body){
        if($this->config["wrap"]<1){
			return false;
		}  
        return $this->pm_wraptext($body, $this->config["wrap"]);
    }
    
    //
    //private function pm_wraptext($message, $length, $qp_mode = false)
    //
    //Wraps text
    //INPUTS:
    //$message		-	text to be wrapped [string]
    //$length		-	length to wrap at [string]
    //$qp_mode		-	quoted-printable encoding mode [boolean] (default: false)
    //
	//returns text string on sucess or false on fail
    function pm_wraptext($message, $length, $qp_mode = false){
        $soft_break=($qp_mode)?sprintf(" =%s", $this->config["lf"]):$this->config["lf"];
        $message = $this->pm_fixeol($message);
        if (substr($message,-1) == $this->config["lf"]){
			$message = substr($message, 0, -1);
		}
        $line = explode($this->config["lf"], $message);
        $message = "";
        for ($i=0 ;$i < count($line); $i++){
        	$line_part = explode(" ", $line[$i]);
          	$buf = "";
          	for ($e = 0; $e<count($line_part); $e++){
              	$word = $line_part[$e];
            	if($qp_mode and (strlen($word) > $length)){
                	$space_left = $length - strlen($buf) - 1;
                	if($e != 0){
                    	if($space_left > 20){
                        	$len = $space_left;
                        	if(substr($word, $len - 1, 1) == "="){
								$len--;
							}
                        	else if(substr($word, $len - 2, 1) == "="){
						  		$len -= 2;
							}
                        	$part = substr($word, 0, $len);
                        	$word = substr($word, $len);
                        	$buf .= " " . $part;
                        	$message .= $buf . sprintf("=%s", $this->LE);
                    	}
                    	else{
                        	$message .= $buf . $soft_break;
                    	}
                    	$buf = "";
                	}
                	while(strlen($word) > 0){
                    	$len = $length;
                        if(substr($word, $len - 1, 1) == "="){
							$len--;
						}
                        else if(substr($word, $len - 2, 1) == "="){
						  	$len -= 2;
						}
                    	$part = substr($word, 0, $len);
                    	$word = substr($word, $len);

                    	if (strlen($word) > 0){
							$message .= $part . sprintf("=%s", $this->config["lf"]);
						}
                		else{
							$buf = $part;	
						}
            		}
            	}
              	else{
                	$buf_o = $buf;
                	$buf .= ($e == 0) ? $word : (" " . $word); 
					if (strlen($buf) > $length and $buf_o != ""){
                    	$message .= $buf_o . $soft_break;
                    	$buf = $word;
                	}
              	}
          	}
          	$message .= $buf . $this->config["lf"];
    	}
        return $message;
	}
    
    //
    //private function pm_attach($attachments)
    //
    //Formats the attachments
    //INPUTS:
    //$attachments		-	array of attachments [array]
    //
	//returns attachments string on sucess or false on fail
	function pm_attach($attachments){
        $mime = array();
		if(!$attachments){
			return false;
		}
        // Add all attachments
        for($i=0;$i<count($attachments);$i++){
            // Check for string attachment
            $bString = $attachments[$i][5];
            if ($bString){
				$string = $attachments[$i][0];	
			}
            else{
				$path = $attachments[$i][0];
			}
            $filename    = $attachments[$i][1];
            $name        = $attachments[$i][2];
            $encoding    = $attachments[$i][3];
            $type        = $attachments[$i][4];
            $disposition = $attachments[$i][6];
            $cid         = $attachments[$i][7];
            
            $mime[] = sprintf("--%s%s", $this->config["b1"], $this->config["lf"]);
            $mime[] = sprintf("Content-Type: %s; name=\"%s\"%s", $type, $name, $this->config["lf"]);
            $mime[] = sprintf("Content-Transfer-Encoding: %s%s", $encoding, $this->config["lf"]);

            if($disposition == "inline"){
				$mime[] = sprintf("Content-ID: <%s>%s", $cid, $this->config["b1"]);	
			}
            $mime[] = sprintf("Content-Disposition: %s; filename=\"%s\"%s", $disposition, $name, $this->config["b1"].$this->config["b1"]);
            // Encode as string attachment
            if($bString){
                $mime[] = $this->pm_encodestring($string, $encoding);
                $mime[] = $this->config["lf"].$this->config["lf"];
            }
        	else{
                $mime[] = $this->pm_encodefile($path, $encoding);                
                $mime[] = $this->config["lf"].$this->config["lf"];
            }
    	}
        $mime[] = sprintf("--%s--%s", $this->config["b1"], $this->config["lf"]);
        return join("", $mime);		
	}
	
    //
    //private function pm_headerline($name,$value)
    //
    //Makes a header line
    //INPUTS:
    //$name		-	element name [string]
    //$value	-	element value [string]
    //
	//returns header line string on sucess or false on fail	
	function pm_headerline($name,$value){
		return $name.": ".$value.$this->config["crlf"];
	}
	
    //
    //private function pm_bodyline($value)
    //
    //Makes a body line
    //INPUTS:
    //$value		-	element value [string]
    //
	//returns body line string on sucess or false on fail	
	function pm_bodyline($value){
        return $value.$this->config["lf"];
    }
	
    //
    //private function pm_encodeheader($str,$position='text')
    //
    //Encodes a header element
    //INPUTS:
    //$str			-	element [string]
    //$position 	-	element type: phrase,comment,text,other [string] (defualt: text)
    //
	//returns header element string on sucess or false on fail	    
    function pm_encodeheader($str,$position='text'){
		$x = 0; 
      	switch (strtolower($position)) {
    		case 'phrase':
          		if (!preg_match('/[\200-\377]/', $str)) {
	        		// Can't use addslashes as we don't know what value has magic_quotes_sybase.
	            	$encoded = addcslashes($str, "\0..\37\177\\\"");
	            	if (($str == $encoded) && !preg_match('/[^A-Za-z0-9!#$%&\'*+\/=?^_`{|}~ -]/', $str)){
	            		return ($encoded);
	            	}
	            	else{
	              		return ("\"$encoded\"");
	              	}
          		}
          		$x = preg_match_all('/[^\040\041\043-\133\135-\176]/', $str, $matches);
          	break;
        	case 'comment':
          		$x = preg_match_all('/[()"]/', $str, $matches);
          		// Fall-through
        	case 'text':
        	default:
          		$x += preg_match_all('/[\000-\010\013\014\016-\037\177-\377]/', $str, $matches);
         	break;
		};
		if ($x == 0){
			return ($str);
		}
		$maxlen = 75 - 7 - strlen($GLOBALS["MANDRIGO"]["LANGUAGE"]["ENCODING"]);
      	// Try to select the encoding which should produce the shortest output
      	if (strlen($str)/3 < $x){
        	$encoding = 'B';
        	$encoded = base64_encode($str);
        	$maxlen -= $maxlen % 4;
        	$encoded = trim(chunk_split($encoded, $maxlen, "\n"));
      	} 
		else{
        	$encoding = 'Q';
        	$encoded = $this->EncodeQ($str, $position);
        	$encoded = $this->WrapText($encoded, $maxlen, true);
        	$encoded = str_replace("=".$this->config["endline"], "\n", trim($encoded));
      	}

      	$encoded = preg_replace('/^(.*)$/m', " =?".$GLOBALS["MANDRIGO"]["LANGUAGE"]["ENCODING"]."?$encoding?\\1?=", $encoded);
      	$encoded = trim(str_replace("\n", $this->config["endline"], $encoded));
      
      	return $encoded;
    }
     
    //
    //private function pm_encodefile($file,$encoding)
    //
    //Encodes a file
    //INPUTS:
    //$file			-	file to encode [string]
    //$encoding 	-	encoding type: base64,7bit,8bit,binary,quoted-printable [string] (defualt: base64)
    //
	//returns encoded file on sucess or false on fail	      
    function pm_encodefile($file,$encoding){
        if(!@$fd = fopen($path, "rb")){
        	return false;
        }
        $file_buffer=fread($fd, filesize($path));
        $file_buffer=$this->pm_encodestring($file_buffer,$encoding);
        fclose($fd);
        return $file_buffer;		
	}
	
    //
    //private function pm_encodestring($str,$encoding="base64")
    //
    //Encodes a string
    //INPUTS:
    //$str			-	string [string]
    //$encoding 	-	encoding type: base64,7bit,8bit,binary,quoted-printable [string] (defualt: base64)
    //
	//returns encoded string on sucess or false on fail	      
    function pm_encodestring($str,$encoding="base64") {
    	$encoded = "";
        switch(strtolower($encoding)) {
	        case "base64":
	            $encoded=chunk_split(base64_encode($str),76,$this->config["lf"]);
	        break;
	        case "7bit":
	        case "8bit":
	        	
	            $encoded = $this->pm_fixeol($str);
	            if(substr($encoded,-(strlen($this->config["lf"])))!=$this->config["lf"]){
					$encoded.=$this->config["lf"];
				}
	        break;
	        case "binary":
	            $encoded = $str;
	        break;
	        case "quoted-printable":
	              $encoded = $this->pm_encodeqp($str);
	        break;
	        default:
	        	return false;
	        break;
        };
        return $encoded;
    }

	
    //
    //private function pm_encodeqp($str)
    //
    //Encodes a string quoted-printable
    //INPUTS:
    //$str			-	string
    //
	//returns encoded string on sucess or false on fail		
    function pm_encodeqp($str){
        $encoded = $this->pm_fixeol($str);
        if (substr($encoded, -(strlen($this->config["endline"]))) != $this->config["endline"]){
			$encoded.=$this->config["endline"];	
		}
        // Replace every high ascii, control and = characters
        $encoded = preg_replace('/([\000-\010\013\014\016-\037\075\177-\377])/e',"'='.sprintf('%02X', ord('\\1'))", $encoded);
        // Replace every spaces and tabs when it's the last character on a line
        $encoded = preg_replace("/([\011\040])".$this->config["endline"]."/e","'='.sprintf('%02X', ord('\\1')).'".$this->config["endline"]."'", $encoded);

        // Maximum line length of 76 characters before CRLF (74 + space + '=')
        $encoded = $this->WrapText($encoded, 74, true);

        return $encoded;
    }
    
	//
    //private function pm_encodeq($str,$position="text")
    //
    //Encodes a string eq
    //INPUTS:
    //$str			-	string
    //$position		-	string type: phrase,comment,text,misc [string] (default: text)
    //
	//returns encoded string on sucess or false on fail	   
    function pm_encodeq($str,$position="text"){
        // There should not be any EOL in the string
        $encoded = preg_replace("[\r\n]", "", $str);

        switch (strtolower($position)) {
          case "phrase":
            $encoded = preg_replace("/([^A-Za-z0-9!*+\/ -])/e", "'='.sprintf('%02X', ord('\\1'))", $encoded);
            break;
          case "comment":
            $encoded = preg_replace("/([\(\)\"])/e", "'='.sprintf('%02X', ord('\\1'))", $encoded);
          case "text":
          default:
            // Replace every high ascii, control =, ? and _ characters
            $encoded = preg_replace('/([\000-\011\013\014\016-\037\075\077\137\177-\377])/e',
                  "'='.sprintf('%02X', ord('\\1'))", $encoded);
            break;
        }
        
        // Replace every spaces to _ (more readable than =20)
        $encoded = str_replace(" ", "_", $encoded);

        return $encoded;
    }
    
    
	//
    //private function pm_inlineimg($attachments)
    //
    //Checks to see if an attachment is inline
    //INPUTS:
    //$attachments	-	attachments [array]
    //
	//returns true of image inline or false if not
	function pm_inlineimg($attachments) {
        for($i=0;$i<count($attachments);$i++){
            if($attachment[$i][6]=="inline"){
                return true;
            }
        }
        return false;
    } 
    
    
	//
    //private function pm_fixeol($str)
    //
    //Fixes endlines
    //INPUTS:
    //$str			-	string
    //
	//returns fixed string on sucess or false on fail	   
    function pm_fixeol($str) {
        $str = str_replace("\r\n","\n",$str);
        $str = str_replace("\r","\n",$str);
        $str = str_replace("\n",$this->config["lf"],$str);
        return $str;
    }
}