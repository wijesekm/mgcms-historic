<?php
/**********************************************************
    hooks.pkg.php
    news ver 1.0
	Last Edited By: Kevin Wijesekera
	Date Last Edited: 02/09/05

	Copyright (C) 2006 Kevin Wijesekera

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
    die("<html><head>
            <title>Forbidden</title>
        </head><body>
            <h1>Forbidden</h1><hr width=\"300\" align=\"left\"/>\n<p>You do not have permission to access this file directly.</p>
        </html></body>");
}

class news_hook{
  	var $pparse_vars;
    
	function news_display_hook(&$sql,$i){
	  	$cur_news=new news_display($sql);
	  	$string="";
	  	if($GLOBALS["HTTP_GET"]["IS_FEED"]){
			if(!$cur_news->nd_load($i,$GLOBALS["HTTP_GET"]["FEED_TYPE"])){
				return false;
			}
			$string=$cur_news->nd_displayfeed($i);
			$this->pparse_vars=$cur_news->nd_returnvars();			
		}
		else if($GLOBALS["HTTP_GET"]["ID"]!=DEFAULT_ID){
			if(!$cur_news->nd_load($i,TPL_NEWS_SINGLE)){
				return false;
			}
			if($GLOBALS["HTTP_GET"]["ACTION"]=="p"){
				$string=$cur_news->nd_addcomment($i);	
			}
			else{
				$string=$cur_news->nd_displaypost($i);
			}
			$this->pparse_vars=$cur_news->nd_returnvars();	
		}
		else{
			if(!$cur_news->nd_load($i,TPL_NEWS)){
				return false;
			}
			$string=$cur_news->nd_displayfull($i);
			$this->pparse_vars=$cur_news->nd_returnvars();
		}
		return $string;
    }
    function news_vars_hook(&$sql,$i){
		return $this->pparse_vars;
    }
    function news_admin_hook(&$sql,$i){

    }
}

?>
