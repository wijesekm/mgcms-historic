<?php
/**********************************************************
    clean_functions.ini.php
	Last Edited By: Kevin Wijesekera
	Date Last Edited: 02/11/07

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
//function clean_var($string,$method)
//
//Runs an input though regx to check to see if its valid
//
//INPUTS:
//$string	-	input string
//$method	-	clean method
//
//returns the cleaned string
function clean_var($string,$method){
	switch($method){
		case "num":
			return (eregi("^[0-9]+$",$string))?$string:BAD_DATA;
		break;
		case "sesid":
			return (eregi("^[0-9a-z]+$",$string))?$string:BAD_DATA;
		break;	
		case "uid":  
			return (eregi("^[0-9]+$",$string))?$string:BAD_DATA;
		break;
		case "email":
			return (eregi("^[a-z0-9._-]+@[a-z.0-9-]+\.[a-z.]{2,5}$",$string))?$string:BAD_DATA;
		break;
		case "id":
			return (eregi("^[a-z]{0,1}[0-9]+$",$string))?$string:BAD_DATA;
		break;
		case "page":
			return (eregi("^[a-z0-9._-]+$",$string))?$string:BAD_DATA;
		break;
		case "action":
			return (eregi("^[a-z]+$",$string))?$string:BAD_DATA;
		break;
		case "password":
			return (eregi("^[a-z0-9._-]+$",$string))?$string:BAD_DATA;
		break;
		case "name":
			return (eregi("^[[:space:]a-z.,]+$",$string))?$string:BAD_DATA;
		break;
		case "text":
			return (!eregi("[<|>]",$string))?$string:BAD_DATA;
		break;
		case "username":
			return (eregi("^[a-z0-9._-]+$",$string))?$string:BAD_DATA;
		break;
		case "url":
			return (eregi("[<|>[|]{|}]",$string))?BAD_DATA:$string;
		break;
		case "feed":
			return (eregi("^[a-z0-9.]+$",$string))?$string:BAD_DATA; 
		break;
	};
}