<?php
namespace MainBundle\Controller;

use MainBundle\Entity\CatalogSection;
use MainBundle\Entity\Product;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;

class AkciiController extends BaseController
{
    const FILTER_KEY = 'filter';

    /**
     * @Route("/akcii", name="akcii_root")
     */
    public function indexAction(Request $request)
    {
        $filterKey = self::FILTER_KEY;
        $pageParams = $this->getBases($request);

        $limit = 9;
        $page = 1;
        $filter = [];

        $this->pageNavigationParams($request, $filter, $page, $limit);

        //filter
        $filter[$filterKey]['enabled'] = 1;
        $filter['_sort']['pos'] = 'ASC';

        // get objects
        $pageParams['objects'] = $this->customMatching($filterKey,'MainBundle:Akcii',$filter);

        //main.breadcrumbs
        $breadCrumbs = $this->getAkciiBreadcrumbs();
        $pageParams['breadcrumbs'] = &$breadCrumbs;

        // page navigation
        $pager = $this->get('pager_generator');
        $pager->setParams($pageParams['objects']['meta']['total'] , $limit);
        $pageParams['pager'] =  $pager->getPager($page);

        return $this->render('MainBundle:Text:akcii.html.twig',$pageParams);
    }

    /**
     * @Route("/akcii/{code}", name="akcii_element")
     */
    public function elementAction(Request $request,$code='')
    {
        $obj = $this->getDoctrine()->getRepository('MainBundle:Akcii')->findOneBy(['code'=>$code,'enabled'=>1]);
        if(!$obj)
            return $this->redirectToRoute('akcii_root'); //404

        $pageParams = $this->getBases($request);
        $pageParams['object'] = &$obj;

        //main.breadcrumbs
        $breadCrumbs = $this->getAkciiBreadcrumbs();
        $pageParams['breadcrumbs'] = &$breadCrumbs;

        $breadCrumbs->add(
            $obj->getTitle(),
            $this->generateUrl('akcii_element',['code' => $code])
        );

        $this->get('main.seo')->update($obj);
        return $this->render('MainBundle:Text:akcii_element.html.twig',$pageParams);
    }

    public function getAkciiBreadcrumbs(){
        return $this->get('main.breadcrumbs')
                    ->add('Акции', $this->generateUrl('akcii_root'));
    }
}