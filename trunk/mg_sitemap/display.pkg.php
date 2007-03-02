<?php
/**********************************************************
    display.pkg.php
    mg_sitemap ver 0.7.0
	Last Edited By: Kevin Wijesekera
	Date Last Edited: 02/29/07

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


class site_map{

	var $tpl;

    function site_map($i){
        $this->tpl=new template();
        $file=$GLOBALS['MANDRIGO']['CONFIG']['TEMPLATE_PATH'].$GLOBALS['MANDRIGO']['CURRENTPAGE']['DATAPATH'].$GLOBALS['MANDRIGO']['CURRENTPAGE']['NAME'].'_'.$i.'.'.TPL_EXT;
		if(!$this->tpl->tpl_load($file,"top")||!$this->tpl->tpl_load($file,"li_item")||!$this->tpl->tpl_load($file,"ul_item")||!$this->tpl->tpl_load($file,"bottom")){
			$GLOBALS["MANDRIGO"]["ERROR_LOGGER"]->el_adderror(110,"display");
			return false;
		}     
        return false;
	}
    
    function sm_display($i){
        $root_pages=$GLOBALS["MANDRIGO"]["DB"]->db_fetcharray(TABLE_PREFIX.TABLE_PAGES,"",array(array("pg_root","=","1")),"ASSOC",DB_ALL_ROWS);
        if(!$root_pages){
			return $GLOBALS["MANDRIGO"]["LANGUAGE"]["MG_SITEMAP_NOROOT"];
		}
		$soq=count($root_pages);
        $data='';
        for($i=0;$i<$soq;$i++){
			$current_page=$root_pages[$i];
			if(eregi('==>',$current_page['pg_name'])){
				$data.=$this->sm_genlistitem($current_page['pg_name'],$current_page['pg_fullname'],false);	
			}
			else{
				$page='';
				if($GLOBALS['MANDRIGO']['SITE']['PAGE_TYPE']==1){
					$page=$current_page['pg_id'];
				}
				else{
					$page=$current_page['pg_name'];
				}
				$data.=$this->sm_genlistitem($page,$current_page['pg_fullname']);	
			}
			$data.=$this->sm_subpage($current_page,1,$current_page['pg_id'].';');
		}
		$this->tpl->tpl_parse(array("UL_LIST",$data,"LEVEL","base"),"ul_item",2);
		return $this->tpl->tpl_return("top").$this->tpl->tpl_return("ul_item").$this->tpl->tpl_return("bottom");
	}
	function sm_subpage($sql,$level,$prev){
		$data='';
		$sql['pg_subpages']=explode(';',$sql['pg_subpages']);
		if(!$sql['pg_subpages'][0]){
			return false;
		}
		$soq=count($sql['pg_subpages']);
		$filter="";
		for($j=0;$j<$soq;$j++){
			if($j+1<$soq){
				$filter[$j]=array('pg_id','=',(int)$sql['pg_subpages'][$j],DB_OR);	
			}else{
				$filter[$j]=array('pg_id','=',(int)$sql['pg_subpages'][$j]);
			}
		}
		$subpages=$GLOBALS['MANDRIGO']['DB']->db_fetcharray(TABLE_PREFIX.TABLE_PAGES,"",$filter,"ASSOC",DB_ALL_ROWS);
		$soq=count($subpages);
		for($j=0;$j<$soq;$j++){
			$current_page=$subpages[$j];
			if(eregi('==>',$current_page['pg_name'])){
				$data.=$this->sm_genlistitem($current_page['pg_name'],$current_page['pg_fullname'],false);	
			}
			else{
				$page='';
				if($GLOBALS['MANDRIGO']['SITE']['PAGE_TYPE']==1){
					$page=$current_page['pg_id'];
				}
				else{
					$page=$current_page['pg_name'];
				}
				$data.=$this->sm_genlistitem($page,$current_page['pg_fullname']);	
			}
			if($this->pg_checkid($current_page['page_id'],$prev)){
				$data.=$this->sm_subpage($current_page,$level+1,$current_page['pg_id'].';'.$prev);
			}
		}
		$tplul=new template();
	 	$tplul->tpl_load("<!--MG_TEMPLATE_START_main-->".$this->tpl->tpl_return("ul_item")."<!--MG_TEMPLATE_END_main-->","main",false);		
	 	$tplul->tpl_parse(array("UL_LIST",$data,"LEVEL",(string)$level),"main",2);
		return $tplul->tpl_return("main");
	}
	function pg_checkid($id,$ids){
		$ids=explode(";",$ids);
		return !in_array($id,$ids);
	}
	function sm_genlistitem($url,$name,$internal=true){
	 	$tple=new template();
	 	$tple->tpl_load("<!--MG_TEMPLATE_START_main-->".$this->tpl->tpl_return("li_item")."<!--MG_TEMPLATE_END_main-->","main",false);
	 	if($internal){
			if($GLOBALS['MANDRIGO']['SITE']['URL_FORMAT']==1){
				$url=$GLOBALS['MANDRIGO']['SITE']['SITE_URL'].$GLOBALS['MANDRIGO']['SITE']['INDEX_NAME'].'/p/'.$url;
			}
			else{
		  		$url=$GLOBALS['MANDRIGO']['SITE']['SITE_URL'].$GLOBALS['MANDRIGO']['SITE']['INDEX_NAME'].'?p='.$url;
			}
		}
	 	$tple->tpl_parse(array("URL",ereg_replace("==>","",$url),"NAME",$name),"main",2);
	 	return $tple->tpl_return("main");
	}
}
