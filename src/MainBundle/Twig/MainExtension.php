<?php
/**
 * Created by PhpStorm.
 * User: N1
 * Date: 10.05.17
 * Time: 12:39
 */

namespace MainBundle\Twig;

class MainExtension extends \Twig_Extension
{
    public function getFilters()
    {
        return array(
            new \Twig_SimpleFilter('callback', array($this, 'callback')),
            new \Twig_SimpleFilter('price', array($this, 'price')),
            new \Twig_SimpleFilter('price_markup', array($this, 'price_markup')),
            new \Twig_SimpleFilter('phone_filter', array($this, 'phone')),
            new \Twig_SimpleFilter('site_date', array($this, 'date')),
            new \Twig_SimpleFilter('shity_price', array($this, 'shity_price'))
        );
    }

    function callback($val, $callback){
        return $callback($val);
    }

    function shity_price($str, $template = ''){
        if(!$template)
            $template = '<span style="font-size: 20px;">$1</span>';
        return preg_replace('/(\d[ \d]*\d)/',$template,$str);
    }

    function date(\DateTime $time){
        $rusMonth = [
             'Янв'
            ,'Фев'
            ,'Мрт'
            ,'Апр'
            ,'Май'
            ,'Июн'
            ,'Июл'
            ,'Авг'
            ,'Сен'
            ,'Окт'
            ,'Нбр'
            ,'Дек'
        ];
        return sprintf('%d %s %d',$time->format('d'),$rusMonth[$time->format('n')-1],$time->format('Y'));
    }

    function price_markup($str){
        return ($str>0?'+':'').self::priceFormatter($str);
    }

    function price($str){
        return self::priceFormatter($str);
    }

    public function phone($str){
        return preg_replace('/[^\d]*/','',$str);
    }

    static function priceFormatter($str){
        return number_format($str, 0, ',', ' ').' руб.';
    }
}