<?php
/**********************************************************
    user.globals.php
	Last Edited By: Kevin Wijesekera
	Date Last Edited: 02/27/07

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

//
//Checks to see if the user is authenticated.  If the user is then the current user becomes that users id.
//If not the userid is set to 1 for the 'Guest' account.
//
$ses=new session();
$ses->se_load($GLOBALS["MANDRIGO"]["VARS"]["COOKIE_USER"]);
$auth=$ses->se_check($GLOBALS["MANDRIGO"]["VARS"]["COOKIE_USER"],$GLOBALS["MANDRIGO"]["VARS"]["COOKIE_SESSION"]);
if($auth){
	$GLOBALS["MANDRIGO"]["CURRENTUSER"]["UID"]=$ses->se_uid();
}
else{
	$GLOBALS["MANDRIGO"]["CURRENTUSER"]["UID"]=1;	
}
$ses=false;

$act=new account($GLOBALS["MANDRIGO"]["CURRENTUSER"]["UID"]);

if($act->ac_id()!=$GLOBALS["MANDRIGO"]["CURRENTUSER"]["UID"]){
	$GLOBALS["MANDRIGO"]["CURRENTUSER"]["UID"]=1;
	$act=new account($GLOBALS["MANDRIGO"]["CURRENTUSER"]["UID"]);
}

//
//Now we will set the users data
//
$GLOBALS["MANDRIGO"]["CURRENTUSER"]=$act->ac_userdata();
$GLOBALS["MANDRIGO"]["CURRENTUSER"]["IP"]=(!empty($HTTP_SERVER_VARS['REMOTE_ADDR']))?$HTTP_SERVER_VARS['REMOTE_ADDR']:((!empty($HTTP_ENV_VARS['REMOTE_ADDR']))?$HTTP_ENV_VARS['REMOTE_ADDR']:getenv('REMOTE_ADDR'));
$GLOBALS["MANDRIGO"]["CURRENTUSER"]["UAGENT"]=(!empty($HTTP_SERVER_VARS['HTTP_USER_AGENT']))?$HTTP_SERVER_VARS['HTTP_USER_AGENT']:((!empty($HTTP_ENV_VARS['HTTP_USER_AGENT']))?$HTTP_ENV_VARS['HTTP_USER_AGENT']:getenv('HTTP_USER_AGENT'));
if(!$GLOBALS["MANDRIGO"]["CURRENTUSER"]["IP"]){
	$GLOBALS["MANDRIGO"]["CURRENTUSER"]["IP"]="000.000.000.000";
}
if(!$GLOBALS["MANDRIGO"]["CURRENTUSER"]["UAGENT"]){
	$GLOBALS["MANDRIGO"]["CURRENTUSER"]["UAGENT"]="NA";
}
$tz=$act->ac_timezone();
$GLOBALS["MANDRIGO"]["CURRENTUSER"]["TZ"]=$tz["TZ"];
$GLOBALS["MANDRIGO"]["CURRENTUSER"]["DST"]=$tz["DST"];
$GLOBALS["MANDRIGO"]["CURRENTUSER"]["LANGUAGE"]=$act->ac_language();
$GLOBALS["MANDRIGO"]["CURRENTUSER"]["AUTHENTICATED"]=$auth;
$GLOBALS["MANDRIGO"]["CURRENTUSER"]["GROUPS"]=$act->ac_groups();
$act="";
$tz="";
