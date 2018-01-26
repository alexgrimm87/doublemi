<?php
namespace MainBundle\Service;


use MainBundle\Entity\CatalogSection;
use MainBundle\Entity\Menu;

class MenuService
{
    private $top;
    private $main;
    private $mobile;
    private $footer;
    private $footerCols;
    private $subsectionMain;
    private $subsectionUnMain;
    private $subsectionWedding;
    private $container;

    public function __construct()
    {
        $this->top = [];
        $this->main = [];
        $this->footer = [];
        $this->mobile = [];
        $this->footerCols = Menu::$cols;
    }


    public function init($container){
        $this->container = $container;

        /**
         * @var Menu $menu
         */
        $menuList = $this->container->get('doctrine')->getRepository('MainBundle:Menu')->findBy(['enabled'=>1],['pos'=>'ASC']);
        if(!$menuList)
            return;

        $router = $this->container->get('router');

        foreach ($menuList as $menu){
            $strict = $this->strictMenu($menu, $router, $menuList);

            if($menu->isMobile())
                $this->addMobile($strict);

            if($menu->isTop())
                $this->addTop($strict);

            if($menu->isMain() && !$menu->getParent())
                $this->addMain($strict);

            if($menu->isFooter())
                $this->addFooter($strict,$menu->getCol());
        }
    }

    public function strictMenu(Menu $menu, $router, &$menuList){
        $res = [
            'title'=>$menu->getTitle(),
            'url'=>$menu->getUrl(),
            'subsections'=>[]
        ];

        if($menu->getCode()) {
            $res['url'] = $router->generate($menu->getCode());
        }

        //add subsection
        $res = $this->addSubsections($res, $menu, $router, $menuList);
        return $res;
    }

    public function addSubsections($strict, Menu $menu, $router, &$menuList){

        switch ($menu->getType()){
            case Menu::TYPE_CATALOG:
                $strict['subsections'] = $this->getSubsectionMain($router);
                break;
            case Menu::TYPE_GIFT:
                $strict['subsections'] = $this->getSubsectionUnMain($router);
                break;
            case Menu::TYPE_WEDDING:
                $strict['subsections'] = $this->getSubsectionWedding($router);
                break;
        }

        if($menu->isMain() && !$menu->getParent()){
            $subsections = $this->getMainSubsections($menu, $router, $menuList);
            if($subsections){
                $strict['subsections'] = array_merge($strict['subsections'], $subsections);
            }
        }
        return $strict;
    }

    public function getMainSubsections(Menu $menu, $router, &$menuList){
        $resp = [];
        /**
         * @var Menu $item
         */
        foreach ($menuList as $item){
            if(!$item->getParent())
                continue;

            if(!$item->isMain())
                continue;

            if($menu->getId() != $item->getParent()->getId())
                continue;

            $res = [
                'title'=>$item->getTitle(),
                'url'=>$item->getUrl()
            ];

            if($item->getCode()) {
                $res['url'] = $router->generate($item->getCode());
            }
            $resp[] = $res;
        }
        return $resp;
    }

    public function getSubsectionWedding($router){
        if($this->subsectionWedding)
            return $this->subsectionWedding;

        $sections = $this->container->get('doctrine')->getRepository('MainBundle:CatalogSection')->findBy(['enabled'=>1, 'inWeddingMenu'=>1],['pos'=>'ASC']);

        if($sections){
            $this->subsectionWedding = [];
            /**
             * @var  CatalogSection $section
             */
            foreach ($sections as $section){
                $this->subsectionWedding[] = [
                    'title'=>$section->getTitle(),
                    'url'=>$router->generate(
                        $section->isMain()?'catalog_main_section':'catalog_unmain_section',
                        [
                            'section_code'=>$section->getCode()
                        ]
                    )
                ];
            }
        }
        return $this->subsectionWedding;
    }

    /**
     * @return mixed
     */
    public function getSubsectionMain($router)
    {
        if($this->subsectionMain)
            return $this->subsectionMain;

        $sections = $this->container->get('doctrine')->getRepository('MainBundle:CatalogSection')->findBy(['enabled'=>1, 'main'=>1, 'inCatalogMenu'=>1],['pos'=>'ASC']);

        if($sections){
            $this->subsectionMain = [];
            foreach ($sections as $section){
                $this->subsectionMain[] = [
                    'title'=>$section->getTitle(),
                    'url'=>$router->generate('catalog_main_section',['section_code'=>$section->getCode()])
                ];
            }
        }
        return $this->subsectionMain;
    }

    /**
     * @return mixed
     */
    public function getSubsectionUnMain($router)
    {
        if($this->subsectionUnMain)
            return $this->subsectionUnMain;

        $sections = $this->container->get('doctrine')->getRepository('MainBundle:CatalogSection')->findBy(['enabled'=>1, 'main'=>0, 'inGiftMenu'=>1],['pos'=>'ASC']);
        if($sections){
            $this->subsectionUnMain = [];
            foreach ($sections as $section){
                $this->subsectionUnMain[] = [
                    'title'=>$section->getTitle(),
                    'url'=>$router->generate('catalog_unmain_section',['section_code'=>$section->getCode()])
                ];
            }
        }
        return $this->subsectionUnMain;
    }


    /**
     * @return mixed
     */
    public function getTop()
    {
        return $this->top;
    }

    /**
     * @param mixed $top
     */
    public function addTop($top)
    {
        $this->top[] = $top;
    }

    /**
     * @return mixed
     */
    public function getMain()
    {
        return $this->main;
    }

    public function getLeftMain(){
        if(!$this->main)
            return[];

        $delimiter = ceil(count($this->main)/2);
        $res = [];
        for($i=0; $i<$delimiter; $i++){
            $res[] = &$this->main[$i];
        }
        return $res;
    }

    public function getRightMain(){
        if(!$this->main)
            return[];

        $count = count($this->main);
        $delimiter = ceil($count/2);
        $res = [];
        for($i=$delimiter; $i<$count; $i++){
            $res[] = &$this->main[$i];
        }
        return $res;
    }

    /**
     * @param $main
     */
    public function addMain($main)
    {
        $this->main[] = $main;
    }

    /**
     * @return mixed
     */
    public function getFooter()
    {
        ksort($this->footer);
        return $this->footer;
    }

    /**
     * @param mixed $col
     * @param mixed $footer
     */
    public function addFooter($footer, $col)
    {
        if(isset($this->footerCols[$col])) {
            if(!isset($this->footer[$col]))
                $this->footer[$col] = [];
            $this->footer[$col][] = $footer;
        }
    }

    public function getCol($col){
        if(isset($this->footerCols[$col]))
            return $this->footerCols[$col];
        return '';
    }

    /**
     * @return mixed
     */
    public function getMobile()
    {
        return $this->mobile;
    }

    /**
     * @param mixed $mobile
     */
    public function addMobile($mobile)
    {
        $this->mobile[] = $mobile;
    }


}