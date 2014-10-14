<?php

/*
 *  This file is part of the OPIT-HRM project.
 *
 *  (c) Opit Consulting Kft. <info@opit.hu>
 *
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 */

namespace Opit\OpitHrm\UserBundle\Security\Ldap;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\SimpleFormAuthenticatorInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\Encoder\EncoderFactoryInterface;
use Symfony\Component\Security\Core\Exception\BadCredentialsException;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserCheckerInterface;
use Psr\Log\LoggerInterface;
use Zend\Ldap\Ldap;

/**
 * Description of LdapAuthenticator
 *
 * @author OPIT Consulting Kft. - PHP Team - {@link http://www.opit.hu}
 * @version 1.0
 * @package OPIT-HRM
 * @subpackage UserBundle
 */
class LdapAuthenticator implements SimpleFormAuthenticatorInterface
{

    private $encoderFactory;
    private $userChecker;
    private $ldapManager;
    private $logger;
    private $hideUserNotFoundExceptions;

    public function __construct(EncoderFactoryInterface $encoderFactory, UserCheckerInterface $userChecker, Ldap $ldapManager = null, LoggerInterface $logger = null, $hideUserNotFoundExceptions = true)
    {
        $this->encoderFactory = $encoderFactory;
        $this->userChecker = $userChecker;
        $this->ldapManager = $ldapManager;
        $this->logger = $logger;
        $this->hideUserNotFoundExceptions = $hideUserNotFoundExceptions;
    }

    /**
     * Function used for user authentication based on token object
     *
     * @param  \Symfony\Component\Security\Core\Authentication\Token\TokenInterface        $token
     * @param  \Symfony\Component\Security\Core\User\UserProviderInterface                 $userProvider
     * @param  string                                                                      $providerKey
     * @return \Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken
     * @throws BadCredentialsException
     */
    public function authenticateToken(TokenInterface $token, UserProviderInterface $userProvider, $providerKey)
    {
        $passwordValid = false;

        // Load user object
        try {
            $user = $userProvider->loadUserByUsername($token->getUsername());
        } catch (UsernameNotFoundException $e) {
            throw new BadCredentialsException('Invalid username or password', 0, $e);
        }

        try {
            $this->userChecker->checkPreAuth($user);
            // Call the correct authentication method
            if (null !== $this->ldapManager && $user->isLdapEnabled()) {
                $passwordValid = $this->checkAuthenticationLdap($user, $token);
            } else {
                $passwordValid = $this->checkAuthentication($user, $token);
            }
            $this->userChecker->checkPostAuth($user);
        } catch (BadCredentialsException $e) {
            if ($this->hideUserNotFoundExceptions) {
                throw new BadCredentialsException('Invalid username or password', 0, $e);
            }

            throw $e;
        }

        // Set the authenticated token
        if ($passwordValid) {
            return new UsernamePasswordToken($user, $user->getPassword(), $providerKey, $user->getRoles());
        }

        throw new BadCredentialsException('Invalid username or password');
    }

    /**
     * {@inheritdoc}
     */
    public function supportsToken(TokenInterface $token, $providerKey)
    {
        return $token instanceof UsernamePasswordToken && $token->getProviderKey() === $providerKey;
    }

    /**
     * {@inheritdoc}
     */
    public function createToken(Request $request, $username, $password, $providerKey)
    {
        return new UsernamePasswordToken($username, $password, $providerKey);
    }

    /**
     * Authenticates the user against the db
     *
     * @param \Symfony\Component\Security\Core\User\UserInterface $user
     * @param \Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken $token
     * @return boolean
     * @throws BadCredentialsException
     */
    protected function checkAuthentication(UserInterface $user, UsernamePasswordToken $token)
    {
        $currentUser = $token->getUser();
        if ($currentUser instanceof UserInterface) {
            if ($currentUser->getPassword() !== $user->getPassword()) {
                throw new BadCredentialsException('The credentials were changed from another session.');
            }
        } else {
            if ("" === ($presentedPassword = $token->getCredentials())) {
                throw new BadCredentialsException('The presented password cannot be empty.');
            }

            if (!$this->encoderFactory->getEncoder($user)->isPasswordValid($user->getPassword(), $presentedPassword, $user->getSalt())) {
                throw new BadCredentialsException('The presented password is invalid.');
            }
        }

        if (null !== $this->logger && !$token->isAuthenticated()) {
            $this->logger->info(
                "[LdapAuthenticator] Local authentication successful.",
                array('user' => $user->getUsername())
            );
        }

        return true;
    }

    /**
     * Authenticates the user via ldap
     *
     * @param \Symfony\Component\Security\Core\User\UserInterface $user
     * @param \Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken $token
     * @return boolean $passwordValid
     * @throws BadCredentialsException
     */
    protected function checkAuthenticationLdap(UserInterface $user, UsernamePasswordToken $token)
    {
        $currentUser = $token->getUser();
        // Due to ldap restrinctions we expect a user authenticated once the token
        // contains a user object
        if ($currentUser instanceof UserInterface) {
            return true;
        }

        try {
            $this->ldapManager->bind($token->getUsername(), $token->getCredentials());
            $passwordValid = (boolean) $this->ldapManager->getBoundUser();

            if (null !== $this->logger && !$token->isAuthenticated()) {
                $this->logger->info(
                    "[LdapAuthenticator] Ldap authentication successful.",
                    array('user' => $this->ldapManager->getBoundUser())
                );
            }

            return $passwordValid;
        } catch (\Zend\Ldap\Exception\LdapException $e) {
            throw new BadCredentialsException('Ldap authentication failed', 0, $e);
        }
    }
}
