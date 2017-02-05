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
	private $fatalErrors = array();
    private $errors = array();

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
                E_RECOVERABLE_ERROR  => 'Catchable_Fatal_Error',
                E_DEPRECATED         => 'Deprecated'
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
        if($errno == E_ERROR || $errno == E_CORE_ERROR || $errno == E_USER_ERROR){
            $this->fatalErrors[] = array(
                $dt,$errno,$this->errorTypes[$errno],(isset($_SERVER['REQUEST_URI']))?$_SERVER['REQUEST_URI']:'',$errmsg,$filename,$linenum
            );
        }
        $this->errors[] = array(

            $this->errorTypes[$errno],$errmsg,$filename,$linenum
        );

	 	$err = "<error>\r\n";
	    $err .= "\t<datetime>" . $dt . "</datetime>\r\n";
        if(isset($GLOBALS['MG']['USER']['UID'])){
            $err .= "\t<user>" . $GLOBALS['MG']['USER']['UID'] . "</user>\r\n";
        }
        if(isset($_POST)){
            $err .= "\t<post>" . var_export($_POST,true) . "</post>\r\n";
        }
	    $err .= "\t<errornum>" . $errno . "</errornum>\r\n";
	    $err .= "\t<errortype>" . $this->errorTypes[$errno] . "</errortype>\r\n";
	    $err .= "\t<erroruri>".(isset($_SERVER['REQUEST_URI']))?$_SERVER['REQUEST_URI']:''."</erroruri>\r\n";
		$err .= "\t<errormsg>" . $errmsg . "</errormsg>\r\n";
		$err .= "\t<scriptname>" . $filename . "</scriptname>\r\n";
		$err .= "\t<scriptlinenum>" . $linenum . "</scriptlinenum>\r\n";
		$err .= "</error>\r\n";
		$this->el_logRotate($GLOBALS['MG']['CFG']['PATH']['LOG'].$this->errorTypes[$errno].'.log');
		return @error_log($err, 3,$GLOBALS['MG']['CFG']['PATH']['LOG'].$this->errorTypes[$errno].'.log');
	}

	/**
	* el_checkFatal()
	*
	* Checks for the existance of fatal errors and stops execution if one is detected.
    * If DISPFATAL config is set it will also display information on the fatal error.
	*
	*/
	public function el_hasFatalErrors(){
		return count($this->fatalErrors) != 0;
	}

    public function el_checkFatal(){

        if(count($this->fatalErrors) == 0){
            return false;
        }
        $GLOBALS['MG']['SQL']->sql_close();
        echo '<html><head><style type="text/css">
            .title{font-size: 26px; background: #FF6666; padding: 10px; }
            .err{ margin-top: 10px; padding: 10px; border: 1px dashed #ADADAD; background: #EDEDED; }
            .ts,.einfo{ margin-right: 7px; }
        </style></head><body>';
        echo '<div class="title">This application encountered fatal errors and was unable to process your request</div>';
        if($GLOBALS['MG']['CFG']['ERRORLOGGER']['DISPFATAL']){
            foreach($this->fatalErrors as $key=>$err){
                echo '<div class="err">';
                //echo '<span class="ts">'.$err[0].'</span>';
                echo '<span class="einfo">'.$err[4].' ('.$err[2].')</span><br/>';
                echo '<span class="dta">'.$err[5].' ('.$err[6].')</span>';
                echo '</div>';
            }
        }
        echo '</body></html>';
        die();
    }

	/**
	* el_setErrorVar()
	*
	* Sets the ERROR_LIST var
	*
	*/
    public function el_setErrorVar(){
       if(count($this->errors) > 0 && $GLOBALS['MG']['CFG']['ERRORLOGGER']['DISPERRORS']){
            $GLOBALS['MG']['PAGE']['VARS']['ERROR_LIST']=mg_jsonEncode($this->errors,true);
       }
       else{
            $GLOBALS['MG']['PAGE']['VARS']['ERROR_LIST'] = '{}';
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
			$new_name=preg_replace('/\.log/i','-'.$dt.'.log',$fname);
			if(!@copy($fname,$new_name)){
				return false;
			}
			return @unlink($fname);
		}
		return true;
	}

}