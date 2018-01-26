<?php

namespace AdminBundle\Controller;

use AdminBundle\Page\DataBaseAdmin;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;

class DefaultController extends PageController
{
    public function indexAction(Request $request)
    {
        $info = new DataBaseAdmin();

        // get menu
        $this->get('admin.pages')->getMenu($info,$this);

        return $this->render('AdminBundle:Admin:dashboard.html.twig',['admin'=>$info]);
    }
}
