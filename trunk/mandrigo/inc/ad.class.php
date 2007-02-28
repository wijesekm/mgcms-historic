<?php

/**********************************************************
    ad.class.php
	Last Edited By: Kevin Wijesekera
	Date Last Edited: 02/10/07
	
	Mandrigo CMS is Copyright (C) 2006-2007 the MandrigoCMS Group
	
	Based off of the adLDAP class Version 2.0 which is written by
	Scott Barnett (scott@wiggumworld.com) - http://adldap.sourceforge.net/
	
	adLDAP is Copyright (C) 2006 Scott Barnett

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

define('ADLDAP_NORMAL_ACCOUNT', 805306368);
define('ADLDAP_WORKSTATION_TRUST', 805306369);
define('ADLDAP_INTERDOMAIN_TRUST', 805306370);
define('ADLDAP_SECURITY_GLOBAL_GROUP', 268435456);
define('ADLDAP_DISTRIBUTION_GROUP', 268435457);
define('ADLDAP_SECURITY_LOCAL_GROUP', 536870912);
define('ADLDAP_DISTRIBUTION_LOCAL_GROUP', 536870913);


class ad{
	
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
    
	function ad_connect($dn,$dc,$a_suffix,$c_user,$c_pass,$ssl=true){
	 	
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
		//set some ldap options for talking to AD
		@ldap_set_option($this->conn, LDAP_OPT_PROTOCOL_VERSION, 3);
		@ldap_set_option($this->conn, LDAP_OPT_REFERRALS, 0);
		
		if(!$this->ad_binduser($c_user,$c_pass)){
			return false;
		}	
		return true;
	}
	function ad_binduser($b_user,$b_pass){
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
		else{
			return false;
		}		
		return true;
	}
	function ad_close(){
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
	
	//#############
    //Query Commands
    //#############	
    function ad_authenticate($username,$password,$revert=true){
		if(!$username||!$password){
			return false;
		}
		if(!$this->ad_binduser($username,$password)){
			return false;
		}
		if($revert){
			if(!$this->ad_binduser($this->config["CUSER"],$this->config["CPASS"])){
				return false;
			}
		}
		return true;
	}
	
	function user_ingroup($username,$group,$recursive=true,$real_pg=true){
	 	if(!$username||!$group||!$this->bind){
			return false;
		}

		//get a list of the groups
		$groups=$this->ad_usergroups($username,$recursive,$real_pg);
		
		//return true if the specified group is in the group list
		if (in_array($group,$groups)){ 
			return true; 
		}
		return true;
	}	
	function ad_usergroups($username,$recursive=true,$real_pg=true){
	 	if(!$username||!$this->bind){
			return false;		
		}

		//search the directory for their information
		$info=$this->ad_userinfo($username,array("memberof","primarygroupid"),$real_pg);
		$groups=$this->ad_nicenames($info[0]["memberof"]); //presuming the entry returned is our guy (unique usernames)

		if ($recursive){
			foreach($groups as $id => $group_name){
				$extra_groups=$this->ad_recursivegroups($group_name);
				$groups=array_merge($groups,$extra_groups);
			}
		}
		return $groups;
	}
	function ad_userinfo($username,$fields=false,$real_pg=true){
	 	if(!$username||!$this->bind){
			return false;		
		}
		$filter="samaccountname=".$username;
		
		$entries=$this->ad_getentries($filter,$fields);
		
		// AD does not return the primary group in the ldap query, we may need to fudge it
		if ($real_pg){
			$entries[0]["memberof"][]=$this->ad_groupcn($entries[0]["primarygroupid"][0]);
		} 
		else{
			$entries[0]["memberof"][]="CN=Domain Users,CN=Users,".$this->config["DN"];
		}
		$entries[0]["memberof"]["count"]++;
		return $entries;
	}
	function ad_groupinfo($group_name,$fields=false){
		if(!$group_name||!$this->bind){
			return false;
		}

		//escape nasty characters
		$group_name=str_replace("(","\(",$group_name);
		$group_name=str_replace(")","\)",$group_name);
		$group_name=str_replace("#","\#",$group_name);
		
		$filter="(&(objectCategory=group)(name=".$group_name."))";
		//echo ($filter."<br>");
		if(!$fields){ 
			$fields=array("member","memberof","cn","description","distinguishedname","objectcategory","samaccountname"); 
		}
		$entries = $this->ad_getentries($filter,$fields);

		return $entries;
	}
	function ad_recursivegroups($group){
		if (!$group){ 
			return false; 
		}

		$ret_groups=array();
		
		$groups=$this->ad_groupinfo($group,array("memberof"));
		$groups=$groups[0]["memberof"];

		if ($groups){
			$group_names=$this->ad_nicenames($groups);
			$ret_groups=array_merge($ret_groups,$group_names); //final groups to return
			
			foreach ($group_names as $id => $group_name){
				$child_groups=$this->ad_recursivegroups($group_name);
				$ret_groups=array_merge($ret_groups,$child_groups);
			}
		}

		return $ret_groups;
	}	
	function ad_allusers($search="*",$include_desc=false,$sorted=true){
	 	if(!$this->bind){
			return false;	
		}
		
		//perform the search and grab all their details
		$filter = "(&(objectClass=user)(samaccounttype=". ADLDAP_NORMAL_ACCOUNT .")(objectCategory=person)(cn=".$search."))";
		$fields=array("samaccountname","displayname");
		$entries = $this->ad_getentries($filter,$fields);

		$users_array = array();
		for($i=0; $i<$entries["count"]; $i++){
			if ($include_desc && strlen($entries[$i]["displayname"][0])>0){
				$users_array[$entries[$i]["samaccountname"][0]] = $entries[$i]["displayname"][0];
			} 
			elseif($include_desc){
				$users_array[$entries[$i]["samaccountname"][0]] = $entries[$i]["samaccountname"][0];
			} 
			else{
				array_push($users_array, $entries[$i]["samaccountname"][0]);
			}
		}
		if($sorted){ 
			asort($users_array);
		}
		return $users_array;
	}
	function ad_allgroups($search="*",$include_desc=false,$sorted=true){
	 	if(!$this->bind){
			return false;		
		}
		//perform the search and grab all their details
		$filter = "(&(objectCategory=group)(samaccounttype=". ADLDAP_SECURITY_GLOBAL_GROUP .")(cn=".$search."))";
		$fields=array("samaccountname","description");
		$entries = $this->ad_getentries($filter,$fields);

		$groups_array = array();		
		for ($i=0; $i<$entries["count"]; $i++){
			if($include_desc && strlen($entries[$i]["description"][0])> 0){
				$groups_array[$entries[$i]["samaccountname"][0]] = $entries[$i]["description"][0];
			} 
			elseif($include_desc){
				$groups_array[$entries[$i]["samaccountname"][0]] = $entries[$i]["samaccountname"][0];
			} 
			else{
				array_push($groups_array, $entries[$i]["samaccountname"][0]);
			}
		}
		if($sorted){ 
		 	asort($groups_array); 
		}
		return $groups_array;
	}
	//#################################
	//
	// PRIVATE FUNCTIONS
	//
	//#################################	
			
	function ad_groupcn($group){
		// coping with AD not returning the primary group
		// http://support.microsoft.com/?kbid=321360
		// for some reason it's not possible to search on primarygrouptoken=XXX
		// if someone can show otherwise, I'd like to know about it :)
		// this way is resource intensive and generally a pain in the @#%^
		
		if(!$group){
			return false;
		}
		$r=false;
		
		$filter="(&(objectCategory=group)(samaccounttype=". ADLDAP_SECURITY_GLOBAL_GROUP ."))";
		$fields=array("primarygrouptoken","samaccountname","distinguishedname");
		$entries=$this->ad_getentries($filter,$fields);
		for ($i=0; $i<$entries["count"]; $i++){
			if ($entries[$i]["primarygrouptoken"][0]==$gid){
				$r=$entries[$i]["distinguishedname"][0];
				$i=$entries["count"];
			}
		}
		return ($r);		
	}
	
	function ad_search($filter,$fields){
		if(!$filter){
			return false;
		}
		if(!$fields){ 
			$fields=array("samaccountname","mail","memberof","department","displayname","telephonenumber","primarygroupid"); 
		}
		if($GLOBALS["MANDRIGO"]["CONFIG"]["DEBUG_MODE"]){
			if(!$r=ldap_search($this->conn,$this->config["DN"],$filter,$fields)){
				return false;
			}
		}
		else{
			if(!(@$r=ldap_search($this->conn,$this->config["DN"],$filter,$fields))){
				return false;
			}
		}
		return $r;
	}
	function ad_getentries($filter,$fields){
	 
	 	if(!$search_id=$this->ad_search($filter,$fields)){
			return false;
		}
		
		if($GLOBALS["MANDRIGO"]["CONFIG"]["DEBUG_MODE"]){
			if(!$r=ldap_get_entries($this->conn,$search_id)){
				return false;
			}
		}
		else{
			if(!(@$r=ldap_get_entries($this->conn,$search_id))){
				return false;
			}
		}
		return $r;		
	}

	function ad_randcontroller($dcs){
		return $dcs[array_rand($dcs)];
	}
	function ad_nicenames($groups){

		$group_array=array();
		for($i=0; $i<$groups["count"]; $i++){ //for each group
			$line=$groups[$i];
			if(strlen($line)>0){ 
				//more presumptions, they're all prefixed with CN=
				//so we ditch the first three characters and the group
				//name goes up to the first comma
				$bits=explode(",",$line);
				$group_array[]=substr($bits[0],3,(strlen($bits[0])-3));
			}
		}
		return $group_array;	
	}	
}
