<?php

namespace MainBundle\Controller;

use MainBundle\Entity\CatalogSection;
use MainBundle\Entity\EmailTemplate;
use MainBundle\Entity\Product;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;
use UserBundle\UserBundle;

class CatalogController extends BaseController
{
    const FILTER_KEY = 'filter';

    /**
     * @Route("/search", name="search")
     */
    public function searchAction(Request $request){
        $pageParams = $this->getBases($request);

        $q = trim($request->query->get('q')?:'');

        if(!$q)
            return $this->redirectToRoute('catalog_main_root');

        $pageParams['q'] = $q;
        $filter = [];
        $filterKey = self::FILTER_KEY;

        //getEnabledSections
        $sections = $this->getEnabledSections();
        if($sections) {
            foreach ($sections as $v){
                $filter[$filterKey]['catalogSection'][] = $v->getId();
            }
        }
        $sections = $this->getEnabledSections(false);
        if($sections) {
            foreach ($sections as $v){
                $filter[$filterKey]['catalogSection'][] = $v->getId();
            }
        }

        if(!$filter[$filterKey]['catalogSection']) {
            $filter[$filterKey]['catalogSection'] = false;
        }

        $session = new Session();
        try {
            $session->start();
        }catch (\RuntimeException $e){

        }

        //limit
        $pageParams['limitParams'] = self::getLimitParams();
        $limit = $session->get('limit');
        if(!$limit)
            $limit = $pageParams['limitParams'][0];

        $newLimit = $limit;
        if($request->query->get('_limit')){
            $newLimit = $request->query->get('_limit');
            if(!in_array($newLimit,$pageParams['limitParams'])){
                $newLimit = $pageParams['limitParams'][0];
            }
        }

        if($limit != $newLimit){
            $session->set('limit', $newLimit);
            $limit = $newLimit;
        }
        $pageParams['limitParamsActive'] = $limit;
        $page = 1;

        //order
        $pageParams['sortParams'] = self::getSortParams();
        $sort = $session->get('sort');
        $order = $session->get('order');

        if($sortArray = $request->query->get('_sort')){
            if(is_string($sortArray)){
                $sortArray = preg_replace('/^_*/','',$sortArray);
                $sortArray = explode('_',$sortArray);
                if(count($sortArray) == 2){
                    $sort = $sortArray[0];
                    $order = $sortArray[1];
                }

            }
        }

        $needDefault = true;
        foreach ($pageParams['sortParams'] as $k=>$v){
            $k2 =  preg_replace('/^_*/','',$k);
            if($k2 == $sort && $order == $v['order']){
                $needDefault = false;
                $pageParams['sortParamsActive'] = [
                    'sort'=>$k,
                    'order'=>$order
                ];
            }
        }

        if($needDefault){
            $sort = 'pos';
            $order = 'ASC';

            $pageParams['sortParamsActive'] = [
                'sort'=>$sort,
                'order'=>$order
            ];
        }

        $session->set('sort',$sort);
        $session->set('order',$order);

        $filter['_sort'][$sort] = $order;

        $session->save();

        $this->pageNavigationParams($request, $filter, $page, $limit);

        $filter[$filterKey]['title'] = $q;
        $filter[$filterKey]['enabled'] = 1;

        // get objects
        $pageParams['objects'] = $this->customMatching($filterKey,'MainBundle:Product',$filter);
        $pageParams['filter'] = $filter;

        // page navigation
        $pager = $this->get('pager_generator');
        $pager->setParams($pageParams['objects']['meta']['total'] , $limit);
        $pageParams['pager'] =  $pager->getPager($page);


        $pageParams['breadcrumbs'] = $this->get('main.breadcrumbs')
                                          ->add('search', '#');

        $pageParams['page_title'] = sprintf('Поиск по запросу "%s"', $q);

        return $this->render('MainBundle:Catalog:search.html.twig',$pageParams);
    }

    /**
     * @Route("/gifts", name="catalog_unmain_root")
     * @Route("/gifts/{section_code}", name="catalog_unmain_section")
     */
    public function giftsAction(Request $request, $section_code=''){

        $pageParams = $this->getBases($request);
        $filter = [];
        $filterKey = self::FILTER_KEY;
        $session = new Session();
        try {
            $session->start();
        }catch (\RuntimeException $e){

        }

        $route = $request->get('_route');
        /**
         * @var CatalogSection $obj
         */
        $obj = null;
        if($route == 'catalog_unmain_section') {
            $obj = $this->getDoctrine()->getRepository('MainBundle:CatalogSection')->findOneBy(['code' => $section_code, 'enabled' => 1, 'main'=>0]);

            if (
                !$obj
            ) //404
                return $this->redirectToRoute('catalog_unmain_root');
        }

        //getEnabledSections
        $pageParams['active_section'] = &$obj;
        if($obj){
            $sections = [
                0 => &$obj
            ];
            $this->get('main.seo')->update($obj);
        } else {
            $sections = $this->getEnabledSections(false);
        }
        $pageParams['sections'] = &$sections;
        if($sections){
            $filter[$filterKey]['catalogSection'] = array_map(function($a){
                return $a->getId();
            },$sections);
        } else {
            $filter[$filterKey]['catalogSection'] = false;
        }

        //main.breadcrumbs
        $breadCrumbs = $this->get('main.breadcrumbs')
                            ->add('gift_title', $this->generateUrl('catalog_unmain_root'));
        if($obj) {
            $breadCrumbs->add($obj->getTitle(), $this->generateUrl('catalog_unmain_section', ['section_code' => $obj->getCode()]));
        }
        $pageParams['breadcrumbs'] = &$breadCrumbs;

        //limit
        $pageParams['limitParams'] = self::getLimitParams();
        $limit = $session->get('limit');
        if(!$limit)
            $limit = $pageParams['limitParams'][0];

        $newLimit = $limit;
        if($request->query->get('_limit')){
            $newLimit = $request->query->get('_limit');
            if(!in_array($newLimit,$pageParams['limitParams'])){
                $newLimit = $pageParams['limitParams'][0];
            }
        }

        if($limit != $newLimit){
            $session->set('limit', $newLimit);
            $limit = $newLimit;
        }
        $pageParams['limitParamsActive'] = $limit;
        $page = 1;

        //order
        $pageParams['sortParams'] = self::getSortParams();
        $sort = $session->get('sort');
        $order = $session->get('order');

        if($sortArray = $request->query->get('_sort')){
            if(is_string($sortArray)){
                $sortArray = preg_replace('/^_*/','',$sortArray);
                $sortArray = explode('_',$sortArray);
                if(count($sortArray) == 2){
                    $sort = $sortArray[0];
                    $order = $sortArray[1];
                }
            }
        }

        $needDefault = true;
        foreach ($pageParams['sortParams'] as $k=>$v){
            $k2 =  preg_replace('/^_*/','',$k);
            if($k2 == $sort && $order == $v['order']){
                $needDefault = false;
                $pageParams['sortParamsActive'] = [
                    'sort'=>$k,
                    'order'=>$order
                ];
            }
        }

        if($needDefault){
            $sort = 'pos';
            $order = 'ASC';

            $pageParams['sortParamsActive'] = [
                'sort'=>$sort,
                'order'=>$order
            ];
        }

        $session->set('sort',$sort);
        $session->set('order',$order);

        $filter['_sort'][$sort] = $order;

        $session->save();

        $this->pageNavigationParams($request, $filter, $page, $limit);

        //filter
        $filter[$filterKey]['enabled'] = 1;

        // get objects
        $pageParams['objects'] = $this->customMatching($filterKey,'MainBundle:Product',$filter);

        // page navigation
        $pager = $this->get('pager_generator');
        $pager->setParams($pageParams['objects']['meta']['total'] , $limit);
        $pageParams['pager'] =  $pager->getPager($page);

        $pageParams['is_main'] = false;
        $pageParams['page_title'] = 'gift_title';
        $pageParams['page_text'] = '';
        if($obj){
            $pageParams['page_title'] = $obj->getTitle();
            $pageParams['page_text'] = $obj->getText();
        }
        return $this->render('MainBundle:Catalog:section_unmain.html.twig',$pageParams);
    }

    /**
     * @Route("/gifts/{section_code}/{product_code}", name="catalog_unmain_section_product")
     */
    public function productGiftAction(Request $request, $section_code = '', $product_code=''){
        /**
         * @var Product $obj
         */

        $pageParams = $this->getBases($request);

        $obj = $this->getDoctrine()->getRepository('MainBundle:Product')->findOneBy(['code'=>$product_code,'enabled'=>1]);
        if(
            !$obj
            ||
            !$obj->getCatalogSection()
            ||
            !$obj->getCatalogSection()->isEnabled()
            ||
            $obj->getCatalogSection()->isMain()
            ||
            $obj->getCatalogSection()->getCode() != $section_code
        ) // 404
            return $this->redirectToRoute('catalog_unmain_root');

        $sections = $this->getEnabledSections(false);
        $pageParams['sections'] = &$sections;
        $pageParams['active_section_code'] = $section_code;

        $pageParams['object'] = &$obj;

        //main.breadcrumbs
        $breadCrumbs = $this->get('main.breadcrumbs')
                            ->add('gift_title', $this->generateUrl('catalog_unmain_root'))
                            ->add(
                                $obj->getCatalogSection()->getTitle(),
                                $this->generateUrl(
                                    'catalog_unmain_section',
                                    [
                                        'section_code'=>$obj->getCatalogSection()->getCode()
                                    ]
                                )
                            )
                            ->add(
                                $obj->getTitle(),
                                $this->generateUrl('catalog_unmain_section_product',['section_code' => $section_code, 'product_code'=>$product_code])
                            );

        $pageParams['breadcrumbs'] = &$breadCrumbs;
        $pageParams['is_main'] = false;
        $pageParams['bonus_product_sections'] = [];

        $this->get('main.seo')->update($obj);
        if($obj->getBonusProduct()->count()>0){
            /**
             * @var Product $item
             */
            foreach ($obj->getBonusProduct()->toArray() as $item){
                if($item->isEnabled() && $item->getCatalogSection() && $item->getCatalogSection()->isEnabled()){
                    $pageParams['bonus_product_sections'][$item->getCatalogSection()->getId()] = $item->getCatalogSection();
                }
            }
        }

        return $this->render('MainBundle:Catalog:product_unmain.html.twig',$pageParams);
    }

    /**
     * @Route("/catalog", name="catalog_main_root")
     * @Route("/catalog/filter-{params}", name="catalog_main_root_filter")
     */
    public function indexAction(Request $request, $params='')
    {

        $pageParams = $this->getBases($request);

        $filter = [];
        $filterKey = self::FILTER_KEY;

        //getEnabledSections
        $sections = $this->getEnabledSections();
        $pageParams['sections'] = &$sections;
        if($sections){
            $filter[$filterKey]['catalogSection'] = array_map(function($a){
                return $a->getId();
            },$sections);
        } else {
            $filter[$filterKey]['catalogSection'] = false;
        }

        //main.breadcrumbs
        $breadCrumbs = $this->getCatalogBreadcrumbs();
        $pageParams['breadcrumbs'] = &$breadCrumbs;

        $pageParams['page_title'] = 'catalog_title';
        $pageParams['page_text'] = '';
        return $this->showCatalog($pageParams,$request,$params,'catalog_main_root','catalog_main_root_filter',[],$filter);
    }

    /**
     * @Route("/catalog/{section_code}", name="catalog_main_section")
     * @Route("/catalog/{section_code}/filter-{params}", name="catalog_main_section_filter")
     */
    public function sectionCatalogAction(Request $request, $section_code = '', $params=''){
        /**
         * @var CatalogSection $obj
         */
        $obj = $this->getDoctrine()->getRepository('MainBundle:CatalogSection')->findOneBy(['code'=>$section_code,'enabled'=>1]);
        if(
            !$obj
        ||
            !$obj->isMain()
        ||
            !$obj->isTotalEnabled()
        ) //404
            return $this->redirectToRoute('catalog_main_root');

        $pageParams = $this->getBases($request);
        $filter = [];
        $filterKey = 'filter';

        //getEnabledSections
        $sections = $this->getEnabledSections(true, $obj);
        $pageParams['sections'] = &$sections;
        $filter[$filterKey]['catalogSection'][] = $obj->getId();
        if(!$obj->getParentSection()){ // find all "kids"
            if($sections){
                foreach ($sections as $section){
                    if($section->getParentSection() && $section->getParentSection()->getId() == $obj->getId()){
                        $filter[$filterKey]['catalogSection'][] = $section->getId();
                    }
                }
            }
        }

        $this->get('main.seo')->update($obj);

        //main.breadcrumbs
        $breadCrumbs = $this->getCatalogBreadcrumbs();
        $pageParams['breadcrumbs'] = &$breadCrumbs;

        if($obj->getParentSection()){
            $breadCrumbs->add(
                $obj->getParentSection()->getTitle(),
                $this->generateUrl(
                    'catalog_main_section',
                    [
                        'section_code'=>$obj->getParentSection()->getCode()
                    ]
                )
            );
        }

        $breadCrumbs->add(
            $obj->getTitle(),
            $this->generateUrl(
                'catalog_main_section',
                [
                    'section_code'=>$obj->getCode()
                ]
            )
        );

        $pageParams['page_title'] = $obj->getTitle();
        $pageParams['page_text'] = $obj->getText()?:'';
        return $this->showCatalog($pageParams,$request,$params,'catalog_main_section','catalog_main_section_filter',['section_code'=>$section_code],$filter);
    }

    /**
     * @Route("/catalog/{section_code}/{product_code}", name="catalog_main_section_product")
     */
    public function productCatalogAction(Request $request, $section_code = '', $product_code=''){
        /**
         * @var Product $obj
         */

        $pageParams = $this->getBases($request);

        $obj = $this->getDoctrine()->getRepository('MainBundle:Product')->findOneBy(['code'=>$product_code,'enabled'=>1]);
        if(
            !$obj
        ||
            !$obj->getCatalogSection()
        ||
            !$obj->getCatalogSection()->isEnabled()
        ||
            !$obj->getCatalogSection()->isMain()
        ||
            $obj->getCatalogSection()->getCode() != $section_code
        ||
            ( $obj->getCatalogSection()->getParentSection() && !$obj->getCatalogSection()->getParentSection()->isEnabled() )
        ) // 404
            return $this->redirectToRoute('catalog_main_root');

        $sections = $this->getEnabledSections();
        $pageParams['sections'] = &$sections;
        $pageParams['active_section_code'] = $section_code;

        $pageParams['object'] = &$obj;
        $this->get('main.seo')->update($obj);

        //main.breadcrumbs
        $breadCrumbs = $this->getCatalogBreadcrumbs();

        if($obj->getCatalogSection()->getParentSection()){
            $breadCrumbs->add(
                $obj->getCatalogSection()->getParentSection()->getTitle(),
                $this->generateUrl(
                    'catalog_main_section',
                    [
                        'section_code'=>$obj->getCatalogSection()->getParentSection()->getCode()
                    ]
                )
            );
        }

        $breadCrumbs->add(
            $obj->getCatalogSection()->getTitle(),
            $this->generateUrl(
                'catalog_main_section',
                [
                    'section_code'=>$obj->getCatalogSection()->getCode()
                ]
            )
        )
        ->add(
            $obj->getTitle(),
            $this->generateUrl('catalog_main_section_product',['section_code' => $section_code, 'product_code'=>$product_code])
        );

        $pageParams['breadcrumbs'] = &$breadCrumbs;
        $pageParams['is_main'] = true;
        $pageParams['bonus_product_sections'] = [];

        if($obj->getBonusProduct()->count()>0){
            /**
             * @var Product $item
             */
            foreach ($obj->getBonusProduct()->toArray() as $item){
                if($item->isEnabled() && $item->getCatalogSection() && $item->getCatalogSection()->isEnabled()){
                    $pageParams['bonus_product_sections'][$item->getCatalogSection()->getId()] = $item->getCatalogSection();
                }
            }
        }

        $params = $this->get('admin.config');
        $pageParams['config_params'] = $params->getData('config');
        return $this->render('MainBundle:Catalog:product.html.twig',$pageParams);
    }


    public function showCatalog($pageParams, Request $request,$params,$rootRoute,$filterRoute, $filterRouteParams=[],$filter){
        $session = new Session();
        try {
            $session->start();
        }catch (\RuntimeException $e){

        }
        $filterKey = self::FILTER_KEY;
        //limit
        $pageParams['limitParams'] = self::getLimitParams();
        $limit = $session->get('limit');
        if(!$limit)
            $limit = $pageParams['limitParams'][0];

        $newLimit = $limit;
        if($request->query->get('_limit')){
            $newLimit = $request->query->get('_limit');
            if(!in_array($newLimit,$pageParams['limitParams'])){
                $newLimit = $pageParams['limitParams'][0];
            }
        }

        if($limit != $newLimit){
            $session->set('limit', $newLimit);
            $limit = $newLimit;
        }
        $pageParams['limitParamsActive'] = $limit;
        $page = 1;

        //order
        $pageParams['sortParams'] = self::getSortParams();
        $sort = $session->get('sort');
        $order = $session->get('order');

        if($sortArray = $request->query->get('_sort')){
            if(is_string($sortArray)){
                $sortArray = preg_replace('/^_*/','',$sortArray);
                $sortArray = explode('_',$sortArray);
                if(count($sortArray) == 2){
                    $sort = $sortArray[0];
                    $order = $sortArray[1];
                }
            }
        }

        $needDefault = true;
        foreach ($pageParams['sortParams'] as $k=>$v){
            $k2 =  preg_replace('/^_*/','',$k);
            if($k2 == $sort && $order == $v['order']){
                $needDefault = false;
                $pageParams['sortParamsActive'] = [
                    'sort'=>$k,
                    'order'=>$order
                ];
            }
        }

        if($needDefault){
            $sort = 'pos';
            $order = 'ASC';

            $pageParams['sortParamsActive'] = [
                'sort'=>$sort,
                'order'=>$order
            ];
        }

        $session->set('sort',$sort);
        $session->set('order',$order);

        $filter['_sort'][$sort] = $order;

        $session->save();

        //filter
        $filterParams = [];
        $activeFilter = $this->getFilter($filterParams,$params,$filter,$filterKey,$filterRoute,$filterRouteParams);
        if(!$activeFilter && $request->get('_route') != $rootRoute)
            return $this->redirectToRoute($rootRoute,$filterRouteParams);

        $pageParams['filterParams'] = &$filterParams;

        $this->pageNavigationParams($request, $filter, $page, $limit);

        //filter
        $filter[$filterKey]['enabled'] = 1;

        // get objects
        $pageParams['objects'] = $this->customMatching($filterKey,'MainBundle:Product',$filter);

        // page navigation
        $pager = $this->get('pager_generator');
        $pager->setParams($pageParams['objects']['meta']['total'] , $limit);
        $pageParams['pager'] =  $pager->getPager($page);

        $pageParams['is_main'] = true;
        return $this->render('MainBundle:Catalog:section.html.twig',$pageParams);
    }

    public function getFilter(&$filterParams,$params,&$filter,$filterKey,$path,$pathParams=[]){

        $this->getFilterParams($filterParams);
        $activeParams = [];
        if($params){
            $params = explode('-',$params);
            $filterPosition = 0;
            $outOfRange = 7;
            foreach ($params as $param){
                while ($filterPosition < $outOfRange){
                    if(isset($filterParams[$filterPosition]['items'][$param])){
                        $filterParams[$filterPosition]['active'] = $filterParams[$filterPosition]['items'][$param]['title'];
                        if(isset($filterParams[$filterPosition]['items'][$param]['range'])){
                            $filter[$filterKey][$filterParams[$filterPosition]['filter_field']] = $filterParams[$filterPosition]['items'][$param]['range'];
                        } else {
                            $filter[$filterKey][$filterParams[$filterPosition]['filter_field']] = $filterParams[$filterPosition]['items'][$param]['value'];
                        }
                        $activeParams[$filterPosition] = $param;
                        $filterPosition++;
                        break;
                    }
                    $filterPosition++;
                }
            }
        }

        //filter generate url
        $this->getFilterParamsGenerateUrl($filterParams,$activeParams,$path,$pathParams);

        return $activeParams;
    }

    public function getEnabledSections($main = true, CatalogSection $activeSection = null){
        $sect = $this->getDoctrine()->getRepository('MainBundle:CatalogSection')->findBy(['enabled'=>1,'main'=>$main],['pos'=>'ASC']);
        if(!$sect)
            return [];

        if($activeSection)
            return $this->getChildSections($activeSection, $sect);
        /**
         * @var CatalogSection $val
         */
        foreach ($sect as $key=>$val){
            if($val->getParentSection() && !$val->getParentSection()->isEnabled()){
                unset($sect[$key]);
            }
        }

        return $sect;
    }

    protected function getChildSections(CatalogSection $activeSection, &$sectionList){
        $res = [
            $activeSection
        ];

        /**
         * @var CatalogSection $val
         */
        foreach ($sectionList as $key=>$val){
            if($val->getParentSection() && $val->getParentSection() == $activeSection){
                $res = array_merge($res, $this->getChildSections($val, $sectionList));
            }
        }

        return $res;
    }

    public function getCatalogBreadcrumbs(){
            return $this->get('main.breadcrumbs')
                   ->add('catalog_title', $this->generateUrl('catalog_main_root'));
    }

    public function getFilterParams(&$filterParams){
        $params = self::getFilterDefaults();

        foreach ($params as $param) {
            $array = $this->getDoctrine()->getRepository($param['entity'])->findBy(['enabled' => 1], ['pos' => 'ASC']);
            $resp = [];
            if ($array) {
                $resp['name'] = $param['name'];
                $resp['filter_field'] = $param['filter_field'];
                $resp['active'] = false;
                $resp['items'] = [];
                foreach ($array as $item) {
                    $resp['items'][$item->getCode()] = [
                        'title' => $item->getTitle(),
                        'value' => $item->getId(),
                        'url' => ''
                    ];

                    if(method_exists($item,'getMinPrice') && method_exists($item,'getMaxPrice')){
                        $resp['items'][$item->getCode()]['range']=[];

                        if($item->getMinPrice())
                            $resp['items'][$item->getCode()]['range']['from'] = $item->getMinPrice();

                        if($item->getMaxPrice())
                            $resp['items'][$item->getCode()]['range']['to'] = $item->getMaxPrice();
                    }
                }
                $filterParams[] = $resp;
            }
        }
    }

    public function getFilterParamsGenerateUrl(&$filterParams,$activeParams,$path,$pathParams=[]){
        foreach ($filterParams as $k=>&$v){
            $params = $activeParams;
            foreach ($v['items'] as $code=>&$item){
                $params[$k] = $code;
                ksort($params);
                $item['url'] = $this->generateUrl($path,array_merge(['params'=>implode('-',$params)],$pathParams));
            }

            if(isset($params[$k]))
                unset($params[$k]);

            array_unshift($v['items'],[
                'title' => 'Все',
                'value' => '',
                'url' => $this->generateUrl($path,array_merge(['params'=>implode('-',$params)],$pathParams))
            ]);
        }
    }


    static function getSortParams(){
        return [
            'pos'=>[
                'order'=>'ASC',
                'name'=>'-------------'
            ],
            'price'=>[
                'order'=>'DESC',
                'name'=>'По убыванию'
            ],
            '_price'=>[
                'order'=>'ASC',
                'name'=>'По возрастанию'
            ],
            'title'=>[
                'order'=>'ASC',
                'name'=>'По названию'
            ]
        ];
    }

    static function getLimitParams(){
        return [
            15,
            9,
            6
        ];
    }

    static function getFilterDefaults(){
        return [
            [
                'entity'=>'MainBundle:Type',
                'name'=>'Цветы',
                'filter_field'=>'type'
            ],
            [
                'entity'=>'MainBundle:Target',
                'name'=>'Кому',
                'filter_field'=>'target'
            ],
            [
                'entity'=>'MainBundle:Occasion',
                'name'=>'Повод',
                'filter_field'=>'occasion'
            ],
            [
                'entity'=>'MainBundle:Note',
                'name'=>'Сказать',
                'filter_field'=>'note'
            ],
            [
                'entity'=>'MainBundle:PriceRange',
                'name'=>'Цена',
                'filter_field'=>'price'
            ],
            [
                'entity'=>'MainBundle:Color',
                'name'=>'Цвет',
                'filter_field'=>'color'
            ],
            [
                'entity'=>'MainBundle:Season',
                'name'=>'Время года',
                'filter_field'=>'season'
            ]
        ];
    }
}