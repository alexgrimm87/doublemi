<?php
namespace MainBundle\Controller;

use MainBundle\Entity\CallRequest;
use MainBundle\Entity\CatalogSection;
use MainBundle\Entity\EmailTemplate;
use MainBundle\Entity\Product;
use MainBundle\Entity\ServiceRequest;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;

class ServiceRequestController extends BaseController
{
    /**
     * @Route("/service_request", name="service_request")
     */
    public function indexAction(Request $request)
    {
        if($request->getMethod() == 'POST'){
            $serviceRequest = new ServiceRequest();
            $serviceRequest->setName($request->request->get('name'));
            $serviceRequest->setPhone($request->request->get('phone'));
            $serviceRequest->setEmail($request->request->get('email'));

            $errors = $this->get('validator')->validate($serviceRequest);
            if (count($errors) > 0)
                return JsonResponse::create('false',400);

            $manager = $this->getDoctrine()->getManager();
            $manager->persist($serviceRequest);
            $manager->flush();

            //email to user
            $code = 'service_request';
            $conf = $this->get('admin.config')->getData('config');
            if(isset($conf['email'])){
                $data = [
                    'emailFrom'=>$conf['email'],
                    'emailTo'=>$conf['email'],
                    'NAME'=>$serviceRequest->getName(),
                    'PHONE'=>$serviceRequest->getPhone(),
                    'EMAIL'=>$serviceRequest->getEmail(),
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