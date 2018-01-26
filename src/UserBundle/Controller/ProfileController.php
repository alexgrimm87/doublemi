<?php
namespace UserBundle\Controller;

use JMS\Serializer\Tests\Fixtures\Order;
use MainBundle\Controller\BaseController;
use MainBundle\Entity\Basket;
use MainBundle\Entity\MailingSubscribe;
use MainBundle\Entity\OrderRequest;
use MainBundle\Service\BasketService;
use Symfony\Component\DependencyInjection\ContainerAware;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use FOS\UserBundle\Model\UserInterface;
use UserBundle\Entity\User;
use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\Session\Session;

/**
 * Controller managing the user profile
 *
 * @author Christophe Coevoet <stof@notk.org>
 */
class ProfileController extends BaseController
{
    const FILTER_KEY = 'filter';

    /**
     * Change user password
     */
    public function changePasswordAction(Request $request)
    {
        /**
         * @var User $user
         */
        $user = $this->container->get('security.context')->getToken()->getUser();
        if (!is_object($user) || !$user instanceof UserInterface) {
            throw new AccessDeniedException('This user does not have access to this section.');
        }
        $pageParams = $this->getProfileBases($request, 'fos_user_change_password');
        /**
         * @var Form $form
         */
        $form = $this->createFormBuilder(null,['csrf_protection' => false])
                        ->add('current',PasswordType::class)
                        ->add('new_one',PasswordType::class)
                        ->add('new_conform',PasswordType::class)
        ;

        $form = $form->getForm();

        if($request->getMethod() == 'POST') { //submit form
            $form->handleRequest($request);
            $data = $form->getData();

            if(
                isset($data['current']) && $data['current']
                &&
                isset($data['new_one']) && $data['new_one']
                &&
                isset($data['new_conform']) && $data['new_conform']
            ){
                $factory = $this->get('security.encoder_factory');
                $encoder = $factory->getEncoder($user);

                $bool = $encoder->isPasswordValid($user->getPassword(),$data['current'],$user->getSalt());
                if($bool){
                    if($data['new_one'] == $data['new_conform']){
                        if(strlen($data['new_one'])>=5){
                            $user->setPlainPassword($data['new_one']);
                            $userManager = $this->get('fos_user.user_manager');
                            $userManager->updateUser($user);
                            $pageParams['form_message'] = 'Пароль изменен успешно';
                        } else {
                            $pageParams['form_error'] = 'Пароль должен состоять как минимум с 5 символов';
                        }
                    } else {
                        $pageParams['form_error'] = 'Подтверждение пароля не совпадает';
                    }
                } else {
                    $pageParams['form_error'] = 'Неверно введен старый пароль';
                }
            }

        }

        $pageParams['form'] = $form->createView();

        return $this->render('MainBundle:Profile:password.html.twig', $pageParams);
    }

    public function ordersAction(Request $request){
        /**
         * @var User $user
         */
        $pageParams = $this->getProfileBases($request, 'user_profile_orders');

        $filterKey = self::FILTER_KEY;
        $limit = 10;
        $page = 1;
        $filter = [];

        $this->pageNavigationParams($request, $filter, $page, $limit);

        $pageParams['orders'] = [];
        if($user = $this->getUser()){
            $filter[$filterKey]['user'] = $user->getId();
            $filter['_sort']['date'] = 'DESC';

            $statusList = OrderRequest::$status_list;
            $pageParams['status_callback'] = function($a) use ($statusList) {
                return isset($statusList[$a]) ? $statusList[$a] : ' - ';
            };

            $pageParams['objects'] = $this->customMatching($filterKey,'MainBundle:OrderRequest',$filter);

            if($pageParams['objects']['items']){ // have items
                /**
                 * @var OrderRequest $v
                 * @var BasketService $service
                 */
                //restructure baskets
                $pageParams['restrict_objects'] = [];
                // get basket service
                $service = $this->get('main.basket');
                foreach ($pageParams['objects']['items'] as $k=>$v){
                    $basket = new Basket();
                    $basket->setObjects($v->getObjects()); //transport order items to basket element
                    $service->init($basket, $this);

                    $pageParams['restrict_objects'][] = [
                        'object'=>&$pageParams['objects']['items'][$k],
                        'basketElements'=>$service->getBasketObjectList()
                    ];
                }
            }

            // page navigation
            $pager = $this->get('pager_generator');
            $pager->setParams($pageParams['objects']['meta']['total'] , $limit);
            $pageParams['pager'] =  $pager->getPager($page);
        }


        return $this->render('MainBundle:Profile:orders.html.twig', $pageParams);
    }

    public function subscribeAction(Request $request){
        /**
         * @var User $user
         */
        $pageParams = $this->getProfileBases($request, 'user_profile_subscribe');

        $pageParams['subscribed'] = false;
        if($user = $this->getUser()){
            $obj = $this->getDoctrine()->getRepository('MainBundle:MailingSubscribe')->findOneBy(['email'=>$user->getEmail()]);
            if($obj){
                $pageParams['subscribed'] = true;
            }
            //save submitted form
            if($request->getMethod() == 'POST'){
                $letters = $request->request->get('letters');
                $letters = ($letters != 'no');

                if($letters && !$obj) { // need create subscribe and have not old one
                    $sub = new MailingSubscribe();
                    $sub->setEmail($user->getEmail());

                    $manager = $this->getDoctrine()->getManager();
                    $manager->persist($sub);
                    $manager->flush();
                } elseif(!$letters && $obj) { // need delete subscribe
                    $manager = $this->getDoctrine()->getManager();
                    $manager->remove($obj);
                    $manager->flush();
                }
            }
        }

        return $this->render('MainBundle:Profile:subscribe.html.twig', $pageParams);
    }

    /**
     * Show the user
     *
     */
    public function showAction(Request $request)
    {
        $user = $this->container->get('security.context')->getToken()->getUser();
        if (!is_object($user) || !$user instanceof UserInterface) {
            throw new AccessDeniedException('This user does not have access to this section.');
        }
        $pageParams = $this->getProfileBases($request);
        return $this->render('MainBundle:Profile:base.html.twig', $pageParams);
    }

    /**
     * Edit the user
     */
    public function editAction(Request $request)
    {
        /**
         * @var User $user
         */
        $user = $this->container->get('security.context')->getToken()->getUser();
        if (!is_object($user) || !$user instanceof UserInterface) {
            throw new AccessDeniedException('This user does not have access to this section.');
        }

        $pageParams = $this->getProfileBases($request, 'fos_user_profile_edit');

        $form = $this->createFormBuilder($user,['csrf_protection' => false])
                     ->add('firstname',HiddenType::class)
                     ->add('lastname',HiddenType::class)
                     ->add('dopEmail',EmailType::class)
                     ->add('phone', HiddenType::class)
                     ->add('inn', HiddenType::class)
        ;

        $form = $form->getForm();

        if($request->getMethod() == 'POST') { //submit form
            $form->handleRequest($request);
            $user = $form->getData();

            $validator = $this->get('validator');
            $errors = $validator->validate($user);

            if($errors->count() == 0 ){
                $userManager = $this->get('fos_user.user_manager');
                $userManager->updateUser($user);
            }

            if($request->isXmlHttpRequest()){
                return new JsonResponse('true');
            }
        }
        $pageParams['form'] = $form->createView();
        return $this->render('MainBundle:Profile:edit.html.twig', $pageParams);
    }

    /**
     * Generate the redirection url when editing is completed.
     *
     * @param \FOS\UserBundle\Model\UserInterface $user
     *
     * @return string
     */
    protected function getRedirectionUrl(UserInterface $user)
    {
        return $this->container->get('router')->generate('fos_user_profile_show');
    }

    /**
     * @param string $action
     * @param string $value
     */
    protected function setFlash($action, $value)
    {
        $this->container->get('session')->getFlashBag()->set($action, $value);
    }

    protected function getProfileBases($request, $activeRoute = ''){
        $pageParams = $this->getBases($request);
        $this->get('main.breadcrumbs')->add('Личный кабинет', $this->generateUrl('fos_user_profile_show'));
        $pageParams['profile_menu'] = $this->getMenu($activeRoute);
        $pageParams['breadcrumbs'] = $this->get('main.breadcrumbs');

        return $pageParams;
    }

    protected function getMenu($activeRoute = ''){
        $res = [
            [
                'route'=>'fos_user_profile_show',
                'active'=>false,
                'title'=>'Моя информация',
                'picture'=>false
            ],
            [
                'route'=>'fos_user_profile_edit',
                'active'=>false,
                'title'=>'Редактировать профиль',
                'picture'=>'/images/person.png'
            ],
            [
                'route'=>'fos_user_change_password',
                'active'=>false,
                'title'=>'Пароль',
                'picture'=>'/images/password.png'
            ],
            [
                'route'=>'user_profile_orders',
                'active'=>false,
                'title'=>'История заказов',
                'picture'=>'/images/clock.png'
            ],
            [
                'route'=>'user_profile_subscribe',
                'active'=>false,
                'title'=>'E-mail рассылка',
                'picture'=>'/images/letter.png'
            ],
            [
                'route'=>'fos_user_security_logout',
                'active'=>false,
                'title'=>'Выход',
                'picture'=>'/images/escape.png'
            ],
        ];

        if($activeRoute){
            foreach ($res as $k=>$v){
                if($v['route'] == $activeRoute){
                    $res[$k]['active'] = true;
                    $this->get('main.breadcrumbs')->add($v['title'], $this->generateUrl($v['route']));
                    break;
                }
            }
        }
        return $res;
    }
}
