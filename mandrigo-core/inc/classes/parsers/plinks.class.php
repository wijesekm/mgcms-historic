<?php

/**
 * @file                parser.class.php
 * @author              Kevin Wijesekera
 * @copyright   		2008
 * @edited              8-24-2009
 
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
	var $cfg;
	
	public function __construct(){
		$GLOBALS['MG']['PAGE']['VARS']['PLINK_REPLACE']='';
		$c=array(array(false,array(DB_AND),'pkg_name','=','plinks'),array(false,false,'page_path','=','*'));
		$dta=$GLOBALS['MG']['SQL']->sql_fetchArray(array(TABLE_PREFIX.'packageconf'),false,$c);
		for($i=0;$i<$dta['count'];$i++){
			$this->cfg[(string)$dta[$i]['var_name']]=(string)$dta[$i]['var_value'];
		}
	}
	
	public function plinks_addElemToExternal($content){
		$this->plinks_getAllLinks($content);
		$tpl=new template();
		$soq=count($this->new_links);
		$repl=array();
		for($i=0;$i<$soq;$i++){
			$url=parse_url($this->new_links[$i]['href']);
			$url2=$this->cfg['domain'];
			$is_match=true;
			if(isset($url['host'])){
				$url['host']=explode('.',$url['host']);
				$url2=explode('.',$url2);
				$ucount=count($url['host']);
				$start=$ucount-count($url2);
				$k=0;
				for($j=$start;$j<$ucount;$j++){
					if($url['host'][$j]!=$url2[$k]){
						$is_match=false;
					}
					$k++;
				}
			}
			if(isset($url['host'])&&!$is_match){
				$parse=array();
				$parse['ATTR']='';
				foreach($this->new_links[$i] as $key=>$value){
					if($key!='link_name'&&$key){
						$parse['ATTR'].=$key.';'.$value.';';
					}
					$parse[strtoupper($key)]=$value;
				}
				$tpl->tpl_load($GLOBALS['MG']['CFG']['PATH']['TPL'].$GLOBALS['MG']['LANG']['NAME'].'/plinks.tpl',array('plinks_repl','plinks_storeinvar'));
				$tpl->tpl_parse($parse,template::TPL_ALL,2,true);
				$repl[$i]=$tpl->tpl_return('plinks_repl');
				$GLOBALS['MG']['PAGE']['VARS']['PLINK_REPLACE'].=$tpl->tpl_return('plinks_storeinvar');			
			}
		}
		$this->new_links=$repl;
		return $this->plinks_replaceAllLinks($content);
	}
	
	private function plinks_getAllLinks($content){
		$this->links_array=array();
		preg_match_all ('!<a(.*?)</a>!is', $content, $links);
		$this->links_to_repl=$links[0];
		$links=$links[1];
		$soq=count($links);
		for($i=0;$i<$soq;$i++){
			$link=split('>',$links[$i]);
			$this->new_links[$i]=array();
			$this->new_links[$i]['link_name']=trim(implode('>',array_slice($link,1)));
			$link=split('"',$link[0]);
			$soq2=count($link);
			for($j=0;$j<$soq2;$j++){
				if($link[$j]){
						$key=ereg_replace('=','',$link[$j]);
						$j++;
						$this->new_links[$i][trim($key)]=trim($link[$j]);
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
		$keys=array_keys($this->new_links);
		for($i=0;$i<$soq;$i++){
			if($this->new_links[$keys[$i]]){
				$this->links_to_repl[$keys[$i]]=preg_quote($this->links_to_repl[$keys[$i]]);
				$content= ereg_replace($this->links_to_repl[$keys[$i]],$this->new_links[$keys[$i]],$content);					
			}
		}
		return $content;
	}
}
