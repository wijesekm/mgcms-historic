<?php

/*!
 * @file        oauth.class.php
 * @author      Kevin Wijesekera
 * @copyright   2015
 * @edited      9-5-2015

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

class oauth extends auth{

    final public function auth_authenticate($username,$password){
        return false;
    }

    final public function auth_canChangePass(){
        return false;
    }

    final public function auth_changePass($uid,$newPass){
        return false;
    }

    final public function auth_getAutoReg($uid,$password){
        $cli = new httpcli(false,true);
        $cli->set_headers(array(
            'Accept'=>'application/json',
            'Authorization'=>'Bearer '.$password
        ));
        $data = $cli->fetch($GLOBALS['MG']['SITE']['OAUTH_URI'],true);
        if(empty($data) || $data['code'] != 200 || $data['content_type'] != 'application/json'){
            trigger_error('(OAUTH): Could not fetch data from server based on token',E_USER_WARNING);
            return false;
        }
        $data = json_decode($data['res'],true);
        if(empty($data) || $data['status'] != 200 || $data['valid_token'] != '1'){
            return false;
        }

        return array(
            'NAME'=>implode(';',explode(' ',$data['name'])),
            'EMAIL'=>$data['email'],
            'COMPANY'=>'Muncie Power Products, Inc.',
            'ADDRESS'=>$data['address'],
            'ABOUT'=>$data['title'].' - '.$data['department'],
            'PHONE'=>$data['phone'],
            'LOCATION'=>''
        );
    }

}