<?php
/**
 * Created by PhpStorm.
 * User: N1
 * Date: 15.05.17
 * Time: 17:16
 */

namespace MainBundle\Controller;

use MainBundle\Entity\CatalogSection;
use MainBundle\Entity\Feedback;
use MainBundle\Entity\Product;
use MainBundle\Form\Type\FeedbackType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;

class FeedbackController extends BaseController
{
    /**
     * @Route("/feedback", name="feedback_root")
     */
    public function indexAction(Request $request){
        $filterKey = 'filter';
        $pageParams = $this->getBases($request);

        $limit = 5;
        $page = 1;
        $filter = [];

        $this->pageNavigationParams($request, $filter, $page, $limit);

        $filter[$filterKey]['enabled'] = 1;
        $filter['_sort']['date'] = 'DESC';

        // get objects
        $pageParams['objects'] = $this->customMatching($filterKey,'MainBundle:Feedback',$filter);

        // page navigation
        $pager = $this->get('pager_generator');
        $pager->setParams($pageParams['objects']['meta']['total'] , $limit);
        $pageParams['pager'] =  $pager->getPager($page);


        $pageParams['breadcrumbs'] = $this->get('main.breadcrumbs')
                                          ->add('Отзывы', $this->generateUrl('feedback_root'));

        $pageParams['formMessage'] = '';

        //get users feedback
        $user = $this->getUser();
        $pageParams['userFeedback'] = ($user? $this->getDoctrine()->getRepository('MainBundle:Feedback')->findOneBy(['user'=>$user]) : null);

        if(!$pageParams['userFeedback'] && $user){
            $feedback = new Feedback();
            $form = $this->createForm(new FeedbackType(),$feedback)->handleRequest($request);
            if($request->getMethod() == 'POST'){
                $feedback = $form->getData();
                $errors = $this->get('validator')->validate($feedback);
                if (count($errors) > 0){
                    $pageParams['formMessage'] = 'Ошибка валидации';
                } else {
                    if($feedback->getPictureFile()){
                        try {
                            $feedback->setPicture(FileController::upload($feedback->getPictureFile(), Feedback::FILE_FOLDER, FileController::PIC_TYPE));
                        } catch (\Exception $e){

                        }
                    }
                    $feedback->setUser($user);

                    $manager = $this->getDoctrine()->getManager();
                    $manager->persist($feedback);
                    $manager->flush();

                    //todo: email
                }

                if($request->isXmlHttpRequest()){
                    if($feedback->getId()){
                        echo 'true';
                    } else {
                        echo 'false';
                    }
                    exit();
                }
            }
        }


        return $this->render('MainBundle:Text:feedback.html.twig',$pageParams);
    }
}