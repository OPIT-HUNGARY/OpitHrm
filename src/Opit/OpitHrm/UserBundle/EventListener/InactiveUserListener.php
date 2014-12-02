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
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Routing\Router;
use Symfony\Component\HttpKernel\HttpKernel;
use Doctrine\Common\Persistence\ObjectManager;
use Psr\Log\LoggerInterface;

/**
 * Description of InactiveUserListener
 * The purpose of this class is to disable users when their leaving date
 * is passed.
 *
 * @author OPIT Consulting Kft. - PHP Team - {@link http://www.opit.hu}
 * @version 1.0
 * @package OPIT-HRM
 * @subpackage UserBundle
 */
class InactiveUserListener
{
    private $tokenStorage;
    private $entityManager;
    private $router;
    private $logger;

    /**
     *
     * @param \Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface tokenStorage
     * @param \Symfony\Component\Routing\Router $router
     * @param \Symfony\Component\DependencyInjection\Container $container
     */
    public function __construct(TokenStorageInterface $tokenStorage, ObjectManager $entityManager, Router $router, LoggerInterface $logger)
    {
        $this->tokenStorage = $tokenStorage;
        $this->entityManager = $entityManager;
        $this->router = $router;
        $this->logger = $logger;
    }

    /**
     * If users flag isFirstLogin is true redirect to change password page.
     *
     * @param \Symfony\Component\HttpKernel\Event\FilterResponseEvent $event
     */
    public function onKernelRequest(GetResponseEvent $event)
    {
        // Don't do anything if it's not the master request or another firewall than "secured"
        $isSecuredArea = (bool) preg_match('/^\/secured/', $event->getRequest()->getPathInfo());
        if (HttpKernel::MASTER_REQUEST != $event->getRequestType() || !$isSecuredArea) {
            return;
        }

        $token = $this->tokenStorage->getToken();
        $user = $token->getUser();

        // check if employee object exists
        if (is_object($user) && null !== $user->getEmployee()) {
            $today = new \DateTime('today');
            $isInactive = (bool) (null !== $user->getEmployee()->getLeavingDate() && $user->getEmployee()->getLeavingDate() < $today);

            // Update user object if leaving date is passed and user is still active
            if ($token->getUser()->getIsActive() && $isInactive) {
                $user->setIsActive(false);

                $this->entityManager->persist($user);
                $this->entityManager->flush();

                if (null !== $this->logger) {
                    $this->logger->notice(
                        '[InactiveUserListener] Employee passed leaving date, user will be disabled.',
                        array('username' => $user->getUsername())
                    );
                }

                // TODO: Return a proper error message on the login page
                $logoutUrl = $this->router->generate('OpitOpitHrmUserBundle_logout', array(), true);
                $event->setResponse(new RedirectResponse($logoutUrl));
            }
        }

    }
}
