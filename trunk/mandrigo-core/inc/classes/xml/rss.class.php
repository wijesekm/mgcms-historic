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
	private $seq_count;
	
	public function __construct($pkg){
		parent::__construct($pkg);
		$this->cfg['xml_contenttype']='application/rss+xml';
		$this->count=0;
	}
	
	public function rss_setType($attrs,$type){
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
				parent::mxml_addTag('rdf:RDF',$attrs,false,false);
				$this->type='1.0';
			break;
		}
	}
	
	public function rss_setChannel($data){
		switch($this->type){
			case '2.0':
				parent::mxml_addTag('channel',false,false,array(0));
				foreach($data as $key=>$value){
					parent::mxml_addTag($key,false,$value,array(0,0));
					$this->count++;
				}
			break;
			case '1.0':
			default:
				$this->seq_count=0;
				$this->count++;
				parent::mxml_addTag('channel',array('rdf:about'=>$data['link']),false,array(0));
				foreach($data as $key=>$value){
					parent::mxml_addTag($key,false,$value,array(0,0));
					$this->seq_count++;
				}
				parent::mxml_addtag('items',false,false,array(0,0));
				parent::mxml_addtag('rdf:Seq',false,false,array(0,0,$this->seq_count));
			break;			
		}
	}
	
	public function rss_addPost($data){
		switch($this->type){
			case '2.0':
				parent::mxml_addtag('item',false,false,array(0,0));
				foreach($data as $key=>$value){
					parent::mxml_addTag($key,false,$value,array(0,0,$this->count));
				}
				$this->count++;				
			break;
			case '1.0':
			default:
				parent::mxml_addtag('item',array('rdf:about'=>$data['link']),false,array(0));
				parent::mxml_addtag('rdf:li',array('rdf:resource'=>$data['link']),false,array(0,0,$this->seq_count,0));
				foreach($data as $key=>$value){
					parent::mxml_addTag($key,false,$value,array(0,$this->count));
				}
				$this->count++;
			break;			
		}
	}
}