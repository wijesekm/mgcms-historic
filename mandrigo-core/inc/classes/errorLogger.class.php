<?php

/**
 * @file		errorLogger.class.php
 * @author 		Kevin Wijesekera
 * @copyright 	2008
 * @edited		6-8-2008

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

class errorLogger{
	
	/**
	* Variables
	*/
	public $errorTypes;
	
	/**
	* Construct and Destruction functions
	*/
	public function __construct(){
		$this->errorTypes = array (
                E_ERROR              => 'Error',
                E_WARNING            => 'Warning',
                E_PARSE              => 'Parsing_Error',
                E_NOTICE             => 'Notice',
                E_CORE_ERROR         => 'Core_Error',
                E_CORE_WARNING       => 'Core_Warning',
                E_COMPILE_ERROR      => 'Compile_Error',
                E_COMPILE_WARNING    => 'Compile_Warning',
                E_USER_ERROR         => 'User_Error',
                E_USER_WARNING       => 'User_Warning',
                E_USER_NOTICE        => 'User_Notice',
                E_STRICT             => 'Runtime_Notice',
                E_RECOVERABLE_ERROR  => 'Catchable_Fatal_Error'
                );
        foreach($this->errorTypes as $key => $value){
			if(!is_file($GLOBALS['MG']['CFG']['PATH']['LOG'].$value.'.log')){
				@touch($GLOBALS['MG']['CFG']['PATH']['LOG'].$value.'.log');
			}
		}
	}
	public function __destruct(){
		$this->userErrors=false;
		$this->errorTypes=false;
	}
	
	/**
	* Public Functions
	*/
	public function el_parseErrorFile($file){
		if(!$f=fopen($file,'r')){
			trigger_error('(ERRORLOGGER): Could not open error log for parsing!',E_USER_ERROR);
			return false;
		}
		$log='';
		$line='';
		$i=0;
		while(!feof($f)){
			$line=fgets($f);
			if(preg_match('/<error>/',$line)){
				$log[$i]=array();
			}
			else if(preg_match('/<datetime>/',$line)){
				$log[$i]['date_time']=trim(preg_replace('/<datetime>(.*)<\/datetime>/','\\1',$line));
			}
			else if(preg_match('/<errornum>/',$line)){
				$log[$i]['error_number']=trim(preg_replace('/<errornum>(.*)<\/errornum>/','\\1',$line));
			}
			else if(preg_match('/<errortype>/',$line)){
				$log[$i]['error_type']=trim(preg_replace('/<errortype>(.*)<\/errortype>/','\\1',$line));
			}
			else if(preg_match('/<erroruri>/',$line)){
				$log[$i]['error_uri']=trim(preg_replace('/<erroruri>(.*)<\/erroruri>/','\\1',$line));
			}
			else if(preg_match('/<errormsg>/',$line)){
				$log[$i]['error_msg']=trim(preg_replace('/<errormsg>(.*)<\/errormsg>/','\\1',$line));
			}
			else if(preg_match('/<scriptname>/',$line)){
				$log[$i]['script_name']=trim(preg_replace('/<scriptname>(.*)<\/scriptname>/','\\1',$line));
			}
			else if(preg_match('/<scriptlinenum>/',$line)){
				$log[$i]['script_line_num']=trim(preg_replace('/<scriptlinenum>(.*)<\/scriptlinenum>/','\\1',$line));
			}
			else if(preg_match('/<\/error>/',$line)){
				$i++;
			}
		}
		return $log;
	}
	/**
	* el_addError($errno, $errmsg, $filename, $linenum, $vars)
	*
	* Adds an error to be logged
	*
	* INPUTS:
	* $errno	-	Error number (int)
	* $errmsg	-	Error message (string)
	* $filename	-	File name (string)
	* $linenum	-	Line Number (int)
	* $vars		-	Current System Variables (array)
	*
	* OUTPUTS:
	* true on success, false on fail
	*/	
	public function el_addError($errno, $errmsg, $filename, $linenum, $vars){
		$dt = @date("Y-m-d H:i:s (T)");
	 	$err = "<error>\r\n";
	    $err .= "\t<datetime>" . $dt . "</datetime>\r\n";
	    $err .= "\t<errornum>" . $errno . "</errornum>\r\n";
	    $err .= "\t<errortype>" . $this->errorTypes[$errno] . "</errortype>\r\n";
	    $err .= "\t<erroruri>".$_SERVER['REQUEST_URI']."</erroruri>\r\n";
		$err .= "\t<errormsg>" . $errmsg . "</errormsg>\r\n";
		$err .= "\t<scriptname>" . $filename . "</scriptname>\r\n";
		$err .= "\t<scriptlinenum>" . $linenum . "</scriptlinenum>\r\n";
		$err .= "</error>\r\n";
		$this->el_logRotate($GLOBALS['MG']['CFG']['PATH']['LOG'].$this->errorTypes[$errno].'.log');
		return @error_log($err, 3,$GLOBALS['MG']['CFG']['PATH']['LOG'].$this->errorTypes[$errno].'.log');		
	}
	
	/**
	* Private Functions
	*/
	
	/**
	* el_logRotate($fname='error')
	*
	* Rotates the error log based on file size
	*
	* INPUTS:
	* $fname	-	Filename (string)
	*
	* OUTPUTS:
	* true on success, false on fail
	*/	

	private function el_logRotate($fname){
		$dt = date("Y-m-d");
		if(!is_file($fname)){
			touch($fname);
		}
		if(@filesize($fname)>$GLOBALS['MG']['CFG']['ERRORLOGGER']['SIZE']){
			$new_name=preg_replace('/\.log/i','-'.$dt.'.log',$fname);
			if(!@copy($fname,$new_name)){
				return false;
			}
			return @unlink($fname);
		}
		return true;
	}

}