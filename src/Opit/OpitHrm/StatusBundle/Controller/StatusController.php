<?php

/*
 *  This file is part of the OPIT-HRM project.
 *
 *  (c) Opit Consulting Kft. <info@opit.hu>
 *
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 */

namespace Opit\OpitHrm\StatusBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFoundation\Request;
use JMS\SecurityExtraBundle\Annotation\Secure;
use Opit\OpitHrm\StatusBundle\Form\ChangeStatusType;

/**
 * StatusController
 *
 * @author OPIT Consulting Kft. - PHP Team - {@link http://www.opit.hu}
 * @version 1.0
 * @package OPIT-HRM
 * @subpackage StatusBundle
 */
class StatusController extends Controller
{
    /**
     * Render the change status template
     *
     * @Route("/secured/status/change/show", name="OpitOpitHrmStatusBundle_status_change_show", options={"expose"=true})
     * @Secure(roles="ROLE_USER")
     * @Method({"POST"})
     * @Template("OpitOpitHrmStatusBundle::showChangeStatus.html.twig")
     */
    public function showChangeStatusAction(Request $request)
    {
        $formData = $request->request->all();
        $form = $this->createForm(new ChangeStatusType(), $formData);
        $templateVars = array_merge(array('form' => $form->createView()), $formData);

        if ($template = $request->request->get('template')) {
            return $this->render($template, $templateVars);
        }

        return $templateVars;
    }
}
