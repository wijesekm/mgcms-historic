<?php

/**
 * @file		mgtime.class.php
 * @author 		Kevin Wijesekera
 * @copyright 	2008
 * @edited		6-8-2008
 
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

class mgtime{
	
	private $server;
	private $client;
	
	public function __construct($tzs,$tzc){
		if(!$tzc){
			$tzc=$tzs;
		}
		$dtzServer = new DateTimeZone($tzs);
		$dtzClient = new DateTimeZone($tzc);
		$dtServer = new DateTime("now", $dtzServer);
		$dtClient = new DateTime("now", $dtzClient);
		$serverOff = $dtzServer->getOffset($dtServer);
		$clientOff = $dtzClient->getOffset($dtClient);
		$this->server=time();
		$this->client=$this->server-($serverOff-$clientOff);
		date_default_timezone_set($tzc);
	}
	
	public function time_server(){
		return $this->server;
	}
	public function time_client(){
		return $this->client;
	}
}