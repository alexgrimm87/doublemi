<?php

namespace AdminBundle\Page;

class FormHolder
{
    private $list;
    private $tabs;
    private $active_tab;

    public function __construct()
    {
        $this->list = [];
        $this->tabs = [];

        $this->tab('base');

    }

    /**
     * @return mixed
     */
    public function getList()
    {
        return $this->list;
    }

    /**
     * @return array
     */
    public function getTabs()
    {
        return $this->tabs;
    }

    public function add($name,$type = null, array $options = array()){
        $this->list[] = [
            'name'=>$name,
            'type'=>$type,
            'options'=>$options
        ];

        if(isset($this->tabs[$this->active_tab])){
            $this->tabs[$this->active_tab]['fields'][] = $name;
        }

        return $this;
    }

    public function activeTab($name) {
        if(isset($this->tabs[$name])) {
            $this->active_tab = $name;
        }
        return $this;
    }

    public function tab($name,$label=''){
        if(!isset($this->tabs[$name]))
            $this->tabs[$name] = [
                'fields'=>[],
                'label'=>$name
            ];

        if($label)
            $this->tabs[$name]['label'] = $label;

        $this->active_tab = $name;

        return $this;
    }

    public function check(){
        return boolval($this->list);
    }
}