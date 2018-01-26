<?php

namespace AdminBundle\Service;


use Symfony\Component\Debug\Exception\ContextErrorException;

class ConfigService
{
    public function getSource(){
        return __DIR__.'/../Resources/json/';
    }

    public function getData($key){
        try {
            $data = file_get_contents($this->getSource() . $key . '.json');
            $data = json_decode($data,1);

            $resp = [];
            foreach($data as $key=>$field){
                if((isset($field['value']) && !empty($field['value'])))
                    $resp[$key] = $field['value'];
            }
            return $resp;
        }catch (ContextErrorException $e){
            return false;
        }
    }
}