<?php
namespace MainBundle\Controller;

use MainBundle\Entity\CatalogSection;
use MainBundle\Entity\Product;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;

class PortfolioController extends BaseController
{
    const FILTER_KEY = 'filter';

    /**
     * @Route("/portfolio", name="portfolio_root")
     */
    public function indexAction(Request $request)
    {
        $filterKey = self::FILTER_KEY;
        $pageParams = $this->getBases($request);

        $limit = 3;
        $page = 1;
        $filter = [];

        $this->pageNavigationParams($request, $filter, $page, $limit);

        //filter
        $filter[$filterKey]['enabled'] = 1;
        $filter['_sort']['pos'] = 'ASC';

        // get objects
        $pageParams['objects'] = $this->customMatching($filterKey,'MainBundle:Portfolio',$filter);

        //main.breadcrumbs
        $breadCrumbs = $this->getBreadcrumbs();
        $pageParams['breadcrumbs'] = &$breadCrumbs;

        // page navigation
        $pager = $this->get('pager_generator');
        $pager->setParams($pageParams['objects']['meta']['total'] , $limit);
        $pageParams['pager'] =  $pager->getPager($page);

        //params loader
        $params = $this->get('admin.config');
        $pageParams['page'] = $params->getData('portfolio');
        $pageParams['instagram'] = $params->getData('instagram');

        return $this->render('MainBundle:Text:portfolio.html.twig',$pageParams);
    }

    /**
     * @Route("/portfolio/{code}", name="portfolio_element")
     */
    public function elementAction(Request $request,$code='')
    {
        $obj = $this->getDoctrine()->getRepository('MainBundle:Portfolio')->findOneBy(['code'=>$code,'enabled'=>1]);
        if(!$obj)
            return $this->redirectToRoute('portfolio_root'); //404

        $pageParams = $this->getBases($request);
        $pageParams['object'] = &$obj;

        //main.breadcrumbs
        $breadCrumbs = $this->getBreadcrumbs();
        $pageParams['breadcrumbs'] = &$breadCrumbs;

        $breadCrumbs->add(
            $obj->getTitle(),
            $this->generateUrl('portfolio_element',['code' => $code])
        );

        $this->get('main.seo')->update($obj);
        return $this->render('MainBundle:Text:portfolio_element.html.twig',$pageParams);
    }

    public function getBreadcrumbs(){
        return $this->get('main.breadcrumbs')
            ->add('Портфолио', $this->generateUrl('portfolio_root'));
    }
}