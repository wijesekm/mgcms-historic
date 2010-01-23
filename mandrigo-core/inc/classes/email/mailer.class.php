<?php

/**
 * @file                phpmailer.class.php
 * @author              Kevin Wijesekera
 * @copyright   		2008
 * @edited              9-7-2008
 
 * Based of the PHPMailer library
 * ------------------------------
 * Copyright (c) 2004-2007, Andy Prevost. All Rights Reserved.
 * Copyright (c) 2001-2003, Brent R. Matzelle
 * License: Distributed under the Lesser General Public License (LGPL)
 * http://www.gnu.org/copyleft/lesser.html
 * ------------------------------
 
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

class phpmailer{
	
	private $mailer;

	public function __construct($cfg=false){
		$keys=array_keys($cfg);
		switch($keys){
			case 'priority':
			
			break;
		}
	}


}