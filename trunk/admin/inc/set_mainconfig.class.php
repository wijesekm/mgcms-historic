<?php
/**********************************************************
    checksys.class.php
	Last Edited By: Kevin Wijesekera
	Date Last Edited: 03/13/07

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

define("CFGTPL_PATH","/config_templates/");
define("EXT_TPL","extension.inc.".TPL_EXT);
define("CFG_TPL","config.ini.php.".TPL_EXT);
define("EXT_NAME","extension.inc");
define("CFG_NAME","config.ini.".PHP_EXT);

class set_mainconfig{
	
	var $config;
	
	function se_mainconfig(){
		$this->config=array("is_installed","true");
	}
	
	function sc_setext($phpext,$tplext,$xmlext){
		if(!$phpext){
			$phpext="php";
		}
		if(!$tplext){
			$tplext="tpl";
		}
		if(!$xmlext){
			$tplext="xml";
		}
		$tpl=new template();
		$tpl->tpl_load($GLOBALS["MANDRIGO"]["CONFIG"]["ADMIN_ROOT_PATH"].CFGTPL_PATH.EXT_TPL,"main");
		$tpl->tpl_parse(array("PHPEXT",$phpext,"TPLEXT",$tplext,"XMLEXT",$xmlext),"main",1,false);
		$cfg_path=ereg_replace("/inc","/config",$GLOBALS["MANDRIGO"]["CONFIG"]["ROOT_PATH");
		return $this->sc_writefile($tpl->tpl_return("main"),$cfg_path.EXT_NAME);
	}
	function sc_setconfig($ad=false){

		$cfg_path=ereg_replace("/inc","/config",$GLOBALS["MANDRIGO"]["CONFIG"]["ROOT_PATH");
		@include($cfg_path.CFG_NAME);
		if(!$this->config["root_path"]){
		 	$parse=array("root_path",$GLOBALS["MANDRIGO"]["CONFIG"]["ROOT_PATH"],
			 			 "plugin_path",$GLOBALS["MANDRIGO"]["CONFIG"]["ROOT_PATH"],
						 "template_path",$GLOBALS["MANDRIGO"]["CONFIG"]["ROOT_PATH"],
			             "log_path",$GLOBALS["MANDRIGO"]["CONFIG"]["ROOT_PATH"],
						 "img_path",$GLOBALS["MANDRIGO"]["CONFIG"]["ROOT_PATH"],
						 "tmp_path",$GLOBALS["MANDRIGO"]["CONFIG"]["ROOT_PATH"],
						 "admin_path",$GLOBALS["MANDRIGO"]["CONFIG"]["ROOT_PATH"],
						 "login_path",$GLOBALS["MANDRIGO"]["CONFIG"]["ROOT_PATH"]);
			$this->sc_appendarray($this->config,$parse);
		}
		if(!$this->config["sqltype"]){
		 	if($sql_config["USE_SSL"]){
				$usessl="true";
			}
			else{
				$usessl="false";
			}
			$ssl_="array(\"KEY\"=>\"{$sql_config["SSL"]["KEY"]}\",
						 \"CERT\"=>\"{$sql_config["SSL"]["CERT"]}\",
						 \"CA\"=>\"{$sql_config["SSL"]["CA"]}\",
						 \"CAPATH\"=>\"{$sql_config["SSL"]["CAPATH"]}\");";
		 	$parse=array("sqltype",$sql_config["MANDRIGO"]["CONFIG"]["SQL_TYPE"],
			 			 "sqlhost",$sql_config["SQL_HOST"],
						 "sqlport",$sql_config["SQL_PORT"],
			             "sqlsocket",$sql_config["SQL_SOCKET"],
			             "sqluser",$sql_config["SQL_USER"],
						 "sqlpass",$sql_config["SQL_PASSWORD"],
						 "sqldb",$sql_config["SQL_DATABASE"],
						 "sqlprefix",$sql_config["TABLE_PREFIX"],
						 "usessl",$usessl,
						 "ssl",$ssl_);
			$this->sc_appendarray($this->config,$parse);
		}
		if(!$this->config["os_type"]){
			$this->sc_appendarray($this->config,array("os_type",$path_style));
		}
		if(!$this->config["loglvl1"]){
		 	if($log_config["LOG_LEVEL_1"]){
				$lvl1="true";
			}
			else{
				$lvl1="false;"
			}
		 	if($log_config["LOG_LEVEL_2"]){
				$lvl2="true";
			}
			else{
				$lvl2="false;"
			}
			$parse=array("loglvl1",$lvl1,"loglvl2",$lvl2,"logarchive",$log_config["ARCHIVE"]);
			$this->sc_appendarray($this->config,$parse);
		}
		if(!$this->config["lang"]){
			$this->sc_appendarray($this->config,array("lang",$GLOBALS["MANDRIGO"]["CONFIG"]["LANGUAGE"]
													  "html",$GLOBALS["MANDRIGO"]["CONFIG"]["HTML_VER"]));
		}
		if(!$this->config["ldapdn"]&&ad){
			$soq=count($adldap_config["DC"]);
			$string="array(";
			for($i=0;$i<$soq;$i++){
				$string.="array(\"{$adldap_config["DC"][$i][0]}\",\"{$adldap_config["DC"][$i][1]}\")";
				if($i+1<$soq){
					$string.=",";
				}
			}
			$string.=");";
			$parse=array("ldapdn",$adldap_config["DN"],
			             "ldapdc",$string,
						 "ldapacctsuffix",$adldap_config["ACCT_SUFFIX"],
						 "ldapcuser",$adldap_config["CONTROL_USER"],
						 "ldapcpass",$adldap_config["CONTROL_PASSWORD"]);	
			$this->sc_appendarray($this->config,$parse);	
		}
		$tpl=new template();
		$tpl->tpl_load($GLOBALS["MANDRIGO"]["CONFIG"]["ADMIN_ROOT_PATH"].CFGTPL_PATH.CFG_TPL,"main");
		$tpl->tpl_parse($this->config,"main",1,false);
		return $this->sc_writefile($tpl->tpl_return("main"),$cfg_path.CFG_NAME);
	}
	function sc_checkpaths($root,$plugin,$tpl,$log,$img,$tmp,$admin,$login){
		$sc=new checksys();
		$errors=array();
		$fail=false;
		if(!$sc->cs_checkpath($root)){
			$errors["root"][0]="notfound";
			$fail=true;
		}
		if(!$sc->cs_checkpath($plugin)){
			$errors["plugin"][0]="notfound";
			$fail=true;
		}
		if(!$sc->cs_checkpath($tpl)){
			$errors["tpl"][0]="notfound";
			$fail=true;
		}
		if(!$sc->cs_checkpath($log)){
			$errors["log"][0]="notfound";
			$fail=true;
		}
		if(!$sc->cs_checkpath($img)){
			$errors["img"][0]="notfound";
			$fail=true;
		}
		if(!$sc->cs_checkpath($tmp)){
			$errors["tmp"][0]="notfound";
			$fail=true;
		}
		if(!$sc->cs_checkpath($admin)){
			$errors["admin"][0]="notfound";
			$fail=true;
		}
		if(!$sc->cs_checkpath($login)){
			$errors["login"][0]="notfound";
			$fail=true;
		}
		$conds_web=array(array("3",array("w"),"in","warn"),array("3",array("r"),"notin","err"));
		$conds_noweb=array(array("3",array("w"),"in","err"),array("3",array("r"),"in","warn"));
		if(!$sc->cs_checkperms($root,$conds_web)){
			$errors["root"][1]="perms";	
			$fail=true;
		}
		if(!$sc->cs_checkperms($plugin,$conds_web)){
			$errors["plugin"][1]="perms";	
			$fail=true;		
		}
		if(!$sc->cs_checkperms($tpl,$conds_noweb)){
			$errors["tpl"][1]="perms";	
			$fail=true;		
		}
		if(!$sc->cs_checkperms($log,$conds_noweb)){
			$errors["log"][1]="perms";	
			$fail=true;		
		}
		if(!$sc->cs_checkperms($img,$conds_web)){
			$errors["img"][1]="perms";	
			$fail=true;		
		}
		if(!$sc->cs_checkperms($tmp,$conds_noweb)){
			$errors["tmp"][1]="perms";
			$fail=true;			
		}
		if(!$sc->cs_checkperms($admin,$conds_web)){
			$errors["admin"][1]="perms";
			$fail=true;			
		}
		if(!$sc->cs_checkperms($login,$conds_web)){
			$errors["login"][1]="perms";
			$fail=true;			
		}
		
		if(!$sc->cs_checkwebwrite($root)){
			$errors["root"][2]="write";
			$fail=true;
		}
		if(!$sc->cs_checkwebwrite($plugin)){
			$errors["plugin"][2]="write";
			$fail=true;
		}
		if(!$sc->cs_checkwebwrite($tpl)){
			$errors["tpl"][2]="write";
			$fail=true;
		}
		if(!$sc->cs_checkwebwrite($log)){
			$errors["log"][2]="write";
			$fail=true;
		}
		if(!$sc->cs_checkwebwrite($img)){
			$errors["img"][2]="write";
			$fail=true;
		}
		if(!$sc->cs_checkwebwrite($tmp)){
			$errors["tmp"][2]="write";
			$fail=true;
		}
		if(!$sc->cs_checkwebwrite($admin)){
			$errors["admin"][2]="write";
			$fail=true;
		}
		if(!$sc->cs_checkwebwrite($login)){
			$errors["login"][2]="write";
			$fail=true;
		}
		if($fail){
			return $errors;
		}
		else{
		 	$parse=array("root_path",$root,"plugin_path",$plugin,"template_path",$tpl
			            ,"log_path",$log,"img_path",$img,"tmp_path",$tmp,"admin_path",$admin,"login_path",$login);
			$this->config=$this->sc_appendarray($parse,$this->config);
		}
		return true;
	}
	function sc_setadldap($dn,$dc,$asuffix,$user,$pass,$ad){
		if($ad){
			@include_once($GLOBALS["MANDRIGO"]["CONFIG"]["ROOT_PATH"]."db{$GLOBALS["MANDRIGO"]["CONFIG"]["PATH"]}ad.class.".PHP_EXT);
			$ad=new ad();
			if(!$ad->ad_connect($dn,$dc,$asuffix,$user,$pass,true)){//you must connect with ssl
				return false;
			}
		}
		else{
			@include_once($GLOBALS["MANDRIGO"]["CONFIG"]["ROOT_PATH"]."db{$GLOBALS["MANDRIGO"]["CONFIG"]["PATH"]}ldap.class.".PHP_EXT);
			return false;
		}
		$soq=count($dc);
		$string="array(";
		for($i=0;$i<$soq;$i++){
			$string.="array(\"{$dc[$i][0]}\",\"{$dc[$i][1]}\")";
			if($i+1<$soq){
				$string.=",";
			}
		}
		$string.=");";
		$parse=array("ldapdn",$dn,"ldapdc",$string,"ldapacctsuffix",$asuffix,"ldapcuser",$user,"ldapcpass",$pass);
		$this->config=$this->sc_appendarray($parse,$this->config);	
		return true;		
	}
	function sc_setlang($lang,$html){
		$parse=array("lang",$lang,"html",$html);
		$this->config=$this->sc_appendarray($parse,$this->config);	
	}
	function sc_setos($os){
	 	if($os!="win"&&$os!="unix"){
			return false;
		}
		$parse=array("os_type",$os);
		$this->config=$this->sc_appendarray($parse,$this->config);	
		return true;
	}
	function sc_setlogging($lvl1=false,$lvl2=true,$archive="m_d"){
		if($archive!="m_d"&&$archive!="m_d_h"&&$archive!="m"&&$archive!="m_W"){
			return false;
		}
		if($lvl1){
			$lvl1="true";
		}
		else{
			$lvl1="false";
		}
		if($lvl2){
			$lvl2="true";
		}
		else{
			$lvl2="false";
		}
		$parse=array("loglvl1",$lvl1,"loglvl2",$lvl2,"logarchive",$archive);
		$this->config=$this->sc_appendarray($parse,$this->config);
		return true;	
	}
	function sc_sqldatabase($type,$host,$port,$socket,$user,$password,$databse,$prefix,$ssl=array(),$usessl=false){
		if($port&&$socket){
			$port="";
		}
		@include_once($GLOBALS["MANDRIGO"]["CONFIG"]["ROOT_PATH"]."db{$GLOBALS["MANDRIGO"]["CONFIG"]["PATH"]}".$type.".class.".PHP_EXT);
		$db=new db();
		if(!$this->db_connect($host,$port,$socket,$user,$password,$database,true,$usessl,$ssl)){
			return false;
		}
		else{
		 	if($usessl){
				$usessl="true";
			}
			else{
				$usessl="false";
			}
			$ssl_="array(\"KEY\"=>\"{$ssl["KEY"]}\",
						 \"CERT\"=>\"{$ssl["CERT"]}\",
						 \"CA\"=>\"{$ssl["CA"]}\",
						 \"CAPATH\"=>\"{$ssl["CAPATH"]}\");";
			$parse=array("sqltype",$type,"sqlhost",$host,"sqlport",$port,"sqlsocket",$socket,"sqluser",$user
						,"sqlpass",$pass,"sqldb",$databse,"sqlprefix",$prefix,"usessl",$usessl,"ssl",$ssl_);	
			$this->config=$this->sc_appendarray($parse,$this->config);
		}
		return true;
	}
	function sc_writefile($content,$path){
		if($GLOBALS["MANDRIGO"]["CONFIG"]["DEBUG_MODE"]){
			$f=fopen($path,"w");
		}
		else{
			@$f=fopen($path,"w");	
		}
		if(!$f){
			return false;
		}
		fwrite($f,$content);
		fclose($f);
		return true;
	}
    //
    //private function pg_mergearrays($a1,$a2)
    //
    //appends $a2 onto the end of $a1
    //
    //INPUTS:
    //$a1		-	array
    //$a2		-	array
    //
	//returns the combined array	
    function sc_appendarray($a1,$a2){
		$size1=count($a1);
		$size2=count($a2);
		$soq=$size1+$size2;
		for($i=$size1;$i<$soq;$i++){
			$a1[$i]=$a2[$i-($size1)];
		}
		return $a1;
	}	
	
	
}