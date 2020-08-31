<?php

/**
 * @file		api.class.php
 * @author 		Kevin Wijesekera
 * @copyright 	2020
 * @edited		8/28/2020

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

class page{

    private $content;
    private $error;

    public function __construct(){
        if(!isset($_SERVER['SERVER_SIGNATURE'])){
            $_SERVER['SERVER_SIGNATURE']='';
        }
        $GLOBALS['MG']['PAGE']['VARS']['NO']='';
        if(!$GLOBALS['MG']['PAGE']['ACTION_HOOK']){
            return;
        }
        $GLOBALS['MG']['PAGE']['VARS']=array(
                'URI'=>$GLOBALS['MG']['SITE']['URI'],
                'SERVER_NAME'=>$_SERVER['SERVER_NAME'],
                'SSL'=>(isset($_SERVER['HTTPS'])&&$_SERVER['HTTPS']!='off')?'1':'0',
                'REQUEST_URI'=>$_SERVER['REQUEST_URI'],
                'SERVER_SOFTWARE'=>$_SERVER['SERVER_SOFTWARE'],
                'SERVER_NAME'=>$_SERVER['SERVER_NAME'],
                'SERVER_SIGNATURE'=>$_SERVER['SERVER_SIGNATURE'],
                'DEFAULT_ACT'=>$GLOBALS['MG']['SITE']['DEFAULT_ACT'],
                'SERVER_TZ'=>$GLOBALS['MG']['SITE']['TZ'],
                'URLTYPE'=>$GLOBALS['MG']['SITE']['URLTYPE'],
                'INDEX_NAME'=>$GLOBALS['MG']['SITE']['INDEX_NAME'],
                'LANGUAGE'=>$GLOBALS['MG']['LANG']['NAME'],
                'SERVER_TIME'=>date($GLOBALS['MG']['SITE']['TIME_FORMAT'],$GLOBALS['MG']['SITE']['TIME']),
                'SERVER_DATE'=>date($GLOBALS['MG']['SITE']['DATE_FORMAT'],$GLOBALS['MG']['SITE']['TIME']),
                'COPYRIGHT_YEAR'=>date('o',$GLOBALS['MG']['SITE']['TIME']),
                'USER_UID'=>$GLOBALS['MG']['USER']['UID'],
                'USER_SESSION'=>$GLOBALS['MG']['COOKIE']['USER_SESSION'],
                'USER_NAME'=>implode(' ',$GLOBALS['MG']['USER']['NAME']),
                'USER_EMAIL'=>$GLOBALS['MG']['USER']['EMAIL'],
                'USER_BANNED'=>$GLOBALS['MG']['USER']['BANNED'],
                'USER_TZ'=>$GLOBALS['MG']['USER']['TZ'],
                'USER_NOAUTH'=>$GLOBALS['MG']['USER']['NOAUTH'],
                'USER_TIME'=>date($GLOBALS['MG']['SITE']['TIME_FORMAT'],$GLOBALS['MG']['USER']['TIME']),
                'USER_DATE'=>date($GLOBALS['MG']['SITE']['DATE_FORMAT'],$GLOBALS['MG']['USER']['TIME']),
                'USER_GROUPS'=>implode(';',$GLOBALS['MG']['USER']['GROUPS']),
                'PAGE_PATH'=>$GLOBALS['MG']['PAGE']['PATH'],
                'PAGE_CREATOR'=>$GLOBALS['MG']['PAGE']['CREATEDBY'],
                'PAGE_CREATED_DATE'=>date($GLOBALS['MG']['SITE']['DATE_FORMAT'],$GLOBALS['MG']['PAGE']['CREATED']),
                'ACL_ADMIN'=>(mg_checkACL($GLOBALS['MG']['PAGE']['PATH'],'admin'))?'1':'0',
                'ACL_MODIFY'=>(mg_checkACL($GLOBALS['MG']['PAGE']['PATH'],'modify'))?'1':'0',
                'ACL_WRITE'=>(mg_checkACL($GLOBALS['MG']['PAGE']['PATH'],'write'))?'1':'0',
                'ACL_READ'=>(mg_checkACL($GLOBALS['MG']['PAGE']['PATH'],'read'))?'1':'0'
        );

        $this->content='';
        $this->error=false;
    }

    public function page_generate($v=false){
        if(!$GLOBALS['MG']['PAGE']['PATH']){
            trigger_error('(PAGE): No content.',E_USER_WARNING);
            return '404:Resource Not Found';
        }
        if(empty($GLOBALS['MG']['PAGE']['ACTION_HOOK'])){
            trigger_error('(PAGE): No content hooks.',E_USER_WARNING);
            $this->page_error('404');
            return $this->content;

        }

        if(!$this->page_execHook()){
            trigger_error('(PAGE): Could not execute API hook.',E_USER_ERROR);
            $this->page_error('500');
        }

        return $this->content;
    }

    private function page_execHook(){
        if(!class_exists($GLOBALS['MG']['PAGE']['PACKAGES'])){
            trigger_error('(PAGE): No class found for api: '.$GLOBALS['MG']['PAGE']['PACKAGES'],E_USER_ERROR);
            return false;
        }

        $obj = new $GLOBALS['MG']['PAGE']['PACKAGES']();

        if(!is_object($obj)){
            trigger_error('(PAGE): Could not create class: '.$GLOBALS['MG']['PAGE']['PACKAGES'],E_USER_ERROR);
            return false;
        }
        $GLOBALS['MG']['PAGE']['ACTION_HOOK'] = 'api_'.$GLOBALS['MG']['PAGE']['ACTION_HOOK'];

        if(!method_exists($obj,$GLOBALS['MG']['PAGE']['ACTION_HOOK'])){
            trigger_error('(PAGE): No function in class found: '.$GLOBALS['MG']['PAGE']['ACTION_HOOK'],E_USER_ERROR);
            return false;
        }
        $reflection = new ReflectionMethod($obj,$GLOBALS['MG']['PAGE']['ACTION_HOOK']);
        if(!$reflection->isPublic()){
            trigger_error('(PAGE): Requested hook is not Public: '.$GLOBALS['MG']['PAGE']['ACTION_HOOK'],E_USER_ERROR);
            return false;
        }

        $this->content = $obj->{$GLOBALS['MG']['PAGE']['ACTION_HOOK']}();
        $this->page_error($this->content);
        return true;
    }

    private function page_error($content = ''){
        if(!empty($content)){
            $this->content = $content;
        }
        $ecode = substr($this->content,0,3);
        switch((int)$ecode){
            case 200:
                if(strlen($content) == 3){
                    $this->content .= ':success';
                }
                mginit_errorHandler(E_ACCESS,'Page Access 200 '.$GLOBALS['MG']['PAGE']['PATH'],'','','');
                break;
            case 500:
                if(strlen($content) == 3){
                    $this->content .= ':Internal Server Error';
                }
                mginit_errorHandler(E_ACCESS_ERR,'Internal Server Error 500 '.substr($content,4),'','','');
                break;
            case 404:
                if(strlen($content) == 3){
                    $this->content .= ':Resource Not Found';
                }
                mginit_errorHandler(E_ACCESS_ERR,'Resource Not Found 404 '.substr($content,4),'','','');
                break;
            case 403:
                if(strlen($content) == 3){
                    $this->content .= ':Forbidden';
                }
                mginit_errorHandler(E_ACCESS_ERR,'Forbidden 403 '.substr($content,4),'','','');
                break;
            case 401:
                if(strlen($content) == 3){
                    $this->content .= ':Authorization Requred';
                }
                mginit_errorHandler(E_ACCESS_ERR,'Authorization Required 401 '.substr($content,4),'','','');
                break;
            default:
                mginit_errorHandler(E_ACCESS,'Page Access 200 API:'.$GLOBALS['MG']['PAGE']['PATH'],'','','');
                break;
        };
    }

}