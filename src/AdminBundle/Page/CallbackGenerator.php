<?php
/**
 * Created by PhpStorm.
 * User: N1
 * Date: 13.06.17
 * Time: 08:32
 */

namespace AdminBundle\Page;


class CallbackGenerator
{
    public static function getBooleanCallback(){
        return function ($a){
            return $a ? 'Да' : 'Нет';
        };
    }
}