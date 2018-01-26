<?php
/**
 * Created by PhpStorm.
 * User: N1
 * Date: 30.05.17
 * Time: 10:17
 */

namespace MainBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;

class InfoController extends BaseController
{
    /**
     * @Route("/i", name="info_root")
     * @Route("/i/{code}", name="info_element")
     */
    public function elementAction(Request $request,$code='')
    {
        if($code === '')
            return $this->redirectToRoute('index');

        $obj = $this->getDoctrine()->getRepository('MainBundle:Info')->findOneBy(['code'=>$code,'enabled'=>1]);
        if(!$obj)
            return $this->redirectToRoute('index'); //404

        $pageParams = $this->getBases($request);
        $this->get('main.seo')->update($obj);
        $pageParams['object'] = &$obj;

        //main.breadcrumbs
        $breadCrumbs = $this->get('main.breadcrumbs')->add($obj->getTitle(),$this->generateUrl('info_element',['code'=>$obj->getCode()]));
        $pageParams['breadcrumbs'] = &$breadCrumbs;

        return $this->render('MainBundle:Text:info_element.html.twig',$pageParams);
    }


}