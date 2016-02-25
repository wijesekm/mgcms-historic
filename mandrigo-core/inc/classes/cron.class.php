<?php

/**
 * @file		cron.class.php
 * @author 		Kevin Wijesekera
 * @copyright 	2012
 * @edited		10-8-2012
 
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

class cron{

	private $obj;

	public function __construct(){
		if(!isset($_SERVER['SERVER_SIGNATURE'])){
			$_SERVER['SERVER_SIGNATURE']='';
		}
		$GLOBALS['MG']['PAGE']['VARS']=array(
			'SERVER_NAME'=>$_SERVER['SERVER_NAME'],
			'SERVER_SOFTWARE'=>$_SERVER['SERVER_SOFTWARE'],
			'SERVER_NAME'=>$_SERVER['SERVER_NAME'],
			'DEFAULT_ACT'=>$GLOBALS['MG']['SITE']['DEFAULT_ACT'],
			'SERVER_TZ'=>$GLOBALS['MG']['SITE']['TZ'],
			'URLTYPE'=>$GLOBALS['MG']['SITE']['URLTYPE'],
			'INDEX_NAME'=>$GLOBALS['MG']['SITE']['INDEX_NAME'],
			'LANGUAGE'=>$GLOBALS['MG']['LANG']['NAME'],
			'SERVER_TIME'=>date($GLOBALS['MG']['SITE']['TIME_FORMAT'],$GLOBALS['MG']['SITE']['TIME']),
			'SERVER_DATE'=>date($GLOBALS['MG']['SITE']['DATE_FORMAT'],$GLOBALS['MG']['SITE']['TIME']),
			'COPYRIGHT_YEAR'=>date('o',$GLOBALS['MG']['SITE']['TIME'])
		);
	}
	
	public function cron_generate(){
		$cur_time = date('G',$GLOBALS['MG']['SITE']['TIME']);
		$cur_min = date('i',$GLOBALS['MG']['SITE']['TIME']);
		$cur_day = date('N',$GLOBALS['MG']['SITE']['TIME']);
		
		if($cur_min >= '30'){
			$cur_time .=':30';
		}
		foreach($GLOBALS['MG']['PAGE']['DATA'] as $val){
			//HOOK
			//DAYS
			//TIMES
			$val['DAYS'] = explode('/',$val['DAYS']);
			$val['TIMES'] = explode('/',$val['TIMES']);
			
			if((in_array($cur_day,$d['cron_days']) && in_array($cur_time,$d['cron_times'])) || $_GET['all']){
				if(!$this->cron_hookEval($val['HOOK'])){
					echo 'Could not run hook';
				}
			}
			
		}
		
	}


	private function cron_hookEval($hook){
		if(!$hook){
			return false;
		}
		if(preg_match('/\:\:/',$hook)){
			
			$hook=explode('::',$hook);
			if(!isset($this->obj[$hook[0]])){
				$this->obj[$hook[0]]=true;
			}
			if(!is_object($this->obj[$hook[0]])){
				eval('$this->obj[\''.$hook[0].'\']=new '.$hook[0].'();');
				if(!is_object($this->obj[$hook[0]])){
					trigger_error('(PAGE): Could not create page class: '.$hook[0],E_USER_WARNING);
				}			
			}
			eval('$ret=$this->obj[\''.$hook[0].'\']->'.$hook[1].'();');
			if(!$ret){
				trigger_error('(PAGE): Could not evaluate hook: '.$hook[1],E_USER_WARNING);
			}
		}
		else{
			eval('$ret='.$hook.'();');
			if(!$ret){
				trigger_error('(PAGE): Could not evaluate hook: '.$hook,E_USER_WARNING);
			}
		}
		return $ret;
	}
}