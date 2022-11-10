<?php

class phpmail_transport extends mail_transport{

    public function send($envelope){

        $params = null;
        //This sets the SMTP envelope sender which gets turned into a return-path header by the receiver
        if (!empty($envelope->sender)) {
            //A space after `-f` is optional, but there is a long history of its presence
            //causing problems, so we don't use one
            //Exim docs: http://www.exim.org/exim-html-current/doc/html/spec_html/ch-the_exim_command_line.html
            //Sendmail docs: http://www.sendmail.org/~ca/email/man/sendmail.html
            //Qmail docs: http://www.qmail.org/man/man8/qmail-inject.html
            //Example problem: https://www.drupal.org/node/1057954
            // CVE-2016-10033, CVE-2016-10045: Don't pass -f if characters will be escaped.
            if ($this->isShellSafe($envelope->sender)) {
                $params = sprintf('-f%s', $envelope->sender);
            }
        }
        if (!empty($envelope->sender)) {
            $old_from = ini_get('sendmail_from');
            ini_set('sendmail_from', $envelope->sender);
        }
        $result = false;
        foreach($envelope->to as $addr){
            $result = $this->mailPassthru($addr, $envelope->subject, $envelope->body, $envelope->header, $params);
        }

        if (isset($old_from)) {
            ini_set('sendmail_from', $old_from);
        }
        if (!$result) {
            trigger_error('(mail_transport): Could not write message',E_USER_WARNING);
            return false;
        }
        return true;
    }

    private function mailPassthru($to, $subject, $body, $header, $params) {
        //TODO clean subject
        $result = @mail($to, $subject, $body, $header, $params);
        return $result;
    }

}