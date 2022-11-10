<?php

abstract class mail_transport{

    protected $config;

    public function __construct($options){
        $this->config = $options;
    }

    abstract public function send($envelope);

    protected function isShellSafe($string){
        // Future-proof
        if(escapeshellcmd($string) !== $string or !in_array(escapeshellarg($string), [
            "'$string'","\"$string\""
        ])){
            return false;
        }

        $length = strlen($string);

        for($i = 0; $i < $length; ++$i){
            $c = $string[$i];

            // All other characters have a special meaning in at least one common shell, including = and +.
            // Full stop (.) has a special meaning in cmd.exe, but its impact should be negligible here.
            // Note that this does permit non-Latin alphanumeric characters based on the current locale.
            if(!ctype_alnum($c) && strpos('@_-.', $c) === false){
                return false;
            }
        }
        return true;
    }
}

class envelope{

    public $sender;
    public $to;
    public $bcc;
    public $cc;
    public $header;
    public $body;
    public $subject;

}