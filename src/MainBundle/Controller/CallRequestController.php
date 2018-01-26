<?php
namespace MainBundle\Controller;

use MainBundle\Entity\CallRequest;
use MainBundle\Entity\CatalogSection;
use MainBundle\Entity\EmailTemplate;
use MainBundle\Entity\Product;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;

class CallRequestController extends BaseController
{
    /**
     * @Route("/call_request", name="call_request")
     */
    public function indexAction(Request $request)
    {
        if($request->getMethod() == 'POST'){
            $callRequest = new CallRequest();
            $callRequest->setName($request->request->get('name'));
            $callRequest->setPhone($request->request->get('phone'));

            $errors = $this->get('validator')->validate($callRequest);
            if (count($errors) > 0)
                return JsonResponse::create('false',400);

            $manager = $this->getDoctrine()->getManager();
            $manager->persist($callRequest);
            $manager->flush();

            //email to user
            $code = 'call_back_request';
            $conf = $this->get('admin.config')->getData('config');
            if(isset($conf['email'])){
                $data = [
                    'emailFrom'=>$conf['email'],
                    'emailTo'=>$conf['email'],
                    'NAME'=>$callRequest->getName(),
                    'PHONE'=>$callRequest->getPhone()
                ];

                EmailTemplate::sendEmail($code,$data,$this);
            }

            return JsonResponse::create('true',200);
        }

        if(!$request->isXmlHttpRequest())
            return $this->redirectToRoute('index');

        return JsonResponse::create('false',400);

    }
}