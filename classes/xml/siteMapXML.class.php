<?php

/**
 * @file                siteMapXML.class.php
 * @author              Kevin Wijesekera
 * @copyright   		2009
 * @edited              4-25-2009
 * @comment				based on Sitemap Protocol 0.9 (http://www.sitemap.org/)
 
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

class siteMapXML extends mgXML{
	
	private $count;
	
	public function __construct($pkg){
		$this->cfg['xml_contenttype']='text/xml';
		parent::__construct($pkg);
		$this->count=0;
		parent::mxml_addTag('urlset',array('xmlns'=>'http://www.sitemaps.org/schemas/sitemap/0.9'),false,false);
	}
	
	public function smxml_addPage($page_url,$update_timestamp,$freq='weekly',$priority='0.5'){
		parent::mxml_addTag('url',false,false,array(0));
		parent::mxml_addTag('loc',false,htmlentities($page_url,ENT_QUOTES,$this->cfg['xml_encoding']),array(0,$this->count));
		parent::mxml_addTag('lastmod',false,date('Y-m-d',$update_timestamp),array(0,$this->count));
		parent::mxml_addTag('changefreq',false,$freq,array(0,$this->count));
		parent::mxml_addTag('priority',false,$priority,array(0,$this->count));
		$this->count++;
	}
	
}