<?php


class aws_transport extends mail_transport{


    public function send($envelope){
        $request = array(
            'Content'=>array(
                'Raw'=> $envelope->header . $envelope->body
            ),
            'Destination'=>array(
                'ToAddresses' => array(),
                'CcAddresses' => array(),
                'BccAddresses' => array()
            ),
            'FromEmailAddress'=> $envelope->sender
        );

        foreach($envelope->to as $val){
            $request['Destination']['ToAddresses'][] = $val[0];
        }

        foreach($envelope->cc as $val){
            $request['Destination']['CcAddresses'][] = $val[0];
        }

        foreach($envelope->bcc as $val){
            $request['Destination']['BccAddresses'][] = $val[0];
        }

        print_r($request);
        //send POST /v2/email/outbound-emails

        //email.us-east-1.amazonaws.com

        return false;
    }
}