<?php

namespace Opit\Notes\UserBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

class DefaultController extends Controller
{
    /**
     * @Route("/", name="OpitNotesUserBundle_default_index")
     * @Template()
     */
    public function indexAction()
    {
        return $this->forward('OpitNotesUserBundle:Security:login');
    }
    
    /**
     * @Route("/secured/notes/versions", name="OpitNotesUserBundle_default_versions")
     * @Template()
     */
    public function versionsAction()
    {
        return array();
    }
}
