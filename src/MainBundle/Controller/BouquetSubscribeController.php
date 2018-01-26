<?php
namespace MainBundle\Controller;

use MainBundle\Entity\BouquetSubscribe;
use MainBundle\Entity\EmailTemplate;
use MainBundle\Entity\IndividualBouquetRequest;
use MainBundle\Twig\MainExtension;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class BouquetSubscribeController extends BaseController
{
    /**
     * @Route("/bouquet_subscribe_request", name="route_bouquet_subscribe_request")
     */
    public function requestAction(Request $request){
        if($request->getMethod() != 'POST')
            return $this->redirectToRoute('route_bouquet_subscribe');

        $subscribe = new BouquetSubscribe();
        $subscribe->setEmail($request->request->get('email'));
        $subscribe->setName($request->request->get('name'));
        $user = $this->getUser();
        if($user)
            $subscribe->setUser($user);

        $errors = $this->get('validator')->validate($subscribe);
        if (count($errors) > 0)
            return JsonResponse::create('false',400);

        $manager = $this->getDoctrine()->getManager();
        $manager->persist($subscribe);
        $manager->flush();

        //email to user
        $code = 'bouquet_subscribe_request';
        $conf = $this->get('admin.config')->getData('config');
        if(isset($conf['email'])){
            $data = [
                'emailFrom'=>$conf['email'],
                'emailTo'=>[
                    $conf['email'],

                ],
                'ID'=>$subscribe->getId(),
                'NAME'=>$subscribe->getName(),
                'EMAIL'=>$subscribe->getEmail()
            ];

            EmailTemplate::sendEmail($code,$data,$this);
        }

        return JsonResponse::create('true',200);
    }

    /**
     * @Route("/bouquet-subscribe", name="route_bouquet_subscribe")
     */
    public function indexAction(Request $request)
    {
        $params = $this->get('admin.config');
        $pageParams = $this->getBases($request);
        $pageParams['page'] = $params->getData('bouquet_subscribe');
        $pageParams['sub_page'] = $params->getData('main_a_page');
        $pageParams['config_params'] = $params->getData('config');
        $pageParams['review_list'] = false;
        if(isset($pageParams['page']['review_show']) && $pageParams['page']['review_show']){
            $pageParams['review_list'] = $this->getDoctrine()->getRepository('MainBundle:Feedback')->findBy(['showOnSubscribePage'=>1, 'enabled'=>1],['date'=>'DESC'],10);
        }

        return $this->render('MainBundle:Text:bouquet_subscribe.html.twig',$pageParams);
    }
}