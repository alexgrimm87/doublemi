<?php

namespace MainBundle\Entity;


use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\EntityManager;
use MainBundle\Controller\EmailController;
use MainBundle\MainBundle;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use UserBundle\Entity\User;
use Swift_Mailer;

/**
 * EmailTemplate
 *
 * @ORM\Table(name="email_template")
 * @ORM\Entity(repositoryClass="MainBundle\Repository\EmailTemplateRepository")
 */
class EmailTemplate extends Inheritance
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="fromName", type="string", length=255)
     */
    private $fromName;

    /**
     * @var string
     *
     * @ORM\Column(name="subject", type="string", length=255)
     */
    private $subject;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=255)
     */
    private $name;

    /**
     * @var string
     *
     * @ORM\Column(name="code", type="string", length=255)
     */
    private $code;

    /**
     * @var string
     *
     * @ORM\Column(name="text", type="text")
     */
    private $text;

    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set fromName
     *
     * @param string $fromName
     * @return EmailTemplate
     */
    public function setFromName($fromName)
    {
        $this->fromName = $fromName;

        return $this;
    }

    /**
     * Get fromName
     *
     * @return string
     */
    public function getFromName()
    {
        return $this->fromName;
    }

    /**
     * Set name
     *
     * @param string $name
     * @return EmailTemplate
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set subject
     *
     * @param string $subject
     * @return EmailTemplate
     */
    public function setSubject($subject)
    {
        $this->subject = $subject;

        return $this;
    }

    /**
     * Get subject
     *
     * @return string
     */
    public function getSubject()
    {
        return $this->subject;
    }

    /**
     * Set code
     *
     * @param string $code
     * @return EmailTemplate
     */
    public function setCode($code)
    {
        $this->code = $code;

        return $this;
    }

    /**
     * Get code
     *
     * @return string
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * Set text
     *
     * @param string $text
     * @return EmailTemplate
     */
    public function setText($text)
    {
        $this->text = $text;

        return $this;
    }

    /**
     * Get text
     *
     * @return string
     */
    public function getText()
    {
        return $this->text;
    }

    public function __toString()
    {
        return ($this->code?$this->code:'-');
    }

    static function parseContent($text, $attributes)
    {
        $keys = array();
        $values = array();

        foreach ($attributes as $key => $value) {
            $keys[] = '%'.$key.'%';
            $values[] = $value;
        }

        foreach ($values as $k=>$v){
            if(is_array($v)){
                $values[$k] = implode(', ', $v);
            }
        }

        return str_replace($keys, $values, $text);
    }

    public static function sendEmail($code, $attributes, $container)
    {
        $em = $container->get('doctrine')->getManager();
        /**
         * @var EmailTemplate $template
         * @var Users $user
         */
        $template = $em->getRepository('MainBundle:EmailTemplate')->findOneByCode($code);

        if( !$template )
        {
            throw new HttpException(404, 'EmailTempalte with code "'.$code.'" not found.');
        }

        if(!empty($attributes['emailTo'])) {

            $message = \Swift_Message::newInstance()
                //->setSubject(self::parseContent($template->getSubject(),$attributes))
                ->setFrom(array($attributes['emailFrom'] => self::parseContent($template->getFromName(),$attributes)))
                ->setTo($attributes['emailTo'])
                ->setBody(

                    $container->get('templating')->render(
                        'MainBundle:EmailTemplate:default.html.twig',
                        array('content' => self::parseContent($template->getText(),$attributes))
                    ),
                    'text/html'
                );

            EmailController::sentMail(self::parseContent($template->getSubject(),$attributes), $message);

            /*if(mail($mailTo,$message->getSubject(),$message->getBody(),$headers))
                return true;
            /*
            if ($container->get('mailer')->send($message) == 1)
                return true;
            */
        }
        return false;
    }
}

