<?php

namespace MainBundle\Controller;

use MainBundle\Entity\MainPageFigure;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;

class DefaultController extends BaseController
{
    /**
     * @Route("/", name="index")
     */
    public function indexAction(Request $request)
    {
        $pageParams = $this->getBases($request);
        $params = $this->get('admin.config');
        $pageParams['akcii'] = $this->getDoctrine()->getRepository('MainBundle:Akcii')->findBy(['enabled'=>1,'onMain'=>1],['pos'=>'ASC']);

        $pageParams['delivery'] = [];
        $pageParams['wedding'] = [];
        $pageParams['present'] = [];
        $figures = $this->getDoctrine()->getRepository('MainBundle:MainPageFigure')->findBy(['enabled'=>1],['pos'=>'ASC']);
        if($figures){
            /**
             * @var MainPageFigure $item
             */
            foreach ($figures as $item){
                if($item->isInDelivery())
                    $pageParams['delivery'][] = $item;

                if($item->isInPresent())
                    $pageParams['present'][] = $item;

                if($item->isInWedding())
                    $pageParams['wedding'][] = $item;
            }
        }

        $pageParams['main_params'] = $params->getData('main_a_page'); //настройки блоков

        return $this->render('MainBundle:Text:index.html.twig',$pageParams);
    }
}
