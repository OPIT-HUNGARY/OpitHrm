<?php

/*
 *  This file is part of the OPIT-HRM project.
 * 
 *  (c) Opit Consulting Kft. <info@opit.hu>
 * 
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 */

namespace Opit\OpitHrm\UserBundle\EventListener;

use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Security\Core\SecurityContext;
use Symfony\Component\Routing\Router;
use Symfony\Component\HttpKernel\HttpKernel;

/**
 * Description of FirstLoginListener
 * 
 * @author OPIT Consulting Kft. - PHP Team - {@link http://www.opit.hu}
 * @version 1.0
 * @package OPIT-HRM
 * @subpackage UserBundle
 */
class FirstLoginListener
{
    private $securityContext;
    private $router;
    
    /**
     * 
     * @param \Symfony\Component\Security\Core\SecurityContext $securityContext
     * @param \Symfony\Component\Routing\Router $router
     * @param \Symfony\Component\DependencyInjection\Container $container
     */
    public function __construct(SecurityContext $securityContext, Router $router)
    {
	$this->securityContext = $securityContext;
        $this->router = $router;
	}
    
    /**
     * If users flag isFirstLogin is true redirect to change password page.
     * 
     * @param \Symfony\Component\HttpKernel\Event\FilterResponseEvent $event
     */
    public function onKernelRequest(GetResponseEvent $event)
    {
        // Don't do anything if it's not the master request or another firewall than "secured"
        $isSecuredArea = (bool) preg_match('/^\/secured/', $event->getRequest()->getBasePath());
        if (HttpKernel::MASTER_REQUEST != $event->getRequestType() || !$isSecuredArea) {
            return;
        }
        
        $token = $this->securityContext->getToken();
        $changePasswordUrl = $this->router->generate('OpitOpitHrmUserBundle_user_change_password', array(), true);
        $excludedRoutes = array(
            $this->router->generate('OpitOpitHrmUserBundle_logout', array(), true),
            $changePasswordUrl
        );
        
        if (null !== $token) {
            if (in_array($event->getRequest()->getUri(), $excludedRoutes)) {
                return;
            }
            
            if ($token->getUser()->getIsFirstLogin()) {
                $event->setResponse(new RedirectResponse($changePasswordUrl));
            }
        }
        
    }
}
