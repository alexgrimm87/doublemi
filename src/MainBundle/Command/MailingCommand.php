<?php
namespace MainBundle\Command;

use MainBundle\Controller\EmailController;
use MainBundle\Entity\MailingList;
use MainBundle\Entity\MailingSubscribe;
use MainBundle\Entity\MailingTemplate;
use MainBundle\MainBundle;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class MailingCommand extends ContainerAwareCommand
{
    protected function configure() {
        $this
            ->setDescription('Create mailing list and send step')
            ->setName('mailing:generate')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output) {
        //generate new email list
        $container = MainBundle::getContainer();
        $templates = $container->get('doctrine')->getRepository('MainBundle:MailingTemplate')->findBy(['generated'=>0],[],1000);
        $subscribers = $container->get('doctrine')->getRepository('MainBundle:MailingSubscribe')->findAll();

        $manager = $container->get('doctrine')->getManager();
        if($templates && $subscribers){
            /**
             * @var MailingTemplate $template
             * @var MailingSubscribe $subscriber
             */
            foreach ($templates as $template){
                if($template->getDate() && $template->getDate()->getTimestamp() < time()){
                    foreach ($subscribers as $subscriber){
                        $new = new MailingList();
                        $new->setTemplate($template);
                        $new->setSubscriber($subscriber);

                        $manager->persist($new);
                    }
                    $template->setGenerated(true);
                    $manager->persist($template);
                }
            }
            $manager->flush();
        }

        //send email list part
        $count = $container->getParameter('mailing_step_count');
        $list = $container->get('doctrine')->getRepository('MainBundle:MailingList')->findBy([],[],intval($count));

        $conf = $container->get('admin.config')->getData('config');
        if(!isset($conf['email']))
            throw new \Exception('Не указан имейл администратора');

        if($list){
            /**
             * @var MailingList $value
             */
            foreach ($list as $value){
                // send
                $message = $value->generateMessage($conf['email'], $container);
                EmailController::sentMail($value->getTemplate()->getSubject(),$message);

                //delete element
                $manager->remove($value);
            }
            $manager->flush();
        }

    }
}