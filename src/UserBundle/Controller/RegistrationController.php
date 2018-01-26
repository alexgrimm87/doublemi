<?php
/**
 * Created by PhpStorm.
 * User: N1
 * Date: 24.05.17
 * Time: 11:40
 */

namespace UserBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Security;
use Doctrine\ORM\Query\Expr\Base;
use MainBundle\Controller\DefaultController as MainController;
use Symfony\Component\Security\Core\SecurityContext;
use MainBundle\Controller\BaseController as MainBundleController;
use Symfony\Component\DependencyInjection\ContainerAware;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Core\Exception\AccountStatusException;
use FOS\UserBundle\Model\UserInterface;
use Symfony\Component\Form\Form;

class RegistrationController extends MainBundleController
{
    public function registerAction()
    {
        $user = $this->getUser();
        if($user)
            return $this->redirectToRoute('fos_user_profile_show');

        /**
         * @var  Form $form
         */
        $form = $this->container->get('fos_user.registration.form');;

        $formHandler = $this->container->get('fos_user.registration.form.handler');
        $confirmationEnabled = $this->container->getParameter('fos_user.registration.confirmation.enabled');

        $process = $formHandler->process($confirmationEnabled);
        if ($process) {
            $user = $form->getData();

            $authUser = false;

            $this->container->get('session')->set('fos_user_send_confirmation_email/email', $user->getEmail());
            $route = 'fos_user_registration_check_email';

            $this->setFlash('fos_user_success', 'registration.flash.user_created');
            $url = $this->container->get('router')->generate($route);
            $response = new RedirectResponse($url);

            if ($authUser) {
                $this->authenticateUser($user, $response);
            }

            return $response;
        }
        $baseData = $this->getBases();
        $baseData['form'] =  $form->createView();
        return $this->container->get('templating')->renderResponse('MainBundle:Security:register.html.twig', $baseData);
    }

    /**
     * Tell the user to check his email provider
     */
    public function checkEmailAction()
    {
        $user = $this->getUser();
        if($user)
            return $this->redirectToRoute('fos_user_profile_show');

        $email = $this->container->get('session')->get('fos_user_send_confirmation_email/email');
        $this->container->get('session')->remove('fos_user_send_confirmation_email/email');
        $user = $this->container->get('fos_user.user_manager')->findUserByEmail($email);

        if (null === $user) {
            throw new NotFoundHttpException(sprintf('The user with email "%s" does not exist', $email));
        }

        $baseData = $this->getBases();
        $baseData['user'] = $user;
        return $this->container->get('templating')->renderResponse('MainBundle:Security:checkEmail.html.twig', $baseData);
    }

    /**
     * Receive the confirmation token from user email provider, login the user
     */
    public function confirmAction($token)
    {
        $user = $this->getUser();
        if($user)
            return $this->redirectToRoute('fos_user_profile_show');

        $baseData = [];
        $user = $this->container->get('fos_user.user_manager')->findUserByConfirmationToken($token);

        if (null === $user) {
            return $this->container->get('templating')->renderResponse('MainBundle:Security:invalidCheckEmail.html.twig', $baseData);
        }

        $user->setConfirmationToken(null);
        $user->setEnabled(true);
        $user->setLastLogin(new \DateTime());

        $this->container->get('fos_user.user_manager')->updateUser($user);
        $response = new RedirectResponse($this->container->get('router')->generate('fos_user_registration_confirmed'));
        $this->authenticateUser($user, $response);

        return $response;
    }

    /**
     * Tell the user his account is now confirmed
     */
    public function confirmedAction()
    {
        $user = $this->container->get('security.context')->getToken()->getUser();
        if (!is_object($user) || !$user instanceof UserInterface) {
            throw new AccessDeniedException('This user does not have access to this section.');
        }

        $baseData = $this->getBases();
        $baseData['user'] = $user;
        return $this->container->get('templating')->renderResponse('MainBundle:Security:confirmed.html.twig', $baseData);
    }

    /**
     * Authenticate a user with Symfony Security
     *
     * @param \FOS\UserBundle\Model\UserInterface        $user
     * @param \Symfony\Component\HttpFoundation\Response $response
     */
    protected function authenticateUser(UserInterface $user, Response $response)
    {
        try {
            $this->container->get('fos_user.security.login_manager')->loginUser(
                $this->container->getParameter('fos_user.firewall_name'),
                $user,
                $response);
        } catch (AccountStatusException $ex) {
            // We simply do not authenticate users which do not pass the user
            // checker (not enabled, expired, etc.).
        }
    }

    /**
     * @param string $action
     * @param string $value
     */
    protected function setFlash($action, $value)
    {
        $this->container->get('session')->getFlashBag()->set($action, $value);
    }

    protected function getEngine()
    {
        return $this->container->getParameter('fos_user.template.engine');
    }
}