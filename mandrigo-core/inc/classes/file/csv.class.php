<?php

/**
 * @file                csv.class.php
 * @author              Kevin Wijesekera
 * @copyright   		2009
 * @edited              5-06-2009
 
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

class csv{
	
	private $csv_array=array();
	
	function csv_addLine($values){
		$this->csv_array[]=$values;
	}
	
	function csv_export($display=true,$file=false,$delimiter=',',$enclosure='"'){
		$delimiter_esc = preg_quote($delimiter, '/');
    	$enclosure_esc = preg_quote($enclosure, '/'); 
		$csv='';
		foreach($this->csv_array as $subArray){
			$soq=count($subArray);
			for($i=0;$i<$soq;$i++){
				if(preg_match("/(?:${delimiter_esc}|${enclosure_esc}|\s)/", $subArray[$i])){
					$subArray[$i]=$enclosure . str_replace($enclosure, $enclosure . $enclosure, $subArray[$i]) . $enclosure;
				}				
			}
			$csv.=implode($delimiter,$subArray)."\n";
		}
		if($file){
			if(!$f=fopen($file,'w')){
				trigger_error('(CSV): Could not open csv file for writing: '.$file,E_USER_ERROR);
				return false;
			}
			fwrite($f,$csv);
			fclose($f);
			return true;
		}
		if($display){
			$GLOBALS['MG']['LANG']['CONTENT_TYPE']='text/csv';
			$GLOBALS['MG']['LANG']['PRAGMA']='no-cache';
			$GLOBALS['MG']['LANG']['CACHE_CONTROL']='no-cache, must-revalidate';
			$GLOBALS['MG']['LANG']['CONTENT_DISPOSITION']='attachment; filename="output.csv" )';
			$GLOBALS['MG']['PAGE']['NOSITETPL']=true;
		}
		return $csv;
	}
}
