<?php

namespace MainBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * MailingList
 *
 * @ORM\Table(name="mailing_list")
 * @ORM\Entity(repositoryClass="MainBundle\Repository\MailingListRepository")
 */
class MailingList
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
     * @var \MainBundle\Entity\MailingTemplate
     * @ORM\ManyToOne(targetEntity="\MainBundle\Entity\MailingTemplate")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="template", referencedColumnName="id",onDelete="CASCADE")
     * })
     */
    private $template;

    /**
     * @var \MainBundle\Entity\MailingSubscribe
     * @ORM\ManyToOne(targetEntity="\MainBundle\Entity\MailingSubscribe")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="subscriber", referencedColumnName="id",onDelete="CASCADE")
     * })
     */
    private $subscriber;

    /**
     * Get id
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return MailingTemplate
     */
    public function getTemplate()
    {
        return $this->template;
    }

    /**
     * @param MailingTemplate $template
     */
    public function setTemplate($template)
    {
        $this->template = $template;
    }

    /**
     * @return MailingSubscribe
     */
    public function getSubscriber()
    {
        return $this->subscriber;
    }

    /**
     * @param MailingSubscribe $subscriber
     */
    public function setSubscriber($subscriber)
    {
        $this->subscriber = $subscriber;
    }

    /**
     * @param $emailFrom
     * @param $container
     * @return \Swift_Message
     */
    public function generateMessage($emailFrom, $container){
        return \Swift_Message::newInstance()
            //->setSubject($this->getTemplate()->getSubject())
            ->setFrom(array($emailFrom => $this->getTemplate()->getFromName()))
            ->setTo($this->getSubscriber()->getEmail())
            ->setBody(
                $container->get('templating')->render(
                    'MainBundle:EmailTemplate:default.html.twig',
                    array('content' => $this->getTemplate()->getText())
                )
                ,
                'text/html'
            );
    }
}

