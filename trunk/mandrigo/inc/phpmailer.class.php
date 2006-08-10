<?php

class phpmailer{
	  
	function phpmailer(){
		$this->config["sendmail"]="/usr/sbin/sendmail";
		$this->config["endline"]="\n";
	}
	 	
	//#################################
	//
	// PRIVATE FUNCTIONS
	//
	//#################################

	//
	//private function fm_phpmail($send_to,$subject,$body,$headers,$sender);
	//
	//sends the message using the php mail function
	//
	function pm_phpmail($send_to,$subject,$body,$headers,$sender){
	  	$params="";
		if($sender!=""&&strlen(ini_get("safe_mode"))<1){
		    $old_from = ini_get("sendmail_from");
            ini_set("sendmail_from", $sender);
            $params = sprintf("-oi -f %s", $sender);
		}
        if($GLOBALS["MANDRIGO_CONFIG"]["DEBUG_MODE"]){
			mail($send_to, $this->EncodeHeader($subject), $body, $header, $params);	
		}
		else{
		 	if(!(@mail($send_to, $this->EncodeHeader($subject), $body, $header, $params))){
				return false;
			}
		}
		if (isset($old_from)){
			ini_set("sendmail_from", $old_from); 
		}
		return true;
	}
	
	//
	//private function fm_sendmail($send_to,$subject,$body,$headers;
	//
	//sends the message using the sendmail functionality
	//	
	function pm_sendmail($send_to,$subject,$body,$headers,$sender){
        if ($sender!=""){
			$sendmail=sprintf("%s -oi -f %s -t", $this->config["sendmail"], $sender);	
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
	//private function fm_encodeheader($str,$position='text');
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

    function pm_encodestring($str,$encoding="base64") {
    	$encoded = "";
        switch(strtolower($encoding)) {
	        case "base64":
	            $encoded=chunk_split(base64_encode($str),76,$this->config["endline"]);
	        break;
	        case "7bit":
	        case "8bit":
	            $encoded = $this->FixEOL($str);
	            if(substr($encoded,-(strlen($this->config["endline"])))!=$this->config["endline"]){
					$encoded.=$this->config["endline"];
				}
	        break;
	        case "binary":
	            $encoded = $str;
	        break;
	        case "quoted-printable":
	              $encoded = $this->EncodeQP($str);
	        break;
	        default:
	        	return false;
	        break;
        };
        return $encoded;
    } 
	  
}
?>