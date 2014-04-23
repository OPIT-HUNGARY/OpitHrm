<?php

/*
 *  This file is part of the {Bundle}.
 * 
 *  (c) Opit Consulting Kft. <info@opit.hu>
 * 
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 */

namespace Opit\Notes\UserBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

/**
 * Description of DefaultController
 *
 * @author OPIT Consulting Kft. - PHP Team - {@link http://www.opit.hu}
 * @version 1.0
 * @package Notes
 * @subpackage UserBundle
 */
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
