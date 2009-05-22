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

class plinks{
	
	var $links_to_repl;
	var $new_links;
	
	public function plinks_addElemToExternal($content){
		$this->plinks_getAllLinks($content);
		$tpl=new template();
		$soq=count($this->new_links);
		$repl=array();
		for($i=0;$i<$soq;$i++){
			$parse=array();
			$parse['ATTR']='';
			foreach($this->new_links[$i] as $key=>$value){
				if($key!='link_name'&&$key){
					$parse['ATTR'].=$key.';'.$value.';';
				}
				$parse[strtoupper($key)]=$value;
				
			}
			$tpl->tpl_load($GLOBALS['MG']['CFG']['PATH']['TPL'].$GLOBALS['MG']['LANG']['NAME'].'/plinks.tpl','plinks_repl');
			$tpl->tpl_parse($parse,'plinks_repl',2,true);
			$repl[$i]=$tpl->tpl_return('plinks_repl');
		}
		$this->new_links=$repl;
		return $this->plinks_replaceAllLinks($content);
	}
	
	private function plinks_getAllLinks($content){
		$this->links_array=array();
		preg_match_all ('!<a(.*?)</a>!is', $content, $links);
		$links=preg_replace('/>/',' ',$links[1]);
		$this->links_to_repl=$links[0];
		$soq=count($links);
		for($i=0;$i<$soq;$i++){
			$link=split(' ',$links[$i]);
			$this->new_links[$i]=array();
			foreach($link as $value){
				if($value){
					if(!eregi('=',$value)){
						$this->new_links[$i]['link_name'].=$value.' ';
					}
					else{
						$tmp=split('=',$value);
						$this->new_links[$i][trim($tmp[0])]=trim(ereg_replace('["\']','',$tmp[1]));
					}
				}
			}
		}
	}
	
	private function plinks_linkArrayToStr(){
		$soq=count($this->new_links);
		$repl=array();
		for($i=0;$i<$soq;$i++){
			$repl[$i]='<a';
			$link_end='</a>';
			foreach($this->new_links[$i] as $key=>$value){
				if($key=='link_name'){
					$link_end='>'.trim($value).$link_end;
				}
				else{
					$repl[$i].=' '.$key.'="'.$value.'"';
				}
			}
			$repl[$i]=$link_end;
		}
		$this->new_links=$repl;		
	}
	
	private function plinks_replaceAllLinks($content){
		$soq=count($this->new_links);
		for($i=0;$i<$soq;$i++){
			$this->links_to_repl[$i]=preg_quote($this->links_to_repl[$i]);
			$content= ereg_replace($this->links_to_repl[$i],$this->new_links[$i],$content);			
		}
		return $content;
	}
}