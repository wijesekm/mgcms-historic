<?php

/**
 * @file                rss.class.php
 * @author              Kevin Wijesekera
 * @copyright   		2009
 * @edited              4-26-2009
 
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

class rss extends mgXML{
	
	private $type;
	private $count;
	
	public function __construct($pkg){
		parent::__construct($pkg);
		$this->cfg['xml_contenttype']='application/rss+xml';
		$this->count=0;
	}
	
	public function rss_start($attrs,$type){
		parent::mxml_reset();
		switch($type){
			case '2.0':
				$attrs['version']='2.0';
				parent::mxml_addTag('rss',$attrs,false,false);
				$this->type='2.0';
			break;
			case '1.0':
			default:
				$attrs['xmlns:rdf']='http://www.w3.org/1999/02/22-rdf-syntax-ns#';
				$attrs['xmlns']='http://purl.org/rss/1.0/';
				$attrs['xmlns:dc']='http://purl.org/dc/elements/1.1/';
				parent::mxml_addTag('rdf:RDF',$attrs,false,false);
				$this->type='1.0';
			break;
		}
	}
	
	public function rss_setPage($title,$page_url,$desc,$ts_ud,$other=false){
		switch($this->type){
			case '2.0':
				parent::mxml_addTag('channel',false,false,array(0));
				parent::mxml_addTag('title',false,$title,array(0,0));
				parent::mxml_addTag('link',false,$page_url,array(0,0));
				parent::mxml_addTag('description',false,$desc,array(0,0));
				parent::mxml_addTag('language',false,$GLOBALS['MG']['LANG']['NAME'],array(0,0));
				parent::mxml_addTag('lastBuildDate',false,date('D, d M Y H:i:s T',$ts_ud),array(0,0));
				$this->count=5;
				if($other['img']){
					parent::mxml_addTag('image',false,false,array(0,0));
					$this->rss_addOther($other['img'],array(0,0,$c));
					$other['img']=false;
					$this->count++;
				}
				if($other['textInput']){
					parent::mxml_addTag('textInput',false,false,array(0,0));
					$this->rss_addOther($other['textInput'],array(0,0,$c));
					$other['textInput']=false;
					$this->count++;					
				}
				$this->rss_addOther($other,array(0,0));
			break;
			case '1.0':
			default:
				$this->count++;
				parent::mxml_addTag('channel',array('rdf:about'=>$page_url),false,array(0));
				parent::mxml_addTag('title',false,$title,array(0,0));
				parent::mxml_addTag('link',false,$page_url,array(0,0));
				parent::mxml_addTag('description',false,$desc,array(0,0));
				parent::mxml_addTag('dc:language',false,$GLOBALS['MG']['LANG']['NAME'],array(0,0));
				parent::mxml_addtag('dc:date',false,date('c',$ts_ud),array(0,0));
				parent::mxml_addtag('items',false,false,array(0,0));
				parent::mxml_addtag('rdf:Seq',false,false,array(0,0,5));
				$this->rss_addOther($other,array(0,0));
			break;			
		}
	}
	
	public function rss_addPost($title,$link,$ts_ud,$summary,$other=false){
		switch($this->type){
			case '2.0':
				parent::mxml_addtag('item',false,false,array(0,0));
				parent::mxml_addTag('title',false,$title,array(0,0,$this->count));
				parent::mxml_addTag('link',false,$link,array(0,0,$this->count));
				parent::mxml_addTag('guid',false,$link,array(0,0,$this->count));
				parent::mxml_addTag('pubDate',false,date('D, d M Y H:i:s T',$ts_ud),array(0,0,$this->count));
				parent::mxml_addTag('description',fales,$summary,array(0,0,$this->count));
				$this->rss_addOther($other,array(0,0,$this->count));
				$this->count++;				
			break;
			case '1.0':
			default:
				parent::mxml_addTag('item',array('rdf:about'=>$link),false,array(0));
				parent::mxml_addTag('rdf:li',array('rdf:resource'=>$link),false,array(0,0,5,0));
				parent::mxml_addTag('title',false,$title,array(0,$this->count));
				parent::mxml_addTag('link',false,$link,array(0,$this->count));
				parent::mxml_addTag('description',false,$summary,array(0,$this->count));
				parent::mxml_addTag('dc:date',false,date('c',$ts_ud),array(0,$this->count));
				$this->rss_addOther($other,array(0,$this->count));
				$this->count++;
			break;			
		}
	}

	private function rss_addOther($other,$parents){
		if(is_array($other)){
			foreach($other as $key=>$val){
				if(is_array($value)){
					parent::mxml_addTag($key,$val[0],$val[1],$parents);
				}
				else if($value){
					parent::mxml_addTag($key,false,$val,$parents);
				}
				if($this->type='2.0'){
					$this->count++;
				}
			}
		}
	}

}