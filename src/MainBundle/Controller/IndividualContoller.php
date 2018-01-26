<?php
namespace MainBundle\Controller;

use MainBundle\Entity\EmailTemplate;
use MainBundle\Entity\IndividualBouquetRequest;
use MainBundle\Twig\MainExtension;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Exception\RouteNotFoundException;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class IndividualContoller extends BaseController
{
    /**
     * @Route("/individual/{id}/{hash}", name="route_individual_payment_success")
     */
    public function paymentSuccessAction(Request $request, $id, $hash){
        /**
         * @var IndividualBouquetRequest $individual
         */
        $individual = $this->getDoctrine()->getRepository('MainBundle:IndividualBouquetRequest')->find($id);
        if(
            !$individual
            ||
            $individual->getStatus() != IndividualBouquetRequest::NEW_STATUS
            ||
            $individual->getPayment() != IndividualBouquetRequest::ONLINE_PAYMENT
            ||
            $hash != $this->getHash($individual)
        ){
            return $this->redirectToRoute('index');
        }

        $individual->setStatus(IndividualBouquetRequest::PAID_STATUS);

        $em = $this->getDoctrine()->getManager();
        $em->persist($individual);
        $em->flush();

        $pageParams = $this->getBases($request);
        return $this->render('MainBundle:Text:individual_payment_success.html.twig',$pageParams);
    }

    /**
     * @Route("/individual/{id}", name="route_individual_payment")
     */
    public function paymentAction(Request $request, $id){
        /**
         * @var IndividualBouquetRequest $individual
         */
        $individual = $this->getDoctrine()->getRepository('MainBundle:IndividualBouquetRequest')->find($id);
        if(!$individual || $individual->getStatus() != IndividualBouquetRequest::NEW_STATUS || $individual->getPayment() != IndividualBouquetRequest::ONLINE_PAYMENT){
            return $this->redirectToRoute('index');
        }

        $pageParams = $this->getBases($request);
        $pageParams['button'] = $this->getPaymentButton(
            $this->get('router')->generate('route_individual_payment_success',['id'=>$individual->getId(), 'hash'=>$this->getHash($individual)],UrlGeneratorInterface::ABSOLUTE_URL),
            $individual->getPrice(),
            'Заказ индивидуального букета №'.$individual->getId()
        );
        return $this->render('MainBundle:Text:individual_payment.html.twig',$pageParams);
    }

    /**
     * @Route("/individual_request", name="route_individual_request")
     */
    public function indexRequestAction(Request $request)
    {
        if($request->getMethod() == 'POST'){
            $individualRequest = new IndividualBouquetRequest();
            $params = $this->get('admin.config')->getData('individual');

            $individualRequest->setStatus(IndividualBouquetRequest::NEW_STATUS);
            $individualRequest->setPhoneSender($request->request->get('phone'));
            $individualRequest->setPhoneRecipient($request->request->get('phoneRecipient'));
            $individualRequest->setAddress($request->request->get('address'));
            $individualRequest->setEmail($request->request->get('email'));
            $individualRequest->setComment($request->request->get('comment'));
            $individualRequest->setNegativeFlower($request->request->get('negativeFlower'));
            $individualRequest->setOccasion($request->request->get('occasion'));
            $individualRequest->setTarget($request->request->get('target'));
            $individualRequest->setPayment(
                $request->request->get('payment') == IndividualBouquetRequest::ONLINE_PAYMENT ? IndividualBouquetRequest::ONLINE_PAYMENT : IndividualBouquetRequest::CASH_PAYMENT
            );

            //delivery date
            $formatListener = [];
            $dateListener = [];
            $time = $request->request->get('time');
            if($time){
                $dateListener[] = $time;
                $formatListener[] = 'H:i';
            }
            $date = $request->request->get('date');
            if($date){
                $dateListener[] = $date;
                $formatListener[] = 'd.m.Y';
            }

            $formatListener = implode(' ', $formatListener);
            if($dateListener){
                $dateListener = implode(' ',$dateListener);
                $dateTime = new \DateTime($dateListener);
                $individualRequest->setDeliveryDate($dateTime);
            }

            //price taken from params
            $basePrice = $totalPrice = (isset($params['price'])?intval($params['price']):0);

            //check params
            $individualRequest->addParamElement('Базовая цена',$basePrice);

            $paramsInformation = [
                [
                    'key'=>'category',
                    'title'=>'Категория цветов'
                ],
                [
                    'key'=>'size',
                    'title'=>'Размер букета'
                ],
                [
                    'key'=>'shape',
                    'title'=>'Форма букета'
                ],
                [
                    'key'=>'kind',
                    'title'=>'Вид букета'
                ],
                [
                    'key'=>'gamma',
                    'title'=>'Цветовая гамма'
                ],
                [
                    'key'=>'package',
                    'title'=>'Вид упаковки'
                ]
            ];

            foreach ($paramsInformation as $info){
                $value = $request->request->get($info['key']);
                $key = $info['key'].'_params';
                if($value && isset($params[$key])){
                    $obj = $this->getParamsTitleObject($params[$key], $value);
                    if($obj){
                        $markup = isset($obj['markup'])?intval($obj['markup']):0;
                        $individualRequest->addParamElement(
                            sprintf('%s: %s', $info['title'], $obj['title']),
                            $markup
                        );
                        $totalPrice += $markup;
                    }
                }
            }

            $individualRequest->setPrice($totalPrice);
            $errors = $this->get('validator')->validate($individualRequest);
            if ($errors->count() > 0)
                return JsonResponse::create('false',400);

            $manager = $this->getDoctrine()->getManager();
            $manager->persist($individualRequest);
            $manager->flush();

            $paramsCallback = function ($a){
                $str = '';
                if(isset($a['title']))
                    $str = $a['title'];

                if(isset($a['markup']) && $a['markup'])
                    $str .= (preg_match('/:/',$str)?' с наценкой ':' ').MainExtension::priceFormatter($a['markup']);

                if($str)
                    $str = sprintf("<p>%s</p>",$str);

                return $str;
            };

            //email to admin
            $code = 'individual_request';
            $conf = $this->get('admin.config')->getData('config');
            if(isset($conf['email'])){
                $data = [
                    'emailFrom'=>$conf['email'],
                    'emailTo'=>$conf['email'],
                    'EMAIL'=>$individualRequest->getEmail()?:'-',
                    'PHONE'=>$individualRequest->getPhoneSender()?:'-',
                    'PHONE_RECIPIENT'=>$individualRequest->getPhoneRecipient()?:'-',
                    'ADDRESS'=>$individualRequest->getAddress()?:'-',
                    'DATE'=>$individualRequest->getDeliveryDate()?$individualRequest->getDeliveryDate()->format($formatListener):'-',
                    'COMMENT'=>$individualRequest->getComment()?:'-',
                    'IGNORE_FLOWER'=>$individualRequest->getNegativeFlower()?:'-',
                    'OCCASION'=>$individualRequest->getOccasion()?:'-',
                    'TARGET'=>$individualRequest->getTarget()?:'-',
                    'PRICE'=>MainExtension::priceFormatter($individualRequest->getPrice()),
                    'PARAMS'=>implode('',array_map($paramsCallback, $individualRequest->getParams())),
                    'PAYMENT'=>$individualRequest->getPayment() == IndividualBouquetRequest::CASH_PAYMENT ? 'наличными' : 'онлайн' ,
                    'ID'=>$individualRequest->getId(),
                ];

                EmailTemplate::sendEmail($code,$data,$this);
            }

            if($individualRequest->getPayment() != IndividualBouquetRequest::ONLINE_PAYMENT) {
                return JsonResponse::create('true', 200);
            } else {
                return JsonResponse::create([
                    'link'=>$this->generateUrl('route_individual_payment',['id'=>$individualRequest->getId()])
                ],200);
            }
        }

        if(!$request->isXmlHttpRequest())
            return $this->redirectToRoute('index');

        return JsonResponse::create('false',400);
    }

    /**
     * @Route("/individual", name="route_individual")
     */
    public function indexAction(Request $request)
    {
        $params = $this->get('admin.config');
        $pageParams = $this->getBases($request);
        $pageParams['page'] = $params->getData('individual');
        $pageParams['config_params'] = $params->getData('config');

        $restrictList = [
            'category',
            'size',
            'shape',
            'kind',
            'gamma',
            'package'
        ];
        $pageParams['blocks'] = [];
        foreach ($restrictList as $row){
            $obj = $this->getRestrictParamsFields($row,$pageParams['page']);
            if($obj){
                $pageParams['blocks'][$row] = $obj;
            }
        }

        //main.breadcrumbs
        $breadCrumbs = $this->get('main.breadcrumbs')
                            ->add('individual_title', $this->generateUrl('route_individual'));

        $pageParams['breadcrumbs'] = &$breadCrumbs;

        return $this->render('MainBundle:Text:individual.html.twig',$pageParams);
    }

    private function getParamsTitleObject($array, $title){
        foreach ($array as $row){
            if(isset($row['title']) && $row['title'] == $title){
                return $row;
            }
        }
        return false;
    }

    private function getRestrictParamsFields($key, $paramList){
        if(
            !isset($paramList[$key.'_show'])
        &&
            !$paramList[$key.'_show']
        &&
            !isset($paramList[$key.'_params'])
        )
            return false;

        $noMarkUp = true;
        foreach ($paramList[$key.'_params'] as $row){
            if(isset($row['markup']) && $row['markup'] != 0){
                $noMarkUp = false;
                break;
            }
        }

        return [
            'title'=>isset($paramList[$key.'_title'])?$paramList[$key.'_title']:'',
            'subtitle'=>$noMarkUp?'<p>(не влияет на цену)</p>':'',
            'picture'=>isset($paramList[$key.'_picture'])?$paramList[$key.'_picture']:'',
            'params'=>$paramList[$key.'_params']
        ];
    }

    public function getHash(IndividualBouquetRequest $a){
        return md5(sprintf('%d_%s-%d',$a->getId(), $a->getEmail(), $a->getId()));
    }
}