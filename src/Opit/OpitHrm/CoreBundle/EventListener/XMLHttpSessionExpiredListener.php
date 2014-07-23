<?php

/*
 *  This file is part of the OPIT-HRM project.
 *
 *  (c) Opit Consulting Kft. <info@opit.hu>
 *
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 */

namespace Opit\OpitHrm\CoreBundle\EventListener;

use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Symfony\Component\HttpKernel\HttpKernel;

/**
 * Description of XMLHttpSessionExpiredListener
 *
 * @author OPIT Consulting Kft. - PHP Team - {@link http://www.opit.hu}
 * @version 1.0
 * @package OPIT-HRM
 * @subpackage CoreBundle
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
                $event->getResponse()->setStatusCode(401);
                $response = new \Symfony\Component\HttpFoundation\Response();
                $response->setStatusCode(401);
                $event->setResponse($response);
            }
        }

    }
}
