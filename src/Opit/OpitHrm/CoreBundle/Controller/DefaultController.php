<?php

namespace Opit\OpitHrm\CoreBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DefaultController extends Controller
{
    public function indexAction()
    {
        throw $this->createNotFoundException('Core bundle is not accessible.');
    }
}
