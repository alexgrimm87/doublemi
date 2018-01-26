<?php

namespace MainBundle\Controller;

use AdminBundle\Form\Type\DateTimeStringType;
use Doctrine\DBAL\Types\BooleanType;
use JMS\SerializerBundle\JMSSerializerBundle;
use MainBundle\Entity\Basket;
use MainBundle\Entity\BasketObject;
use MainBundle\Entity\CatalogPayment;
use MainBundle\Entity\CatalogSection;
use MainBundle\Entity\EmailTemplate;
use MainBundle\Entity\OrderRequest;
use MainBundle\Entity\Package;
use MainBundle\Entity\Product;
use MainBundle\Twig\MainExtension;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use UserBundle\Entity\User;
use UserBundle\UserBundle;
use JMS\Serializer\Exception\UnsupportedFormatException;

class BasketController extends BaseController
{
    ///     /**
    ///      * @var OrderRequest $oldOrder
    ///      */
    ///     $oldOrder = $this->getDoctrine()->getRepository('MainBundle:OrderRequest')->findOneBy([]);
    ///     $basket = new Basket();
    ///     $basket->setObjects($oldOrder->getObjects());
    ///     $service = $this->get('main.basket');
    ///     $service->init($basket, $this);
    ///     $objects = $service->getBasketObjectList();
    ///     $str = $this->renderView('MainBundle:Catalog:orderObjectsList.html.twig',['order'=>$oldOrder,'list'=>$objects]);

    /**
     * @Route("/basket/success/{id}/{hash}", name="basket_payment_success")
     */
    public function paymentSuccessAction(Request $request, $id='', $hash=''){
        //get order
        /**
         * @var OrderRequest $order
         */
        $order = $this->getDoctrine()->getRepository('MainBundle:OrderRequest')->find($id);
        if(!$order && $order->getPaymentInfo() != $hash) //404
            return $this->redirectToRoute('index');

        //check payment and status
        if($order->getStatus() != OrderRequest::STATUS_NEW || !$order->getCatalogPayment() || !$order->getCatalogPayment()->getService()){
            return $this->redirectToRoute('index');
        }

        //check permissions
        $noPermission = true;
        $user = $this->getUser();
        if($user){
            if($order->getUser() && $order->getUser()->getId() == $user->getId()){
                $noPermission = false;
            }
        } else {
            if(!$order->getUser()){
                $noPermission = false;
            }
        }

        if($noPermission){ //403
            return $this->redirectToRoute('index');
        }

        $order->setStatus(OrderRequest::STATUS_PAYED);
        $manager = $this->getDoctrine()->getManager();
        $manager->persist($order);
        $manager->flush();

        //show thx message
        $pageParams = $this->getBases($request);
        return $this->render('MainBundle:Catalog:payment_success.html.twig',$pageParams);
    }

    /**
     * @Route("/basket/payment/{id}", name="basket_payment")
     */
    public function paymentAction(Request $request, $id=''){
        //get order
        /**
         * @var OrderRequest $order
         */
        $order = $this->getDoctrine()->getRepository('MainBundle:OrderRequest')->find($id);
        if(!$order) //404
            return $this->redirectToRoute('index');

        //check payment and status
        if($order->getStatus() != OrderRequest::STATUS_NEW || !$order->getCatalogPayment() || !$order->getCatalogPayment()->getService()){
            return $this->redirectToRoute('index');
        }

        //check permissions
        $noPermission = true;
        $user = $this->getUser();
        if($user){
            if($order->getUser() && $order->getUser()->getId() == $user->getId()){
                $noPermission = false;
            }
        } else {
            if(!$order->getUser()){
                $noPermission = false;
            }
        }

        if($noPermission){ //403
            return $this->redirectToRoute('index');
        }

        $order->setPaymentInfo($this->generateHash($order));
        $manager = $this->getDoctrine()->getManager();
        $manager->persist($order);
        $manager->flush();

        $pageParams = $this->getBases($request);
        $pageParams['button'] = $this->getPaymentButton(
            $this->get('router')->generate('basket_payment_success',['id'=>$order->getId(), 'hash'=>$order->getPaymentInfo()],UrlGeneratorInterface::ABSOLUTE_URL),
            $order->getSumm(),
            'Заказ №'.$order->getId()
        );
        return $this->render('MainBundle:Catalog:payment.html.twig',$pageParams);
    }

    protected function generateHash(OrderRequest $orderRequest){
        return md5(sprintf('%d-%s-%d',$orderRequest->getId(),$orderRequest->getEmail(),$orderRequest->getId()));
    }

    /**
     * @Route("/one_click_order", name="one_click_order")
     */
    public function oneClickOrder(Request $request){
        if($request->getMethod() != 'POST')
            return $this->redirectToRoute('index');

        $session = new Session();
        try {
            $session->start();
        }catch (\RuntimeException $e){

        }

        /**
         * @var BasketObject $data
         * @var Basket $basket
         */
        /// get basket object
        $data = $this->getRequestBasketObject($request);
        if(is_array($data)) //if error
            return new JsonResponse($data);

        /// get active basket
        $basket = New Basket();

        // get basket service for add element
        $service = $this->get('main.basket');
        $service->init($basket, $this);
        $service->addObject($data);
        $basket = $service->getBasket();

        $name = strval($request->request->get('name'));
        $phone = strval($request->request->get('phone'));

        $orderRequest = new OrderRequest();
        $orderRequest->setName($name);
        $orderRequest->setPhone($phone);

        $orderRequest->setObjects($basket->getObjects());
        $orderRequest->setStatus(OrderRequest::STATUS_NEW);
        $orderRequest->setOneClickOrder(true);

        $params = $service->getInformationParams();

        $orderRequest->setSumm($params['price']);
        /**
         * @var User $user
         */
        $user = $this->getUser();
        if($user){
            $orderRequest->setEmail($user->getEmail());
            $orderRequest->setUser($user);
        }

        $validator = $this->get('validator');
        $errors = $validator->validate($orderRequest);
        if ($errors->count() == 0) {
            //save order
            $manager = $this->getDoctrine()->getManager();
            $manager->persist($orderRequest);
            $manager->flush();
            $pageParams['form_params']['message'] = 'Заказ оформлен';

            $this->sendOrderEmail($orderRequest, $service->getBasketObjectList());

        } else {
            $pageParams['form_params']['error'] = 'Ошибка создания заказа, перезагрузите страницу и попробуйте еще раз'.$errors;
        }
        if($request->isXmlHttpRequest()){
            return new JsonResponse($pageParams['form_params']);
        }
        return $this->redirectToRoute('root_basket');
    }

    /**
     * @Route("/basket", name="root_basket")
     */
    public function basketAction(Request $request){
        $pageParams = $this->getBases($request);
        $conf = $this->get('admin.config')->getData('config');

        $pageParams['breadcrumbs'] = $this->get('main.breadcrumbs')
                                          ->add('Оформление заказа', $this->generateUrl('root_basket'));

        $pageParams['price_limit'] = $deliveryPriceLimit = isset($conf['free_delivery_limit']) && intval($conf['free_delivery_limit'])>=0 ? intval($conf['free_delivery_limit']) : 0 ;
        $pageParams['price_delivery'] = $deliveryPrice = isset($conf['delivery_price']) && intval($conf['delivery_price'])>=0 ? intval($conf['delivery_price']) : 0 ;

        $session = new Session();
        try {
            $session->start();
        }catch (\RuntimeException $e){

        }

        /// get active basket
        /**
         * @var Basket $basket
         */
        $basket = $this->getBasket($session);

        // get basket service
        $service = $this->get('main.basket');
        $service->init($basket, $this);

        // validate and save active basket
        $service->validateObjects($this->get('validator'));
        $basket = $service->getBasket();
        $this->saveBasket($basket,$session);

        $pageParams['basket'] = $service->getBasketObjectList();
        $pageParams['basket_params'] = $service->getInformationParams();
        $pageParams['form_params'] = [];
        if($pageParams['basket']){ //if not empty basket
            /**
             * @var User $user
             */
            $user = $this->getUser();

            //build form
            /**
             * @var OrderRequest $orderRequest
             * @var OrderRequest $order
             */
            $orderRequest = new OrderRequest();

            if($user){ // check for old order
                $oldOrder = $this->getDoctrine()->getRepository('MainBundle:OrderRequest')->findBy(['user'=>$user],['date'=>'DESC'],1);
                if($oldOrder){
                    foreach ($oldOrder as $order){
                        $orderRequest->setName($order->getName());
                        $orderRequest->setPhone($order->getPhone());
                        $orderRequest->setCatalogPayment($order->getCatalogPayment());
                        $orderRequest->setDeliveryCity($order->getDeliveryCity());
                        $orderRequest->setDeliveryStreet($order->getDeliveryStreet());

                        $orderRequest->setDeliveryName($order->getDeliveryName());
                        $orderRequest->setDeliveryPhone($order->getDeliveryPhone());

                        $orderRequest->setOrgName($order->getOrgName());
                        $orderRequest->setUriAddress($order->getUriAddress());
                        $orderRequest->setFactAddress($order->getFactAddress());
                        $orderRequest->setUriPhone($order->getUriPhone());
                        $orderRequest->setInn($order->getInn());
                        $orderRequest->setBankName($order->getBankName());
                        $orderRequest->setBankCheckingIndex($order->getBankCheckingIndex());
                        $orderRequest->setBankCorrespondentIndex($order->getBankCorrespondentIndex());
                        $orderRequest->setBankIndex($order->getBankIndex());
                        break;
                    }
                } else { //get data from user profile
                    $orderRequest->setOrgName($user->getOrgName());
                    $orderRequest->setUriAddress($user->getUriAddress());
                    $orderRequest->setFactAddress($user->getFactAddress());
                    $orderRequest->setUriPhone($user->getUriPhone());
                    $orderRequest->setInn($user->getInn());
                    $orderRequest->setBankName($user->getBankName());
                    $orderRequest->setBankCheckingIndex($user->getBankCheckingIndex());
                    $orderRequest->setBankCorrespondentIndex($user->getBankCorrespondentIndex());
                    $orderRequest->setBankIndex($user->getBankIndex());
                }
            }

            $form = $this->createFormBuilder($orderRequest,['csrf_protection' => false])
                         ->add('name',HiddenType::class)
                         ->add('phone',HiddenType::class)
                         ->add('selfReceive',CheckboxType::class)
                         ->add('incognito',CheckboxType::class)
                         ->add('freeCard',CheckboxType::class)
                         ->add('cardText',HiddenType::class)
                         ->add('cardSign',HiddenType::class)
                         ->add('delivery',ChoiceType::class,['choices'=>OrderRequest::$delivery_list])
                         ->add('callForAddress',CheckboxType::class)
                         ->add('deliveryDate', DateTimeStringType::class)
                         ->add('deliveryCity',HiddenType::class)
                         ->add('deliveryName',HiddenType::class)
                         ->add('deliveryPhone',HiddenType::class)
                         ->add('deliveryStreet',HiddenType::class)
                         ->add('comment',HiddenType::class)
                         ->add('catalogPayment',EntityType::class,['class'=>CatalogPayment::class])

                        ->add('orgName', HiddenType::class)
                        ->add('uriAddress', HiddenType::class)
                        ->add('factAddress', HiddenType::class)
                        ->add('uriPhone', HiddenType::class)
                        ->add('inn', HiddenType::class)
                        ->add('bankName', HiddenType::class)
                        ->add('bankCheckingIndex', HiddenType::class)
                        ->add('bankCorrespondentIndex', HiddenType::class)
                        ->add('bankIndex', HiddenType::class)
            ;
            if(!$user){
                $form->add('email',EmailType::class);
            }

            $form = $form->getForm();

            if($request->getMethod() == 'POST') { //submit form
                $form->handleRequest($request);
                $orderRequest = $form->getData();

                //registration
                if(!$user){
                    $form = $request->request->get('form');
                    if(isset($form['need_registration']) && $form['need_registration'] == 1){
                        $email = $orderRequest->getEmail();
                        $pass = isset($form['password']) ? $form['password'] : false;
                        $passConfirm = isset($form['password_confirm']) ? $form['password_confirm'] : false;

                        if($pass && $pass == $passConfirm) {
                            $userManager = $this->get('fos_user.user_manager');
                            //check email
                            $email_exist = $userManager->findUserByEmail($email);
                            if($email_exist){
                                $pageParams['form_params']['error'] = sprintf('Ошибка регистрации, e-mail "%s" уже используется',$email);
                            } else {
                                $user = $userManager->createUser();
                                $user->setUsername($email);
                                $user->setEmail($email);
                                $user->addRole('ROLE_USER');
                                $user->setEmailCanonical($email);
                                $user->setLocked(0); // don't lock the user
                                $user->setEnabled(1); // enable the user or enable it later with a confirmation token in the email
                                // this method will encrypt the password with the default settings :)
                                $user->setPlainPassword($pass);
                                $userManager->updateUser($user);
                            }
                        } else {
                            $pageParams['form_params']['error'] = 'Ошибка регистрации, пароли не совпадают';
                        }
                    }
                }


                if(!isset($pageParams['form_params']['error'])) { //if registration problems
                    $service->buildDataObjects();
                    $basket = $service->getBasket();
                    //add params
                    $orderRequest->setObjects($basket->getObjects());
                    $orderRequest->setStatus(OrderRequest::STATUS_NEW);
                    $orderRequest->setSumm($pageParams['basket_params']['price']);
                    $orderRequest->setOneClickOrder(false);

                    //delivery logic
                    if ($deliveryPrice && $orderRequest->getDelivery() == OrderRequest::DELIVERY_SIMPLE) {
                        if ($deliveryPriceLimit > 0 && $orderRequest->getSumm() >= $deliveryPriceLimit) {
                            $deliveryPrice = 0;
                        }
                        $orderRequest->setDeliveryPrice($deliveryPrice);
                        $orderRequest->setSumm($orderRequest->getSumm() + $deliveryPrice);
                    }

                    if ($user) {
                        $orderRequest->setEmail($user->getEmail());
                        $orderRequest->setUser($user);
                    }

                    $validator = $this->get('validator');
                    $errors = $validator->validate($orderRequest);
                    if ($errors->count() == 0) {
                        //save order
                        $manager = $this->getDoctrine()->getManager();
                        $manager->persist($orderRequest);
                        $manager->flush();
                        $pageParams['form_params']['message'] = 'Заказ оформлен';

                        //payment stuff
                        if ($orderRequest->getCatalogPayment() && $orderRequest->getCatalogPayment()->getService()) {
                            //todo: service magic
                            $pageParams['form_params']['redirection'] = $this->get('router')->generate('basket_payment',['id'=>$orderRequest->getId()]);

                            $this->sendOrderEmail($orderRequest, $pageParams['basket']);
                        } else {
                            // redirect
                            $pageParams['form_params']['redirect'] = $this->generateUrl('index');

                            $this->sendOrderEmail($orderRequest, $pageParams['basket']);
                        }

                        //clear basket
                        $this->clearBasket($basket, $session);
                        $service->init($basket, $this);
                        $pageParams['basket'] = $service->getBasketObjectList();

                    } else {
                        $pageParams['form_params']['error'] = 'Ошибка заполнения формы, перезагрузите страницу и попробуйте еще раз';
                    }
                }

                if($request->isXmlHttpRequest()){
                    return new JsonResponse($pageParams['form_params']);
                }
            }

            $pageParams['form_params']['name'] = $orderRequest->getName();
            $pageParams['form_params']['phone'] = $orderRequest->getPhone();
            $pageParams['form_params']['payment'] = $orderRequest->getCatalogPayment() ? $orderRequest->getCatalogPayment()->getId() : false;
            $pageParams['form_params']['delivery_city'] = $orderRequest->getDeliveryCity();
            $pageParams['form_params']['delivery_street'] = $orderRequest->getDeliveryStreet();

            $pageParams['form_params']['deliveryName'] = $orderRequest->getDeliveryName();
            $pageParams['form_params']['deliveryPhone'] = $orderRequest->getDeliveryPhone();

            $pageParams['form_params']['orgName'] = $orderRequest->getDeliveryStreet();
            $pageParams['form_params']['uriAddress'] = $orderRequest->getDeliveryStreet();
            $pageParams['form_params']['factAddress'] = $orderRequest->getDeliveryStreet();
            $pageParams['form_params']['uriPhone'] = $orderRequest->getDeliveryStreet();
            $pageParams['form_params']['inn'] = $orderRequest->getDeliveryStreet();
            $pageParams['form_params']['bankName'] = $orderRequest->getDeliveryStreet();
            $pageParams['form_params']['bankCheckingIndex'] = $orderRequest->getDeliveryStreet();
            $pageParams['form_params']['bankCorrespondentIndex'] = $orderRequest->getDeliveryStreet();
            $pageParams['form_params']['bankIndex'] = $orderRequest->getDeliveryStreet();

            $pageParams['form'] = $form->createView();

            $pageParams['payment_list'] = $this->getDoctrine()->getRepository('MainBundle:CatalogPayment')->findBy(['enabled'=>'1'],['pos'=>'ASC']);
        }

        return $this->render('MainBundle:Catalog:basket.html.twig',$pageParams);
    }

    public function sendOrderEmail(OrderRequest $orderRequest, $basketList){
        $conf = $this->get('admin.config')->getData('config');
        if(!isset($conf['email']))
            return;

        if($orderRequest->isOneClickOrder()){
            $templateCode = 'order_request_one_click';
            $emails = [$conf['email']];
            if($orderRequest->getEmail())
                $emails[] = $orderRequest->getEmail();

            $emailParams = [
                'emailFrom'=>$conf['email'],
                'emailTo'=>$emails,
                'ID' => $orderRequest->getId(),
                'NAME' => $orderRequest->getName(),
                'PHONE' => $orderRequest->getPhone(),
                'ITEMS' => $this->renderView('MainBundle:Catalog:orderObjectsList.html.twig', ['order' => $orderRequest, 'list' => $basketList])
            ];

            EmailTemplate::sendEmail($templateCode,$emailParams,$this);
        } else {
            $templateCode = 'order_request';
            $textGroup = '';

            if ($orderRequest->getDelivery() == OrderRequest::DELIVERY_SELF_LIFT) {
                $deliveryText = 'Самовывоз';
            } else {
                $deliveryText = 'Доставка<br>';
                if ($orderRequest->isCallForAddress())
                    $deliveryText .= 'уточнить адрес через телефон<br>';

                if ($orderRequest->getDeliveryCity()) {
                    $deliveryText .= sprintf('Город: %s<br>', $orderRequest->getDeliveryCity());
                }

                if ($orderRequest->getDeliveryStreet()) {
                    $deliveryText .= sprintf('Улица: %s<br>', $orderRequest->getDeliveryStreet());
                }

                if ($orderRequest->getDeliveryDate()) {
                    $deliveryText .= sprintf('Дата доставки: %s<br>', $orderRequest->getDeliveryStreet());
                }

                $deliveryText .= 'Цена доставки: '.MainExtension::priceFormatter($orderRequest->getDeliveryPrice());
            }
            if ($orderRequest->isSelfReceive())
                $textGroup .= "Получает товар сам<br>";

            if ($orderRequest->isIncognito())
                $textGroup .= "Прозьба не разглашать имя<br>";

            if ($orderRequest->isIncognito())
                $textGroup .= "Добавить бесплатную открытку<br>";

            if ($orderRequest->getCardText())
                $textGroup .= "Тест открытки:<br>" . $orderRequest->getCardText();

            if ($orderRequest->getCardSign())
                $textGroup .= "Подпись открытки:<br>" . $orderRequest->getCardSign();

            $paymentText = '';
            if ($orderRequest->getCatalogPayment()) {
                $paymentText = $orderRequest->getCatalogPayment()->getTitle();
            }

            $emailParams = [
                'emailFrom'=>$conf['email'],
                'emailTo'=>[
                    $conf['email'],
                    $orderRequest->getEmail()
                ],
                'ID' => $orderRequest->getId(),
                'NAME' => $orderRequest->getName(),
                'PHONE' => $orderRequest->getPhone(),
                'TEXT_GROUP' => $textGroup,
                'DELIVERY' => $deliveryText,
                'COMMENT' => $orderRequest->getComment() ?: 'отсутствует',
                'PAYMENT' => $paymentText,
                'ITEMS' => $this->renderView('MainBundle:Catalog:orderObjectsList.html.twig', ['order' => $orderRequest, 'list' => $basketList])
            ];

            EmailTemplate::sendEmail($templateCode,$emailParams,$this);
        }
    }

    /**
     * time
     * date
     * count
     * product
     * service
     * package
     * size
     *
     * @Route("/add_2_basket", name="add_to_basket")
     */
    public function addToBasketAction(Request $request){
        if($request->getMethod() != 'POST')
            return $this->redirectToRoute('index');

        $session = new Session();
        try {
            $session->start();
        }catch (\RuntimeException $e){

        }

        /// get basket object
        $data = $this->getRequestBasketObject($request);
        if(is_array($data)) //if error
            return new JsonResponse($data);

        /// get active basket
        $basket = $this->getBasket($session);

        // get basket service for add element
        $service = $this->get('main.basket');
        $service->init($basket, $this);
        $service->addObject($data);

        // save active basket
        $service->validateObjects($this->get('validator'));
        $basket = $service->getBasket();
        $this->saveBasket($basket,$session);

        return $this->basketResponse($service, $basket);
    }

    /**
     * @Route("/get_basket", name="get_basket")
     */
    public function getBasketAction(Request $request){
        $session = new Session();
        try {
            $session->start();
        }catch (\RuntimeException $e){

        }

        /// get active basket
        $basket = $this->getBasket($session);

        // get basket service
        $service = $this->get('main.basket');
        $service->init($basket, $this);

        // validate and save active basket
        $service->validateObjects($this->get('validator'));
        $basket = $service->getBasket();
        $this->saveBasket($basket,$session);

        return $this->basketResponse($service, $basket);
    }

    /**
     * @Route("/remove_basket/{key}", name="remove_from_basket")
     */
    public function removeFromBasket(Request $request, $key=''){
        if($request->getMethod() == 'GET')
            return $this->redirectToRoute('index');

        $session = new Session();
        try {
            $session->start();
        }catch (\RuntimeException $e){

        }

        /// get active basket
        $basket = $this->getBasket($session);

        // get basket service
        $service = $this->get('main.basket');
        $service->init($basket, $this);
        $service->removeObject($key);

        // validate and save active basket
        $service->validateObjects($this->get('validator'));
        $basket = $service->getBasket();
        $this->saveBasket($basket,$session);

        return $this->basketResponse($service, $basket);
    }

    /**
     * @Route("/count_basket/{key}", name="change_count_basket")
     */
    public function changeCountBasket(Request $request, $key=''){
        $count = intval($request->get('count'));
        if($count<1){
            return new JsonResponse('false');
        }

        $session = new Session();
        try {
            $session->start();
        }catch (\RuntimeException $e){

        }

        /// get active basket
        $basket = $this->getBasket($session);

        // get basket service
        $service = $this->get('main.basket');
        $service->init($basket, $this);
        $service->changeCountObject($key,$count);

        // validate and save active basket
        $service->validateObjects($this->get('validator'));
        $basket = $service->getBasket();
        $this->saveBasket($basket,$session);

        return $this->basketResponse($service, $basket);
    }

    public function basketResponse($service, $basket){
        return new JsonResponse($this->getBasketResponseArray($service, $basket));
    }

    public function getBasketResponseArray($service, $basket){
        return ['params'=>$service->getInformationParams(),'items'=>$basket->getObjects()];
    }

    /**
     * @param Basket $basket
     * @param $session
     */
    public function saveBasket(Basket &$basket, &$session){
        if($basket->getUser()){ //orm save
            $manager = $this->getDoctrine()->getManager();
            $manager->persist($basket);
            $manager->flush();
        } else { // session save
            $session->set('basket', $basket);
        }
    }

    public function clearBasket(Basket &$basket, &$session){
        if($basket->getObjects()){
            $basket->setObjects([]);
            if($basket->getUser() && $basket->getId()){
                $manager = $this->getDoctrine()->getManager();
                $manager->remove($basket);
                $manager->flush();
            } else { // session save
                $session->set('basket', $basket);
            }
        }
    }

    public function getBasket($session){
        $user = $this->getUser();
        $basket = null;
        if($user){
            //get ORM basket
            $basket = $this->getDoctrine()->getRepository('MainBundle:Basket')->findOneBy(['user'=>$user]);
        }

        if(!$basket){
            //get basket from session
            $basket = $session->get('basket');
            if(!$basket || !is_a($basket,'MainBundle\Entity\Basket')){
                $basket = null;
            }
        }

        if(!$basket){
            $basket = new Basket();

            if($user)
                $basket->setUser($user);
        }

        return $basket;
    }

    public function getRequestBasketObject(Request $request){
        $intCallBack = function ($a){
            return intval($a);
        };
        $data = new BasketObject();

        //delivery date
        $dateListener = [];
        $time = $request->request->get('time');
        if($time){
            $dateListener[] = $time;
        }
        $date = $request->request->get('date');
        if($date){
            $dateListener[] = $date;
        }

        if($dateListener){
            $dateListener = implode(' ',$dateListener);
            $dateTime = new \DateTime($dateListener);
            $data->setDate($dateTime);
        }
        /////
        $data->setCount($intCallBack($request->request->get('count'))?:1);
        $data->setProduct(
            $this->getDoctrine()->getRepository('MainBundle:Product')->findOneBy(['id'=>$intCallBack($request->request->get('product')), 'enabled'=>1])
        );

        if($data->getProduct()){
            $serv = $request->request->get('service');
            if(!is_array($serv))
                $serv = [$serv];

            $service = $this->getDoctrine()->getRepository('MainBundle:Service')->findBy(['id'=>array_map($intCallBack,$serv)]);
            if($service){
                foreach ($service as $s){
                    if($s->isEnabled() && $data->getProduct()->getServ()->contains($s)){
                        $data->addServ($s);
                    }
                }
            }
            /**
             * @var Package $package
             */
            $package = $this->getDoctrine()->getRepository('MainBundle:Package')->find($intCallBack($request->request->get('package')));
            if($package && $package->isEnabled() && $data->getProduct()->getPackage()->contains($package))
                $data->setPackage($package);

            // get size
            if($data->getProduct()->getSize()){
                $title = $request->request->get('size');
                //check if size title in list
                if($title){
                    foreach ($data->getProduct()->getSize() as $size ){
                        if(isset($size['title']) && $size['title'] == $title){
                            $data->setSize($title, isset($size['markup'])?$size['markup']:0);
                            break;
                        }
                    }
                }
            }
        }

        $errors = $this->get('validator')->validate($data);
        if ($errors->count() != 0){
            return ['error'=>'Ошибка добавления товара'];
        }
        return $data;
    }
}