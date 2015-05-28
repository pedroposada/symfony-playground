<?php

namespace PSL\ClipperBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DefaultController extends Controller
{
    public function indexAction($name)
    {
        return $this->render('PSLClipperBundle:Default:index.html.twig', array('name' => $name));
    }
}
