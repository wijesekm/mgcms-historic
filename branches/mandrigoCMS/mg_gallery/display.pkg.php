<?php
/**********************************************************
    display.pkg.php
    mg_gallery ver 0.7.0
	Last Edited By: Kevin Wijesekera
	Date Last Edited: 07/16/07

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

class mg_gallery{
	
	var $config;
	var $tpl;
	
	function mg_gallery{

		//template load
		$this->tpl=new template();
        $file=$GLOBALS['MANDRIGO']['CONFIG']['TEMPLATE_PATH'].$GLOBALS['MANDRIGO']['CURRENTPAGE']['DATAPATH'].$GLOBALS['MANDRIGO']['CURRENTPAGE']['NAME'].'_'.$i.'.'.TPL_EXT;
		if($GLOBALS["MANDRIGO"]["VARS"]["G_IMAGE"]>0){
			if($GLOBALS["MANDRIGO"]["VARS"]["G_SHOWSOURCE"]==1){
				if(!$this->tpl->tpl_load($file,"overview")){
				    $GLOBALS['MANDRIGO']["ERROR_LOGGER"]->el_adderror(140,'sql');
					return false;
				}				
			}
			else{
				if(!$this->tpl->tpl_load($file,"overview")){
				 	$GLOBALS['MANDRIGO']["ERROR_LOGGER"]->el_adderror(140,'sql');
					return false;
				}
			}
		}
		else{
			if(!$this->tpl->tpl_load($file,"overview")){
			 	$GLOBALS['MANDRIGO']["ERROR_LOGGER"]->el_adderror(140,'sql');
				return false;
			}
		}		

		//config load
		$config=$this->ga_load("config");
		if(!$config){
		 	$GLOBALS['MANDRIGO']["ERROR_LOGGER"]->el_adderror(420,'sql');
			return false;
		}
		$this->config["path"]=$GLOBALS["MANDRIGO"]["CONFIG"]["EXTERNAL_PATH"].GA_PATH.$GLOBALS["MANDRIGO"]["CURRENTPAGE"]["NAME"];

	}
	function ga_display(){
		if($GLOBALS["MANDRIGO"]["VARS"]["G_IMAGE"]>0){
			if($GLOBALS["MANDRIGO"]["VARS"]["G_SHOWSOURCE"]==1){
				return ga_sourceimg();
			}
			else{
				return ga_displayimg();
			}
		}
		else{
			$this->ga_displayalbum();
		}
	}
	function ga_displayalbum(){
	 	if(!$album=$this->ga_load("album")){
			return false;
		}
	}
	function ga_displayimg(){
	 	if(!$album=$this->ga_load("album")){
			return false;
		}
	 	if(!$image=$this->ga_load("image"){
			return false;		
		}
		$file=$this->config["path"].$album["al_path"].GA_DISPLAYPATH.$image["img_file"];
		if(!is_file($file)){
			$source=$this->config["path"].$album["al_path"].GA_SOURCEPATH.$image["img_file"];
			$s_image=new img();
			$s_image->img_read($source);
			$size=explode($this->config["display_size"]);
			$s_image->img_presizelimit($size[0],$size[1]);
			$s_image->img_write("",$file);
		}
		if((int)$album["al_enabeled"]==0){
			return false;	
		}
		if((int)$album["al_readlevel"] < $GLOBALS["MANDRIGO"]["CURRENTUSER"]["ACL"]["READ"]){
			return false;
		}
		$parse("IMG_PATH",$file,
			   "AL_IMG_ID",$image["al_img_id"],
			   "IMG_ID",$image["img_id"],
			   "IMG_NAME",$image["img_name"],
			   "IMG_FULLNAME",$image["img_extendedname"],
			   "IMG_FILENAME",$image["img_file"],
			   "IMG_DESCRIPTION",$image["img_description"],
			   "IMG_FILETYPE",$image["img_filetype"]
			   );
	}
	function ga_sourceimg(){
	 	if(!$album=$this->ga_load("album")){
			return false;			
		}
	 	if(!$image=$this->ga_load("image"){
			return false;	
		}
		if((int)$album["al_enabeled"]==0){
			return false;	
		}
		if((int)$album["al_readlevel"] < $GLOBALS["MANDRIGO"]["CURRENTUSER"]["ACL"]["READ"]){
			return false;
		}
	 	$file=$this->config["path"].$album["al_path"].GA_SOURCEPATH.$image["img_file"];
	 	$cur_img=new img();
	 	$cur_img->img_read($file);
	 	if(!$cur_img->img_display()){
	 	 	$this->tpl->tpl_parse(array("GALLERY_ITEM",$GLOBALS["MANDRIGO"]["LANGUAGE"]["GA_NOIMG"]),"overview");
			return $this->tpl->tpl_return("overview");
		}
	}
	function ga_convertext($ext){
		switch($ext){
			case 'jpg':
			case 'jpeg':
				return IMAGETYPE_JPEG;
			break;
			case 'png':
				return IMAGETYPE_PNG;
			break;
			case 'gif':
				return IMAGETYPE_GIF;
			break;
			default:
				return false;
			break;
		}
	}
	function ga_load($item="config"){
	 	switch($config){
			case "album":
	 			return $GLOBALS["MANDRIGO"]["DB"]->db_fetcharray(TABLE_PREFIX.TABLE_ALBUM.$GLOBALS["MANDRIGO"]["CURRENTPAGE"]["NAME"],'',array(array("al_id","=",$GLOBALS["MANDRIGO"]["VARS"]["G_ALBUM"])));	
			break;
			case "img":
	 			return $GLOBALS["MANDRIGO"]["DB"]->db_fetcharray(TABLE_PREFIX.TABLE_IMAGES.$GLOBALS["MANDRIGO"]["CURRENTPAGE"]["NAME"],'',array(array("gal_img_id","=",$GLOBALS["MANDRIGO"]["VARS"]["G_IMAGE"],"",DB_AND),array("al_id","=",$GLOBALS["MANDRIGO"]["VARS"]["G_ALBUM"])));				
			break;
			case "config":
			default:
	 			return $GLOBALS["MANDRIGO"]["DB"]->db_fetcharray(TABLE_PREFIX.TABLE_GALLERY,'',array(array("pg_id","=",$GLOBALS["MANDRIGO"]["CURRENTPAGE"]["ID"])));				
			break;
		}
	}
}