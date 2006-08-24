<?php
/**********************************************************
    phpmailer.class.php
	Last Edited By: Kevin Wijesekera
	Date Last Edited: 08/18/06

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

class phpmailer{
 
	var $config=array("sendmail"=>"/usr/sbin/sendmail"
			  				,"lf"=>"\n"
							,"crlf"=>"\r\n"
							,"mailer"=>"mail"
							,"hostname"=>""
							,"encoding"=>"8bit"
							,"dctype"=>TEXT_PLAIN
							,"wrap"=>40
							,"priority"=>3);
	
	function phpmailer($conf=""){
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
	//public function pm_mail($recipients,$sender,$subject,$body,$attachments);
	//
	//sends a message
	//	
	function pm_mail($recipients,$sender,$subject,$body,$attachments=array(),$alt=true){
	  	$subject=strip_tags($subject);
	  	$ctype=$this->config["dctype"];
	  	$mtype="";
		if((count($recipients["to"])+count($recipients["cc"])+count($recipients["bcc"]))<1){
            return false;
        }
		$alen=count($attachments);
		if($alt){
			$ctype=MULTIPART_ALT;
		}	
		if($alen>0){
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
				return $this->pm_sendmail($body,$headers,$sender);
			break;
			case "mail":
			default:
				return $this->pm_phpmail($send_to,$subject,$body,$headers,$sender);
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
	//private function pm_phpmail($send_to,$subject,$body,$headers,$sender);
	//
	//sends the message using the php mail function
	//
	function pm_phpmail($send_to,$subject,$body,$headers,$sender){
	  	$params="";
		if($sender!=""&&strlen(ini_get("safe_mode"))<1){
		    $old_from = ini_get("sendmail_from");
            ini_set("sendmail_from", $sender[0][1]);
            $params = sprintf("-oi -f %s", $sender[0][1]);
		}
        if($GLOBALS["MANDRIGO_CONFIG"]["DEBUG_MODE"]){
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
	//private function pm_sendmail($send_to,$subject,$body,$headers;
	//
	//sends the message using the sendmail functionality
	//	
	function pm_sendmail($body,$headers,$sender){
        if ($sender!=""){
			$sendmail=sprintf("%s -oi -f %s -t", $this->config["sendmail"], $sender[0][1]);	
		}
        else{
			$sendmail=sprintf("%s -oi -t", $this->config["sendmail"]);
		}
		if($GLOBALS["MANDRIGO_CONFIG"]["DEBUG_MODE"]){
			$mail = popen($sendmail, "w");
		}
		else{
		 	if(!(@$mail=popen($sendmail, "w"))){
				return false;
			}
		}

        @fputs($mail, $headers);
        @fputs($mail, $body);
		if($GLOBALS["MANDRIGO_CONFIG"]["DEBUG_MODE"]){
			$result = pclose($mail) >> 8 & 0xFF;
		}
		else{
		  	@$result=pclose($mail) >> 8 & 0xFF;
		 	if(!$result){
				return false;
			}
		}        
        return true;
	}
	
	//
	//private function pm_mkaddr($addrs,$type,$header=true);
	//
	//sends the message using the php mail function
	//
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
	//private function pm_formataddr($name,$address,$encode=true);
	//
	//sends the message using the php mail function
	//
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
	//private function pm_formataddr($name,$address,$encode=true);
	//
	//sends the message using the php mail function
	//
	function pm_makeheader($recipients,$sender,$subject,$attachments,$ctype,$mtype="plain"){
	  
	  	$mid = md5(uniqid(time()));
	  	$this->config["b1"]="b1_" . $mid;
	  	$this->config["b2"]="b2_" . $mid;
		if(!$sender||!$recipients){
			return false;
		}
		$header="";
		$header.=$this->pm_headerline("Date",date("D, j M Y H:i:S O"));
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
		$header.=$this->pm_headerline("X-Mailer", "mandrigoCMS [version ".$GLOBALS["SITE_DATA"]["MANDRIGO_VER"]."]");
		$header.=$this->pm_headerline("MIME-Version", "1.0");
		switch($mtype){
			case "plain":
				$header.=$this->pm_headerline("Content-Transfer-Encoding",$this->config["encoding"]);
				$header.=sprintf("Content-Type: %s; charset=\"%s\"",$ctype, $GLOBALS["LANGUAGE"]["ENCODING"]);
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
            	$result .= $this->pm_formatboundary($this->config["b1"]);
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
    function pm_formatboundary($boundary,$ctype) {
        $result = "";
        $result .= "--".$boundary.$this->config["lf"];
        $result .= sprintf("Content-Type: %s; charset = \"%s\"",$ctype,$GLOBALS["LANGUAGE"]["CHARSET"]);
        $result .= $this->config["lf"];
        $result .= $this->pm_headerline("Content-Transfer-Encoding", $this->config["encoding"]);
        $result .= $this->config["lf"];
        return $result;
    }
    function pm_endboundary($boundary) {
        return $this->config["lf"] . "--" . $boundary . "--" . $this->config["lf"]; 
    }
    function pm_wrapbody($body){
        if($this->config["wrap"]<1){
			return false;
		}  
        return $this->pm_wraptext($body, $this->config["wrap"]);
    }
    function pm_wraptext($message, $length, $qp_mode = false) {
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
	function pm_attach($attachments){
    	// Return text of body
        $mime = array();

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
	//private function pm_headerline($name,$value);
	//
	//returns the formatted header value
	//
	function pm_headerline($name,$value){
		return $name.": ".$value.$this->config["crlf"];
	}
	
	//private function pm_bodyline($value);
	//
	//returns the formatted bodyline
	//		
	function pm_bodyline($value) {
        return $value.$this->config["lf"];
    }
	//
	
	//private function pm_encodeheader($str,$position='text');
	//
	//encodes the header
	//	
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
		$maxlen = 75 - 7 - strlen($GLOBALS["LANGUAGE"]["ENCODING"]);
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

      	$encoded = preg_replace('/^(.*)$/m', " =?".$GLOBALS["LANGUAGE"]["ENCODING"]."?$encoding?\\1?=", $encoded);
      	$encoded = trim(str_replace("\n", $this->config["endline"], $encoded));
      
      	return $encoded;
    }
    
	//private function pm_encodestring($str,$encoding='text');
	//
	//encodes a string
	//	
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
    function pm_encodefile($file,$encoding){
        if(!@$fd = fopen($path, "rb")){
        	return false;
        }
        $magic_quotes = get_magic_quotes_runtime();
        set_magic_quotes_runtime(0);
        $file_buffer=fread($fd, filesize($path));
        $file_buffer=$this->pm_encodestring($file_buffer,$encoding);
        fclose($fd);
        set_magic_quotes_runtime($magic_quotes);
        return $file_buffer;		
	}
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
	function pm_inlineimg($attachments) {
        for($i=0;$i<count($attachments);$i++){
            if($attachment[$i][6]=="inline"){
                return true;
            }
        }
        return false;
    } 
    function pm_fixeol($str) {
        $str = str_replace("\r\n","\n",$str);
        $str = str_replace("\r","\n",$str);
        $str = str_replace("\n",$this->config["lf"],$str);
        return $str;
    }
}
?>