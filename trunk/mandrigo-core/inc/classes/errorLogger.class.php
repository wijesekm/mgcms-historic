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
	private $errorTypes;
	private $errors;
	private $userErrors;

	/**
	* Constants
	*/
	const TEMPLATE_NAME		= 'error_log.tpl';
	const USER_ERRORS		= 'user-errors.log';
	const PHP_ERRORS		= 'php-errors.log';
	
	/**
	* Construct and Destruction functions
	*/
	public function __construct(){
		$this->errorTypes = array (
                E_ERROR              => 'Error',
                E_WARNING            => 'Warning',
                E_PARSE              => 'Parsing Error',
                E_NOTICE             => 'Notice',
                E_CORE_ERROR         => 'Core Error',
                E_CORE_WARNING       => 'Core Warning',
                E_COMPILE_ERROR      => 'Compile Error',
                E_COMPILE_WARNING    => 'Compile Warning',
                E_USER_ERROR         => 'User Error',
                E_USER_WARNING       => 'User Warning',
                E_USER_NOTICE        => 'User Notice',
                E_STRICT             => 'Runtime Notice',
                E_RECOVERABLE_ERROR  => 'Catchable Fatal Error'
                );
        $this->userErrors = array(E_USER_ERROR, E_USER_WARNING, E_USER_NOTICE);
        $this->errors=array();
	}
	public function __destruct(){
		$this->errors=false;
		$this->userErrors=false;
		$this->errorTypes=false;
	}
	
	/**
	* Public Functions
	*/
		
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
	 	$err = "<errorentry>\n";
	    $err .= "\t<datetime>" . $dt . "</datetime>\n";
	    $err .= "\t<errornum>" . $errno . "</errornum>\n";
	    $err .= "\t<errortype>" . $this->errorTypes[$errno] . "</errortype>\n";
		$err .= "\t<errormsg>" . $errmsg . "</errormsg>\n";
		$err .= "\t<scriptname>" . $filename . "</scriptname>\n";
		$err .= "\t<scriptlinenum>" . $linenum . "</scriptlinenum>\n";
		$err .= "</errorentry>\n\n";
		if(in_array($errno,$this->userErrors)){
			$this->el_logRotate($GLOBALS['MG']['CFG']['PATH']['LOG'].errorLogger::USER_ERRORS);
			$this->errors[]=array($dt, $errno, $errmsg, $filename, $linenum);
			return @error_log($err, 3,$GLOBALS['MG']['CFG']['PATH']['LOG'].errorLogger::USER_ERRORS);		
		}
		else{
			$this->el_logRotate($GLOBALS['MG']['CFG']['PATH']['LOG'].errorLogger::PHP_ERRORS);
			return @error_log($err, 3, $GLOBALS['MG']['CFG']['PATH']['LOG'].errorLogger::PHP_ERRORS);
		}
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
			$new_name=ereg_replace('\.log','-'.$dt.'.log',$fname);
			if(!@copy($fname,$new_name)){
				return false;
			}
			return @unlink($fname);
		}
		return true;
	}

}