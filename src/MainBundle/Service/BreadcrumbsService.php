<?php
namespace MainBundle\Service;


class BreadcrumbsService
{
    protected $list;

    public function __construct()
    {
        $this->list = [];
        $this->add('Главная', '/');
    }

    public function add($title, $url){
        $this->list[] = [
            'title'=>$title,
            'url'=>$url
        ];

        return $this;
    }

    /**
     * @return array
     */
    public function getList()
    {
        return $this->list;
    }
}