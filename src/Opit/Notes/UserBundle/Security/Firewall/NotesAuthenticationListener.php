<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Opit\Notes\UserBundle\Security\Firewall;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\Security\Http\Firewall\ListenerInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\SecurityContextInterface;
use Symfony\Component\Security\Core\Authentication\AuthenticationManagerInterface;
use Opit\Notes\UserBundle\Security\Authentication\Token\WsseUserToken;

/**
 * Description of NotesAuthenticationListener
 *
 * @author OPIT\kaufmann
 */
class NotesAuthenticationListener implements ListenerInterface
{
    protected $securityContext;
    protected $authenticationManager;

    public function __construct(SecurityContextInterface $securityContext, AuthenticationManagerInterface $authenticationManager)
    {
        $this->securityContext = $securityContext;
        $this->authenticationManager = $authenticationManager;
    }
    
    public function handle(GetResponseEvent $event)
    {
        $request = $event->getRequest();
        
        $securityPassword = $request->get("password");
        $securityUsername = trim($request->get("username"));
        
        $token = new WsseUserToken();
        $token->setUser($securityUsername);
        $token->digest();
        
        try{
            $authToken = $this->authenticationManager->authenticate($token);
            $this->securityContext->setToken($authToken);
            
            return;
        } catch (Exception $ex) {
            $response = new Response();
            $response->setStatusCode(403);
            $event->setResponse($response);
        }
        
        $response = new Response();
        $response->setStatusCode(403);
        $event->setResponse($response);
    }

//put your code here
}
