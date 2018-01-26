<?php
/**
 * Created by PhpStorm.
 * User: N1
 * Date: 24.05.17
 * Time: 15:50
 */

namespace UserBundle\Controller;


use MainBundle\Entity\EmailTemplate;
use Symfony\Component\DependencyInjection\ContainerAware;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\Exception\AccountStatusException;
use FOS\UserBundle\Model\UserInterface;
use MainBundle\Controller\BaseController as MainBundleController;

class ResettingController extends MainBundleController
{
    const SESSION_EMAIL = 'fos_user_send_resetting_email/email';

    /**
     * Request reset user password: show form
     */
    public function requestAction()
    {
        $user = $this->getUser();
        if($user)
            return $this->redirectToRoute('fos_user_profile_show');
        $paramsTemplate = $this->getBases();
        return $this->container->get('templating')->renderResponse('MainBundle:Security:reset_request.html.twig',$paramsTemplate);
    }

    /**
     * Request reset user password: submit form and send email
     */
    public function sendEmailAction()
    {
        $username = $this->container->get('request')->request->get('username');

        /** @var $user UserInterface */
        $user = $this->container->get('fos_user.user_manager')->findUserByUsernameOrEmail($username);
        $paramsTemplate = $this->getBases();
        if (null === $user) {
            $paramsTemplate['invalid_username'] = $username;
            return $this->container->get('templating')->renderResponse('MainBundle:Security:reset_request.html.twig', $paramsTemplate);
        }

        if ($user->isPasswordRequestNonExpired($this->container->getParameter('fos_user.resetting.token_ttl'))) {
            return $this->container->get('templating')->renderResponse('MainBundle:Security:passwordAlreadyRequested.html.twig');
        }

        if (null === $user->getConfirmationToken()) {
            /** @var $tokenGenerator \FOS\UserBundle\Util\TokenGeneratorInterface */
            $tokenGenerator = $this->container->get('fos_user.util.token_generator');
            $user->setConfirmationToken($tokenGenerator->generateToken());
        }

        $this->container->get('session')->set(static::SESSION_EMAIL, $this->getObfuscatedEmail($user));
        ///change password request email
        $code = 'resetting_password';
        $conf = $this->container->get('admin.config')->getData('config');
        $data = [
            'emailFrom'=>(isset($conf['email'])?$conf['email']:''),
            'emailTo'=>$user->getEmail(),
            'CODE'=>$user->getConfirmationToken(),
            'URL'=>$this->container->get('router')->generate('fos_user_resetting_reset',['token'=>$user->getConfirmationToken()],true)
        ];

        EmailTemplate::sendEmail($code,$data,$this->container);
        //////

        $user->setPasswordRequestedAt(new \DateTime());
        $this->container->get('fos_user.user_manager')->updateUser($user);

        return new RedirectResponse($this->container->get('router')->generate('fos_user_resetting_check_email'));
    }

    /**
     * Tell the user to check his email provider
     */
    public function checkEmailAction()
    {
        $session = $this->container->get('session');
        $email = $session->get(static::SESSION_EMAIL);
        $session->remove(static::SESSION_EMAIL);

        if (empty($email)) {
            // the user does not come from the sendEmail action
            return new RedirectResponse($this->container->get('router')->generate('fos_user_resetting_request'));
        }

        $paramsTemplate = $this->getBases();
        $paramsTemplate['email'] = $email;
        return $this->container->get('templating')->renderResponse('MainBundle:Security:reset_checkEmail.html.twig', $paramsTemplate);
    }

    /**
     * Reset user password
     */
    public function resetAction($token)
    {
        $baseData = $this->getBases();
        $user = $this->container->get('fos_user.user_manager')->findUserByConfirmationToken($token);

        if (null === $user) {
            return $this->container->get('templating')->renderResponse('MainBundle:Security:invalidCheckEmail.html.twig', $baseData);
        }

        if (!$user->isPasswordRequestNonExpired($this->container->getParameter('fos_user.resetting.token_ttl'))) {
            return new RedirectResponse($this->container->get('router')->generate('fos_user_resetting_request'));
        }

        $form = $this->container->get('fos_user.resetting.form');
        $formHandler = $this->container->get('fos_user.resetting.form.handler');
        $process = $formHandler->process($user);

        if ($process) {
            $this->setFlash('fos_user_success', 'resetting.flash.success');
            $response = new RedirectResponse($this->getRedirectionUrl($user));
            $this->authenticateUser($user, $response);

            return $response;
        }

        $baseData['token'] = $token;
        $baseData['form'] = $form->createView();
        return $this->container->get('templating')->renderResponse('MainBundle:Security:reset_password.html.twig', $baseData);
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
     * Generate the redirection url when the resetting is completed.
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
     * Get the truncated email displayed when requesting the resetting.
     *
     * The default implementation only keeps the part following @ in the address.
     *
     * @param \FOS\UserBundle\Model\UserInterface $user
     *
     * @return string
     */
    protected function getObfuscatedEmail(UserInterface $user)
    {
        $email = $user->getEmail();
        if (false !== $pos = strpos($email, '@')) {
            $email = '...' . substr($email, $pos);
        }

        return $email;
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