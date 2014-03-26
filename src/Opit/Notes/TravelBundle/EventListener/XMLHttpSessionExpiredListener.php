<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.

 *  */
namespace Opit\Notes\TravelBundle\EventListener;

use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Symfony\Component\HttpKernel\HttpKernel;

/**
 * Description of NotificationExceptionListener
 *
 * @author OPIT\kaufmann
 */
class XMLHttpSessionExpiredListener
{
    public function onKernelResponse(FilterResponseEvent $event)
    {
        if (HttpKernel::MASTER_REQUEST != $event->getRequestType()) {
            // don't do anything if it's not the master request
            return;
        }
        
        if (false === strpos($event->getRequest()->getRequestUri(), 'login')) {
            if ($event->getRequest()->isXmlHttpRequest() && $event->getResponse()->getStatusCode() == "302") {
                $event->getResponse()->setStatusCode(403);
                $response = new \Symfony\Component\HttpFoundation\Response();
                $response->setStatusCode(403);
                $event->setResponse($response);
            }
        }
        
    }
}
