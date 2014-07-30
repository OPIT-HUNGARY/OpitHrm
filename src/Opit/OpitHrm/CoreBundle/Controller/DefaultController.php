<?php

namespace Opit\OpitHrm\CoreBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

class DefaultController extends Controller
{
    public function indexAction()
    {
        throw $this->createNotFoundException('Core bundle is not accessible.');
    }

    /**
     * @Route("/secured/opithrm/release-notes", name="OpitOpitHrmCoreBundle_default_versions")
     * @Template()
     */
    public function versionsAction()
    {
        return array();
    }
}
