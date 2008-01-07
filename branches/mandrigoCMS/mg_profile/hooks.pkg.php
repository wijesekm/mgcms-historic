<?php
/**********************************************************
    hooks.pkg.php
    mg_profile ver 0.7.0
	Last Edited By: Kevin Wijesekera
	Date Last Edited: 07/17/07

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

class mg_profile_hook{
    function mg_profile_display_hook($i){
	
		include_once("display.pkg.php");
    
     	$profile=new mg_profile($i);
      	if(ereg('g',$GLOBALS['MANDRIGO']['VARS']['ID'])){
			$GLOBALS['MANDRIGO']['VARS']['ID']=substr($GLOBALS['MANDRIGO']['VARS']['ID'],1);	
			return $profile->pr_display('group');
		}
		else if(ereg('u',$GLOBALS['MANDRIGO']['VARS']['ID'])){
			$GLOBALS['MANDRIGO']['VARS']['ID']=substr($GLOBALS['MANDRIGO']['VARS']['ID'],1);
			return $profile->pr_display('user');
		}
		else{
			return $profile->pr_display('');	
		}
    }
    function mg_profile_vars_hook($i){
        return array();
    }
    function mg_profile_admin_hook($i){

    }
}