<?php

/**
 * @file		clean.conf.php
 * @author 		Kevin Wijesekera
 * @copyright 	2009
 * @edited		2-27-2009
 
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

$GLOBALS['MG']['CLEAN']=array(
	'file_path'=>array('!eregi',"[<|>?%\*\:\|\"]"),
	'url'=>array('preg_match',"/^[a-zA-Z]+[:\/\/]+[A-Za-z0-9\-_]+\\.+[A-Za-z0-9\.\/%&=\?\-_]+$/i"),
	'uri'=>array('preg_match',"/^[A-Za-z0-9\-_]+\\.+[A-Za-z0-9\.\/%&=\?\-_]+$/i"),
	'file_name'=>array('!eregi',"[<|>?%\*\:\|\"\/\\]"),
	'e-mail'=>array('eregi',"^[a-z\\+0-9._-]+@[a-z.0-9-]+\.[a-z.]{2,5}$"),
	'id'=>array('eregi',"^[0-9a-z]+$"),
	'ip'=>array('preg_match',"/^([0-9]{1,3}[.]){1,3}[0-9]{1,3}$/"),
	'prefix'=>array('eregi',"^[a-z0-9]{1,15}_$"),
	'mac'=>array('preg_match',"/^([0-9a-fA-F]{2}[:-]){5}[0-9a-fA-F]{2}$/"),
	'domain'=>array('preg_match',"/^([a-zA-Z0-9_-]+\.){1,3}+([a-zA-Z]{2,3}+\.){0,1}+[a-zA-Z]{2,3}$/"),
	'letter'=>array('eregi',"^[a-z]+$"),
	'groupname'=>array('eregi',"^[a-z0-9._-]+$"),
	'username'=>array('eregi',"^[a-z0-9\\@._-]+$"),
	'page_title'=>array('eregi',"^[[:space:]a-z0-9_-]+$"),
	'title'=>array('eregi',"^[[:space:]a-z0-9_-|:,\.]+$"),
	'name'=>array('eregi',"^[[:space:]a-z.,]+$"),
	'lang-code'=>array('eregi',"^[a-z]{1,3}-[a-z]{1,3}$")
);