<?php

/**
 * @file		cert.class.php
 * @author 		Kevin Wijesekera
 * @copyright 	2020
 * @edited		8/31/2020

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

class cert extends auth{

    private $remote_server;
    private $cert_path;

    public function __construct(){
        $this->remote_server = false;
    }

    public function auth_setRemoteServer($rmt, $cert_path=false){
        $this->remote_server = $rmt;
        $this->cert_path = $cert_path;
    }

    final public function auth_authenticate($username,$password){
        return true;
    }

    final public function auth_sessionStart($uid, $expires){
        $cdta=array(
                'SECURE'=>false,
                'PATH'=>'',
                'DOM'=>'',
                'EXPIRES'=>$expires
        );

        $ses= new session($GLOBALS['MG']['USER']['TIME']);
        if(!$ses_tok = $ses->session_start($uid,$cdta,true,1)){
            trigger_error('(CERTAUTH): Could not start session',E_USER_ERROR);
            return false;
        }

        if($this->remote_server){
            $ch = curl_init();
            $opts = array(
                    CURLOPT_URL             => $this->remote_server,
                    CURLOPT_RETURNTRANSFER  => true,
                    CURLOPT_POST            => 2,
                    CURLOPT_POSTFIELDS      => 'user='.$uid.'&token='.$ses_tok,
                    CURLOPT_TIMEOUT         => 10,
                    CURLOPT_CONNECTTIMEOUT  => 10,
                    CURLOPT_FOLLOWLOCATION  => true,
                    CURLOPT_HEADER          => 0,
                    CURLOPT_IPRESOLVE       => CURL_IPRESOLVE_V4,
                    CURLOPT_SSL_VERIFYPEER  => true,
                    CURLOPT_SSL_VERIFYHOST  => 2,
                    CURLOPT_FRESH_CONNECT   => true,
                    CURLOPT_USERAGENT       => 'MG CURL',
                    CURLOPT_HTTP_VERSION    => CURL_HTTP_VERSION_1_1
            );

            // Set the appropriate content-type.
            curl_setopt_array($ch, $opts);
            if($this->cert_path){
                curl_setopt($ch,CURLOPT_CAINFO,$GLOBALS['MG']['CFG']['PATH']['ROOT'].$this->cert_path);
            }
            $response = curl_exec($ch);
            $info = curl_getinfo( $ch );
            $code = substr($response,0,3);
            if($code == '200'){
                return substr($response,4);
            }
            else{
                return false;
            }
        }
        else{
            //fix cert if its in compact format
            if(strpos($GLOBALS['MG']['USER']['PASSWORD'],'-----') === false){
                //fix cert
                $GLOBALS['MG']['USER']['PASSWORD'] = chunk_split($GLOBALS['MG']['USER']['PASSWORD'],64,"\n");
                $GLOBALS['MG']['USER']['PASSWORD'] = "-----BEGIN PUBLIC KEY-----\n".$GLOBALS['MG']['USER']['PASSWORD']."-----END PUBLIC KEY-----\n";
            }

            $salt = $this->auth_randstring(6);
            $pk1 = openssl_pkey_get_public($GLOBALS['MG']['USER']['PASSWORD']);
            if($pk1 === false){
                trigger_error('(CERTAUTH): Public key is invalid for user',E_USER_ERROR);
                return false;
            }
            $crypt = '';
            if(!openssl_public_encrypt('T='.$ses_tok,$crypt,$pk1)){
                trigger_error('(CERTAUTH): Could not encrypt data',E_USER_ERROR);
                return false;
            }
            //decrypt with privkey
            //$ echo $data | base64 --decode | openssl rsautl -decrypt -passin pass:abcdefg -inkey mptest.key

            return base64_encode($crypt);
        }
        return false;
    }

    final public function auth_changePass($uid,$newPass){
        return false;
    }

    final public function auth_getAutoReg($uid,$password){
        return false;
    }

}