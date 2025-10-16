<?php

/**
 * @file                parser.class.php
 * @author              Kevin Wijesekera
 * @copyright   		2008
 * @edited              9-29-2008

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

class parser{

	const P_CODE_START 	= '<!--TPL_CODE_START-->';
	const P_CODE_END	= '<!--TPL_CODE_END-->';

	public function p_vparse($vars,$text){
	    foreach($vars as $key=>$val){
	        if(!$val){
	            $val = '';
	        }
	        if(preg_match('/{'.$key.'}/',$text)){
	            $text=str_replace('{'.$key.'}',$val,$text);
	        }
	    }
		return $text;
	}

	public function p_lparse($lang,$text){
	    foreach($lang as $key=>$val){
	        if(!$val){
	            $val = '';
	        }
	        if(preg_match('/ACRO:/',$key)&&preg_match('/{'.$key.'}/',$text)){
	            $tmp=explode(':',$key);
	            $text=str_replace('{ACRO:('.$tmp[1].')}',$GLOBALS['MG']['LANG']['ACRO'],$text);
	            $text=str_replace('{AP_ACROVALUE}',$lang[$key],$text);
	        }
	        else if(preg_match('/{LANG:'.$key.'}/',$text)){
	            $text=str_replace('{LANG:'.$key.'}',$val,$text);
	        }
	    }
		return $text;
	}

	public function p_phpcompile($section){
		if(!preg_match('/'.parser::P_CODE_START.'/',$section)||!preg_match('/'.parser::P_CODE_END.'/',$section)){
			return $section;
		}
		$section=explode(parser::P_CODE_START,$section);
		$ssoc=count($section);
		for($cco=0;$cco<$ssoc;$cco++){
			if(preg_match('/'.parser::P_CODE_END.'/',$section[$cco])){
				$section_split=explode(parser::P_CODE_END,$section[$cco]);
				$retvar='';
				if(!eval($section_split[0])){
					trigger_error('(TEMPLATE): Compile Error or return value not specified! '.$section_split[0],E_USER_WARNING);
				}

				$section[$cco]=$retvar.$section_split[1];

			}
		}
		return implode('',$section);
	}

	public function p_runCustomParsers($text,$hooks){
		$hook= preg_replace('/(.*)parser](.*)/','\\2',$hooks);
		$hook=explode('==>',$hook);
		$hook=explode(';',$hook[0]);
		foreach($hook as $value){
			$test=$this->p_hookEval($value,$text);
			if(!$test){
				return $text;
			}
			$text=$test;
		}
		return $text;
	}

	public function p_changeVarsToStore($string){
		$string=preg_replace("/\[TPL_CODE_S\]/","<!--TPL_CODE_START-->",$string);
		$string=preg_replace("/\[TPL_CODE_E\]/","<!--TPL_CODE_END-->",$string);
		preg_match_all("/\[VAR:+([a-z0-9:-_])+\]/i",$string,$array);
		$search=$array[0];
		$replace=preg_replace(array('/\[VAR:/','/\]/'),array('{','}'),$array[0]);
		$search=preg_replace(array('/\[/','/\]/'),array('',''),$array[0]);
		$soq=count($search);
		for($i=0;$i<$soq;$i++){
			$search[$i]='/\['.$search[$i].'\]/';
		}
		return preg_replace($search,$replace,$string);
	}

	public function p_changeVarsToDisplay($string){
		$string=preg_replace("/<!--TPL_CODE_START-->/","[TPL_CODE_S]",$string);
		$string=preg_replace("/<!--TPL_CODE_END-->/","[TPL_CODE_E]",$string);
		preg_match_all("/{+([a-z0-9:-_])+}/i",$string,$array);
		$search=$array[0];
		$replace=preg_replace(array('/{/','/}/'),array('[VAR:',']'),$array[0]);
		$soq=count($search);
		for($i=0;$i<$soq;$i++){
			$search[$i]='/'.$search[$i].'/';
		}
		return preg_replace($search,$replace,$string);
	}

	private function p_hookEval($hook,$text){
		if(!$hook){
			return false;
		}
		if(preg_match('/::/',$hook)){
			$hook=explode('::',$hook);
			mginit_loadCustomPackages(array($hook[0]));
			eval('$obj=new '.$hook[0].'();');
			if(!is_object($obj)){
				trigger_error('(PARSER): Could not create class: '.$hook[0],E_USER_WARNING);
			}
			eval('$ret=$obj->'.$hook[1].'($text);');
			if(!$ret){
				trigger_error('(PARSER): Could not evaluate hook: '.$hook[1],E_USER_WARNING);
			}
		}
		else{
			eval('$ret='.$hook.'($text);');
			if(!$ret){
				trigger_error('(PARSER): Could not evaluate hook: '.$hook,E_USER_WARNING);
			}
		}
		return $ret;
	}
}