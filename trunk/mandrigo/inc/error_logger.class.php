<?php
/**********************************************************
    error_logger.class.php
	Last Edited By: Kevin Wijesekera
	Date Last Edited: 11/03/05

	Copyright (C) 2005  Kevin Wijesekera

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
    die("<html><head>
            <title>Forbidden</title>
        </head><body>
            <h1>Forbidden</h1><hr width=\"300\" align=\"left\"/>\n<p>You do not have permission to access this file directly.</p>
        </html></body>");
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
    
    //resets the error logger and sets default settings
    function error_logger($lvl1,$lvl2,$format){
        $this->lvl[1]=$lvl1;
        $this->lvl[2]=$lvl2;
        $this->format=$format;
        $this->status["END"]=false;
        $this->status["DISPLAY"]=false;
        $this->type=array("sql","script","display","access");
        $this->fatal_type=array("sql"=>1,"script"=>1);
        for($i=0;$i<count($this->type);$i++){
            $this->log[$this->type[$i]]=array("0");
        }
    }
    //adds an error to the log array assuming that $error is in the $log_inc array and that $type is a part of that error array
    //if these conditions are not met it will show up as blank in the error log
    function add_error($error,$type){
        $this->log["$type"][count($this->log["$type"])]=$error;
        if($this->fatal_type["$type"]){
             $this->status["END"]=true;
        }
        $this->status["DISPLAY"]=true;
    }
    //returns the current status of the error logger: 2 - fatal errors, 1 - non fatal errors, 0 - no errors
    function get_status(){
        return ($this->status["DISPLAY"])?(($this->status["END"])?2:1):0;
    }
    
    //generates an error report and displays/logs it
    function generate_report(){
        //loads error log inc from file
        if(!$log_inc=$this->error_log_init()){
            return $GLOBALS["ELOG"]["ONE"];
        }
        //loads template to the $tpl var
        if($GLOBALS["MANDRIGO_CONFIG"]["DEBUG_MODE"]){
            $f=fopen($GLOBALS["MANDRIGO_CONFIG"]["TEMPLATE_PATH"].TPL_ERROR_LOG,"r");
        }
        else{
            if(!(@$f=fopen($GLOBALS["MANDRIGO_CONFIG"]["TEMPLATE_PATH"]."error_log.tpl","r"))){
                return $GLOBALS["ELOG"]["TWO"]];
            }
        }
        $tpl="";
        while(!feof($f)){
            $tpl.=fgets($f);
        }
        fclose($f);
        
        $report="";
        
        //goes through each error array in the $log array and generates a report based on what is inside it
        for($i=0;$i<count($this->type);$i++){
            $type=$this->type[$i];
            $len=count($this->log[$this->type[$i]]);
            if($len!=1){
                for($j=0;$j<$len;$j++){
                    $report.=$log_inc[$this->type[$i]]["l_".$this->log[$this->type[$i]][$j]];
                    if($this->log[$this->type[$i]][$j]!=0){
                        $report.=$GLOBALS["HTML"]["BR"].$GLOBALS["HTML"]["BR"];
                    }
                }
            }
        }
        
        //if use has requested error logging, logs the report
        if(($this->get_status()==1&&$this->lvl[1])||($this->get_status()==2&&$this->lvl[2])){
            $this->write_to_file($report);
        }
        return ereg_replace("{CONTENT}",$report,$tpl);
    }
    
    //loads log_inc data from the init files
    //to make a new error type simply place a file with that name in the directory
    //and add the name to the type array and/or fatal_type array if necessary
    //error log files should be of this format ROOT_PATH/log_config/some_name.inc.log
    function error_log_init(){
        $log_inc="";
        for($i=0;$i<count($this->type);$i++){
            $log_inc[$this->type[$i]]=$this->open_array_assoc($GLOBALS["MANDRIGO_CONFIG"]["ROOT_PATH"]."log_config/".$this->type[$i].".inc.log");
            if(!$log_inc[$this->type[$i]]){
                return false;
            }
        }
        return $log_inc;
    }
    
    //forms an associative array based on the error log file
    //ex array("error_number","error_message")
    function open_array_assoc($file_name){
        $deliminator="&split;";
        $file="";
        if($GLOBALS["MANDRIGO_CONFIG"]["DEBUG_MODE"]){
            $f=fopen($file_name,"r");
        }
        else{
            if(!(@$f=fopen($file_name,"r"))){
            return false;
            }
        }
		while(!feof($f)){
			$file.=fgets($f);
        }
		fclose($f);
        $raw_data = explode($deliminator,$file);
        $data = array();
        $raw_data_length=count($raw_data);
        if($raw_data_length%2){
            return false;
        }
        for($i=0; $i< $raw_data_length; $i=$i+2){
            $data = $this->add_array($data,$raw_data[$i],$raw_data[$i+1]);
        }
        return $data;
    }
    //simple function to add two arrays
    function add_array($array, $key, $val){
        $tmp = array("l_$key"=>"$val");
        $array = array_merge($array, $tmp);
        return $array;
    }
    //logs the error report to a file
    //log files are located in LOG_PATH/log_timestamp.log
    function write_to_file($report){
        $time = date("m-d-Y")." at ".date("h:i:s")."\n";
        if($GLOBALS["MANDRIGO_CONFIG"]["DEBUG_MODE"]){
            if(!($f=fopen($GLOBALS["MANDRIGO_CONFIG"]["LOG_PATH"]."log_".date($this->format).".log","x"))){
                $f=fopen($GLOBALS["MANDRIGO_CONFIG"]["LOG_PATH"]."log_".date($this->format).".log","a");
            }
        }
        else{
            if(!(@$f=fopen($GLOBALS["MANDRIGO_CONFIG"]["LOG_PATH"]."log_".date($this->format).".log","x"))){
                if(!(@$f=fopen($GLOBALS["MANDRIGO_CONFIG"]["LOG_PATH"]."log_".date($this->format).".log","a"))){
                    die($GLOBALS["HTML"]["EHEAD"].$GLOBALS["LANGUAGE"]["ETITLE"].$GLOBALS["HTML"]["EBODY"].
                        $GLOBALS["ELOG"]["THREE"].$GLOBALS["HTML"]["EEND"]);
                }
            }
        }
        fwrite($f,$time);
        fwrite($f,strip_tags($report)."\n");
        fclose($f);
    }
}
?>
