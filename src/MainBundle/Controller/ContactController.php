<?php
namespace MainBundle\Controller;

use MainBundle\Entity\CatalogSection;
use MainBundle\Entity\Contact;
use MainBundle\Entity\Product;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\HttpFoundation\Request;


class ContactController extends BaseController
{
    /**
     * @Route("/contact", name="contact_page")
     */
    public function indexAction(Request $request, $params='')
    {
        $pageParams = $this->getBases($request);
        $data = new Contact();

        if ($request->getMethod() == 'POST') {

            $data->setName($request->request->get('name'));
            $data->setEmail($request->request->get('email'));
            $data->setTheme($request->request->get('theme'));
            $data->setQuestion($request->request->get('question'));

            $errors = $this->get('validator')->validate($data);

            if (count($errors) == 0){
                $manager = $this->getDoctrine()->getManager();
                $manager->persist($data);
                $manager->flush();

                //TODO: EMAIL NOTIFICATION
            }

            if($request->isXmlHttpRequest()){ // ajax
                if($data->getId())
                    echo 'true';
                else
                    echo 'false';
                exit();
            }
        }

        $pageParams['contact'] = $this->get('admin.config')->getData('contact');
        $pageParams['breadcrumbs'] = $this->get('main.breadcrumbs')
            ->add('Контакты', $this->generateUrl('contact_page'));

        return $this->render('MainBundle:Contact:contact.html.twig',$pageParams);
    }
}