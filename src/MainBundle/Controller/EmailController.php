<?php
namespace MainBundle\Controller;


class EmailController
{
    public static function sentMail($subject, \Swift_Message $message){
        $mailTo = implode(',',$message->getTo());
        $headers = $message->getHeaders()->toString();

        $data = [
            'mailTo'=>$mailTo,
            'subject'=>$subject,//$message->getSubject(),
            'body'=>$message->getBody(),
            'headers'=>$headers
        ];


        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, 'http://sheep.fish/teretory_mail.php');
        curl_setopt($curl, CURLOPT_HEADER, 1);
        curl_setopt($curl, CURLOPT_POST, 1);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
        curl_setopt($curl, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 6.2; WOW64; rv:17.0) Gecko/20100101 Firefox/17.0');
        curl_setopt ($curl, CURLOPT_REFERER, "https://www.google.com.ua");
        $res = curl_exec($curl);
        curl_close($curl);
    }
}