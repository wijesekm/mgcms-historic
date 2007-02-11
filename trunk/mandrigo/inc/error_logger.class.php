<?php
/**********************************************************
    error_logger.class.php
	Last Edited By: Kevin Wijesekera
	Date Last Edited: 02/10/07
	
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

class error_logger{
    //log level defaults
    var $lvl;
    //error log
    var $log;
    //current status of the error logger
    var $status;
    //errors that cause program termination
    var $fatal_type;
    //all error types
    var $type;
    //data format of error logs
    var $format;
    //for xml parsing
   	var $document;
   	var $curr_tag;
   	var $tag_stack;
    
    //
    //constructor function error_logger($lvl1,$lvl2,$format,$type,$fatal_type)
    //
    //Initializes the error logging script
    //
    //INPUTS:
    //$lvl1			-	log non system critical errors (default: )
    //$lvl2			-	log system critical errors (default: )
    //$format		-	report file name format (default: )
    //$type			-	array of error log file names (default: )
    //$fatal_type	-	array of log types that are system critical (default: )
    function error_logger($lvl1,$lvl2,$format,$type,$fatal_type){
        $this->lvl[1]=$lvl1;
        $this->lvl[2]=$lvl2;
        $this->format=$format;
        $this->status["END"]=false;
        $this->status["DISPLAY"]=false;
        $this->type=(is_array($type))?$type:array("sql","script","display","access");
        $this->fatal_type=(is_array($fatal_type))?$fatal_type:array("sql"=>1,"script"=>1);
        for($i=0;$i<count($this->type);$i++){
            $this->log[$this->type[$i]]=array("1");
        }
    }
    
	//#################################
	//
	// PUBLIC FUNCTIONS
	//
	//#################################	
	
    //
    //public function el_adderror($error,$type)
    //
    //Adds an error occurance
    //
    //INPUTS:
    //$error	-	error number (default: )
    //$type		-	type of error (default: )
    function el_adderror($error,$type){
        $this->log["$type"][count($this->log["$type"])]=$error;
        if($this->fatal_type["$type"]){
             $this->status["END"]=true;
        }
        $this->status["DISPLAY"]=true;
    }
    //
    //public function el_getstatus()
    //
    //Adds an error occurance
    //
    //returns the current status of the error logger [2:fatal errors, 1:non fatal errors, 0:no errors]
    function el_getstatus(){
        return ($this->status["DISPLAY"])?(($this->status["END"])?2:1):0;
    }
    
    //
    //public function el_adderror($error,$type)
    //
    //Adds an error occurance
    //
    //INPUTS:
    //$vars		-	array of parse vars (default: )
    //
    //returns error log
    function el_generatereport($vars=array()){
        //loads error log inc from file
        if(!$log_inc=$this->el_init()){
            return $GLOBALS["MANDRIGO"]["ELOG"]["ONE"];
        }
		
		if(!$this->el_getstatus()){
			return false;
		}

		//loads the error logger template
		$tpl=$this->el_loadtemplate($GLOBALS["MANDRIGO"]["CONFIG"]["TEMPLATE_PATH"].TPL_ERROR_LOG);
		
        $report="";
        //goes through each error array in the $log array and generates a report based on what is inside it
        for($i=0;$i<count($this->type);$i++){
            $type=$this->type[$i];
            $len=count($this->log[$this->type[$i]]);
            if($len>1){
                for($j=0;$j<$len;$j++){
                 	if($this->log[$this->type[$i]][$j]>1){
						$report.="[".$this->log[$this->type[$i]][$j]."] ";
					}
                    $report.=$this->el_vparse($vars,$log_inc[$this->type[$i]]["L".$this->log[$this->type[$i]][$j]]);
                    if($this->log[$this->type[$i]][$j]>1){
                        $report.=$GLOBALS["MANDRIGO"]["ELOG"]["BR"];
                    }
                }
            }
        }
        //if use has requested error logging, logs the report
        if(($this->el_getstatus()==1&&$this->lvl[1])||($this->el_getstatus()==2&&$this->lvl[2])){
        //    $this->el_writetofile($report);
        }
        return ereg_replace("{CONTENT}",$report,$tpl);
    }
    
    
	//#################################
	//
	// PRIVATE FUNCTIONS
	//
	//#################################	    
 
    //
    //private function el_adderror($error,$type)
    //
    //Adds an error occurance
    //
    //INPUTS:
    //$vars		-	array of parse vars (default: )
    //$string	-	string to parse (default: )
    //
	//returns parsed string
    function el_vparse($vars,$string){
        $sov=count($vars);
        if(!$sov%2){
            return $string;
        }
        for($i=0;$i<$sov-1;$i+=2){
            $string=ereg_replace("{".$vars[$i]."}",$vars[$i+1],$string);
        }
        $string=eregi_replace("[{]+[a-z0-9_-]+[}]","",$string);
        return $string;
    }
    
    //
    //private function el_init()
    //
    //Sets up log_inc array
    //
    //returns array of all log messages
    function el_init(){
        $log_inc="";
        for($i=0;$i<count($this->type);$i++){
            $log_inc[$this->type[$i]]=$this->el_openarrayassoc($GLOBALS["MANDRIGO"]["CONFIG"]["ROOT_PATH"]."log_config/".$this->type[$i].".".XML_EXT);
            if(!$log_inc[$this->type[$i]]){
                return false;
            }
        }
   		$this->document="";
   		$this->curr_tag="";
   		$this->tag_stack="";
        return $log_inc;
    }
    
    //
    //private function el_openarrayassoc($path)
    //
    //Creates an array for a given log init file
    //
    //INPUTS:
    //$path	-	path to error log file (default: )
    //
    //returns array of all messages in log init file
    function el_openarrayassoc($path){
        
        $this->el_xmlparse($path);
        $data=$this->document["ERROR_LOG"][0]["MSG"];
        $soq=count($data);
        $log=array();
		for($i=0;$i<$soq;$i++){
			$log=$this->el_addarray($log,$data[$i]["ID"][0]["data"],$data[$i]["VALUE"][0]["data"]);
		}
		
        return $log;
    }
    
    //
    //private function el_openarrayassoc($path)
    //
    //Creates an array for a given log init file
    //
    //INPUTS:
    //$array	-	array to merge to (default: )
    //$key		-	key (default: )
    //$val		-	value (default: )
    //
    //returns merged array
    function el_addarray($array, $key, $val){
        $tmp = array("L$key"=>"$val");
        $array = array_merge($array, $tmp);
        return $array;
    }
    
    //
    //private function el_writetofile($report)
    //
    //Write the error log to a file
    //
    //INPUTS:
    //$report	-	error log (default: )
    function el_writetofile($report){
        $time = date("m-d-Y")." at ".date("h:i:s")."\n";

        if($GLOBALS["MANDRIGO"]["CONFIG"]["DEBUG_MODE"]){
            if(!is_file($GLOBALS["MANDRIGO"]["CONFIG"]["LOG_PATH"]."log_".date($this->format).".log")){
                $f=fopen($GLOBALS["MANDRIGO"]["CONFIG"]["LOG_PATH"]."log_".date($this->format).".log","x");
            }
            else{
				$f=fopen($GLOBALS["MANDRIGO"]["CONFIG"]["LOG_PATH"]."log_".date($this->format).".log","a");
			}
        }
        else{
         	if(!is_file($GLOBALS["MANDRIGO"]["CONFIG"]["LOG_PATH"]."log_".date($this->format).".log")){
				if(!(@$f=fopen($GLOBALS["MANDRIGO"]["CONFIG"]["LOG_PATH"]."log_".date($this->format).".log","x"))){
                    die($GLOBALS["HTML"]["EHEAD"].$GLOBALS["LANGUAGE"]["ETITLE"].$GLOBALS["HTML"]["EBODY"].
                        $GLOBALS["MANDRIGO"]["ELOG"]["THREE"].$GLOBALS["HTML"]["EEND"]);				
				}	
			}
			else{
				if(!(@$f=fopen($GLOBALS["MANDRIGO"]["CONFIG"]["LOG_PATH"]."log_".date($this->format).".log","a"))){
                    die($GLOBALS["HTML"]["EHEAD"].$GLOBALS["LANGUAGE"]["ETITLE"].$GLOBALS["HTML"]["EBODY"].
                        $GLOBALS["MANDRIGO"]["ELOG"]["THREE"].$GLOBALS["HTML"]["EEND"]);
                }			
			}
        }
        fwrite($f,$time);
        fwrite($f,"----------------------\n");
        fwrite($f,strip_tags($report)."\n\n");
        fclose($f);
    }
     
    //
    //private function el_loadtemplate($path)
    //
    //Loads a template file
    //
    //INPUTS:
    //$path	-	path to template (default: )   
    //
    //returns the template
    function el_loadtemplate($path){

        //loads template to the $tpl var
        if($GLOBALS["MANDRIGO"]["CONFIG"]["DEBUG_MODE"]){
            $f=fopen($path,"r");
        }
        else{
            if(!(@$f=fopen($path,"r"))){
                return $GLOBALS["MANDRIGO"]["ELOG"]["TWO"];
            }
        }
        $tpl="";
        while(!feof($f)){
            $tpl.=fgets($f);
        }
        fclose($f);
		return $tpl;	
	}
	
    //
    //private function el_xmlparse($path)
    //
    //Loads and parses an xml file
    //
    //INPUTS:
    //$path	-	path to the xml file (default: )   
    //
    //returns true on success or false on fail
	function el_xmlparse($path){
		$parser=xml_parser_create();
	 	$this->document = array();
		$this->currTag =& $this->document;
		$this->tagStack = array();
	    xml_set_object($parser, $this);
	    xml_set_character_data_handler($parser, 'el_datahandler');
	    xml_set_element_handler($parser, 'el_starthandler', 'el_endhandler');
	   	if(!($fp = fopen($path, "r"))){
	           return false;
	    }
		while($data = fread($fp, 4096)){
	    	if(!xml_parse($parser, $data, feof($fp))){
	        	return false;
			}
		}
	    fclose($fp);
	   	xml_parser_free($parser);
	   	return true;
	}
	
    //
    //private function el_starthandler($parser, $name, $attribs)
    //
    //Required funtion to parse the beginning of each xml tag
    //
    //INPUTS:
    //$parser	-	xml parser(default: )  
	//$name		-	xml tag name (default: ) 
    //$attribs	-	xml tag attributes (default: )
	function el_starthandler($parser, $name, $attribs){
	    if(!isset($this->currTag[$name])){
			$this->currTag[$name] = array();
		}
	      
	    $newTag = array();
	    if(!empty($attribs)){
			$newTag['attr'] = $attribs;	
		}
	    array_push($this->currTag[$name], $newTag);
	      
		$t =& $this->currTag[$name];
		$this->currTag =& $t[count($t)-1];
		array_push($this->tagStack, $name);
	}

    //
    //private function el_datahandler($parser, $data)
    //
    //Required funtion to parse the middle of each xml tag
    //
    //INPUTS:
    //$parser	-	xml parser(default: )  
	//$data		-	xml tag data (default: ) 	  
	function el_datahandler($parser, $data){
	    $data = trim($data); 
	    if(!empty($data)){
	        if(isset($this->currTag['data'])){
				$this->currTag['data'] .= $data;	
			}
	        else{
				$this->currTag['data'] = $data;
			}
	    }
	}
	
    //
    //private function el_endhandler($parser, $name)
    //
    //Required funtion to parse the end of each xml tag
    //
    //INPUTS:
    //$parser	-	xml parser(default: )  
	//$name		-	xml tag name (default: )  
	function el_endhandler($parser, $name){
	    $this->currTag =& $this->document;
	    array_pop($this->tagStack);
	      
	    for($i = 0; $i < count($this->tagStack); $i++){
	        $t =& $this->currTag[$this->tagStack[$i]];
	        $this->currTag =& $t[count($t)-1];
	    }
	}
}
