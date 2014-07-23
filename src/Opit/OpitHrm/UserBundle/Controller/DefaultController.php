<?php

/*
 *  This file is part of the OPIT-HRM project.
 * 
 *  (c) Opit Consulting Kft. <info@opit.hu>
 * 
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 */

namespace Opit\OpitHrm\UserBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

/**
 * Description of DefaultController
 *
 * @author OPIT Consulting Kft. - PHP Team - {@link http://www.opit.hu}
 * @version 1.0
 * @package OPIT-HRM
 * @subpackage UserBundle
 */
class DefaultController extends Controller
{
    /**
     * @Route("/", name="OpitOpitHrmUserBundle_default_index")
     * @Template()
     */
    public function indexAction()
    {
        return $this->forward('OpitOpitHrmUserBundle:Security:login');
    }
    
    /**
     * @Route("/secured/opithrm/versions", name="OpitOpitHrmUserBundle_default_versions")
     * @Template()
     */
    public function versionsAction()
    {
        return array();
    }
}
