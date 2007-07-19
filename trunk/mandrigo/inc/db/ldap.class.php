<?php
/**********************************************************
    ldap.class.php
	Last Edited By: Kevin Wijesekera
	Date Last Edited: 07/17/07
	
	Mandrigo CMS is Copyright (C) 2006-2007 the MandrigoCMS Group

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

define("MLDAP_PROTOCOL_VERSION",3)

class ldap{
	
	var $conn;
	var $bind;
	var $config;
	
	//#################################
	//
	// PUBLIC FUNCTIONS
	//
	//#################################	
	
	//#############
    //Connection Commands
    //#############
    
	function ldap_connect($dn,$dc,$a_suffix,$c_user,$c_pass,$ssl=true){
	 	if(!$dn||!$dc){
			return false;
		}
		
		$dc=$this->ad_randcontroller($dc);
		
		if(!is_array($dc)){
			$dc=array($dc);
		}
		
		if(!$dc[1]){
			$dc[1]="389";
		}

	 	$this->config=array("DN"=>$dn,"DC"=>$dc,"ASUFF"=>$a_suffix,
							"CUSER"=>$c_user,"CPASS"=>$c_pass);
		
		if($GLOBALS["MANDRIGO"]["CONFIG"]["DEBUG_MODE"]){
			if($ssl){
				if(!($this->conn = ldap_connect("ldaps://".$dc[0],$dc[1]))){
					return false;
				}
			}
			else{
				if(!($this->conn = ldap_connect($dc[0],$dc[1]))){
					return false;
				}			
			}	
		}
		else{
			if($ssl){
				if(!(@$this->conn = ldap_connect("ldaps://".$dc[0],$dc[1]))){
					return false;
				}
			}
			else{
				if(!(@$this->conn = ldap_connect($dc[0],$dc[1]))){
					return false;
				}			
			}		
		}
		@ldap_set_option($this->conn, LDAP_OPT_PROTOCOL_VERSION, MLDAP_PROTOCOL_VERSION);
		return true;		
	}
	function ldap_binduser($b_user="",$b_pass="",$auth=false){
		if($b_user&&$b_pass){
		 	if($GLOBALS["MANDRIGO"]["CONFIG"]["DEBUG_MODE"]){
		 		if(!$this->bind=ldap_bind($this->conn,$b_user.$this->config["ASUFF"],$b_pass)){
					return false;
				}
			}
		 	else{
				if(!(@$this->bind=ldap_bind($this->conn,$b_user.$this->config["ASUFF"],$b_pass))){
					return false;
				}
			} 
		}
		else if(!$auth){
			return $this->ad_binduser($this->config["CUSER"],$this->config["CPASS"],true);
		}		
		else{
			return false;
		}
		return true;
	}	
	function ldap_close(){
        if($this->conn){
            if($GLOBALS["MANDRIGO"]["CONFIG"]["DEBUG_MODE"]){
                ldap_close($this->conn);
            }
            else{
                if(!(@ldap_close($this->conn))){
					return false;
				}
            }
        }
        return true;
	}
}