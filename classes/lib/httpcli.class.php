<?php
/**
 * This file provides a class for sending HTTP requests
 * to other servers
 *
 * @package OMNI CMS\Libraries
 *
 * @author Kevin Wijesekera <kwijesekera@munciepower.com>
 * @copyright 2021 Muncie Power Products
 * @license GPL
 *
 */
if(!defined('STARTED')){
    die();
}

/**
 * CMS HTTP Client class.  This class provides an interface for
 * sending/receiving http data to other servers
 *
 * @author Kevin Wijesekera <kwijesekera@munciepower.com>
 *
 */
class httpcli{

    /** @var string[] Array of HTTP option data to be included in request */
    private $opts;

    /**
     * This is the class constructor which sets up the standard HTTP
     * request options, sets up the SSL cert if supplied, and checks
     * to make sure CURL is loaded.
     *
     * @param string $cert Path to SSL cert file or null to use pre-loaded certs
     */
    public function __construct($cert = null, $noverif=false){
        if(!function_exists('curl_init')){
            trigger_error('(httpcli): CURL library not loaded',E_USER_ERROR);
            return;
        }

        $this->opts = array(
            CURLOPT_RETURNTRANSFER  => true,
            CURLOPT_POST            => false,
            CURLOPT_TIMEOUT         => 15,
            CURLOPT_CONNECTTIMEOUT  => 15,
            CURLOPT_FOLLOWLOCATION  => true,
            CURLOPT_HEADER          => 0,
            CURLOPT_IPRESOLVE       => CURL_IPRESOLVE_V4,
            CURLOPT_SSL_VERIFYPEER  => $noverif?false:true,
            CURLOPT_SSL_VERIFYHOST  => $noverif?0:2,
            CURLOPT_FRESH_CONNECT   => true,
            CURLOPT_USERAGENT       => 'MG CURL',
            CURLOPT_HTTP_VERSION    => CURL_HTTP_VERSION_1_1
        );
        if(!empty($cert)){
            if(!is_file($cert)){
                trigger_error('(httpcli): Invalid root certificate: '.$cert,E_USER_NOTICE);
            }
            else{
                $this->opts[CURLOPT_CAINFO] = $cert;
            }
        }
    }

    /**
     * This function sets up authentication to be used on all requets going forward
     *
     * @param int $type Authentication type (see CURLOPT_HTTPAUTH)
     * @param string $username Username to use for auth
     * @param string $password Password to use for auth
     */
    public function set_auth($type, $username, $password){
        $this->opts[CURLOPT_HTTPAUTH] = $type;
        $this->opts[CURLOPT_USERPWD] = $username.':'.$password;
    }

    /**
     * This function clears the authentication headers
     *
     */
    public function clear_auth(){
        unset($this->opts[CURLOPT_HTTPAUTH]);
        unset($this->opts[CURLOPT_USERPWD]);
    }

    /**
     * This function sets up extra headers
     *
     * @param string[] $headers Array of header data as HEADER => VALUE
     */
    public function set_headers($headers){
        $insert = array();
        foreach($headers as $key=>$val){
            $insert[] = $key.': '.$val;
        }
       if(isset($this->opts[CURLOPT_HTTPHEADER])){
           $insert = array_merge($this->opts[CURLOPT_HTTPHEADER],$insert);
       }
       $this->opts[CURLOPT_HTTPHEADER] = $insert;
    }

    /**
     * This function fetches data from a URL
     *
     * @param string $url URL to fetch data from
     * @param string[]|string $post Array of post data as var => value or false if GET request or raw string for JSON
     *
     * @return string[]|boolean False on failure or response data containing the following keys:
     *                  .code HTTP response code
     *                  .res HTTP response
     *                  .time Total HTTP query time
     *                  .ip IP of remote server
     */
    public function fetch($url, $post=false){

        $ch = curl_init();
        $opts = $this->opts;
        $opts[CURLOPT_URL] = $url;

        if($post){
            $opts[CURLOPT_POST] = true;
            $opts[CURLOPT_POSTFIELDS] = $post;
            if(is_string($post) && ($post[0] == '{' || $post[0] == '[')){
                $opts[CURLOPT_HTTPHEADER][] = 'Content-Type: application/json';
            }
        }

        // Set the appropriate headers
        if(!curl_setopt_array($ch, $opts)){
            trigger_error('(httpcli): Could not set curl options',E_USER_WARNING);
            return false;
        }

        $response = array();
        $response['res'] = curl_exec($ch);
        $response['code'] = 0;
        $res = curl_getinfo( $ch );
        $response['content_type'] = empty($res['content_type'])?'':$res['content_type'];
        $response['code'] = $res['http_code'];
        $response['time'] = $res['total_time'];
        $response['ip'] = $res['primary_ip'];
        $response['url'] = $res['url'];
        if(empty($res)){
            trigger_error('(httpcli): Server response is empty',E_USER_WARNING);
            return false;
        }

        return $response;
    }

}
