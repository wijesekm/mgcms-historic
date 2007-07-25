<?php
/**********************************************************
	rss.class.php
	Last Edited By: Kevin Wijesekera
	Date Last Edited: 04/12/07

	Copyright (C) 2006-2007 the MandrigoCMS Group

	##########################################################
	This program is free software; you can redistribute it and/or
	modify it under the terms of the GNU General Public License
	as published by the Free Software Foundation; either version 2
	of the License, or (at your option) any later version.

	This program is distributed in the hope that it will be useful,
	but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	GNU General Public License for more details.

	You should have received a copy of the GNU General Public License
	along with this program; if not, write to the Free Software
	Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.

	###########################################################

**********************************************************/

//
//To prevent direct script access
//
if(!defined("START_MANDRIGO")){
    die($GLOBALS["MANDRIGO"]["CONFIG"]["DIE_STRING"]);
}

@include_once($GLOBALS["MANDRIGO"]["CONFIG"]["ROOT_PATH"]."xml.class.".PHP_EXT);

class rss extends xml{
	
	var $rss_array;
	var $version;
	var $post_count;
	
	function rss_load($data,$isfile=true){
		return $this->rss_array=$this->mxml_read($data,$isfile);
	}
	
	function rss_setblank($version="2.0"){
	 	switch($version){
			case "0.92":
				$this->rss_array=array("RSS"=>array("attr"=>'version="0.92"'));			
			break;
			case "2.0":
				$this->rss_array=array("RSS"=>array("attr"=>'version="2.0" xmlns:dc="http://purl.org/dc/elements/1.1/" xmlns:admin="http://webns.net/mvcb/" xmlns:rdf="http://www.w3.org/1999/02/22-rdf-syntax-ns#" xmlns:content="http://purl.org/rss/1.0/modules/content/"'));	
			break;
		};
	}
	function rss_setchannel($title,$link,$description,$ttl,$vesion="2.0"){
	 	
	 	switch($version){
			case "0.92":
				$this->rss_array["RSS"][0]=array(
											"CHANNEL"=>
												array(
													"TITLE"=>
														array("data"=>$title);
												);
												array(
													"LINK"=>
														array("data"=>$link);
												);
												array(
													"DESCRIPTION"=>
														array("data"=>$description);
												);
												array(
													"LANGUAGE"=>
														array("data"=>$GLOBALS["MANDRIGO"]["LANGUAGE"]["NAME"]);
												);
												array(
													"DOCS"=>
														array("data"=>"http://backend.userland.com/rss092/");
												);
											);			
			break;
			case "2.0":
				$this->rss_array["RSS"][0]=array(
											"CHANNEL"=>
												array(
													"TITLE"=>
														array("data"=>$title);
												);
												array(
													"LINK"=>
														array("data"=>$link);
												);
												array(
													"DESCRIPTION"=>
														array("data"=>$description);
												);
												array(
													"LANGUAGE"=>
														array("data"=>$GLOBALS["MANDRIGO"]["LANGUAGE"]["NAME"]);
												);
												array(
													"DOCS"=>
														array("data"=>"http://backend.userland.com/rss/");
												);
												array(
													"ADMIN:GENERATORAGENT"=>
														array("attr"=>'rdf:resource="http://mandrigo.org/"')
														array("data"=>"");
												);
												array(
													"TTL"=>
														array("data"=>$ttl);
												);
											);
			break;
		};		
	}
	function rss_setpost($post_title,$feed_link,$timestamp,$post_id,$content,$version="2.0"){
	 	$c_encoded=htmlspecialchars($content,ENT_QUOTES,$GLOBALS['MANDRIGO']['LANGUAGE']['CHARSET']);
	 	$c_nohtml=strip_tags($content);
	 	$post_title=strip_tags($post_title);
	 	$guid=$post_id."@".$GLOBALS["MANDRIGO"]["SITE"]["SITE_URL"].$GLOBALS["MANDRIGO"]["CURRENTPAGE"]["NAME"];
	 	
	 	switch($version){
			case "0.92":
				$this->rss_array["RSS"][0]["CHANNEL"]=array(
														"ITEM"=>
															array(
																"TITLE"=>
																	array("data"=>$post_title);
															);
															array(
																"DESCRIPTION"=>
																	array("data"=>$c_nohtml);
															);
															array(
																"LINK"=>
																	array("data"=>$feed_link);
															);
														);			
			break;
			case "2.0":
				$this->rss_array["RSS"][0]["CHANNEL"]=array(
														"ITEM"=>
															array(
																"TITLE"=>
																	array("data"=>$post_title);
															);
															array(
																"LINK"=>
																	array("data"=>$feed_link);
															);
															array(
																"pubDate"=>
																	array("data"=>date('D, d M Y H:i:s O',$timestamp))
															);
															array(
																"GUID"=>
																	array("attr"=>'isPermaLink="false"'),
																	array("data"=>$guid)
															);
															array(
																"DESCRIPTION"=>
																	array("data"=>$c_nohtml);
															);

														);		
			break;
		};
	}
    function rss_genlink($url_data){
      	$link='';
 		if($GLOBALS['MANDRIGO']['SITE']['URL_FORMAT']==1){
			$url=$GLOBALS['MANDRIGO']['SITE']['SITE_URL'].$GLOBALS['MANDRIGO']['SITE']['INDEX_NAME']."/";
		}
		else{
		  	$url=$GLOBALS['MANDRIGO']['SITE']['SITE_URL'].$GLOBALS['MANDRIGO']['SITE']['INDEX_NAME']."?";
		}  
		$soq=count($url_data);
		$i=0;
		while($i<$soq){
			if($GLOBALS['MANDRIGO']['SITE']['URL_FORMAT']==1){
				$url.=$url_data[$i]."/".$url_data[$i+1];
				$i+=2;
				if($i<$soq){
					$url.="/";
				}
			}
			else{
				$url.=$url_data[$i]."=".$url_data[$i+1];
				$i+=2;
				if($i<$soq){
					$url.="&";
				}
			}
		}
		return $url;
	}
	
}