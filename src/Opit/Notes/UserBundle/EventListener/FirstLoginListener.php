<?php

namespace Opit\Notes\UserBundle\EventListener;

use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Security\Core\SecurityContext;
use Symfony\Component\Routing\Router;
use Symfony\Component\HttpKernel\HttpKernel;

/**
 *
 * @author OPIT\NOTES
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
        if (HttpKernel::MASTER_REQUEST != $event->getRequestType()) {
            // don't do anything if it's not the master request
            return;
        }
        
        $token = $this->securityContext->getToken();
        $changePasswordUrl = $this->router->generate('OpitNotesUserBundle_user_change_password', array(), true);
        $excludedRoutes = array(
            $this->router->generate('OpitNotesUserBundle_logout', array(), true),
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
