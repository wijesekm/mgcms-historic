<?php
/**********************************************************
    display.pkg.php
    mg_menu ver 0.7.0
	Last Edited By: Kevin Wijesekera
	Date Last Edited: 04/08/07

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



class mg_menu{

	var $config;
	var $tpl;

    function mg_menu($i){
        $this->tpl=new template();
        $file=$GLOBALS['MANDRIGO']['CONFIG']['TEMPLATE_PATH'].$GLOBALS['MANDRIGO']['CURRENTPAGE']['DATAPATH'].$GLOBALS['MANDRIGO']['CURRENTPAGE']['NAME'].'_'.$i.'.'.TPL_EXT;
		if(!$this->tpl->tpl_load($file,"overview")||!$this->tpl->tpl_load($file,"mitem")){
			$GLOBALS["MANDRIGO"]["ERROR_LOGGER"]->el_adderror(130,"display");
			return false;
		}
		if(!$this->config=$GLOBALS["MANDRIGO"]["DB"]->db_fetcharray(TABLE_PREFIX.TABLE_MENU,'',array(array('page_id','=',$GLOBALS['MANDRIGO']['CURRENTPAGE']['ID'],DB_AND),array('part_id','=',$i)))){
            $GLOBALS['MANDRIGO']["ERROR_LOGGER"]->el_adderror(410,'sql');
			return false;
        }    
        $this->config['menu_items']=explode(";",$this->config['menu_items']);
        return true;
    }
    
	//#################################
	//
	// PUBLIC FUNCTIONS
	//
	//#################################

	//
	//public function mm_display();
	//
	//Displays the menu
	//
    function mm_display(){
		$mstring='';
		$soa=count($this->config['menu_items']);
		for($i=0;$i<$soa;$i++){
			$cur_subpage=$GLOBALS["MANDRIGO"]["CURRENTPAGE"]["SUBPAGES"][$this->config['menu_items'][$i]];
			if($cur_subpage > 0){
				$subpage=$GLOBALS["MANDRIGO"]["DB"]->db_fetcharray(TABLE_PREFIX.TABLE_PAGES,'',array(array('pg_id','=',$cur_subpage)));
				
				if(eregi('==>',$subpage['pg_name'])){
				 	$link=ereg_replace('==>','',$subpage['pg_name']);
				 	$name=$subpage['pg_fullname'];
				}
				else{
					$link=$this->mm_genurl($subpage['pg_name']);
					$name=$subpage['pg_fullname'];
				}
				$tpl_new=new template();
				$tpl_new->tpl_load($this->tpl->tpl_return('mitem'),'item',false);
				$tpl_new->tpl_parse(array("ITEM_URL",$link,"ITEM",$name,"DESC",$subpage['pg_desc']),'item',1,false);
				$mstring.=$tpl_new->tpl_return('item');			
			}
		}
		$this->tpl->tpl_parse(array('MENU',$mstring),'overview',1,false);
		return $this->tpl->tpl_return('overview');
	}

	
	//#################################
	//
	// PRIVATE FUNCTIONS
	//
	//#################################
	
	//
	//private function md_genurl($page,$page_name);
	//
	//Generates a navigation URL given the page and the page_name
	//	
	function mm_genurl($page,$page_name){
		if($GLOBALS['MANDRIGO']['SITE']['URL_FORMAT']==1){
			return $GLOBALS['MANDRIGO']['SITE']['SITE_URL'].$GLOBALS['MANDRIGO']['SITE']['INDEX_NAME'].'/p/'.$page; 
		}
		else{
	  		return $GLOBALS['MANDRIGO']['SITE']['SITE_URL'].$GLOBALS['MANDRIGO']['SITE']['INDEX_NAME'].'?p='.$page; 
		}
	}
}
?>
