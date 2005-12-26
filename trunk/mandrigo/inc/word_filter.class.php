<?php
/**********************************************************
    page.class.php
	Last Edited By: Kevin Wijesekera
	Date Last Edited: 12/24/05

	Copyright (C) 2005  Kevin Wijesekera

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
class word_filter{

    var $b_list;
    var $rx_list;
    var $r_list;

    function word_filter($ban_list,$replace_list=""){
        $this->b_list=$ban_list;
        $this->regex_gen();
        $this->r_list=$replace_list;
    }
    function regex_gen(){
        $sol=count($this->b_list);
        for($i=0;$i<$sol;$i++){
            $no_start=false;
            $no_end=false;
            $tmp=explode("*",$this->b_list[$i]);
            $sot=count($tmp);
            if($sot==1){
                $this->rx_list[$i]="^".$tmp[0]."+$";
            }
            else if($sot==3){
                $this->rx_list[$i]=$tmp[1];
            }
            else if($sot==2){
                if(!$tmp[0]){
                    $this->rx_list[$i]=$tmp[1]."+$";
                }
                else{
                    $this->rx_list[$i]="^".$tmp[0];
                }
            }
        }
    }
    function validate($string){
        $sol=count($this->rx_list);
        for($i=0;$i<$sol;$i++){
            if(eregi($this->rx_list[$i],$string)){
                $string="BAD_DATA";
                return $string;
            }
        }
        return $string;
    }
    function censor($string){
        $sol=count($this->rx_list);
        for($i=0;$i<$sol;$i++){
            if(eregi($this->rx_list[$i],$string)){
                $string=eregi_replace($this->rx_list[$i],$this->r_list[$this->b_list[$i]],$string);
            }
        }
        return $string;
    }

}

?>
