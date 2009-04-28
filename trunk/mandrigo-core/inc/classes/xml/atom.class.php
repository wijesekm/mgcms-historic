<?php

/**
 * @file                atom.class.php
 * @author              Kevin Wijesekera
 * @copyright   		2009
 * @edited              4-27-2009
 
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

class atom extends mgXML{

	private $count;
	
	public function __construct($pkg){
		parent::__construct($pkg);
		$this->cfg['xml_contenttype']='application/atom+xml';
		$this->count=0;
	}
	
	public function atom_start($attrs,$type=false){
		parent::mxml_reset();
		$attrs['xmlns']='http://www.w3.org/2005/Atom';
		$attrs['xml:lang']=$GLOBALS['MG']['LANG']['NAME'];
		parent::mxml_addTag('feed',$attrs,false,false);
	}
	
	public function atom_setPage($title,$feed_url,$page_url,$author,$ts_ud,$other=false){
		parent::mxml_addTag('title',false,$title,array(0));
		parent::mxml_addTag('link',array('href'=>$feed_url,'rel'=>'self'),false,array(0));
		parent::mxml_addTag('link',array('href'=>$page_url),false,array(0));
		parent::mxml_addTag('id',false,$page_url,array(0));
		parent::mxml_addTag('updated',false,date('c',$ts_ud),array(0));
		$this->count=5;
		if(is_array($author)){
			foreach($author as $value){
				parent::mxml_addTag($value[0],false,false,array(0));
				foreach($value[1] as $key=>$val){
					if(is_array($val)){
						parent::mxml_addTag($key,$val[0],$val[1],array(0,$this->count));
					}
					else{
						parent::mxml_addTag($key,false,$val,array(0,$this->count));
					}
				}
				$this->count++;
			}
		}
		$this->atom_addOther($other,array(0));
		return true;
	}
	
	public function atom_addPost($title,$link,$ts_ud,$summary,$other=false){
		parent::mxml_addTag('entry',false,false,array(0));
		parent::mxml_addTag('id',false,$link,array(0,$this->count));
		parent::mxml_addTag('link',array('href'=>$link),false,array(0,$this->count));
		parent::mxml_addTag('updated',false,date('c',$ts_ud),array(0,$this->count));
		if($summary){
			parent::mxml_addTag('summary',false,$summary,array(0,$this->count));
		}
		$this->atom_addOther($other,array(0,$this->count));
		$this->count++;
	}
	
	private function atom_addOther($other,$parents){
		if(is_array($other)){
			foreach($other as $key=>$val){
				if(is_array($value)){
					parent::mxml_addTag($key,$val[0],$val[1],$parents);
				}
				else if($value){
					parent::mxml_addTag($key,false,$val,$parents);
				}
				$this->count++;
			}
		}
	}
}