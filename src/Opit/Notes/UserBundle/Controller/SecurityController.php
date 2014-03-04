<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Opit\Notes\UserBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;
use Opit\Notes\UserBundle\Entity\User;
use Opit\Notes\UserBundle\Form\UserType;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\SecurityContext;
use Opit\Notes\UserBundle\Entity\UserRepository;

/**
 * Description of SecurityController
 *
 * @author OPIT\kaufmann
 */
class SecurityController extends Controller
{
    /**
     * @Route("/secured/login", name="OpitNotesUserBundle_security_login")
     * @Template()
     */
    public function loginAction(Request $request)
    {
        $form = $this->createForm(new UserType());
        
        $request = $this->getRequest();
        $session = $request->getSession();
        
        if ($request->attributes->has(SecurityContext::AUTHENTICATION_ERROR)) {
            $error = $request->attributes->get(SecurityContext::AUTHENTICATION_ERROR);
        } else {
            $error = $session->get(SecurityContext::AUTHENTICATION_ERROR);
            $session->remove(SecurityContext::AUTHENTICATION_ERROR);
        }
        
        return array(
            'last_username' => $session->get(SecurityContext::LAST_USERNAME),
            'error'         => $error,
            'form' => $form->createView()
        );
    }
    
    /**
     * @Route("/secured/login_check", name="OpitNotesUserBundle_check")
     */
    public function securityCheckAction()
    {
        // The security layer will intercept this request
    }

    /**
     * @Route("/secured/logout", name="OpitNotesUserBundle_logout")
     */
    public function logoutAction()
    {
        // The security layer will intercept this request
    }
}
