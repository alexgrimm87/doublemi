<?php
/**
 * Created by PhpStorm.
 * User: N1
 * Date: 24.05.17
 * Time: 12:47
 */

namespace UserBundle\Form\Handler;

use FOS\UserBundle\Form\Handler\RegistrationFormHandler as BaseHandler;
use MainBundle\Entity\EmailTemplate;
use Symfony\Component\Form\Form;
use UserBundle\Entity\User;
use FOS\UserBundle\Model\UserManagerInterface;
use FOS\UserBundle\Model\UserInterface;
use FOS\UserBundle\Mailer\MailerInterface;
use FOS\UserBundle\Util\TokenGeneratorInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use UserBundle\UserBundle;

class RegistrationFormHandler extends BaseHandler
{
    public function process($confirmation = false)
    {
        /**
         * @var User $user
         */
        $user = $this->createUser();
        $user->setUsername(md5(rand(1,99).'qqq'.time()));
        $this->form->setData($user);

        if ('POST' === $this->request->getMethod()) {
            $this->form->bind($this->request);

            if ($this->form->isValid()) {

                $user->setEnabled(false);
                if (null === $user->getConfirmationToken()) {
                    $user->setConfirmationToken($this->tokenGenerator->generateToken());
                }

                //email to user
                $container = UserBundle::getContainer();
                $code = 'registration_confirm';
                $conf = $container->get('admin.config')->getData('config');
                $data = [
                    'emailFrom'=>(isset($conf['email'])?$conf['email']:''),
                    'emailTo'=>$user->getEmail(),
                    'CODE'=>$user->getConfirmationToken(),
                    'URL'=>$container->get('router')->generate('fos_user_registration_confirm',['token'=>$user->getConfirmationToken()],true)
                ];

                EmailTemplate::sendEmail($code,$data,$container);

                $this->userManager->updateUser($user);

                return true;
            }
        }

        return false;
    }

}