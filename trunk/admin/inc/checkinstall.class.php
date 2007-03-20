<?php
/**********************************************************
    checksys.class.php
	Last Edited By: Kevin Wijesekera
	Date Last Edited: 03/19/07

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

class checkinstall{
	
	var $tpl;
	var $url;
	var $fail;
	var $syscheck;
	
	function checkinstall(){
	 	$this->fail=false;
		$pth=$GLOBALS["MANDRIGO"]["CONFIG"]["TEMPLATE_PATH"].TPL_ADMINPATH.TPL_ADMINCHECK;
		$this->tpl=new template();
		if(!$this->tpl->tpl_load($pth,"main")||!$this->tpl->tpl_load($pth,"check")||!$this->tpl->tpl_load($pth,"phpini")
		||!$this->tpl->tpl_load($pth,"phpext")||!$this->tpl->tpl_load($pth,"perms")||!$this->tpl->tpl_load($pth,"locs")){
		 	$GLOBALS["MANDRIGO"]["ERROR_LOGGER"]->el_adderror(7,"display");
			return false;
		}
		$warn="src=\"".$GLOBALS["MANDRIGO"]["SITE"]["IMG_URL"]."admin/check/dep.png\"";
		$err="src=\"".$GLOBALS["MANDRIGO"]["SITE"]["IMG_URL"]."admin/check/incomplete.png\"";
		$ok="src=\"".$GLOBALS["MANDRIGO"]["SITE"]["IMG_URL"]."admin/check/completed.png\"";
		$this->url["WARN"]=ereg_replace("{ATTRIB}",$warn." alt=\"W\"",$GLOBALS["MANDRIGO"]["HTML"]["IMG"]);
		$this->url["ERR"]=ereg_replace("{ATTRIB}",$err." alt=\"E\"",$GLOBALS["MANDRIGO"]["HTML"]["IMG"]);
		$this->url["OK"]=ereg_replace("{ATTRIB}",$ok." alt=\"O\"",$GLOBALS["MANDRIGO"]["HTML"]["IMG"]);
		$this->syscheck=new checksys();
	}
	
	function ci_display(){
		$c=$this->ci_phpbasic();
		$c.=$this->ci_phpext();
		$c.=$this->ci_filelocs();
		$c.=$this->ci_fileperms();
		$this->tpl->tpl_parse(array("CONTENT",$c),"main",1,false);
		return $this->tpl->tpl_return("main");
	}
	function ci_phpbasic(){
	 	$content="";
	 	$content.=$this->ci_set("PHPVER",$this->syscheck->cs_phpver(array('4.6.5','5.4.0'),array('4.0.0')));
	 	$checks=array(array("SAFEMODE","safe_mode",array(array("set","","warn")),false),
		 			  array("MQR","magic_quotes_runtime",array(array("set","","warn")),false),
					  array("RG","regester_globals",array(array("set","","err")),false),
					  array("FUP","file_uploads",array(array("notset","","err")),false),
					  array("UPMFS","upload_max_filesize",array(array("<=","2M","err"),array("<=","10M","warn")),true),
					  array("SMP","sendmail_path",array(array("in","/","err")),false),
					  array("MQGPC","magic_quotes_gpc",array(array("notset","","warn")),false),
					  array("INCP","include_path",array(array("in",".","err")),false),
					  array("DCLASS","disable_classes",array(array("set","","err")),false),
					  array("DFUNCT","disable_functions",array(array("set","","err")),false));
	 	
		$socheck=count($checks);
	 	for($j=0;$j<$socheck;$j++){
			$content.=$this->ci_set($checks[$j][0],$this->syscheck->cs_phpini($checks[$j][1],$checks[$j][2],$checks[$j][3]));
		}

		$this->tpl->tpl_parse(array("CHECKS",$content),"phpini",1,false);
		return $this->tpl->tpl_return("phpini");
	}
	function ci_phpext(){
		$checks=array(array("LDAP","ldap",true,false),
			     	  array("MBSTRING","mbstring",true,true),
					  array("MYSQL","mysql",true,false),
					  array("MYSQLI","mysqli",true,false),
					  array("PGSQL","pgsql",true,false),
					  array("OCI8","oci8",true,false),
					  array("MSSQL","mssql",true,false),
					  array("ZLIB","zlib",true,false),
					  array("XML","xml",true,true),
					  array("GD","gd",true,true));
		$content="";
		$socheck=count($checks);
	 	for($j=0;$j<$socheck;$j++){
			$content.=$this->ci_set($checks[$j][0],$this->syscheck->cs_phpext($checks[$j][1],$checks[$j][2],$checks[$j][3]));
		}
		$this->tpl->tpl_parse(array("CHECKS",$content),"phpext",1,false);
		return $this->tpl->tpl_return("phpext");
	}
	function ci_filelocs(){

		$checks=array(array("LBASE",$GLOBALS["MANDRIGO"]["CONFIG"]["BASE_PATH"],true,true),
			          array("LINC",$GLOBALS["MANDRIGO"]["CONFIG"]["ROOT_PATH"],true,true),
			  		  array("LTEMPLATE",$GLOBALS["MANDRIGO"]["CONFIG"]["PLUGIN_PATH"],true,true),
			  		  array("LPLUGIN",$GLOBALS["MANDRIGO"]["CONFIG"]["TEMPLATE_PATH"],true,true),
			          array("LLOG",$GLOBALS["MANDRIGO"]["CONFIG"]["LOG_PATH"],true,true),
			          array("LIMG",$GLOBALS["MANDRIGO"]["CONFIG"]["IMG_PATH"],true,true),
			          array("LTMP",$GLOBALS["MANDRIGO"]["CONFIG"]["TMP_PATH"],true,true),
			          array("LADMIN",$GLOBALS["MANDRIGO"]["CONFIG"]["ADMIN_ROOT_PATH"],true,true),
			          array("LLOGIN",$GLOBALS["MANDRIGO"]["CONFIG"]["LOGIN_ROOT_PATH"],true,true)
			         );
		$content="";
		$socheck=count($checks);
		for($j=0;$j<$socheck;$j++){
			$content.=$this->ci_set($checks[$j][0],$this->syscheck->cs_checkpath($checks[$j][1],$checks[$j][2],$checks[$j][3]));
		}
		$this->tpl->tpl_parse(array("CHECKS",$content),"locs",1,false);
		return $this->tpl->tpl_return("locs");		
	}
	function ci_fileperms(){
	 	$root=$GLOBALS["MANDRIGO"]["CONFIG"]["BASE_PATH"];
	 	
		$checks=
		array(array("EXT",$root."config{$GLOBALS["MANDRIGO"]["CONFIG"]["PATH"]}extension.inc",array(true,true),array(array(3,array("w","r"),array("in","in"),array("err","err")))),
		array("CONFIGINI",$root."config{$GLOBALS["MANDRIGO"]["CONFIG"]["PATH"]}config.ini.".PHP_EXT,array(true,true),array(array(3,array("w","r"),array("in","in"),array("err","err")))),
		array("BASE",$root,"",array(array(3,array("w"),array("in"),array("err")))),
		array("INC",$GLOBALS["MANDRIGO"]["CONFIG"]["ROOT_PATH"],"",array(array(3,array("w","r"),array("in","in"),array("err","warn")))),
		array("TEMPLATE",$GLOBALS["MANDRIGO"]["CONFIG"]["TEMPLATE_PATH"],array(true,true),array(array(3,array("w"),array("in"),array("err")))),
		array("PLUGIN",$GLOBALS["MANDRIGO"]["CONFIG"]["PLUGIN_PATH"],array(true,true),array(array(3,array("w","r"),array("in","in"),array("err","warn")))),
		array("LOG",$GLOBALS["MANDRIGO"]["CONFIG"]["LOG_PATH"],array(true,true),""),
		array("IMG",$GLOBALS["MANDRIGO"]["CONFIG"]["IMG_PATH"],array(true,true),""),
		array("TMP",$GLOBALS["MANDRIGO"]["CONFIG"]["TMP_PATH"],array(true,true),array(array(3,array("w","r"),array("in","in"),array("err","err")))),
		array("ADMIN",$GLOBALS["MANDRIGO"]["CONFIG"]["ADMIN_ROOT_PATH"],"",array(array(3,array("w"),array("in"),array("err")))),
		array("LOGIN",$GLOBALS["MANDRIGO"]["CONFIG"]["LOGIN_ROOT_PATH"],"",array(array(3,array("w"),array("in"),array("err")))),
		);
		$content="";
		$socheck=count($checks);
	 	for($j=0;$j<$socheck;$j++){
	 	 	if($checks[$j][2]){
	 	 		$c1=$this->syscheck->cs_checkwebwrite($checks[$j][1],$checks[$j][2][0],$checks[$j][2][1]);				
			}
			else{
				$c1=0;
			}
	 	 	if($checks[$j][3]){
	 	 		$c2=$this->syscheck->cs_checkperms($checks[$j][1],$checks[$j][3]);				
			}
			else{
				$c2=0;
			}
	 	 	
	 	 	if($c1===2&&$c2===2){
				$content.=$this->ci_set($checks[$j][0],2);
			}
			else if($c1===1&&$c2===1){
				$content.=$this->ci_set($checks[$j][0],1);
			}
			else{
				$content.=$this->ci_set($checks[$j][0],0);	
			}
		}		
		$this->tpl->tpl_parse(array("CHECKS",$content),"perms",1,false);
		return $this->tpl->tpl_return("perms");		
	}
	function ci_set($name,$error){
	 	$url="";
	 	$msg="";
	 	if($error==2){
			$msg=$GLOBALS["MANDRIGO"]["LANGUAGE"]["{$name}_ERR"];
			$url=$this->url["ERR"];
			$this->fail=true;
		}
	 	else if($error==1){
			$msg=$GLOBALS["MANDRIGO"]["LANGUAGE"]["{$name}_WARN"];
			$url=$this->url["WARN"];
			
		}
		else{
			$msg=$GLOBALS["MANDRIGO"]["LANGUAGE"]["{$name}_OK"];
			$url=$this->url["OK"];
			
		}
		$parse=array("CHECK_NAME",$GLOBALS["MANDRIGO"]["LANGUAGE"]["$name"],"IMG",$url,"MSG",$msg);
		$tpl1=new template();
		$tpl1->tpl_load($this->tpl->tpl_return("check"),"check_sub",false);
		$tpl1->tpl_parse($parse,"check_sub",1,false);

		return $tpl1->tpl_return("check_sub");
	}
}